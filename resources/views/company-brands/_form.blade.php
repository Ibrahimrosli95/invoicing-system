{{-- Brand Information Section --}}
<div class="bg-white shadow rounded-lg p-6 mb-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Brand Information</h3>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        {{-- Trading Name --}}
        <div class="sm:col-span-2">
            <label for="name" class="block text-sm font-medium text-gray-700">
                Trading Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" id="name" value="{{ old('name', $brand->name ?? '') }}" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Legal Name --}}
        <div>
            <label for="legal_name" class="block text-sm font-medium text-gray-700">Legal Name</label>
            <input type="text" name="legal_name" id="legal_name" value="{{ old('legal_name', $brand->legal_name ?? '') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('legal_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Registration Number --}}
        <div>
            <label for="registration_number" class="block text-sm font-medium text-gray-700">Registration Number</label>
            <input type="text" name="registration_number" id="registration_number" value="{{ old('registration_number', $brand->registration_number ?? '') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('registration_number')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Logo Upload --}}
        <div class="sm:col-span-2">
            <label for="logo" class="block text-sm font-medium text-gray-700">Logo</label>
            <div class="mt-1 flex items-center space-x-4">
                @if(isset($brand) && $brand->logo_path)
                    <img src="{{ $brand->getLogoUrl() }}" alt="Current logo" class="h-16 w-auto object-contain border border-gray-300 rounded">
                @endif
                <input type="file" name="logo" id="logo" accept="image/*"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>
            <p class="mt-1 text-sm text-gray-500">Recommended: 300x100px, Max 2MB</p>
            @error('logo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tagline --}}
        <div class="sm:col-span-2">
            <label for="tagline" class="block text-sm font-medium text-gray-700">Tagline</label>
            <input type="text" name="tagline" id="tagline" value="{{ old('tagline', $brand->tagline ?? '') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('tagline')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

{{-- Contact Details Section --}}
<div class="bg-white shadow rounded-lg p-6 mb-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Details</h3>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        {{-- Address --}}
        <div class="sm:col-span-2">
            <label for="address" class="block text-sm font-medium text-gray-700">
                Address <span class="text-red-500">*</span>
            </label>
            <textarea name="address" id="address" rows="3" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('address', $brand->address ?? '') }}</textarea>
            @error('address')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- City --}}
        <div>
            <label for="city" class="block text-sm font-medium text-gray-700">
                City <span class="text-red-500">*</span>
            </label>
            <input type="text" name="city" id="city" value="{{ old('city', $brand->city ?? '') }}" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('city')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- State --}}
        <div>
            <label for="state" class="block text-sm font-medium text-gray-700">
                State <span class="text-red-500">*</span>
            </label>
            <input type="text" name="state" id="state" value="{{ old('state', $brand->state ?? '') }}" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('state')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Postal Code --}}
        <div>
            <label for="postal_code" class="block text-sm font-medium text-gray-700">
                Postal Code <span class="text-red-500">*</span>
            </label>
            <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $brand->postal_code ?? '') }}" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('postal_code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Phone --}}
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">
                Phone <span class="text-red-500">*</span>
            </label>
            <input type="text" name="phone" id="phone" value="{{ old('phone', $brand->phone ?? '') }}" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="+60123456789">
            @error('phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
                Email <span class="text-red-500">*</span>
            </label>
            <input type="email" name="email" id="email" value="{{ old('email', $brand->email ?? '') }}" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Website --}}
        <div>
            <label for="website" class="block text-sm font-medium text-gray-700">Website</label>
            <input type="url" name="website" id="website" value="{{ old('website', $brand->website ?? '') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="https://example.com">
            @error('website')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

{{-- Bank Details Section --}}
<div class="bg-white shadow rounded-lg p-6 mb-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Bank Details (Optional)</h3>
    <p class="text-sm text-gray-500 mb-4">Leave blank to use company default bank details</p>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        {{-- Bank Name --}}
        <div>
            <label for="bank_name" class="block text-sm font-medium text-gray-700">Bank Name</label>
            <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $brand->bank_name ?? '') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('bank_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Account Name --}}
        <div>
            <label for="bank_account_name" class="block text-sm font-medium text-gray-700">Account Name</label>
            <input type="text" name="bank_account_name" id="bank_account_name" value="{{ old('bank_account_name', $brand->bank_account_name ?? '') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('bank_account_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Account Number --}}
        <div class="sm:col-span-2">
            <label for="bank_account_number" class="block text-sm font-medium text-gray-700">Account Number</label>
            <input type="text" name="bank_account_number" id="bank_account_number" value="{{ old('bank_account_number', $brand->bank_account_number ?? '') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('bank_account_number')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

{{-- Brand Colors Section --}}
<div class="bg-white shadow rounded-lg p-6 mb-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Brand Colors (Optional)</h3>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        {{-- Primary Color --}}
        <div>
            <label for="color_primary" class="block text-sm font-medium text-gray-700">Primary Color</label>
            <div class="mt-1 flex items-center space-x-3">
                <input type="color" name="color_primary" id="color_primary" value="{{ old('color_primary', $brand->color_primary ?? '#2563EB') }}"
                    class="h-10 w-20 border-gray-300 rounded">
                <input type="text" value="{{ old('color_primary', $brand->color_primary ?? '#2563EB') }}"
                    class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="#2563EB" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$">
            </div>
            @error('color_primary')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Secondary Color --}}
        <div>
            <label for="color_secondary" class="block text-sm font-medium text-gray-700">Secondary Color</label>
            <div class="mt-1 flex items-center space-x-3">
                <input type="color" name="color_secondary" id="color_secondary" value="{{ old('color_secondary', $brand->color_secondary ?? '#1E40AF') }}"
                    class="h-10 w-20 border-gray-300 rounded">
                <input type="text" value="{{ old('color_secondary', $brand->color_secondary ?? '#1E40AF') }}"
                    class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="#1E40AF" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$">
            </div>
            @error('color_secondary')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

{{-- Settings Section --}}
<div class="bg-white shadow rounded-lg p-6 mb-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Settings</h3>

    <div class="space-y-4">
        {{-- Is Default --}}
        <div class="flex items-center">
            <input type="checkbox" name="is_default" id="is_default" value="1" {{ old('is_default', $brand->is_default ?? false) ? 'checked' : '' }}
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="is_default" class="ml-2 block text-sm text-gray-900">
                Set as default brand (will be pre-selected for new documents)
            </label>
        </div>

        {{-- Is Active --}}
        <div class="flex items-center">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $brand->is_active ?? true) ? 'checked' : '' }}
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                Active (available for use in quotations and invoices)
            </label>
        </div>
    </div>
</div>
