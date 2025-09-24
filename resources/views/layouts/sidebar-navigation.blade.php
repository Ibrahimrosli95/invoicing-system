<!-- Sidebar Navigation -->
@php
    if (!function_exists('render_sidebar_icon')) {
        function render_sidebar_icon(string $icon): string
        {
            $paths = match ($icon) {
                'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" /><path stroke-linecap="round" stroke-linejoin="round" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v3H8V5z" />',
                'leads' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />',
                'assessments' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />',
                'organization' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 21h15M6 21V10.5A1.5 1.5 0 017.5 9h9a1.5 1.5 0 011.5 1.5V21M9 9V6.75A1.75 1.75 0 0110.75 5h2.5A1.75 1.75 0 0115 6.75V9M9 13.5h6" />',
                'teams' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 20.25v-1.5a3 3 0 013-3h4.5a3 3 0 013 3v1.5M8.25 9a3 3 0 116 0 3 3 0 01-6 0zm-3 6a3 3 0 00-2.25-3 3 3 0 100 6A3 3 0 015.25 15zm13.5 0a3 3 0 012.25-3 3 3 0 110 6 3 3 0 01-2.25-3z" />',
                'quotations' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
                'invoices' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />',
                'service-templates' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h2.25a1.5 1.5 0 011.5 1.5v.75a1.5 1.5 0 103 0V8.25a1.5 1.5 0 011.5-1.5h2.25v2.25a1.5 1.5 0 01-1.5 1.5h-.75a1.5 1.5 0 100 3h.75a1.5 1.5 0 011.5 1.5v2.25h-2.25a1.5 1.5 0 01-1.5-1.5v-.75a1.5 1.5 0 10-3 0v.75a1.5 1.5 0 01-1.5 1.5H4.5v-2.25a1.5 1.5 0 011.5-1.5h.75a1.5 1.5 0 000-3H6a1.5 1.5 0 01-1.5-1.5z" />',
                'pricing' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />',
                'proofs' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 5.25h10.5a1.5 1.5 0 011.5 1.5v10.5a1.5 1.5 0 01-1.5 1.5H6.75a1.5 1.5 0 01-1.5-1.5V6.75a1.5 1.5 0 011.5-1.5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 10.5a1.5 1.5 0 110-3 1.5 1.5 0 010 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 16.5l3.75-3.75 2.25 2.25 3-3 4.5 4.5" />',
                'testimonials' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 8.25A7.5 7.5 0 0112 3a7.5 7.5 0 017.5 7.5 7.5 7.5 0 01-7.5 7.5H9.75L6 21v-3.75A7.5 7.5 0 014.5 8.25z" />',
                'certifications' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75a6 6 0 100-12 6 6 0 000 12z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 14.25l-1.5 6 3.75-2.25 3.75 2.25-1.5-6" /><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l1.5 1.5 3-3" />',
                'reports' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />',
                'search' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />',
                'audit' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />',
                'security' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.5a4.5 4.5 0 10-9 0v3a2.25 2.25 0 00-2.25 2.25v5.25A2.25 2.25 0 007.5 20.25h9a2.25 2.25 0 002.25-2.25v-5.25A2.25 2.25 0 0016.5 10.5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2.25" />',
                'notifications' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21a1.875 1.875 0 001.875-1.875h-3.75A1.875 1.875 0 0012 21z" /><path stroke-linecap="round" stroke-linejoin="round" d="M18 16.875H6l1.2-1.8a3 3 0 00.45-1.65V11.25a4.5 4.5 0 119 0v2.175a3 3 0 00.45 1.65l1.2 1.8z" />',
                'customers' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 20.25v-.75a6 6 0 016-6h3a6 6 0 016 6v.75" />',
                'customer-segments' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM9 9a2 2 0 11-4 0 2 2 0 014 0z" />',
                'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 20.25v-.75a6 6 0 016-6h3a6 6 0 016 6v.75" />',
                'company' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />',
                'numbering' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 9h13.5m-15 6h13.5M9 3.75L7.5 20.25M16.5 3.75L15 20.25" />',
                'documents' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.75h6a1.5 1.5 0 011.5 1.5v12a1.5 1.5 0 01-1.5 1.5H9.75a1.5 1.5 0 01-1.5-1.5v-12a1.5 1.5 0 011.5-1.5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h-1.5a1.5 1.5 0 00-1.5 1.5v11.25a1.5 1.5 0 001.5 1.5h8.25" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75h3M12 12.75h3M12 15.75h3" />',
                'system' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h4.5m3 0h7.5M4.5 12h9m3 0h3M4.5 17.25h3m3 0h9" /><circle cx="9.75" cy="6.75" r="1.5" /><circle cx="15.75" cy="12" r="1.5" /><circle cx="9.75" cy="17.25" r="1.5" />',
                'webhook' => '<circle cx="12" cy="7.5" r="2.25" /><circle cx="7.5" cy="17.25" r="2.25" /><circle cx="16.5" cy="17.25" r="2.25" /><path stroke-linecap="round" stroke-linejoin="round" d="M10.44 9.36l-2.1 4.89m5.22-4.89l2.1 4.89" />',
                default => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />',
            };

            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">'.$paths.'</svg>';
        }
    }

    if (!function_exists('sidebar_item_is_visible')) {
        function sidebar_item_is_visible(array $item): bool
        {
            $rule = $item['visible'] ?? true;

            if ($rule === true) {
                return true;
            }

            if ($rule === false) {
                return false;
            }

            if (is_string($rule)) {
                $user = auth()->user();
                return $user ? $user->can($rule) : false;
            }

            if (!is_array($rule)) {
                return (bool) $rule;
            }

            $type = $rule['type'] ?? 'can';
            $user = auth()->user();

            if ($type === 'auth') {
                return (bool) $user;
            }

            if (in_array($type, ['can', 'ability'], true)) {
                if (!$user) {
                    return false;
                }

                $ability = $rule['ability'] ?? null;

                if (!$ability) {
                    return false;
                }

                $arguments = $rule['arguments'] ?? null;

                return $arguments === null
                    ? $user->can($ability)
                    : $user->can($ability, $arguments);
            }

            if ($type === 'canany') {
                if (!$user) {
                    return false;
                }

                $abilities = $rule['abilities'] ?? [];

                if (!is_iterable($abilities)) {
                    return false;
                }

                $arguments = $rule['arguments'] ?? null;

                foreach ($abilities as $ability) {
                    if ($arguments === null ? $user->can($ability) : $user->can($ability, $arguments)) {
                        return true;
                    }
                }

                return false;
            }

            return (bool) $rule;
        }
    }

    if (!function_exists('sidebar_item_is_active')) {
        function sidebar_item_is_active(array|string|null $patterns): bool
        {
            if ($patterns === null) {
                return false;
            }

            $patterns = is_array($patterns) ? $patterns : [$patterns];

            foreach ($patterns as $pattern) {
                if ($pattern && request()->routeIs($pattern)) {
                    return true;
                }
            }

            return false;
        }
    }

    if (!function_exists('sidebar_user_initials')) {
        function sidebar_user_initials(?string $name): string
        {
            if (!$name) {
                return '';
            }

            $parts = preg_split('/\s+/', trim($name)) ?: [];
            $initials = '';

            foreach ($parts as $part) {
                if ($part === '') {
                    continue;
                }

                $initials .= strtoupper(substr($part, 0, 1));

                if (strlen($initials) >= 2) {
                    break;
                }
            }

            if ($initials === '' && $name !== '') {
                $initials = strtoupper(substr($name, 0, 1));
            }

            return substr($initials, 0, 2);
        }
    }

    $navGroups = [
        [
            'label' => 'Overview',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'dashboard',
                    'patterns' => ['dashboard'],
                    'icon' => 'dashboard',
                    'visible' => ['type' => 'auth'],
                ],
            ],
        ],
        [
            'label' => 'CRM',
            'items' => [
                [
                    'label' => 'Leads',
                    'route' => 'leads.index',
                    'patterns' => ['leads.*'],
                    'icon' => 'leads',
                    'visible' => ['type' => 'can', 'ability' => 'viewAny', 'arguments' => \App\Models\Lead::class],
                ],
                [
                    'label' => 'Customers',
                    'route' => 'customers.index',
                    'patterns' => ['customers.*'],
                    'icon' => 'customers',
                    'visible' => ['type' => 'can', 'ability' => 'viewAny', 'arguments' => \App\Models\Customer::class],
                ],
                [
                    'label' => 'Customer Segments',
                    'route' => 'customer-segments.index',
                    'patterns' => ['customer-segments.*'],
                    'icon' => 'customer-segments',
                    'visible' => ['type' => 'can', 'ability' => 'viewAny', 'arguments' => \App\Models\CustomerSegment::class],
                ],
                [
                    'label' => 'Organization',
                    'route' => 'organization.index',
                    'patterns' => ['organization.*'],
                    'icon' => 'organization',
                    'visible' => ['type' => 'canany', 'abilities' => ['view teams', 'manage teams']],
                ],
                [
                    'label' => 'Teams',
                    'route' => 'teams.index',
                    'patterns' => ['teams.*'],
                    'icon' => 'teams',
                    'visible' => ['type' => 'canany', 'abilities' => ['view teams', 'manage teams']],
                ],
            ],
        ],
        [
            'label' => 'Sales',
            'items' => [
                [
                    'label' => 'Assessments',
                    'route' => 'assessments.index',
                    'patterns' => ['assessments.*'],
                    'icon' => 'assessments',
                    'visible' => ['type' => 'can', 'ability' => 'viewAny', 'arguments' => \App\Models\Assessment::class],
                ],
                [
                    'label' => 'Quotations',
                    'route' => 'quotations.index',
                    'patterns' => ['quotations.*'],
                    'icon' => 'quotations',
                    'visible' => ['type' => 'can', 'ability' => 'viewAny', 'arguments' => \App\Models\Quotation::class],
                    'submenu' => [
                        [
                            'label' => 'All Quotations',
                            'route' => 'quotations.index',
                            'patterns' => ['quotations.index', 'quotations.show', 'quotations.edit'],
                        ],
                        [
                            'label' => 'Create Product Quote',
                            'route' => 'quotations.create.products',
                            'patterns' => ['quotations.create.products'],
                            'visible' => function() { return config('features.quotation_builder_v2', false); },
                        ],
                        [
                            'label' => 'Create Service Quote',
                            'route' => 'quotations.create.services',
                            'patterns' => ['quotations.create.services'],
                            'visible' => function() { return config('features.quotation_builder_v2', false); },
                        ],
                        [
                            'label' => 'Create Quotation (Legacy)',
                            'route' => 'quotations.create',
                            'patterns' => ['quotations.create'],
                            'visible' => function() { return !config('features.quotation_builder_v2', false); },
                        ],
                    ],
                ],
                [
                    'label' => 'Invoices',
                    'route' => 'invoices.index',
                    'patterns' => ['invoices.*'],
                    'icon' => 'invoices',
                    'visible' => ['type' => 'can', 'ability' => 'viewAny', 'arguments' => \App\Models\Invoice::class],
                    'submenu' => [
                        [
                            'label' => 'All Invoices',
                            'route' => 'invoices.index',
                            'patterns' => ['invoices.index', 'invoices.show', 'invoices.edit'],
                        ],
                        [
                            'label' => 'Invoice Builder',
                            'route' => 'invoices.builder',
                            'patterns' => ['invoices.builder'],
                            'visible' => ['type' => 'can', 'ability' => 'create', 'arguments' => \App\Models\Invoice::class],
                        ],
                        [
                            'label' => 'Create Product Invoice',
                            'route' => 'invoices.create.products',
                            'patterns' => ['invoices.create.products'],
                            'visible' => function() { return config('features.invoice_builder_v2', false); },
                        ],
                        [
                            'label' => 'Create Service Invoice',
                            'route' => 'invoices.create.services',
                            'patterns' => ['invoices.create.services'],
                            'visible' => function() { return config('features.invoice_builder_v2', false); },
                        ],
                        [
                            'label' => 'Create Invoice (Legacy)',
                            'route' => 'invoices.create',
                            'patterns' => ['invoices.create'],
                            'visible' => function() { return !config('features.invoice_builder_v2', false); },
                        ],
                    ],
                ],
                [
                    'label' => 'Service Templates',
                    'route' => 'service-templates.index',
                    'patterns' => ['service-templates.*'],
                    'icon' => 'service-templates',
                    'visible' => ['type' => 'can', 'ability' => 'viewAny', 'arguments' => \App\Models\ServiceTemplate::class],
                ],
                [
                    'label' => 'Pricing Book',
                    'route' => 'pricing.index',
                    'patterns' => ['pricing.index'],
                    'icon' => 'pricing',
                    'visible' => ['type' => 'canany', 'abilities' => ['view pricing', 'manage pricing']],
                ],
                [
                    'label' => 'Customer Segments',
                    'route' => 'pricing.segments',
                    'patterns' => ['pricing.segments*'],
                    'icon' => 'pricing',
                    'visible' => ['type' => 'canany', 'abilities' => ['view pricing', 'manage pricing']],
                ],
            ],
        ],
        [
            'label' => 'Proof Engine',
            'items' => [
                [
                    'label' => 'Proof Library',
                    'route' => 'proofs.index',
                    'patterns' => ['proofs.*'],
                    'icon' => 'proofs',
                    'visible' => ['type' => 'can', 'ability' => 'viewAny', 'arguments' => \App\Models\Proof::class],
                ],
                [
                    'label' => 'Testimonials',
                    'route' => 'testimonials.index',
                    'patterns' => ['testimonials.*'],
                    'icon' => 'testimonials',
                    'visible' => ['type' => 'auth'],
                ],
                [
                    'label' => 'Certifications',
                    'route' => 'certifications.index',
                    'patterns' => ['certifications.*'],
                    'icon' => 'certifications',
                    'visible' => ['type' => 'auth'],
                ],
            ],
        ],
        [
            'label' => 'Intelligence',
            'items' => [
                [
                    'label' => 'Reports',
                    'route' => 'reports.index',
                    'patterns' => ['reports.*'],
                    'icon' => 'reports',
                    'visible' => ['type' => 'auth'],
                ],
                [
                    'label' => 'Global Search',
                    'route' => 'search.index',
                    'patterns' => ['search.*'],
                    'icon' => 'search',
                    'visible' => ['type' => 'auth'],
                ],
                [
                    'label' => 'Audit Center',
                    'route' => 'audit.index',
                    'patterns' => ['audit.*'],
                    'icon' => 'audit',
                    'visible' => 'view audit logs',
                ],
                [
                    'label' => 'Security Center',
                    'route' => 'security.dashboard',
                    'patterns' => ['security.*'],
                    'icon' => 'security',
                    'visible' => 'view security monitoring',
                ],
                [
                    'label' => 'Notifications',
                    'route' => 'notifications.preferences.index',
                    'patterns' => ['notifications.preferences.*'],
                    'icon' => 'notifications',
                    'visible' => ['type' => 'auth'],
                ],
            ],
        ],
        [
            'label' => 'Administration',
            'items' => [
                [
                    'label' => 'Users',
                    'route' => 'users.index',
                    'patterns' => ['users.*'],
                    'icon' => 'users',
                    'visible' => ['type' => 'can', 'ability' => 'viewAny', 'arguments' => \App\Models\User::class],
                ],
                [
                    'label' => 'My Profile',
                    'route' => 'profile',
                    'patterns' => ['profile*'],
                    'icon' => 'users',
                    'visible' => ['type' => 'auth'],
                ],
                [
                    'label' => 'Company Profile',
                    'route' => 'company.show',
                    'patterns' => ['company.*'],
                    'icon' => 'company',
                    'visible' => ['type' => 'can', 'ability' => 'manage', 'arguments' => \App\Models\Company::class],
                ],
                [
                    'label' => 'Numbering',
                    'route' => 'settings.numbering.index',
                    'patterns' => ['settings.numbering.*'],
                    'icon' => 'numbering',
                    'visible' => ['type' => 'can', 'ability' => 'manage', 'arguments' => \App\Models\Company::class],
                ],
                [
                    'label' => 'Document Settings',
                    'route' => 'settings.documents.index',
                    'patterns' => ['settings.documents.*'],
                    'icon' => 'documents',
                    'visible' => ['type' => 'can', 'ability' => 'manage', 'arguments' => \App\Models\Company::class],
                ],
                [
                    'label' => 'Invoice Settings',
                    'route' => 'invoice-settings.index',
                    'patterns' => ['invoice-settings.*'],
                    'icon' => 'invoice-settings',
                    'visible' => ['type' => 'can', 'ability' => 'manage', 'arguments' => \App\Models\Company::class],
                ],
                [
                    'label' => 'System Settings',
                    'route' => 'settings.system.index',
                    'patterns' => ['settings.system.*'],
                    'icon' => 'system',
                    'visible' => ['type' => 'can', 'ability' => 'manage', 'arguments' => \App\Models\Company::class],
                ],
                [
                    'label' => 'Webhook Endpoints',
                    'route' => 'webhook-endpoints.index',
                    'patterns' => ['webhook-endpoints.*'],
                    'icon' => 'webhook',
                    'visible' => ['type' => 'can', 'ability' => 'viewAny', 'arguments' => \App\Models\WebhookEndpoint::class],
                ],
            ],
        ],
    ];

    $navigationGroups = [];

    foreach ($navGroups as $group) {
        $items = [];

        foreach ($group['items'] as $item) {
            if (sidebar_item_is_visible($item)) {
                $items[] = $item;
            }
        }

        if (!empty($items)) {
            $group['items'] = $items;
            $navigationGroups[] = $group;
        }
    }

    $user = auth()->user();
    $initials = sidebar_user_initials($user?->name);
    $appName = config('app.name', 'Sales System');
