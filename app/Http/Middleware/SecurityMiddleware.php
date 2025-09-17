<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\AuditLog;
use Carbon\Carbon;

class SecurityMiddleware
{
    // Security configuration
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOCKOUT_DURATION = 900; // 15 minutes
    const MAX_REQUESTS_PER_MINUTE = 60;
    const SUSPICIOUS_PATTERNS = [
        'union.*select',
        'drop.*table',
        'insert.*into',
        'delete.*from',
        '<script',
        'javascript:',
        'eval\(',
        'base64_decode',
        '../',
        '..\\',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Apply security measures
        $this->enforceRateLimit($request);
        $this->checkBruteForce($request);
        $this->detectSuspiciousActivity($request);
        $this->enforceCSRFProtection($request);

        // Process the request
        $response = $next($request);

        // Add security headers
        $this->addSecurityHeaders($response);

        return $response;
    }

    /**
     * Enforce rate limiting per IP address.
     */
    private function enforceRateLimit(Request $request): void
    {
        $key = 'rate_limit:' . $request->ip();
        $requests = Cache::get($key, 0);

        if ($requests >= self::MAX_REQUESTS_PER_MINUTE) {
            $this->logSecurityEvent($request, 'rate_limit_exceeded', [
                'ip' => $request->ip(),
                'requests' => $requests,
                'limit' => self::MAX_REQUESTS_PER_MINUTE,
            ]);

            abort(429, 'Too Many Requests');
        }

        Cache::put($key, $requests + 1, 60); // 1 minute TTL
    }

