<?php

namespace App\Http\Controllers;

use App\Models\NumberSequence;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class NumberSequenceController extends Controller
{

    /**
     * Display the numbering settings page.
     */
    public function index(): View
    {
        $companyId = auth()->user()->company_id;
        
        // Get all sequences for current company
        $sequences = NumberSequence::forCompany($companyId)
            ->orderBy('type')
            ->get()
            ->keyBy('type');

        // Get available types and ensure we have sequences for all
        $availableTypes = NumberSequence::getAvailableTypes();
        
        foreach ($availableTypes as $type => $label) {
            if (!$sequences->has($type)) {
                $sequence = NumberSequence::getForType($type, $companyId);
                $sequences->put($type, $sequence);
            }
        }

        return view('settings.numbering.index', compact('sequences', 'availableTypes'));
    }

    /**
     * Show the form for editing a specific sequence.
     */
    public function edit(string $type): View
    {
        $sequence = NumberSequence::getForType($type, auth()->user()->company_id);
        $availableTypes = NumberSequence::getAvailableTypes();
        $formatPlaceholders = NumberSequence::getFormatPlaceholders();
        
        if (!array_key_exists($type, $availableTypes)) {
            abort(404, 'Sequence type not found');
        }

        return view('settings.numbering.edit', compact('sequence', 'availableTypes', 'formatPlaceholders'));
    }

    /**
     * Update the specified sequence.
     */
    public function update(Request $request, string $type): RedirectResponse
    {
        $sequence = NumberSequence::getForType($type, auth()->user()->company_id);
        
        $validated = $request->validate([
            'prefix' => 'required|string|max:10',
            'format' => 'required|string|max:50',
            'padding' => 'required|integer|min:1|max:10',
            'yearly_reset' => 'boolean',
            'current_number' => 'required|integer|min:0',
        ]);

        // Validate format
        if (!NumberSequence::validateFormat($validated['format'])) {
            return back()->withInput()->withErrors([
                'format' => 'Invalid format. Must contain {number} and only valid placeholders.',
            ]);
        }

        $sequence->update($validated);

        return redirect()->route('settings.numbering.index')
            ->with('success', 'Numbering sequence updated successfully.');
    }

    /**
     * Reset a sequence to a specific number.
     */
    public function reset(Request $request, string $type): RedirectResponse
    {
        $sequence = NumberSequence::getForType($type, auth()->user()->company_id);
        
        $validated = $request->validate([
            'reset_to' => 'required|integer|min:0',
        ]);

        $sequence->resetTo($validated['reset_to']);

        return redirect()->route('settings.numbering.index')
            ->with('success', "Sequence reset to {$validated['reset_to']} successfully.");
    }

    /**
     * Preview what a number would look like with given settings.
     */
    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prefix' => 'required|string|max:10',
            'format' => 'required|string|max:50',
            'padding' => 'required|integer|min:1|max:10',
            'number' => 'nullable|integer|min:1',
            'yearly_reset' => 'boolean',
        ]);

        // Validate format
        if (!NumberSequence::validateFormat($validated['format'])) {
            return response()->json([
                'error' => 'Invalid format. Must contain {number} and only valid placeholders.',
            ], 400);
        }

        $number = $validated['number'] ?? 1;
        $paddedNumber = str_pad($number, $validated['padding'], '0', STR_PAD_LEFT);
        
        $preview = str_replace(
            ['{prefix}', '{year}', '{number}'],
            [$validated['prefix'], now()->year, $paddedNumber],
            $validated['format']
        );

        return response()->json([
            'preview' => $preview,
        ]);
    }

    /**
     * Get sequence statistics.
     */
    public function statistics(string $type): JsonResponse
    {
        $sequence = NumberSequence::getForType($type, auth()->user()->company_id);
        $stats = $sequence->getStatistics();

        return response()->json($stats);
    }

    /**
     * Bulk update all sequences.
     */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sequences' => 'required|array',
            'sequences.*.type' => 'required|string',
            'sequences.*.prefix' => 'required|string|max:10',
            'sequences.*.format' => 'required|string|max:50',
            'sequences.*.padding' => 'required|integer|min:1|max:10',
            'sequences.*.yearly_reset' => 'boolean',
        ]);

        $companyId = auth()->user()->company_id;
        $updated = 0;

        foreach ($validated['sequences'] as $sequenceData) {
            // Validate format
            if (!NumberSequence::validateFormat($sequenceData['format'])) {
                continue; // Skip invalid formats
            }

            $sequence = NumberSequence::getForType($sequenceData['type'], $companyId);
            $sequence->update([
                'prefix' => $sequenceData['prefix'],
                'format' => $sequenceData['format'],
                'padding' => $sequenceData['padding'],
                'yearly_reset' => $sequenceData['yearly_reset'] ?? false,
            ]);
            
            $updated++;
        }

        return redirect()->route('settings.numbering.index')
            ->with('success', "Updated {$updated} numbering sequences successfully.");
    }

    /**
     * Reset all sequences to defaults.
     */
    public function resetToDefaults(): RedirectResponse
    {
        $companyId = auth()->user()->company_id;
        $availableTypes = NumberSequence::getAvailableTypes();
        $defaultPrefixes = NumberSequence::getDefaultPrefixes();
        $defaultFormats = NumberSequence::getDefaultFormats();

        foreach ($availableTypes as $type => $label) {
            $sequence = NumberSequence::getForType($type, $companyId);
            $sequence->update([
                'prefix' => $defaultPrefixes[$type] ?? strtoupper($type),
                'format' => $defaultFormats[$type] ?? '{prefix}-{year}-{number}',
                'padding' => 6,
                'yearly_reset' => true,
            ]);
        }

        return redirect()->route('settings.numbering.index')
            ->with('success', 'All numbering sequences reset to defaults successfully.');
    }

    /**
     * Export numbering configuration as JSON.
     */
    public function export(): JsonResponse
    {
        $sequences = NumberSequence::forCompany(auth()->user()->company_id)
            ->orderBy('type')
            ->get(['type', 'prefix', 'format', 'padding', 'yearly_reset', 'current_number'])
            ->keyBy('type');

        return response()->json([
            'company_id' => auth()->user()->company_id,
            'exported_at' => now()->toISOString(),
            'sequences' => $sequences,
        ]);
    }

    /**
     * Import numbering configuration from JSON.
     */
    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'import_file' => 'required|file|mimes:json|max:1024',
        ]);

        try {
            $content = file_get_contents($validated['import_file']->path());
            $data = json_decode($content, true);

            if (!$data || !isset($data['sequences'])) {
                throw new \Exception('Invalid import file format');
            }

            $companyId = auth()->user()->company_id;
            $imported = 0;

            foreach ($data['sequences'] as $type => $sequenceData) {
                // Validate format
                if (!NumberSequence::validateFormat($sequenceData['format'])) {
                    continue;
                }

                $sequence = NumberSequence::getForType($type, $companyId);
                $sequence->update([
                    'prefix' => $sequenceData['prefix'],
                    'format' => $sequenceData['format'],
                    'padding' => $sequenceData['padding'],
                    'yearly_reset' => $sequenceData['yearly_reset'] ?? false,
                ]);
                
                $imported++;
            }

            return redirect()->route('settings.numbering.index')
                ->with('success', "Imported {$imported} numbering sequences successfully.");

        } catch (\Exception $e) {
            return back()->withErrors(['import_file' => 'Failed to import: ' . $e->getMessage()]);
        }
    }
}