@endphp

@once
    <style>[x-cloak]{display:none!important;}</style>
@endonce

<div x-data="{ mobileOpen: false, desktopCollapsed: false }" class="relative min-h-screen bg-slate-100">
    <header class="sticky top-0 z-30 flex items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-3 shadow-sm md:hidden">
        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-slate-200 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1" @click="mobileOpen = true">
            <span class="sr-only">Open navigation</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <x-application-logo class="h-8 w-8 text-blue-600" />
            <span class="text-base font-semibold text-slate-900">{{ $appName }}</span>
        </a>
        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white">
            {{ $initials }}
        </div>
    </header>

    <div x-cloak x-show="mobileOpen" class="fixed inset-0 z-40 flex md:hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60" @click="mobileOpen = false"></div>
        <aside x-show="mobileOpen" x-transition:enter="transition ease-in-out duration-200 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-200 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative flex w-64 max-w-xs flex-col bg-white shadow-2xl">
            <div class="flex items-center justify-between px-5 pt-5">
                <div class="flex items-center gap-2">
                    <x-application-logo class="h-8 w-8 text-blue-600" />
                    <span class="text-lg font-semibold text-slate-900">{{ $appName }}</span>
                </div>
                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-md text-slate-500 transition hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" @click="mobileOpen = false">
                    <span class="sr-only">Close navigation</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>
            <nav class="mt-6 flex-1 overflow-y-auto px-4 pb-6" aria-label="Mobile sidebar">
                @foreach ($navigationGroups as $group)
                    <div class="mb-6">
                        <p class="px-1 text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $group['label'] }}</p>
                        <div class="mt-2 space-y-1">
                            @foreach ($group['items'] as $item)
                                @php
                                    $isActive = sidebar_item_is_active($item['patterns'] ?? $item['route']);
                                    $linkBase = 'group relative flex items-center gap-3 overflow-hidden rounded-lg pl-5 pr-3 py-2 text-base font-medium transition-colors duration-150';
                                    $linkTone = $isActive ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900';
                                @endphp
                                <a href="{{ route($item['route']) }}" class="{{ $linkBase }} {{ $linkTone }}">
                                    <span class="absolute left-0 top-1/2 h-8 w-1 -translate-y-1/2 rounded-r-full transition-colors duration-150 {{ $isActive ? 'bg-blue-600' : 'bg-transparent group-hover:bg-blue-200' }}"></span>
                                    <span class="relative flex h-10 w-10 items-center justify-center rounded-md {{ $isActive ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-500 group-hover:bg-blue-50 group-hover:text-blue-600' }}">
                                        {!! render_sidebar_icon($item['icon'] ?? 'default') !!}
                                    </span>
                                    <span class="relative">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
            <div class="border-t border-slate-200 p-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white">
                        {{ $initials }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-slate-900">{{ $user?->name }}</p>
                        @if ($user?->email)
                            <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                        @endif
                        <div class="mt-2 flex items-center gap-3 text-xs">
                            <a href="{{ route('profile') }}" class="font-medium text-blue-600 transition hover:text-blue-700">Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-slate-500 transition hover:text-slate-700">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <aside class="hidden md:fixed md:inset-y-0 md:flex md:flex-col md:border-r md:border-slate-200 md:bg-white md:shadow-sm md:transition-[width] md:duration-200" :class="desktopCollapsed ? 'md:w-20' : 'md:w-64'">
        <div class="flex h-16 items-center justify-between px-4" :class="desktopCollapsed ? 'md:px-3' : 'md:px-5'">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2" :class="desktopCollapsed ? 'justify-center' : ''">
                <x-application-logo class="h-9 w-9 text-blue-600" />
                <span class="text-lg font-semibold text-slate-900" x-show="!desktopCollapsed" x-cloak>{{ $appName }}</span>
            </a>
            <button type="button" class="hidden h-9 w-9 items-center justify-center rounded-md border border-slate-200 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 md:inline-flex" @click="desktopCollapsed = !desktopCollapsed" :aria-expanded="(!desktopCollapsed).toString()" aria-label="Toggle sidebar width">
                <svg x-show="!desktopCollapsed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                <svg x-show="desktopCollapsed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
        <div class="flex flex-1 flex-col overflow-y-auto" :class="desktopCollapsed ? 'md:px-2' : 'md:px-4'">
            <nav class="mt-4 flex-1 pb-6" aria-label="Primary sidebar">
                @foreach ($navigationGroups as $group)
                    <div class="mb-6" :class="desktopCollapsed ? 'md:mb-4' : 'md:mb-7'">
                        <p class="px-1 text-xs font-semibold uppercase tracking-wider text-slate-400" x-show="!desktopCollapsed" x-cloak>{{ $group['label'] }}</p>
                        <div class="mt-2 space-y-1.5" :class="desktopCollapsed ? 'md:space-y-1' : 'md:space-y-1.5'">
                            @foreach ($group['items'] as $item)
                                @php
                                    $isActive = sidebar_item_is_active($item['patterns'] ?? $item['route']);
                                    $baseClasses = 'group relative flex items-center overflow-hidden rounded-lg py-2 text-sm font-medium transition-colors duration-150';
                                    $toneClasses = $isActive ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900';
                                @endphp
                                <a href="{{ route($item['route']) }}" class="{{ $baseClasses }} {{ $toneClasses }}" :class="desktopCollapsed ? 'justify-center px-2' : 'pl-5 pr-3 gap-3'" title="{{ $item['label'] }}">
                                    <span class="absolute left-0 top-1/2 h-8 w-1 -translate-y-1/2 rounded-r-full transition-colors duration-150 {{ $isActive ? 'bg-blue-600' : 'bg-transparent group-hover:bg-blue-200' }}" x-show="!desktopCollapsed" x-cloak></span>
                                    <span class="relative flex items-center justify-center rounded-md {{ $isActive ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-500 group-hover:bg-blue-50 group-hover:text-blue-600' }}" :class="desktopCollapsed ? 'h-10 w-10' : 'h-9 w-9'"><span class="sr-only">{{ $item['label'] }}</span>{!! render_sidebar_icon($item['icon'] ?? 'default') !!}</span>
                                    <span class="relative" x-show="!desktopCollapsed" x-cloak>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
            <div class="border-t border-slate-200 py-5" :class="desktopCollapsed ? 'md:px-2' : 'md:px-4'">
                <div class="flex items-center gap-3" :class="desktopCollapsed ? 'md:flex-col md:items-center md:gap-2' : ''">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white">
                        {{ $initials }}
                    </div>
                    <div class="min-w-0 text-center md:text-left" :class="desktopCollapsed ? 'md:hidden' : ''">
                        <p class="truncate text-sm font-semibold text-slate-900">{{ $user?->name }}</p>
                        @if ($user?->email)
                            <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                        @endif
                        <div class="mt-2 flex items-center gap-3 text-xs">
                            <a href="{{ route('profile') }}" class="font-medium text-blue-600 transition hover:text-blue-700">Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-slate-500 transition hover:text-slate-700">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex min-h-screen flex-1 flex-col transition-[padding] duration-200" :class="desktopCollapsed ? 'md:pl-20' : 'md:pl-64'">
        <div class="flex flex-1 flex-col">
            @php
                $hasComponentHeader = isset($header) && $header instanceof \Illuminate\View\ComponentSlot && ! $header->isEmpty();
                $sectionHeader = trim($__env->yieldContent('header'));
                $shouldRenderHeader = $hasComponentHeader || $sectionHeader !== '';
            @endphp

            @if ($shouldRenderHeader)
                <div class="border-b border-slate-200 bg-white/80 backdrop-blur">
                    @if ($hasComponentHeader)
                        {{ $header }}
                    @else
                        {!! $sectionHeader !!}
                    @endif
                </div>
            @endif

            <main class="flex-1">
                @if (isset($slot) && trim((string) $slot) !== '')
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </main>
        </div>
    </div>
</div>

