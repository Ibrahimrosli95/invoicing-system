<x-customer-portal.layouts.guest>
    <div class="mb-4 text-center">
        <h2 class="text-xl font-semibold text-gray-900">Create Account</h2>
        <p class="text-sm text-gray-600 mt-1">Join our customer portal</p>
    </div>

    <form method="POST" action="{{ route('customer-portal.register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Phone -->
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone Number')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Company Name -->
        <div class="mt-4">
            <x-input-label for="company_name" :value="__('Company Name')" />
            <x-text-input id="company_name" class="block mt-1 w-full" type="text" name="company_name" :value="old('company_name')" autocomplete="organization" />
            <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
        </div>

        <!-- Address -->
        <div class="mt-4">
            <x-input-label for="address" :value="__('Address')" />
            <textarea id="address" name="address" rows="2" class="block mt-1 w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm">{{ old('address') }}</textarea>
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <!-- City, State, Postal Code in a row -->
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <x-input-label for="city" :value="__('City')" />
                <x-text-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city')" />
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="state" :value="__('State')" />
                <x-text-input id="state" class="block mt-1 w-full" type="text" name="state" :value="old('state')" />
                <x-input-error :messages="$errors->get('state')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="postal_code" :value="__('Postal Code')" />
                <x-text-input id="postal_code" class="block mt-1 w-full" type="text" name="postal_code" :value="old('postal_code')" />
                <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
            </div>
        </div>

        <!-- Country -->
        <div class="mt-4">
            <x-input-label for="country" :value="__('Country')" />
            <select id="country" name="country" class="block mt-1 w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm">
                <option value="Malaysia" {{ old('country', 'Malaysia') == 'Malaysia' ? 'selected' : '' }}>Malaysia</option>
                <option value="Singapore" {{ old('country') == 'Singapore' ? 'selected' : '' }}>Singapore</option>
                <option value="Thailand" {{ old('country') == 'Thailand' ? 'selected' : '' }}>Thailand</option>
                <option value="Indonesia" {{ old('country') == 'Indonesia' ? 'selected' : '' }}>Indonesia</option>
                <option value="Philippines" {{ old('country') == 'Philippines' ? 'selected' : '' }}>Philippines</option>
                <option value="Vietnam" {{ old('country') == 'Vietnam' ? 'selected' : '' }}>Vietnam</option>
                <option value="Other" {{ old('country') == 'Other' ? 'selected' : '' }}>Other</option>
            </select>
            <x-input-error :messages="$errors->get('country')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="text-sm text-primary-600 hover:text-primary-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" href="{{ route('customer-portal.login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ml-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-customer-portal.layouts.guest>