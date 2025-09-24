@extends('layouts.app')

@section('title', 'Bulk Import Pricing Items')

@section('header')
<div class="flex justify-between items-center">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Bulk Import Pricing Items
    </h2>
    <div class="flex space-x-3">
        <a href="{{ route('pricing.download-template') }}"
           class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-4 py-2 rounded-lg text-sm font-medium">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Download Template
        </a>
        <a href="{{ route('pricing.index') }}"
           class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Back to Pricing Book
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="py-6" x-data="bulkImport">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Import Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Import Instructions</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Download the CSV template to see the correct format and column headers</li>
                            <li><strong>Required fields:</strong> name, category, unit, cost_price</li>
                            <li><strong>Optional fields:</strong> item_code, description, specifications, tags, stock_quantity</li>
                            <li><strong>Segment pricing:</strong> Add columns for each customer segment (e.g., end_user_price, contractor_price, dealer_price)</li>
                            <li><strong>Boolean fields:</strong> is_active, is_featured - use TRUE/FALSE or 1/0</li>
                            <li><strong>Tags format:</strong> Use comma-separated values (e.g., "construction,materials,tools")</li>
                            <li><strong>Numbers:</strong> Prices in RM format (150.00), stock_quantity as whole numbers</li>
                            <li>Categories must exist in the system (create them first if needed)</li>
                            <li>Maximum file size: 10MB, Maximum rows: 1000</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Form -->
        <div class="bg-white rounded-lg shadow">
            <form method="POST" action="{{ route('pricing.process-import') }}" enctype="multipart/form-data">
                @csrf

                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Upload CSV File</h3>

                    <div class="space-y-6">
                        <!-- File Upload -->
                        <div>
                            <label for="csv_file" class="block text-sm font-medium text-gray-700">CSV File</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3-3m-3 3l3-3m-8 0a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="csv_file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                            <span>Upload a CSV file</span>
                                            <input id="csv_file" name="csv_file" type="file" accept=".csv" required class="sr-only" @change="handleFileSelect">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">CSV files up to 10MB</p>
                                </div>
                            </div>
                            @error('csv_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Selected File Display -->
                        <div x-show="selectedFile" class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="ml-2 text-sm text-gray-900" x-text="selectedFile"></span>
                                <button type="button" @click="clearFile" class="ml-auto text-red-600 hover:text-red-800">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Import Options -->
                        <div class="space-y-4">
                            <h4 class="text-sm font-medium text-gray-900">Import Options</h4>

                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="skip_duplicates" value="1" checked class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-600">Skip items with duplicate item codes</span>
                                </label>

                                <label class="flex items-center">
                                    <input type="checkbox" name="update_existing" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-600">Update existing items (if item code matches)</span>
                                </label>

                                <label class="flex items-center">
                                    <input type="checkbox" name="validate_only" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-600">Validate only (don't save, just check for errors)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg space-x-4">
                    <a href="{{ route('pricing.index') }}"
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Cancel
                    </a>
                    <button type="submit"
                            :disabled="!selectedFile"
                            :class="selectedFile ? 'bg-blue-500 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                            class="text-white font-bold py-2 px-4 rounded transition-colors">
                        <span x-show="!importing">Process Import</span>
                        <span x-show="importing" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Import History -->
        @if(isset($recentImports) && $recentImports->count() > 0)
            <div class="mt-8 bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent Imports</h3>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Records</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($recentImports as $import)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $import->created_at->format('M j, Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $import->filename }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $import->total_rows }} rows
                                            @if($import->success_count > 0)
                                                <span class="text-green-600">({{ $import->success_count }} success)</span>
                                            @endif
                                            @if($import->error_count > 0)
                                                <span class="text-red-600">({{ $import->error_count }} errors)</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $import->status === 'completed' ? 'bg-green-100 text-green-800' :
                                                   ($import->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ ucfirst($import->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if($import->status === 'failed' || $import->error_count > 0)
                                                <a href="{{ route('pricing.import-errors', $import) }}" class="text-red-600 hover:text-red-900">View Errors</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
function bulkImport() {
    return {
        selectedFile: null,
        importing: false,

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.selectedFile = file.name;
            }
        },

        clearFile() {
            this.selectedFile = null;
            document.getElementById('csv_file').value = '';
        }
    }
}
</script>
@endsection