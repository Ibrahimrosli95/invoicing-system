<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;

class SecurityController extends Controller
{

    /**
     * Security monitoring dashboard.
     */
    public function dashboard(): View
    {
        $companyId = auth()->user()->company_id;

        // Get security metrics
        $metrics = $this->getSecurityMetrics($companyId);

        // Get recent security events
        $recentEvents = $this->getRecentSecurityEvents($companyId);

        // Get active security alerts
        $activeAlerts = $this->getActiveSecurityAlerts($companyId);

        // Get failed login attempts
        $failedLogins = $this->getFailedLoginAttempts($companyId);

        // Get suspicious activities
        $suspiciousActivities = $this->getSuspiciousActivities($companyId);

        return view('security.dashboard', compact(
            'metrics',
            'recentEvents',
            'activeAlerts',
            'failedLogins',
            'suspiciousActivities'
        ));
    }

    /**
     * Security events listing.
     */
    public function events(Request $request): View
    {
        $companyId = auth()->user()->company_id;

        $query = AuditLog::query()
            ->where('company_id', $companyId)
            ->where('event', 'security_event')
            ->with(['user'])
            ->latest();

        // Apply filters
        if ($request->filled('severity')) {
            $query->whereJsonContains('metadata->severity', $request->severity);
        }

        if ($request->filled('event_type')) {
            $query->where('action', $request->event_type);
        }

        if ($request->filled('date_range')) {
            $dateRange = explode(' to ', $request->date_range);
            if (count($dateRange) === 2) {
                $query->whereBetween('created_at', [
                    Carbon::parse($dateRange[0])->startOfDay(),
                    Carbon::parse($dateRange[1])->endOfDay(),
                ]);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%")
                  ->orWhereJsonContains('metadata', $search);
            });
        }

        $events = $query->paginate(20);

        // Get filter options
        $severityOptions = ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'];
        $eventTypeOptions = [
            'brute_force_lockout',
            'suspicious_activity',
            'rate_limit_exceeded',
            'invalid_referrer',
            'csrf_token_missing',
            'two_factor_failed',
            'two_factor_verified',
            'two_factor_enabled',
            'two_factor_disabled',
            'failed_login_attempt'
        ];

