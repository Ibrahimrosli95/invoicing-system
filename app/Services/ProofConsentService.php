<?php

namespace App\Services;

use App\Models\Proof;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProofConsentService
{
    /**
     * Generate consent token for customer approval
     */
    public function generateConsentToken(Proof $proof, array $customerData): string
    {
        $token = Str::random(32);
        
        // Store consent data in proof metadata
        $metadata = $proof->metadata ?? [];
        $metadata['consent'] = [
            'token' => hash('sha256', $token),
            'customer_name' => $customerData['name'] ?? null,
            'customer_email' => $customerData['email'] ?? null,
            'customer_phone' => $customerData['phone'] ?? null,
            'requested_at' => now()->toISOString(),
            'requested_by' => auth()->id(),
            'status' => 'pending',
            'consent_type' => $this->getConsentType($proof->type),
            'data_usage' => $this->getDataUsageDescription($proof->type),
        ];
        
        $proof->update(['metadata' => $metadata]);
        
        Log::info('Consent token generated for proof', [
            'proof_id' => $proof->id,
            'customer_email' => $customerData['email'] ?? 'not provided',
            'token_hash' => $metadata['consent']['token'],
        ]);
        
        return $token;
    }

    /**
     * Send consent request email to customer
     */
    public function sendConsentRequest(Proof $proof, string $token, array $customerData): bool
    {
        try {
            $consentUrl = route('proof.consent.form', [
                'uuid' => $proof->uuid,
                'token' => $token
            ]);

            // In a real implementation, you would create a ConsentRequestNotification
            // For now, we'll log the consent request
            Log::info('Consent request email would be sent', [
                'proof_id' => $proof->id,
                'customer_email' => $customerData['email'] ?? null,
                'consent_url' => $consentUrl,
                'proof_title' => $proof->title,
                'company' => $proof->company->name ?? 'Unknown Company',
            ]);

            // Update metadata to track email sent
            $metadata = $proof->metadata ?? [];
            $metadata['consent']['email_sent_at'] = now()->toISOString();
            $proof->update(['metadata' => $metadata]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send consent request email', [
                'proof_id' => $proof->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Verify consent token
     */
    public function verifyConsentToken(Proof $proof, string $token): bool
    {
        $metadata = $proof->metadata ?? [];
        $consentData = $metadata['consent'] ?? [];
        
        if (!isset($consentData['token'])) {
            return false;
        }
        
        return hash('sha256', $token) === $consentData['token'] &&
               $consentData['status'] === 'pending';
    }

    /**
     * Grant consent for proof usage
     */
    public function grantConsent(Proof $proof, string $token, array $consentDetails = []): bool
    {
        if (!$this->verifyConsentToken($proof, $token)) {
            return false;
        }

        $metadata = $proof->metadata ?? [];
        $metadata['consent']['status'] = 'granted';
        $metadata['consent']['granted_at'] = now()->toISOString();
        $metadata['consent']['ip_address'] = request()->ip();
        $metadata['consent']['user_agent'] = request()->userAgent();
        
        // Store additional consent details
        if (!empty($consentDetails)) {
            $metadata['consent']['details'] = $consentDetails;
        }

        $proof->update([
            'metadata' => $metadata,
            'status' => 'active', // Auto-publish when consent is granted
        ]);

        Log::info('Consent granted for proof', [
            'proof_id' => $proof->id,
            'granted_at' => $metadata['consent']['granted_at'],
            'ip_address' => $metadata['consent']['ip_address'],
        ]);

        return true;
    }

    /**
     * Revoke consent for proof usage
     */
    public function revokeConsent(Proof $proof, string $reason = null): bool
    {
        $metadata = $proof->metadata ?? [];
        
        if (!isset($metadata['consent'])) {
            return false;
        }

        $metadata['consent']['status'] = 'revoked';
        $metadata['consent']['revoked_at'] = now()->toISOString();
        $metadata['consent']['revocation_reason'] = $reason;
        $metadata['consent']['revoked_by_ip'] = request()->ip();

        $proof->update([
            'metadata' => $metadata,
            'status' => 'archived', // Auto-archive when consent is revoked
        ]);

        Log::warning('Consent revoked for proof', [
            'proof_id' => $proof->id,
            'revoked_at' => $metadata['consent']['revoked_at'],
            'reason' => $reason,
        ]);

        // Trigger cleanup of proof usage in existing documents
        $this->cleanupRevokedProof($proof);

        return true;
    }

    /**
     * Get consent status for a proof
     */
    public function getConsentStatus(Proof $proof): array
    {
        $metadata = $proof->metadata ?? [];
        $consentData = $metadata['consent'] ?? [];

        return [
            'has_consent_data' => !empty($consentData),
            'status' => $consentData['status'] ?? 'not_required',
            'requested_at' => $consentData['requested_at'] ?? null,
            'granted_at' => $consentData['granted_at'] ?? null,
            'revoked_at' => $consentData['revoked_at'] ?? null,
            'customer_email' => $consentData['customer_email'] ?? null,
            'customer_name' => $consentData['customer_name'] ?? null,
            'consent_type' => $consentData['consent_type'] ?? null,
            'email_sent' => isset($consentData['email_sent_at']),
            'expires_at' => $this->getConsentExpiration($consentData),
        ];
    }

    /**
     * Check if proof requires customer consent
     */
    public function requiresConsent(Proof $proof): bool
    {
        return in_array($proof->type, [
            'testimonial',
            'case_study', 
            'client_review',
            'social_proof'
        ]) || $this->containsPersonalData($proof);
    }

    /**
     * Bulk consent status check
     */
    public function getBulkConsentStatus(array $proofIds): array
    {
        $proofs = Proof::whereIn('id', $proofIds)->get();
        $results = [];

        foreach ($proofs as $proof) {
            $results[$proof->id] = $this->getConsentStatus($proof);
        }

        return $results;
    }

    /**
     * Get expiring consents
     */
    public function getExpiringConsents(int $days = 30): array
    {
        // Find proofs with consent expiring within specified days
        $proofs = Proof::where('status', 'active')
            ->whereNotNull('metadata')
            ->get()
            ->filter(function ($proof) use ($days) {
                $consentData = $proof->metadata['consent'] ?? [];
                
                if ($consentData['status'] ?? null !== 'granted') {
                    return false;
                }
                
                $expiresAt = $this->getConsentExpiration($consentData);
                
                return $expiresAt && 
                       Carbon::parse($expiresAt)->isBefore(now()->addDays($days));
            });

        return $proofs->map(function ($proof) {
            return [
                'proof' => $proof,
                'consent_status' => $this->getConsentStatus($proof),
                'expires_in_days' => Carbon::parse($this->getConsentExpiration($proof->metadata['consent'] ?? []))
                    ->diffInDays(now()),
            ];
        })->toArray();
    }

    /**
     * Cleanup proof when consent is revoked
     */
    protected function cleanupRevokedProof(Proof $proof): void
    {
        // Remove proof from active use in quotations and invoices
        $proof->update([
            'show_in_pdf' => false,
            'show_in_quotation' => false,
            'show_in_invoice' => false,
            'is_featured' => false,
        ]);

        Log::info('Cleaned up revoked proof from active use', [
            'proof_id' => $proof->id,
        ]);
    }

    /**
     * Get consent type based on proof type
     */
    protected function getConsentType(string $proofType): string
    {
        return match ($proofType) {
            'testimonial' => 'testimonial_usage',
            'case_study' => 'case_study_publication',
            'client_review' => 'review_display',
            'social_proof' => 'social_media_content',
            default => 'general_marketing'
        };
    }

    /**
     * Get data usage description
     */
    protected function getDataUsageDescription(string $proofType): string
    {
        return match ($proofType) {
            'testimonial' => 'Your testimonial will be used in marketing materials, quotations, and company website.',
            'case_study' => 'Your project details will be published as a case study for marketing purposes.',
            'client_review' => 'Your review will be displayed publicly and in sales materials.',
            'social_proof' => 'Your content will be shared on social media and marketing channels.',
            default => 'Your information will be used for general marketing and sales purposes.'
        };
    }

    /**
     * Check if proof contains personal data
     */
    protected function containsPersonalData(Proof $proof): bool
    {
        $metadata = $proof->metadata ?? [];
        
        return isset($metadata['contains_pii']) && $metadata['contains_pii'] === true ||
               isset($metadata['customer_data']) && !empty($metadata['customer_data']) ||
               !empty($metadata['customer_email'] ?? null) ||
               !empty($metadata['customer_phone'] ?? null);
    }

    /**
     * Get consent expiration date
     */
    protected function getConsentExpiration(array $consentData): ?string
    {
        if (!isset($consentData['granted_at'])) {
            return null;
        }

        // Default consent validity: 2 years
        $grantedAt = Carbon::parse($consentData['granted_at']);
        return $grantedAt->addYears(2)->toISOString();
    }

    /**
     * Generate consent withdrawal link
     */
    public function generateWithdrawalLink(Proof $proof): ?string
    {
        $consentData = $proof->metadata['consent'] ?? [];
        
        if (($consentData['status'] ?? null) !== 'granted') {
            return null;
        }

        return route('proof.consent.withdraw', [
            'uuid' => $proof->uuid,
            'token' => Str::random(32) // Generate new token for withdrawal
        ]);
    }

    /**
     * Anonymize proof data for GDPR compliance
     */
    public function anonymizeProofData(Proof $proof): bool
    {
        try {
            $metadata = $proof->metadata ?? [];
            
            // Remove personal identifiable information
            if (isset($metadata['consent'])) {
                unset(
                    $metadata['consent']['customer_name'],
                    $metadata['consent']['customer_email'],
                    $metadata['consent']['customer_phone']
                );
                $metadata['consent']['anonymized_at'] = now()->toISOString();
                $metadata['consent']['anonymized_by'] = auth()->id();
            }

            // Remove any customer data
            unset(
                $metadata['customer_data'],
                $metadata['customer_email'],
                $metadata['customer_phone'],
                $metadata['customer_contact']
            );

            $proof->update([
                'metadata' => $metadata,
                'title' => 'Anonymized Proof',
                'description' => 'This proof has been anonymized for privacy compliance.',
            ]);

            Log::info('Proof data anonymized', [
                'proof_id' => $proof->id,
                'anonymized_at' => now()->toISOString(),
                'anonymized_by' => auth()->id(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to anonymize proof data', [
                'proof_id' => $proof->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}