<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | This file contains feature flags for the application. Feature flags
    | allow you to enable/disable features without deploying new code.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Enhanced Invoice & Quotation Builders
    |--------------------------------------------------------------------------
    |
    | These flags control the enhanced canvas-based builders for invoices
    | and quotations. When enabled, users will be redirected to the new
    | builder interfaces instead of the legacy forms.
    |
    */

    'invoice_builder_v2' => env('FEATURE_INVOICE_BUILDER_V2', false),
    'quotation_builder_v2' => env('FEATURE_QUOTATION_BUILDER_V2', false),

    /*
    |--------------------------------------------------------------------------
    | Builder Features
    |--------------------------------------------------------------------------
    |
    | Fine-grained control over specific builder features.
    |
    */

    'product_search_modal' => env('FEATURE_PRODUCT_SEARCH_MODAL', true),
    'service_template_browser' => env('FEATURE_SERVICE_TEMPLATE_BROWSER', true),
    'real_time_pricing' => env('FEATURE_REAL_TIME_PRICING', true),
    'client_suggestions' => env('FEATURE_CLIENT_SUGGESTIONS', true),
    'automatic_lead_creation' => env('FEATURE_AUTOMATIC_LEAD_CREATION', true),

    /*
    |--------------------------------------------------------------------------
    | Future Features
    |--------------------------------------------------------------------------
    |
    | Flags for upcoming features and enhancements.
    |
    */

    'advanced_analytics' => env('FEATURE_ADVANCED_ANALYTICS', false),
    'ai_recommendations' => env('FEATURE_AI_RECOMMENDATIONS', false),
    'mobile_app_api' => env('FEATURE_MOBILE_APP_API', false),

];