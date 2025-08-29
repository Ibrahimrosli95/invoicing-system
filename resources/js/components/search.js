import Alpine from 'alpinejs';

// Search component with typeahead/autocomplete functionality
Alpine.data('search', (options = {}) => ({
    query: '',
    results: [],
    loading: false,
    open: false,
    selectedIndex: -1,
    
    // Configuration
    minQueryLength: options.minQueryLength || 2,
    debounceMs: options.debounceMs || 300,
    maxResults: options.maxResults || 10,
    searchUrl: options.searchUrl || '/api/search',
    
    init() {
        // Debounced search
        this.$watch('query', () => {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.performSearch();
            }, this.debounceMs);
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.$root.contains(e.target)) {
                this.close();
            }
        });
    },
    
    async performSearch() {
        if (this.query.length < this.minQueryLength) {
            this.close();
            return;
        }
        
        this.loading = true;
        
        try {
            const response = await fetch(`${this.searchUrl}?q=${encodeURIComponent(this.query)}&limit=${this.maxResults}`);
            const data = await response.json();
            
            this.results = data.results || [];
            this.open = this.results.length > 0;
            this.selectedIndex = -1;
        } catch (error) {
            console.error('Search error:', error);
            this.results = [];
            this.open = false;
        } finally {
            this.loading = false;
        }
    },
    
    selectResult(result, index = -1) {
        this.selectedIndex = index;
        this.$dispatch('result-selected', { result });
        this.close();
    },
    
    close() {
        this.open = false;
        this.selectedIndex = -1;
    },
    
    // Keyboard navigation
    handleKeydown(event) {
        if (!this.open) return;
        
        switch (event.key) {
            case 'ArrowDown':
                event.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, this.results.length - 1);
                break;
            case 'ArrowUp':
                event.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                break;
            case 'Enter':
                event.preventDefault();
                if (this.selectedIndex >= 0) {
                    this.selectResult(this.results[this.selectedIndex], this.selectedIndex);
                }
                break;
            case 'Escape':
                event.preventDefault();
                this.close();
                break;
        }
    },
    
    clearSearch() {
        this.query = '';
        this.results = [];
        this.close();
    }
}));