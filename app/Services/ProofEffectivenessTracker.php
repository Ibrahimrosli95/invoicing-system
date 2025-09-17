<?php

namespace App\Services;

use App\Models\Proof;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\ProofView;
use App\Models\Company;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ProofEffectivenessTracker
{
    /**
     * Track proof effectiveness for a specific event
     */
    public function trackEffectivenessEvent(string $eventType, $model, array $metadata = []): void
    {
        try {
            switch ($eventType) {
                case 'quotation_viewed':
                    $this->trackQuotationViewed($model, $metadata);
                    break;
                    
                case 'quotation_accepted':
                    $this->trackQuotationAccepted($model, $metadata);
                    break;
                    
                case 'invoice_paid':
                    $this->trackInvoicePaid($model, $metadata);
                    break;
                    
                case 'proof_viewed':
                    $this->trackProofViewed($model, $metadata);
                    break;
                    
                case 'proof_clicked':
                    $this->trackProofClicked($model, $metadata);
                    break;
                    
                default:
                    Log::warning('Unknown effectiveness tracking event', [
                        'event_type' => $eventType,
                        'model_type' => get_class($model),
                        'model_id' => $model->id ?? null,
                    ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to track proof effectiveness event', [
                'event_type' => $eventType,
                'error' => $e->getMessage(),
                'model_type' => get_class($model),
            ]);
        }
    }

    /**
     * Calculate comprehensive effectiveness score for a proof
     */
    public function calculateEffectivenessScore(Proof $proof): array
    {
        $metrics = $this->gatherProofMetrics($proof);
        $benchmarks = $this->getCompanyBenchmarks($proof->company_id);
        
        $score = [
            'overall_score' => 0,
            'component_scores' => [],
            'metrics' => $metrics,
            'benchmarks' => $benchmarks,
            'recommendations' => [],
            'grade' => 'F',
            'calculated_at' => now(),
        ];

        // Calculate component scores
        $score['component_scores']['visibility'] = $this->calculateVisibilityScore($metrics, $benchmarks);
        $score['component_scores']['engagement'] = $this->calculateEngagementScore($metrics, $benchmarks);
        $score['component_scores']['conversion'] = $this->calculateConversionScore($metrics, $benchmarks);
        $score['component_scores']['quality'] = $this->calculateQualityScore($proof, $metrics);
        $score['component_scores']['relevance'] = $this->calculateRelevanceScore($proof, $metrics);

        // Calculate weighted overall score
        $weights = [
            'visibility' => 0.20,
            'engagement' => 0.25,
            'conversion' => 0.30,
            'quality' => 0.15,
            'relevance' => 0.10,
        ];

        $score['overall_score'] = array_sum(array_map(
            fn($component, $weight) => $score['component_scores'][$component] * $weight,
            array_keys($weights),
            $weights
        ));

        // Assign grade
        $score['grade'] = $this->assignGrade($score['overall_score']);

        // Generate recommendations
        $score['recommendations'] = $this->generateRecommendations($score, $proof);

        // Dispatch webhook for effectiveness scoring completion
        $this->dispatchEffectivenessWebhook($proof, $score);

        return $score;
    }

    /**
     * Analyze conversion attribution for proofs
     */
    public function analyzeConversionAttribution(Company $company, array $options = []): array
    {
        $period = $options['period'] ?? 30; // days
        $startDate = now()->subDays($period);

        // Get all conversions (accepted quotations and paid invoices) in period
        $conversions = $this->getConversionsInPeriod($company, $startDate);
        
        $attributionData = [
            'total_conversions' => count($conversions),
            'total_value' => 0,
            'proof_attributed_conversions' => 0,
            'proof_attributed_value' => 0,
            'attribution_by_proof_type' => [],
            'attribution_by_proof' => [],
            'effectiveness_insights' => [],
        ];

        foreach ($conversions as $conversion) {
            $attributionData['total_value'] += $conversion['value'];
            
            // Find proofs associated with this conversion
            $relatedProofs = $this->findRelatedProofs($conversion);
            
            if ($relatedProofs->isNotEmpty()) {
                $attributionData['proof_attributed_conversions']++;
                $attributionData['proof_attributed_value'] += $conversion['value'];
                
                // Attribute conversion value to proofs
                $this->attributeConversionToProofs($relatedProofs, $conversion, $attributionData);
            }
        }

        // Calculate attribution rates
        $attributionData['attribution_rate'] = $attributionData['total_conversions'] > 0 
            ? ($attributionData['proof_attributed_conversions'] / $attributionData['total_conversions']) * 100 
            : 0;

        $attributionData['value_attribution_rate'] = $attributionData['total_value'] > 0 
            ? ($attributionData['proof_attributed_value'] / $attributionData['total_value']) * 100 
            : 0;

        // Generate insights
        $attributionData['effectiveness_insights'] = $this->generateAttributionInsights($attributionData);

        return $attributionData;
    }

    /**
     * Track A/B test performance for proof variations
     */
    public function trackABTestPerformance(string $testId, Proof $proof, string $variant, array $eventData): void
    {
        $cacheKey = "ab_test_{$testId}";
        $testData = Cache::get($cacheKey, [
            'test_id' => $testId,
            'start_date' => now(),
            'variants' => [],
            'events' => [],
        ]);

        // Initialize variant if not exists
        if (!isset($testData['variants'][$variant])) {
            $testData['variants'][$variant] = [
                'proof_id' => $proof->id,
                'views' => 0,
                'clicks' => 0,
                'conversions' => 0,
                'conversion_value' => 0,
            ];
        }

        // Track event
        $testData['events'][] = array_merge($eventData, [
            'timestamp' => now(),
            'variant' => $variant,
            'proof_id' => $proof->id,
        ]);

        // Update variant metrics
        if ($eventData['event_type'] === 'view') {
            $testData['variants'][$variant]['views']++;
        } elseif ($eventData['event_type'] === 'click') {
            $testData['variants'][$variant]['clicks']++;
        } elseif ($eventData['event_type'] === 'conversion') {
            $testData['variants'][$variant]['conversions']++;
            $testData['variants'][$variant]['conversion_value'] += $eventData['value'] ?? 0;
        }

        // Cache updated data (24 hours)
        Cache::put($cacheKey, $testData, 86400);

        Log::info('A/B test performance tracked', [
            'test_id' => $testId,
            'variant' => $variant,
            'event_type' => $eventData['event_type'],
            'proof_id' => $proof->id,
        ]);
    }

    /**
     * Get A/B test results with statistical significance
     */
    public function getABTestResults(string $testId): array
    {
        $testData = Cache::get("ab_test_{$testId}");
        
        if (!$testData) {
            return ['error' => 'Test data not found'];
        }

        $results = [
            'test_id' => $testId,
            'duration_days' => now()->diffInDays($testData['start_date']),
            'variants' => [],
            'winner' => null,
            'statistical_significance' => false,
            'confidence_level' => 0,
            'recommendations' => [],
        ];

        // Calculate performance metrics for each variant
        foreach ($testData['variants'] as $variant => $data) {
            $conversionRate = $data['views'] > 0 ? ($data['conversions'] / $data['views']) * 100 : 0;
            $avgConversionValue = $data['conversions'] > 0 ? $data['conversion_value'] / $data['conversions'] : 0;
            
            $results['variants'][$variant] = array_merge($data, [
                'conversion_rate' => $conversionRate,
                'avg_conversion_value' => $avgConversionValue,
                'total_value_per_view' => $data['views'] > 0 ? $data['conversion_value'] / $data['views'] : 0,
            ]);
        }

        // Determine winner and statistical significance
        if (count($results['variants']) >= 2) {
            $results = $this->calculateStatisticalSignificance($results);
        }

        return $results;
    }

    // Private helper methods

    private function trackQuotationViewed(Quotation $quotation, array $metadata): void
    {
        // Find proofs attached to this quotation or its related documents
        $proofs = Proof::where('company_id', $quotation->company_id)
                       ->where(function($query) use ($quotation) {
                           $query->where('scope_type', 'App\\Models\\Quotation')
                                 ->where('scope_id', $quotation->id)
                                 ->orWhere('visibility', 'public')
                                 ->where('show_in_quotation', true);
                       })
                       ->get();

        foreach ($proofs as $proof) {
            $this->incrementProofMetric($proof, 'quotation_views', 1, $metadata);
        }
    }

    private function trackQuotationAccepted(Quotation $quotation, array $metadata): void
    {
        // Find proofs that contributed to this conversion
        $proofs = Proof::where('company_id', $quotation->company_id)
                       ->where(function($query) use ($quotation) {
                           $query->where('scope_type', 'App\\Models\\Quotation')
                                 ->where('scope_id', $quotation->id)
                                 ->orWhere('show_in_quotation', true);
                       })
                       ->get();

        foreach ($proofs as $proof) {
            $conversionValue = $quotation->total;
            $this->incrementProofMetric($proof, 'conversions', 1, array_merge($metadata, [
                'conversion_type' => 'quotation_accepted',
                'conversion_value' => $conversionValue,
            ]));
            
            // Update conversion impact
            $proof->increment('conversion_impact', $conversionValue / 1000); // Scale down for storage
        }
    }

    private function trackInvoicePaid(Invoice $invoice, array $metadata): void
    {
        $quotation = $invoice->quotation;
        if (!$quotation) return;

        $proofs = Proof::where('company_id', $invoice->company_id)
                       ->where(function($query) use ($invoice, $quotation) {
                           $query->where('scope_type', 'App\\Models\\Invoice')
                                 ->where('scope_id', $invoice->id)
                                 ->orWhere('scope_type', 'App\\Models\\Quotation')
                                 ->where('scope_id', $quotation->id);
                       })
                       ->get();

        foreach ($proofs as $proof) {
            $this->incrementProofMetric($proof, 'invoice_payments', 1, array_merge($metadata, [
                'payment_value' => $invoice->total,
                'project_completed' => true,
            ]));
        }
    }

    private function trackProofViewed(ProofView $proofView, array $metadata): void
    {
        $proof = $proofView->proof;
        $this->incrementProofMetric($proof, 'direct_views', 1, $metadata);
    }

    private function trackProofClicked(Proof $proof, array $metadata): void
    {
        $this->incrementProofMetric($proof, 'direct_clicks', 1, $metadata);
    }

    private function incrementProofMetric(Proof $proof, string $metric, int $increment, array $metadata): void
    {
        $proofMetadata = $proof->metadata ?? [];
        $proofMetadata['effectiveness_tracking'] = $proofMetadata['effectiveness_tracking'] ?? [];
        $proofMetadata['effectiveness_tracking'][$metric] = ($proofMetadata['effectiveness_tracking'][$metric] ?? 0) + $increment;
        $proofMetadata['effectiveness_tracking']['last_updated'] = now();
        
        if (!empty($metadata)) {
            $proofMetadata['effectiveness_tracking']['recent_events'][] = array_merge($metadata, [
                'metric' => $metric,
                'increment' => $increment,
                'timestamp' => now(),
            ]);
            
            // Keep only last 10 events
            $proofMetadata['effectiveness_tracking']['recent_events'] = array_slice(
                $proofMetadata['effectiveness_tracking']['recent_events'] ?? [], -10
            );
        }

        $proof->update(['metadata' => $proofMetadata]);
    }

    private function gatherProofMetrics(Proof $proof): array
    {
        $effectivenessData = $proof->metadata['effectiveness_tracking'] ?? [];
        
        return [
            'views' => $proof->view_count,
            'clicks' => $proof->click_count,
            'quotation_views' => $effectivenessData['quotation_views'] ?? 0,
            'direct_views' => $effectivenessData['direct_views'] ?? 0,
            'conversions' => $effectivenessData['conversions'] ?? 0,
            'invoice_payments' => $effectivenessData['invoice_payments'] ?? 0,
            'conversion_impact' => $proof->conversion_impact,
            'engagement_rate' => $proof->getEngagementRate(),
            'asset_count' => $proof->assets()->count(),
            'processed_assets' => $proof->assets()->where('status', 'processed')->count(),
            'created_days_ago' => $proof->created_at->diffInDays(now()),
        ];
    }

    private function getCompanyBenchmarks(int $companyId): array
    {
        $cacheKey = "proof_benchmarks_{$companyId}";
        
        return Cache::remember($cacheKey, 3600, function() use ($companyId) {
            $proofs = Proof::where('company_id', $companyId)->where('status', 'active')->get();
            
            if ($proofs->isEmpty()) {
                return $this->getDefaultBenchmarks();
            }

            return [
                'avg_views' => $proofs->avg('view_count'),
                'avg_clicks' => $proofs->avg('click_count'),
                'avg_engagement_rate' => $proofs->avg(fn($p) => $p->getEngagementRate()),
                'avg_conversion_impact' => $proofs->avg('conversion_impact'),
                'top_quartile_views' => $proofs->sortByDesc('view_count')->take(ceil($proofs->count() / 4))->avg('view_count'),
                'total_proofs' => $proofs->count(),
                'featured_proofs' => $proofs->where('is_featured', true)->count(),
            ];
        });
    }

    private function getDefaultBenchmarks(): array
    {
        return [
            'avg_views' => 10,
            'avg_clicks' => 2,
            'avg_engagement_rate' => 20,
            'avg_conversion_impact' => 5,
            'top_quartile_views' => 25,
            'total_proofs' => 0,
            'featured_proofs' => 0,
        ];
    }

    private function calculateVisibilityScore(array $metrics, array $benchmarks): float
    {
        $avgViews = $benchmarks['avg_views'] ?: 1;
        $score = min(($metrics['views'] / $avgViews) * 100, 100);
        return round($score, 2);
    }

    private function calculateEngagementScore(array $metrics, array $benchmarks): float
    {
        $avgEngagement = $benchmarks['avg_engagement_rate'] ?: 1;
        $score = min(($metrics['engagement_rate'] / $avgEngagement) * 100, 100);
        return round($score, 2);
    }

    private function calculateConversionScore(array $metrics, array $benchmarks): float
    {
        $avgConversion = $benchmarks['avg_conversion_impact'] ?: 1;
        $score = min(($metrics['conversion_impact'] / $avgConversion) * 100, 100);
        return round($score, 2);
    }

    private function calculateQualityScore(Proof $proof, array $metrics): float
    {
        $score = 50; // Base score

        // Asset quality bonus
        if ($metrics['asset_count'] > 0) {
            $assetQuality = ($metrics['processed_assets'] / $metrics['asset_count']) * 100;
            $score += min($assetQuality * 0.3, 30);
        }

        // Content completeness bonus
        if (!empty($proof->title) && !empty($proof->description)) {
            $score += 10;
        }

        // Featured status bonus
        if ($proof->is_featured) {
            $score += 10;
        }

        return round(min($score, 100), 2);
    }

    private function calculateRelevanceScore(Proof $proof, array $metrics): float
    {
        $score = 70; // Base score

        // Recent activity bonus
        if ($metrics['created_days_ago'] < 30) {
            $score += 20;
        } elseif ($metrics['created_days_ago'] > 365) {
            $score -= 20;
        }

        // Usage frequency bonus
        $recentActivity = $metrics['quotation_views'] + $metrics['direct_views'];
        if ($recentActivity > 10) {
            $score += 10;
        }

        return round(max(min($score, 100), 0), 2);
    }

    private function assignGrade(float $score): string
    {
        if ($score >= 90) return 'A+';
        if ($score >= 80) return 'A';
        if ($score >= 70) return 'B';
        if ($score >= 60) return 'C';
        if ($score >= 50) return 'D';
        return 'F';
    }

    private function generateRecommendations(array $score, Proof $proof): array
    {
        $recommendations = [];

        if ($score['component_scores']['visibility'] < 50) {
            $recommendations[] = [
                'type' => 'visibility',
                'priority' => 'high',
                'message' => 'Low visibility - consider featuring this proof or improving its placement in quotations.',
            ];
        }

        if ($score['component_scores']['engagement'] < 30) {
            $recommendations[] = [
                'type' => 'engagement',
                'priority' => 'medium',
                'message' => 'Low engagement - review content quality and consider adding more compelling assets.',
            ];
        }

        if ($score['component_scores']['quality'] < 70) {
            $recommendations[] = [
                'type' => 'quality',
                'priority' => 'medium',
                'message' => 'Improve content quality by adding processed assets and completing all fields.',
            ];
        }

        return $recommendations;
    }

    private function getConversionsInPeriod(Company $company, $startDate): array
    {
        $conversions = [];

        // Get accepted quotations
        $quotations = Quotation::where('company_id', $company->id)
                               ->where('status', 'ACCEPTED')
                               ->where('accepted_at', '>=', $startDate)
                               ->get();

        foreach ($quotations as $quotation) {
            $conversions[] = [
                'type' => 'quotation',
                'id' => $quotation->id,
                'value' => $quotation->total,
                'date' => $quotation->accepted_at,
                'model' => $quotation,
            ];
        }

        // Get paid invoices
        $invoices = Invoice::where('company_id', $company->id)
                           ->where('status', 'PAID')
                           ->whereHas('paymentRecords', function($query) use ($startDate) {
                               $query->where('created_at', '>=', $startDate);
                           })
                           ->get();

        foreach ($invoices as $invoice) {
            $conversions[] = [
                'type' => 'invoice',
                'id' => $invoice->id,
                'value' => $invoice->total,
                'date' => $invoice->paymentRecords()->latest()->first()->created_at,
                'model' => $invoice,
            ];
        }

        return $conversions;
    }

    private function findRelatedProofs(array $conversion): Collection
    {
        $model = $conversion['model'];
        $companyId = $model->company_id;

        return Proof::where('company_id', $companyId)
                    ->where(function($query) use ($model) {
                        $query->where('scope_type', get_class($model))
                              ->where('scope_id', $model->id);
                        
                        // Also include proofs shown in quotations/invoices
                        if ($model instanceof Quotation) {
                            $query->orWhere('show_in_quotation', true);
                        } elseif ($model instanceof Invoice && $model->quotation) {
                            $query->orWhere('scope_type', 'App\\Models\\Quotation')
                                  ->orWhere('scope_id', $model->quotation->id)
                                  ->orWhere('show_in_quotation', true);
                        }
                    })
                    ->get();
    }

    private function attributeConversionToProofs(Collection $proofs, array $conversion, array &$attributionData): void
    {
        $conversionValue = $conversion['value'];
        $attributionPerProof = $conversionValue / $proofs->count();

        foreach ($proofs as $proof) {
            // Track by proof type
            if (!isset($attributionData['attribution_by_proof_type'][$proof->type])) {
                $attributionData['attribution_by_proof_type'][$proof->type] = [
                    'conversions' => 0,
                    'value' => 0,
                ];
            }

            $attributionData['attribution_by_proof_type'][$proof->type]['conversions']++;
            $attributionData['attribution_by_proof_type'][$proof->type]['value'] += $attributionPerProof;

            // Track by individual proof
            $attributionData['attribution_by_proof'][] = [
                'proof_id' => $proof->id,
                'proof_title' => $proof->title,
                'proof_type' => $proof->type,
                'conversion_type' => $conversion['type'],
                'attributed_value' => $attributionPerProof,
                'conversion_date' => $conversion['date'],
            ];
        }
    }

    private function generateAttributionInsights(array $attributionData): array
    {
        $insights = [];

        // Overall attribution insight
        if ($attributionData['attribution_rate'] > 70) {
            $insights[] = [
                'type' => 'success',
                'message' => "Excellent proof attribution rate of {$attributionData['attribution_rate']}%. Proofs are effectively supporting conversions.",
            ];
        } elseif ($attributionData['attribution_rate'] < 30) {
            $insights[] = [
                'type' => 'warning',
                'message' => "Low proof attribution rate of {$attributionData['attribution_rate']}%. Consider improving proof integration in sales process.",
            ];
        }

        // Best performing proof type
        if (!empty($attributionData['attribution_by_proof_type'])) {
            $bestType = collect($attributionData['attribution_by_proof_type'])
                        ->sortByDesc('value')
                        ->first();
            
            if ($bestType) {
                $typeName = array_search($bestType, $attributionData['attribution_by_proof_type']);
                $insights[] = [
                    'type' => 'info',
                    'message' => "'{$typeName}' proofs are generating the highest conversion value with RM " . number_format($bestType['value'], 2) . " attributed.",
                ];
            }
        }

        return $insights;
    }

    private function calculateStatisticalSignificance(array $results): array
    {
        // Simplified statistical significance calculation
        // In production, you'd want to use proper statistical libraries
        
        $variants = array_values($results['variants']);
        if (count($variants) < 2) return $results;

        $controlVariant = $variants[0];
        $testVariant = $variants[1];

        $controlRate = $controlVariant['conversion_rate'] / 100;
        $testRate = $testVariant['conversion_rate'] / 100;

        // Simple z-test approximation
        if ($controlVariant['views'] > 100 && $testVariant['views'] > 100) {
            $pooledRate = ($controlVariant['conversions'] + $testVariant['conversions']) / 
                         ($controlVariant['views'] + $testVariant['views']);
            
            $se = sqrt($pooledRate * (1 - $pooledRate) * (1/$controlVariant['views'] + 1/$testVariant['views']));
            
            if ($se > 0) {
                $zScore = abs($testRate - $controlRate) / $se;
                $results['statistical_significance'] = $zScore > 1.96; // 95% confidence
                $results['confidence_level'] = min(95, $zScore * 50); // Approximation
            }
        }

        // Determine winner
        $results['winner'] = $testRate > $controlRate ? array_keys($results['variants'])[1] : array_keys($results['variants'])[0];

        return $results;
    }

    /**
     * Dispatch webhook for proof effectiveness scoring completion
     */
    private function dispatchEffectivenessWebhook(Proof $proof, array $effectivenessData): void
    {
        try {
            // Use dependency injection to get webhook service
            $webhookService = app(\App\Services\WebhookEventService::class);
            
            // Prepare comprehensive effectiveness data for webhook
            $webhookData = [
                'component_scores' => $effectivenessData['component_scores'] ?? [],
                'overall_score' => $effectivenessData['overall_score'] ?? 0,
                'effectiveness_grade' => $effectivenessData['grade'] ?? 'Unknown',
                'performance_metrics' => $effectivenessData['metrics'] ?? [],
                'views' => $effectivenessData['metrics']['views'] ?? 0,
                'engagement_rate' => $effectivenessData['metrics']['engagement_rate'] ?? 0,
                'conversion_attribution' => $effectivenessData['metrics']['conversion_impact'] ?? 0,
                'business_impact' => $effectivenessData['metrics']['business_impact'] ?? 0,
                'recommendations' => $effectivenessData['recommendations'] ?? [],
                'benchmark_comparison' => $effectivenessData['benchmarks'] ?? [],
                'analysis_period' => '30_days', // Default analysis period
                'sample_size' => $effectivenessData['metrics']['views'] ?? 0,
                'confidence_level' => $this->calculateConfidenceLevel($effectivenessData['metrics'] ?? []),
            ];
            
            $webhookService->proofEffectivenessScored($proof, $webhookData);
            
        } catch (\Exception $e) {
            Log::error('Failed to dispatch proof effectiveness webhook', [
                'proof_id' => $proof->id,
                'proof_uuid' => $proof->uuid,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calculate confidence level based on sample size and metrics
     */
    private function calculateConfidenceLevel(array $metrics): ?float
    {
        $views = $metrics['views'] ?? 0;
        $engagement = $metrics['engagement_rate'] ?? 0;
        
        if ($views < 10) {
            return 0.1; // Very low confidence
        } elseif ($views < 50) {
            return 0.5; // Low confidence
        } elseif ($views < 100) {
            return 0.7; // Medium confidence
        } elseif ($views >= 100 && $engagement > 0) {
            return 0.9; // High confidence
        }
        
        return 0.8; // Default confidence
    }
}