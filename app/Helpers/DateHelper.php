<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class DateHelper
{
    /**
     * Format a date for display using DD/MM/YYYY format.
     *
     * @param mixed $date
     * @param bool $includeTime
     * @return string
     */
    public static function format($date, bool $includeTime = false): string
    {
        if (!$date) {
            return '';
        }

        $carbon = Carbon::parse($date);
        $format = $includeTime
            ? Config::get('dates.display_format_with_time', 'd/m/Y H:i')
            : Config::get('dates.display_format', 'd/m/Y');

        return $carbon->format($format);
    }

    /**
     * Format a date for form input (DD/MM/YYYY).
     *
     * @param mixed $date
     * @return string
     */
    public static function formatForInput($date): string
    {
        if (!$date) {
            return '';
        }

        $carbon = Carbon::parse($date);
        return $carbon->format(Config::get('dates.input_format', 'd/m/Y'));
    }

    /**
     * Parse a DD/MM/YYYY date string for database storage.
     *
     * @param string $dateString
     * @return Carbon|null
     */
    public static function parseFromInput(string $dateString): ?Carbon
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            // Try to parse DD/MM/YYYY format
            return Carbon::createFromFormat('d/m/Y', $dateString)->startOfDay();
        } catch (\Exception $e) {
            // Fallback to Carbon's automatic parsing
            try {
                return Carbon::parse($dateString);
            } catch (\Exception $fallbackException) {
                return null;
            }
        }
    }

    /**
     * Parse a DD/MM/YYYY HH:MM date string for database storage.
     *
     * @param string $dateString
     * @return Carbon|null
     */
    public static function parseFromInputWithTime(string $dateString): ?Carbon
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            // Try to parse DD/MM/YYYY HH:MM format
            return Carbon::createFromFormat('d/m/Y H:i', $dateString);
        } catch (\Exception $e) {
            // Try without time
            try {
                return Carbon::createFromFormat('d/m/Y', $dateString)->startOfDay();
            } catch (\Exception $fallbackException) {
                // Final fallback to Carbon's automatic parsing
                try {
                    return Carbon::parse($dateString);
                } catch (\Exception $finalException) {
                    return null;
                }
            }
        }
    }

    /**
     * Get the JavaScript-compatible date format.
     *
     * @param bool $includeTime
     * @return string
     */
    public static function getJavaScriptFormat(bool $includeTime = false): string
    {
        return $includeTime
            ? Config::get('dates.js_format_with_time', 'DD/MM/YYYY HH:mm')
            : Config::get('dates.js_format', 'DD/MM/YYYY');
    }

    /**
     * Format date for JavaScript consumption.
     *
     * @param mixed $date
     * @param bool $includeTime
     * @return string
     */
    public static function formatForJavaScript($date, bool $includeTime = false): string
    {
        if (!$date) {
            return '';
        }

        $carbon = Carbon::parse($date);

        if ($includeTime) {
            return $carbon->format('d/m/Y H:i');
        }

        return $carbon->format('d/m/Y');
    }

    /**
     * Convert date to HTML5 date input format (YYYY-MM-DD).
     *
     * @param mixed $date
     * @return string
     */
    public static function formatForHtml5Input($date): string
    {
        if (!$date) {
            return '';
        }

        $carbon = Carbon::parse($date);
        return $carbon->format('Y-m-d');
    }

    /**
     * Format a date range for display.
     *
     * @param mixed $startDate
     * @param mixed $endDate
     * @param bool $includeTime
     * @return string
     */
    public static function formatRange($startDate, $endDate, bool $includeTime = false): string
    {
        $start = self::format($startDate, $includeTime);
        $end = self::format($endDate, $includeTime);

        if (!$start && !$end) {
            return '';
        }

        if (!$start) {
            return "Until {$end}";
        }

        if (!$end) {
            return "From {$start}";
        }

        return "{$start} - {$end}";
    }

    /**
     * Get relative time (e.g., "2 days ago") with DD/MM/YYYY fallback.
     *
     * @param mixed $date
     * @return string
     */
    public static function formatRelative($date): string
    {
        if (!$date) {
            return '';
        }

        $carbon = Carbon::parse($date);

        // If date is within last 7 days, show relative time
        if ($carbon->diffInDays(now()) <= 7) {
            return $carbon->diffForHumans();
        }

        // Otherwise show DD/MM/YYYY format
        return $carbon->format('d/m/Y');
    }

    /**
     * Validate DD/MM/YYYY date format.
     *
     * @param string $dateString
     * @return bool
     */
    public static function validateFormat(string $dateString): bool
    {
        if (empty($dateString)) {
            return false;
        }

        try {
            $date = Carbon::createFromFormat('d/m/Y', $dateString);
            return $date && $date->format('d/m/Y') === $dateString;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get today's date in DD/MM/YYYY format.
     *
     * @return string
     */
    public static function today(): string
    {
        return Carbon::today()->format('d/m/Y');
    }

    /**
     * Get current date and time in DD/MM/YYYY HH:MM format.
     *
     * @return string
     */
    public static function now(): string
    {
        return Carbon::now()->format('d/m/Y H:i');
    }
}