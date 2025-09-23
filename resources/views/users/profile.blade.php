@extends('layouts.app')

@section('title', 'My Profile')

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('My Profile') }}
    </h2>
</div>
@endsection

@section('content')

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Profile Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center mb-6">
                        <!-- Avatar -->
                        <div class="flex-shrink-0 mr-6">
                            @if($user->avatar)
                                <img src="{{ Storage::url($user->avatar) }}" 
                                     alt="{{ $user->name }}"
                                     class="h-20 w-20 object-cover rounded-full border-2 border-gray-200">
                            @else
                                <div class="h-20 w-20 bg-gray-100 rounded-full border-2 border-gray-200 flex items-center justify-center">
                                    <span class="text-2xl font-medium text-gray-600">
                                        {{ substr($user->name, 0, 1) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- User Details -->
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h3>
                            <p class="text-gray-600 mb-2">{{ $user->email }}</p>
                            @if($user->phone)
                                <p class="text-gray-600 mb-2">{{ $user->phone }}</p>
                            @endif
                            
                            <!-- Roles -->
                            <div class="flex flex-wrap gap-2 mb-2">
                                @foreach($user->roles as $role)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                    </span>
                                @endforeach
                            </div>

                            <!-- Teams -->
                            @if($user->teams->isNotEmpty())
                                <div class="flex flex-wrap gap-2">
                                    @foreach($user->teams as $team)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $team->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Profile Update Form -->
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('Name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" 
                                              :value="old('name', $user->name)" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <!-- Email -->
                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" 
                                              :value="old('email', $user->email)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                            </div>

                            <!-- Phone -->
                            <div>
                                <x-input-label for="phone" :value="__('Phone')" />
                                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" 
                                              :value="old('phone', $user->phone)" />
                                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                            </div>

                            <!-- Avatar Upload -->
                            <div>
                                <x-input-label for="avatar" :value="__('Profile Picture')" />
                                <input type="file" id="avatar" name="avatar" accept="image/*" 
                                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                <p class="mt-1 text-sm text-gray-500">PNG, JPG up to 2MB</p>
                                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                            </div>
                        </div>

                        <!-- Password Update Section -->
                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Change Password</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Current Password -->
                                <div>
                                    <x-input-label for="current_password" :value="__('Current Password')" />
                                    <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full" />
                                    <x-input-error class="mt-2" :messages="$errors->get('current_password')" />
                                </div>

                                <!-- New Password -->
                                <div>
                                    <x-input-label for="password" :value="__('New Password')" />
                                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" />
                                    <x-input-error class="mt-2" :messages="$errors->get('password')" />
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
                                    <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end">
                            <x-primary-button>
                                {{ __('Update Profile') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Company Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Company Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Company</dt>
                            <dd class="text-sm text-gray-900">{{ $user->company->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                            <dd class="text-sm text-gray-900">{{ $user->created_at->format('M j, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                            <dd class="text-sm text-gray-900">{{ $user->updated_at->diffForHumans() }}</dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection