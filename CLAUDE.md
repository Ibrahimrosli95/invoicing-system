# Claude Context Guide - Sales Quotation & Invoicing System

## Project Overview
You are working on a **Sales Quotation & Invoicing System** for Bina Group. This is a Laravel 11 web application that manages the complete sales cycle from lead capture to payment collection. The system emphasizes clean, minimalist design with role-based access control and beautiful PDF generation.

## Quick Start Commands
```bash
# Development
php artisan serve
npm run dev
php artisan queue:work

# Testing
php artisan test
php artisan test --filter=PermissionTest
php artisan test --parallel

# Database
php artisan migrate:fresh --seed
php artisan db:seed --class=DemoDataSeeder
```

## Core Technology Stack
- **Backend**: Laravel 11, PHP 8.3
- **Database**: MySQL 8 (utf8mb4)
- **Frontend**: Blade + Alpine.js + Tailwind CSS
- **PDF**: Browsershot (Chromium-based)
- **Queue**: Laravel Horizon with Redis
- **Auth**: Laravel Breeze + Spatie Permission
- **Testing**: Pest PHP

## Design System

### Color Palette
```css
/* Use these exact colors throughout the application */
--primary: #2563EB;    /* Blue - Primary actions */
--success: #10B981;    /* Green - Success states */
--warning: #F59E0B;    /* Amber - Warnings */
--error: #EF4444;      /* Red - Errors */
--neutral: #6B7280;    /* Gray - Secondary text */
--background: #FFFFFF; /* White - Main background */
--surface: #F9FAFB;    /* Light Gray - Cards/sections */
```

### UI Principles
1. **Minimalist**: Clean lines, generous white space, subtle shadows
2. **Responsive**: Mobile-first, touch-optimized
3. **Consistent**: Use Tailwind utility classes, no custom CSS unless necessary
4. **Accessible**: WCAG 2.1 AA compliance, proper ARIA labels
5. **Fast**: Lazy loading, optimized queries, cached views

## Database Architecture

### Key Tables & Relationships
```
companies (tenant container)
├── users (belongsToMany teams)
├── teams (hasMany users, hasMany leads)
├── leads (belongsTo team, hasMany quotations)
├── quotations (belongsTo lead, hasOne invoice)
├── invoices (belongsTo quotation)
├── pricing_items (used in quotations/invoices)
├── service_templates (belongsToMany teams)
└── audit_logs (polymorphic, tracks all changes)
```

### Multi-tenancy Strategy
- Single database with `company_id` on all tenant tables
- Global scope: `->where('company_id', auth()->user()->company_id)`
- Use trait: `BelongsToCompany` on all models

## Role-Based Access Control (RBAC)

### Permission Hierarchy
```php
// Always check permissions in this order:
1. Company scope (can access company?)
2. Team scope (can access team?)  
3. Owner scope (owns the resource?)
4. Action permission (can perform action?)
```

### Role Capabilities Quick Reference
| Role | Scope | Create | Read | Update | Delete |
|------|-------|--------|------|--------|--------|
| Superadmin | All | ✅ | ✅ | ✅ | ✅ |
| Company Manager | Company | ❌ | ✅ | Invoice Status | ❌ |
| Finance Manager | Company | ❌ | ✅ | Pricing + Invoice Status | ❌ |
| Sales Manager | Team | ✅ | Team | Team | Team |
| Sales Coordinator | Company | Leads | ✅ | Leads | ❌ |
| Sales Executive | Own | ✅ | Own | Own | ❌ |

## Core Modules Implementation

### 1. Lead Management
```php
// Key model relationships
class Lead extends Model {
    use BelongsToCompany, HasStatuses, SoftDeletes;
    
    // Status flow
    const STATUSES = ['NEW', 'CONTACTED', 'QUOTED', 'WON', 'LOST'];
    
    // Always eager load these
    protected $with = ['team', 'assignedRep', 'activities'];
}

// Controller pattern
class LeadController {
    public function index() {
        $leads = Lead::query()
            ->forCompany() // scope
            ->forUserTeams() // scope based on role
            ->with(['team', 'assignedRep', 'lastActivity'])
            ->paginate(20);
    }
}
```

