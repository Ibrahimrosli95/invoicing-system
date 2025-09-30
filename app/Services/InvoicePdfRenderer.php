<?php

namespace App\Services;

use App\Models\Invoice;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class InvoicePdfRenderer
{
    public function __construct(private InvoiceSettingsService $settingsService)
    {
    }

    /**
     * Generate the raw PDF binary for an invoice.
     *
     * @param Invoice|\stdClass $invoice Invoice model or mock object
     */
    public function generate($invoice, array $options = []): string
    {
        // Only load relationships if this is an actual Invoice model
        if ($invoice instanceof Invoice) {
            $invoice->loadMissing([
                'company',
                'items',
                'createdBy',
                'paymentRecords' => fn ($query) => $query->orderBy('payment_date'),
            ]);
        }

        $viewData = $this->buildViewData($invoice, $options);
        $html = View::make('pdf.invoice', $viewData)->render();

        $dompdf = $this->makeDompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $paper = $options['paper'] ?? 'A4';
        $orientation = $options['orientation'] ?? 'portrait';
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Stream the PDF as a download response.
     *
     * @param Invoice|\stdClass $invoice Invoice model or mock object
     */
    public function downloadResponse($invoice): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = $this->makeFilename($invoice);
        $pdf = $this->generate($invoice);

        return response()->streamDownload(static function () use ($pdf) {
            echo $pdf;
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Stream the PDF inline for preview.
     *
     * @param Invoice|\stdClass $invoice Invoice model or mock object
     */
    public function inlineResponse($invoice): \Symfony\Component\HttpFoundation\Response
    {
        $filename = $this->makeFilename($invoice);
        $pdf = $this->generate($invoice);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * @param Invoice|\stdClass $invoice Invoice model or mock object
     */
    protected function makeFilename($invoice): string
    {
        $number = $invoice->number ?: 'draft-invoice';

        return 'invoice-' . Str::slug($number) . '.pdf';
    }

    /**
     * Prepare shared data for the invoice PDF view.
     *
     * @param Invoice|\stdClass $invoice Invoice model or mock object
     */
    protected function buildViewData($invoice, array $options = []): array
    {
        $companyId = $invoice->company_id ?? null;
        $mergedSettings = $this->settingsService->getMergedSettingsForPDF($invoice, $companyId);
        $currency = $mergedSettings['defaults']['currency'] ?? 'RM';

        return [
            'invoice' => $invoice,
            'sections' => $this->resolveSections($invoice, $mergedSettings),
            'palette' => $this->resolvePalette($invoice, $options, $mergedSettings),
            'columns' => $this->resolveColumns($mergedSettings),
            'currency' => $currency,
            'currencyHelper' => function($amount) use ($currency) {
                return $currency . ' ' . number_format($amount, 2);
            },
            'dateHelper' => function($date) {
                return $date ? $date->format('d M, Y') : '';
            },
            'settings' => $mergedSettings,
        ];
    }

    /**
     * @param Invoice|\stdClass $invoice Invoice model or mock object
     */
    protected function resolveSections($invoice, array $mergedSettings): array
    {
        $sections = $mergedSettings['sections'] ?? [];

        return [
            'show_company_logo' => $sections['show_company_logo'] ?? true,
            'show_payment_instructions' => $sections['show_payment_instructions'] ?? true,
            'show_signatures' => $sections['show_signatures'] ?? true,
        ];
    }

    /**
     * @param Invoice|\stdClass $invoice Invoice model or mock object
     */
    protected function resolvePalette($invoice, array $options, array $mergedSettings): array
    {
        $appearance = $mergedSettings['appearance'] ?? [];

        // SIMPLIFIED - Only 6 essential colors
        $defaults = [
            'accent_color' => '#0b57d0',
            'accent_text_color' => '#ffffff',
            'text_color' => '#000000',
            'muted_text_color' => '#4b5563',
            'heading_color' => '#000000',
            'border_color' => '#d0d5dd',
        ];

        // Precedence: options > appearance > defaults
        return array_merge($defaults, $appearance, $options['palette'] ?? []);
    }

    protected function resolveColumns(array $mergedSettings): array
    {
        $columns = $mergedSettings['columns'] ?? [];

        // If columns is a string, decode it
        if (is_string($columns)) {
            $columns = json_decode($columns, true) ?? [];
        }

        // Ensure columns is an array
        if (!is_array($columns)) {
            $columns = [];
        }

        // Filter out non-array items and ensure each column has required keys
        $columns = array_filter($columns, function($col) {
            return is_array($col) && isset($col['key']) && isset($col['label']);
        });

        // Remove duplicates by key (keep first occurrence)
        $uniqueColumns = [];
        $seenKeys = [];
        foreach ($columns as $col) {
            if (!in_array($col['key'], $seenKeys)) {
                $seenKeys[] = $col['key'];
                $uniqueColumns[] = $col;
            }
        }

        // Filter visible columns and sort by order
        $visibleColumns = array_filter($uniqueColumns, fn($col) => $col['visible'] ?? true);
        usort($visibleColumns, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

        return $visibleColumns;
    }

    /**
     * Calculate invoice totals for PDF display
     */
    public function calculateTotals(Invoice $invoice): array
    {
        $itemsTotal = $invoice->items->sum('total_price');
        $subtotal = $itemsTotal;
        $discountAmount = ($subtotal * ($invoice->discount_percentage ?? 0)) / 100;
        $subtotalAfterDiscount = $subtotal - $discountAmount;
        $taxAmount = ($subtotalAfterDiscount * ($invoice->tax_rate ?? 0)) / 100;
        $total = $subtotalAfterDiscount + $taxAmount;
        $totalPaid = $invoice->paymentRecords->where('status', 'CLEARED')->sum('amount');
        $balance = $total - $totalPaid;

        return [
            'subtotal' => $subtotal,
            'discount' => $discountAmount,
            'subtotal_after_discount' => $subtotalAfterDiscount,
            'tax' => $taxAmount,
            'total' => $total,
            'total_paid' => $totalPaid,
            'balance' => $balance,
        ];
    }

    protected function makeDompdf(array $options = []): Dompdf
    {
        if (!class_exists('Dompdf\Dompdf') || !class_exists('Dompdf\Options')) {
            throw new \Exception('Dompdf package is not installed. Please run "composer install" to install required dependencies.');
        }

        $domPdfOptions = new Options();
        $domPdfOptions->set('isHtml5ParserEnabled', true);
        $domPdfOptions->set('isRemoteEnabled', true);
        $domPdfOptions->set('defaultFont', $options['default_font'] ?? 'Helvetica');

        return new Dompdf($domPdfOptions);
    }
}