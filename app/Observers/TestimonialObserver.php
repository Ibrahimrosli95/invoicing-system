<?php

namespace App\Observers;

use App\Models\Testimonial;
use App\Models\Proof;

class TestimonialObserver
{
    /**
     * Handle the Testimonial "created" event.
     */
    public function created(Testimonial $testimonial): void
    {
        $this->createOrUpdateProof($testimonial);
    }

    /**
     * Handle the Testimonial "updated" event.
     */
    public function updated(Testimonial $testimonial): void
    {
        // Only update proof if testimonial is published/featured
        if ($testimonial->status === 'published' || $testimonial->is_featured) {
            $this->createOrUpdateProof($testimonial);
        }
    }

    /**
     * Handle the Testimonial "deleted" event.
     */
    public function deleted(Testimonial $testimonial): void
    {
        $this->deleteRelatedProofs($testimonial);
    }

    /**
     * Handle the Testimonial "restored" event.
     */
    public function restored(Testimonial $testimonial): void
    {
        $this->createOrUpdateProof($testimonial);
    }

    /**
     * Handle the Testimonial "force deleted" event.
     */
    public function forceDeleted(Testimonial $testimonial): void
    {
        $this->deleteRelatedProofs($testimonial);
    }

    /**
     * Create or update associated proof for the testimonial
     */
    private function createOrUpdateProof(Testimonial $testimonial): void
    {
        // Only create proof for published testimonials
        if ($testimonial->status !== 'published') {
            return;
        }

        // Find existing proof or create new one
        $proof = Proof::where('company_id', $testimonial->company_id)
            ->where('title', 'Customer Testimonials')
            ->where('proof_type', 'testimonials')
            ->first();

        if (!$proof) {
            $proof = Proof::create([
                'company_id' => $testimonial->company_id,
                'title' => 'Customer Testimonials',
                'description' => 'Verified customer testimonials and reviews showcasing our quality service and client satisfaction.',
                'proof_type' => 'testimonials',
                'category' => 'Social',
                'status' => 'active',
                'metadata' => [
                    'auto_generated' => true,
                    'source' => 'testimonial_observer',
                    'last_updated' => now()->toIso8601String()
                ]
            ]);
        }

        // Update proof content with latest testimonials
        $this->updateProofContent($proof);
    }

    /**
     * Update proof content with testimonial data
     */
    private function updateProofContent(Proof $proof): void
    {
        $testimonials = Testimonial::where('company_id', $proof->company_id)
            ->where('status', 'published')
            ->orderBy('is_featured', 'desc')
            ->orderBy('rating', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $proofData = [
            'total_testimonials' => $testimonials->count(),
            'average_rating' => $testimonials->avg('rating'),
            'featured_count' => $testimonials->where('is_featured', true)->count(),
            'testimonials' => $testimonials->map(function ($testimonial) {
                return [
                    'id' => $testimonial->id,
                    'customer_name' => $testimonial->customer_name,
                    'company_name' => $testimonial->company_name,
                    'rating' => $testimonial->rating,
                    'review' => $testimonial->review,
                    'service_provided' => $testimonial->service_provided,
                    'project_date' => $testimonial->project_date?->format('Y-m-d'),
                    'is_featured' => $testimonial->is_featured,
                    'verified' => $testimonial->verified_at ? true : false,
                ];
            })->toArray()
        ];

        $proof->update([
            'content' => $proofData,
            'impact_score' => $this->calculateImpactScore($testimonials),
            'metadata' => array_merge($proof->metadata ?? [], [
                'last_compiled' => now()->toIso8601String(),
                'testimonial_count' => $testimonials->count(),
                'average_rating' => round($testimonials->avg('rating'), 2),
            ])
        ]);
    }

    /**
     * Calculate impact score based on testimonial quality
     */
    private function calculateImpactScore($testimonials): int
    {
        if ($testimonials->isEmpty()) {
            return 0;
        }

        $score = 0;
        foreach ($testimonials as $testimonial) {
            // Base score from rating
            $score += $testimonial->rating * 10;
            
            // Bonus for featured testimonials
            if ($testimonial->is_featured) {
                $score += 20;
            }
            
            // Bonus for verified testimonials
            if ($testimonial->verified_at) {
                $score += 15;
            }
            
            // Bonus for detailed reviews
            if (strlen($testimonial->review) > 100) {
                $score += 10;
            }
        }

        return min(100, round($score / $testimonials->count()));
    }

    /**
     * Delete related proofs when testimonial is deleted
     */
    private function deleteRelatedProofs(Testimonial $testimonial): void
    {
        // Check if this was the last testimonial for this company
        $remainingCount = Testimonial::where('company_id', $testimonial->company_id)
            ->where('status', 'published')
            ->count();

        if ($remainingCount === 0) {
            // Delete the auto-generated proof if no testimonials remain
            Proof::where('company_id', $testimonial->company_id)
                ->where('proof_type', 'testimonials')
                ->whereJsonContains('metadata->auto_generated', true)
                ->delete();
        } else {
            // Update existing proof to remove this testimonial
            $proof = Proof::where('company_id', $testimonial->company_id)
                ->where('proof_type', 'testimonials')
                ->first();
                
            if ($proof) {
                $this->updateProofContent($proof);
            }
        }
    }
}
