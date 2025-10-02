<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserSignatureController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the signature management page for the authenticated user
     */
    public function index()
    {
        $user = auth()->user();

        return view('profile.signature', compact('user'));
    }

    /**
     * Update the authenticated user's signature
     */
    public function update(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'signature_name' => 'nullable|string|max:100',
            'signature_title' => 'nullable|string|max:100',
            'signature_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'remove_signature' => 'nullable|boolean',
        ]);

        // Handle signature image upload
        if ($request->hasFile('signature_image')) {
            // Delete old signature if exists
            if (!empty($user->signature_path)) {
                Storage::disk('public')->delete($user->signature_path);
            }

            // Store new signature in user-specific subdirectory
            $path = $request->file('signature_image')->store('signatures/users', 'public');
            $user->signature_path = $path;
        }

        // Handle signature removal
        if ($request->boolean('remove_signature')) {
            if (!empty($user->signature_path)) {
                Storage::disk('public')->delete($user->signature_path);
            }
            $user->signature_path = null;
        }

        // Update name and title
        $user->signature_name = $validated['signature_name'] ?? $user->name;
        $user->signature_title = $validated['signature_title'] ?? 'Sales Representative';

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Signature updated successfully.',
            'signature' => [
                'name' => $user->signature_name,
                'title' => $user->signature_title,
                'image_path' => $user->signature_path,
            ]
        ]);
    }

    /**
     * Get the authenticated user's signature
     */
    public function show(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'signature' => [
                'name' => $user->signature_name,
                'title' => $user->signature_title,
                'image_path' => $user->signature_path,
            ]
        ]);
    }

    /**
     * Remove the authenticated user's signature
     */
    public function destroy(): JsonResponse
    {
        $user = auth()->user();

        // Delete signature file if exists
        if (!empty($user->signature_path)) {
            Storage::disk('public')->delete($user->signature_path);
        }

        // Clear signature fields
        $user->signature_path = null;
        $user->signature_name = null;
        $user->signature_title = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Signature removed successfully.'
        ]);
    }
}
