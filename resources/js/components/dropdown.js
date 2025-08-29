import Alpine from 'alpinejs';

// Dropdown component for navigation and actions
Alpine.data('dropdown', () => ({
    open: false,
    
    init() {
        // Close dropdown when clicking outside
        this.$watch('open', value => {
            if (value) {
                this.$nextTick(() => {
                    this.$refs.panel.focus();
                });
            }
        });
    },
    
    toggle() {
        this.open = !this.open;
    },
    
    close() {
        this.open = false;
    },
    
    // Handle keyboard navigation
    handleKeydown(event) {
        if (event.key === 'Escape') {
            this.close();
        }
    }
}));