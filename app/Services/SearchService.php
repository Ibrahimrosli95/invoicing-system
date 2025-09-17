<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SearchService
{
    protected array $searchableModels = [
        'leads' => Lead::class,
        'quotations' => Quotation::class,
        'invoices' => Invoice::class,
        'users' => User::class,
    ];

    protected array $searchableFields = [
        'leads' => ['name', 'email', 'phone', 'company_name', 'address', 'requirements', 'notes'],
        'quotations' => ['number', 'customer_name', 'customer_email', 'customer_phone', 'title', 'description', 'notes'],
        'invoices' => ['number', 'customer_name', 'customer_email', 'customer_phone', 'notes'],
        'users' => ['name', 'email', 'phone'],
    ];

    /**
     * Perform global search across all entities
     */
    public function globalSearch(string $query, array $filters = []): array
    {
        $results = [];
        $companyId = auth()->user()->company_id;
        
        // Cache key for this search
        $cacheKey = $this->getCacheKey($query, $filters, $companyId);
        
        return Cache::remember($cacheKey, 300, function () use ($query, $filters, $companyId) {
            $results = [];
            
            foreach ($this->searchableModels as $type => $modelClass) {
                $modelResults = $this->searchModel($modelClass, $query, $filters, $type);
                if ($modelResults->isNotEmpty()) {
                    $results[$type] = [
                        'count' => $modelResults->count(),
                        'results' => $modelResults->take(10), // Limit results per type for global search
                        'total_count' => $this->getModelTotalCount($modelClass, $query, $filters),
                    ];
                }
            }
            
            return $results;
        });
    }

    /**
     * Search within a specific model
     */
    public function searchModel(string $modelClass, string $query, array $filters = [], string $type = null): Collection
    {
        $builder = $modelClass::query();
        
        // Apply company scoping
        if (method_exists($modelClass, 'forCompany')) {
            $builder->forCompany();
        } elseif (in_array('company_id', (new $modelClass)->getFillable())) {
            $builder->where('company_id', auth()->user()->company_id);
        }

        // Apply search query
        if (!empty($query)) {
            $builder = $this->applySearchQuery($builder, $query, $type ?? strtolower(class_basename($modelClass)) . 's');
        }

        // Apply filters
        $builder = $this->applyFilters($builder, $filters, $type ?? strtolower(class_basename($modelClass)) . 's');

        // Apply default ordering
        $builder->latest();

        return $builder->get();
    }

    /**
     * Apply search query to builder
     */
    protected function applySearchQuery(Builder $builder, string $query, string $type): Builder
    {
        $fields = $this->searchableFields[$type] ?? [];
        
        if (empty($fields)) {
            return $builder;
        }

        return $builder->where(function ($q) use ($query, $fields) {
            foreach ($fields as $field) {
                $q->orWhere($field, 'LIKE', "%{$query}%");
            }
            
            // Special handling for document numbers (exact match priority)
            if (in_array('number', $fields)) {
                $q->orWhere('number', 'LIKE', "{$query}%");
            }
        });
    }

    /**
     * Apply filters to builder
     */
    protected function applyFilters(Builder $builder, array $filters, string $type): Builder
    {
        // Date range filters
        if (isset($filters['date_from'])) {
            $builder->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $builder->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Status filters
        if (isset($filters['status']) && !empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $builder->whereIn('status', $filters['status']);
            } else {
                $builder->where('status', $filters['status']);
            }
        }

        // Amount range filters
        if (isset($filters['amount_from']) && in_array($type, ['quotations', 'invoices'])) {
            $builder->where('total', '>=', $filters['amount_from']);
        }
        
        if (isset($filters['amount_to']) && in_array($type, ['quotations', 'invoices'])) {
            $builder->where('total', '<=', $filters['amount_to']);
        }

        // Team/User filters
        if (isset($filters['team_id']) && !empty($filters['team_id'])) {
            if ($type === 'users') {
                $builder->whereHas('teams', function ($q) use ($filters) {
                    $q->where('team_id', $filters['team_id']);
                });
            } else {
                $builder->where('team_id', $filters['team_id']);
            }
        }

        if (isset($filters['user_id']) && !empty($filters['user_id'])) {
            $userField = $this->getUserFieldForType($type);
            if ($userField) {
                $builder->where($userField, $filters['user_id']);
            }
        }

        // Tag filters (if applicable)
        if (isset($filters['tags']) && !empty($filters['tags']) && $type === 'leads') {
            $builder->where(function ($q) use ($filters) {
                foreach ((array) $filters['tags'] as $tag) {
                    $q->orWhereJsonContains('tags', $tag);
                }
            });
        }

        return $builder;
    }

    /**
     * Get user field for different entity types
     */
    protected function getUserFieldForType(string $type): ?string
    {
        return match ($type) {
            'leads' => 'assigned_to',
            'quotations' => 'created_by',
            'invoices' => 'created_by',
            default => null,
        };
    }

    /**
     * Get total count for a model search
     */
    protected function getModelTotalCount(string $modelClass, string $query, array $filters = []): int
    {
        $builder = $modelClass::query();
        
        // Apply company scoping
        if (method_exists($modelClass, 'forCompany')) {
            $builder->forCompany();
        } elseif (in_array('company_id', (new $modelClass)->getFillable())) {
            $builder->where('company_id', auth()->user()->company_id);
        }

        // Apply search query
        if (!empty($query)) {
            $type = strtolower(class_basename($modelClass)) . 's';
            $builder = $this->applySearchQuery($builder, $query, $type);
        }

        // Apply filters
        $type = strtolower(class_basename($modelClass)) . 's';
        $builder = $this->applyFilters($builder, $filters, $type);

        return $builder->count();
    }

    /**
     * Get search suggestions based on query
     */
    public function getSuggestions(string $query, int $limit = 5): array
    {
        $suggestions = [];
        $companyId = auth()->user()->company_id;
        
        if (strlen($query) < 2) {
            return $suggestions;
        }

        // Customer name suggestions
        $customerNames = DB::table('leads')
            ->where('company_id', $companyId)
            ->where('name', 'LIKE', "{$query}%")
            ->distinct()
            ->limit($limit)
            ->pluck('name')
            ->toArray();

        foreach ($customerNames as $name) {
            $suggestions[] = [
                'type' => 'customer',
                'value' => $name,
                'label' => "Customer: {$name}",
            ];
        }

        // Phone number suggestions
        $phones = DB::table('leads')
            ->where('company_id', $companyId)
            ->where('phone', 'LIKE', "{$query}%")
            ->distinct()
            ->limit($limit)
            ->pluck('phone')
            ->toArray();

        foreach ($phones as $phone) {
            $suggestions[] = [
                'type' => 'phone',
                'value' => $phone,
                'label' => "Phone: {$phone}",
            ];
        }

        // Document number suggestions
        $documentNumbers = collect();
        
        // Quotation numbers
        $quotationNumbers = DB::table('quotations')
            ->where('company_id', $companyId)
            ->where('number', 'LIKE', "{$query}%")
            ->limit($limit)
            ->pluck('number');
        
        foreach ($quotationNumbers as $number) {
            $suggestions[] = [
                'type' => 'document',
                'value' => $number,
                'label' => "Quotation: {$number}",
            ];
        }

        // Invoice numbers
        $invoiceNumbers = DB::table('invoices')
            ->where('company_id', $companyId)
            ->where('number', 'LIKE', "{$query}%")
            ->limit($limit)
            ->pluck('number');
        
        foreach ($invoiceNumbers as $number) {
            $suggestions[] = [
                'type' => 'document',
                'value' => $number,
                'label' => "Invoice: {$number}",
            ];
        }

        return array_slice($suggestions, 0, $limit);
    }

    /**
     * Save search query to recent searches
     */
    public function saveRecentSearch(string $query, array $filters = [], string $type = 'global'): void
    {
        $userId = auth()->id();
        $cacheKey = "recent_searches.user.{$userId}";
        
        $recentSearches = Cache::get($cacheKey, []);
        
        $searchData = [
            'query' => $query,
            'filters' => $filters,
            'type' => $type,
            'timestamp' => now()->toISOString(),
        ];
        
        // Remove duplicate if exists
        $recentSearches = array_filter($recentSearches, function ($search) use ($query, $filters, $type) {
            return !($search['query'] === $query && $search['filters'] === $filters && $search['type'] === $type);
        });
        
        // Add to beginning
        array_unshift($recentSearches, $searchData);
        
        // Keep only last 10 searches
        $recentSearches = array_slice($recentSearches, 0, 10);
        
        Cache::put($cacheKey, $recentSearches, 86400); // 24 hours
    }

    /**
     * Get recent searches for current user
     */
    public function getRecentSearches(): array
    {
        $userId = auth()->id();
        $cacheKey = "recent_searches.user.{$userId}";
        
        return Cache::get($cacheKey, []);
    }

    /**
     * Clear recent searches for current user
     */
    public function clearRecentSearches(): void
    {
        $userId = auth()->id();
        $cacheKey = "recent_searches.user.{$userId}";
        
        Cache::forget($cacheKey);
    }

    /**
     * Get search analytics
     */
    public function getSearchAnalytics(): array
    {
        $userId = auth()->id();
        $companyId = auth()->user()->company_id;
        
        // Get search frequency by type
        $searchFrequency = Cache::get("search_analytics.company.{$companyId}", []);
        
        // Get user's search history
        $recentSearches = $this->getRecentSearches();
        $searchHistory = array_slice($recentSearches, 0, 5);
        
        return [
            'search_frequency' => $searchFrequency,
            'recent_searches' => $searchHistory,
            'total_searches_today' => $this->getTodaysSearchCount($userId),
        ];
    }

    /**
     * Record search analytics
     */
    public function recordSearch(string $query, string $type, int $resultCount): void
    {
        $userId = auth()->id();
        $companyId = auth()->user()->company_id;
        
        // Record daily search count
        $dailyKey = "search_count.user.{$userId}." . now()->format('Y-m-d');
        $currentCount = Cache::get($dailyKey, 0);
        Cache::put($dailyKey, $currentCount + 1, 86400);
        
        // Record search frequency by type
        $frequencyKey = "search_analytics.company.{$companyId}";
        $frequency = Cache::get($frequencyKey, []);
        $frequency[$type] = ($frequency[$type] ?? 0) + 1;
        Cache::put($frequencyKey, $frequency, 86400 * 7); // 7 days
    }

    /**
     * Get today's search count for user
     */
    protected function getTodaysSearchCount(int $userId): int
    {
        $dailyKey = "search_count.user.{$userId}." . now()->format('Y-m-d');
        return Cache::get($dailyKey, 0);
    }

    /**
     * Generate cache key for search
     */
    protected function getCacheKey(string $query, array $filters, int $companyId): string
    {
        $filterHash = md5(serialize($filters));
        return "search.{$companyId}." . md5($query) . ".{$filterHash}";
    }

    /**
     * Clear search cache
     */
    public function clearCache(int $companyId = null): void
    {
        $companyId = $companyId ?? auth()->user()->company_id;
        
        // This would need a more sophisticated cache tagging system
        // For now, we'll rely on TTL expiration
        Cache::flush(); // Note: This clears ALL cache, use with caution in production
    }

    /**
     * Get available filter options for a type
     */
    public function getFilterOptions(string $type): array
    {
        $options = [
            'date_ranges' => $this->getDateRangeOptions(),
            'status' => $this->getStatusOptions($type),
        ];

        if (in_array($type, ['quotations', 'invoices'])) {
            $options['amount_ranges'] = $this->getAmountRangeOptions($type);
        }

        $options['teams'] = $this->getTeamOptions();
        $options['users'] = $this->getUserOptions();

        if ($type === 'leads') {
            $options['tags'] = $this->getTagOptions();
        }

        return $options;
    }

    /**
     * Get date range filter options
     */
    protected function getDateRangeOptions(): array
    {
        return [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'this_week' => 'This Week',
            'last_week' => 'Last Week',
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            'this_quarter' => 'This Quarter',
            'this_year' => 'This Year',
            'custom' => 'Custom Range',
        ];
    }

    /**
     * Get status options for type
     */
    protected function getStatusOptions(string $type): array
    {
        return match ($type) {
            'leads' => Lead::getStatuses(),
            'quotations' => [
                'DRAFT' => 'Draft',
                'SENT' => 'Sent',
                'VIEWED' => 'Viewed',
                'ACCEPTED' => 'Accepted',
                'REJECTED' => 'Rejected',
                'EXPIRED' => 'Expired',
                'CONVERTED' => 'Converted',
            ],
            'invoices' => [
                'DRAFT' => 'Draft',
                'SENT' => 'Sent',
                'PARTIAL' => 'Partial',
                'PAID' => 'Paid',
                'OVERDUE' => 'Overdue',
                'CANCELLED' => 'Cancelled',
            ],
            default => [],
        };
    }

    /**
     * Get amount range options for financial documents
     */
    protected function getAmountRangeOptions(string $type): array
    {
        return [
            '0-1000' => 'RM 0 - RM 1,000',
            '1000-5000' => 'RM 1,000 - RM 5,000',
            '5000-10000' => 'RM 5,000 - RM 10,000',
            '10000-50000' => 'RM 10,000 - RM 50,000',
            '50000+' => 'RM 50,000+',
            'custom' => 'Custom Range',
        ];
    }

    /**
     * Get team options
     */
    protected function getTeamOptions(): array
    {
        return auth()->user()->company->teams()
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Get user options
     */
    protected function getUserOptions(): array
    {
        return auth()->user()->company->users()
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Get tag options for leads
     */
    protected function getTagOptions(): array
    {
        return Lead::forCompany()
            ->whereNotNull('tags')
            ->get()
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get model class for entity type
     */
    public function getModelClass(string $type): ?string
    {
        return $this->searchableModels[$type] ?? null;
    }
}