        return view('security.events', compact(
            'events',
            'severityOptions',
            'eventTypeOptions'
        ));
    }

    /**
     * Security alerts management.
     */
    public function alerts(): View
    {
        $companyId = auth()->user()->company_id;

        // Get active alerts
        $activeAlerts = $this->getActiveSecurityAlerts($companyId);

        // Get alert statistics
        $alertStats = $this->getAlertStatistics($companyId);

        return view('security.alerts', compact('activeAlerts', 'alertStats'));
    }

    /**
     * Get security metrics for dashboard.
     */
    private function getSecurityMetrics(int $companyId): array
    {
        return Cache::remember("security_metrics:{$companyId}", 300, function () use ($companyId) {
            $now = now();
            $last24Hours = $now->copy()->subDay();
            $last7Days = $now->copy()->subWeek();

            return [
                // Security events in last 24 hours
                'events_24h' => AuditLog::where('company_id', $companyId)
                    ->where('event', 'security_event')
                    ->where('created_at', '>=', $last24Hours)
                    ->count(),

                // Failed login attempts
                'failed_logins_24h' => AuditLog::where('company_id', $companyId)
                    ->where('event', AuditLog::EVENT_FAILED_LOGIN)
                    ->where('created_at', '>=', $last24Hours)
                    ->count(),

                // Locked IPs
                'locked_ips' => $this->getLockedIPCount(),

                // Users with 2FA enabled
                'users_2fa_enabled' => User::where('company_id', $companyId)
                    ->where('two_factor_enabled', true)
                    ->count(),

                // Total users
                'total_users' => User::where('company_id', $companyId)
                    ->where('is_active', true)
                    ->count(),

                // Suspicious activities in last 7 days
                'suspicious_activities_7d' => AuditLog::where('company_id', $companyId)
                    ->where('action', 'suspicious_activity')
                    ->where('created_at', '>=', $last7Days)
                    ->count(),

                // Critical events in last 24 hours
                'critical_events_24h' => AuditLog::where('company_id', $companyId)
                    ->where('event', 'security_event')
                    ->whereJsonContains('metadata->severity', 'HIGH')
                    ->orWhereJsonContains('metadata->severity', 'CRITICAL')
                    ->where('created_at', '>=', $last24Hours)
                    ->count(),
            ];
        });
    }

    /**
     * Get recent security events.
     */
    private function getRecentSecurityEvents(int $companyId): array
    {
        return AuditLog::where('company_id', $companyId)
            ->where('event', 'security_event')
            ->with(['user'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'action' => $event->action,
                    'user_name' => $event->user_name ?? 'Anonymous',
                    'ip_address' => $event->ip_address,
                    'severity' => $event->metadata['severity'] ?? 'INFO',
                    'created_at' => $event->created_at,
                    'description' => $this->getEventDescription($event),
                ];
            })
            ->toArray();
    }

    /**
     * Get active security alerts.
     */
    private function getActiveSecurityAlerts(int $companyId): array
    {
        $alerts = [];

        // Check for brute force attempts
        $bruteForceAttempts = AuditLog::where('company_id', $companyId)
            ->where('action', 'failed_login_attempt')
            ->where('created_at', '>=', now()->subHour())
            ->groupBy('ip_address')
            ->selectRaw('ip_address, COUNT(*) as attempts')
            ->having('attempts', '>=', 3)
            ->get();

        foreach ($bruteForceAttempts as $attempt) {
            $alerts[] = [
                'type' => 'brute_force',
                'severity' => 'HIGH',
                'title' => 'Potential Brute Force Attack',
                'message' => "Multiple failed login attempts from IP: {$attempt->ip_address} ({$attempt->attempts} attempts)",
                'created_at' => now(),
            ];
        }

        // Check for suspicious activities
        $suspiciousCount = AuditLog::where('company_id', $companyId)
            ->where('action', 'suspicious_activity')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        if ($suspiciousCount > 10) {
            $alerts[] = [
                'type' => 'suspicious_activity',
                'severity' => 'MEDIUM',
                'title' => 'Elevated Suspicious Activity',
                'message' => "Detected {$suspiciousCount} suspicious activities in the last 24 hours",
                'created_at' => now(),
            ];
        }

        // Check for low 2FA adoption
        $totalUsers = User::where('company_id', $companyId)->where('is_active', true)->count();
        $users2FA = User::where('company_id', $companyId)->where('two_factor_enabled', true)->count();

        if ($totalUsers > 5 && ($users2FA / $totalUsers) < 0.5) {
            $percentage = round(($users2FA / $totalUsers) * 100);
            $alerts[] = [
                'type' => 'low_2fa_adoption',
                'severity' => 'LOW',
                'title' => 'Low Two-Factor Authentication Adoption',
                'message' => "Only {$percentage}% of users have enabled 2FA ({$users2FA}/{$totalUsers})",
                'created_at' => now(),
            ];
        }

        return $alerts;
    }

    /**
     * Get failed login attempts.
     */
    private function getFailedLoginAttempts(int $companyId): array
    {
        return AuditLog::where('company_id', $companyId)
            ->where('event', AuditLog::EVENT_FAILED_LOGIN)
            ->where('created_at', '>=', now()->subDay())
            ->latest()
            ->limit(15)
            ->get()
            ->map(function ($attempt) {
                return [
                    'email' => $attempt->metadata['email_attempted'] ?? 'Unknown',
                    'ip_address' => $attempt->ip_address,
                    'user_agent' => $attempt->user_agent,
                    'attempt_number' => $attempt->metadata['attempt_number'] ?? 1,
                    'lockout_active' => $attempt->metadata['lockout_active'] ?? false,
                    'created_at' => $attempt->created_at,
                ];
            })
            ->toArray();
    }

    /**
     * Get suspicious activities.
     */
    private function getSuspiciousActivities(int $companyId): array
    {
        return AuditLog::where('company_id', $companyId)
            ->where('action', 'suspicious_activity')
            ->where('created_at', '>=', now()->subDay())
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'ip_address' => $activity->ip_address,
                    'patterns' => $activity->metadata['patterns'] ?? [],
                    'url' => $activity->metadata['url'] ?? '',
                    'user_agent' => $activity->metadata['user_agent'] ?? '',
                    'created_at' => $activity->created_at,
                ];
            })
            ->toArray();
    }

    /**
     * Get alert statistics.
     */
    private function getAlertStatistics(int $companyId): array
    {
        return [
            'total_events_today' => AuditLog::where('company_id', $companyId)
                ->where('event', 'security_event')
                ->whereDate('created_at', today())
                ->count(),

            'failed_logins_today' => AuditLog::where('company_id', $companyId)
                ->where('event', AuditLog::EVENT_FAILED_LOGIN)
                ->whereDate('created_at', today())
                ->count(),

            'blocked_ips' => $this->getLockedIPCount(),

            'average_events_per_day' => AuditLog::where('company_id', $companyId)
                ->where('event', 'security_event')
                ->where('created_at', '>=', now()->subDays(30))
                ->count() / 30,
        ];
    }

    /**
     * Get locked IP count.
     */
    private function getLockedIPCount(): int
    {
        // Count active lockouts in cache
        $lockoutKeys = Cache::getStore()->getRedis()->keys('lockout:*');
        return count($lockoutKeys ?? []);
    }

    /**
     * Get event description.
     */
    private function getEventDescription($event): string
    {
        $descriptions = [
            'brute_force_lockout' => 'IP address locked due to multiple failed login attempts',
            'suspicious_activity' => 'Suspicious patterns detected in request',
            'rate_limit_exceeded' => 'Too many requests from IP address',
            'invalid_referrer' => 'Invalid referrer header on sensitive operation',
            'csrf_token_missing' => 'Missing CSRF token on protected endpoint',
            'two_factor_failed' => 'Failed two-factor authentication attempt',
            'two_factor_verified' => 'Successful two-factor authentication',
            'two_factor_enabled' => 'Two-factor authentication enabled for account',
            'two_factor_disabled' => 'Two-factor authentication disabled for account',
            'failed_login_attempt' => 'Failed login attempt recorded',
        ];

        return $descriptions[$event->action] ?? 'Security event recorded';
    }

    /**
     * Get security analytics data for charts.
     */
    public function analyticsData(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $days = $request->input('days', 7);

        $startDate = now()->subDays($days);

        // Security events by day
        $eventsByDay = AuditLog::where('company_id', $companyId)
            ->where('event', 'security_event')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Failed logins by day
        $failedLoginsByDay = AuditLog::where('company_id', $companyId)
            ->where('event', AuditLog::EVENT_FAILED_LOGIN)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Event types distribution
        $eventTypes = AuditLog::where('company_id', $companyId)
            ->where('event', 'security_event')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->get();

        return response()->json([
            'events_by_day' => $eventsByDay,
            'failed_logins_by_day' => $failedLoginsByDay,
            'event_types' => $eventTypes,
        ]);
    }

    /**
     * Dismiss an alert.
     */
    public function dismissAlert(Request $request): JsonResponse
    {
        $request->validate([
            'alert_type' => 'required|string',
            'alert_id' => 'nullable|string',
        ]);

        // Store dismissed alert in cache to avoid showing again
        $key = "dismissed_alert:{$request->alert_type}:" . auth()->user()->company_id;
        Cache::put($key, true, now()->addDay());

        return response()->json(['success' => true]);
    }

    /**
     * Block an IP address.
     */
    public function blockIP(Request $request): JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'required|string|max:255',
        ]);

        // Add IP to lockout cache with extended duration
        $lockoutKey = 'lockout:' . $request->ip_address;
        Cache::put($lockoutKey, now()->addDays(7), 7 * 24 * 60 * 60); // 7 days

        // Log the manual IP block
        AuditLog::record(
            'security_event',
            null,
            'manual_ip_block',
            null,
            null,
            [
                'security_action' => 'manual_ip_block',
                'blocked_ip' => $request->ip_address,
                'reason' => $request->reason,
                'blocked_by' => auth()->user()->name,
                'timestamp' => now()->toIso8601String(),
                'severity' => 'HIGH',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "IP address {$request->ip_address} has been blocked for 7 days"
        ]);
    }

    /**
     * Unblock an IP address.
     */
    public function unblockIP(Request $request): JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|ip',
        ]);

        // Remove IP from lockout cache
        $lockoutKey = 'lockout:' . $request->ip_address;
        $bruteForceKey = 'brute_force:' . $request->ip_address;

        Cache::forget($lockoutKey);
        Cache::forget($bruteForceKey);

        // Log the manual IP unblock
        AuditLog::record(
            'security_event',
            null,
            'manual_ip_unblock',
            null,
            null,
            [
                'security_action' => 'manual_ip_unblock',
                'unblocked_ip' => $request->ip_address,
                'unblocked_by' => auth()->user()->name,
                'timestamp' => now()->toIso8601String(),
                'severity' => 'MEDIUM',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "IP address {$request->ip_address} has been unblocked"
        ]);
    }
}
