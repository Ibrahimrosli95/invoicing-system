@extends('layouts.app')

@section('title', $invoiceNoteTemplate->name)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('invoice-note-templates.index') }}"
                   class="flex items-center text-gray-600 hover:text-gray-900">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $invoiceNoteTemplate->name }}</h1>
                    <div class="flex items-center space-x-3 mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($invoiceNoteTemplate->type === 'notes') bg-green-100 text-green-800
                            @elseif($invoiceNoteTemplate->type === 'terms') bg-amber-100 text-amber-800
                            @elseif($invoiceNoteTemplate->type === 'payment_instructions') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $invoiceNoteTemplate->getTypeDisplayName() }}
                        </span>
                        @if($invoiceNoteTemplate->is_default)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Default Template
                            </span>
                        @endif
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($invoiceNoteTemplate->is_active) bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $invoiceNoteTemplate->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('invoice-note-templates.edit', $invoiceNoteTemplate) }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Template
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Template Content -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Template Content</h2>
                </div>
                <div class="p-6">
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="whitespace-pre-wrap text-gray-900 text-sm leading-relaxed">{{ $invoiceNoteTemplate->content }}</div>
                    </div>

                    <!-- Copy Button -->
                    <div class="mt-4 flex justify-end">
                        <button onclick="copyContent()"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Copy Content
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Template Information -->
        <div class="space-y-6">
            <!-- Details Card -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Template Details</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Template Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $invoiceNoteTemplate->name }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Type</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $invoiceNoteTemplate->getTypeDisplayName() }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Status</label>
                        <div class="mt-1 flex items-center space-x-2">
                            @if($invoiceNoteTemplate->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                            @endif

                            @if($invoiceNoteTemplate->is_default)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Default
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Created</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $invoiceNoteTemplate->created_at->format('M j, Y g:i A') }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Last Modified</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $invoiceNoteTemplate->updated_at->format('M j, Y g:i A') }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Content Length</label>
                        <p class="mt-1 text-sm text-gray-900">{{ strlen($invoiceNoteTemplate->content) }} characters</p>
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('invoice-note-templates.edit', $invoiceNoteTemplate) }}"
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Template
                    </a>

                    @if(!$invoiceNoteTemplate->is_default)
                        <button onclick="setAsDefault()"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Set as Default
                        </button>
                    @endif

                    <form method="POST" action="{{ route('invoice-note-templates.destroy', $invoiceNoteTemplate) }}"
                          onsubmit="return confirm('Are you sure you want to delete this template? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Template
                        </button>
                    </form>
                </div>
            </div>

            <!-- Usage Tips -->
            <div class="bg-blue-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-blue-900 mb-2">Usage Tips</h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Templates can be applied when creating invoices</li>
                    <li>• Default templates are automatically applied</li>
                    <li>• You can create multiple templates per type</li>
                    <li>• Inactive templates won't appear in selection</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function copyContent() {
    const content = `{{ addslashes($invoiceNoteTemplate->content) }}`;
    navigator.clipboard.writeText(content).then(function() {
        // Show success message
        alert('Template content copied to clipboard!');
    }, function() {
        // Fallback for older browsers
        const textArea = document.createElement("textarea");
        textArea.value = content;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Template content copied to clipboard!');
    });
}

async function setAsDefault() {
    if (!confirm('Set this template as the default for {{ $invoiceNoteTemplate->getTypeDisplayName() }}?')) {
        return;
    }

    try {
        const response = await fetch(`{{ route('invoice-note-templates.set-default', $invoiceNoteTemplate) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (response.ok) {
            window.location.reload();
        } else {
            alert('Failed to set template as default. Please try again.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}
</script>
@endsection