# Invoice & Quotation Creation PRD

## 1. Objective
Deliver modernised product/service builders for invoices (and, in the next iteration, quotations) that plug into the Pricing Book, Service Templates, client data, numbering, document settings, and signatures. Replace the current generic form with two tailored flows (Products vs. Services) that share a common UX and logic.

## 2. Key Outcomes
- Prepare an invoice in <= 3 minutes for the average sales user.
- Eliminate template-related validation errors (target: 0 per sprint).
- Capture source metadata on 100% of invoice items for analytics.
- Provide a reusable foundation so quotations can adopt the same UI/data contracts with minimal lift.

## 3. Users & Jobs

| Role | Primary Jobs | Notes |
|------|--------------|-------|
| Sales Executive/Coordinator | Draft product or service invoices/quotes, pull catalogue items/templates, send to clients | Power users; speed-focused |
| Finance Manager | Finalise invoices, ensure terms compliance, monitor payments | Needs defaults, numbering, bank info |
| Company Manager | Configure defaults (terms, numbering, signatures), monitor adoption | Needs unified reporting |

## 4. Scope

### In Scope
- New product and service invoice creation flows (UI + backend logic).
- Data model extensions: invoices.type, invoice_items.source_type/source_id/item_code.
- Support APIs for product search, service-template insertion, and client suggestions.
- Conversion of quotations into invoices using the new builders.
- Reusable helper methods to power the forthcoming quotation redesign.

### Out of Scope
- Payment recording changes or PDF redesigns (phase 2).
- Multi-currency enhancements (retain current behaviour).
- Inventory/stock adjustments or approval workflows.

## 5. Experience Overview
1. **Entry Points**  
   - /invoices/create/products and /invoices/create/services (menu buttons & "Convert to Invoice").
   - /invoices/create redirects to the product builder.
   - Quotation conversion auto-routes based on quotation.type.

2. **Shared Layout**  
   - Left: invoice canvas (company header, client block, items, totals, terms, signature).  
   - Right: utility sidebar (currency/date, templates, client search, defaults).  
   - Top sticky bar: invoice metadata (number preview, status, due date, team/assignee).

3. **Product Builder**  
   - "Add product" modal backed by Pricing Items search.  
   - Lines store source_type=pricing_item, source_id for analytics; manual lines allowed (source_type=manual).  
   - Discount/tax toggles replicate screenshot behaviour.  
   - Running totals panel with subtotal, discount, tax, adjustments.

4. **Service Builder**  
   - Template browser in sidebar with category/filter.  
   - Selecting injects grouped rows (section header optional).  
   - Lines store source_type=service_template_item; manual add-ons allowed.  
   - Summary shows estimated hours.

5. **Client Block**  
   - Search existing leads/quotations by name/email; selection pre-fills contact fields.  
   - Saving an invoice with a brand-new client automatically creates a Lead record.

6. **Defaults & Signatures**  
   - Terms, notes, payment instructions pulled from company.settings.document.  
   - Signature pad with stored company signature support.

7. **Validation Feedback**  
   - Inline errors; totals recalc in real time via Alpine store.  
   - Save disabled until mandatory fields valid.

## 6. Functional Requirements

1. Persist invoices.type (product/service); default to product.  
2. Enforce matching between quotation type and invoice builder; reject mismatched conversions.  
3. Extend invoice_items with source_type, source_id, item_code; maintain quotation_item_id.  
4. Provide product search API: GET /api/pricing-items/search?query=&category=&tag= returning id/name/SKU/unit/price/description.  
5. Provide service template API: GET /api/service-templates/{template} returning sections/items.  
6. Provide client suggestion API combining leads + recent quotations; create new lead automatically if a new client is saved.  
7. Number preview uses Invoice::generateNumber(); display read-only field.  
8. Totals recalculated server-side after persistence; front-end mirrors totals for UX.  
9. Introduce createProduct/createService controller actions; keep RESTful resource for other operations.  
10. Feature-flag old form as fallback (config('features.invoice_builder_v2')).

## 7. Non-Functional Requirements
- Page load =2s on cached network.
- Search APIs =500ms server time.
- PHP 8.3 compatibility ensured before deployment.
- No database locks from new migrations (add columns without dropping tables).
- Supports responsive layout (desktop-first design, workable on tablets).

