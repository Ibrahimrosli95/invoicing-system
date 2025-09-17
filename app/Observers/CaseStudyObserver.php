<?php

namespace App\Observers;

use App\Models\CaseStudy;
use App\Models\Proof;

class CaseStudyObserver
{
    public function created(CaseStudy $caseStudy): void
    {
        $this->createOrUpdateProof($caseStudy);
    }

    public function updated(CaseStudy $caseStudy): void
    {
        if ($caseStudy->status === 'published') {
            $this->createOrUpdateProof($caseStudy);
        }
    }

    public function deleted(CaseStudy $caseStudy): void
    {
        $this->deleteRelatedProofs($caseStudy);
    }

    private function createOrUpdateProof(CaseStudy $caseStudy): void
    {
        if ($caseStudy->status !== 'published') {
            return;
        }

        $proof = Proof::where('company_id', $caseStudy->company_id)
            ->where('title', 'Success Stories & Case Studies')
            ->where('proof_type', 'case_studies')
            ->first();

        if (!$proof) {
            $proof = Proof::create([
                'company_id' => $caseStudy->company_id,
                'title' => 'Success Stories & Case Studies',
                'description' => 'Real project case studies showcasing our successful implementations and satisfied clients.',
                'proof_type' => 'case_studies',
                'category' => 'Performance',
                'status' => 'active',
                'metadata' => [
                    'auto_generated' => true,
                    'source' => 'case_study_observer',
                    'last_updated' => now()->toIso8601String()
                ]
            ]);
        }

        $this->updateProofContent($proof);
    }

    private function updateProofContent(Proof $proof): void
    {
        $caseStudies = CaseStudy::where('company_id', $proof->company_id)
            ->where('status', 'published')
            ->orderBy('project_completion_date', 'desc')
            ->get();

        $proofData = [
            'total_case_studies' => $caseStudies->count(),
            'total_project_value' => $caseStudies->sum('project_value'),
            'average_project_value' => $caseStudies->avg('project_value'),
            'case_studies' => $caseStudies->map(function ($study) {
                return [
                    'id' => $study->id,
                    'title' => $study->title,
                    'client_name' => $study->client_name,
                    'industry' => $study->industry,
                    'challenge' => $study->challenge_description,
                    'solution' => $study->solution_description,
                    'results' => $study->results_achieved,
                    'project_value' => $study->project_value,
                    'completion_date' => $study->project_completion_date?->format('Y-m-d'),
                ];
            })->toArray()
        ];

        $proof->update([
            'content' => $proofData,
            'impact_score' => min(100, $caseStudies->count() * 15),
            'metadata' => array_merge($proof->metadata ?? [], [
                'last_compiled' => now()->toIso8601String(),
                'case_study_count' => $caseStudies->count(),
                'total_value' => $caseStudies->sum('project_value'),
            ])
        ]);
    }

    private function deleteRelatedProofs(CaseStudy $caseStudy): void
    {
        $remainingCount = CaseStudy::where('company_id', $caseStudy->company_id)
            ->where('status', 'published')
            ->count();

        if ($remainingCount === 0) {
            Proof::where('company_id', $caseStudy->company_id)
                ->where('proof_type', 'case_studies')
                ->whereJsonContains('metadata->auto_generated', true)
                ->delete();
        } else {
            $proof = Proof::where('company_id', $caseStudy->company_id)
                ->where('proof_type', 'case_studies')
                ->first();
                
            if ($proof) {
                $this->updateProofContent($proof);
            }
        }
    }
}
