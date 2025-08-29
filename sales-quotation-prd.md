# Sales Quotation & Invoicing System
## Product Requirements Document

**Version:** 1.0  
**Date:** August 30, 2025  
**Company:** Bina Group  
**Document Type:** Internal PRD

---

## Executive Summary

### Vision
Build a centralized, role-based web application that transforms how Bina Group manages its sales operations—from lead capture through payment collection. The system will deliver a seamless, intuitive experience while enforcing proper visibility hierarchies and maintaining data integrity.

### Core Objectives
- **Centralization**: Single source of truth for all quotations and invoices
- **Standardization**: Consistent templates for products and services with professional PDF exports
- **Visibility**: Real-time dashboards with role-appropriate access
- **Automation**: Streamlined workflows reducing manual processes by 30%

### Key Outcomes
- 100% quotation/invoice centralization within 3 months
- 30% reduction in lead-to-quote cycle time
- 90% monthly active usage by management and finance teams
- Complete visibility of invoice aging (0-30/31-60/61-90/90+ days)

---

## 1. Users & Access Architecture

### User Hierarchy
The system follows a container-based visibility model:

```
Company (Container)
├── Management Layer
│   ├── Company Manager (GM/CEO)
│   └── Finance Manager
└── Sales Organization
    ├── Sales Managers
    ├── Sales Coordinators
    └── Sales Teams
        └── Sales Executives (Reps)
```

### Role Definitions

#### Superadmin
- Full system control
- User management
- Global settings configuration
- Complete data access

#### Company Manager
- Read access to all data
- Export capabilities
- Invoice status updates
- No creation/editing of sales documents

#### Finance Manager
- Pricing book management
- Full read access to quotes/invoices
- Invoice status updates
- Financial reporting

#### Sales Manager
- Team management
- Service template creation
- Full access to team data
- Performance reporting

#### Sales Coordinator
- Lead creation and assignment
- Company-wide read access
- Lost reason tracking
- No pricing modifications

#### Sales Executive
- Quote/invoice creation
- Access to own documents only
- Lead status updates
- Personal performance dashboard

---

## 2. Core Modules

### 2.1 Lead Management (CRM-Lite)

#### Purpose
Centralized lead tracking with clear assignment and conversion visibility.

#### Key Features
- **Quick Create**: Name + Phone number minimum
- **Smart Assignment**: Team and rep allocation
- **Status Pipeline**: NEW → CONTACTED → QUOTED → WON/LOST
- **Activity Timeline**: Track all interactions
- **Duplicate Detection**: Phone-based with merge options

#### User Experience
- Kanban board visualization by status
- One-click lead-to-quote conversion
- Bulk operations (assign/export)
- Real-time search by name/phone/company

#### Data Requirements
| Field | Type | Validation |
|-------|------|------------|
| Name | String | Required, 2-100 chars |
| Phone | String | Required, unique per company |
| Email | String | Optional, valid format |
| Status | Enum | Required, follows pipeline |
| Lost Reason | Reference | Required when LOST |
| Team Assignment | Array | At least one team |

---

### 2.2 Quotation System

#### Two Distinct Styles

##### Product Quotation (Table Format)
- Clean tabular layout
- Line items with quantity/price calculations
- Suitable for product-based sales
- Auto-calculation of totals

##### Service Quotation (Sectioned Format)
- Section-based organization
- Scope notes and detailed descriptions
- Per-section subtotals
- Template-driven creation

#### Common Features
- **Global Numbering**: QTN-YYYY-###### format
- **Status Tracking**: DRAFT → SENT
- **PDF Generation**: Professional, branded documents
- **Template Library**: Reusable configurations
- **Terms Management**: Company/Team/Document levels

#### Design Principles
- Inline editing for efficiency
- Drag-and-drop reordering
- Real-time total calculations
- WYSIWYG PDF preview
- Mobile-responsive interface

---

### 2.3 Invoice Management

#### Creation Flows
1. **Primary Path**: Generate from approved quotation
2. **Standalone**: Direct invoice creation (configurable)

#### Status Lifecycle
```
DRAFT → SENT → UNPAID → OVERDUE/PAID
                  ↓
              Auto-overdue when past due date
```

#### Key Features
- **Numbering**: INV-YYYY-###### format
- **Due Date**: Default 30 days (configurable)
- **Payment Tracking**: Notes and reference fields
- **Status Control**: Finance/Management can update
- **Lock on Payment**: Items locked when PAID