## 8. Data Model Changes

`sql
ALTER TABLE invoices
    ADD COLUMN type VARCHAR(20) NOT NULL DEFAULT 'product';

ALTER TABLE invoice_items
    ADD COLUMN source_type VARCHAR(50) NULL,
    ADD COLUMN source_id BIGINT NULL,
    ADD COLUMN item_code VARCHAR(100) NULL;
`

- Update Invoice fillable, casts, constants (TYPE_PRODUCT, TYPE_SERVICE, getTypes() helper).  
- Update factories/fakers to seed 	ype.  
- Update InvoiceItem fillable/casts; add source() morph relation.
- Optional indexes: CREATE INDEX invoice_items_source_idx ON invoice_items (source_type, source_id);

## 9. Controller & Routes
- create() redirects to invoices.create.products.  
- createProduct()/createService() share uildInvoiceFormPayload() helper covering teams, assignees, segments, document defaults, next number, shortlist, etc.  
- store() delegates to storeProductInvoice()/storeServiceInvoice() via validated payload.  
- persistInvoice() handles shared persistence (with lead auto-create when client is new).  
- New routes:
  `php
  Route::get('invoices/create/products', [InvoiceController::class, 'createProduct'])->name('invoices.create.products');
  Route::get('invoices/create/services', [InvoiceController::class, 'createService'])->name('invoices.create.services');
  Route::post('invoices/store', [InvoiceController::class, 'store'])->name('invoices.store'); // retains resource name
  `
- Mirror strategy for quotations in follow-up iteration.

## 10. Front-End Requirements
- New Blade templates: invoices/create-product.blade.php, invoices/create-service.blade.php, shared partials (invoice-builder/items-table, invoice-builder/sidebar).  
- Alpine store invoiceBuilder managing items, client, totals; restful updates via fetch.  
- Modal components for product search & template preview.  
- Tailwind-based layout matching reference screenshot (canvas + sidebar).  
- Signature pad component reused from existing invoice show/edit.

## 11. Integration Points
- Pricing Book: PricingItem::forCompany()->active()->ordered() with search filters.  
- Service Templates: ServiceTemplate::forCompany()->with('sections.items').  
- Numbering: existing Invoice::generateNumber() (consider NumberSequence integration later).  
- Document settings & bank accounts: company.settings['document'] & ['bank_accounts'].  
- Leads: create new lead via Lead::create() when client not matched to existing lead/quotation.

## 12. Analytics & Logging
- source_type/source_id captured for every line item.  
- Existing webhook events (invoiceCreated, invoiceSent, invoicePaid, invoiceOverdue) to include new metadata.  
- Optionally log creation duration via request header.

## 13. Testing Strategy
- **Unit**: invoice type defaults, item morph relation, lead auto-create helper.  
- **Feature**: create product invoice (pricing item lines, totals), create service invoice (template lines), convert quotations, new client saved ? lead created, API auth & filters.  
- **Regression**: PDF generation, payment recording.  
- **Manual QA**: cross-browser UI verification, performance checks.

## 14. Rollout Plan
1. Merge migrations & backend scaffolding (feature flag default off).  
2. Implement product builder UI; QA.  
3. Implement service builder UI; QA.  
4. Enable feature flag on staging, gather feedback.  
5. Train users; enable in production after PHP upgrade.  
6. Remove fallback after two sprints when stable.  
7. Begin quotation UI rework using same building blocks.

## 15. Risks & Mitigations
- **PHP version mismatch**: coordinate DevOps to upgrade to PHP =8.3 before deployment.  
- **Large controller diff**: incremental commits + backups to avoid regressions.  
- **Performance issues in search**: index frequently queried columns; add result limits & caching.  
- **User adoption**: provide tooltips, documentation, and temporary access to legacy form.  
- **Lead auto-create duplicates**: dedupe by email + name combination; provide toast feedback.

## 16. Open Questions (Resolved)
- Mixed product/service invoices? **No** — keep modes distinct.  
- Product search follow stock? **No** — show all active items.  
- Service approval workflow? **No** in this scope.  
- Auto-create lead for new client? **Yes** — implemented in persistence logic.
