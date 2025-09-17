<?php

namespace App\Services;

use App\Models\Proof;
use App\Models\ProofAsset;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProofSecurityService
{
    /**
     * Security classification levels
     */
    const SECURITY_LEVELS = [
        'public' => 0,
        'internal' => 1,
        'confidential' => 2,
        'restricted' => 3,
        'highly_confidential' => 4,
    ];

    /**
     * Data sensitivity types
     */
    const SENSITIVITY_TYPES = [
        'financial_data' => 'Financial Information',
        'customer_pii' => 'Customer Personal Information',
        'trade_secrets' => 'Trade Secrets',
        'legal_sensitive' => 'Legally Sensitive Content',
        'competitive_info' => 'Competitive Information',
        'customer_confidential' => 'Customer Confidential Data',
    ];

    /**
     * Check if user can access sensitive content
     */
    public function canAccessSensitiveContent(User $user, Proof $proof): bool
    {
        // Multi-tenant check first
        if ($user->company_id !== $proof->company_id) {
            return false;
        }

        $securityLevel = $this->getSecurityLevel($proof);
        $userClearance = $this->getUserClearanceLevel($user);

        // Check if user has sufficient clearance
        if ($userClearance < $securityLevel) {
            Log::warning('Access denied: Insufficient security clearance', [
                'user_id' => $user->id,
                'proof_id' => $proof->id,
                'required_level' => $securityLevel,
                'user_clearance' => $userClearance,
            ]);
            return false;
        }

        // Additional checks for highly sensitive content
        if ($securityLevel >= self::SECURITY_LEVELS['restricted']) {
            return $this->performAdditionalSecurityChecks($user, $proof);
        }

        return true;
    }

    /**
     * Get security level for a proof
     */
    public function getSecurityLevel(Proof $proof): int
    {
        $metadata = $proof->metadata ?? [];
        
        // Check explicit security classification
        if (isset($metadata['security_level'])) {
            return self::SECURITY_LEVELS[$metadata['security_level']] ?? self::SECURITY_LEVELS['internal'];
        }

        // Auto-classify based on content and sensitivity
        return $this->autoClassifySecurityLevel($proof, $metadata);
    }

    /**
     * Set security level for a proof
     */
    public function setSecurityLevel(Proof $proof, string $level, string $reason = null): bool
    {
        if (!array_key_exists($level, self::SECURITY_LEVELS)) {
            return false;
        }

        try {
            $metadata = $proof->metadata ?? [];
            $metadata['security_level'] = $level;
            $metadata['security_classification'] = [
                'level' => $level,
                'classified_at' => now()->toISOString(),
                'classified_by' => auth()->id(),
                'reason' => $reason,
                'auto_classified' => false,
            ];

            $proof->update(['metadata' => $metadata]);

            Log::info('Proof security level updated', [
                'proof_id' => $proof->id,
                'security_level' => $level,
                'classified_by' => auth()->id(),
                'reason' => $reason,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to set security level', [
                'proof_id' => $proof->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get user security clearance level
     */
    public function getUserClearanceLevel(User $user): int
    {
        // Base clearance on user role
        if ($user->hasRole('superadmin')) {
            return self::SECURITY_LEVELS['highly_confidential'];
        }
        
        if ($user->hasRole('company_manager')) {
            return self::SECURITY_LEVELS['restricted'];
        }
        
        if ($user->hasAnyRole(['finance_manager', 'sales_manager'])) {
            return self::SECURITY_LEVELS['confidential'];
        }
        
        if ($user->hasRole('sales_coordinator')) {
            return self::SECURITY_LEVELS['internal'];
        }
        
        // Default for sales executives
        return self::SECURITY_LEVELS['internal'];
    }

    /**
     * Check for sensitive data patterns in proof content
     */
    public function scanForSensitiveData(Proof $proof): array
    {
        $findings = [];
        $content = [
            'title' => $proof->title ?? '',
            'description' => $proof->description ?? '',
            'metadata' => json_encode($proof->metadata ?? []),
        ];

        foreach ($content as $field => $text) {
            $findings = array_merge($findings, $this->scanTextForSensitivePatterns($text, $field));
        }

        // Scan assets for sensitive content
        foreach ($proof->assets as $asset) {
            $assetFindings = $this->scanAssetForSensitiveData($asset);
            $findings = array_merge($findings, $assetFindings);
        }

        return $findings;
    }

    /**
     * Apply access restrictions to proof
     */
    public function applyAccessRestrictions(Proof $proof, array $restrictions): bool
    {
        try {
            $metadata = $proof->metadata ?? [];
            $metadata['access_restrictions'] = [
                'ip_whitelist' => $restrictions['ip_whitelist'] ?? null,
                'time_restrictions' => $restrictions['time_restrictions'] ?? null,
                'location_restrictions' => $restrictions['location_restrictions'] ?? null,
                'device_restrictions' => $restrictions['device_restrictions'] ?? null,
                'watermarking_required' => $restrictions['watermarking_required'] ?? false,
                'download_disabled' => $restrictions['download_disabled'] ?? false,
                'view_limit' => $restrictions['view_limit'] ?? null,
                'expiry_date' => $restrictions['expiry_date'] ?? null,
                'applied_by' => auth()->id(),
                'applied_at' => now()->toISOString(),
            ];

            $proof->update(['metadata' => $metadata]);

            Log::info('Access restrictions applied to proof', [
                'proof_id' => $proof->id,
                'restrictions' => array_keys($restrictions),
                'applied_by' => auth()->id(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to apply access restrictions', [
                'proof_id' => $proof->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check access restrictions for current request
     */
    public function checkAccessRestrictions(Proof $proof, Request $request): array
    {
        $metadata = $proof->metadata ?? [];
        $restrictions = $metadata['access_restrictions'] ?? [];
        $violations = [];

        if (empty($restrictions)) {
            return ['allowed' => true, 'violations' => []];
        }

        // Check IP whitelist
        if (!empty($restrictions['ip_whitelist'])) {
            $clientIp = $request->ip();
            if (!in_array($clientIp, $restrictions['ip_whitelist'])) {
                $violations[] = "IP address {$clientIp} not in whitelist";
            }
        }

        // Check time restrictions
        if (!empty($restrictions['time_restrictions'])) {
            $currentHour = now()->hour;
            $timeRestriction = $restrictions['time_restrictions'];
            
            if (isset($timeRestriction['start_hour'], $timeRestriction['end_hour'])) {
                if ($currentHour < $timeRestriction['start_hour'] || $currentHour > $timeRestriction['end_hour']) {
                    $violations[] = "Access outside allowed hours ({$timeRestriction['start_hour']}-{$timeRestriction['end_hour']})";
                }
            }
        }

        // Check device restrictions
        if (!empty($restrictions['device_restrictions'])) {
            $userAgent = $request->userAgent();
            $deviceType = $this->detectDeviceType($userAgent);
            
            if (!empty($restrictions['device_restrictions']['allowed_devices'])) {
                if (!in_array($deviceType, $restrictions['device_restrictions']['allowed_devices'])) {
                    $violations[] = "Device type '{$deviceType}' not allowed";
                }
            }
        }

        // Check view limit
        if (!empty($restrictions['view_limit'])) {
            $viewCount = $this->getUserViewCount($proof, auth()->id());
            if ($viewCount >= $restrictions['view_limit']) {
                $violations[] = "View limit ({$restrictions['view_limit']}) exceeded";
            }
        }

        // Check expiry date
        if (!empty($restrictions['expiry_date'])) {
            if (now()->isAfter(Carbon::parse($restrictions['expiry_date']))) {
                $violations[] = "Content has expired";
            }
        }

        return [
            'allowed' => empty($violations),
            'violations' => $violations,
            'restrictions' => $restrictions,
        ];
    }

    /**
     * Generate secure access token for sensitive proof
     */
    public function generateSecureAccessToken(Proof $proof, int $userId, int $validityHours = 24): string
    {
        $tokenData = [
            'proof_id' => $proof->id,
            'user_id' => $userId,
            'company_id' => $proof->company_id,
            'security_level' => $this->getSecurityLevel($proof),
            'generated_at' => now()->timestamp,
            'expires_at' => now()->addHours($validityHours)->timestamp,
            'nonce' => bin2hex(random_bytes(16)),
        ];

        $token = base64_encode(json_encode($tokenData));
        $signature = hash_hmac('sha256', $token, config('app.key'));
        
        $secureToken = $token . '.' . $signature;

        // Store token for validation
        $cacheKey = "proof_access_token:{$userId}:{$proof->id}";
        Cache::put($cacheKey, $secureToken, now()->addHours($validityHours));

        Log::info('Secure access token generated', [
            'proof_id' => $proof->id,
            'user_id' => $userId,
            'validity_hours' => $validityHours,
        ]);

        return $secureToken;
    }

    /**
     * Validate secure access token
     */
    public function validateSecureAccessToken(string $token, Proof $proof, int $userId): bool
    {
        try {
            [$tokenData, $signature] = explode('.', $token, 2);
            
            // Verify signature
            $expectedSignature = hash_hmac('sha256', $tokenData, config('app.key'));
            if (!hash_equals($expectedSignature, $signature)) {
                return false;
            }

            $data = json_decode(base64_decode($tokenData), true);
            
            // Validate token data
            if ($data['proof_id'] !== $proof->id || 
                $data['user_id'] !== $userId ||
                $data['company_id'] !== $proof->company_id ||
                $data['expires_at'] < now()->timestamp) {
                return false;
            }

            // Check if token still exists in cache
            $cacheKey = "proof_access_token:{$userId}:{$proof->id}";
            return Cache::has($cacheKey);
            
        } catch (\Exception $e) {
            Log::warning('Invalid access token format', [
                'proof_id' => $proof->id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Revoke access token
     */
    public function revokeAccessToken(Proof $proof, int $userId): bool
    {
        $cacheKey = "proof_access_token:{$userId}:{$proof->id}";
        Cache::forget($cacheKey);

        Log::info('Access token revoked', [
            'proof_id' => $proof->id,
            'user_id' => $userId,
        ]);

        return true;
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $eventType, Proof $proof, array $details = []): void
    {
        $securityEvent = [
            'event_type' => $eventType,
            'proof_id' => $proof->id,
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => $details,
        ];

        Log::warning('Proof security event', $securityEvent);

        // Store in proof metadata for audit
        $metadata = $proof->metadata ?? [];
        $metadata['security_events'] = $metadata['security_events'] ?? [];
        $metadata['security_events'][] = $securityEvent;
        $proof->update(['metadata' => $metadata]);
    }

    /**
     * Get security violations for a proof
     */
    public function getSecurityViolations(Proof $proof, int $days = 30): array
    {
        $metadata = $proof->metadata ?? [];
        $securityEvents = $metadata['security_events'] ?? [];
        $cutoffDate = now()->subDays($days);

        return array_filter($securityEvents, function ($event) use ($cutoffDate) {
            return Carbon::parse($event['timestamp'])->isAfter($cutoffDate);
        });
    }

    /**
     * Auto-classify security level based on content
     */
    protected function autoClassifySecurityLevel(Proof $proof, array $metadata): int
    {
        $level = self::SECURITY_LEVELS['internal']; // Default

        // Check for sensitive data indicators
        if (isset($metadata['contains_pii']) && $metadata['contains_pii']) {
            $level = max($level, self::SECURITY_LEVELS['confidential']);
        }

        if (isset($metadata['financial_data']) && !empty($metadata['financial_data'])) {
            $level = max($level, self::SECURITY_LEVELS['confidential']);
        }

        if (isset($metadata['customer_consent_required']) && $metadata['customer_consent_required']) {
            $level = max($level, self::SECURITY_LEVELS['restricted']);
        }

        // Check proof type
        if (in_array($proof->type, ['case_study', 'testimonial', 'client_review'])) {
            $level = max($level, self::SECURITY_LEVELS['confidential']);
        }

        return $level;
    }

    /**
     * Perform additional security checks for highly sensitive content
     */
    protected function performAdditionalSecurityChecks(User $user, Proof $proof): bool
    {
        // Check if user has recently authenticated (within last hour)
        $lastActivity = Cache::get("user_last_activity:{$user->id}");
        if (!$lastActivity || Carbon::parse($lastActivity)->isBefore(now()->subHour())) {
            Log::warning('Access denied: Recent authentication required', [
                'user_id' => $user->id,
                'proof_id' => $proof->id,
            ]);
            return false;
        }

        // Check for any recent security violations
        $violations = $this->getSecurityViolations($proof, 1);
        if (!empty($violations)) {
            Log::warning('Access denied: Recent security violations', [
                'user_id' => $user->id,
                'proof_id' => $proof->id,
                'violation_count' => count($violations),
            ]);
            return false;
        }

        return true;
    }

    /**
     * Scan text for sensitive data patterns
     */
    protected function scanTextForSensitivePatterns(string $text, string $field): array
    {
        $findings = [];
        
        // Credit card patterns
        if (preg_match('/\b\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}\b/', $text)) {
            $findings[] = [
                'type' => 'financial_data',
                'pattern' => 'credit_card',
                'field' => $field,
                'confidence' => 'high',
            ];
        }

        // Phone number patterns
        if (preg_match('/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/', $text)) {
            $findings[] = [
                'type' => 'customer_pii',
                'pattern' => 'phone_number',
                'field' => $field,
                'confidence' => 'medium',
            ];
        }

        // Email patterns
        if (preg_match('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', $text)) {
            $findings[] = [
                'type' => 'customer_pii',
                'pattern' => 'email_address',
                'field' => $field,
                'confidence' => 'high',
            ];
        }

        // Currency amounts
        if (preg_match('/\$\d+(?:,\d{3})*(?:\.\d{2})?|\b\d+(?:,\d{3})*(?:\.\d{2})?\s*(?:USD|MYR|RM)\b/', $text)) {
            $findings[] = [
                'type' => 'financial_data',
                'pattern' => 'currency_amount',
                'field' => $field,
                'confidence' => 'medium',
            ];
        }

        return $findings;
    }

    /**
     * Scan asset for sensitive data
     */
    protected function scanAssetForSensitiveData(ProofAsset $asset): array
    {
        $findings = [];

        // Check file metadata
        if ($asset->title && $this->scanTextForSensitivePatterns($asset->title, 'asset_title')) {
            $findings = array_merge($findings, $this->scanTextForSensitivePatterns($asset->title, 'asset_title'));
        }

        // Add file type specific checks
        if ($asset->type === 'document') {
            $findings[] = [
                'type' => 'document_content',
                'pattern' => 'document_file',
                'field' => 'asset_file',
                'confidence' => 'medium',
                'recommendation' => 'Review document content for sensitive information',
            ];
        }

        return $findings;
    }

    /**
     * Detect device type from user agent
     */
    protected function detectDeviceType(string $userAgent): string
    {
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return 'mobile';
        }
        
        if (preg_match('/Tablet|iPad/', $userAgent)) {
            return 'tablet';
        }
        
        return 'desktop';
    }

    /**
     * Get user view count for a proof
     */
    protected function getUserViewCount(Proof $proof, ?int $userId): int
    {
        if (!$userId) {
            return 0;
        }

        return $proof->views()
            ->where('viewer_type', 'user')
            ->where('viewer_id', $userId)
            ->count();
    }
}