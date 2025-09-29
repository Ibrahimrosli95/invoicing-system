@extends('layouts.app')

@section('title', 'Invoice Note Templates')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Invoice Note Templates</h1>
                <p class="text-gray-600 mt-1">Manage templates for notes, terms & conditions, and payment instructions</p>
            </div>
            <a href="{{ route('invoice-note-templates.create') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Template
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0 sm:space-x-4">
                <!-- Search -->
                <div class="flex-1 max-w-lg">
                    <form method="GET" action="{{ route('invoice-note-templates.index') }}">
                        <div class="relative">
                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Search templates..."
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Type Filter -->
                <div class="flex items-center space-x-2">
                    <label for="type-filter" class="text-sm font-medium text-gray-700">Type:</label>
                    <select id="type-filter"
                            onchange="window.location.href = '{{ route('invoice-note-templates.index') }}?type=' + this.value + '&search=' + encodeURIComponent('{{ request('search') }}')"
                            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Types</option>
                        @foreach(\App\Models\InvoiceNoteTemplate::getTypes() as $key => $label)
                            <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($templates->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Template</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content Preview</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($templates as $template)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $template->name }}</div>
                                            <div class="text-sm text-gray-500">
                                                Created {{ $template->created_at->format('M j, Y') }}
                                            </div>
                                        </div>
                                        @if($template->is_default)
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Default
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($template->type === 'notes') bg-green-100 text-green-800
                                        @elseif($template->type === 'terms') bg-amber-100 text-amber-800
                                        @elseif($template->type === 'payment_instructions') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $template->getTypeDisplayName() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 line-clamp-2 max-w-xs">
                                        {{ Str::limit($template->content, 100) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($template->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('invoice-note-templates.show', $template) }}"
                                           class="text-blue-600 hover:text-blue-900">View</a>
                                        <a href="{{ route('invoice-note-templates.edit', $template) }}"
                                           class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        @if(!$template->is_default)
                                            <button onclick="setAsDefault({{ $template->id }})"
                                                    class="text-green-600 hover:text-green-900">Set Default</button>
                                        @endif
                                        <form method="POST" action="{{ route('invoice-note-templates.destroy', $template) }}"
                                              class="inline" onsubmit="return confirm('Are you sure you want to delete this template?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($templates->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $templates->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No templates found</h3>
                <p class="mt-2 text-gray-600">
                    @if(request('search') || request('type'))
                        No templates match your current filters.
                        <a href="{{ route('invoice-note-templates.index') }}" class="text-blue-600 hover:text-blue-500">Clear filters</a>
                    @else
                        Get started by creating your first template.
                    @endif
                </p>
                @if(!request('search') && !request('type'))
                    <div class="mt-6">
                        <a href="{{ route('invoice-note-templates.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Create Your First Template
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

<script>
async function setAsDefault(templateId) {
    try {
        const response = await fetch(`/invoice-note-templates/${templateId}/set-default`, {
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