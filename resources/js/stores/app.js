import Alpine from 'alpinejs';

// Global application store
Alpine.store('app', {
    // User and company information
    user: null,
    company: null,
    
    // UI state
    sidebarOpen: false,
    notifications: [],
    loading: false,
    
    // Theme and preferences  
    theme: localStorage.getItem('theme') || 'light',
    language: localStorage.getItem('language') || 'en',
    
    // Initialize the store
    init() {
        // Load user data from meta tags or API
        this.loadInitialData();
        
        // Apply saved theme
        this.applyTheme();
        
        // Set up notification system
        this.setupNotifications();
    },
    
    // Load initial data
    loadInitialData() {
        // Get data from meta tags set by Blade templates
        const userMeta = document.querySelector('meta[name="user-data"]');
        const companyMeta = document.querySelector('meta[name="company-data"]');
        
        if (userMeta) {
            try {
                this.user = JSON.parse(userMeta.getAttribute('content'));
            } catch (e) {
                console.warn('Failed to parse user data from meta tag');
            }
        }
        
        if (companyMeta) {
            try {
                this.company = JSON.parse(companyMeta.getAttribute('content'));
            } catch (e) {
                console.warn('Failed to parse company data from meta tag');
            }
        }
    },
    
    // Theme management
    setTheme(theme) {
        this.theme = theme;
        localStorage.setItem('theme', theme);
        this.applyTheme();
    },
    
    applyTheme() {
        if (this.theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    },
    
    // Sidebar management
    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
    },
    
    closeSidebar() {
        this.sidebarOpen = false;
    },
    
    // Notification system
    setupNotifications() {
        // Listen for server-sent events or Laravel Echo events
        // This will be enhanced when we add real-time notifications
    },
    
    addNotification(message, type = 'info', duration = 5000) {
        const id = Date.now();
        const notification = { id, message, type, duration };
        
        this.notifications.push(notification);
        
        if (duration > 0) {
            setTimeout(() => {
                this.removeNotification(id);
            }, duration);
        }
        
        return id;
    },
    
    removeNotification(id) {
        const index = this.notifications.findIndex(n => n.id === id);
        if (index > -1) {
            this.notifications.splice(index, 1);
        }
    },
    
    // Loading state
    setLoading(loading) {
        this.loading = loading;
    },
    
    // Helper methods
    can(permission) {
        return this.user?.permissions?.includes(permission) || false;
    },
    
    hasRole(role) {
        return this.user?.roles?.includes(role) || false;
    },
    
    getCompanySetting(key, defaultValue = null) {
        return this.company?.settings?.[key] || defaultValue;
    },
    
    getUserPreference(key, defaultValue = null) {
        return this.user?.preferences?.[key] || defaultValue;
    }
});