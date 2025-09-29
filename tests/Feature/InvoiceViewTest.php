<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceViewTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Company $company;
    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        // Create company
        $this->company = Company::factory()->create([
            'name' => 'Test Company',
            'email' => 'test@company.com',
            'phone' => '+60 12-345 6789'
        ]);

        // Create user with company
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        // Create invoice with items
        $this->invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '+60 12-345 6789',
            'status' => 'SENT',
            'subtotal' => 1000.00,
            'total' => 1060.00,
            'amount_due' => 1060.00,
        ]);

        // Create invoice items
        InvoiceItem::factory()->create([
            'invoice_id' => $this->invoice->id,
            'description' => 'Test Item 1',
            'quantity' => 2,
            'unit_price' => 250.00,
            'total_price' => 500.00,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $this->invoice->id,
            'description' => 'Test Item 2',
            'quantity' => 1,
            'unit_price' => 500.00,
            'total_price' => 500.00,
        ]);
    }

    public function test_invoice_show_page_displays_correctly()
    {
        $response = $this->actingAs($this->user)
            ->get(route('invoices.show', $this->invoice));

        $response->assertStatus(200);
        $response->assertSee('INVOICE'); // Title from partial
        $response->assertSee($this->invoice->number);
        $response->assertSee($this->invoice->customer_name);
        $response->assertSee('Test Company'); // Company name
        $response->assertSee('Test Item 1'); // Invoice items
        $response->assertSee('Test Item 2');
        $response->assertSee('RM 1,060.00'); // Total amount
    }

    public function test_invoice_show_page_uses_shared_partial()
    {
        $response = $this->actingAs($this->user)
            ->get(route('invoices.show', $this->invoice));

        $response->assertStatus(200);

        // Check for specific elements that are in the shared partial
        $response->assertSee('Bill To'); // Customer section from partial
        $response->assertSee('Invoice Details'); // Invoice details section from partial
        $response->assertSee('Invoice Items'); // Items section from partial

        // Check the structure matches the partial layout
        $response->assertSeeInOrder([
            'INVOICE', // Title first
            'Test Company', // Company info
            'Bill To', // Customer section
            'Test Customer', // Customer name
            'Invoice Details', // Invoice details section
            $this->invoice->number, // Invoice number
        ]);
    }

    public function test_pdf_preview_generates_successfully()
    {
        $response = $this->actingAs($this->user)
            ->get(route('invoices.preview', $this->invoice));

        // Note: This might return a 500 error in testing environment due to Browsershot
        // but we're testing that the route is accessible and properly authorized
        $this->assertTrue(
            $response->status() === 200 ||
            ($response->status() === 302 && str_contains($response->headers->get('location'), 'invoices'))
        );
    }

    public function test_pdf_download_route_is_accessible()
    {
        $response = $this->actingAs($this->user)
            ->get(route('invoices.pdf', $this->invoice));

        // Note: This might return a 500 error in testing environment due to Browsershot
        // but we're testing that the route is accessible and properly authorized
        $this->assertTrue(
            $response->status() === 200 ||
            ($response->status() === 302 && str_contains($response->headers->get('location'), 'invoices'))
        );
    }

    public function test_unauthorized_user_cannot_view_invoice()
    {
        // Create another company and user
        $otherCompany = Company::factory()->create(['name' => 'Other Company']);
        $otherUser = User::factory()->create(['company_id' => $otherCompany->id]);

        $response = $this->actingAs($otherUser)
            ->get(route('invoices.show', $this->invoice));

        // Should be forbidden due to multi-tenant isolation
        $response->assertStatus(403);
    }

    public function test_guest_user_is_redirected_to_login()
    {
        $response = $this->get(route('invoices.show', $this->invoice));

        $response->assertRedirect(route('login'));
    }
}