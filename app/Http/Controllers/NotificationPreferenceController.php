<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationPreferenceController extends Controller
{
    /**
     * Display user's notification preferences.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get existing preferences
        $existingPreferences = $user->notificationPreferences()->pluck('notification_type')->toArray();
        
        // Get all available notification types
        $allTypes = NotificationPreference::TYPES;
        
        // Prepare preferences with current settings
        $preferences = [];
        foreach ($allTypes as $type => $label) {
            $preference = $user->getNotificationPreference($type);
            
            $preferences[$type] = [
                'type' => $type,
                'label' => $label,
                'email_enabled' => $preference ? $preference->email_enabled : true,
                'push_enabled' => $preference ? $preference->push_enabled : true,
                'settings' => $preference ? $preference->settings : NotificationPreference::getDefaultSettings($type),
            ];
        }
        
        // Group preferences by category
        $groupedPreferences = [
            'Lead Notifications' => [
                'lead_assigned',
                'lead_status_changed',
                'lead_new_activity',
            ],
            'Quotation Notifications' => [
                'quotation_created',
                'quotation_sent',
                'quotation_accepted',
                'quotation_rejected',
                'quotation_expires_soon',
            ],
            'Invoice Notifications' => [
                'invoice_created',
                'invoice_sent',
                'invoice_payment_received',
                'invoice_overdue',
                'invoice_reminder',
            ],
            'Team Notifications' => [
                'team_assignment',
                'team_performance',
            ],
            'System Notifications' => [
                'system_maintenance',
                'system_updates',
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'preferences' => $preferences,
                'grouped_preferences' => $groupedPreferences,
            ]);
        }

        return view('notifications.preferences.index', compact('preferences', 'groupedPreferences'));
    }

    /**
     * Update notification preference.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:' . implode(',', array_keys(NotificationPreference::TYPES)),
            'email_enabled' => 'boolean',
            'push_enabled' => 'boolean',
            'settings' => 'array',
        ]);

        $user = auth()->user();

        // Find or create preference
        $preference = $user->notificationPreferences()
            ->where('notification_type', $request->type)
            ->first();

        if (!$preference) {
            $preference = new NotificationPreference([
                'user_id' => $user->id,
                'notification_type' => $request->type,
                'email_enabled' => true,
                'push_enabled' => true,
                'settings' => NotificationPreference::getDefaultSettings($request->type),
            ]);
        }

        // Update settings
        if ($request->has('email_enabled')) {
            $preference->email_enabled = $request->boolean('email_enabled');
        }

        if ($request->has('push_enabled')) {
            $preference->push_enabled = $request->boolean('push_enabled');
        }

        if ($request->has('settings')) {
            $preference->settings = array_merge($preference->settings ?? [], $request->settings);
        }

        $preference->save();

        return response()->json([
            'success' => true,
            'message' => 'Notification preference updated successfully',
            'preference' => $preference,
        ]);
    }

    /**
     * Toggle notification preference on/off.
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:' . implode(',', array_keys(NotificationPreference::TYPES)),
            'channel' => 'required|string|in:email,push',
        ]);

        $user = auth()->user();

        // Find or create preference
        $preference = $user->notificationPreferences()
            ->where('notification_type', $request->type)
            ->first();

        if (!$preference) {
            $preference = $user->notificationPreferences()->create([
                'notification_type' => $request->type,
                'email_enabled' => true,
                'push_enabled' => true,
                'settings' => NotificationPreference::getDefaultSettings($request->type),
            ]);
        }

        // Toggle the specified channel
        if ($request->channel === 'email') {
            $preference->toggleEmail();
            $enabled = $preference->email_enabled;
        } else {
            $preference->togglePush();
            $enabled = $preference->push_enabled;
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->channel) . ' notifications ' . ($enabled ? 'enabled' : 'disabled'),
            'enabled' => $enabled,
            'preference' => $preference,
        ]);
    }

    /**
     * Bulk update notification preferences.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'preferences' => 'required|array',
            'preferences.*.type' => 'required|string|in:' . implode(',', array_keys(NotificationPreference::TYPES)),
            'preferences.*.email_enabled' => 'boolean',
            'preferences.*.push_enabled' => 'boolean',
            'preferences.*.settings' => 'array',
        ]);

        $user = auth()->user();
        $updated = 0;

        foreach ($request->preferences as $prefData) {
            $preference = $user->notificationPreferences()
                ->where('notification_type', $prefData['type'])
                ->first();

            if (!$preference) {
                $preference = new NotificationPreference([
                    'user_id' => $user->id,
                    'notification_type' => $prefData['type'],
                ]);
            }

            // Update fields if provided
            if (isset($prefData['email_enabled'])) {
                $preference->email_enabled = $prefData['email_enabled'];
            }

            if (isset($prefData['push_enabled'])) {
                $preference->push_enabled = $prefData['push_enabled'];
            }

            if (isset($prefData['settings'])) {
                $preference->settings = array_merge($preference->settings ?? [], $prefData['settings']);
            }

            $preference->save();
            $updated++;
        }

        return response()->json([
            'success' => true,
            'message' => "Updated {$updated} notification preferences",
            'updated_count' => $updated,
        ]);
    }

    /**
     * Reset to default preferences.
     */
    public function resetToDefaults(): JsonResponse
    {
        $user = auth()->user();

        // Delete all existing preferences
        $user->notificationPreferences()->delete();

        // Setup default preferences
        $user->setupDefaultNotificationPreferences();

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences reset to defaults',
        ]);
    }
}
