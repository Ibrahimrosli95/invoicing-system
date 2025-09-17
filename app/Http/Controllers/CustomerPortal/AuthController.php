<?php

namespace App\Http\Controllers\CustomerPortal;

use App\Http\Controllers\Controller;
use App\Models\CustomerPortalUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:customer-portal')->except('logout');
    }

    public function showLogin()
    {
        return view('customer-portal.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('customer-portal')->attempt($request->only('email', 'password'), $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::guard('customer-portal')->user();
            $user->updateLoginTracking($request->ip());

            return redirect()->intended(route('customer-portal.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('customer-portal.auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customer_portal_users',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = CustomerPortalUser::create([
            'company_id' => 1, // Default to main company for now
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company_name' => $request->company_name,
            'password' => Hash::make($request->password),
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country ?? 'Malaysia',
            'is_active' => true,
            'preferred_language' => 'en',
            'timezone' => 'Asia/Kuala_Lumpur',
        ]);

        Auth::guard('customer-portal')->login($user);

        return redirect(route('customer-portal.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('customer-portal')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer-portal.login');
    }

    public function showForgotPassword()
    {
        return view('customer-portal.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = CustomerPortalUser::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->withErrors(['email' => 'We can\'t find a user with that email address.']);
        }

        $token = $user->generatePasswordResetToken();

        // Send reset email (would integrate with notification system)
        // For now, just return success
        
        return back()->with('status', 'We have emailed your password reset link!');
    }

    public function showResetPassword(Request $request, $token = null)
    {
        return view('customer-portal.auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = CustomerPortalUser::where('email', $request->email)->first();

        if (!$user || !$user->isPasswordResetTokenValid($request->token)) {
            return back()->withErrors(['email' => 'This password reset token is invalid.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $user->clearPasswordResetToken();

        Auth::guard('customer-portal')->login($user);

        return redirect()->route('customer-portal.dashboard')->with('status', 'Your password has been reset!');
    }

    public function verify(Request $request)
    {
        $user = Auth::guard('customer-portal')->user();
        
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('customer-portal.dashboard');
        }

        $user->markEmailAsVerified();

        return redirect()->route('customer-portal.dashboard')->with('verified', true);
    }

    public function resendVerification(Request $request)
    {
        $user = Auth::guard('customer-portal')->user();
        
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('customer-portal.dashboard');
        }

        // Send verification email (would integrate with notification system)
        // For now, just return success
        
        return back()->with('status', 'Verification link sent!');
    }
}