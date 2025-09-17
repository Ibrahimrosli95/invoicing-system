<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\Proof;
use App\Models\ProofAsset;
use App\Models\User;
use App\Models\Company;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Services\PDFService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class ProofPDFIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $user;
    protected PDFService $pdfService;
    protected Quotation $quotation;
    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        // Create company and user
        $this->company = Company::factory()->create([
            'name' => 'Test Company Ltd',
            'settings' => [
                'address' => '123 Test Street, Test City',
                'phone' => '+60123456789',
                'email' => 'contact@testcompany.com',
                'website' => 'www.testcompany.com',
            ]
        ]);

        $this->createRolesIfNeeded();
        $this->user = User::factory()->create(['company_id' => $this->company->id]);
        $this->user->assignRole('sales_manager');

        $this->pdfService = new PDFService();

        // Create test quotation and invoice
        $this->quotation = Quotation::factory()->create([
            'company_id' => $this->company->id,
            'number' => 'QTN-2025-000001',
            'customer_name' => 'John Smith',
            'customer_email' => 'john.smith@example.com',
            'status' => 'SENT',
            'total' => 15000.00,
        ]);

        $this->invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'number' => 'INV-2025-000001',
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane.doe@example.com',
            'status' => 'SENT',
            'total' => 25000.00,
        ]);

        $this->actingAs($this->user);
    }

    public function test_quotation_pdf_includes_proof_section()
    {
        // Create proofs for quotation
        $testimonial = $this->createTestimonialProof();
        $certification = $this->createCertificationProof();
        $caseStudy = $this->createCaseStudyProof();

        // Generate quotation PDF
        $pdfContent = $this->pdfService->generateQuotationPDF($this->quotation);

        $this->assertNotEmpty($pdfContent);
        
        // Check that proof content appears in PDF
        $this->assertStringContainsString('Why Choose Us', $pdfContent);
        $this->assertStringContainsString($testimonial->title, $pdfContent);
        $this->assertStringContainsString($certification->title, $pdfContent);
        $this->assertStringContainsString($caseStudy->title, $pdfContent);
    }

    public function test_invoice_pdf_includes_credentials_section()
    {
        // Create professional proofs for invoice
        $certification = $this->createCertificationProof();
        $award = $this->createAwardProof();
        $partnership = $this->createPartnershipProof();

        // Generate invoice PDF
        $pdfContent = $this->pdfService->generateInvoicePDF($this->invoice);

        $this->assertNotEmpty($pdfContent);
        
        // Check that credential proofs appear in PDF
        $this->assertStringContainsString('Our Credentials', $pdfContent);
        $this->assertStringContainsString($certification->title, $pdfContent);
        $this->assertStringContainsString($award->title, $pdfContent);
        $this->assertStringContainsString($partnership->title, $pdfContent);
    }

    public function test_proof_pack_pdf_generation()
    {
        // Create various proofs
        $testimonial = $this->createTestimonialProof();
        $certification = $this->createCertificationProof();
        $caseStudy = $this->createCaseStudyProof();
        $award = $this->createAwardProof();

        $proofs = [$testimonial, $certification, $caseStudy, $award];

        // Generate proof pack PDF
        $response = $this->post(route('proofs.generate-proof-pack'), [
            'title' => 'Complete Proof Pack',
            'description' => 'Comprehensive proof collection for client presentation',
            'proof_ids' => array_map(fn($p) => $p->id, $proofs),
            'include_cover_page' => true,
            'organize_by_category' => true,
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        $pdfContent = $response->getContent();
        $this->assertNotEmpty($pdfContent);

        // Verify PDF contains all proof titles
        foreach ($proofs as $proof) {
            $this->assertStringContainsString($proof->title, $pdfContent);
        }

        // Verify cover page elements
        $this->assertStringContainsString('Complete Proof Pack', $pdfContent);
        $this->assertStringContainsString($this->company->name, $pdfContent);
    }

    public function test_proof_assets_included_in_pdf()
    {
        $proof = $this->createTestimonialProof();
        
        // Add image asset to proof
        $imageFile = UploadedFile::fake()->image('testimonial.jpg', 800, 600);
        Storage::put('proof_assets/testimonial.jpg', $imageFile->getContent());

        ProofAsset::factory()->create([
            'proof_id' => $proof->id,
            'type' => 'image',
            'title' => 'Customer Photo',
            'file_name' => 'testimonial.jpg',
            'file_path' => 'proof_assets/testimonial.jpg',
            'thumbnail_path' => 'proof_assets/thumbnails/testimonial_thumb.jpg',
            'sort_order' => 1,
        ]);

        // Generate proof pack with assets
        $response = $this->post(route('proofs.generate-proof-pack'), [
            'title' => 'Testimonial Pack',
            'proof_ids' => [$proof->id],
            'include_assets' => true,
        ]);

        $response->assertStatus(200);
        $pdfContent = $response->getContent();

        // Verify asset is referenced in PDF
        $this->assertStringContainsString('Customer Photo', $pdfContent);
    }

    public function test_security_watermarks_in_pdf()
    {
        // Create restricted proof
        $proof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'case_study',
            'title' => 'Confidential Case Study',
            'status' => 'active',
            'metadata' => [
                'security_level' => 'restricted',
                'access_restrictions' => [
                    'watermarking_required' => true,
                ]
            ],
        ]);

        // Generate PDF with watermark
        $response = $this->post(route('proofs.generate-proof-pack'), [
            'title' => 'Restricted Content Pack',
            'proof_ids' => [$proof->id],
            'apply_watermarks' => true,
        ]);

        $response->assertStatus(200);
        $pdfContent = $response->getContent();

        // Check for watermark indicators in PDF
        $this->assertStringContainsString('CONFIDENTIAL', $pdfContent);
        $this->assertStringContainsString($this->company->name, $pdfContent);
    }

    public function test_proof_filtering_by_context()
    {
        // Create proofs with different display settings
        $quotationProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'testimonial',
            'show_in_quotation' => true,
            'show_in_invoice' => false,
            'status' => 'active',
        ]);

        $invoiceProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'certification',
            'show_in_quotation' => false,
            'show_in_invoice' => true,
            'status' => 'active',
        ]);

        $bothProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'case_study',
            'show_in_quotation' => true,
            'show_in_invoice' => true,
            'status' => 'active',
        ]);

        // Generate quotation PDF
        $quotationPdf = $this->pdfService->generateQuotationPDF($this->quotation);
        $this->assertStringContainsString($quotationProof->title, $quotationPdf);
        $this->assertStringNotContainsString($invoiceProof->title, $quotationPdf);
        $this->assertStringContainsString($bothProof->title, $quotationPdf);

        // Generate invoice PDF
        $invoicePdf = $this->pdfService->generateInvoicePDF($this->invoice);
        $this->assertStringNotContainsString($quotationProof->title, $invoicePdf);
        $this->assertStringContainsString($invoiceProof->title, $invoicePdf);
        $this->assertStringContainsString($bothProof->title, $invoicePdf);
    }

    public function test_proof_expiration_handling_in_pdf()
    {
        // Create expired proof
        $expiredProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'certification',
            'title' => 'Expired Certification',
            'status' => 'active',
            'expires_at' => now()->subDays(30),
        ]);

        // Create active proof
        $activeProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'testimonial',
            'title' => 'Current Testimonial',
            'status' => 'active',
            'expires_at' => now()->addDays(30),
        ]);

        // Generate proof pack - expired proofs should be excluded by default
        $response = $this->post(route('proofs.generate-proof-pack'), [
            'title' => 'Current Proofs Only',
            'proof_ids' => [$expiredProof->id, $activeProof->id],
            'exclude_expired' => true,
        ]);

        $response->assertStatus(200);
        $pdfContent = $response->getContent();

        $this->assertStringNotContainsString($expiredProof->title, $pdfContent);
        $this->assertStringContainsString($activeProof->title, $pdfContent);
    }

    public function test_multi_language_proof_pack_generation()
    {
        // Create proof with multi-language metadata
        $proof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'testimonial',
            'title' => 'Customer Testimonial',
            'status' => 'active',
            'metadata' => [
                'translations' => [
                    'en' => [
                        'title' => 'Excellent Service',
                        'description' => 'Outstanding quality and support',
                    ],
                    'ms' => [
                        'title' => 'Perkhidmatan Cemerlang',
                        'description' => 'Kualiti dan sokongan yang luar biasa',
                    ],
                ]
            ],
        ]);

        // Generate English version
        $response = $this->post(route('proofs.generate-proof-pack'), [
            'title' => 'Proof Pack',
            'proof_ids' => [$proof->id],
            'language' => 'en',
        ]);

        $response->assertStatus(200);
        $pdfContent = $response->getContent();
        $this->assertStringContainsString('Excellent Service', $pdfContent);

        // Generate Malay version
        $response = $this->post(route('proofs.generate-proof-pack'), [
            'title' => 'Pakej Bukti',
            'proof_ids' => [$proof->id],
            'language' => 'ms',
        ]);

        $response->assertStatus(200);
        $pdfContent = $response->getContent();
        $this->assertStringContainsString('Perkhidmatan Cemerlang', $pdfContent);
    }

    public function test_proof_analytics_integration_in_pdf()
    {
        $proof = $this->createTestimonialProof();

        // Add some view data
        $proof->views()->createMany([
            ['viewer_type' => 'customer', 'viewer_id' => null, 'created_at' => now()->subDays(1)],
            ['viewer_type' => 'customer', 'viewer_id' => null, 'created_at' => now()->subDays(2)],
            ['viewer_type' => 'user', 'viewer_id' => $this->user->id, 'created_at' => now()->subDays(3)],
        ]);

        $proof->update([
            'view_count' => 25,
            'conversion_impact' => 15.5,
        ]);

        // Generate proof pack with analytics
        $response = $this->post(route('proofs.generate-proof-pack'), [
            'title' => 'Analytics Proof Pack',
            'proof_ids' => [$proof->id],
            'include_analytics' => true,
        ]);

        $response->assertStatus(200);
        $pdfContent = $response->getContent();

        // Verify analytics data in PDF
        $this->assertStringContainsString('25 views', $pdfContent);
        $this->assertStringContainsString('15.5%', $pdfContent);
    }

    public function test_batch_proof_processing_for_large_collections()
    {
        // Create multiple proofs
        $proofs = collect(range(1, 15))->map(function ($i) {
            return Proof::factory()->create([
                'company_id' => $this->company->id,
                'type' => 'testimonial',
                'title' => "Testimonial #{$i}",
                'status' => 'active',
            ]);
        });

        // Generate large proof pack
        $response = $this->post(route('proofs.generate-proof-pack'), [
            'title' => 'Large Proof Collection',
            'proof_ids' => $proofs->pluck('id')->toArray(),
            'organize_by_category' => true,
            'include_cover_page' => true,
            'include_table_of_contents' => true,
        ]);

        $response->assertStatus(200);
        $pdfContent = $response->getContent();

        // Verify all proofs are included
        foreach ($proofs as $proof) {
            $this->assertStringContainsString($proof->title, $pdfContent);
        }

        // Verify organizational elements
        $this->assertStringContainsString('Table of Contents', $pdfContent);
        $this->assertStringContainsString('Large Proof Collection', $pdfContent);
    }

    protected function createTestimonialProof(): Proof
    {
        return Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'testimonial',
            'title' => 'Outstanding Service Quality',
            'description' => 'The team exceeded our expectations in every way.',
            'status' => 'active',
            'show_in_quotation' => true,
            'show_in_invoice' => false,
            'metadata' => [
                'customer_name' => 'John Smith',
                'project_value' => 50000,
                'rating' => 5,
            ],
        ]);
    }

    protected function createCertificationProof(): Proof
    {
        return Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'certification',
            'title' => 'ISO 9001:2015 Quality Management',
            'description' => 'Certified quality management system.',
            'status' => 'active',
            'show_in_quotation' => true,
            'show_in_invoice' => true,
            'metadata' => [
                'certification_body' => 'SGS Malaysia',
                'valid_until' => now()->addYears(3)->toDateString(),
                'certificate_number' => 'MY-QMS-001',
            ],
        ]);
    }

    protected function createCaseStudyProof(): Proof
    {
        return Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'case_study',
            'title' => 'Manufacturing Efficiency Improvement',
            'description' => '40% improvement in production efficiency for major client.',
            'status' => 'active',
            'show_in_quotation' => true,
            'show_in_invoice' => false,
            'metadata' => [
                'client_industry' => 'Manufacturing',
                'project_duration' => '6 months',
                'results' => '40% efficiency gain, 25% cost reduction',
            ],
        ]);
    }

    protected function createAwardProof(): Proof
    {
        return Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'award',
            'title' => 'Best Service Provider 2024',
            'description' => 'Industry recognition for excellence in service delivery.',
            'status' => 'active',
            'show_in_invoice' => true,
            'metadata' => [
                'award_body' => 'Malaysia Service Excellence Awards',
                'award_date' => '2024-11-15',
                'category' => 'Professional Services',
            ],
        ]);
    }

    protected function createPartnershipProof(): Proof
    {
        return Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'partnership',
            'title' => 'Microsoft Gold Partner',
            'description' => 'Certified Microsoft Gold Partner status.',
            'status' => 'active',
            'show_in_invoice' => true,
            'metadata' => [
                'partner_level' => 'Gold',
                'specializations' => ['Cloud Platform', 'Data Analytics'],
                'valid_until' => now()->addYears(1)->toDateString(),
            ],
        ]);
    }

    protected function createRolesIfNeeded(): void
    {
        $roles = ['sales_manager', 'sales_executive'];

        foreach ($roles as $roleName) {
            if (!Role::where('name', $roleName)->exists()) {
                Role::create(['name' => $roleName, 'guard_name' => 'web']);
            }
        }
    }
}