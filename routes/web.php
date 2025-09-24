<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ServiceTemplateController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationPreferenceController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\SecurityController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Public Shared Proof Pack Routes (no auth required)
Route::get('shared/proof-pack/{token}', [\App\Http\Controllers\ProofController::class, 'sharedProofPack'])->name('proofs.shared-pack');
Route::get('shared/proof-pack/{token}/download', [\App\Http\Controllers\ProofController::class, 'downloadSharedProofPack'])->name('proofs.shared-pack.download');

Route::middleware('auth')->group(function () {
    // User Profile (replacing default Laravel Breeze profile routes)
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::patch('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Company Settings
    Route::get('/company', [CompanyController::class, 'show'])->name('company.show');
    Route::get('/company/edit', [CompanyController::class, 'edit'])->name('company.edit');
    Route::patch('/company', [CompanyController::class, 'update'])->name('company.update');
    
    // Settings Management
    Route::prefix('settings')->name('settings.')->group(function () {
        // Numbering Settings
        Route::get('numbering', [\App\Http\Controllers\NumberSequenceController::class, 'index'])->name('numbering.index');
        Route::get('numbering/{type}/edit', [\App\Http\Controllers\NumberSequenceController::class, 'edit'])->name('numbering.edit');
        Route::patch('numbering/{type}', [\App\Http\Controllers\NumberSequenceController::class, 'update'])->name('numbering.update');
        Route::post('numbering/{type}/reset', [\App\Http\Controllers\NumberSequenceController::class, 'reset'])->name('numbering.reset');
        Route::post('numbering/preview', [\App\Http\Controllers\NumberSequenceController::class, 'preview'])->name('numbering.preview');
        Route::get('numbering/{type}/statistics', [\App\Http\Controllers\NumberSequenceController::class, 'statistics'])->name('numbering.statistics');
        Route::post('numbering/bulk-update', [\App\Http\Controllers\NumberSequenceController::class, 'bulkUpdate'])->name('numbering.bulk-update');
        Route::post('numbering/reset-defaults', [\App\Http\Controllers\NumberSequenceController::class, 'resetToDefaults'])->name('numbering.reset-defaults');
        Route::get('numbering/export', [\App\Http\Controllers\NumberSequenceController::class, 'export'])->name('numbering.export');
        Route::post('numbering/import', [\App\Http\Controllers\NumberSequenceController::class, 'import'])->name('numbering.import');
        
        // Document Settings
        Route::get('documents', [\App\Http\Controllers\DocumentSettingsController::class, 'index'])->name('documents.index');
        Route::patch('documents', [\App\Http\Controllers\DocumentSettingsController::class, 'update'])->name('documents.update');
        Route::get('documents/bank-accounts', [\App\Http\Controllers\DocumentSettingsController::class, 'bankAccounts'])->name('documents.bank-accounts');
        Route::patch('documents/bank-accounts', [\App\Http\Controllers\DocumentSettingsController::class, 'updateBankAccounts'])->name('documents.update-bank-accounts');
        Route::get('documents/custom-fields', [\App\Http\Controllers\DocumentSettingsController::class, 'customFields'])->name('documents.custom-fields');
        Route::patch('documents/custom-fields', [\App\Http\Controllers\DocumentSettingsController::class, 'updateCustomFields'])->name('documents.update-custom-fields');
        Route::get('documents/export', [\App\Http\Controllers\DocumentSettingsController::class, 'export'])->name('documents.export');
        Route::post('documents/import', [\App\Http\Controllers\DocumentSettingsController::class, 'import'])->name('documents.import');
        
        // System Settings
        Route::get('system', [\App\Http\Controllers\SystemSettingsController::class, 'index'])->name('system.index');
        Route::patch('system', [\App\Http\Controllers\SystemSettingsController::class, 'update'])->name('system.update');
        Route::post('system/test-email', [\App\Http\Controllers\SystemSettingsController::class, 'testEmail'])->name('system.test-email');
        Route::post('system/clear-cache', [\App\Http\Controllers\SystemSettingsController::class, 'clearCache'])->name('system.clear-cache');
        Route::get('system/export', [\App\Http\Controllers\SystemSettingsController::class, 'export'])->name('system.export');
        Route::post('system/import', [\App\Http\Controllers\SystemSettingsController::class, 'import'])->name('system.import');
        Route::post('system/reset-defaults', [\App\Http\Controllers\SystemSettingsController::class, 'resetToDefaults'])->name('system.reset-defaults');
    });
    
    // User Management (for administrators)
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    
    // Team Management
    Route::resource('teams', TeamController::class);
    
    // Team Member Assignment
    Route::get('teams/{team}/members', [TeamController::class, 'members'])->name('teams.members');
    Route::post('teams/{team}/assign-members', [TeamController::class, 'assignMembers'])->name('teams.assign-members');
    Route::delete('teams/{team}/remove-member/{user}', [TeamController::class, 'removeMember'])->name('teams.remove-member');
    
    // Team Settings
    Route::get('teams/{team}/settings', [TeamController::class, 'settings'])->name('teams.settings');
    Route::patch('teams/{team}/settings', [TeamController::class, 'updateSettings'])->name('teams.update-settings');
    
    // Organization Hierarchy
    Route::get('organization', [OrganizationController::class, 'index'])->name('organization.index');
    Route::get('organization/chart', [OrganizationController::class, 'chart'])->name('organization.chart');
    
    // Lead Management (CRM-Lite)
    Route::resource('leads', LeadController::class);
    Route::get('leads-kanban', [LeadController::class, 'kanban'])->name('leads.kanban');
    Route::patch('leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.update-status');
    Route::get('leads/{lead}/convert', [QuotationController::class, 'createFromLead'])->name('leads.convert');

    // Assessment Management
    Route::resource('assessments', AssessmentController::class);
    Route::post('assessments/{assessment}/start', [AssessmentController::class, 'start'])->name('assessments.start');
    Route::post('assessments/{assessment}/complete', [AssessmentController::class, 'complete'])->name('assessments.complete');
    Route::post('assessments/{assessment}/cancel', [AssessmentController::class, 'cancel'])->name('assessments.cancel');
    Route::post('assessments/{assessment}/reschedule', [AssessmentController::class, 'reschedule'])->name('assessments.reschedule');
    Route::post('assessments/{assessment}/upload-photos', [AssessmentController::class, 'uploadPhotos'])->name('assessments.upload-photos');
    Route::get('assessments/{assessment}/pdf', [AssessmentController::class, 'downloadPDF'])->name('assessments.pdf');
    Route::get('assessments/{assessment}/preview', [AssessmentController::class, 'previewPDF'])->name('assessments.preview');
    Route::get('assessments/{assessment}/analytics', [AssessmentController::class, 'analytics'])->name('assessments.analytics');
    Route::get('assessments/{assessment}/convert', [QuotationController::class, 'createFromAssessment'])->name('assessments.convert');
    
    // Quotation Management
    Route::resource('quotations', QuotationController::class);

    // Enhanced Quotation Builders
    Route::get('quotations/create/products', [QuotationController::class, 'createProduct'])->name('quotations.create.products');
    Route::get('quotations/create/services', [QuotationController::class, 'createService'])->name('quotations.create.services');

    Route::post('quotations/{quotation}/mark-sent', [QuotationController::class, 'markAsSent'])->name('quotations.mark-sent');
    Route::post('quotations/{quotation}/mark-accepted', [QuotationController::class, 'markAsAccepted'])->name('quotations.mark-accepted');
    Route::post('quotations/{quotation}/mark-rejected', [QuotationController::class, 'markAsRejected'])->name('quotations.mark-rejected');
    
    // Quotation PDF Generation
    Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'downloadPDF'])->name('quotations.pdf');
    Route::get('quotations/{quotation}/preview', [QuotationController::class, 'previewPDF'])->name('quotations.preview');
    Route::get('quotations-pricing-items', [QuotationController::class, 'getPricingItems'])->name('quotations.pricing-items');
    Route::post('quotations/get-segment-pricing', [QuotationController::class, 'getSegmentPricing'])->name('quotations.get-segment-pricing');
    
    // Invoice Management
    Route::resource('invoices', InvoiceController::class);

    // Enhanced Invoice Builders
    Route::get('invoices/create/products', [InvoiceController::class, 'createProduct'])->name('invoices.create.products');
    Route::get('invoices/create/services', [InvoiceController::class, 'createService'])->name('invoices.create.services');

    Route::post('invoices/{invoice}/mark-sent', [InvoiceController::class, 'markAsSent'])->name('invoices.mark-sent');
    Route::get('invoices/{invoice}/payment', [InvoiceController::class, 'showPaymentForm'])->name('invoices.payment-form');
    Route::post('invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('invoices.record-payment');
    Route::get('quotations/{quotation}/convert', [InvoiceController::class, 'createFromQuotation'])->name('quotations.convert');
    
    // Invoice PDF Generation
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPDF'])->name('invoices.pdf');
    Route::get('invoices/{invoice}/preview', [InvoiceController::class, 'previewPDF'])->name('invoices.preview');
    
    // Service Template Management
    Route::resource('service-templates', ServiceTemplateController::class);
    Route::post('service-templates/{serviceTemplate}/duplicate', [ServiceTemplateController::class, 'duplicate'])->name('service-templates.duplicate');
    Route::patch('service-templates/{serviceTemplate}/toggle-status', [ServiceTemplateController::class, 'toggleStatus'])->name('service-templates.toggle-status');
    Route::post('service-templates/{serviceTemplate}/convert', [ServiceTemplateController::class, 'convertToQuotation'])->name('service-templates.convert');
    
    // Pricing Book Management
    Route::resource('pricing', PricingController::class);
    Route::post('pricing/{pricing}/duplicate', [PricingController::class, 'duplicate'])->name('pricing.duplicate');
    Route::patch('pricing/{pricing}/toggle-status', [PricingController::class, 'toggleStatus'])->name('pricing.toggle-status');
    Route::get('pricing-search', [PricingController::class, 'search'])->name('pricing.search');
    Route::get('pricing-export', [PricingController::class, 'export'])->name('pricing.export');
    Route::get('pricing-popular', [PricingController::class, 'popular'])->name('pricing.popular');

    // Bulk Import/Export Routes
    Route::get('pricing/import', [PricingController::class, 'import'])->name('pricing.import');
    Route::post('pricing/process-import', [PricingController::class, 'processImport'])->name('pricing.process-import');
    Route::get('pricing/download-template', [PricingController::class, 'downloadTemplate'])->name('pricing.download-template');
    
    // Tier Pricing Management
    Route::get('pricing/{pricing}/tiers', [PricingController::class, 'manageTiers'])->name('pricing.manage-tiers');
    Route::post('pricing/{pricing}/tiers', [PricingController::class, 'storeTier'])->name('pricing.store-tier');
    Route::patch('pricing/{pricing}/tiers/{tier}', [PricingController::class, 'updateTier'])->name('pricing.update-tier');
    Route::delete('pricing/{pricing}/tiers/{tier}', [PricingController::class, 'destroyTier'])->name('pricing.destroy-tier');
    Route::post('pricing/{pricing}/generate-suggested-tiers', [PricingController::class, 'generateSuggestedTiers'])->name('pricing.generate-suggested-tiers');
    Route::post('pricing/{pricing}/bulk-create-tiers', [PricingController::class, 'bulkCreateTiers'])->name('pricing.bulk-create-tiers');
    Route::post('pricing/get-segment-pricing', [PricingController::class, 'getSegmentPricing'])->name('pricing.get-segment-pricing');
    
    // Customer Segment Management
    Route::get('pricing/segments', [PricingController::class, 'segments'])->name('pricing.segments');
    Route::post('pricing/segments', [PricingController::class, 'storeSegment'])->name('pricing.store-segment');
    Route::patch('pricing/segments/{segment}', [PricingController::class, 'updateSegment'])->name('pricing.update-segment');
    Route::patch('pricing/segments/{segment}/toggle', [PricingController::class, 'toggleSegment'])->name('pricing.toggle-segment');
    
    // Reports & Analytics
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/builder', [ReportController::class, 'builder'])->name('reports.builder');
    Route::post('reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::post('reports/save-template', [ReportController::class, 'saveTemplate'])->name('reports.save-template');
    Route::get('reports/template/{templateId}', [ReportController::class, 'loadTemplate'])->name('reports.load-template');
    
    // Notification Preferences
    Route::get('notifications/preferences', [NotificationPreferenceController::class, 'index'])->name('notifications.preferences.index');
    Route::post('notifications/preferences/update', [NotificationPreferenceController::class, 'update'])->name('notifications.preferences.update');
    Route::post('notifications/preferences/toggle', [NotificationPreferenceController::class, 'toggle'])->name('notifications.preferences.toggle');
    Route::post('notifications/preferences/bulk', [NotificationPreferenceController::class, 'bulkUpdate'])->name('notifications.preferences.bulk');
    Route::post('notifications/preferences/reset', [NotificationPreferenceController::class, 'resetToDefaults'])->name('notifications.preferences.reset');
    
    // Webhook Management
    Route::resource('webhook-endpoints', \App\Http\Controllers\WebhookEndpointController::class);
    Route::post('webhook-endpoints/{webhook_endpoint}/test', [\App\Http\Controllers\WebhookEndpointController::class, 'test'])->name('webhook-endpoints.test');
    Route::post('webhook-endpoints/{webhook_endpoint}/toggle-status', [\App\Http\Controllers\WebhookEndpointController::class, 'toggleStatus'])->name('webhook-endpoints.toggle-status');
    Route::post('webhook-endpoints/{webhook_endpoint}/regenerate-secret', [\App\Http\Controllers\WebhookEndpointController::class, 'regenerateSecret'])->name('webhook-endpoints.regenerate-secret');
    Route::get('webhook-endpoints/{webhook_endpoint}/deliveries', [\App\Http\Controllers\WebhookEndpointController::class, 'deliveries'])->name('webhook-endpoints.deliveries');
    Route::post('webhook-endpoints/{webhook_endpoint}/retry-failed', [\App\Http\Controllers\WebhookEndpointController::class, 'retryFailed'])->name('webhook-endpoints.retry-failed');
    
    // Search & Filters
    Route::get('search', [\App\Http\Controllers\SearchController::class, 'index'])->name('search.index');
    Route::post('search/global', [\App\Http\Controllers\SearchController::class, 'globalSearch'])->name('search.global');
    Route::post('search/{type}', [\App\Http\Controllers\SearchController::class, 'searchEntity'])->name('search.entity');
    Route::get('search/advanced/{type}', [\App\Http\Controllers\SearchController::class, 'advanced'])->name('search.advanced');
    Route::post('search/export', [\App\Http\Controllers\SearchController::class, 'export'])->name('search.export');
    Route::get('search/suggestions', [\App\Http\Controllers\SearchController::class, 'suggestions'])->name('search.suggestions');
    Route::get('search/recent', [\App\Http\Controllers\SearchController::class, 'recentSearches'])->name('search.recent');
    Route::delete('search/recent', [\App\Http\Controllers\SearchController::class, 'clearRecentSearches'])->name('search.clear-recent');
    Route::get('search/filter-options/{type}', [\App\Http\Controllers\SearchController::class, 'filterOptions'])->name('search.filter-options');
    Route::get('search/analytics', [\App\Http\Controllers\SearchController::class, 'analytics'])->name('search.analytics');
    Route::post('search/save', [\App\Http\Controllers\SearchController::class, 'saveSearch'])->name('search.save');
    Route::get('search/saved', [\App\Http\Controllers\SearchController::class, 'savedSearches'])->name('search.saved');
    Route::delete('search/saved', [\App\Http\Controllers\SearchController::class, 'deleteSavedSearch'])->name('search.delete-saved');

    // Proof Engine Management
    Route::resource('proofs', \App\Http\Controllers\ProofController::class)->parameters(['proofs' => 'uuid']);
    Route::post('proofs/{uuid}/duplicate', [\App\Http\Controllers\ProofController::class, 'duplicate'])->name('proofs.duplicate');
    Route::post('proofs/{uuid}/toggle-status', [\App\Http\Controllers\ProofController::class, 'toggleStatus'])->name('proofs.toggle-status');
    Route::post('proofs/{uuid}/toggle-featured', [\App\Http\Controllers\ProofController::class, 'toggleFeatured'])->name('proofs.toggle-featured');
    Route::post('proofs/{uuid}/upload-assets', [\App\Http\Controllers\ProofController::class, 'uploadAssets'])->middleware('validate-file-upload')->name('proofs.upload-assets');
    Route::get('proofs/analytics/data', [\App\Http\Controllers\ProofController::class, 'analytics'])->name('proofs.analytics');
    Route::get('proofs/scope/get', [\App\Http\Controllers\ProofController::class, 'getForScope'])->name('proofs.get-for-scope');
    
    // Proof Pack PDF Generation Routes
    Route::get('proofs/proof-pack/form', [\App\Http\Controllers\ProofController::class, 'proofPackForm'])->name('proofs.proof-pack.form');
    Route::post('proofs/proof-pack/generate', [\App\Http\Controllers\ProofController::class, 'generateProofPack'])->name('proofs.proof-pack.generate');
    Route::post('proofs/proof-pack/preview', [\App\Http\Controllers\ProofController::class, 'previewProofPack'])->name('proofs.proof-pack.preview');
    
    // Secure Proof Pack Sharing Routes (Phase 6 Deferred)
    Route::post('proofs/generate-share-url', [\App\Http\Controllers\ProofController::class, 'generateSecureShareUrl'])->name('proofs.generate-share-url');
    
    // Proof Pack Email Delivery Routes (Phase 6 Deferred)
    Route::post('proofs/email-proof-pack', [\App\Http\Controllers\ProofController::class, 'emailProofPack'])->name('proofs.email-proof-pack');
    Route::post('proofs/bulk-email-proof-pack', [\App\Http\Controllers\ProofController::class, 'bulkEmailProofPack'])->name('proofs.bulk-email-proof-pack');
    
    // Proof Pack Version Control Routes (Phase 6 Deferred)
    Route::post('proofs/versions/create', [\App\Http\Controllers\ProofController::class, 'createProofPackVersion'])->name('proofs.versions.create');
    Route::put('proofs/versions/{version_id}', [\App\Http\Controllers\ProofController::class, 'updateProofPackVersion'])->name('proofs.versions.update');
    Route::get('proofs/versions/{version_id}', [\App\Http\Controllers\ProofController::class, 'getProofPackVersion'])->name('proofs.versions.get');
    Route::get('proofs/versions', [\App\Http\Controllers\ProofController::class, 'listProofPackVersions'])->name('proofs.versions.list');
    Route::delete('proofs/versions/{version_id}', [\App\Http\Controllers\ProofController::class, 'deleteProofPackVersion'])->name('proofs.versions.delete');
    Route::post('proofs/versions/compare', [\App\Http\Controllers\ProofController::class, 'compareProofPackVersions'])->name('proofs.versions.compare');
    Route::get('proofs/versions/{version_id}/pdf', [\App\Http\Controllers\ProofController::class, 'generateVersionedProofPackPDF'])->name('proofs.versions.pdf');
    
    // File upload routes with validation middleware
    Route::middleware('validate-file-upload')->group(function () {
        Route::post('proofs/store-with-files', [\App\Http\Controllers\ProofController::class, 'store'])->name('proofs.store-with-files');
    });

    // Testimonial Management (Phase 4 Proof Engine)
    Route::resource('testimonials', \App\Http\Controllers\TestimonialController::class);
    Route::post('testimonials/{testimonial}/approve', [\App\Http\Controllers\TestimonialController::class, 'approve'])->name('testimonials.approve');
    Route::post('testimonials/{testimonial}/reject', [\App\Http\Controllers\TestimonialController::class, 'reject'])->name('testimonials.reject');
    Route::post('testimonials/{testimonial}/toggle-featured', [\App\Http\Controllers\TestimonialController::class, 'toggleFeatured'])->name('testimonials.toggle-featured');
    Route::get('testimonials/{testimonial}/download-attachments', [\App\Http\Controllers\TestimonialController::class, 'downloadAttachments'])->name('testimonials.download-attachments');

    // Certification Management (Phase 4 Proof Engine) 
    Route::resource('certifications', \App\Http\Controllers\CertificationController::class);
    Route::post('certifications/{certification}/verify', [\App\Http\Controllers\CertificationController::class, 'verify'])->name('certifications.verify');
    Route::post('certifications/{certification}/revoke', [\App\Http\Controllers\CertificationController::class, 'revoke'])->name('certifications.revoke');
    Route::post('certifications/{certification}/suspend', [\App\Http\Controllers\CertificationController::class, 'suspend'])->name('certifications.suspend');
    Route::post('certifications/{certification}/reactivate', [\App\Http\Controllers\CertificationController::class, 'reactivate'])->name('certifications.reactivate');
    Route::post('certifications/{certification}/renew', [\App\Http\Controllers\CertificationController::class, 'renew'])->name('certifications.renew');
    Route::post('certifications/{certification}/toggle-featured', [\App\Http\Controllers\CertificationController::class, 'toggleFeatured'])->name('certifications.toggle-featured');
    Route::post('certifications/{certification}/renewal-reminder', [\App\Http\Controllers\CertificationController::class, 'sendRenewalReminder'])->name('certifications.renewal-reminder');
    Route::get('certifications/{certification}/download', [\App\Http\Controllers\CertificationController::class, 'download'])->name('certifications.download');

    // Audit & Security Management
    Route::get('audit', [\App\Http\Controllers\AuditController::class, 'index'])->name('audit.index');
    Route::get('audit/dashboard', [\App\Http\Controllers\AuditController::class, 'dashboard'])->name('audit.dashboard');
    Route::get('audit/{auditLog}', [\App\Http\Controllers\AuditController::class, 'show'])->name('audit.show');
    Route::post('audit/model', [\App\Http\Controllers\AuditController::class, 'model'])->name('audit.model');
    Route::get('audit/export', [\App\Http\Controllers\AuditController::class, 'export'])->name('audit.export');
    Route::post('audit/compare', [\App\Http\Controllers\AuditController::class, 'compare'])->name('audit.compare');
    Route::post('audit/cleanup', [\App\Http\Controllers\AuditController::class, 'cleanup'])->name('audit.cleanup');

    // Two-Factor Authentication Management
    Route::prefix('two-factor')->name('two-factor.')->group(function () {
        Route::get('setup', [TwoFactorController::class, 'setup'])->name('setup');
        Route::post('enable', [TwoFactorController::class, 'enable'])->name('enable');
        Route::post('disable', [TwoFactorController::class, 'disable'])->name('disable');
        Route::post('regenerate-recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('regenerate-recovery-codes');
        Route::get('download-recovery-codes', [TwoFactorController::class, 'downloadRecoveryCodes'])->name('download-recovery-codes');
        Route::post('check-required', [TwoFactorController::class, 'checkRequired'])->name('check-required');
    });

    // Security Monitoring & Management
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('dashboard', [SecurityController::class, 'dashboard'])->name('dashboard');
        Route::get('events', [SecurityController::class, 'events'])->name('events');
        Route::get('alerts', [SecurityController::class, 'alerts'])->name('alerts');
        Route::get('analytics-data', [SecurityController::class, 'analyticsData'])->name('analytics-data');
        Route::post('dismiss-alert', [SecurityController::class, 'dismissAlert'])->name('dismiss-alert');
        Route::post('block-ip', [SecurityController::class, 'blockIP'])->name('block-ip');
        Route::post('unblock-ip', [SecurityController::class, 'unblockIP'])->name('unblock-ip');
    });
});

