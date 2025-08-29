import Alpine from 'alpinejs';

// Date picker component (placeholder - will be enhanced with a library later)
Alpine.data('datePicker', (initialDate = null) => ({
    date: initialDate,
    open: false,
    
    init() {
        // Initialize date picker
        // This will be enhanced with a proper date picker library
    },
    
    toggle() {
        this.open = !this.open;
    },
    
    selectDate(date) {
        this.date = date;
        this.open = false;
        this.$dispatch('date-selected', { date });
    },
    
    clear() {
        this.date = null;
        this.$dispatch('date-selected', { date: null });
    },
    
    formatDate(date) {
        if (!date) return '';
        return new Date(date).toLocaleDateString();
    }
}));