### 2. Quotation System
```php
// Two types of quotations
abstract class Quotation extends Model {
    use HasNumbering, GeneratesPDF, HasStatuses;
    
    // Global numbering pattern
    protected function generateNumber() {
        return sprintf('QTN-%s-%06d', 
            now()->year, 
            $this->getNextSequence()
        );
    }
}

class ProductQuotation extends Quotation {
    // Table-based layout
    protected $pdfTemplate = 'pdf.quotation.product';
}

class ServiceQuotation extends Quotation {
    // Section-based layout with subtotals
    protected $pdfTemplate = 'pdf.quotation.service';
    
    public function sections() {
        return $this->hasMany(QuotationSection::class);
    }
}
```

### 3. Invoice Management
```php
class Invoice extends Model {
    // Status state machine
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_SENT = 'SENT';
    const STATUS_UNPAID = 'UNPAID';
    const STATUS_PAID = 'PAID';
    const STATUS_OVERDUE = 'OVERDUE';
    
    // Auto-overdue check
    protected static function booted() {
        static::retrieved(function ($invoice) {
            if ($invoice->isOverdue()) {
                $invoice->markAsOverdue();
            }
        });
    }
    
    // Lock items when paid
    public function lockItems() {
        if ($this->status === self::STATUS_PAID) {
            $this->items->each->lock();
        }
    }
}
```

### 4. Service Template Manager
```php
class ServiceTemplate extends Model {
    use BelongsToTeams, Versionable;
    
    // Manager-only creation
    public function duplicate() {
        $new = $this->replicate();
        $new->name = $this->name . ' (Copy)';
        $new->version = $this->version + 1;
        $new->save();
        
        // Deep copy sections and items
        $this->sections->each(function ($section) use ($new) {
            $newSection = $section->replicate();
            $newSection->template_id = $new->id;
            $newSection->save();
            
            $section->items->each(function ($item) use ($newSection) {
                $newItem = $item->replicate();
                $newItem->section_id = $newSection->id;
                $newItem->save();
            });
        });
        
        return $new;
    }
}
```

## PDF Generation

### Browsershot Configuration
```php
use Spatie\Browsershot\Browsershot;

class PDFService {
    public function generate($model, $template) {
        $html = view($template, compact('model'))->render();
        
        return Browsershot::html($html)
            ->format('A4')
            ->margins(15, 15, 15, 15)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->pdf();
    }
}
```

### PDF Template Structure
```blade
{{-- resources/views/pdf/quotation/product.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <style>
        /* Inline styles for PDF consistency */
        body { font-family: 'Inter', sans-serif; }
        .watermark { 
            position: fixed; 
            opacity: 0.1; 
            transform: rotate(-45deg);
        }
    </style>
</head>
<body>
    @if($quotation->status === 'DRAFT')
        <div class="watermark">DRAFT</div>
    @endif
    
    {{-- Header, content, footer --}}
</body>
</html>
```

## Webhook System

### Event Broadcasting
```php
// Always fire these events
class QuotationCreated implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public function broadcastWith() {
        return [
            'quotation_id' => $this->quotation->id,
            'number' => $this->quotation->number,
            'customer' => $this->quotation->customer_name,
            'total' => $this->quotation->total,
            'created_at' => $this->quotation->created_at->toIso8601String(),
        ];
    }
}
```

### Webhook Delivery
```php
class WebhookService {
    public function deliver($event, $payload) {
        $signature = $this->generateSignature($payload);
        
        Http::withHeaders([
            'X-Webhook-Event' => $event,
            'X-Webhook-Signature' => $signature,
            'X-Webhook-Timestamp' => now()->timestamp,
        ])
        ->timeout(5)
        ->retry(3, 1000) // 3 retries with exponential backoff
        ->post($this->endpoint, $payload);
    }
}
```

