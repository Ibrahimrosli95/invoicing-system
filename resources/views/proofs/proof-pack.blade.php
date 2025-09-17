<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Generate Proof Pack PDF
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Create a comprehensive PDF portfolio showcasing your social proof and credentials
                </p>
            </div>
            <a href="{{ route('proofs.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Back to Proofs
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($proofs->isEmpty())
                <!-- No Proofs Available -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6 text-center">
                        <div class="w-12 h-12 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Proofs Available</h3>
                        <p class="text-gray-600 mb-4">You need to create some proofs before generating a proof pack PDF.</p>
                        <a href="{{ route('proofs.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Create Your First Proof
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="proofPackGenerator()">
                    <!-- Configuration Panel -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-6">Pack Configuration</h3>
                                
                                <form id="proof-pack-form" x-ref="form">
                                    @csrf
                                    
                                    <!-- Pack Title -->
                                    <div class="mb-6">
                                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Pack Title</label>
                                        <input type="text" 
                                               name="title" 
                                               id="title"
                                               x-model="config.title"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                               placeholder="Social Proof Portfolio"
                                               required>
                                    </div>

                                    <!-- Orientation -->
                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-3">Orientation</label>
                                        <div class="flex space-x-4">
                                            <label class="flex items-center">
                                                <input type="radio" 
                                                       name="orientation" 
                                                       value="portrait" 
                                                       x-model="config.orientation"
                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                                <span class="ml-2 text-sm text-gray-700">Portrait</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" 
                                                       name="orientation" 
                                                       value="landscape" 
                                                       x-model="config.orientation"
                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                                <span class="ml-2 text-sm text-gray-700">Landscape</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Options -->
                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-3">Options</label>
                                        <div class="space-y-3">
                                            <label class="flex items-center">
                                                <input type="checkbox" 
                                                       name="show_analytics" 
                                                       x-model="config.show_analytics"
                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                <span class="ml-2 text-sm text-gray-700">Include Analytics</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Watermark -->
                                    <div class="mb-6">
                                        <label for="watermark" class="block text-sm font-medium text-gray-700 mb-2">
                                            Watermark (Optional)
                                        </label>
                                        <input type="text" 
                                               name="watermark" 
                                               id="watermark"
                                               x-model="config.watermark"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                               placeholder="CONFIDENTIAL"
                                               maxlength="50">
                                        <p class="mt-1 text-xs text-gray-500">Light watermark text overlay</p>
                                    </div>

                                    <!-- Selected Count -->
                                    <div class="mb-6 p-3 bg-blue-50 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-blue-900">Selected Proofs:</span>
                                            <span class="text-sm font-bold text-blue-600" x-text="selectedCount"></span>
                                        </div>
                                        <template x-if="selectedCount === 0">
                                            <p class="text-xs text-blue-700 mt-1">Select at least one proof to generate PDF</p>
                                        </template>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="space-y-3">
                                        <button type="button" 
                                                @click="previewPDF()"
                                                :disabled="selectedCount === 0"
                                                :class="selectedCount === 0 ? 'bg-gray-400' : 'bg-green-600 hover:bg-green-700'"
                                                class="w-full px-4 py-2 text-white font-medium rounded-md">
                                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            Preview PDF
                                        </button>
                                        
                                        <button type="button" 
                                                @click="downloadPDF()"
                                                :disabled="selectedCount === 0"
                                                :class="selectedCount === 0 ? 'bg-gray-400' : 'bg-blue-600 hover:bg-blue-700'"
                                                class="w-full px-4 py-2 text-white font-medium rounded-md">
                                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Download PDF
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Analytics Summary -->
                        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Portfolio Statistics</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                                        <div class="text-2xl font-bold text-blue-600">{{ $analytics['total_proofs'] }}</div>
                                        <div class="text-xs text-gray-600">Total Proofs</div>
                                    </div>
                                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                                        <div class="text-2xl font-bold text-green-600">{{ $analytics['featured_count'] }}</div>
                                        <div class="text-xs text-gray-600">Featured</div>
                                    </div>
                                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                                        <div class="text-2xl font-bold text-yellow-600">{{ number_format($analytics['total_views']) }}</div>
                                        <div class="text-xs text-gray-600">Total Views</div>
                                    </div>
                                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                                        <div class="text-2xl font-bold text-purple-600">{{ number_format($analytics['average_impact'], 1) }}%</div>
                                        <div class="text-xs text-gray-600">Avg Impact</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Proof Selection Panel -->
                    <div class="lg:col-span-2">
                        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-lg font-medium text-gray-900">Select Proofs to Include</h3>
                                    <div class="flex space-x-2">
                                        <button type="button" 
                                                @click="selectAll()"
                                                class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                            Select All
                                        </button>
                                        <button type="button" 
                                                @click="deselectAll()"
                                                class="text-xs text-gray-600 hover:text-gray-800 font-medium">
                                            Deselect All
                                        </button>
                                    </div>
                                </div>

                                <!-- Proof Categories -->
                                @foreach($proofs as $categoryName => $categoryProofs)
                                    <div class="mb-8">
                                        <div class="flex items-center justify-between mb-4">
                                            <h4 class="text-base font-medium text-gray-900 flex items-center">
                                                <span class="inline-block w-3 h-3 rounded-full mr-2" 
                                                      style="background-color: {{ $categoryProofs->first()->getCategoryColor() }};"></span>
                                                {{ \App\Models\Proof::getTypeLabels()[$categoryName] ?? Str::title($categoryName) }}
                                                <span class="ml-2 text-sm text-gray-500">({{ $categoryProofs->count() }})</span>
                                            </h4>
                                            <button type="button" 
                                                    @click="toggleCategory('{{ $categoryName }}')"
                                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                                Toggle Category
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach($categoryProofs as $proof)
                                                <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors">
                                                    <div class="flex items-start space-x-3">
                                                        <input type="checkbox" 
                                                               :checked="selectedProofs.includes('{{ $proof->uuid }}')"
                                                               @change="toggleProof('{{ $proof->uuid }}')"
                                                               class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                        
                                                        <div class="flex-1 min-w-0">
                                                            <div class="flex items-center space-x-2 mb-2">
                                                                @if($proof->is_featured)
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                                        ★ Featured
                                                                    </span>
                                                                @endif
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium text-white"
                                                                      style="background-color: {{ $proof->getCategoryColor() }};">
                                                                    {{ $proof->type_label }}
                                                                </span>
                                                            </div>
                                                            
                                                            <h5 class="text-sm font-medium text-gray-900 mb-1">{{ $proof->title }}</h5>
                                                            
                                                            @if($proof->description)
                                                                <p class="text-xs text-gray-600 mb-2 line-clamp-2">{{ Str::limit($proof->description, 100) }}</p>
                                                            @endif

                                                            <div class="flex items-center justify-between text-xs text-gray-500">
                                                                <span>{{ $proof->assets->count() }} {{ Str::plural('asset', $proof->assets->count()) }}</span>
                                                                @if($proof->views_count > 0)
                                                                    <span>{{ number_format($proof->views_count) }} views</span>
                                                                @endif
                                                                @if($proof->conversion_impact)
                                                                    <span class="text-green-600 font-medium">{{ $proof->conversion_impact }}% impact</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function proofPackGenerator() {
            return {
                selectedProofs: [],
                config: {
                    title: 'Social Proof Portfolio',
                    orientation: 'portrait',
                    show_analytics: false,
                    watermark: ''
                },

                get selectedCount() {
                    return this.selectedProofs.length;
                },

                toggleProof(uuid) {
                    const index = this.selectedProofs.indexOf(uuid);
                    if (index > -1) {
                        this.selectedProofs.splice(index, 1);
                    } else {
                        this.selectedProofs.push(uuid);
                    }
                },

                selectAll() {
                    // Get all proof UUIDs from the page
                    const checkboxes = document.querySelectorAll('input[type="checkbox"]:not([name="show_analytics"])');
                    this.selectedProofs = [];
                    checkboxes.forEach(checkbox => {
                        const onChange = checkbox.getAttribute('@change');
                        if (onChange && onChange.includes('toggleProof')) {
                            const uuid = onChange.match(/'([^']+)'/)[1];
                            this.selectedProofs.push(uuid);
                        }
                    });
                },

                deselectAll() {
                    this.selectedProofs = [];
                },

                toggleCategory(categoryName) {
                    // Find all checkboxes in this category and toggle them
                    const categorySection = event.target.closest('.mb-8');
                    const checkboxes = categorySection.querySelectorAll('input[type="checkbox"]');
                    
                    let categoryProofs = [];
                    checkboxes.forEach(checkbox => {
                        const onChange = checkbox.getAttribute('@change');
                        if (onChange && onChange.includes('toggleProof')) {
                            const uuid = onChange.match(/'([^']+)'/)[1];
                            categoryProofs.push(uuid);
                        }
                    });

                    // Check if all are selected
                    const allSelected = categoryProofs.every(uuid => this.selectedProofs.includes(uuid));
                    
                    if (allSelected) {
                        // Deselect all in category
                        categoryProofs.forEach(uuid => {
                            const index = this.selectedProofs.indexOf(uuid);
                            if (index > -1) {
                                this.selectedProofs.splice(index, 1);
                            }
                        });
                    } else {
                        // Select all in category
                        categoryProofs.forEach(uuid => {
                            if (!this.selectedProofs.includes(uuid)) {
                                this.selectedProofs.push(uuid);
                            }
                        });
                    }
                },

                previewPDF() {
                    this.submitForm('{{ route('proofs.proof-pack.preview') }}', '_blank');
                },

                downloadPDF() {
                    this.submitForm('{{ route('proofs.proof-pack.generate') }}');
                },

                submitForm(action, target = null) {
                    if (this.selectedCount === 0) {
                        alert('Please select at least one proof to include in the PDF.');
                        return;
                    }

                    const form = this.$refs.form;
                    const formData = new FormData(form);
                    
                    // Add selected proof IDs
                    this.selectedProofs.forEach(uuid => {
                        formData.append('proof_ids[]', uuid);
                    });
                    
                    // Add config
                    formData.append('title', this.config.title);
                    formData.append('orientation', this.config.orientation);
                    formData.append('show_analytics', this.config.show_analytics ? '1' : '0');
                    if (this.config.watermark) {
                        formData.append('watermark', this.config.watermark);
                    }

                    // Create and submit form
                    const tempForm = document.createElement('form');
                    tempForm.method = 'POST';
                    tempForm.action = action;
                    if (target) {
                        tempForm.target = target;
                    }

                    // Convert FormData to hidden inputs
                    for (let [key, value] of formData.entries()) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = value;
                        tempForm.appendChild(input);
                    }

                    document.body.appendChild(tempForm);
                    tempForm.submit();
                    document.body.removeChild(tempForm);
                }
            };
        }
    </script>
    @endpush
</x-app-layout>