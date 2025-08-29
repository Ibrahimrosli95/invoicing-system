import Alpine from 'alpinejs';

// Notification/Toast component
Alpine.data('notification', () => ({
    notifications: [],
    
    // Add a notification
    add(message, type = 'info', duration = 5000) {
        const id = Date.now();
        const notification = {
            id,
            message,
            type, // success, error, warning, info
            duration
        };
        
        this.notifications.push(notification);
        
        // Auto-remove after duration
        if (duration > 0) {
            setTimeout(() => {
                this.remove(id);
            }, duration);
        }
        
        return id;
    },
    
    // Remove a notification
    remove(id) {
        const index = this.notifications.findIndex(n => n.id === id);
        if (index > -1) {
            this.notifications.splice(index, 1);
        }
    },
    
    // Clear all notifications
    clear() {
        this.notifications = [];
    },
    
    // Helper methods for different types
    success(message, duration = 5000) {
        return this.add(message, 'success', duration);
    },
    
    error(message, duration = 8000) {
        return this.add(message, 'error', duration);
    },
    
    warning(message, duration = 6000) {
        return this.add(message, 'warning', duration);
    },
    
    info(message, duration = 5000) {
        return this.add(message, 'info', duration);
    }
}));