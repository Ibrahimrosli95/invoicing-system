@extends('layouts.app')

@section('title', 'Team Settings: ' . $team->name)

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Team Settings: ') . $team->name }}
        </h2>
        <div class="flex space-x-2">
            <a href="{{ route('teams.show', $team) }}"
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                View Team
            </a>
            <a href="{{ route('teams.index') }}"
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Teams
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Team Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center space-x-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $team->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $team->description ?: 'No description' }}</p>
                        </div>
                        <div class="flex-1"></div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Manager</p>
                            <p class="text-sm font-medium text-gray-900">{{ $team->manager?->name ?: 'Not assigned' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('teams.update-settings', $team) }}" class="space-y-6">
                @csrf
                @method('PATCH')

                <!-- Default Terms & Conditions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Default Terms & Notes</h3>
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="default_terms" :value="__('Default Terms & Conditions')" />
                                <textarea id="default_terms" 
                                        name="default_terms" 
                                        rows="6"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        placeholder="Enter default terms and conditions that will be used in quotations for this team...">{{ old('default_terms', $team->settings['default_terms'] ?? '') }}</textarea>
                                <x-input-error :messages="$errors->get('default_terms')" class="mt-2" />
                                <p class="mt-1 text-sm text-gray-500">
                                    These terms will be automatically included in new quotations created by team members.
                                </p>
                            </div>

                            <div>
                                <x-input-label for="default_notes" :value="__('Default Internal Notes')" />
                                <textarea id="default_notes" 
                                        name="default_notes" 
                                        rows="4"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        placeholder="Enter default internal notes for this team...">{{ old('default_notes', $team->settings['default_notes'] ?? '') }}</textarea>
                                <x-input-error :messages="$errors->get('default_notes')" class="mt-2" />
                                <p class="mt-1 text-sm text-gray-500">
                                    Internal notes that will be pre-filled for new quotations (not visible to customers).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Goals -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Performance Goals</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="monthly_quota" :value="__('Monthly Revenue Quota (RM)')" />
                                <x-text-input id="monthly_quota" 
                                            class="block mt-1 w-full" 
                                            type="number" 
                                            name="performance_goals[monthly_quota]" 
                                            :value="old('performance_goals.monthly_quota', $team->settings['performance_goals']['monthly_quota'] ?? '')" 
                                            step="0.01"
                                            min="0"
                                            placeholder="0.00" />
                                <x-input-error :messages="$errors->get('performance_goals.monthly_quota')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="annual_quota" :value="__('Annual Revenue Quota (RM)')" />
                                <x-text-input id="annual_quota" 
                                            class="block mt-1 w-full" 
                                            type="number" 
                                            name="performance_goals[annual_quota]" 
                                            :value="old('performance_goals.annual_quota', $team->settings['performance_goals']['annual_quota'] ?? '')" 
                                            step="0.01"
                                            min="0"
                                            placeholder="0.00" />
                                <x-input-error :messages="$errors->get('performance_goals.annual_quota')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="conversion_target" :value="__('Conversion Rate Target (%)')" />
                                <x-text-input id="conversion_target" 
                                            class="block mt-1 w-full" 
                                            type="number" 
                                            name="performance_goals[conversion_target]" 
                                            :value="old('performance_goals.conversion_target', $team->settings['performance_goals']['conversion_target'] ?? '')" 
                                            step="0.1"
                                            min="0"
                                            max="100"
                                            placeholder="0.0" />
                                <x-input-error :messages="$errors->get('performance_goals.conversion_target')" class="mt-2" />
                                <p class="mt-1 text-sm text-gray-500">
                                    Target percentage of leads that should convert to sales.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notification Preferences -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Notification Preferences</h3>
                        <p class="text-sm text-gray-600 mb-4">Configure when team members and managers should receive notifications:</p>
                        
                        <div class="space-y-4">
                            @php
                                $notifications = [
                                    'new_lead_assigned' => 'When a new lead is assigned to the team',
                                    'quotation_created' => 'When a team member creates a quotation',
                                    'quotation_sent' => 'When a quotation is sent to a customer',
                                    'quotation_accepted' => 'When a quotation is accepted by a customer',
                                    'invoice_overdue' => 'When an invoice becomes overdue',
                                    'monthly_report' => 'Send monthly performance reports',
                                    'target_achievement' => 'When team achieves performance targets',
                                ];
                                $currentPrefs = $team->settings['notification_preferences'] ?? [];
                            @endphp

                            @foreach($notifications as $key => $label)
                                <label class="flex items-center space-x-3">
                                    <input type="hidden" name="notification_preferences[{{ $key }}]" value="0">
                                    <input type="checkbox" 
                                           name="notification_preferences[{{ $key }}]" 
                                           value="1"
                                           {{ old("notification_preferences.{$key}", $currentPrefs[$key] ?? false) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Current Settings Summary -->
                @if($team->settings)
                    <div class="bg-gray-50 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Current Settings Summary</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Performance Goals</h4>
                                    <dl class="text-sm space-y-1">
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500">Monthly Quota:</dt>
                                            <dd class="font-medium">
                                                @if(isset($team->settings['performance_goals']['monthly_quota']))
                                                    RM {{ number_format($team->settings['performance_goals']['monthly_quota'], 2) }}
                                                @else
                                                    Not set
                                                @endif
                                            </dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500">Annual Quota:</dt>
                                            <dd class="font-medium">
                                                @if(isset($team->settings['performance_goals']['annual_quota']))
                                                    RM {{ number_format($team->settings['performance_goals']['annual_quota'], 2) }}
                                                @else
                                                    Not set
                                                @endif
                                            </dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500">Conversion Target:</dt>
                                            <dd class="font-medium">
                                                @if(isset($team->settings['performance_goals']['conversion_target']))
                                                    {{ $team->settings['performance_goals']['conversion_target'] }}%
                                                @else
                                                    Not set
                                                @endif
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Notifications</h4>
                                    <div class="text-sm">
                                        @if(isset($team->settings['notification_preferences']))
                                            @php
                                                $activeNotifications = array_filter($team->settings['notification_preferences']);
                                            @endphp
                                            @if(count($activeNotifications) > 0)
                                                <p class="text-green-600">{{ count($activeNotifications) }} notification types enabled</p>
                                            @else
                                                <p class="text-gray-500">No notifications enabled</p>
                                            @endif
                                        @else
                                            <p class="text-gray-500">No notification preferences set</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('teams.show', $team) }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Cancel
                    </a>
                    <x-primary-button>
                        {{ __('Update Settings') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
@endsection