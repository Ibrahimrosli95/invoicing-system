<x-customer-portal.layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quotations') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters and Search -->
            <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('customer-portal.quotations.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Search -->
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                                <input type="text" 
                                       name="search" 
                                       id="search"
                                       value="{{ request('search') }}" 
                                       placeholder="Search by number, project, or customer..."
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Statuses</option>
                                    <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>Draft</option>
                                    <option value="SENT" {{ request('status') === 'SENT' ? 'selected' : '' }}>Sent</option>
                                    <option value="VIEWED" {{ request('status') === 'VIEWED' ? 'selected' : '' }}>Viewed</option>
                                    <option value="ACCEPTED" {{ request('status') === 'ACCEPTED' ? 'selected' : '' }}>Accepted</option>
                                    <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                                    <option value="EXPIRED" {{ request('status') === 'EXPIRED' ? 'selected' : '' }}>Expired</option>
                                </select>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-end space-x-2">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    Filter
                                </button>
                                <a href="{{ route('customer-portal.quotations.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                                    Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Status Badges -->
            <div class="flex flex-wrap gap-2 mb-6">
                <a href="{{ route('customer-portal.quotations.index') }}" 
                   class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ !request('status') ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    All ({{ $statusCounts['all'] }})
                </a>
                @foreach(['DRAFT', 'SENT', 'VIEWED', 'ACCEPTED', 'REJECTED', 'EXPIRED'] as $status)
                    <a href="{{ route('customer-portal.quotations.index', ['status' => $status]) }}" 
                       class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ request('status') === $status ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ ucfirst(strtolower($status)) }} ({{ $statusCounts[$status] }})
                    </a>
                @endforeach
            </div>

            <!-- Quotations List -->
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($quotations->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quotation
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Project
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($quotations as $quotation)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $quotation->number }}</div>
                                                    <div class="text-sm text-gray-500">{{ $quotation->customer_name }}</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $quotation->project_name ?: 'No project name' }}</div>
                                                <div class="text-sm text-gray-500">{{ Str::limit($quotation->description, 50) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($quotation->status === 'ACCEPTED') bg-green-100 text-green-800
                                                    @elseif($quotation->status === 'SENT') bg-blue-100 text-blue-800
                                                    @elseif($quotation->status === 'VIEWED') bg-purple-100 text-purple-800
                                                    @elseif($quotation->status === 'REJECTED') bg-red-100 text-red-800
                                                    @elseif($quotation->status === 'EXPIRED') bg-gray-100 text-gray-800
                                                    @else bg-yellow-100 text-yellow-800
                                                    @endif">
                                                    {{ $quotation->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                RM {{ number_format($quotation->total, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $quotation->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end space-x-2">
                                                    <a href="{{ route('customer-portal.quotations.show', $quotation) }}" 
                                                       class="text-blue-600 hover:text-blue-900">
                                                        View
                                                    </a>
                                                    @if(Auth::guard('customer-portal')->user()->can_download_pdfs)
                                                        <a href="{{ route('customer-portal.quotations.pdf', $quotation) }}" 
                                                           class="text-green-600 hover:text-green-900">
                                                            PDF
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $quotations->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No quotations found</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if(request()->hasAny(['search', 'status']))
                                    Try adjusting your search criteria.
                                @else
                                    Your quotations will appear here once they are created.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-customer-portal.layouts.app>