#### Aging Analytics
- 0-30 days (Current)
- 31-60 days (Late)
- 61-90 days (Very Late)
- 90+ days (Critical)

---

### 2.4 Pricing Book

#### Purpose
Centralized catalog for consistent pricing across all quotations.

#### Structure
```
Pricing Book
├── Products
│   ├── Code (unique)
│   ├── Name
│   ├── Unit (Nos/M²/Litre)
│   └── Default Price
└── Services
    ├── Code (unique)
    ├── Name
    ├── Description
    └── Default Rate
```

#### Features
- Category and tag organization
- Quick search with typeahead
- Bulk import/export (CSV)
- Price change audit trail
- Active/Inactive status

---

### 2.5 Service Template Manager

#### Purpose
Standardize service quotations with manager-maintained templates.

#### Template Structure
```
Template
├── Basic Information
│   ├── Name
│   ├── Category
│   └── Tags
└── Sections (Ordered)
    ├── Section Title
    ├── Scope Notes (Optional)
    └── Line Items
        ├── Description
        ├── Unit
        ├── Default Quantity
        └── Default Price
```

#### Manager Capabilities
- Create and maintain templates
- Assign to specific teams
- Version control
- Duplicate and modify
- Activate/Deactivate

#### Rep Usage
- Browse available templates
- One-click application
- Full editing after application
- Add/remove sections as needed

---

## 3. Reporting & Analytics

### Dashboard Hierarchy

#### Company Dashboard
- **Metrics**: Total quotes, conversion rates, revenue pipeline
- **Trends**: Monthly/quarterly comparisons
- **Rankings**: Top teams and performers
- **Alerts**: Overdue invoices, stalled quotes

#### Team Dashboard
- **Pipeline**: Stage-by-stage breakdown
- **Performance**: Team leaderboard
- **Aging**: Quote and invoice aging
- **Analysis**: Lost reason breakdown

#### Individual Dashboard
- **Personal Pipeline**: Own leads and quotes
- **Pending Actions**: Follow-ups and drafts
- **Performance**: Personal metrics
- **Calendar**: Upcoming activities

### Export Capabilities
- CSV/XLSX formats
- Filtered data exports
- Scheduled reports
- Role-based data scoping

---

## 4. Design System

### Visual Principles

#### Minimalist Interface
- **Clean Lines**: Subtle borders and dividers
- **White Space**: Generous padding for breathing room
- **Typography**: Clear hierarchy with 2-3 font weights
- **Color Palette**: Neutral base with accent colors for actions

#### Color Scheme
```
Primary:    #2563EB (Blue)
Success:    #10B981 (Green)
Warning:    #F59E0B (Amber)
Error:      #EF4444 (Red)
Neutral:    #6B7280 (Gray)
Background: #FFFFFF (White)
Surface:    #F9FAFB (Light Gray)
```

#### Component Library
- **Buttons**: Ghost, outline, and filled variants
- **Forms**: Floating labels with subtle animations
- **Tables**: Alternating rows with hover states
- **Cards**: Soft shadows and rounded corners
- **Modals**: Centered with backdrop blur

### Responsive Design
- Mobile-first approach
- Breakpoints: 640px, 768px, 1024px, 1280px
- Touch-optimized controls
- Collapsible navigation
- Swipe gestures for mobile

---

## 5. Technical Specifications

### Architecture

#### Technology Stack
- **Backend**: Laravel 11 with PHP 8.3
- **Database**: MySQL 8 with utf8mb4
- **Cache**: Redis
- **Queue**: Laravel Horizon
- **PDF**: Browsershot (Chromium)
- **Frontend**: Blade + Alpine.js + Tailwind CSS

#### Security
- Role-based access control (RBAC)
- CSRF protection
- XSS prevention
- Input sanitization
- Signed URLs for PDFs
- Rate limiting on exports

#### Performance Targets
- Page load: < 2 seconds
- API response: < 200ms (p95)
- PDF generation: < 3 seconds
- Dashboard refresh: < 1 second
- Search results: < 500ms

### Integration

#### Webhook System
- **Events**: Lead, Quote, Invoice lifecycle events
- **Security**: HMAC SHA-256 signatures
- **Reliability**: Retry with exponential backoff
- **Monitoring**: Delivery logs and replay capability

#### API Endpoints
```
/api/v1/leads
/api/v1/quotations
/api/v1/invoices
/api/v1/pricing
/api/v1/templates
/api/v1/reports
```

---

## 6. PDF Templates

