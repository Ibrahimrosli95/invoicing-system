<?php

namespace App\Observers;

use App\Models\Certification;
use App\Models\Proof;

class CertificationObserver
{
    public function created(Certification $certification): void
    {
        $this->createOrUpdateProof($certification);
    }

    public function updated(Certification $certification): void
    {
        if ($certification->status === 'active') {
            $this->createOrUpdateProof($certification);
        }
    }

    public function deleted(Certification $certification): void
    {
        $this->deleteRelatedProofs($certification);
    }

    private function createOrUpdateProof(Certification $certification): void
    {
        if ($certification->status !== 'active') {
            return;
        }

        $proof = Proof::where('company_id', $certification->company_id)
            ->where('title', 'Professional Certifications')
            ->where('proof_type', 'certifications')
            ->first();

        if (!$proof) {
            $proof = Proof::create([
                'company_id' => $certification->company_id,
                'title' => 'Professional Certifications',
                'description' => 'Industry certifications and professional qualifications demonstrating our expertise and commitment to quality.',
                'proof_type' => 'certifications',
                'category' => 'Professional',
                'status' => 'active',
                'metadata' => [
                    'auto_generated' => true,
                    'source' => 'certification_observer',
                    'last_updated' => now()->toIso8601String()
                ]
            ]);
        }

        $this->updateProofContent($proof);
    }

    private function updateProofContent(Proof $proof): void
    {
        $certifications = Certification::where('company_id', $proof->company_id)
            ->where('status', 'active')
            ->orderBy('expiry_date', 'desc')
            ->get();

        $proofData = [
            'total_certifications' => $certifications->count(),
            'active_certifications' => $certifications->where('status', 'active')->count(),
            'expiring_soon' => $certifications->where('expiry_date', '<=', now()->addDays(30))->count(),
            'certifications' => $certifications->map(function ($cert) {
                return [
                    'id' => $cert->id,
                    'name' => $cert->name,
                    'issuer' => $cert->issuer,
                    'issued_date' => $cert->issued_date?->format('Y-m-d'),
                    'expiry_date' => $cert->expiry_date?->format('Y-m-d'),
                    'credential_id' => $cert->credential_id,
                    'verification_url' => $cert->verification_url,
                ];
            })->toArray()
        ];

        $proof->update([
            'content' => $proofData,
            'impact_score' => min(100, $certifications->count() * 10),
            'metadata' => array_merge($proof->metadata ?? [], [
                'last_compiled' => now()->toIso8601String(),
                'certification_count' => $certifications->count(),
            ])
        ]);
    }

    private function deleteRelatedProofs(Certification $certification): void
    {
        $remainingCount = Certification::where('company_id', $certification->company_id)
            ->where('status', 'active')
            ->count();

        if ($remainingCount === 0) {
            Proof::where('company_id', $certification->company_id)
                ->where('proof_type', 'certifications')
                ->whereJsonContains('metadata->auto_generated', true)
                ->delete();
        } else {
            $proof = Proof::where('company_id', $certification->company_id)
                ->where('proof_type', 'certifications')
                ->first();
                
            if ($proof) {
                $this->updateProofContent($proof);
            }
        }
    }
}
