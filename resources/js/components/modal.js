import Alpine from 'alpinejs';

// Modal component for dialogs and forms
Alpine.data('modal', (initialOpen = false) => ({
    open: initialOpen,
    
    init() {
        // Handle modal opening/closing
        this.$watch('open', (value) => {
            if (value) {
                document.body.classList.add('overflow-hidden');
                this.$nextTick(() => {
                    this.$refs.modal && this.$refs.modal.focus();
                });
            } else {
                document.body.classList.remove('overflow-hidden');
            }
        });
        
        // Close on escape key
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && this.open) {
                this.close();
            }
        });
    },
    
    show() {
        this.open = true;
    },
    
    close() {
        this.open = false;
    },
    
    // Close when clicking backdrop
    closeOnBackdrop(event) {
        if (event.target === event.currentTarget) {
            this.close();
        }
    }
}));