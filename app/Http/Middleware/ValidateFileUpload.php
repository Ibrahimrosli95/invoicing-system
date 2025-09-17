<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\FileProcessingService;

class ValidateFileUpload
{
    protected FileProcessingService $fileProcessingService;
    
    public function __construct(FileProcessingService $fileProcessingService)
    {
        $this->fileProcessingService = $fileProcessingService;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only validate if there are file uploads
        if (!$request->hasFile('assets') && !$request->hasFile('asset')) {
            return $next($request);
        }
        
        $files = [];
        
        // Handle single file upload
        if ($request->hasFile('asset')) {
            $files[] = $request->file('asset');
        }
        
        // Handle multiple file uploads
        if ($request->hasFile('assets') && is_array($request->file('assets'))) {
            $files = array_merge($files, $request->file('assets'));
        }
        
        $errors = [];
        $totalSize = 0;
        
        foreach ($files as $file) {
            if (!$file->isValid()) {
                $errors[] = "Invalid file: {$file->getClientOriginalName()}";
                continue;
            }
            
            // Validate each file
            $validation = $this->fileProcessingService->validateFile($file);
            
            if (!$validation['valid']) {
                $errors = array_merge($errors, $validation['errors']);
            }
            
            $totalSize += $file->getSize();
        }
        
        // Check total upload size (100MB limit)
        $maxTotalSize = 100 * 1024 * 1024; // 100MB
        if ($totalSize > $maxTotalSize) {
            $errors[] = "Total upload size exceeds " . $this->formatFileSize($maxTotalSize) . " limit.";
        }
        
        // Return validation errors if any
        if (!empty($errors)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File validation failed',
                    'errors' => ['files' => $errors]
                ], 422);
            }
            
            return back()
                ->withErrors(['files' => $errors])
                ->withInput();
        }
        
        return $next($request);
    }
    
    /**
     * Format file size for human reading
     */
    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf("%.1f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }
}