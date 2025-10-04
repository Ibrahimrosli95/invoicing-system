<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lead Contact Transparency Tracking
    |--------------------------------------------------------------------------
    |
    | This configuration controls the lead contact tracking system.
    | When enabled, the system tracks all sales rep interactions with leads
    | and provides warnings about duplicate contacts and price wars.
    |
    | Set 'enabled' to false to disable the entire tracking system.
    |
    */

    'enabled' => env('LEAD_TRACKING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Contact Tracking Settings
    |--------------------------------------------------------------------------
    |
    | These settings control how contact tracking works.
    |
    */

    'contact_tracking' => [
        // Record every rep interaction with a lead
        'track_contacts' => env('LEAD_TRACK_CONTACTS', true),

        // Record quote amounts for price comparison
        'track_quote_amounts' => env('LEAD_TRACK_QUOTES', true),

        // Show warning when opening already-contacted lead
        'show_duplicate_warning' => env('LEAD_SHOW_DUPLICATE_WARNING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Price War Detection
    |--------------------------------------------------------------------------
    |
    | Detect when sales reps are undercutting each other significantly.
    |
    */

    'price_war_detection' => [
        // Enable price war detection
        'enabled' => env('LEAD_PRICE_WAR_DETECTION', true),

        // Price drop threshold percentage to trigger alert
        // Example: 15 means alert if new quote is 15% or more below previous quote
        'threshold_percentage' => env('LEAD_PRICE_WAR_THRESHOLD', 15),

        // Automatically flag lead for manager review
        'auto_flag_for_review' => env('LEAD_AUTO_FLAG_PRICE_WAR', true),

        // Send notification to manager immediately
        'notify_manager' => env('LEAD_NOTIFY_MANAGER_PRICE_WAR', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Minimum Price Floor (Optional)
    |--------------------------------------------------------------------------
    |
    | Set minimum prices for different service categories.
    | Quotes below these minimums require manager approval.
    |
    | Set to null or 0 to disable price floor enforcement.
    |
    */

    'price_floors' => [
        // Enable minimum price floor enforcement
        'enabled' => env('LEAD_PRICE_FLOOR_ENABLED', false),

        // Minimum prices by service category (add your categories)
        // Example: 'waterproofing' => 10000,
        'minimums' => [
            // 'waterproofing' => env('PRICE_FLOOR_WATERPROOFING', 0),
            // 'painting' => env('PRICE_FLOOR_PAINTING', 0),
            // 'flooring' => env('PRICE_FLOOR_FLOORING', 0),
        ],

        // Require manager approval for quotes below minimum
        'require_manager_approval' => env('LEAD_REQUIRE_APPROVAL_BELOW_FLOOR', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Manager Alerts
    |--------------------------------------------------------------------------
    |
    | Configure when and how managers are alerted about lead issues.
    |
    */

    'manager_alerts' => [
        // Alert on multiple quotes for same customer
        'multiple_quotes' => env('LEAD_ALERT_MULTIPLE_QUOTES', true),

        // Minimum number of quotes to trigger alert (e.g., 2+ quotes)
        'multiple_quotes_threshold' => env('LEAD_MULTIPLE_QUOTES_THRESHOLD', 2),

        // Alert on price wars
        'price_wars' => env('LEAD_ALERT_PRICE_WARS', true),

        // Alert on quotes below price floor
        'below_price_floor' => env('LEAD_ALERT_BELOW_FLOOR', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Widgets
    |--------------------------------------------------------------------------
    |
    | Control which transparency tracking widgets appear on dashboards.
    |
    */

    'dashboard' => [
        // Show "Price War Alerts" widget on manager dashboard
        'show_price_war_widget' => env('LEAD_DASHBOARD_PRICE_WAR_WIDGET', true),

        // Show "Multiple Quotes" widget on manager dashboard
        'show_multiple_quotes_widget' => env('LEAD_DASHBOARD_MULTIPLE_QUOTES_WIDGET', true),

        // Show "Flagged Leads" widget on coordinator dashboard
        'show_flagged_leads_widget' => env('LEAD_DASHBOARD_FLAGGED_WIDGET', true),

        // Number of recent alerts to show in widgets
        'recent_alerts_limit' => env('LEAD_DASHBOARD_ALERTS_LIMIT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rep Warnings
    |--------------------------------------------------------------------------
    |
    | Configure warnings shown to sales reps.
    |
    */

    'rep_warnings' => [
        // Show warning when opening lead contacted by another rep
        'show_duplicate_contact_warning' => env('LEAD_WARN_DUPLICATE_CONTACT', true),

        // Show previous quote amounts to reps
        'show_previous_quotes' => env('LEAD_SHOW_PREVIOUS_QUOTES', true),

        // Suggest coordination with other reps
        'suggest_coordination' => env('LEAD_SUGGEST_COORDINATION', true),

        // Block quote submission if below minimum (when price floor enabled)
        'block_below_minimum' => env('LEAD_BLOCK_BELOW_MINIMUM', false),
    ],

];
