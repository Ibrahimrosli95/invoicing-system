@extends('layouts.app')

@section('title', 'Edit Team: ' . $team->name)

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Team: ') . $team->name }}
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
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('teams.update', $team) }}" class="space-y-6">
                        @csrf
                        @method('PATCH')

                        <!-- Team Name -->
                        <div>
                            <x-input-label for="name" :value="__('Team Name')" />
                            <x-text-input id="name" 
                                        class="block mt-1 w-full" 
                                        type="text" 
                                        name="name" 
                                        :value="old('name', $team->name)" 
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
                                    placeholder="Brief description of the team's focus or responsibilities">{{ old('description', $team->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Manager -->
                        <div>
                            <x-input-label for="manager_id" :value="__('Team Manager')" />
                            <select id="manager_id" 
                                    name="manager_id"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Select a manager</option>
                                @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}" 
                                            {{ old('manager_id', $team->manager_id) == $manager->id ? 'selected' : '' }}>
                                        {{ $manager->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('manager_id')" class="mt-2" />
                        </div>

                        <!-- Coordinator -->
                        <div>
                            <x-input-label for="coordinator_id" :value="__('Team Coordinator')" />
                            <select id="coordinator_id" 
                                    name="coordinator_id"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Select a coordinator</option>
                                @foreach($coordinators as $coordinator)
                                    <option value="{{ $coordinator->id }}" 
                                            {{ old('coordinator_id', $team->coordinator_id) == $coordinator->id ? 'selected' : '' }}>
                                        {{ $coordinator->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('coordinator_id')" class="mt-2" />
                        </div>

                        <!-- Territory -->
                        <div>
                            <x-input-label for="territory" :value="__('Territory/Region')" />
                            <x-text-input id="territory" 
                                        class="block mt-1 w-full" 
                                        type="text" 
                                        name="territory" 
                                        :value="old('territory', $team->territory)" 
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
                                        :value="old('target_revenue', $team->target_revenue)" 
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00" />
                            <x-input-error :messages="$errors->get('target_revenue')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">
                                Optional: Set monthly or annual revenue target for this team.
                            </p>
                        </div>

                        <!-- Active Status -->
                        <div class="flex items-center">
                            <input id="is_active" 
                                   name="is_active" 
                                   type="checkbox" 
                                   value="1"
                                   {{ old('is_active', $team->is_active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                Team is active
                            </label>
                        </div>

                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('teams.show', $team) }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Update Team') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection