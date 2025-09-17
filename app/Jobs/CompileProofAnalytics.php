<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\Proof;
use App\Models\ProofView;
use App\Models\KPI;
use App\Services\WebhookEventService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CompileProofAnalytics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $companyId;
    public array $options;
    public int $timeout = 300;
    public int $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(int $companyId, array $options = [])
    {
        $this->companyId = $companyId;
        $this->options = array_merge([
            'period' => 'daily', // daily, weekly, monthly
            'update_kpis' => true,
            'calculate_effectiveness' => true,
            'generate_insights' => true,
            'cache_results' => true,
        ], $options);

        // Use analytics queue for better resource management
        $this->onQueue('analytics');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting proof analytics compilation', [
            'company_id' => $this->companyId,
            'period' => $this->options['period'],
        ]);

        try {
            $company = Company::find($this->companyId);
            if (!$company) {
                throw new \Exception("Company not found: {$this->companyId}");
            }

            // Compile analytics data
            $analyticsData = $this->compileAnalyticsData($company);
            
            // Update KPIs if requested
            if ($this->options['update_kpis']) {
                $this->updateProofKPIs($company, $analyticsData);
            }
            
            // Calculate proof effectiveness
            if ($this->options['calculate_effectiveness']) {
                $this->calculateProofEffectiveness($company, $analyticsData);
            }
            
            // Generate business insights
            if ($this->options['generate_insights']) {
                $insights = $this->generateBusinessInsights($analyticsData);
                $analyticsData['insights'] = $insights;
            }
            
            // Cache results for performance
            if ($this->options['cache_results']) {
                $this->cacheAnalyticsResults($company, $analyticsData);
            }

            // Dispatch webhook for analytics compilation completion
            $this->dispatchAnalyticsWebhook($analyticsData);

            Log::info('Proof analytics compilation completed', [
                'company_id' => $this->companyId,
                'analytics_period' => $this->options['period'],
                'proofs_analyzed' => $analyticsData['summary']['total_proofs'] ?? 0,
            ]);

        } catch (\Exception $e) {
            Log::error('Proof analytics compilation failed', [
                'company_id' => $this->companyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Compile comprehensive analytics data
     */
    private function compileAnalyticsData(Company $company): array
    {
        $period = $this->determinePeriodDates();
        
        return [
            'company_id' => $company->id,
            'period' => $this->options['period'],
            'period_start' => $period['start'],
            'period_end' => $period['end'],
            'compilation_time' => now(),
            'summary' => $this->compileSummaryMetrics($company, $period),
            'performance' => $this->compilePerformanceMetrics($company, $period),
            'engagement' => $this->compileEngagementMetrics($company, $period),
            'conversion' => $this->compileConversionMetrics($company, $period),
            'trends' => $this->compileTrendData($company, $period),
            'categories' => $this->compileCategoryAnalytics($company, $period),
            'assets' => $this->compileAssetAnalytics($company, $period),
        ];
    }

    /**
     * Determine period dates based on options
     */
    private function determinePeriodDates(): array
    {
        return match($this->options['period']) {
            'daily' => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'weekly' => [
                'start' => now()->startOfWeek(),
                'end' => now()->endOfWeek(),
            ],
            'monthly' => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth(),
            ],
            'quarterly' => [
                'start' => now()->startOfQuarter(),
                'end' => now()->endOfQuarter(),
            ],
            default => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
            ],
        };
    }

    /**
     * Compile summary metrics
     */
    private function compileSummaryMetrics(Company $company, array $period): array
    {
        $proofs = Proof::where('company_id', $company->id)
                       ->whereBetween('created_at', [$period['start'], $period['end']])
                       ->get();

        $views = ProofView::whereHas('proof', function($query) use ($company) {
                    $query->where('company_id', $company->id);
                 })
                 ->whereBetween('viewed_at', [$period['start'], $period['end']])
                 ->get();

        return [
            'total_proofs' => $proofs->count(),
            'active_proofs' => $proofs->where('status', 'active')->count(),
            'featured_proofs' => $proofs->where('is_featured', true)->count(),
            'total_views' => $views->count(),
            'unique_viewers' => $views->unique('session_id')->count(),
            'avg_views_per_proof' => $proofs->count() > 0 ? $views->count() / $proofs->count() : 0,
            'total_assets' => $proofs->sum(function($proof) { 
                return $proof->assets()->count(); 
            }),
            'processed_assets' => $proofs->sum(function($proof) { 
                return $proof->assets()->where('status', 'processed')->count(); 
            }),
        ];
    }

    /**
     * Compile performance metrics
     */
    private function compilePerformanceMetrics(Company $company, array $period): array
    {
        $proofs = Proof::where('company_id', $company->id)
                       ->where('status', 'active')
                       ->get();

        $topPerforming = $proofs->sortByDesc('view_count')->take(5);
        $leastPerforming = $proofs->where('view_count', '>', 0)->sortBy('view_count')->take(5);

        return [
            'top_performing' => $topPerforming->map(function($proof) {
                return [
                    'id' => $proof->id,
                    'title' => $proof->title,
                    'type' => $proof->type,
                    'view_count' => $proof->view_count,
                    'click_count' => $proof->click_count,
                    'engagement_rate' => $proof->getEngagementRate(),
                ];
            })->toArray(),
            'least_performing' => $leastPerforming->map(function($proof) {
                return [
                    'id' => $proof->id,
                    'title' => $proof->title,
                    'type' => $proof->type,
                    'view_count' => $proof->view_count,
                    'click_count' => $proof->click_count,
                    'engagement_rate' => $proof->getEngagementRate(),
                ];
            })->toArray(),
            'average_engagement_rate' => $proofs->avg(function($proof) {
                return $proof->getEngagementRate();
            }),
            'total_engagement_events' => $proofs->sum('click_count'),
        ];
    }

    /**
     * Compile engagement metrics
     */
    private function compileEngagementMetrics(Company $company, array $period): array
    {
        $views = ProofView::whereHas('proof', function($query) use ($company) {
                    $query->where('company_id', $company->id);
                 })
                 ->whereBetween('viewed_at', [$period['start'], $period['end']])
                 ->get();

        // Group views by hour for trend analysis
        $hourlyViews = $views->groupBy(function($view) {
            return $view->viewed_at->format('H');
        })->map->count();

        // Group views by day of week
        $dailyViews = $views->groupBy(function($view) {
            return $view->viewed_at->format('l');
        })->map->count();

        return [
            'peak_hours' => $hourlyViews->sortDesc()->take(3)->keys()->toArray(),
            'peak_days' => $dailyViews->sortDesc()->take(3)->keys()->toArray(),
            'hourly_distribution' => $hourlyViews->toArray(),
            'daily_distribution' => $dailyViews->toArray(),
            'average_session_duration' => $this->calculateAverageSessionDuration($views),
            'bounce_rate' => $this->calculateBounceRate($views),
            'return_viewer_rate' => $this->calculateReturnViewerRate($views),
        ];
    }

    /**
     * Compile conversion metrics
     */
    private function compileConversionMetrics(Company $company, array $period): array
    {
        // Get proofs with quotation/invoice connections
        $proofsWithConversions = Proof::where('company_id', $company->id)
            ->whereIn('scope_type', ['App\\Models\\Quotation', 'App\\Models\\Invoice'])
            ->with('scope')
            ->get();

        $conversionData = [];
        $totalConversionValue = 0;
        $conversionCount = 0;

        foreach ($proofsWithConversions as $proof) {
            if ($proof->scope) {
                $conversionValue = 0;
                $conversionType = 'unknown';

                if ($proof->scope instanceof \App\Models\Quotation && $proof->scope->status === 'ACCEPTED') {
                    $conversionValue = $proof->scope->total;
                    $conversionType = 'quotation_accepted';
                    $conversionCount++;
                }

                if ($proof->scope instanceof \App\Models\Invoice && $proof->scope->status === 'PAID') {
                    $conversionValue = $proof->scope->total;
                    $conversionType = 'invoice_paid';
                    $conversionCount++;
                }

                if ($conversionValue > 0) {
                    $conversionData[] = [
                        'proof_id' => $proof->id,
                        'proof_type' => $proof->type,
                        'conversion_type' => $conversionType,
                        'conversion_value' => $conversionValue,
                        'views_before_conversion' => $proof->view_count,
                    ];
                    $totalConversionValue += $conversionValue;
                }
            }
        }

        return [
            'total_conversion_value' => $totalConversionValue,
            'conversion_count' => $conversionCount,
            'average_conversion_value' => $conversionCount > 0 ? $totalConversionValue / $conversionCount : 0,
            'conversion_rate' => $proofsWithConversions->count() > 0 ? ($conversionCount / $proofsWithConversions->count()) * 100 : 0,
            'conversions_by_type' => collect($conversionData)->groupBy('proof_type')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_value' => $group->sum('conversion_value'),
                    'average_views' => $group->avg('views_before_conversion'),
                ];
            })->toArray(),
            'top_converting_proofs' => collect($conversionData)->sortByDesc('conversion_value')->take(5)->values()->toArray(),
        ];
    }

    /**
     * Compile trend data
     */
    private function compileTrendData(Company $company, array $period): array
    {
        // Get historical data for comparison
        $previousPeriod = $this->getPreviousPeriodDates();
        
        $currentViews = ProofView::whereHas('proof', function($query) use ($company) {
                          $query->where('company_id', $company->id);
                       })
                       ->whereBetween('viewed_at', [$period['start'], $period['end']])
                       ->count();

        $previousViews = ProofView::whereHas('proof', function($query) use ($company) {
                           $query->where('company_id', $company->id);
                        })
                        ->whereBetween('viewed_at', [$previousPeriod['start'], $previousPeriod['end']])
                        ->count();

        $viewsGrowth = $previousViews > 0 ? (($currentViews - $previousViews) / $previousViews) * 100 : 0;

        return [
            'views_growth' => round($viewsGrowth, 2),
            'views_trend' => $viewsGrowth > 0 ? 'increasing' : ($viewsGrowth < 0 ? 'decreasing' : 'stable'),
            'current_period_views' => $currentViews,
            'previous_period_views' => $previousViews,
            'weekly_trend' => $this->calculateWeeklyTrend($company),
        ];
    }

    /**
     * Compile category analytics
     */
    private function compileCategoryAnalytics(Company $company, array $period): array
    {
        $proofs = Proof::where('company_id', $company->id)
                       ->where('status', 'active')
                       ->get();

        $categoryData = [];
        foreach (Proof::TYPES as $typeKey => $typeName) {
            $categoryProofs = $proofs->where('type', $typeKey);
            $categoryViews = $categoryProofs->sum('view_count');
            $categoryClicks = $categoryProofs->sum('click_count');

            $categoryData[$typeKey] = [
                'name' => $typeName,
                'count' => $categoryProofs->count(),
                'views' => $categoryViews,
                'clicks' => $categoryClicks,
                'engagement_rate' => $categoryViews > 0 ? ($categoryClicks / $categoryViews) * 100 : 0,
                'avg_views_per_proof' => $categoryProofs->count() > 0 ? $categoryViews / $categoryProofs->count() : 0,
            ];
        }

        return $categoryData;
    }

    /**
     * Compile asset analytics
     */
    private function compileAssetAnalytics(Company $company, array $period): array
    {
        $assets = DB::table('proof_assets')
                    ->join('proofs', 'proof_assets.proof_id', '=', 'proofs.id')
                    ->where('proofs.company_id', $company->id)
                    ->whereBetween('proof_assets.created_at', [$period['start'], $period['end']])
                    ->select('proof_assets.*')
                    ->get();

        $assetsByType = $assets->groupBy('file_type');

        return [
            'total_assets' => $assets->count(),
            'by_type' => $assetsByType->map->count()->toArray(),
            'processing_status' => $assets->groupBy('status')->map->count()->toArray(),
            'average_file_size' => $assets->avg('file_size'),
            'total_storage_used' => $assets->sum('file_size'),
        ];
    }

    /**
     * Update proof-related KPIs
     */
    private function updateProofKPIs(Company $company, array $analyticsData): void
    {
        $period = $this->options['period'];
        $periodStart = $analyticsData['period_start'];

        // Proof engagement rate KPI
        KPI::updateOrCreate([
            'company_id' => $company->id,
            'scope_type' => 'App\\Models\\Company',
            'scope_id' => $company->id,
            'metric_name' => 'proof_engagement_rate',
            'period_type' => $period,
            'period_start' => $periodStart,
        ], [
            'current_value' => $analyticsData['performance']['average_engagement_rate'],
            'target_value' => 15.0, // 15% target engagement rate
            'unit' => 'percentage',
            'metadata' => [
                'total_views' => $analyticsData['summary']['total_views'],
                'total_clicks' => $analyticsData['performance']['total_engagement_events'],
                'active_proofs' => $analyticsData['summary']['active_proofs'],
            ],
        ]);

        // Proof conversion value KPI
        KPI::updateOrCreate([
            'company_id' => $company->id,
            'scope_type' => 'App\\Models\\Company',
            'scope_id' => $company->id,
            'metric_name' => 'proof_conversion_value',
            'period_type' => $period,
            'period_start' => $periodStart,
        ], [
            'current_value' => $analyticsData['conversion']['total_conversion_value'],
            'target_value' => 100000, // RM 100k target
            'unit' => 'currency',
            'metadata' => [
                'conversion_count' => $analyticsData['conversion']['conversion_count'],
                'conversion_rate' => $analyticsData['conversion']['conversion_rate'],
                'average_conversion_value' => $analyticsData['conversion']['average_conversion_value'],
            ],
        ]);

        // Views growth KPI
        KPI::updateOrCreate([
            'company_id' => $company->id,
            'scope_type' => 'App\\Models\\Company',
            'scope_id' => $company->id,
            'metric_name' => 'proof_views_growth',
            'period_type' => $period,
            'period_start' => $periodStart,
        ], [
            'current_value' => $analyticsData['trends']['views_growth'],
            'target_value' => 10.0, // 10% growth target
            'unit' => 'percentage',
            'metadata' => [
                'current_views' => $analyticsData['trends']['current_period_views'],
                'previous_views' => $analyticsData['trends']['previous_period_views'],
                'trend_direction' => $analyticsData['trends']['views_trend'],
            ],
        ]);
    }

    /**
     * Calculate proof effectiveness
     */
    private function calculateProofEffectiveness(Company $company, array $analyticsData): void
    {
        $proofs = Proof::where('company_id', $company->id)->where('status', 'active')->get();

        foreach ($proofs as $proof) {
            $effectiveness = $this->calculateIndividualProofEffectiveness($proof, $analyticsData);
            
            // Update proof with effectiveness score
            $metadata = $proof->metadata ?? [];
            $metadata['effectiveness_score'] = $effectiveness['score'];
            $metadata['effectiveness_breakdown'] = $effectiveness['breakdown'];
            $metadata['last_effectiveness_calculation'] = now();

            $proof->update([
                'conversion_impact' => $effectiveness['score'],
                'metadata' => $metadata,
            ]);
        }
    }

    /**
     * Calculate individual proof effectiveness
     */
    private function calculateIndividualProofEffectiveness(Proof $proof, array $analyticsData): array
    {
        $score = 0;
        $breakdown = [];

        // View performance (40% weight)
        $avgViews = $analyticsData['summary']['avg_views_per_proof'];
        $viewScore = $avgViews > 0 ? min(($proof->view_count / $avgViews) * 40, 40) : 0;
        $score += $viewScore;
        $breakdown['view_performance'] = round($viewScore, 2);

        // Engagement performance (30% weight)
        $avgEngagement = $analyticsData['performance']['average_engagement_rate'];
        $proofEngagement = $proof->getEngagementRate();
        $engagementScore = $avgEngagement > 0 ? min(($proofEngagement / $avgEngagement) * 30, 30) : 0;
        $score += $engagementScore;
        $breakdown['engagement_performance'] = round($engagementScore, 2);

        // Conversion impact (30% weight)
        $conversionScore = min($proof->conversion_impact * 3, 30); // Scale existing conversion_impact
        $score += $conversionScore;
        $breakdown['conversion_impact'] = round($conversionScore, 2);

        return [
            'score' => round($score, 2),
            'breakdown' => $breakdown,
            'grade' => $this->getEffectivenessGrade($score),
        ];
    }

    /**
     * Generate business insights
     */
    private function generateBusinessInsights(array $analyticsData): array
    {
        $insights = [];

        // Performance insights
        if ($analyticsData['performance']['average_engagement_rate'] < 5) {
            $insights[] = [
                'type' => 'performance',
                'severity' => 'warning',
                'title' => 'Low Engagement Rate',
                'message' => 'Average proof engagement rate is below 5%. Consider reviewing proof content and presentation.',
                'recommendation' => 'Focus on visual proofs and customer testimonials which typically have higher engagement rates.',
            ];
        }

        // Trend insights
        if ($analyticsData['trends']['views_growth'] < -10) {
            $insights[] = [
                'type' => 'trend',
                'severity' => 'alert',
                'title' => 'Declining Proof Views',
                'message' => 'Proof views have declined by more than 10% compared to the previous period.',
                'recommendation' => 'Review proof placement in quotations and consider refreshing content with new assets.',
            ];
        }

        // Category insights
        $categoryData = $analyticsData['categories'];
        $bestPerforming = collect($categoryData)->sortByDesc('engagement_rate')->first();
        $worstPerforming = collect($categoryData)->sortBy('engagement_rate')->first();

        if ($bestPerforming && $worstPerforming) {
            $insights[] = [
                'type' => 'category',
                'severity' => 'info',
                'title' => 'Category Performance Variation',
                'message' => "'{$bestPerforming['name']}' proofs perform best (engagement: {$bestPerforming['engagement_rate']}%) while '{$worstPerforming['name']}' proofs need improvement.",
                'recommendation' => "Consider creating more {$bestPerforming['name']} content and improving {$worstPerforming['name']} presentation.",
            ];
        }

        // Conversion insights
        if ($analyticsData['conversion']['conversion_rate'] > 0) {
            $insights[] = [
                'type' => 'conversion',
                'severity' => 'success',
                'title' => 'Positive Conversion Impact',
                'message' => "Proofs are contributing to conversions with {$analyticsData['conversion']['conversion_rate']}% conversion rate.",
                'recommendation' => 'Continue leveraging high-converting proof types in new quotations.',
            ];
        }

        return $insights;
    }

    /**
     * Cache analytics results
     */
    private function cacheAnalyticsResults(Company $company, array $analyticsData): void
    {
        $cacheKey = "proof_analytics_{$company->id}_{$this->options['period']}";
        $cacheDuration = match($this->options['period']) {
            'daily' => 3600, // 1 hour
            'weekly' => 7200, // 2 hours
            'monthly' => 14400, // 4 hours
            default => 3600,
        };

        Cache::put($cacheKey, $analyticsData, $cacheDuration);

        Log::info('Proof analytics results cached', [
            'company_id' => $company->id,
            'cache_key' => $cacheKey,
            'cache_duration' => $cacheDuration,
        ]);
    }

    // Helper methods

    private function getPreviousPeriodDates(): array
    {
        return match($this->options['period']) {
            'daily' => [
                'start' => now()->subDay()->startOfDay(),
                'end' => now()->subDay()->endOfDay(),
            ],
            'weekly' => [
                'start' => now()->subWeek()->startOfWeek(),
                'end' => now()->subWeek()->endOfWeek(),
            ],
            'monthly' => [
                'start' => now()->subMonth()->startOfMonth(),
                'end' => now()->subMonth()->endOfMonth(),
            ],
            default => [
                'start' => now()->subDay()->startOfDay(),
                'end' => now()->subDay()->endOfDay(),
            ],
        };
    }

    private function calculateAverageSessionDuration($views): float
    {
        // Placeholder - would calculate based on session tracking
        return 0.0;
    }

    private function calculateBounceRate($views): float
    {
        // Placeholder - would calculate based on single-view sessions
        return 0.0;
    }

    private function calculateReturnViewerRate($views): float
    {
        // Calculate percentage of viewers who viewed multiple times
        $totalViewers = $views->unique('session_id')->count();
        if ($totalViewers === 0) return 0.0;

        $returnViewers = $views->groupBy('session_id')->filter(function($group) {
            return $group->count() > 1;
        })->count();

        return ($returnViewers / $totalViewers) * 100;
    }

    private function calculateWeeklyTrend(Company $company): array
    {
        // Get last 4 weeks of data
        $weeks = [];
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();
            
            $weekViews = ProofView::whereHas('proof', function($query) use ($company) {
                            $query->where('company_id', $company->id);
                         })
                         ->whereBetween('viewed_at', [$weekStart, $weekEnd])
                         ->count();

            $weeks[] = [
                'week_start' => $weekStart->format('Y-m-d'),
                'views' => $weekViews,
            ];
        }

        return $weeks;
    }

    private function getEffectivenessGrade(float $score): string
    {
        if ($score >= 80) return 'A';
        if ($score >= 70) return 'B';
        if ($score >= 60) return 'C';
        if ($score >= 50) return 'D';
        return 'F';
    }

    /**
     * Dispatch webhook for analytics compilation completion
     */
    private function dispatchAnalyticsWebhook(array $analyticsData): void
    {
        try {
            $webhookService = app(WebhookEventService::class);
            
            // Prepare comprehensive analytics data for webhook
            $webhookData = [
                'total_proofs' => $analyticsData['summary']['total_proofs'] ?? 0,
                'active_proofs' => $analyticsData['summary']['active_proofs'] ?? 0,
                'featured_proofs' => $analyticsData['summary']['featured_proofs'] ?? 0,
                'total_views' => $analyticsData['summary']['total_views'] ?? 0,
                'total_conversions' => $analyticsData['summary']['total_conversions'] ?? 0,
                'average_effectiveness' => $analyticsData['summary']['average_effectiveness'] ?? 0,
                'top_performing_proofs' => $analyticsData['top_performers'] ?? [],
                'underperforming_proofs' => $analyticsData['underperformers'] ?? [],
                'trending_proof_types' => $analyticsData['trending_types'] ?? [],
                'engagement_trends' => $analyticsData['engagement_trends'] ?? [],
                'kpi_updates' => $analyticsData['kpi_updates'] ?? [],
                'optimization_suggestions' => $analyticsData['insights']['optimization_suggestions'] ?? [],
                'content_gaps' => $analyticsData['insights']['content_gaps'] ?? [],
                'strategy_adjustments' => $analyticsData['insights']['strategy_adjustments'] ?? [],
                'analysis_period' => $this->options['period'] ?? '30_days',
                'data_points_analyzed' => $analyticsData['metadata']['data_points'] ?? 0,
                'compilation_trigger' => $this->options['trigger'] ?? 'scheduled',
                'processing_time' => $analyticsData['metadata']['processing_time'] ?? null,
            ];
            
            $webhookService->proofAnalyticsCompiled($this->companyId, $webhookData);
            
        } catch (\Exception $e) {
            Log::error('Failed to dispatch proof analytics webhook', [
                'company_id' => $this->companyId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle failed job
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('CompileProofAnalytics job failed', [
            'company_id' => $this->companyId,
            'options' => $this->options,
            'error' => $exception->getMessage(),
        ]);
    }
}