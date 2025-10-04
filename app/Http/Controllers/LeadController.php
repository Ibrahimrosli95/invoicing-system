<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class LeadController extends Controller
{

    /**
     * Display a listing of leads.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Lead::class);

        $query = Lead::forCompany()
            ->forUserTeams()
            ->with(['team', 'assignedTo', 'activities' => function ($query) {
                $query->latest()->limit(1);
            }]);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('requirements', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('urgency')) {
            $query->where('urgency', $request->urgency);
        }

        if ($request->filled('qualified')) {
            $query->where('is_qualified', $request->qualified === 'yes');
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $leads = $query->paginate(20)->withQueryString();

        // Get filter options
        $teams = Team::forCompany()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $assignees = User::forCompany()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['sales_executive', 'sales_coordinator', 'sales_manager']);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $filters = [
            'statuses' => Lead::getStatuses(),
            'sources' => Lead::getSources(),
            'urgencyLevels' => Lead::getUrgencyLevels(),
            'teams' => $teams,
            'assignees' => $assignees,
        ];

        return view('leads.index', compact('leads', 'filters'));
    }

    /**
     * Display the Kanban board view.
     */
    public function kanban(Request $request): View
    {
        $this->authorize('viewAny', Lead::class);

        $query = Lead::forCompany()
            ->forUserTeams()
            ->with(['team', 'assignedTo']);

        // Apply filters
        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $leads = $query->orderBy('created_at', 'desc')->get();

        // Group leads by status
        $leadsByStatus = [
            Lead::STATUS_NEW => $leads->where('status', Lead::STATUS_NEW),
            Lead::STATUS_CONTACTED => $leads->where('status', Lead::STATUS_CONTACTED),
            Lead::STATUS_QUOTED => $leads->where('status', Lead::STATUS_QUOTED),
            Lead::STATUS_WON => $leads->where('status', Lead::STATUS_WON),
            Lead::STATUS_LOST => $leads->where('status', Lead::STATUS_LOST),
        ];

        // Get filter options
        $teams = Team::forCompany()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $assignees = User::forCompany()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['sales_executive', 'sales_coordinator', 'sales_manager']);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $filters = [
            'teams' => $teams,
            'assignees' => $assignees,
        ];

        return view('leads.kanban', compact('leadsByStatus', 'filters'));
    }

    /**
     * Show the form for creating a new lead.
     */
    public function create(): View
    {
        $this->authorize('create', Lead::class);

        $teams = Team::forCompany()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $assignees = User::forCompany()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['sales_executive', 'sales_coordinator']);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('leads.create', compact('teams', 'assignees'));
    }

    /**
     * Store a newly created lead in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Lead::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'source' => 'required|string|in:' . implode(',', array_keys(Lead::getSources())),
            'urgency' => 'required|string|in:' . implode(',', array_keys(Lead::getUrgencyLevels())),
            'requirements' => 'nullable|string',
            'estimated_value' => 'nullable|numeric|min:0',
            'team_id' => 'nullable|exists:teams,id',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        // Add company_id from authenticated user
        $validated['company_id'] = auth()->user()->company_id;

        // Check for duplicate phone numbers
        $existingLead = Lead::forCompany()
            ->where('phone', $validated['phone'])
            ->first();

        if ($existingLead) {
            return back()
                ->withInput()
                ->with('warning', 'A lead with this phone number already exists: ' . $existingLead->name);
        }

        $lead = Lead::create($validated);

        // Create initial activity
        LeadActivity::createActivity(
            $lead,
            auth()->user(),
            LeadActivity::TYPE_NOTE,
            'Lead created',
            'Lead was created in the system',
            null,
            ['created_via' => 'manual_entry']
        );

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead created successfully.');
    }

    /**
     * Display the specified lead.
     */
    public function show(Lead $lead): View
    {
        $this->authorize('view', $lead);

        // Track contact if transparency tracking is enabled
        // Only track if current user is not the assigned user (to track when OTHER reps view the lead)
        if (config('lead_tracking.enabled') &&
            config('lead_tracking.contact_tracking.track_contacts') &&
            $lead->assigned_to !== auth()->id()) {
            $lead->recordContact(auth()->user());
        }

        $lead->load([
            'team',
            'assignedTo',
            'activities' => function ($query) {
                $query->with('user')->latest();
            },
            'quotations' => function ($query) {
                $query->latest();
            }
        ]);

        return view('leads.show', compact('lead'));
    }

    /**
     * Show the form for editing the specified lead.
     */
    public function edit(Lead $lead): View
    {
        $this->authorize('update', $lead);

        $teams = Team::forCompany()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $assignees = User::forCompany()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['sales_executive', 'sales_coordinator']);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('leads.edit', compact('lead', 'teams', 'assignees'));
    }

    /**
     * Update the specified lead in storage.
     */
    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'source' => 'required|string|in:' . implode(',', array_keys(Lead::getSources())),
            'status' => 'required|string|in:' . implode(',', array_keys(Lead::getStatuses())),
            'urgency' => 'required|string|in:' . implode(',', array_keys(Lead::getUrgencyLevels())),
            'requirements' => 'nullable|string',
            'estimated_value' => 'nullable|numeric|min:0',
            'team_id' => 'nullable|exists:teams,id',
            'assigned_to' => 'nullable|exists:users,id',
            'is_qualified' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Track status changes
        $statusChanged = $lead->status !== $validated['status'];
        $assignmentChanged = $lead->assigned_to !== $validated['assigned_to'];

        $lead->update($validated);

        // Log status change activity
        if ($statusChanged) {
            LeadActivity::createActivity(
                $lead,
                auth()->user(),
                LeadActivity::TYPE_STATUS_CHANGE,
                'Status changed to ' . $validated['status'],
                "Lead status changed from {$lead->getOriginal('status')} to {$validated['status']}",
                null,
                ['old_status' => $lead->getOriginal('status'), 'new_status' => $validated['status']]
            );
        }

        // Log assignment change activity
        if ($assignmentChanged) {
            $oldAssignee = $lead->getOriginal('assigned_to') 
                ? User::find($lead->getOriginal('assigned_to'))?->name 
                : 'Unassigned';
            $newAssignee = $validated['assigned_to'] 
                ? User::find($validated['assigned_to'])?->name 
                : 'Unassigned';

            LeadActivity::createActivity(
                $lead,
                auth()->user(),
                LeadActivity::TYPE_ASSIGNMENT,
                'Assignment changed',
                "Lead reassigned from {$oldAssignee} to {$newAssignee}",
                null,
                ['old_assignee' => $lead->getOriginal('assigned_to'), 'new_assignee' => $validated['assigned_to']]
            );
        }

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead updated successfully.');
    }

    /**
     * Remove the specified lead from storage.
     */
    public function destroy(Lead $lead): RedirectResponse
    {
        $this->authorize('delete', $lead);

        $lead->delete();

        return redirect()->route('leads.index')
            ->with('success', 'Lead deleted successfully.');
    }

    /**
     * Clear review flags for a lead (manager/coordinator action)
     */
    public function clearFlags(Lead $lead): RedirectResponse
    {
        // Only managers and coordinators can clear flags
        if (!auth()->user()->hasAnyRole(['superadmin', 'company_manager', 'sales_manager', 'sales_coordinator'])) {
            abort(403, 'Unauthorized to clear review flags.');
        }

        $lead->clearReviewFlags();

        return back()->with('success', 'Review flags cleared successfully.');
    }

    /**
     * Update lead status via AJAX (for Kanban board).
     */
    public function updateStatus(Request $request, Lead $lead): JsonResponse
    {
        $this->authorize('update', $lead);

        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(Lead::getStatuses())),
        ]);

        $oldStatus = $lead->status;
        $lead->update(['status' => $validated['status']]);

        // Log status change
        LeadActivity::createActivity(
            $lead,
            auth()->user(),
            LeadActivity::TYPE_STATUS_CHANGE,
            'Status changed to ' . $validated['status'],
            "Lead status changed from {$oldStatus} to {$validated['status']} via Kanban board",
            null,
            ['old_status' => $oldStatus, 'new_status' => $validated['status'], 'via' => 'kanban']
        );

        return response()->json([
            'success' => true,
            'message' => 'Lead status updated successfully.',
        ]);
    }

    /**
     * Search clients for enhanced builders (API endpoint).
     */
    public function searchClients(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['clients' => []]);
        }

        $clients = Lead::forCompany()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'phone', 'email', 'address', 'source')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($lead) {
                return [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'phone' => $lead->phone,
                    'email' => $lead->email,
                    'address' => $lead->address,
                    'source' => $lead->source,
                ];
            });

        return response()->json(['clients' => $clients]);
    }

    /**
     * Get recent clients for enhanced builders (API endpoint).
     */
    public function getRecentClients(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 20);

        $clients = Lead::forCompany()
            ->where('status', '!=', 'LOST')
            ->select('id', 'name', 'phone', 'email', 'address', 'source', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($lead) {
                return [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'phone' => $lead->phone,
                    'email' => $lead->email,
                    'address' => $lead->address,
                    'source' => $lead->source,
                    'last_contact' => $lead->updated_at->diffForHumans(),
                ];
            });

        return response()->json(['clients' => $clients]);
    }
}