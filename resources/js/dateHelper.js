/**
 * Date Helper for DD/MM/YYYY format
 * Provides consistent date formatting across the application
 */

class DateHelper {
    /**
     * Format a date to DD/MM/YYYY
     */
    static format(date) {
        if (!date) return '';

        const d = new Date(date);
        if (isNaN(d.getTime())) return '';

        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();

        return `${day}/${month}/${year}`;
    }

    /**
     * Format a date to DD/MM/YYYY HH:MM
     */
    static formatWithTime(date) {
        if (!date) return '';

        const d = new Date(date);
        if (isNaN(d.getTime())) return '';

        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');

        return `${day}/${month}/${year} ${hours}:${minutes}`;
    }

    /**
     * Parse DD/MM/YYYY to Date object
     */
    static parse(dateString) {
        if (!dateString) return null;

        // Handle DD/MM/YYYY format
        if (dateString.includes('/')) {
            const parts = dateString.split('/');
            if (parts.length === 3) {
                const day = parseInt(parts[0], 10);
                const month = parseInt(parts[1], 10) - 1; // Month is 0-indexed
                const year = parseInt(parts[2], 10);

                const date = new Date(year, month, day);
                if (!isNaN(date.getTime())) {
                    return date;
                }
            }
        }

        // Fallback to native parsing
        const fallback = new Date(dateString);
        return !isNaN(fallback.getTime()) ? fallback : null;
    }

    /**
     * Get today's date in DD/MM/YYYY format
     */
    static today() {
        return this.format(new Date());
    }

    /**
     * Get current date and time in DD/MM/YYYY HH:MM format
     */
    static now() {
        return this.formatWithTime(new Date());
    }

    /**
     * Convert to HTML5 date input format (YYYY-MM-DD)
     */
    static toHtml5Input(date) {
        if (!date) return '';

        const d = new Date(date);
        if (isNaN(d.getTime())) return '';

        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    /**
     * Convert from HTML5 date input format (YYYY-MM-DD) to DD/MM/YYYY
     */
    static fromHtml5Input(dateString) {
        if (!dateString) return '';

        const date = new Date(dateString);
        return this.format(date);
    }

    /**
     * Validate DD/MM/YYYY format
     */
    static isValid(dateString) {
        return this.parse(dateString) !== null;
    }

    /**
     * Add days to a date
     */
    static addDays(date, days) {
        const result = new Date(date);
        result.setDate(result.getDate() + days);
        return result;
    }

    /**
     * Format date for display with fallback
     */
    static displayFormat(date, includeTime = false) {
        try {
            return includeTime ? this.formatWithTime(date) : this.format(date);
        } catch (error) {
            console.warn('Date formatting error:', error);
            return String(date);
        }
    }
}

// Make available globally
window.DateHelper = DateHelper;

// jQuery plugin for date inputs (if jQuery is available)
if (typeof jQuery !== 'undefined') {
    jQuery.fn.ddmmyyyy = function() {
        return this.each(function() {
            const $input = jQuery(this);

            // Format on blur
            $input.on('blur', function() {
                const value = $input.val();
                if (value && DateHelper.isValid(value)) {
                    $input.val(DateHelper.format(DateHelper.parse(value)));
                }
            });

            // Add placeholder if not present
            if (!$input.attr('placeholder')) {
                $input.attr('placeholder', 'DD/MM/YYYY');
            }
        });
    };
}

export default DateHelper;