// Two-Factor Authentication Routes (outside auth middleware for login flow)
Route::prefix('two-factor')->name('two-factor.')->group(function () {
    Route::get('verify', [TwoFactorController::class, 'verify'])->name('verify');
    Route::post('verify', [TwoFactorController::class, 'verifyCode'])->name('verify.submit');
});

// API Routes for Enhanced Builders
Route::prefix('api')->middleware('auth')->group(function () {
    // Client/Lead Search API
    Route::get('clients/search', [LeadController::class, 'searchClients'])->name('api.clients.search');
    Route::get('leads/recent-clients', [LeadController::class, 'getRecentClients'])->name('api.leads.recent-clients');

    // Product Search & Pricing API
    Route::get('pricing-items/search', [PricingController::class, 'searchApi'])->name('api.pricing-items.search');
    Route::get('pricing-items/{item}/segment-pricing', [PricingController::class, 'getItemSegmentPricing'])->name('api.pricing-items.segment-pricing');
    Route::get('pricing-items/{item}/tier-pricing', [PricingController::class, 'getItemTierPricing'])->name('api.pricing-items.tier-pricing');

    // Service Template API
    Route::get('service-templates/search', [ServiceTemplateController::class, 'searchApi'])->name('api.service-templates.search');
    Route::get('service-templates/{template}/data', [ServiceTemplateController::class, 'getTemplateData'])->name('api.service-templates.data');

    // Customer Segment Pricing API
    Route::post('quotations/calculate-segment-pricing', [QuotationController::class, 'calculateSegmentPricing'])->name('api.quotations.calculate-segment-pricing');
    Route::post('invoices/calculate-segment-pricing', [InvoiceController::class, 'calculateSegmentPricing'])->name('api.invoices.calculate-segment-pricing');

    // Invoice Builder API (New enhanced features)
    Route::post('invoices/calculate-pricing', [InvoiceController::class, 'calculatePricing'])->name('api.invoices.calculate-pricing');
    Route::get('invoices/search-clients', [InvoiceController::class, 'searchClients'])->name('api.invoices.search-clients');
    Route::get('invoices/load-service-template', [InvoiceController::class, 'loadServiceTemplate'])->name('api.invoices.load-service-template');
});

require __DIR__.'/auth.php';
