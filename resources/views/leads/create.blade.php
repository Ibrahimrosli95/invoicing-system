@extends('layouts.app')

@section('title', 'Create New Lead')

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Lead') }}
        </h2>
        <a href="{{ route('leads.index') }}"
           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Back to Leads
        </a>
    </div>
</div>
@endsection

@section('content')

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <strong>Please correct the following errors:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                            {{ session('warning') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('leads.store') }}">
                        @csrf

                        <!-- Lead Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Lead Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="name" :value="__('Name *')" />
                                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" 
                                                  :value="old('name')" required autofocus />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="phone" :value="__('Phone *')" />
                                    <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" 
                                                  :value="old('phone')" required placeholder="+60123456789" />
                                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                                    <p class="mt-1 text-sm text-gray-500">Malaysian format: +60123456789 or 0123456789</p>
                                </div>

                                <div>
                                    <x-input-label for="email" :value="__('Email')" />
                                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" 
                                                  :value="old('email')" />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="source" :value="__('Source *')" />
                                    <select id="source" name="source" required
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Select Source</option>
                                        <option value="website" {{ old('source') == 'website' ? 'selected' : '' }}>Website</option>
                                        <option value="referral" {{ old('source') == 'referral' ? 'selected' : '' }}>Referral</option>
                                        <option value="social_media" {{ old('source') == 'social_media' ? 'selected' : '' }}>Social Media</option>
                                        <option value="advertisement" {{ old('source') == 'advertisement' ? 'selected' : '' }}>Advertisement</option>
                                        <option value="phone_call" {{ old('source') == 'phone_call' ? 'selected' : '' }}>Phone Call</option>
                                        <option value="walk_in" {{ old('source') == 'walk_in' ? 'selected' : '' }}>Walk-in</option>
                                        <option value="trade_show" {{ old('source') == 'trade_show' ? 'selected' : '' }}>Trade Show</option>
                                        <option value="other" {{ old('source') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('source')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="urgency" :value="__('Urgency *')" />
                                    <select id="urgency" name="urgency" required
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Select Urgency</option>
                                        <option value="low" {{ old('urgency') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('urgency') == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('urgency') == 'high' ? 'selected' : '' }}>High</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('urgency')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="estimated_value" :value="__('Estimated Value (RM)')" />
                                    <x-text-input id="estimated_value" name="estimated_value" type="number" 
                                                  class="mt-1 block w-full" :value="old('estimated_value')" 
                                                  min="0" step="0.01" placeholder="0.00" />
                                    <x-input-error :messages="$errors->get('estimated_value')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Address Information</h3>
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <x-input-label for="address" :value="__('Address')" />
                                    <textarea id="address" name="address" rows="3"
                                              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                              placeholder="Street address, building name, unit number">{{ old('address') }}</textarea>
                                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <x-input-label for="city" :value="__('City')" />
                                        <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" 
                                                      :value="old('city')" placeholder="Kuala Lumpur" />
                                        <x-input-error :messages="$errors->get('city')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="state" :value="__('State')" />
                                        <x-text-input id="state" name="state" type="text" class="mt-1 block w-full" 
                                                      :value="old('state')" placeholder="Selangor" />
                                        <x-input-error :messages="$errors->get('state')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="postal_code" :value="__('Postal Code')" />
                                        <x-text-input id="postal_code" name="postal_code" type="text" class="mt-1 block w-full" 
                                                      :value="old('postal_code')" placeholder="50000" />
                                        <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assignment & Details -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Assignment & Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="team_id" :value="__('Team')" />
                                    <select id="team_id" name="team_id"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">No Team</option>
                                        @foreach($teams as $team)
                                            <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                                {{ $team->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('team_id')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="assigned_to" :value="__('Assign To')" />
                                    <select id="assigned_to" name="assigned_to"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Unassigned</option>
                                        @foreach($assignees as $assignee)
                                            <option value="{{ $assignee->id }}" {{ old('assigned_to') == $assignee->id ? 'selected' : '' }}>
                                                {{ $assignee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('assigned_to')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Requirements & Notes -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Requirements & Notes</h3>
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <x-input-label for="requirements" :value="__('Requirements')" />
                                    <textarea id="requirements" name="requirements" rows="4"
                                              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                              placeholder="Describe what the customer needs...">{{ old('requirements') }}</textarea>
                                    <x-input-error :messages="$errors->get('requirements')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="notes" :value="__('Initial Notes')" />
                                    <textarea id="notes" name="notes" rows="3"
                                              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                              placeholder="Any additional notes or observations...">{{ old('notes') }}</textarea>
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('leads.index') }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Create Lead') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection