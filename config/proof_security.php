<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Proof Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the proof security system
    | including access controls, retention policies, and compliance settings.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Security Levels & Clearance
    |--------------------------------------------------------------------------
    */
    'security_levels' => [
        'public' => 0,
        'internal' => 1,
        'confidential' => 2,
        'restricted' => 3,
        'highly_confidential' => 4,
    ],

    'role_clearance_mapping' => [
        'superadmin' => 'highly_confidential',
        'company_manager' => 'restricted',
        'finance_manager' => 'confidential',
        'sales_manager' => 'confidential',
        'sales_coordinator' => 'internal',
        'sales_executive' => 'internal',
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Retention Policies
    |--------------------------------------------------------------------------
    */
    'retention' => [
        'default_periods' => [
            'draft' => 6,           // months
            'active' => 24,         // months
            'archived' => 12,       // months
            'consent_revoked' => 3, // months
            'view_logs' => 12,      // months
            'assets' => 24,         // months
        ],
        
        'auto_cleanup_enabled' => env('PROOF_AUTO_CLEANUP', false),
        'cleanup_schedule' => '0 2 * * 0', // Weekly at 2 AM Sunday
        'notification_before_deletion' => 7, // days
    ],

    /*
    |--------------------------------------------------------------------------
    | Consent Management
    |--------------------------------------------------------------------------
    */
    'consent' => [
        'required_for_types' => [
            'testimonial',
            'case_study',
            'client_review',
            'social_proof',
        ],
        
        'consent_validity_years' => 2,
        'expiry_notification_days' => [30, 14, 7, 1],
        'auto_revoke_on_request' => true,
        
        'consent_token' => [
            'length' => 32,
            'algorithm' => 'sha256',
            'validity_days' => 30,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Access Restrictions
    |--------------------------------------------------------------------------
    */
    'access_restrictions' => [
        'ip_whitelist_enabled' => env('PROOF_IP_WHITELIST', false),
        'time_restrictions_enabled' => env('PROOF_TIME_RESTRICTIONS', false),
        'device_restrictions_enabled' => env('PROOF_DEVICE_RESTRICTIONS', false),
        'view_limits_enabled' => env('PROOF_VIEW_LIMITS', false),
        
        'max_concurrent_views' => 5,
        'session_timeout_minutes' => 30,
        'token_validity_hours' => 24,
        
        'watermarking' => [
            'enabled' => env('PROOF_WATERMARKING', true),
            'auto_apply_levels' => ['restricted', 'highly_confidential'],
            'text' => 'CONFIDENTIAL - {company} - {timestamp}',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sensitive Data Detection
    |--------------------------------------------------------------------------
    */
    'sensitive_data' => [
        'auto_classification' => env('PROOF_AUTO_CLASSIFY', true),
        
        'patterns' => [
            'credit_card' => '/\b\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}\b/',
            'phone_number' => '/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/',
            'email_address' => '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/',
            'currency_amount' => '/\$\d+(?:,\d{3})*(?:\.\d{2})?|\b\d+(?:,\d{3})*(?:\.\d{2})?\s*(?:USD|MYR|RM)\b/',
            'ic_number' => '/\b\d{6}-\d{2}-\d{4}\b/', // Malaysian IC format
            'passport_number' => '/\b[A-Z]\d{8}\b/',
        ],
        
        'classification_rules' => [
            'contains_pii' => ['phone_number', 'email_address', 'ic_number', 'passport_number'],
            'financial_data' => ['credit_card', 'currency_amount'],
            'auto_restrict' => ['credit_card', 'ic_number', 'passport_number'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Approval Workflows
    |--------------------------------------------------------------------------
    */
    'approval' => [
        'enabled' => env('PROOF_APPROVAL_REQUIRED', false),
        'auto_approve_for_roles' => ['company_manager', 'superadmin'],
        
        'workflow_types' => [
            'single_approver' => 'Single Approver',
            'sequential' => 'Sequential Approval',
            'parallel' => 'Parallel Approval',
        ],
        
        'default_deadline_days' => 5,
        'escalation_enabled' => true,
        'escalation_after_days' => 3,
        
        'require_approval_for' => [
            'high_security_levels' => ['restricted', 'highly_confidential'],
            'sensitive_types' => ['testimonial', 'case_study'],
            'external_sharing' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit & Logging
    |--------------------------------------------------------------------------
    */
    'audit' => [
        'enabled' => env('PROOF_AUDIT_ENABLED', true),
        'log_all_access' => env('PROOF_LOG_ALL_ACCESS', true),
        'log_ip_addresses' => env('PROOF_LOG_IP', true),
        'log_user_agents' => env('PROOF_LOG_USER_AGENT', true),
        
        'events_to_log' => [
            'created', 'updated', 'deleted', 'published', 'archived',
            'viewed', 'downloaded', 'shared',
            'consent_granted', 'consent_revoked',
            'approval_submitted', 'approval_granted', 'approval_rejected',
            'asset_uploaded', 'asset_deleted',
            'data_exported', 'data_anonymized',
        ],
        
        'retention_months' => 24,
        'export_format' => 'json',
        'compress_exports' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Monitoring
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'real_time_alerts' => env('PROOF_REAL_TIME_ALERTS', false),
        'alert_channels' => ['log', 'email'], // 'slack', 'webhook'
        
        'alert_triggers' => [
            'failed_access_attempts' => 5,     // per user per hour
            'bulk_downloads' => 10,            // per user per hour
            'suspicious_ip_activity' => 3,     // different IPs per user per hour
            'consent_violations' => 1,         // any violation
            'security_level_bypass' => 1,      // any attempt
        ],
        
        'rate_limiting' => [
            'max_views_per_hour' => 100,
            'max_downloads_per_hour' => 20,
            'max_shares_per_day' => 10,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | GDPR & Compliance
    |--------------------------------------------------------------------------
    */
    'compliance' => [
        'gdpr_enabled' => env('PROOF_GDPR_ENABLED', true),
        'auto_anonymize_after_deletion' => true,
        'export_user_data_on_request' => true,
        
        'data_subject_rights' => [
            'right_to_access' => true,         // Article 15
            'right_to_rectification' => true,  // Article 16
            'right_to_erasure' => true,        // Article 17
            'right_to_portability' => true,    // Article 20
            'right_to_object' => true,         // Article 21
        ],
        
        'legal_basis_tracking' => true,
        'consent_withdrawal_process' => 'immediate', // 'immediate', 'scheduled'
        'data_breach_notification' => env('PROOF_BREACH_NOTIFICATION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance & Optimization
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'cache_security_checks' => true,
        'cache_ttl_minutes' => 15,
        
        'lazy_load_assets' => true,
        'compress_audit_logs' => true,
        'batch_size_cleanup' => 100,
        
        'background_processing' => [
            'enabled' => true,
            'queue' => 'proof-security',
            'timeout' => 300, // 5 minutes
            'max_attempts' => 3,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Security
    |--------------------------------------------------------------------------
    */
    'files' => [
        'secure_deletion' => [
            'method' => 'overwrite', // 'overwrite', 'standard'
            'overwrite_passes' => 3,
            'verify_deletion' => true,
        ],
        
        'encryption' => [
            'enabled' => env('PROOF_FILE_ENCRYPTION', false),
            'algorithm' => 'AES-256-GCM',
            'key_rotation_days' => 90,
        ],
        
        'virus_scanning' => [
            'enabled' => env('PROOF_VIRUS_SCAN', false),
            'quarantine_infected' => true,
            'scan_on_upload' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Integration Settings
    |--------------------------------------------------------------------------
    */
    'integrations' => [
        'webhook_security_events' => env('PROOF_WEBHOOK_SECURITY', false),
        'external_audit_systems' => env('PROOF_EXTERNAL_AUDIT', false),
        
        'notification_channels' => [
            'email' => [
                'enabled' => true,
                'template_prefix' => 'proof.security.',
            ],
            'slack' => [
                'enabled' => env('PROOF_SLACK_NOTIFICATIONS', false),
                'webhook_url' => env('PROOF_SLACK_WEBHOOK'),
            ],
        ],
    ],
];