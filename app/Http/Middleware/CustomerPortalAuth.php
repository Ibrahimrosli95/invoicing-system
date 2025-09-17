<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerPortalAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('customer-portal')->check()) {
            return redirect()->route('customer-portal.login');
        }

        $user = Auth::guard('customer-portal')->user();

        // Check if user is active
        if (!$user->is_active) {
            Auth::guard('customer-portal')->logout();
            return redirect()->route('customer-portal.login')
                ->withErrors(['account' => 'Your account has been deactivated. Please contact support.']);
        }

        return $next($request);
    }
}