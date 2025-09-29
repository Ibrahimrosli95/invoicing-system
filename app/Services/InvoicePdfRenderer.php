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
     */
    public function generate(Invoice $invoice, array $options = []): string
    {
        $invoice->loadMissing([
            'company',
            'items',
            'createdBy',
            'paymentRecords' => fn ($query) => $query->orderBy('payment_date'),
        ]);

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
     */
    public function downloadResponse(Invoice $invoice): \Symfony\Component\HttpFoundation\StreamedResponse
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
     */
    public function inlineResponse(Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        $filename = $this->makeFilename($invoice);
        $pdf = $this->generate($invoice);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    protected function makeFilename(Invoice $invoice): string
    {
        $number = $invoice->number ?: 'draft-invoice';

        return 'invoice-' . Str::slug($number) . '.pdf';
    }

    /**
     * Prepare shared data for the invoice PDF view.
     */
    protected function buildViewData(Invoice $invoice, array $options = []): array
    {
        $currency = $this->settingsService->getSetting('defaults.currency', 'RM', $invoice->company_id);

        return [
            'invoice' => $invoice,
            'sections' => $this->resolveSections($invoice),
            'palette' => $this->resolvePalette($invoice, $options),
            'currency' => $currency,
            'currencyHelper' => function($amount) use ($currency) {
                return $currency . ' ' . number_format($amount, 2);
            },
            'dateHelper' => function($date) {
                return $date ? $date->format('d M, Y') : '';
            },
        ];
    }

    protected function resolveSections(Invoice $invoice): array
    {
        $defaultSections = $this->settingsService->getOptionalSections($invoice->company_id);
        $invoiceSections = $invoice->optional_sections ?? [];

        if (is_string($invoiceSections)) {
            $invoiceSections = json_decode($invoiceSections, true) ?? [];
        }

        $logoSettings = $this->settingsService->getLogoSettings($invoice->company_id);

        return [
            'show_shipping' => $invoiceSections['show_shipping'] ?? ($defaultSections['show_shipping'] ?? false),
            'show_payment_instructions' => $invoiceSections['show_payment_instructions'] ?? ($defaultSections['show_payment_instructions'] ?? false),
            'show_signatures' => $invoiceSections['show_signatures'] ?? ($defaultSections['show_signatures'] ?? false),
            'show_additional_notes' => $invoiceSections['show_additional_notes'] ?? ($defaultSections['show_additional_notes'] ?? false),
            'show_terms_conditions' => $invoiceSections['show_terms_conditions'] ?? ($defaultSections['show_terms_conditions'] ?? true),
            'show_company_logo' => $logoSettings['show_company_logo'] ?? true,
        ];
    }

    protected function resolvePalette(Invoice $invoice, array $options = []): array
    {
        $appearance = $this->settingsService->getSetting('appearance', [], $invoice->company_id);

        return array_merge([
            'background_color' => '#ffffff',
            'border_color' => '#e5e7eb',
            'heading_color' => '#111827',
            'subheading_color' => '#1f2937',
            'text_color' => '#111827',
            'muted_text_color' => '#6b7280',
            'accent_color' => '#1d4ed8',
            'accent_text_color' => '#ffffff',
            'table_header_background' => '#1d4ed8',
            'table_header_text' => '#ffffff',
            'table_row_even' => '#f8fafc',
        ], $appearance, $options['palette'] ?? []);
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