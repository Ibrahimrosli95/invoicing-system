import './bootstrap';
import './dateHelper';

import Alpine from 'alpinejs';

// Add DateHelper to Alpine global data
document.addEventListener('alpine:init', () => {
    Alpine.store('dateHelper', window.DateHelper);
});

window.Alpine = Alpine;

Alpine.start();

// Initialize date inputs when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all date inputs with DD/MM/YYYY format
    const dateInputs = document.querySelectorAll('input[type="text"][data-date-format="dd/mm/yyyy"]');
    dateInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value && window.DateHelper.isValid(value)) {
                this.value = window.DateHelper.format(window.DateHelper.parse(value));
            }
        });

        if (!input.placeholder) {
            input.placeholder = 'DD/MM/YYYY';
        }
    });

    // Add date format hints
    const dateHints = document.querySelectorAll('.date-format-hint');
    dateHints.forEach(hint => {
        hint.textContent = 'Format: DD/MM/YYYY';
    });
});

// Add global helper functions
window.formatDate = function(date) {
    return window.DateHelper.format(date);
};

window.formatDateTime = function(date) {
    return window.DateHelper.formatWithTime(date);
};

window.parseDate = function(dateString) {
    return window.DateHelper.parse(dateString);
};

window.todayFormatted = function() {
    return window.DateHelper.today();
};

window.nowFormatted = function() {
    return window.DateHelper.now();
};
