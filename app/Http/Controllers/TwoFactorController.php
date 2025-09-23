<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use App\Models\AuditLog;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Show the 2FA setup page.
     */
    public function setup(): View
    {
        $user = Auth::user();

        // Generate secret if not exists
        if (!$user->two_factor_secret) {
            $secret = $this->google2fa->generateSecretKey();
            $user->update(['two_factor_secret' => encrypt($secret)]);
        } else {
            $secret = decrypt($user->two_factor_secret);
        }

        // Generate QR code URL
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        // Generate QR code image
        $qrCodeImage = $this->generateQRCode($qrCodeUrl);

        return view('auth.two-factor.setup', [
            'secret' => $secret,
            'qrCodeImage' => $qrCodeImage,
            'backupCodes' => $user->two_factor_recovery_codes ?
                json_decode(decrypt($user->two_factor_recovery_codes), true) : null,
        ]);
    }

    /**
     * Enable 2FA for the user.
     */
    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        // Verify 2FA code
        $secret = decrypt($user->two_factor_secret);
        $isValid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$isValid) {
            throw ValidationException::withMessages([
                'code' => ['The provided two factor authentication code is invalid.'],
            ]);
        }

        // Generate recovery codes
        $recoveryCodes = $this->generateRecoveryCodes();

        // Enable 2FA
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
            'two_factor_enabled_at' => now(),
        ]);

        // Log the security event
        AuditLog::record(
            'security_event',
            $user,
            'two_factor_enabled',
            null,
            ['two_factor_enabled' => true],
            [
                'security_action' => 'two_factor_authentication_enabled',
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'timestamp' => now()->toIso8601String(),
            ]
        );

        Session::flash('two_factor_recovery_codes', $recoveryCodes);

        return redirect()->route('two-factor.setup')
            ->with('success', 'Two-factor authentication has been enabled! Please save your recovery codes.');
    }

    /**
     * Disable 2FA for the user.
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string',
            'code' => 'required|string',
        ]);

        $user = Auth::user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        // Verify 2FA code or recovery code
        $isValidCode = false;
        if (strlen($request->code) === 6) {
            // Regular TOTP code
            $secret = decrypt($user->two_factor_secret);
            $isValidCode = $this->google2fa->verifyKey($secret, $request->code);
        } elseif (strlen($request->code) === 8) {
            // Recovery code
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            $isValidCode = in_array($request->code, $recoveryCodes);
        }

        if (!$isValidCode) {
            throw ValidationException::withMessages([
                'code' => ['The provided code is invalid.'],
            ]);
        }

        // Disable 2FA
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_enabled_at' => null,
        ]);

        // Log the security event
        AuditLog::record(
            'security_event',
            $user,
            'two_factor_disabled',
            ['two_factor_enabled' => true],
            ['two_factor_enabled' => false],
            [
                'security_action' => 'two_factor_authentication_disabled',
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'timestamp' => now()->toIso8601String(),
            ]
        );

        return redirect()->route('two-factor.setup')
            ->with('success', 'Two-factor authentication has been disabled.');
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        if (!$user->two_factor_enabled) {
            return redirect()->route('two-factor.setup')
                ->with('error', 'Two-factor authentication is not enabled.');
        }

        // Generate new recovery codes
        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ]);

        // Log the security event
        AuditLog::record(
            'security_event',
            $user,
            'recovery_codes_regenerated',
            null,
            null,
            [
                'security_action' => 'recovery_codes_regenerated',
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'timestamp' => now()->toIso8601String(),
            ]
        );

        Session::flash('two_factor_recovery_codes', $recoveryCodes);

        return redirect()->route('two-factor.setup')
            ->with('success', 'Recovery codes have been regenerated! Please save the new codes.');
    }

    /**
     * Show 2FA verification form.
     */
    public function verify(): View
    {
        return view('auth.two-factor.verify');
    }

    /**
     * Verify 2FA code during login.
     */
    public function verifyCode(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->session()->get('two_factor_user');

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Two-factor authentication session expired.');
        }

        $isValid = false;
        $codeType = 'totp';

        // Check if it's a TOTP code (6 digits)
        if (strlen($request->code) === 6) {
            $secret = decrypt($user->two_factor_secret);
            $isValid = $this->google2fa->verifyKey($secret, $request->code);
        }
        // Check if it's a recovery code (8 characters)
        elseif (strlen($request->code) === 8) {
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            if (in_array($request->code, $recoveryCodes)) {
                $isValid = true;
                $codeType = 'recovery';

                // Remove used recovery code
                $recoveryCodes = array_filter($recoveryCodes, fn($code) => $code !== $request->code);
                $user->update([
                    'two_factor_recovery_codes' => encrypt(json_encode(array_values($recoveryCodes))),
                ]);
            }
        }

        if (!$isValid) {
            // Log failed 2FA attempt
            AuditLog::record(
                'security_event',
                $user,
                'two_factor_failed',
                null,
                null,
                [
                    'security_action' => 'two_factor_verification_failed',
                    'code_type' => $codeType,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'timestamp' => now()->toIso8601String(),
                ]
            );

            throw ValidationException::withMessages([
                'code' => ['The provided two factor authentication code is invalid.'],
            ]);
        }

        // Complete login
        Auth::login($user);
        $request->session()->forget('two_factor_user');

        // Log successful 2FA
        AuditLog::record(
            'security_event',
            $user,
            'two_factor_verified',
            null,
            null,
            [
                'security_action' => 'two_factor_verification_successful',
                'code_type' => $codeType,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'timestamp' => now()->toIso8601String(),
            ]
        );

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Check if 2FA is required for user.
     */
    public function checkRequired(Request $request): JsonResponse
    {
        $email = $request->input('email');

        if (!$email) {
            return response()->json(['required' => false]);
        }

        $user = \App\Models\User::where('email', $email)->first();

        return response()->json([
            'required' => $user && $user->two_factor_enabled,
        ]);
    }

    /**
     * Generate QR code image.
     */
    private function generateQRCode(string $url): string
    {
        try {
            $renderer = new ImageRenderer(
                new RendererStyle(200),
                new ImagickImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qrCode = $writer->writeString($url);

            return 'data:image/png;base64,' . base64_encode($qrCode);
        } catch (\Exception $e) {
            // Fallback to URL if QR generation fails
            return $url;
        }
    }

    /**
     * Generate recovery codes.
     */
    private function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(substr(md5(random_bytes(16)), 0, 8));
        }
        return $codes;
    }

    /**
     * Download recovery codes as text file.
     */
    public function downloadRecoveryCodes(): Response
    {
        $user = Auth::user();

        if (!$user->two_factor_enabled || !$user->two_factor_recovery_codes) {
            abort(404);
        }

        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        $content = "Two-Factor Authentication Recovery Codes\n";
        $content .= "For: " . $user->email . "\n";
        $content .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n\n";
        $content .= "IMPORTANT: Keep these codes safe and secure.\n";
        $content .= "Each code can only be used once.\n\n";

        foreach ($recoveryCodes as $index => $code) {
            $content .= ($index + 1) . ". " . $code . "\n";
        }

        $headers = [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="recovery-codes-' . now()->format('Y-m-d') . '.txt"',
        ];

        return response($content, 200, $headers);
    }
}