<!-- Invoice Builder Sidebar -->
<div class="space-y-6">
    <!-- Currency & Date Section -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <h3 class="text-sm font-medium text-gray-900 mb-3">Invoice Details</h3>

        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Due Date</label>
                <input type="date"
                       x-model="invoice.due_date"
                       class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent">
            </div>

            @if($type === 'product')
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Payment Terms</label>
                <select x-model="invoice.payment_terms"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent">
                    <option value="7">7 days</option>
                    <option value="14">14 days</option>
                    <option value="30" selected>30 days</option>
                    <option value="60">60 days</option>
                    <option value="90">90 days</option>
                </select>
            </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Currency</label>
                <select class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded bg-gray-50" disabled>
                    <option>Malaysian Ringgit (RM)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Team Assignment Section -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <h3 class="text-sm font-medium text-gray-900 mb-3">Assignment</h3>

        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Team</label>
                <select x-model="invoice.team_id"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select Team</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Assigned To</label>
                <select x-model="invoice.assigned_to"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select Assignee</option>
                    @foreach($assignees as $assignee)
                        <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if($type === 'service')
    <!-- Service Templates Section -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <h3 class="text-sm font-medium text-gray-900 mb-3">Service Templates</h3>

        <div class="space-y-2 max-h-64 overflow-y-auto">
            <div class="text-xs text-gray-500 mb-2">Click to apply template</div>

            @foreach(\App\Models\ServiceTemplate::forCompany()->active()->limit(10)->get() as $template)
            <div class="p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer"
                 @click="applyServiceTemplate({{ $template->id }})">
                <div class="font-medium text-xs text-gray-900">{{ $template->name }}</div>
                <div class="text-xs text-gray-500">{{ $template->category }}</div>
                <div class="text-xs text-blue-600">{{ $template->sections->count() }} sections</div>
            </div>
            @endforeach
        </div>

        <button type="button"
                class="w-full mt-2 px-3 py-1.5 text-xs font-medium text-blue-600 border border-blue-600 rounded hover:bg-blue-50"
                onclick="window.open('{{ route('service-templates.index') }}', '_blank')">
            Manage Templates
        </button>
    </div>
    @else
    <!-- Product Search Section -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <h3 class="text-sm font-medium text-gray-900 mb-3">Quick Add Products</h3>

        <div class="space-y-2">
            <input type="text"
                   x-model="quickSearch.query"
                   @input="performQuickSearch()"
                   placeholder="Search products..."
                   class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent">

            <div class="space-y-1 max-h-32 overflow-y-auto" x-show="quickSearch.results.length > 0">
                <template x-for="product in quickSearch.results" :key="product.id">
                    <div class="p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer text-xs"
                         @click="addProductToInvoice(product)">
                        <div class="font-medium text-gray-900" x-text="product.name"></div>
                        <div class="text-gray-500" x-text="'RM ' + getProductPrice(product).toFixed(2)"></div>
                        <div class="text-gray-400" x-text="product.item_code"></div>
                    </div>
                </template>
            </div>
        </div>

        <button type="button"
                @click="showProductSearch = true"
                class="w-full mt-2 px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
            Browse All Products
        </button>
    </div>

    <!-- Service Templates Section (For Product invoices too) -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <h3 class="text-sm font-medium text-gray-900 mb-3">Service Templates</h3>

        <div class="space-y-2">
            <select x-model="selectedTemplate"
                    @change="loadServiceTemplate()"
                    class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent">
                <option value="">Select a template</option>
                <option value="1">Basic Installation</option>
                <option value="2">Maintenance Package</option>
                <option value="3">Consulting Service</option>
                <option value="4">Training Package</option>
            </select>
        </div>

        <button type="button"
                class="w-full mt-2 px-3 py-1.5 text-xs font-medium text-blue-600 border border-blue-600 rounded hover:bg-blue-50"
                onclick="window.open('{{ route('service-templates.index') }}', '_blank')">
            Manage Templates
        </button>
    </div>
    @endif

    <!-- Client Suggestions Section -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <h3 class="text-sm font-medium text-gray-900 mb-3">Recent Clients</h3>

        <div class="space-y-2 max-h-48 overflow-y-auto">
            @foreach($recentClients->take(8) as $client)
            <div class="p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer"
                 @click="selectClient({{ json_encode($client) }})">
                <div class="font-medium text-xs text-gray-900">{{ $client['name'] }}</div>
                <div class="text-xs text-gray-500">{{ $client['phone'] }}</div>
                <div class="text-xs text-blue-600">{{ ucfirst($client['source']) }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Document Defaults Section -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <h3 class="text-sm font-medium text-gray-900 mb-3">Document Defaults</h3>

        <div class="space-y-2">
            @if(!empty($documentDefaults['terms_conditions']))
            <button type="button"
                    @click="invoice.terms_conditions = `{!! addslashes($documentDefaults['terms_conditions']) !!}`"
                    class="w-full px-2 py-1.5 text-xs text-left text-gray-700 border border-gray-300 rounded hover:bg-gray-50">
                Apply Default Terms
            </button>
            @endif

            @if(!empty($documentDefaults['notes']))
            <button type="button"
                    @click="invoice.notes = `{!! addslashes($documentDefaults['notes']) !!}`"
                    class="w-full px-2 py-1.5 text-xs text-left text-gray-700 border border-gray-300 rounded hover:bg-gray-50">
                Apply Default Notes
            </button>
            @endif

            @if(!empty($documentDefaults['tax_percentage']))
            <button type="button"
                    @click="invoice.tax_percentage = {{ $documentDefaults['tax_percentage'] }}; calculateTotals()"
                    class="w-full px-2 py-1.5 text-xs text-left text-gray-700 border border-gray-300 rounded hover:bg-gray-50">
                Apply Default Tax ({{ $documentDefaults['tax_percentage'] }}%)
            </button>
            @endif
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <h3 class="text-sm font-medium text-gray-900 mb-3">Quick Actions</h3>

        <div class="space-y-2">
            <button type="button"
                    @click="addManualItem()"
                    class="w-full px-2 py-1.5 text-xs font-medium text-gray-700 border border-gray-300 rounded hover:bg-gray-50">
                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Custom Line
            </button>

            <button type="button"
                    @click="clearAllItems()"
                    class="w-full px-2 py-1.5 text-xs font-medium text-red-600 border border-red-300 rounded hover:bg-red-50">
                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Clear All Items
            </button>

            <button type="button"
                    onclick="window.location.href='{{ route('invoices.index') }}'"
                    class="w-full px-2 py-1.5 text-xs font-medium text-gray-700 border border-gray-300 rounded hover:bg-gray-50">
                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                View All {{ $type === 'product' ? 'Invoices' : 'Quotations' }}
            </button>
        </div>
    </div>

    @if($type === 'product')
    <!-- Customer Segment Info -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200" x-show="invoice.customer_segment_id">
        <h3 class="text-sm font-medium text-gray-900 mb-3">Pricing Information</h3>

        <div class="text-xs text-gray-600">
            <div>Selected segment pricing will be applied automatically</div>
            <div class="mt-1 text-blue-600">Tier pricing available for bulk quantities</div>
        </div>
    </div>
    @endif
</div>

<script>
// Additional Alpine.js methods for sidebar functionality
document.addEventListener('alpine:init', () => {
    Alpine.data('sidebarMethods', () => ({
        quickProductSearch: '',
        quickProducts: [],

        searchQuickProducts() {
            if (this.quickProductSearch.length < 2) {
                this.quickProducts = [];
                return;
            }

            // Simulate API call to search products
            fetch(`/api/pricing-items/search?query=${this.quickProductSearch}&limit=5`)
                .then(response => response.json())
                .then(data => {
                    this.quickProducts = data.products || [];
                })
                .catch(error => {
                    console.error('Product search error:', error);
                    this.quickProducts = [];
                });
        },

        addQuickProduct(product) {
            this.addProductFromSearch(product);
            this.quickProductSearch = '';
            this.quickProducts = [];
        },

        applyServiceTemplate(templateId) {
            // Load service template and add sections/items
            fetch(`/api/service-templates/${templateId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.template && data.template.sections) {
                        data.template.sections.forEach(section => {
                            section.items.forEach(item => {
                                this.invoice.items.push({
                                    description: item.description,
                                    quantity: item.default_quantity || 1,
                                    unit_price: item.unit_price,
                                    item_code: item.item_code || '',
                                    source_type: 'service_template_item',
                                    source_id: item.id
                                });
                            });
                        });
                        this.calculateTotals();
                    }
                })
                .catch(error => {
                    console.error('Template load error:', error);
                });
        },

        clearAllItems() {
            if (confirm('Are you sure you want to clear all items?')) {
                this.invoice.items = [];
                this.calculateTotals();
            }
        }
    }));
});
</script>