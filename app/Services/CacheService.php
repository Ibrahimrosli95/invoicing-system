<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CacheService
{
    /**
     * Cache duration constants (in minutes)
     */
    const SHORT_CACHE = 5;      // 5 minutes - for frequently changing data
    const MEDIUM_CACHE = 30;    // 30 minutes - for moderately stable data
    const LONG_CACHE = 1440;    // 24 hours - for stable data
    const WEEK_CACHE = 10080;   // 1 week - for very stable data

    /**
     * Cache key prefixes
     */
    const PREFIX_DASHBOARD = 'dashboard';
    const PREFIX_REPORTS = 'reports';
    const PREFIX_ANALYTICS = 'analytics';
    const PREFIX_COMPANY = 'company';
    const PREFIX_USER = 'user';

    /**
     * Get cached dashboard data for a company
     */
    public function getDashboardData(int $companyId, string $period = 'month'): array
    {
        $cacheKey = $this->buildKey(self::PREFIX_DASHBOARD, $companyId, $period);
        
        return Cache::remember($cacheKey, self::MEDIUM_CACHE, function () use ($companyId, $period) {
            return $this->calculateDashboardMetrics($companyId, $period);
        });
    }

    /**
     * Get cached report data
     */
    public function getReportData(string $reportType, array $filters, int $companyId): array
    {
        $cacheKey = $this->buildKey(self::PREFIX_REPORTS, $reportType, md5(serialize($filters)), $companyId);
        
        return Cache::remember($cacheKey, self::SHORT_CACHE, function () use ($reportType, $filters, $companyId) {
            return $this->generateReportData($reportType, $filters, $companyId);
        });
    }

    /**
     * Cache company settings
     */
    public function getCompanySettings(int $companyId): array
    {
        $cacheKey = $this->buildKey(self::PREFIX_COMPANY, 'settings', $companyId);
        
        return Cache::remember($cacheKey, self::LONG_CACHE, function () use ($companyId) {
            return DB::table('companies')
                ->where('id', $companyId)
                ->first(['timezone', 'currency', 'date_format', 'primary_color', 'secondary_color'])
                ->toArray() ?? [];
        });
    }

    /**
     * Cache user permissions and roles
     */
    public function getUserPermissions(int $userId): array
    {
        $cacheKey = $this->buildKey(self::PREFIX_USER, 'permissions', $userId);
        
        return Cache::remember($cacheKey, self::LONG_CACHE, function () use ($userId) {
            $user = \App\Models\User::with(['roles.permissions'])->find($userId);
            
            if (!$user) {
                return [];
            }

            $permissions = $user->roles->flatMap->permissions->pluck('name')->unique()->toArray();
            $roles = $user->roles->pluck('name')->toArray();
            
            return [
                'permissions' => $permissions,
                'roles' => $roles,
            ];
        });
    }

    /**
     * Get cached invoice aging statistics
     */
    public function getInvoiceAgingStats(int $companyId): array
    {
        $cacheKey = $this->buildKey(self::PREFIX_ANALYTICS, 'invoice_aging', $companyId);
        
        return Cache::remember($cacheKey, self::SHORT_CACHE, function () use ($companyId) {
            $baseQuery = \App\Models\Invoice::where('company_id', $companyId);
            
            return [
                'current' => [
                    'count' => $baseQuery->current()->count(),
                    'amount' => $baseQuery->current()->sum('amount_due'),
                ],
                '0-30' => [
                    'count' => $baseQuery->aging0To30()->count(),
                    'amount' => $baseQuery->aging0To30()->sum('amount_due'),
                ],
                '31-60' => [
                    'count' => $baseQuery->aging31To60()->count(),
                    'amount' => $baseQuery->aging31To60()->sum('amount_due'),
                ],
                '61-90' => [
                    'count' => $baseQuery->aging61To90()->count(),
                    'amount' => $baseQuery->aging61To90()->sum('amount_due'),
                ],
                '90+' => [
                    'count' => $baseQuery->aging90Plus()->count(),
                    'amount' => $baseQuery->aging90Plus()->sum('amount_due'),
                ],
            ];
        });
    }

    /**
     * Clear cache for specific company data
     */
    public function clearCompanyCache(int $companyId): void
    {
        $patterns = [
            $this->buildKey(self::PREFIX_DASHBOARD, $companyId, '*'),
            $this->buildKey(self::PREFIX_COMPANY, '*', $companyId),
            $this->buildKey(self::PREFIX_ANALYTICS, '*', $companyId),
        ];

        foreach ($patterns as $pattern) {
            $this->clearByPattern($pattern);
        }
    }

    /**
     * Clear user-specific cache
     */
    public function clearUserCache(int $userId): void
    {
        $pattern = $this->buildKey(self::PREFIX_USER, '*', $userId);
        $this->clearByPattern($pattern);
    }

    /**
     * Clear all report caches
     */
    public function clearReportCache(): void
    {
        $pattern = $this->buildKey(self::PREFIX_REPORTS, '*');
        $this->clearByPattern($pattern);
    }

    /**
     * Build cache key with consistent format
     */
    private function buildKey(...$parts): string
    {
        return 'sales_system:' . implode(':', array_filter($parts));
    }

    /**
     * Clear cache by pattern (works with Redis)
     */
    private function clearByPattern(string $pattern): void
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Cache::getRedis();
                $keys = $redis->keys($pattern);
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            } else {
                // For file-based cache, we can't use patterns
                // This is a limitation we'll document
                Cache::flush();
            }
        } catch (\Exception $e) {
            // Fallback to cache flush if pattern clearing fails
            Cache::flush();
        }
    }

    /**
     * Calculate dashboard metrics
     */
    private function calculateDashboardMetrics(int $companyId, string $period): array
    {
        $startDate = match ($period) {
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'quarter' => Carbon::now()->subQuarter(),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subMonth(),
        };

        return [
            'total_leads' => \App\Models\Lead::where('company_id', $companyId)->count(),
            'leads_this_period' => \App\Models\Lead::where('company_id', $companyId)
                ->where('created_at', '>=', $startDate)->count(),
            'total_quotations' => \App\Models\Quotation::where('company_id', $companyId)->count(),
            'quotations_this_period' => \App\Models\Quotation::where('company_id', $companyId)
                ->where('created_at', '>=', $startDate)->count(),
            'total_invoices' => \App\Models\Invoice::where('company_id', $companyId)->count(),
            'invoices_this_period' => \App\Models\Invoice::where('company_id', $companyId)
                ->where('created_at', '>=', $startDate)->count(),
            'total_revenue' => \App\Models\Invoice::where('company_id', $companyId)
                ->where('status', 'PAID')->sum('total'),
            'revenue_this_period' => \App\Models\Invoice::where('company_id', $companyId)
                ->where('status', 'PAID')
                ->where('paid_at', '>=', $startDate)->sum('total'),
            'cached_at' => now()->toISOString(),
        ];
    }

    /**
     * Generate report data (placeholder for complex report logic)
     */
    private function generateReportData(string $reportType, array $filters, int $companyId): array
    {
        // This would contain complex report generation logic
        // For now, return basic structure
        return [
            'type' => $reportType,
            'filters' => $filters,
            'company_id' => $companyId,
            'generated_at' => now()->toISOString(),
            'data' => [], // Would contain actual report data
        ];
    }
}