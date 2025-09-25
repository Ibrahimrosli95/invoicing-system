@extends('layouts.app')

@section('title', __('Create Webhook Endpoint'))

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">{{ __('Create Webhook Endpoint') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('Send real-time updates to third-party systems by subscribing to events.') }}</p>
        </div>
        <a href="{{ route('webhook-endpoints.index') }}"
           class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
            {{ __('Back to Endpoints') }}
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="py-12">
    <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
        <div class="rounded-lg bg-white p-6 shadow-sm">
            @include('webhooks.partials.form', [
                'action' => route('webhook-endpoints.store'),
                'availableEvents' => $availableEvents,
                'submitLabel' => __('Create Endpoint')
            ])
        </div>
    </div>
</div>
@endsection
