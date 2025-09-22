<!-- Product Search Modal -->
<div x-show="showProductSearch"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">

    <!-- Background overlay -->
    <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
             @click="showProductSearch = false"></div>

        <!-- Modal panel -->
        <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl sm:align-middle"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

            <!-- Modal Header -->
            <div class="bg-white px-6 pt-6 pb-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Add Products from Pricing Book</h3>
                    <button type="button"
                            @click="showProductSearch = false"
                            class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Search and Filter Controls -->
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <input type="text"
                               x-model="productSearchQuery"
                               @input="searchProducts()"
                               placeholder="Search products by name, code, or description..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <select x-model="productCategoryFilter"
                                @change="searchProducts()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Categories</option>
                            <option value="electrical">Electrical</option>
                            <option value="plumbing">Plumbing</option>
                            <option value="construction">Construction</option>
                            <option value="tools">Tools</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="bg-white px-6 py-4 max-h-96 overflow-y-auto">
                <!-- Loading State -->
                <div x-show="loadingProducts" class="flex justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>

                <!-- Products Grid -->
                <div x-show="!loadingProducts && searchResults.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="product in searchResults" :key="product.id">
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:shadow-md transition-all cursor-pointer"
                             @click="selectProduct(product)">

                            <!-- Product Info -->
                            <div class="mb-3">
                                <h4 class="font-medium text-gray-900 mb-1" x-text="product.name"></h4>
                                <p class="text-sm text-gray-500" x-text="product.item_code || 'No code'"></p>
                                <p class="text-xs text-gray-400 mt-1" x-text="product.description || 'No description'"></p>
                            </div>

                            <!-- Pricing Info -->
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Base Price:</span>
                                    <span class="font-medium text-lg" x-text="'RM ' + parseFloat(product.unit_price).toFixed(2)"></span>
                                </div>

                                <!-- Customer Segment Pricing -->
                                <div x-show="invoice.customer_segment_id && product.segment_pricing" class="bg-green-50 p-2 rounded">
                                    <div class="text-xs text-green-800 font-medium">Segment Pricing Available</div>
                                    <div class="text-sm text-green-700" x-text="'Your Price: RM ' + getSegmentPrice(product)"></div>
                                </div>

                                <!-- Tier Pricing Indicator -->
                                <div x-show="product.tier_pricing && product.tier_pricing.length > 0" class="bg-blue-50 p-2 rounded">
                                    <div class="text-xs text-blue-800 font-medium">Bulk Pricing Available</div>
                                    <div class="text-xs text-blue-600">
                                        <span x-text="product.tier_pricing[0].min_quantity + '+ units: RM ' + product.tier_pricing[0].unit_price"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Stock Info -->
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600">Unit:</span>
                                    <span x-text="product.unit || 'pcs'"></span>
                                </div>
                                <div x-show="product.stock_quantity !== null" class="flex justify-between items-center text-sm mt-1">
                                    <span class="text-gray-600">Stock:</span>
                                    <span x-text="product.stock_quantity"
                                          :class="product.stock_quantity > 10 ? 'text-green-600' : product.stock_quantity > 0 ? 'text-yellow-600' : 'text-red-600'"></span>
                                </div>
                            </div>

                            <!-- Add Button -->
                            <button type="button"
                                    @click.stop="addProductToInvoice(product)"
                                    class="w-full mt-3 px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Add to Invoice
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Empty State -->
                <div x-show="!loadingProducts && searchResults.length === 0" class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your search criteria or browse all products.</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-3 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    <span x-show="searchResults.length > 0" x-text="searchResults.length + ' products found'"></span>
                </div>
                <div class="flex space-x-3">
                    <button type="button"
                            @click="showProductSearch = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Close
                    </button>
                    <button type="button"
                            onclick="window.open('{{ route('pricing.index') }}', '_blank')"
                            class="px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 border border-blue-300 rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Manage Products
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Additional Alpine.js methods for product search modal
document.addEventListener('alpine:init', () => {
    Alpine.data('productSearchMethods', () => ({
        showProductSearch: false,
        productSearchQuery: '',
        productCategoryFilter: '',
        searchResults: [],
        loadingProducts: false,

        openProductSearch() {
            this.showProductSearch = true;
            this.searchProducts();
        },

        async searchProducts() {
            this.loadingProducts = true;

            try {
                const params = new URLSearchParams({
                    query: this.productSearchQuery,
                    category: this.productCategoryFilter,
                    limit: 50
                });

                const response = await fetch(`/api/pricing-items/search?${params}`);
                const data = await response.json();

                this.searchResults = data.products || [];
            } catch (error) {
                console.error('Product search error:', error);
                this.searchResults = [];
            } finally {
                this.loadingProducts = false;
            }
        },

        selectProduct(product) {
            this.addProductToInvoice(product);
        },

        addProductToInvoice(product) {
            // Get the correct price based on customer segment
            const price = this.getSegmentPrice(product);

            this.invoice.items.push({
                description: product.name,
                quantity: 1,
                unit_price: price,
                item_code: product.item_code || '',
                source_type: 'pricing_item',
                source_id: product.id,
                tier_info: this.getTierInfo(product, 1)
            });

            this.calculateTotals();
            this.showProductSearch = false;

            // Show success message
            this.showNotification('Product added to invoice', 'success');
        },

        getSegmentPrice(product) {
            if (!this.invoice.customer_segment_id || !product.segment_pricing) {
                return parseFloat(product.unit_price);
            }

            const segmentPrice = product.segment_pricing.find(sp =>
                sp.customer_segment_id == this.invoice.customer_segment_id
            );

            return segmentPrice ? parseFloat(segmentPrice.unit_price) : parseFloat(product.unit_price);
        },

        getTierInfo(product, quantity) {
            if (!product.tier_pricing || product.tier_pricing.length === 0) {
                return null;
            }

            const applicableTier = product.tier_pricing
                .filter(tier => quantity >= tier.min_quantity)
                .sort((a, b) => b.min_quantity - a.min_quantity)[0];

            if (applicableTier && applicableTier.unit_price < product.unit_price) {
                const savings = (product.unit_price - applicableTier.unit_price) * quantity;
                return `Tier pricing: Save RM ${savings.toFixed(2)}`;
            }

            return null;
        },

        showNotification(message, type = 'info') {
            // Simple notification - can be enhanced with a proper notification system
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-md ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                'bg-blue-500 text-white'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    }));
});
</script>