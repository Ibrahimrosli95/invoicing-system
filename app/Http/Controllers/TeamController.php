<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TeamController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view,team')->only(['show']);
        $this->middleware('can:update,team')->only(['edit', 'update']);
        $this->middleware('can:delete,team')->only(['destroy']);
    }

    /**
     * Display a listing of the teams.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Team::class);

        $teams = Team::forCompany()
            ->with(['manager', 'coordinator', 'users'])
            ->withCount('users')
            ->orderBy('name')
            ->paginate(20);

        return view('teams.index', compact('teams'));
    }

    /**
     * Show the form for creating a new team.
     */
    public function create(): View
    {
        $this->authorize('create', Team::class);

        $managers = User::forCompany()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['sales_manager', 'company_manager']);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $coordinators = User::forCompany()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['sales_coordinator', 'sales_manager']);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('teams.create', compact('managers', 'coordinators'));
    }

    /**
     * Store a newly created team in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Team::class);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'manager_id' => 'nullable|exists:users,id',
            'coordinator_id' => 'nullable|exists:users,id',
            'territory' => 'nullable|string|max:255',
            'target_revenue' => 'nullable|numeric|min:0',
        ]);

        // Add company_id from authenticated user
        $validated['company_id'] = auth()->user()->company_id;
        $validated['is_active'] = true;

        $team = Team::create($validated);

        return redirect()->route('teams.show', $team)
            ->with('success', 'Team created successfully.');
    }

    /**
     * Display the specified team.
     */
    public function show(Team $team): View
    {
        $this->authorize('view', $team);

        $team->load(['manager', 'coordinator', 'users', 'company']);

        // Get team statistics
        $stats = [
            'total_users' => $team->users->count(),
            'active_users' => $team->users->where('is_active', true)->count(),
            // Add more stats when leads model is ready
        ];

        return view('teams.show', compact('team', 'stats'));
    }

    /**
     * Show the form for editing the specified team.
     */
    public function edit(Team $team): View
    {
        $this->authorize('update', $team);

        $managers = User::forCompany()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['sales_manager', 'company_manager']);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $coordinators = User::forCompany()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['sales_coordinator', 'sales_manager']);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('teams.edit', compact('team', 'managers', 'coordinators'));
    }

    /**
     * Update the specified team in storage.
     */
    public function update(Request $request, Team $team): RedirectResponse
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'manager_id' => 'nullable|exists:users,id',
            'coordinator_id' => 'nullable|exists:users,id',
            'territory' => 'nullable|string|max:255',
            'target_revenue' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $team->update($validated);

        return redirect()->route('teams.show', $team)
            ->with('success', 'Team updated successfully.');
    }

    /**
     * Remove the specified team from storage.
     */
    public function destroy(Team $team): RedirectResponse
    {
        $this->authorize('delete', $team);

        // Check if team has users assigned
        if ($team->users->count() > 0) {
            return back()->with('error', 'Cannot delete team with assigned users.');
        }

        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Team deleted successfully.');
    }

    /**
     * Show the team member assignment interface.
     */
    public function members(Team $team): View
    {
        $this->authorize('update', $team);

        $team->load(['users', 'company']);

        // Get available users for assignment (users in same company not already on team)
        $availableUsers = User::forCompany($team->company_id)
            ->whereDoesntHave('teams', function ($query) use ($team) {
                $query->where('team_id', $team->id);
            })
            ->select('id', 'name', 'email', 'title')
            ->orderBy('name')
            ->get();

        return view('teams.members', compact('team', 'availableUsers'));
    }

    /**
     * Assign users to the team.
     */
    public function assignMembers(Request $request, Team $team): RedirectResponse
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        // Verify all users belong to the same company
        $users = User::whereIn('id', $validated['user_ids'])
            ->where('company_id', $team->company_id)
            ->get();

        if ($users->count() !== count($validated['user_ids'])) {
            return back()->with('error', 'Some users do not belong to the same company.');
        }

        // Attach users to team (sync will replace existing assignments)
        $team->users()->syncWithoutDetaching($validated['user_ids']);

        return redirect()->route('teams.members', $team)
            ->with('success', 'Team members assigned successfully.');
    }

    /**
     * Remove a user from the team.
     */
    public function removeMember(Request $request, Team $team, User $user): RedirectResponse
    {
        $this->authorize('update', $team);

        // Verify user belongs to the same company
        if ($user->company_id !== $team->company_id) {
            return back()->with('error', 'User does not belong to the same company.');
        }

        $team->users()->detach($user->id);

        return redirect()->route('teams.members', $team)
            ->with('success', 'User removed from team successfully.');
    }

    /**
     * Show the team settings page.
     */
    public function settings(Team $team): View
    {
        $this->authorize('update', $team);

        $team->load(['manager', 'coordinator', 'company']);

        return view('teams.settings', compact('team'));
    }

    /**
     * Update team settings.
     */
    public function updateSettings(Request $request, Team $team): RedirectResponse
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'default_terms' => 'nullable|string',
            'default_notes' => 'nullable|string',
            'notification_preferences' => 'nullable|array',
            'notification_preferences.*' => 'boolean',
            'performance_goals' => 'nullable|array',
            'performance_goals.monthly_quota' => 'nullable|numeric|min:0',
            'performance_goals.annual_quota' => 'nullable|numeric|min:0',
            'performance_goals.conversion_target' => 'nullable|numeric|min:0|max:100',
        ]);

        // Merge with existing settings
        $settings = $team->settings ?: [];
        
        if (isset($validated['default_terms'])) {
            $settings['default_terms'] = $validated['default_terms'];
        }
        
        if (isset($validated['default_notes'])) {
            $settings['default_notes'] = $validated['default_notes'];
        }
        
        if (isset($validated['notification_preferences'])) {
            $settings['notification_preferences'] = $validated['notification_preferences'];
        }
        
        if (isset($validated['performance_goals'])) {
            $settings['performance_goals'] = array_filter($validated['performance_goals']);
        }

        $team->update(['settings' => $settings]);

        return redirect()->route('teams.settings', $team)
            ->with('success', 'Team settings updated successfully.');
    }
}