<?php

namespace App\Http\Controllers;

use App\Models\InvoiceNoteTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class InvoiceNoteTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = InvoiceNoteTemplate::forCompany()
            ->with('company');

        // Filter by type if specified
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        $templates = $query->orderBy('type')
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->paginate(15);

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'templates' => $templates->items(),
                'pagination' => [
                    'current_page' => $templates->currentPage(),
                    'last_page' => $templates->lastPage(),
                    'total' => $templates->total(),
                ]
            ]);
        }

        return view('invoice-note-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $types = InvoiceNoteTemplate::getTypes();
        return view('invoice-note-templates.create', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(InvoiceNoteTemplate::getTypes())),
            'content' => 'required|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active', true);

        $template = InvoiceNoteTemplate::create($validated);

        // Set as default if requested
        if ($validated['is_default']) {
            $template->setAsDefault();
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'template' => $template,
                'message' => 'Template created successfully'
            ]);
        }

        return redirect()->route('invoice-note-templates.index')
            ->with('success', 'Template created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(InvoiceNoteTemplate $invoiceNoteTemplate): View
    {
        // Ensure the template belongs to the user's company
        if ($invoiceNoteTemplate->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        return view('invoice-note-templates.show', compact('invoiceNoteTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvoiceNoteTemplate $invoiceNoteTemplate): View
    {
        // Ensure the template belongs to the user's company
        if ($invoiceNoteTemplate->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $types = InvoiceNoteTemplate::getTypes();
        return view('invoice-note-templates.edit', compact('invoiceNoteTemplate', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InvoiceNoteTemplate $invoiceNoteTemplate): RedirectResponse|JsonResponse
    {
        // Ensure the template belongs to the user's company
        if ($invoiceNoteTemplate->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(InvoiceNoteTemplate::getTypes())),
            'content' => 'required|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active', true);

        $invoiceNoteTemplate->update($validated);

        // Set as default if requested
        if ($validated['is_default']) {
            $invoiceNoteTemplate->setAsDefault();
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'template' => $invoiceNoteTemplate->fresh(),
                'message' => 'Template updated successfully'
            ]);
        }

        return redirect()->route('invoice-note-templates.index')
            ->with('success', 'Template updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceNoteTemplate $invoiceNoteTemplate): RedirectResponse|JsonResponse
    {
        // Ensure the template belongs to the user's company
        if ($invoiceNoteTemplate->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $invoiceNoteTemplate->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully'
            ]);
        }

        return redirect()->route('invoice-note-templates.index')
            ->with('success', 'Template deleted successfully');
    }

    /**
     * Set a template as default for its type
     */
    public function setDefault(InvoiceNoteTemplate $invoiceNoteTemplate): JsonResponse
    {
        // Ensure the template belongs to the user's company
        if ($invoiceNoteTemplate->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $invoiceNoteTemplate->setAsDefault();

        return response()->json([
            'success' => true,
            'message' => 'Template set as default'
        ]);
    }

    /**
     * Get templates for a specific type (AJAX endpoint for the invoice builder)
     */
    public function getByType(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:' . implode(',', array_keys(InvoiceNoteTemplate::getTypes()))
        ]);

        $templates = InvoiceNoteTemplate::getTemplatesForType($request->type);

        return response()->json([
            'templates' => $templates,
            'default' => InvoiceNoteTemplate::getDefaultForType($request->type)
        ]);
    }
}
