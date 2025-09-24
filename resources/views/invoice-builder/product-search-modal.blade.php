<!-- Product Search Modal -->
<div x-show="showProductSearch"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
        <div class="fixed inset-0 transition-opacity" @click="showProductSearch = false">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full p-0"
             @click.stop>

            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Add Products to Invoice</h3>
                    <p class="text-sm text-gray-500">Search and select products from your pricing book</p>
                </div>
                <button @click="showProductSearch = false"
                        class="text-gray-400 hover:text-gray-600 p-2">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Search and Filters -->
            <div class="p-6 border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search Input -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Products</label>
                        <div class="relative">
                            <input type="text"
                                   x-model="productSearch.query"
                                   @input="searchProducts()"
                                   placeholder="Search by name, item code, or description..."
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select x-model="productSearch.category"
                                @change="searchProducts()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Categories</option>
                            <option value="paint">Paint & Coatings</option>
                            <option value="tools">Tools & Equipment</option>
                            <option value="hardware">Hardware</option>
                            <option value="accessories">Accessories</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div class="max-h-96 overflow-y-auto">
                <!-- Loading State -->
                <div x-show="productSearch.loading" class="p-8 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <p class="mt-2 text-sm text-gray-500">Searching products...</p>
                </div>

                <!-- Empty State -->
                <div x-show="!productSearch.loading && productSearch.results.length === 0 && productSearch.query"
                     class="p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.5-.98-6.084-2.563C4.658 10.83 3.592 9.005 3.317 7.089a.75.75 0 01-.09-.35 4.5 4.5 0 016.09 3.508"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No products found matching your search</p>
                </div>

                <!-- Product Results -->
                <div x-show="!productSearch.loading" class="divide-y divide-gray-200">
                    <template x-for="product in productSearch.results" :key="product.id">
                        <div class="p-4 hover:bg-gray-50 cursor-pointer"
                             @click="selectProduct(product)">
                            <div class="flex items-center justify-between">
                                <!-- Product Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-3">
                                        <!-- Product Image -->
                                        <div class="flex-shrink-0 w-12 h-12">
                                            <template x-if="product.image_url">
                                                <img :src="product.image_url"
                                                     :alt="product.name"
                                                     class="w-12 h-12 rounded-lg object-cover border border-gray-200">
                                            </template>
                                            <template x-if="!product.image_url">
                                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200">
                                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                                    </svg>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Product Details -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2">
                                                <h4 class="text-sm font-medium text-gray-900 truncate" x-text="product.name"></h4>
                                                <template x-if="product.item_code">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800" x-text="product.item_code"></span>
                                                </template>
                                            </div>

                                            <p class="text-sm text-gray-500 mt-1" x-text="product.category_path || product.category_name"></p>

                                            <template x-if="product.specifications">
                                                <p class="text-xs text-gray-400 mt-1 line-clamp-2" x-text="product.specifications"></p>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pricing Info -->
                                <div class="ml-4 flex-shrink-0 text-right">
                                    <div class="text-lg font-medium text-gray-900">
                                        <span x-text="'RM ' + getProductPrice(product).toFixed(2)"></span>
                                    </div>

                                    <!-- Unit -->
                                    <div class="text-sm text-gray-500">
                                        per <span x-text="product.unit || 'unit'"></span>
                                    </div>

                                    <!-- Tier Pricing Indicator -->
                                    <template x-if="product.has_tier_pricing && invoice.customer_segment_id">
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Bulk pricing available
                                            </span>
                                        </div>
                                    </template>

                                    <!-- Stock Info -->
                                    <template x-if="product.track_stock">
                                        <div class="mt-1 text-xs" :class="product.stock_quantity > 10 ? 'text-green-600' : 'text-orange-600'">
                                            <span x-text="product.stock_quantity + ' in stock'"></span>
                                        </div>
                                    </template>
                                </div>

                                <!-- Add Button -->
                                <div class="ml-4 flex-shrink-0">
                                    <button @click.stop="addProductToInvoice(product)"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Add
                                    </button>
                                </div>
                            </div>

                            <!-- Tier Pricing Details -->
                            <template x-if="product.tier_info && product.tier_info.length > 0">
                                <div class="mt-3 pl-15">
                                    <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                                        <h5 class="text-xs font-medium text-blue-900 mb-2">Quantity Discounts Available:</h5>
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                            <template x-for="tier in product.tier_info.slice(0, 3)" :key="tier.id">
                                                <div class="text-xs bg-white rounded px-2 py-1 border border-blue-200">
                                                    <span class="font-medium" x-text="tier.range"></span>:
                                                    <span class="text-blue-700" x-text="'RM ' + tier.price.toFixed(2)"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="text-sm text-gray-500">
                    <span x-text="productSearch.results.length"></span> products found
                </div>
                <button @click="showProductSearch = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Product Search Methods (Merged into main Alpine.js component)
window.productSearchMethods = {
    productSearch: {
        query: '',
        category: '',
        loading: false,
        results: []
    },

    searchProducts() {
        if (this.productSearch.query.length < 2 && !this.productSearch.category) {
            this.productSearch.results = [];
            return;
        }

        this.productSearch.loading = true;

        const params = new URLSearchParams();
        if (this.productSearch.query) params.append('q', this.productSearch.query);
        if (this.productSearch.category) params.append('category', this.productSearch.category);
        if (this.invoice.customer_segment_id) params.append('segment_id', this.invoice.customer_segment_id);

        fetch(`/api/pricing-items/search?${params}`)
            .then(response => response.json())
            .then(data => {
                this.productSearch.results = data.map(item => {
                    // Calculate segment-specific pricing
                    const basePrice = parseFloat(item.unit_price);
                    let segmentPrice = basePrice;

                    if (this.invoice.customer_segment_id && item.segment_pricing) {
                        segmentPrice = parseFloat(item.segment_pricing.unit_price || basePrice);
                    }

                    return {
                        ...item,
                        base_price: basePrice,
                        segment_price: segmentPrice,
                        tier_info: item.tier_pricing || []
                    };
                });
                this.productSearch.loading = false;
            })
            .catch(error => {
                console.error('Product search error:', error);
                this.productSearch.loading = false;
            });
    },

    getProductPrice(product) {
        // Return segment-specific price if available, otherwise base price
        return this.invoice.customer_segment_id ?
               (product.segment_price || product.base_price || product.unit_price) :
               (product.base_price || product.unit_price);
    },

    selectProduct(product) {
        // For detailed product view or additional customization
        this.addProductToInvoice(product);
    },

    addProductToInvoice(product) {
        const price = this.getProductPrice(product);

        // Check if product already exists in invoice
        const existingIndex = this.invoice.items.findIndex(item =>
            item.item_code === product.item_code ||
            (item.description === product.name && item.unit_price === price)
        );

        if (existingIndex >= 0) {
            // Increment quantity if product already exists
            this.invoice.items[existingIndex].quantity += 1;
            this.calculateItemTotal(existingIndex);
        } else {
            // Add new product to invoice
            const newItem = {
                description: product.name,
                item_code: product.item_code || '',
                unit: product.unit || 'pcs',
                quantity: 1,
                unit_price: price,
                base_unit_price: product.unit_price, // Store original price
                pricing_item_id: product.id,
                specifications: product.specifications || '',
                source_type: 'pricing_item',
                source_id: product.id
            };

            this.invoice.items.push(newItem);
            this.calculateTotals();
        }

        // Show success feedback
        this.showProductAddedFeedback(product.name);
    },

    showProductAddedFeedback(productName) {
        // Simple toast notification
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg z-50';
        toast.textContent = `${productName} added to invoice`;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
};
</script>