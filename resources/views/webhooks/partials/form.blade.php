@php
    $endpoint = $endpoint ?? null;
    $isEdit = (bool) $endpoint;
    $selectedEvents = old('events', $endpoint ? $endpoint->events : []);
    if (!is_array($selectedEvents)) {
        $selectedEvents = [];
    }
    $headersValue = old('headers', $endpoint && $endpoint->headers ? json_encode($endpoint->headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '');
@endphp

<form method="POST" action="{{ $action }}" class="space-y-8">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Endpoint Name *') }}</label>
            <input type="text" id="name" name="name" value="{{ old('name', $endpoint->name ?? '') }}" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="url" class="block text-sm font-medium text-gray-700">{{ __('Destination URL *') }}</label>
            <input type="url" id="url" name="url" value="{{ old('url', $endpoint->url ?? '') }}" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://example.com/webhook">
            @error('url')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="{{ __('Short note about this integration') }}">{{ old('description', $endpoint->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="rounded-lg border border-gray-200 bg-gray-50 p-5">
        <h4 class="text-sm font-semibold text-gray-800">{{ __('Subscribed Events *') }}</h4>
        <p class="mt-1 text-xs text-gray-500">{{ __('Select the events that should trigger this webhook. At least one event is required.') }}</p>
        <div class="mt-4 grid grid-cols-1 gap-2 md:grid-cols-2">
            @foreach($availableEvents as $eventKey => $label)
                <label class="inline-flex items-start gap-3 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm hover:border-indigo-200">
                    <input type="checkbox" name="events[]" value="{{ $eventKey }}" class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                           {{ in_array($eventKey, $selectedEvents, true) ? 'checked' : '' }}>
                    <span>
                        <span class="block font-medium text-gray-900">{{ $label }}</span>
                        <span class="block text-xs text-gray-500">{{ $eventKey }}</span>
                    </span>
                </label>
            @endforeach
        </div>
        @error('events')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <div>
            <label for="timeout" class="block text-sm font-medium text-gray-700">{{ __('Timeout (seconds)') }}</label>
            <input type="number" id="timeout" name="timeout" min="5" max="120" step="1"
                   value="{{ old('timeout', $endpoint->timeout ?? 30) }}"
                   class="mt-1 block w-full rounded-md border-gray-300  shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('timeout')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="max_retries" class="block text-sm font-medium text-gray-700">{{ __('Max Retries') }}</label>
            <input type="number" id="max_retries" name="max_retries" min="0" max="10" step="1"
                   value="{{ old('max_retries', $endpoint->max_retries ?? 3) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('max_retries')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex items-center pt-6">
            <label class="inline-flex items-center text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                       {{ old('is_active', $endpoint->is_active ?? true) ? 'checked' : '' }}>
                <span class="ml-2">{{ __('Enable endpoint immediately') }}</span>
            </label>
        </div>
    </div>

    <div>
        <label for="headers" class="block text-sm font-medium text-gray-700">{{ __('Custom Headers (JSON)') }}</label>
        <textarea id="headers" name="headers" rows="5"
                  class="mt-1 block w-full rounded-md border-gray-300 font-mono text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder='{"Authorization": "Bearer token", "X-Custom": "value"}'>{{ $headersValue }}</textarea>
        @error('headers')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
        <a href="{{ route('webhook-endpoints.index') }}"
           class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
            {{ __('Cancel') }}
        </a>
        <button type="submit"
                class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
            {{ $submitLabel }}
        </button>
    </div>
</form>