    /**
     * Check for brute force login attempts.
     */
    private function checkBruteForce(Request $request): void
    {
        // Only check for authentication routes
        if (!$this->isAuthRoute($request)) {
            return;
        }

        $key = 'brute_force:' . $request->ip();
        $attempts = Cache::get($key, 0);

        if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $lockoutKey = 'lockout:' . $request->ip();
            $lockoutTime = Cache::get($lockoutKey);

            if ($lockoutTime && now()->lt($lockoutTime)) {
                $this->logSecurityEvent($request, 'brute_force_lockout', [
                    'ip' => $request->ip(),
                    'attempts' => $attempts,
                    'lockout_until' => $lockoutTime,
                ]);

                abort(423, 'IP Address locked due to too many failed login attempts');
            }

            // Reset attempts after lockout period
            if (!$lockoutTime || now()->gte($lockoutTime)) {
                Cache::forget($key);
                Cache::forget($lockoutKey);
            }
        }
    }

    /**
     * Detect suspicious activity patterns.
     */
    private function detectSuspiciousActivity(Request $request): void
    {
        $suspicious = false;
        $patterns = [];

        // Check URL for suspicious patterns
        $url = strtolower($request->fullUrl());
        foreach (self::SUSPICIOUS_PATTERNS as $pattern) {
            if (preg_match('/' . $pattern . '/i', $url)) {
                $suspicious = true;
                $patterns[] = $pattern;
            }
        }

        // Check request parameters
        $allInput = $request->all();
        foreach ($allInput as $key => $value) {
            if (is_string($value)) {
                $value = strtolower($value);
                foreach (self::SUSPICIOUS_PATTERNS as $pattern) {
                    if (preg_match('/' . $pattern . '/i', $value)) {
                        $suspicious = true;
                        $patterns[] = $pattern . ' in ' . $key;
                    }
                }
            }
        }

        // Check user agent for known attack tools
        $userAgent = strtolower($request->header('User-Agent', ''));
        $maliciousAgents = ['sqlmap', 'nikto', 'nmap', 'burp', 'owasp'];
        foreach ($maliciousAgents as $agent) {
            if (str_contains($userAgent, $agent)) {
                $suspicious = true;
                $patterns[] = 'malicious_user_agent: ' . $agent;
            }
        }

        if ($suspicious) {
            $this->logSecurityEvent($request, 'suspicious_activity', [
                'ip' => $request->ip(),
                'patterns' => $patterns,
                'url' => $request->fullUrl(),
                'user_agent' => $request->header('User-Agent'),
                'method' => $request->method(),
            ]);

            // Increase rate limit tracking for suspicious IPs
            $suspiciousKey = 'suspicious:' . $request->ip();
            $suspiciousCount = Cache::get($suspiciousKey, 0);
            Cache::put($suspiciousKey, $suspiciousCount + 1, 3600); // 1 hour

            // Block after multiple suspicious attempts
            if ($suspiciousCount >= 3) {
                abort(403, 'Suspicious activity detected');
            }
        }
    }

    /**
     * Enforce enhanced CSRF protection.
     */
    private function enforceCSRFProtection(Request $request): void
    {
        // Skip CSRF for read-only requests
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return;
        }

        // Check for double submit cookies pattern
        $token = $request->header('X-CSRF-TOKEN') ?: $request->input('_token');
        $cookieToken = $request->cookie('XSRF-TOKEN');

        if (!$token || !$cookieToken) {
            $this->logSecurityEvent($request, 'csrf_token_missing', [
                'ip' => $request->ip(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
            ]);
        }

        // Additional referrer checking for sensitive operations
        if ($this->isSensitiveOperation($request)) {
            $referrer = $request->header('Referer');
            $host = $request->getHost();

            if (!$referrer || !str_contains($referrer, $host)) {
                $this->logSecurityEvent($request, 'invalid_referrer', [
                    'ip' => $request->ip(),
                    'referrer' => $referrer,
                    'expected_host' => $host,
                    'url' => $request->fullUrl(),
                ]);

                abort(403, 'Invalid request origin');
            }
        }
    }

    /**
     * Add comprehensive security headers to response.
     */
    private function addSecurityHeaders(Response $response): void
    {
        $headers = [
            // Prevent clickjacking
            'X-Frame-Options' => 'DENY',

            // Prevent MIME type sniffing
            'X-Content-Type-Options' => 'nosniff',

            // Enable XSS protection
            'X-XSS-Protection' => '1; mode=block',

            // Strict transport security (HTTPS)
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',

            // Content Security Policy
            'Content-Security-Policy' => implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.jsdelivr.net",
                "style-src 'self' 'unsafe-inline' fonts.googleapis.com",
                "font-src 'self' fonts.gstatic.com",
                "img-src 'self' data: blob:",
                "connect-src 'self'",
                "frame-ancestors 'none'",
            ]),

            // Referrer policy
            'Referrer-Policy' => 'strict-origin-when-cross-origin',

            // Permissions policy
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',

            // Remove server information
            'Server' => 'Web Server',

            // Custom security headers
            'X-Security-Framework' => 'Bina-Security-v1.0',
        ];

        foreach ($headers as $header => $value) {
            $response->headers->set($header, $value);
        }

        // Remove potentially sensitive headers
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
    }

    /**
     * Log security events to audit log.
     */
    private function logSecurityEvent(Request $request, string $event, array $metadata = []): void
    {
        try {
            // Create a dummy user model for security events if no user is authenticated
            $user = Auth::user();
            if (!$user) {
                $user = new \App\Models\User([
                    'id' => null,
                    'name' => 'Anonymous',
                    'company_id' => null,
                ]);
            }

            $auditData = [
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name ?? 'Anonymous',
                'auditable_type' => 'Security Event',
                'auditable_id' => 0,
                'event' => 'security_event',
                'action' => $event,
                'old_values' => null,
                'new_values' => null,
                'metadata' => array_merge([
                    'security_event' => $event,
                    'timestamp' => now()->toIso8601String(),
                    'severity' => $this->getEventSeverity($event),
                ], $metadata),
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'session_id' => session()->getId(),
                'batch_id' => null,
            ];

            AuditLog::create($auditData);

            // Also log to Laravel's security log
            Log::channel('security')->warning("Security Event: {$event}", $metadata);

        } catch (\Exception $e) {
            // Fallback logging if audit log fails
            Log::error("Failed to log security event: {$event}", [
                'error' => $e->getMessage(),
                'metadata' => $metadata,
            ]);
        }
    }

    /**
     * Check if this is an authentication route.
     */
    private function isAuthRoute(Request $request): bool
    {
        $authRoutes = ['login', 'register', 'password.request', 'password.email', 'password.reset'];
        return in_array($request->route()?->getName(), $authRoutes) ||
               str_contains($request->path(), 'login') ||
               str_contains($request->path(), 'register') ||
               str_contains($request->path(), 'password');
    }

    /**
     * Check if this is a sensitive operation.
     */
    private function isSensitiveOperation(Request $request): bool
    {
        $sensitiveRoutes = [
            'users.destroy',
            'audit.cleanup',
            'invoices.destroy',
            'quotations.destroy',
            'assessments.destroy',
        ];

        $routeName = $request->route()?->getName();
        return in_array($routeName, $sensitiveRoutes) ||
               str_contains($request->path(), 'delete') ||
               str_contains($request->path(), 'destroy') ||
               $request->method() === 'DELETE';
    }

    /**
     * Get event severity level.
     */
    private function getEventSeverity(string $event): string
    {
        return match($event) {
            'brute_force_lockout', 'suspicious_activity' => 'HIGH',
            'rate_limit_exceeded', 'invalid_referrer' => 'MEDIUM',
            'csrf_token_missing' => 'LOW',
            default => 'INFO',
        };
    }

    /**
     * Record failed login attempt.
     */
    public static function recordFailedLogin(Request $request, ?string $email = null): void
    {
        $key = 'brute_force:' . $request->ip();
        $attempts = Cache::get($key, 0) + 1;
        Cache::put($key, $attempts, self::LOCKOUT_DURATION);

        // Set lockout if max attempts reached
        if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $lockoutKey = 'lockout:' . $request->ip();
            Cache::put($lockoutKey, now()->addSeconds(self::LOCKOUT_DURATION), self::LOCKOUT_DURATION);
        }

        // Create security audit log
        try {
            AuditLog::create([
                'company_id' => null,
                'user_id' => null,
                'user_name' => $email ?? 'Anonymous',
                'auditable_type' => 'Security Event',
                'auditable_id' => 0,
                'event' => AuditLog::EVENT_FAILED_LOGIN,
                'action' => 'failed_login_attempt',
                'old_values' => null,
                'new_values' => null,
                'metadata' => [
                    'ip' => $request->ip(),
                    'email_attempted' => $email,
                    'attempt_number' => $attempts,
                    'lockout_active' => $attempts >= self::MAX_LOGIN_ATTEMPTS,
                    'user_agent' => $request->header('User-Agent'),
                    'timestamp' => now()->toIso8601String(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'session_id' => session()->getId(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record failed login attempt in audit log', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'email' => $email,
            ]);
        }
    }

    /**
     * Clear failed login attempts on successful login.
     */
    public static function clearFailedLoginAttempts(Request $request): void
    {
        $key = 'brute_force:' . $request->ip();
        $lockoutKey = 'lockout:' . $request->ip();

        Cache::forget($key);
        Cache::forget($lockoutKey);
    }
}