@extends('layouts.app')

@section('title', 'Create Team')

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Team') }}
        </h2>
        <a href="{{ route('teams.index') }}"
           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Back to Teams
        </a>
    </div>
</div>
@endsection

@section('content')

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('teams.store') }}" class="space-y-6">
                        @csrf

                        <!-- Team Name -->
                        <div>
                            <x-input-label for="name" :value="__('Team Name')" />
                            <x-text-input id="name" 
                                        class="block mt-1 w-full" 
                                        type="text" 
                                        name="name" 
                                        :value="old('name')" 
                                        required 
                                        autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" 
                                    name="description" 
                                    rows="3"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                    placeholder="Brief description of the team's focus or responsibilities">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Manager -->
                        <div>
                            <x-input-label for="manager_id" :value="__('Team Manager')" />
                            <select id="manager_id" 
                                    name="manager_id"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Select a manager</option>
                                @if($recommendedManagers->isNotEmpty())
                                    <optgroup label="Recommended">
                                        @foreach($recommendedManagers as $manager)
                                            <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                                {{ $manager->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @php
                                    $otherManagers = $users->whereNotIn('id', $recommendedManagers->pluck('id'));
                                @endphp
                                @if($otherManagers->isNotEmpty())
                                    <optgroup label="Other team members">
                                        @foreach($otherManagers as $user)
                                            <option value="{{ $user->id }}" {{ old('manager_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                                @if($user->roles->isNotEmpty())
                                                    ({{ $user->roles->pluck('name')->implode(', ') }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                            <p class="mt-1 text-xs text-gray-500">If the selected user is not yet a sales manager, the role will be assigned automatically.</p>
                            <x-input-error :messages="$errors->get('manager_id')" class="mt-2" />
                        </div>

                        <!-- Coordinator -->
                        <div>
                            <x-input-label for="coordinator_id" :value="__('Team Coordinator')" />
                            <select id="coordinator_id" 
                                    name="coordinator_id"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Select a coordinator</option>
                                @if($recommendedCoordinators->isNotEmpty())
                                    <optgroup label="Recommended">
                                        @foreach($recommendedCoordinators as $coordinator)
                                            <option value="{{ $coordinator->id }}" {{ old('coordinator_id') == $coordinator->id ? 'selected' : '' }}>
                                                {{ $coordinator->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @php
                                    $otherCoordinators = $users->whereNotIn('id', $recommendedCoordinators->pluck('id'));
                                @endphp
                                @if($otherCoordinators->isNotEmpty())
                                    <optgroup label="Other team members">
                                        @foreach($otherCoordinators as $user)
                                            <option value="{{ $user->id }}" {{ old('coordinator_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                                @if($user->roles->isNotEmpty())
                                                    ({{ $user->roles->pluck('name')->implode(', ') }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                            <p class="mt-1 text-xs text-gray-500">If needed, the sales coordinator role will be assigned automatically.</p>
                            <x-input-error :messages="$errors->get('coordinator_id')" class="mt-2" />
                        </div>

                        <!-- Territory -->
                        <div>
                            <x-input-label for="territory" :value="__('Territory/Region')" />
                            <x-text-input id="territory" 
                                        class="block mt-1 w-full" 
                                        type="text" 
                                        name="territory" 
                                        :value="old('territory')" 
                                        placeholder="e.g. North Region, Klang Valley, etc." />
                            <x-input-error :messages="$errors->get('territory')" class="mt-2" />
                        </div>

                        <!-- Target Revenue -->
                        <div>
                            <x-input-label for="target_revenue" :value="__('Target Revenue (RM)')" />
                            <x-text-input id="target_revenue" 
                                        class="block mt-1 w-full" 
                                        type="number" 
                                        name="target_revenue" 
                                        :value="old('target_revenue')" 
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00" />
                            <x-input-error :messages="$errors->get('target_revenue')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">
                                Optional: Set monthly or annual revenue target for this team.
                            </p>
                        </div>

                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('teams.index') }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Create Team') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
