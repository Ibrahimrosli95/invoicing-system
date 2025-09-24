@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg px-4 py-5 sm:p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $customer->name }}</h1>
                @if($customer->company_name)
                    <p class="mt-1 text-lg text-gray-600">{{ $customer->company_name }}</p>
                @endif
                <div class="mt-2 flex items-center space-x-3">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $customer->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    @if($customer->is_new_customer)
                        <span class="inline-flex px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                            New Customer
                        </span>
                    @endif
                    @if($customer->customerSegment)
                        <span class="inline-flex px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                            {{ $customer->customerSegment->name }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('customers.edit', $customer) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Edit Customer
                </a>
                <a href="{{ route('customers.index') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Contact Information -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="text-sm text-gray-900">
                            <a href="tel:{{ $customer->phone }}" class="text-blue-600 hover:text-blue-800">
                                {{ $customer->phone }}
                            </a>
                        </dd>
                    </div>
                    @if($customer->email)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="text-sm text-gray-900">
                                <a href="mailto:{{ $customer->email }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $customer->email }}
                                </a>
                            </dd>
                        </div>
                    @endif
                    @if($customer->full_address)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                            <dd class="text-sm text-gray-900">{{ $customer->full_address }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Business Information -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Business Information</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Customer Segment</dt>
                        <dd class="text-sm text-gray-900">
                            @if($customer->customerSegment)
                                <span class="inline-flex px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    {{ $customer->customerSegment->name }}
                                </span>
                            @else
                                <span class="text-gray-400">No segment assigned</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="text-sm text-gray-900">
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $customer->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Customer Type</dt>
                        <dd class="text-sm text-gray-900">
                            {{ $customer->is_new_customer ? 'New Customer' : 'Existing Customer' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                        <dd class="text-sm text-gray-900">
                            {{ $customer->created_at->format('M j, Y \a\t g:i A') }}
                            @if($customer->createdBy)
                                by {{ $customer->createdBy->name }}
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Notes -->
    @if($customer->notes)
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Notes</h2>
                <div class="text-sm text-gray-900 whitespace-pre-wrap">{{ $customer->notes }}</div>
            </div>
        </div>
    @endif

    <!-- Related Information -->
    @if($customer->lead_id)
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Lead Information</h2>
                <p class="text-sm text-gray-600">
                    This customer was converted from a lead.
                    @if($customer->lead)
                        <a href="{{ route('leads.show', $customer->lead) }}" class="text-blue-600 hover:text-blue-800">
                            View original lead
                        </a>
                    @endif
                </p>
            </div>
        </div>
    @endif
</div>
@endsection