@extends('layouts.app')

@section('title', 'Notification Preferences')

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Notification Preferences') }}
    </h2>
</div>
@endsection

@section('content')

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Email & Push Notifications</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Control when and how you receive notifications about important events.
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <button type="button" 
                                    onclick="enableAllNotifications()" 
                                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                                Enable All
                            </button>
                            <button type="button" 
                                    onclick="disableAllNotifications()" 
                                    class="px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 focus:ring-2 focus:ring-gray-500">
                                Disable All
                            </button>
                            <button type="button" 
                                    onclick="resetToDefaults()" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-md hover:bg-gray-50 focus:ring-2 focus:ring-blue-500">
                                Reset to Defaults
                            </button>
                        </div>
                    </div>

                    <!-- Notification Categories -->
                    <div class="space-y-8">
                        @foreach($groupedPreferences as $category => $types)
                            <div class="border border-gray-200 rounded-lg">
                                <!-- Category Header -->
                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 rounded-t-lg">
                                    <h4 class="font-medium text-gray-900 flex items-center">
                                        <span class="mr-2">
                                            @switch($category)
                                                @case('Lead Notifications')
                                                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                    </svg>
                                                    @break
                                                @case('Quotation Notifications')
                                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z"/>
                                                    </svg>
                                                    @break
                                                @case('Invoice Notifications')
                                                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"/>
                                                    </svg>
                                                    @break
                                                @default
                                                    <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
                                                    </svg>
                                            @endswitch
                                        </span>
                                        {{ $category }}
                                    </h4>
                                </div>

                                <!-- Notification Types -->
                                <div class="p-4 space-y-4">
                                    @foreach($types as $type)
                                        @if(isset($preferences[$type]))
                                            @php $pref = $preferences[$type]; @endphp
                                            <div class="flex items-center justify-between py-2">
                                                <div class="flex-1">
                                                    <label class="font-medium text-gray-900">{{ $pref['label'] }}</label>
                                                    <p class="text-sm text-gray-500 mt-1">
                                                        @switch($type)
                                                            @case('lead_assigned')
                                                                Receive notifications when a lead is assigned to you
                                                                @break
                                                            @case('lead_status_changed')
                                                                Get updates when lead status changes in your team
                                                                @break
                                                            @case('quotation_sent')
                                                                Notifications when quotations are sent to customers
                                                                @break
                                                            @case('quotation_accepted')
                                                                Alerts when customers accept quotations
                                                                @break
                                                            @case('invoice_overdue')
                                                                Urgent alerts for overdue invoice payments
                                                                @break
                                                            @case('invoice_payment_received')
                                                                Confirmations when payments are received
                                                                @break
                                                            @default
                                                                Stay informed about {{ strtolower(str_replace('_', ' ', $type)) }} events
                                                        @endswitch
                                                    </p>
                                                </div>
                                                
                                                <div class="flex items-center space-x-4 ml-6">
                                                    <!-- Email Toggle -->
                                                    <div class="flex items-center">
                                                        <label class="text-sm text-gray-700 mr-2">Email</label>
                                                        <button type="button" 
                                                                onclick="toggleNotification('{{ $type }}', 'email')" 
                                                                id="email-{{ $type }}"
                                                                class="notification-toggle {{ $pref['email_enabled'] ? 'bg-blue-600' : 'bg-gray-200' }} relative inline-flex items-center h-6 rounded-full w-11 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                                            <span class="sr-only">Enable email notifications for {{ $pref['label'] }}</span>
                                                            <span class="toggle-thumb {{ $pref['email_enabled'] ? 'translate-x-6' : 'translate-x-1' }} inline-block w-4 h-4 transform bg-white rounded-full transition-transform duration-200"></span>
                                                        </button>
                                                    </div>

                                                    <!-- Push Toggle -->
                                                    <div class="flex items-center">
                                                        <label class="text-sm text-gray-700 mr-2">Push</label>
                                                        <button type="button" 
                                                                onclick="toggleNotification('{{ $type }}', 'push')" 
                                                                id="push-{{ $type }}"
                                                                class="notification-toggle {{ $pref['push_enabled'] ? 'bg-blue-600' : 'bg-gray-200' }} relative inline-flex items-center h-6 rounded-full w-11 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                                            <span class="sr-only">Enable push notifications for {{ $pref['label'] }}</span>
                                                            <span class="toggle-thumb {{ $pref['push_enabled'] ? 'translate-x-6' : 'translate-x-1' }} inline-block w-4 h-4 transform bg-white rounded-full transition-transform duration-200"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Save Message -->
                    <div id="save-message" class="hidden mt-6 p-4 bg-green-50 border border-green-200 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700" id="save-message-text">
                                    Notification preferences saved successfully
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle individual notification
        async function toggleNotification(type, channel) {
            try {
                const response = await fetch('/notifications/preferences/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        type: type,
                        channel: channel
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Update toggle UI
                    const toggle = document.getElementById(`${channel}-${type}`);
                    const thumb = toggle.querySelector('.toggle-thumb');
                    
                    if (data.enabled) {
                        toggle.classList.remove('bg-gray-200');
                        toggle.classList.add('bg-blue-600');
                        thumb.classList.remove('translate-x-1');
                        thumb.classList.add('translate-x-6');
                    } else {
                        toggle.classList.remove('bg-blue-600');
                        toggle.classList.add('bg-gray-200');
                        thumb.classList.remove('translate-x-6');
                        thumb.classList.add('translate-x-1');
                    }
                    
                    showSaveMessage(data.message);
                } else {
                    throw new Error(data.message || 'Failed to update preference');
                }
            } catch (error) {
                console.error('Error toggling notification:', error);
                alert('Failed to update notification preference. Please try again.');
            }
        }

        // Enable all notifications
        async function enableAllNotifications() {
            try {
                const preferences = [];
                document.querySelectorAll('.notification-toggle').forEach(toggle => {
                    const [channel, ...typeParts] = toggle.id.split('-');
                    const type = typeParts.join('-');
                    
                    preferences.push({
                        type: type,
                        [`${channel}_enabled`]: true
                    });
                });

                await bulkUpdatePreferences(preferences, 'All notifications enabled');
            } catch (error) {
                console.error('Error enabling all notifications:', error);
                alert('Failed to enable all notifications. Please try again.');
            }
        }

        // Disable all notifications  
        async function disableAllNotifications() {
            try {
                const preferences = [];
                document.querySelectorAll('.notification-toggle').forEach(toggle => {
                    const [channel, ...typeParts] = toggle.id.split('-');
                    const type = typeParts.join('-');
                    
                    preferences.push({
                        type: type,
                        [`${channel}_enabled`]: false
                    });
                });

                await bulkUpdatePreferences(preferences, 'All notifications disabled');
            } catch (error) {
                console.error('Error disabling all notifications:', error);
                alert('Failed to disable all notifications. Please try again.');
            }
        }

        // Reset to defaults
        async function resetToDefaults() {
            if (!confirm('Are you sure you want to reset all notification preferences to their defaults?')) {
                return;
            }

            try {
                const response = await fetch('/notifications/preferences/reset', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    showSaveMessage(data.message);
                    // Reload page to reflect changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Failed to reset preferences');
                }
            } catch (error) {
                console.error('Error resetting preferences:', error);
                alert('Failed to reset preferences. Please try again.');
            }
        }

        // Bulk update preferences
        async function bulkUpdatePreferences(preferences, message) {
            const response = await fetch('/notifications/preferences/bulk', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    preferences: preferences
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // Update all toggle UIs
                preferences.forEach(pref => {
                    ['email', 'push'].forEach(channel => {
                        if (pref.hasOwnProperty(`${channel}_enabled`)) {
                            const toggle = document.getElementById(`${channel}-${pref.type}`);
                            const thumb = toggle.querySelector('.toggle-thumb');
                            const enabled = pref[`${channel}_enabled`];
                            
                            if (enabled) {
                                toggle.classList.remove('bg-gray-200');
                                toggle.classList.add('bg-blue-600');
                                thumb.classList.remove('translate-x-1');
                                thumb.classList.add('translate-x-6');
                            } else {
                                toggle.classList.remove('bg-blue-600');
                                toggle.classList.add('bg-gray-200');
                                thumb.classList.remove('translate-x-6');
                                thumb.classList.add('translate-x-1');
                            }
                        }
                    });
                });
                
                showSaveMessage(message);
            } else {
                throw new Error(data.message || 'Failed to update preferences');
            }
        }

        // Show save message
        function showSaveMessage(message) {
            const messageDiv = document.getElementById('save-message');
            const messageText = document.getElementById('save-message-text');
            
            messageText.textContent = message;
            messageDiv.classList.remove('hidden');
            
            // Hide message after 3 seconds
            setTimeout(() => {
                messageDiv.classList.add('hidden');
            }, 3000);
        }
    </script>
@endsection