## Common Patterns & Best Practices

### 1. Always Use Scopes
```php
// Good
$quotes = Quotation::forCompany()->forUserTeams()->get();

// Bad
$quotes = Quotation::where('company_id', $companyId)->get();
```

### 2. Eager Loading
```php
// Good - prevents N+1
$leads = Lead::with(['team', 'assignedRep', 'quotations'])->get();

// Bad
$leads = Lead::all();
foreach ($leads as $lead) {
    echo $lead->team->name; // N+1 query
}
```

### 3. Form Validation
```php
// Use Form Requests
class StoreQuotationRequest extends FormRequest {
    public function rules() {
        return [
            'customer_name' => 'required|string|max:100',
            'phone' => ['required', 'regex:/^(\+?6?0)[0-9]{9,10}$/'],
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }
}
```

### 4. Audit Logging
```php
// Automatic with model events
class AuditableModel extends Model {
    protected static function booted() {
        static::created(fn($model) => AuditLog::record('created', $model));
        static::updated(fn($model) => AuditLog::record('updated', $model));
        static::deleted(fn($model) => AuditLog::record('deleted', $model));
    }
}
```

### 5. Number Generation
```php
// Use database transactions to prevent duplicates
DB::transaction(function () use ($quotation) {
    $sequence = NumberSequence::lockForUpdate()
        ->where('type', 'quotation')
        ->where('company_id', $quotation->company_id)
        ->first();
    
    $quotation->number = sprintf('QTN-%s-%06d', 
        now()->year, 
        ++$sequence->current_number
    );
    
    $sequence->save();
    $quotation->save();
});
```

## Testing Guidelines

### Test Structure
```php
// tests/Feature/QuotationTest.php
test('sales rep can create quotation for own lead', function () {
    $rep = User::factory()->salesRep()->create();
    $lead = Lead::factory()->for($rep->team)->create();
    
    actingAs($rep)
        ->post('/quotations', [
            'lead_id' => $lead->id,
            'items' => [...]
        ])
        ->assertStatus(201)
        ->assertJson(['status' => 'DRAFT']);
});

test('sales rep cannot view other rep quotations', function () {
    $rep1 = User::factory()->salesRep()->create();
    $rep2 = User::factory()->salesRep()->create();
    $quotation = Quotation::factory()->for($rep2)->create();
    
    actingAs($rep1)
        ->get("/quotations/{$quotation->id}")
        ->assertStatus(403);
});
```

## Performance Optimization

### Query Optimization
```php
// Index these columns
Schema::table('leads', function (Blueprint $table) {
    $table->index(['company_id', 'status']);
    $table->index(['company_id', 'team_id']);
    $table->index('phone'); // for duplicate checking
});

// Use chunk for large datasets
Lead::where('status', 'NEW')
    ->chunk(100, function ($leads) {
        // Process in batches
    });
```

### Caching Strategy
```php
// Cache dashboard metrics
Cache::remember('dashboard.company.'.$companyId, 300, function () {
    return [
        'total_quotes' => Quotation::forCompany()->count(),
        'conversion_rate' => $this->calculateConversionRate(),
        // ...
    ];
});
```

## Common Issues & Solutions

### Issue: PDF Generation Timeout
```php
// Solution: Increase timeout and use queue
class GeneratePDFJob implements ShouldQueue {
    public $timeout = 120; // 2 minutes
    
    public function handle() {
        ini_set('max_execution_time', 120);
        // Generate PDF
    }
}
```

### Issue: Duplicate Phone Numbers
```php
// Solution: Soft duplicate warning
if ($existing = Lead::where('phone', $phone)->first()) {
    return response()->json([
        'warning' => 'Phone number exists',
        'existing_lead' => $existing,
        'action' => 'merge_or_create'
    ]);
}
```

### Issue: Permission Leaks
```php
// Solution: Always use policy checks
class QuotationController {
    public function __construct() {
        $this->authorizeResource(Quotation::class, 'quotation');
    }
}
```

## Development Workflow

### Branch Naming
```
feature/module-name
bugfix/issue-description
hotfix/critical-issue
```

### Commit Messages
```
feat: Add service template manager
fix: Correct PDF watermark positioning
refactor: Optimize lead query performance
test: Add invoice status transition tests
```

### Code Review Checklist
- [ ] Permissions checked at controller and query level
- [ ] N+1 queries prevented with eager loading
- [ ] Form validation includes all required fields
- [ ] Tests cover happy path and edge cases
- [ ] PDF generation works for all templates
- [ ] Audit logging captures changes
- [ ] Performance within targets (<200ms API, <3s PDF)

## API Response Format

### Success Response
```json
{
    "success": true,
    "data": {
        "id": 123,
        "number": "QTN-2025-000123",
        "status": "SENT"
    },
    "message": "Quotation created successfully"
}
```

### Error Response
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "phone": ["The phone field is required."],
        "items": ["At least one item is required."]
    }
}
```

### Pagination Response
```json
{
    "data": [...],
    "links": {
        "first": "http://app.test/api/leads?page=1",
        "last": "http://app.test/api/leads?page=10",
        "prev": null,
        "next": "http://app.test/api/leads?page=2"
    },
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 200
    }
}
```

## Environment Variables

```env
# Essential for development
APP_NAME="Sales System"
APP_ENV=local
APP_DEBUG=true

# Database
DB_CONNECTION=mysql
DB_DATABASE=sales_system
DB_USERNAME=root
DB_PASSWORD=

# Redis for queues
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Browsershot for PDFs
BROWSERSHOT_BIN=/usr/local/bin/chromium
BROWSERSHOT_TIMEOUT=60

# Webhook defaults
WEBHOOK_RETRY_COUNT=3
WEBHOOK_TIMEOUT=5
WEBHOOK_SIGNATURE_KEY=your-secret-key

# Number sequences
QUOTATION_PREFIX=QTN
INVOICE_PREFIX=INV
RESET_NUMBERS_YEARLY=true
```

## Deployment Checklist

### Pre-deployment
- [ ] Run tests: `php artisan test`
- [ ] Check migrations: `php artisan migrate:status`
- [ ] Compile assets: `npm run build`
- [ ] Clear caches: `php artisan optimize:clear`

### Deployment
- [ ] Enable maintenance mode: `php artisan down`
- [ ] Pull latest code
- [ ] Install dependencies: `composer install --no-dev`
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Cache configs: `php artisan optimize`
- [ ] Restart queues: `php artisan queue:restart`
- [ ] Disable maintenance: `php artisan up`

### Post-deployment
- [ ] Verify webhooks are firing
- [ ] Test PDF generation
- [ ] Check permission boundaries
- [ ] Monitor error logs
- [ ] Verify queue processing

## Contact & Support

**Project**: Sales Quotation & Invoicing System  
**Company**: Bina Group  
**Version**: 1.0  
**Laravel**: 11.x  
**PHP**: 8.3+  

---

## Quick Reference Card

### Artisan Commands
```bash
# Custom commands for this project
php artisan lead:assign {lead_id} {team_id}
php artisan quotation:send {quotation_id}
php artisan invoice:check-overdue
php artisan webhook:replay {delivery_id}
php artisan template:duplicate {template_id}
php artisan number:reset --type=quotation
php artisan audit:export --from=2025-01-01
```

### Key URLs
```
/leads           # Lead kanban board
/quotations      # Quotation list
/invoices        # Invoice management
/pricing         # Pricing book
/templates       # Service templates
/reports         # Analytics dashboard
/settings        # System configuration
```

Remember: This is a **minimalist, clean, and beautiful** application. Every UI decision should prioritize clarity, simplicity, and user experience.