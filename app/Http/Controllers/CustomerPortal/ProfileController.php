<?php

namespace App\Http\Controllers\CustomerPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::guard('customer-portal')->user();
        return view('customer-portal.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::guard('customer-portal')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customer_portal_users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'preferred_language' => 'required|string|in:en,ms,zh',
            'timezone' => 'required|string',
        ]);

        $user->update($validated);

        return redirect()->route('customer-portal.profile.edit')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::guard('customer-portal')->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('customer-portal.profile.edit')
            ->with('success', 'Password updated successfully.');
    }

    public function updatePreferences(Request $request)
    {
        $user = Auth::guard('customer-portal')->user();

        $validated = $request->validate([
            'notification_preferences' => 'array',
            'notification_preferences.*' => 'boolean',
        ]);

        // Get current preferences or default to empty array
        $currentPreferences = $user->notification_preferences ?? [];

        // Update with new preferences
        $newPreferences = array_merge($currentPreferences, $validated['notification_preferences'] ?? []);

        $user->update([
            'notification_preferences' => $newPreferences,
        ]);

        return redirect()->route('customer-portal.profile.edit')
            ->with('success', 'Notification preferences updated successfully.');
    }

    public function destroy(Request $request)
    {
        $user = Auth::guard('customer-portal')->user();

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'The password is incorrect.']);
        }

        // Deactivate account instead of deleting
        $user->update(['is_active' => false]);

        Auth::guard('customer-portal')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer-portal.login')
            ->with('status', 'Your account has been deactivated successfully.');
    }
}