<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ProofSecurityService;
use App\Services\ProofAuditService;
use App\Models\Proof;
use Illuminate\Support\Facades\Log;

class ProofSecurityMiddleware
{
    protected ProofSecurityService $securityService;
    protected ProofAuditService $auditService;

    public function __construct(
        ProofSecurityService $securityService,
        ProofAuditService $auditService
    ) {
        $this->securityService = $securityService;
        $this->auditService = $auditService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $action = 'view'): Response
    {
        // Get proof from route parameters
        $proof = $this->getProofFromRequest($request);
        
        if (!$proof) {
            return response()->json(['error' => 'Proof not found'], 404);
        }

        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        // Check if user can access sensitive content
        if (!$this->securityService->canAccessSensitiveContent($user, $proof)) {
            $this->auditService->logEvent('access_denied', $proof, [
                'reason' => 'insufficient_clearance',
                'action' => $action,
                'user_clearance' => $this->securityService->getUserClearanceLevel($user),
                'required_clearance' => $this->securityService->getSecurityLevel($proof),
            ]);

            return response()->json([
                'error' => 'Access denied: Insufficient security clearance',
                'required_clearance' => $this->getSecurityLevelName($this->securityService->getSecurityLevel($proof))
            ], 403);
        }

        // Check access restrictions
        $restrictionsCheck = $this->securityService->checkAccessRestrictions($proof, $request);
        
        if (!$restrictionsCheck['allowed']) {
            $this->auditService->logEvent('access_denied', $proof, [
                'reason' => 'access_restrictions',
                'action' => $action,
                'violations' => $restrictionsCheck['violations'],
            ]);

            $this->securityService->logSecurityEvent('access_restriction_violation', $proof, [
                'violations' => $restrictionsCheck['violations'],
                'action' => $action,
            ]);

            return response()->json([
                'error' => 'Access restricted',
                'violations' => $restrictionsCheck['violations'],
            ], 403);
        }

        // Log successful access
        $this->auditService->logEvent($this->mapActionToEvent($action), $proof, [
            'action' => $action,
            'security_level' => $this->securityService->getSecurityLevel($proof),
            'user_clearance' => $this->securityService->getUserClearanceLevel($user),
        ]);

        // Add security headers to response
        $response = $next($request);
        
        return $this->addSecurityHeaders($response, $proof);
    }

    /**
     * Get proof from request parameters
     */
    protected function getProofFromRequest(Request $request): ?Proof
    {
        // Try different parameter names
        $proofId = $request->route('proof') ?? 
                  $request->route('uuid') ?? 
                  $request->route('id');

        if (!$proofId) {
            return null;
        }

        // Try to find by ID first, then by UUID
        if (is_numeric($proofId)) {
            return Proof::find($proofId);
        }

        return Proof::where('uuid', $proofId)->first();
    }

    /**
     * Map middleware action to audit event
     */
    protected function mapActionToEvent(string $action): string
    {
        return match ($action) {
            'view' => 'viewed',
            'download' => 'downloaded',
            'share' => 'shared',
            'edit' => 'accessed_for_edit',
            'delete' => 'accessed_for_delete',
            default => 'accessed',
        };
    }

    /**
     * Get security level name
     */
    protected function getSecurityLevelName(int $level): string
    {
        $levels = array_flip(ProofSecurityService::SECURITY_LEVELS);
        return $levels[$level] ?? 'unknown';
    }

    /**
     * Add security headers to response
     */
    protected function addSecurityHeaders(Response $response, Proof $proof): Response
    {
        $securityLevel = $this->securityService->getSecurityLevel($proof);
        $metadata = $proof->metadata ?? [];
        $restrictions = $metadata['access_restrictions'] ?? [];

        // Add content security headers
        if ($securityLevel >= ProofSecurityService::SECURITY_LEVELS['confidential']) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
        }

        // Add no-cache headers for sensitive content
        if ($securityLevel >= ProofSecurityService::SECURITY_LEVELS['restricted']) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        // Add watermarking header if required
        if ($restrictions['watermarking_required'] ?? false) {
            $response->headers->set('X-Watermark-Required', 'true');
        }

        // Add download restriction header
        if ($restrictions['download_disabled'] ?? false) {
            $response->headers->set('X-Download-Disabled', 'true');
            $response->headers->set('Content-Disposition', 'inline');
        }

        return $response;
    }
}