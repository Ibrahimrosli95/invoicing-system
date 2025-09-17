<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Company Settings') }}
            </h2>
            <a href="{{ route('company.show') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                {{ __('Cancel') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('company.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PATCH')

                <!-- Company Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 mb-6">Company Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Company Name -->
                            <div class="md:col-span-2">
                                <x-input-label for="name" :value="__('Company Name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" 
                                              :value="old('name', $company->name)" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <!-- Tagline -->
                            <div class="md:col-span-2">
                                <x-input-label for="tagline" :value="__('Tagline')" />
                                <x-text-input id="tagline" name="tagline" type="text" class="mt-1 block w-full" 
                                              :value="old('tagline', $company->tagline)" />
                                <x-input-error class="mt-2" :messages="$errors->get('tagline')" />
                            </div>

                            <!-- Email -->
                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" 
                                              :value="old('email', $company->email)" />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                            </div>

                            <!-- Phone -->
                            <div>
                                <x-input-label for="phone" :value="__('Phone')" />
                                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" 
                                              :value="old('phone', $company->phone)" />
                                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                            </div>

                            <!-- Website -->
                            <div>
                                <x-input-label for="website" :value="__('Website')" />
                                <x-text-input id="website" name="website" type="url" class="mt-1 block w-full" 
                                              :value="old('website', $company->website)" />
                                <x-input-error class="mt-2" :messages="$errors->get('website')" />
                            </div>

                            <!-- Registration Number -->
                            <div>
                                <x-input-label for="registration_number" :value="__('Registration Number')" />
                                <x-text-input id="registration_number" name="registration_number" type="text" class="mt-1 block w-full" 
                                              :value="old('registration_number', $company->registration_number)" />
                                <x-input-error class="mt-2" :messages="$errors->get('registration_number')" />
                            </div>

                            <!-- Tax Number -->
                            <div>
                                <x-input-label for="tax_number" :value="__('Tax Number')" />
                                <x-text-input id="tax_number" name="tax_number" type="text" class="mt-1 block w-full" 
                                              :value="old('tax_number', $company->tax_number)" />
                                <x-input-error class="mt-2" :messages="$errors->get('tax_number')" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 mb-6">Address Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Address -->
                            <div class="md:col-span-2">
                                <x-input-label for="address" :value="__('Street Address')" />
                                <textarea id="address" name="address" rows="3" 
                                          class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('address', $company->address) }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('address')" />
                            </div>

                            <!-- City -->
                            <div>
                                <x-input-label for="city" :value="__('City')" />
                                <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" 
                                              :value="old('city', $company->city)" />
                                <x-input-error class="mt-2" :messages="$errors->get('city')" />
                            </div>

                            <!-- State -->
                            <div>
                                <x-input-label for="state" :value="__('State/Province')" />
                                <x-text-input id="state" name="state" type="text" class="mt-1 block w-full" 
                                              :value="old('state', $company->state)" />
                                <x-input-error class="mt-2" :messages="$errors->get('state')" />
                            </div>

                            <!-- Postal Code -->
                            <div>
                                <x-input-label for="postal_code" :value="__('Postal Code')" />
                                <x-text-input id="postal_code" name="postal_code" type="text" class="mt-1 block w-full" 
                                              :value="old('postal_code', $company->postal_code)" />
                                <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
                            </div>

                            <!-- Country -->
                            <div>
                                <x-input-label for="country" :value="__('Country')" />
                                <select id="country" name="country" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Select Country</option>
                                    <option value="MY" {{ old('country', $company->country) == 'MY' ? 'selected' : '' }}>Malaysia</option>
                                    <option value="SG" {{ old('country', $company->country) == 'SG' ? 'selected' : '' }}>Singapore</option>
                                    <option value="TH" {{ old('country', $company->country) == 'TH' ? 'selected' : '' }}>Thailand</option>
                                    <option value="ID" {{ old('country', $company->country) == 'ID' ? 'selected' : '' }}>Indonesia</option>
                                    <option value="PH" {{ old('country', $company->country) == 'PH' ? 'selected' : '' }}>Philippines</option>
                                    <option value="VN" {{ old('country', $company->country) == 'VN' ? 'selected' : '' }}>Vietnam</option>
                                    <option value="US" {{ old('country', $company->country) == 'US' ? 'selected' : '' }}>United States</option>
                                    <option value="GB" {{ old('country', $company->country) == 'GB' ? 'selected' : '' }}>United Kingdom</option>
                                    <option value="AU" {{ old('country', $company->country) == 'AU' ? 'selected' : '' }}>Australia</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('country')" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Branding & Customization -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 mb-6">Branding & Customization</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Logo Upload -->
                            <div class="md:col-span-2">
                                <x-input-label for="logo" :value="__('Company Logo')" />
                                
                                @if($company->logo)
                                    <div class="mt-2 mb-4">
                                        <p class="text-sm text-gray-600 mb-2">Current logo:</p>
                                        <img src="{{ Storage::url($company->logo) }}" 
                                             alt="Current Logo" 
                                             class="h-20 w-20 object-cover rounded-lg border border-gray-200">
                                    </div>
                                @endif
                                
                                <input type="file" id="logo" name="logo" accept="image/*" 
                                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                <p class="mt-1 text-sm text-gray-500">PNG, JPG up to 2MB. Recommended size: 200x200px</p>
                                <x-input-error class="mt-2" :messages="$errors->get('logo')" />
                            </div>

                            <!-- Primary Color -->
                            <div>
                                <x-input-label for="primary_color" :value="__('Primary Color')" />
                                <div class="mt-1 flex">
                                    <input type="color" id="primary_color" name="primary_color" 
                                           class="h-10 w-16 border border-gray-300 rounded-l-md"
                                           value="{{ old('primary_color', $company->primary_color ?? '#2563EB') }}">
                                    <x-text-input name="primary_color_text" type="text" 
                                                  class="rounded-l-none border-l-0" 
                                                  :value="old('primary_color', $company->primary_color ?? '#2563EB')"
                                                  x-data x-init="$watch('$el.previousElementSibling.value', value => $el.value = value)" />
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('primary_color')" />
                            </div>

                            <!-- Secondary Color -->
                            <div>
                                <x-input-label for="secondary_color" :value="__('Secondary Color')" />
                                <div class="mt-1 flex">
                                    <input type="color" id="secondary_color" name="secondary_color" 
                                           class="h-10 w-16 border border-gray-300 rounded-l-md"
                                           value="{{ old('secondary_color', $company->secondary_color ?? '#10B981') }}">
                                    <x-text-input name="secondary_color_text" type="text" 
                                                  class="rounded-l-none border-l-0" 
                                                  :value="old('secondary_color', $company->secondary_color ?? '#10B981')"
                                                  x-data x-init="$watch('$el.previousElementSibling.value', value => $el.value = value)" />
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('secondary_color')" />
                            </div>

                            <!-- Timezone -->
                            <div>
                                <x-input-label for="timezone" :value="__('Timezone')" />
                                <select id="timezone" name="timezone" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="Asia/Kuala_Lumpur" {{ old('timezone', $company->timezone) == 'Asia/Kuala_Lumpur' ? 'selected' : '' }}>Asia/Kuala_Lumpur (GMT+8)</option>
                                    <option value="Asia/Singapore" {{ old('timezone', $company->timezone) == 'Asia/Singapore' ? 'selected' : '' }}>Asia/Singapore (GMT+8)</option>
                                    <option value="Asia/Bangkok" {{ old('timezone', $company->timezone) == 'Asia/Bangkok' ? 'selected' : '' }}>Asia/Bangkok (GMT+7)</option>
                                    <option value="Asia/Jakarta" {{ old('timezone', $company->timezone) == 'Asia/Jakarta' ? 'selected' : '' }}>Asia/Jakarta (GMT+7)</option>
                                    <option value="UTC" {{ old('timezone', $company->timezone) == 'UTC' ? 'selected' : '' }}>UTC (GMT+0)</option>
                                    <option value="America/New_York" {{ old('timezone', $company->timezone) == 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                                    <option value="Europe/London" {{ old('timezone', $company->timezone) == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
                            </div>

                            <!-- Currency -->
                            <div>
                                <x-input-label for="currency" :value="__('Currency')" />
                                <select id="currency" name="currency" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="MYR" {{ old('currency', $company->currency) == 'MYR' ? 'selected' : '' }}>MYR - Malaysian Ringgit</option>
                                    <option value="SGD" {{ old('currency', $company->currency) == 'SGD' ? 'selected' : '' }}>SGD - Singapore Dollar</option>
                                    <option value="THB" {{ old('currency', $company->currency) == 'THB' ? 'selected' : '' }}>THB - Thai Baht</option>
                                    <option value="IDR" {{ old('currency', $company->currency) == 'IDR' ? 'selected' : '' }}>IDR - Indonesian Rupiah</option>
                                    <option value="USD" {{ old('currency', $company->currency) == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                    <option value="EUR" {{ old('currency', $company->currency) == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                    <option value="GBP" {{ old('currency', $company->currency) == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('currency')" />
                            </div>

                            <!-- Date Format -->
                            <div>
                                <x-input-label for="date_format" :value="__('Date Format')" />
                                <select id="date_format" name="date_format" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="Y-m-d" {{ old('date_format', $company->date_format) == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD ({{ date('Y-m-d') }})</option>
                                    <option value="d/m/Y" {{ old('date_format', $company->date_format) == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY ({{ date('d/m/Y') }})</option>
                                    <option value="m/d/Y" {{ old('date_format', $company->date_format) == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY ({{ date('m/d/Y') }})</option>
                                    <option value="d-M-Y" {{ old('date_format', $company->date_format) == 'd-M-Y' ? 'selected' : '' }}>DD-MMM-YYYY ({{ date('d-M-Y') }})</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('date_format')" />
                            </div>

                            <!-- Number Format -->
                            <div>
                                <x-input-label for="number_format" :value="__('Number Format')" />
                                <select id="number_format" name="number_format" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="1,234.56" {{ old('number_format', $company->number_format) == '1,234.56' ? 'selected' : '' }}>1,234.56 (Comma thousands, dot decimal)</option>
                                    <option value="1.234,56" {{ old('number_format', $company->number_format) == '1.234,56' ? 'selected' : '' }}>1.234,56 (Dot thousands, comma decimal)</option>
                                    <option value="1 234.56" {{ old('number_format', $company->number_format) == '1 234.56' ? 'selected' : '' }}>1 234.56 (Space thousands, dot decimal)</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('number_format')" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('company.show') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Save Changes') }}
                            </x-primary-button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Sync color picker with text input
        document.querySelectorAll('input[type="color"]').forEach(colorInput => {
            const textInput = colorInput.nextElementSibling;
            
            colorInput.addEventListener('input', function() {
                textInput.value = this.value;
            });
            
            textInput.addEventListener('input', function() {
                if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                    colorInput.value = this.value;
                }
            });
        });
    </script>
    @endpush
</x-app-layout>