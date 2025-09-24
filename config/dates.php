<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Date Display Format
    |--------------------------------------------------------------------------
    |
    | These formats will be used throughout the application for displaying dates.
    | The format follows PHP's date() function format.
    |
    */

    'display_format' => 'd/m/Y',
    'display_format_with_time' => 'd/m/Y H:i',
    'display_format_long' => 'd/m/Y H:i:s',

    /*
    |--------------------------------------------------------------------------
    | Date Input Format
    |--------------------------------------------------------------------------
    |
    | These formats will be used for form inputs and data parsing.
    |
    */

    'input_format' => 'd/m/Y',
    'input_format_with_time' => 'd/m/Y H:i',

    /*
    |--------------------------------------------------------------------------
    | Database Storage Format
    |--------------------------------------------------------------------------
    |
    | Laravel uses these formats for database storage (should remain standard).
    |
    */

    'storage_format' => 'Y-m-d',
    'storage_format_with_time' => 'Y-m-d H:i:s',

    /*
    |--------------------------------------------------------------------------
    | JavaScript Date Format
    |--------------------------------------------------------------------------
    |
    | Format used in JavaScript/frontend components.
    |
    */

    'js_format' => 'DD/MM/YYYY',
    'js_format_with_time' => 'DD/MM/YYYY HH:mm',

    /*
    |--------------------------------------------------------------------------
    | Carbon Date Format Masks
    |--------------------------------------------------------------------------
    |
    | Format masks for Carbon date manipulation.
    |
    */

    'carbon_format' => 'd/m/Y',
    'carbon_format_with_time' => 'd/m/Y H:i',
];