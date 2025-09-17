<x-customer-portal.layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Profile Information -->
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Profile Information') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ __("Update your account's profile information and email address.") }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('customer-portal.profile.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('Name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <!-- Email -->
                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                            </div>

                            <!-- Phone -->
                            <div>
                                <x-input-label for="phone" :value="__('Phone Number')" />
                                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
                                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                            </div>

                            <!-- Company Name -->
                            <div>
                                <x-input-label for="company_name" :value="__('Company Name')" />
                                <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $user->company_name)" autocomplete="organization" />
                                <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                            </div>

                            <!-- Address -->
                            <div>
                                <x-input-label for="address" :value="__('Address')" />
                                <textarea id="address" name="address" rows="3" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">{{ old('address', $user->address) }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('address')" />
                            </div>

                            <!-- City, State, Postal Code -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="city" :value="__('City')" />
                                    <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $user->city)" />
                                    <x-input-error class="mt-2" :messages="$errors->get('city')" />
                                </div>
                                <div>
                                    <x-input-label for="state" :value="__('State')" />
                                    <x-text-input id="state" name="state" type="text" class="mt-1 block w-full" :value="old('state', $user->state)" />
                                    <x-input-error class="mt-2" :messages="$errors->get('state')" />
                                </div>
                                <div>
                                    <x-input-label for="postal_code" :value="__('Postal Code')" />
                                    <x-text-input id="postal_code" name="postal_code" type="text" class="mt-1 block w-full" :value="old('postal_code', $user->postal_code)" />
                                    <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
                                </div>
                            </div>

                            <!-- Country -->
                            <div>
                                <x-input-label for="country" :value="__('Country')" />
                                <select id="country" name="country" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                    <option value="Malaysia" {{ old('country', $user->country) == 'Malaysia' ? 'selected' : '' }}>Malaysia</option>
                                    <option value="Singapore" {{ old('country', $user->country) == 'Singapore' ? 'selected' : '' }}>Singapore</option>
                                    <option value="Thailand" {{ old('country', $user->country) == 'Thailand' ? 'selected' : '' }}>Thailand</option>
                                    <option value="Indonesia" {{ old('country', $user->country) == 'Indonesia' ? 'selected' : '' }}>Indonesia</option>
                                    <option value="Philippines" {{ old('country', $user->country) == 'Philippines' ? 'selected' : '' }}>Philippines</option>
                                    <option value="Vietnam" {{ old('country', $user->country) == 'Vietnam' ? 'selected' : '' }}>Vietnam</option>
                                    <option value="Other" {{ old('country', $user->country) == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('country')" />
                            </div>

                            <!-- Preferred Language -->
                            <div>
                                <x-input-label for="preferred_language" :value="__('Preferred Language')" />
                                <select id="preferred_language" name="preferred_language" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                    <option value="en" {{ old('preferred_language', $user->preferred_language) == 'en' ? 'selected' : '' }}>English</option>
                                    <option value="ms" {{ old('preferred_language', $user->preferred_language) == 'ms' ? 'selected' : '' }}>Bahasa Malaysia</option>
                                    <option value="zh" {{ old('preferred_language', $user->preferred_language) == 'zh' ? 'selected' : '' }}>中文</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('preferred_language')" />
                            </div>

                            <!-- Timezone -->
                            <div>
                                <x-input-label for="timezone" :value="__('Timezone')" />
                                <select id="timezone" name="timezone" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                    <option value="Asia/Kuala_Lumpur" {{ old('timezone', $user->timezone) == 'Asia/Kuala_Lumpur' ? 'selected' : '' }}>Malaysia (GMT+8)</option>
                                    <option value="Asia/Singapore" {{ old('timezone', $user->timezone) == 'Asia/Singapore' ? 'selected' : '' }}>Singapore (GMT+8)</option>
                                    <option value="Asia/Bangkok" {{ old('timezone', $user->timezone) == 'Asia/Bangkok' ? 'selected' : '' }}>Thailand (GMT+7)</option>
                                    <option value="Asia/Jakarta" {{ old('timezone', $user->timezone) == 'Asia/Jakarta' ? 'selected' : '' }}>Indonesia (GMT+7)</option>
                                    <option value="Asia/Manila" {{ old('timezone', $user->timezone) == 'Asia/Manila' ? 'selected' : '' }}>Philippines (GMT+8)</option>
                                    <option value="Asia/Ho_Chi_Minh" {{ old('timezone', $user->timezone) == 'Asia/Ho_Chi_Minh' ? 'selected' : '' }}>Vietnam (GMT+7)</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Save') }}</x-primary-button>

                                @if (session('success'))
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">{{ session('success') }}</p>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Update Password -->
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Update Password') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Ensure your account is using a long, random password to stay secure.') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('customer-portal.profile.password') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div>
                                <x-input-label for="current_password" :value="__('Current Password')" />
                                <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                                <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="password" :value="__('New Password')" />
                                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Save') }}</x-primary-button>

                                @if (session('success'))
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">{{ session('success') }}</p>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Notification Preferences -->
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Notification Preferences') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Choose which notifications you want to receive.') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('customer-portal.profile.preferences') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div class="space-y-4">
                                @php
                                    $notificationTypes = [
                                        'quotation_sent' => 'New quotations',
                                        'quotation_status' => 'Quotation status updates',
                                        'invoice_sent' => 'New invoices',
                                        'invoice_reminder' => 'Payment reminders',
                                        'payment_received' => 'Payment confirmations',
                                        'system_updates' => 'System updates and maintenance',
                                        'marketing' => 'Marketing communications',
                                    ];
                                    $currentPreferences = $user->notification_preferences ?? [];
                                @endphp

                                @foreach($notificationTypes as $key => $label)
                                    <div class="flex items-center">
                                        <input id="notification_{{ $key }}" 
                                               name="notification_preferences[{{ $key }}]" 
                                               type="checkbox" 
                                               value="1"
                                               {{ ($currentPreferences[$key] ?? true) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="notification_{{ $key }}" class="ml-3 block text-sm font-medium text-gray-700">
                                            {{ $label }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Save Preferences') }}</x-primary-button>

                                @if (session('success'))
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">{{ session('success') }}</p>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Deactivate Account -->
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Deactivate Account') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Once your account is deactivated, you will no longer be able to access the customer portal. You can contact support to reactivate your account.') }}
                            </p>
                        </header>

                        <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" class="mt-6 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Deactivate Account') }}
                        </button>

                        <!-- Deactivation Modal -->
                        <div id="confirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                    <form method="POST" action="{{ route('customer-portal.profile.destroy') }}">
                                        @csrf
                                        @method('delete')
                                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                            <div class="sm:flex sm:items-start">
                                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                    </svg>
                                                </div>
                                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                        Deactivate Account
                                                    </h3>
                                                    <div class="mt-2">
                                                        <p class="text-sm text-gray-500">
                                                            Are you sure you want to deactivate your account? This action will prevent you from accessing the customer portal.
                                                        </p>
                                                        <div class="mt-4">
                                                            <label for="password" class="block text-sm font-medium text-gray-700">Enter your password to confirm</label>
                                                            <input type="password" name="password" id="password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                Deactivate Account
                                            </button>
                                            <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('confirmModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('confirmModal').classList.add('hidden');
        }

        // Listen for the custom event to open modal
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('open-modal', function(event) {
                if (event.detail === 'confirm-user-deletion') {
                    openModal();
                }
            });
        });

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('confirmModal');
            if (event.target === modal) {
                closeModal();
            }
        });
    </script>
</x-customer-portal.layouts.app>