### Design Specifications

#### Page Setup
- Size: A4 Portrait
- Margins: 15mm all sides
- Font: Inter/Roboto 10pt
- Headers: 12-16pt bold

#### Product Template Layout
```
┌─────────────────────────────────────┐
│ [Company Info]          [Logo]      │
├─────────────────────────────────────┤
│           QUOTATION #QTN-2025-000123│
│           Date: 30 Aug 2025         │
├─────────────────────────────────────┤
│ Bill To:                            │
│ Customer Name                       │
│ Address                             │
├─────────────────────────────────────┤
│ No | Description | Qty | Price | Amt│
│ 1  | Item...     | 10  | 50.00 | 500│
├─────────────────────────────────────┤
│                    Subtotal: RM 500 │
│                    Total: RM 500    │
├─────────────────────────────────────┤
│ Terms & Conditions                  │
│ Bank Details                        │
│ [Signature]                         │
└─────────────────────────────────────┘
```

#### Service Template Layout
- Section-based with headers
- Bullet points for scope
- Per-section subtotals
- Grand total at end
- Multiple signature blocks

---

## 7. Implementation Roadmap

### Phase 1: Foundation (Weeks 1-4)
- [ ] Project setup and infrastructure
- [ ] Authentication and RBAC
- [ ] Company and team management
- [ ] Basic UI framework

### Phase 2: Core Modules (Weeks 5-8)
- [ ] Lead management
- [ ] Pricing book
- [ ] Quotation system (Product)
- [ ] Basic reporting

### Phase 3: Advanced Features (Weeks 9-12)
- [ ] Service quotations
- [ ] Template manager
- [ ] Invoice management
- [ ] Webhook system

### Phase 4: Polish & Launch (Weeks 13-16)
- [ ] PDF generation refinement
- [ ] Dashboard optimization
- [ ] Performance tuning
- [ ] UAT and bug fixes
- [ ] Deployment and training

---

## 8. Success Metrics

### Adoption Metrics
- User activation rate: > 95% in first month
- Daily active users: > 80%
- Feature utilization: > 70% using templates

### Performance Metrics
- System uptime: 99.9%
- Response time: < 2s for 95% of requests
- Error rate: < 0.1%

### Business Metrics
- Quote creation time: -50%
- Invoice processing time: -40%
- Outstanding invoice reduction: -30%
- Customer satisfaction: > 4.5/5

---

## 9. Testing Strategy

### Automated Testing
- Unit tests: 80% coverage
- Integration tests: Critical paths
- E2E tests: User journeys
- Performance tests: Load scenarios

### Manual Testing
- Cross-browser compatibility
- Mobile responsiveness
- PDF accuracy
- Permission boundaries

### User Acceptance Testing
- Role-based scenarios
- Real-world workflows
- Edge cases
- Data migration validation

---

## 10. Future Enhancements

### Version 1.1 (Q2 2026)
- Mobile applications (iOS/Android)
- Advanced analytics dashboard
- Bulk operations improvements
- Email integration

### Version 1.2 (Q3 2026)
- Accounting software integration
- E-signature capability
- Online payment processing
- Multi-language support

### Version 2.0 (Q4 2026)
- AI-powered insights
- Predictive analytics
- Workflow automation
- Advanced CRM features

---

## Appendix A: Data Model

### Core Entities
```
companies
├── users
├── teams
├── leads
├── quotations
├── invoices
├── pricing_items
├── service_templates
├── webhook_endpoints
└── audit_logs
```

### Relationships
- Users ↔ Teams (many-to-many)
- Teams → Leads (one-to-many)
- Leads → Quotations (one-to-many)
- Quotations → Invoices (one-to-one)
- Templates → Teams (many-to-many)

---

## Appendix B: API Documentation

### Authentication
```http
POST /api/v1/auth/login
{
  "email": "user@example.com",
  "password": "secure_password"
}
```

### Lead Creation
```http
POST /api/v1/leads
{
  "name": "John Doe",
  "phone": "+60123456789",
  "company": "ABC Corp",
  "team_ids": [1, 2]
}
```

### Quotation Generation
```http
POST /api/v1/quotations
{
  "lead_id": 123,
  "type": "product",
  "items": [...],
  "terms": "Net 30"
}
```

---

## Sign-off

**Product Owner:** _________________ **Date:** _________

**Technical Lead:** _________________ **Date:** _________

**QA Lead:** _________________ **Date:** _________

**Design Lead:** _________________ **Date:** _________