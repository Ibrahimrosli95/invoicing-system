<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Company Settings') }}
            </h2>
            @can('update', $company)
                <a href="{{ route('company.edit') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                    {{ __('Edit Settings') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Company Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center mb-6">
                        <!-- Company Logo -->
                        <div class="flex-shrink-0 mr-6">
                            @if($company->logo)
                                <img src="{{ Storage::url($company->logo) }}" 
                                     alt="{{ $company->name }} Logo"
                                     class="h-16 w-16 object-cover rounded-lg border border-gray-200">
                            @else
                                <div class="h-16 w-16 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Company Details -->
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ $company->name }}</h3>
                            @if($company->tagline)
                                <p class="text-gray-600 mt-1">{{ $company->tagline }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Company Information Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        
                        <!-- Contact Information -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Contact Information</h4>
                            <dl class="space-y-2">
                                @if($company->email)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                                        <dd class="text-sm text-gray-900">{{ $company->email }}</dd>
                                    </div>
                                @endif
                                @if($company->phone)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                        <dd class="text-sm text-gray-900">{{ $company->phone }}</dd>
                                    </div>
                                @endif
                                @if($company->website)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Website</dt>
                                        <dd class="text-sm text-gray-900">
                                            <a href="{{ $company->website }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                {{ $company->website }}
                                            </a>
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>

                        <!-- Address Information -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Address</h4>
                            @if($company->address)
                                <address class="text-sm text-gray-900 not-italic">
                                    {{ $company->address }}<br>
                                    @if($company->city){{ $company->city }}@endif
                                    @if($company->state && $company->city), @endif
                                    @if($company->state){{ $company->state }}@endif
                                    @if($company->postal_code) {{ $company->postal_code }}@endif<br>
                                    @if($company->country){{ $company->country }}@endif
                                </address>
                            @else
                                <p class="text-sm text-gray-500">No address specified</p>
                            @endif
                        </div>

                        <!-- Business Information -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Business Information</h4>
                            <dl class="space-y-2">
                                @if($company->registration_number)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Registration Number</dt>
                                        <dd class="text-sm text-gray-900">{{ $company->registration_number }}</dd>
                                    </div>
                                @endif
                                @if($company->tax_number)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Tax Number</dt>
                                        <dd class="text-sm text-gray-900">{{ $company->tax_number }}</dd>
                                    </div>
                                @endif
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Timezone</dt>
                                    <dd class="text-sm text-gray-900">{{ $company->timezone ?? 'UTC' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Currency</dt>
                                    <dd class="text-sm text-gray-900">{{ $company->currency ?? 'USD' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branding & Customization -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Branding & Customization</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Color Scheme -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Color Scheme</h4>
                            <div class="flex space-x-4">
                                @if($company->primary_color)
                                    <div class="flex items-center space-x-2">
                                        <div class="w-8 h-8 rounded border border-gray-200" 
                                             style="background-color: {{ $company->primary_color }}"></div>
                                        <span class="text-sm text-gray-600">Primary: {{ $company->primary_color }}</span>
                                    </div>
                                @endif
                                @if($company->secondary_color)
                                    <div class="flex items-center space-x-2">
                                        <div class="w-8 h-8 rounded border border-gray-200" 
                                             style="background-color: {{ $company->secondary_color }}"></div>
                                        <span class="text-sm text-gray-600">Secondary: {{ $company->secondary_color }}</span>
                                    </div>
                                @endif
                            </div>
                            @if(!$company->primary_color && !$company->secondary_color)
                                <p class="text-sm text-gray-500">Default color scheme</p>
                            @endif
                        </div>

                        <!-- Document Settings -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Document Settings</h4>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Date Format</dt>
                                    <dd class="text-sm text-gray-900">{{ $company->date_format ?? 'Y-m-d' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Number Format</dt>
                                    <dd class="text-sm text-gray-900">{{ $company->number_format ?? 'Default' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">System Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Total Users</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $company->users->count() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Total Teams</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $company->teams->count() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="text-sm text-gray-900">{{ $company->created_at->format('M j, Y') }}</dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>