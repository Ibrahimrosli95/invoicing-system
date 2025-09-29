<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoicePdfRenderer;
use App\Services\InvoiceSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class InvoicePdfGenerationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $company;
    protected $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a company and user
        $this->company = Company::factory()->create([
            'name' => 'Test Company Ltd',
            'email' => 'test@company.com',
            'phone' => '+60123456789',
            'address' => '123 Test Street',
            'city' => 'Kuala Lumpur',
            'state' => 'Selangor',
            'postal_code' => '50000',
        ]);

        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create a test invoice with items
        $this->invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
            'number' => 'INV-2025-000001',
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@test.com',
            'customer_phone' => '+60987654321',
            'customer_address' => '456 Customer Avenue',
            'status' => 'SENT',
            'tax_rate' => 6.0,
            'discount_amount' => 0,
            'created_at' => now(),
            'due_date' => now()->addDays(30),
        ]);

        // Add invoice items
        InvoiceItem::factory()->create([
            'invoice_id' => $this->invoice->id,
            'description' => 'Professional Services',
            'quantity' => 1,
            'unit_price' => 1000.00,
            'notes' => 'Consulting services for Q1 2025',
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $this->invoice->id,
            'description' => 'Software License',
            'quantity' => 2,
            'unit_price' => 250.00,
            'notes' => 'Annual software licenses',
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_generate_pdf_with_default_settings()
    {
        $renderer = app(InvoicePdfRenderer::class);

        $pdfContent = $renderer->generate($this->invoice);

        $this->assertNotEmpty($pdfContent);
        $this->assertTrue(str_starts_with($pdfContent, '%PDF'));
        $this->assertStringContainsString('PDF', $pdfContent);
    }

    /** @test */
    public function pdf_download_route_returns_correct_response()
    {
        $response = $this->get(route('invoices.pdf', $this->invoice));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', function ($header) {
            return str_contains($header, 'attachment') &&
                   str_contains($header, 'Invoice-INV-2025-000001.pdf');
        });
    }

    /** @test */
    public function pdf_preview_route_returns_correct_response()
    {
        $response = $this->get(route('invoices.pdf.preview', $this->invoice));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', function ($header) {
            return str_contains($header, 'inline') &&
                   str_contains($header, 'Invoice-INV-2025-000001.pdf');
        });
    }

    /** @test */
    public function pdf_contains_invoice_data()
    {
        $renderer = app(InvoicePdfRenderer::class);

        // We can't easily parse PDF content, but we can test that the renderer
        // receives the correct data by checking the view compilation
        $html = view('pdf.invoice', [
            'invoice' => $this->invoice->load(['company', 'items', 'createdBy', 'paymentRecords']),
            'palette' => [
                'background_color' => '#ffffff',
                'accent_color' => '#1d4ed8',
                'text_color' => '#111827',
            ],
            'sections' => [
                'show_company_logo' => true,
                'show_payment_instructions' => true,
                'show_terms_conditions' => true,
                'show_signatures' => false,
            ],
            'currencyHelper' => function($amount) { return 'RM ' . number_format($amount, 2); },
            'dateHelper' => function($date) { return $date->format('d M, Y'); },
        ])->render();

        // Check that the rendered HTML contains expected invoice data
        $this->assertStringContainsString('INV-2025-000001', $html);
        $this->assertStringContainsString('Test Customer', $html);
        $this->assertStringContainsString('customer@test.com', $html);
        $this->assertStringContainsString('Professional Services', $html);
        $this->assertStringContainsString('Software License', $html);
        $this->assertStringContainsString('Test Company Ltd', $html);
    }

    /** @test */
    public function pdf_uses_custom_color_palette()
    {
        // Set custom appearance settings
        $settingsService = app(InvoiceSettingsService::class);
        $settingsService->updateSettings([
            'appearance' => [
                'accent_color' => '#ff6b35',
                'heading_color' => '#2d3748',
                'text_color' => '#1a202c',
                'background_color' => '#f7fafc',
            ]
        ], $this->company->id);

        $renderer = app(InvoicePdfRenderer::class);

        // Generate PDF to test custom colors are applied
        $pdfContent = $renderer->generate($this->invoice);

        $this->assertNotEmpty($pdfContent);
        $this->assertTrue(str_starts_with($pdfContent, '%PDF'));

        // Test that custom colors are used in the view
        $html = view('pdf.invoice', [
            'invoice' => $this->invoice->load(['company', 'items', 'createdBy', 'paymentRecords']),
            'palette' => [
                'accent_color' => '#ff6b35',
                'heading_color' => '#2d3748',
                'text_color' => '#1a202c',
                'background_color' => '#f7fafc',
            ],
            'sections' => [
                'show_company_logo' => true,
                'show_payment_instructions' => true,
                'show_terms_conditions' => true,
                'show_signatures' => false,
            ],
            'currencyHelper' => function($amount) { return 'RM ' . number_format($amount, 2); },
            'dateHelper' => function($date) { return $date->format('d M, Y'); },
        ])->render();

        // Check that custom colors are applied in CSS variables
        $this->assertStringContainsString('--accent-color: #ff6b35', $html);
        $this->assertStringContainsString('--heading-color: #2d3748', $html);
        $this->assertStringContainsString('--text-color: #1a202c', $html);
        $this->assertStringContainsString('--background-color: #f7fafc', $html);
    }

    /** @test */
    public function pdf_shows_draft_watermark_for_draft_invoices()
    {
        $this->invoice->update(['status' => 'DRAFT']);

        $html = view('pdf.invoice', [
            'invoice' => $this->invoice->load(['company', 'items', 'createdBy', 'paymentRecords']),
            'palette' => ['accent_color' => '#1d4ed8'],
            'sections' => ['show_company_logo' => true],
            'currencyHelper' => function($amount) { return 'RM ' . number_format($amount, 2); },
            'dateHelper' => function($date) { return $date->format('d M, Y'); },
        ])->render();

        $this->assertStringContainsString('watermark draft', $html);
        $this->assertStringContainsString('DRAFT', $html);
    }

    /** @test */
    public function pdf_shows_overdue_watermark_for_overdue_invoices()
    {
        $this->invoice->update(['status' => 'OVERDUE']);

        $html = view('pdf.invoice', [
            'invoice' => $this->invoice->load(['company', 'items', 'createdBy', 'paymentRecords']),
            'palette' => ['accent_color' => '#1d4ed8'],
            'sections' => ['show_company_logo' => true],
            'currencyHelper' => function($amount) { return 'RM ' . number_format($amount, 2); },
            'dateHelper' => function($date) { return $date->format('d M, Y'); },
        ])->render();

        $this->assertStringContainsString('watermark overdue', $html);
        $this->assertStringContainsString('OVERDUE', $html);
    }

    /** @test */
    public function pdf_calculates_totals_correctly()
    {
        $renderer = app(InvoicePdfRenderer::class);
        $totals = $renderer->calculateTotals($this->invoice);

        $expectedSubtotal = 1000.00 + (2 * 250.00); // 1500.00
        $expectedTax = $expectedSubtotal * (6.0 / 100); // 90.00
        $expectedTotal = $expectedSubtotal + $expectedTax; // 1590.00

        $this->assertEquals($expectedSubtotal, $totals['subtotal']);
        $this->assertEquals(0, $totals['discount']);
        $this->assertEquals($expectedSubtotal, $totals['subtotal_after_discount']);
        $this->assertEquals($expectedTax, $totals['tax']);
        $this->assertEquals($expectedTotal, $totals['total']);
        $this->assertEquals(0, $totals['total_paid']);
        $this->assertEquals($expectedTotal, $totals['balance']);
    }

    /** @test */
    public function unauthorized_user_cannot_access_pdf()
    {
        // Create another company and user
        $otherCompany = Company::factory()->create();
        $otherUser = User::factory()->create(['company_id' => $otherCompany->id]);

        $this->actingAs($otherUser);

        $response = $this->get(route('invoices.pdf', $this->invoice));

        $response->assertStatus(403);
    }

    /** @test */
    public function pdf_generation_handles_missing_data_gracefully()
    {
        // Create invoice with minimal data
        $minimalInvoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
            'number' => 'INV-2025-000002',
            'customer_name' => 'Minimal Customer',
            'customer_email' => null,
            'customer_phone' => null,
            'customer_address' => null,
            'status' => 'DRAFT',
        ]);

        $renderer = app(InvoicePdfRenderer::class);

        $pdfContent = $renderer->generate($minimalInvoice);

        $this->assertNotEmpty($pdfContent);
        $this->assertTrue(str_starts_with($pdfContent, '%PDF'));
    }

    /** @test */
    public function pdf_respects_section_visibility_settings()
    {
        $html = view('pdf.invoice', [
            'invoice' => $this->invoice->load(['company', 'items', 'createdBy', 'paymentRecords']),
            'palette' => ['accent_color' => '#1d4ed8'],
            'sections' => [
                'show_company_logo' => false,
                'show_payment_instructions' => false,
                'show_terms_conditions' => false,
                'show_signatures' => true,
            ],
            'currencyHelper' => function($amount) { return 'RM ' . number_format($amount, 2); },
            'dateHelper' => function($date) { return $date->format('d M, Y'); },
        ])->render();

        // Payment instructions should not be shown
        $this->assertStringNotContainsString('Payment Instructions', $html);

        // Signature section should be shown
        $this->assertStringContainsString('signature-section', $html);
    }
}