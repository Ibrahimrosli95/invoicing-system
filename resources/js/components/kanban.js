import Alpine from 'alpinejs';

// Kanban board component for leads pipeline
Alpine.data('kanban', (initialData = {}) => ({
    columns: initialData.columns || [],
    draggedItem: null,
    draggedFromColumn: null,
    
    init() {
        // Set up drag and drop event listeners
        this.$nextTick(() => {
            this.setupDragAndDrop();
        });
    },
    
    setupDragAndDrop() {
        // This will be enhanced when we add sortable.js or implement custom drag/drop
    },
    
    // Handle item drag start
    handleDragStart(item, columnIndex, event) {
        this.draggedItem = item;
        this.draggedFromColumn = columnIndex;
        event.dataTransfer.effectAllowed = 'move';
    },
    
    // Handle drop on column
    handleDrop(columnIndex, event) {
        event.preventDefault();
        
        if (this.draggedItem && this.draggedFromColumn !== null) {
            // Remove from source column
            const sourceColumn = this.columns[this.draggedFromColumn];
            const itemIndex = sourceColumn.items.findIndex(item => item.id === this.draggedItem.id);
            if (itemIndex > -1) {
                sourceColumn.items.splice(itemIndex, 1);
            }
            
            // Add to target column
            const targetColumn = this.columns[columnIndex];
            targetColumn.items.push(this.draggedItem);
            
            // Update item status
            this.draggedItem.status = targetColumn.status;
            
            // Emit event for server sync
            this.$dispatch('item-moved', {
                item: this.draggedItem,
                fromColumn: this.draggedFromColumn,
                toColumn: columnIndex,
                newStatus: targetColumn.status
            });
        }
        
        this.clearDragState();
    },
    
    handleDragOver(event) {
        event.preventDefault();
    },
    
    clearDragState() {
        this.draggedItem = null;
        this.draggedFromColumn = null;
    },
    
    // Add new item to column
    addItem(columnIndex, item) {
        this.columns[columnIndex].items.push(item);
    },
    
    // Remove item from column
    removeItem(columnIndex, itemId) {
        const column = this.columns[columnIndex];
        const index = column.items.findIndex(item => item.id === itemId);
        if (index > -1) {
            column.items.splice(index, 1);
        }
    },
    
    // Get item count for a column
    getColumnCount(columnIndex) {
        return this.columns[columnIndex]?.items?.length || 0;
    }
}));