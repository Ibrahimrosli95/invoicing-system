<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'teams', 'company'])
            ->where('company_id', auth()->user()->company_id);

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // Role filter
        if ($role = $request->get('role')) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        // Status filter
        if ($status = $request->get('status')) {
            if ($status === 'active') {
                $query->whereNotNull('email_verified_at');
            } elseif ($status === 'inactive') {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get available roles for filter dropdown
        $roles = Role::all();
        
        // Get user statistics
        $stats = [
            'total_users' => User::where('company_id', auth()->user()->company_id)->count(),
            'active_users' => User::where('company_id', auth()->user()->company_id)
                ->whereNotNull('email_verified_at')->count(),
            'inactive_users' => User::where('company_id', auth()->user()->company_id)
                ->whereNull('email_verified_at')->count(),
        ];

        return view('users.index', compact('users', 'roles', 'stats'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::all();
        $teams = Team::where('company_id', auth()->user()->company_id)->get();
        
        return view('users.create', compact('roles', 'teams'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
            'teams' => ['array'],
            'teams.*' => ['exists:teams,id'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        // Handle avatar upload
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'company_id' => auth()->user()->company_id,
            'avatar' => $avatarPath,
            'email_verified_at' => now(), // Auto-verify for admin created users
        ]);

        // Assign role
        $user->assignRole($validated['role']);

        // Assign teams
        if (!empty($validated['teams'])) {
            $user->teams()->sync($validated['teams']);
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load(['roles', 'teams.members', 'company', 'leads', 'quotations', 'invoices']);

        // Get user statistics
        $stats = [
            'leads_count' => $user->leads()->count(),
            'quotations_count' => $user->quotations()->count(),
            'invoices_count' => $user->invoices()->count(),
            'teams_count' => $user->teams()->count(),
        ];

        // Recent activity (last 30 days)
        $recentActivity = collect()
            ->merge($user->leads()->where('created_at', '>=', now()->subDays(30))->get()->map(function ($item) {
                return [
                    'type' => 'lead',
                    'action' => 'created',
                    'description' => "Created lead: {$item->customer_name}",
                    'date' => $item->created_at,
                    'url' => route('leads.show', $item),
                ];
            }))
            ->merge($user->quotations()->where('created_at', '>=', now()->subDays(30))->get()->map(function ($item) {
                return [
                    'type' => 'quotation',
                    'action' => 'created',
                    'description' => "Created quotation: {$item->number}",
                    'date' => $item->created_at,
                    'url' => route('quotations.show', $item),
                ];
            }))
            ->sortByDesc('date')
            ->take(10);

        return view('users.show', compact('user', 'stats', 'recentActivity'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = Role::all();
        $teams = Team::where('company_id', auth()->user()->company_id)->get();
        $userTeams = $user->teams->pluck('id')->toArray();

        return view('users.edit', compact('user', 'roles', 'teams', 'userTeams'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
            'teams' => ['array'],
            'teams.*' => ['exists:teams,id'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Handle password update
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Handle email verification based on status
        if ($validated['status'] === 'active') {
            $validated['email_verified_at'] = $user->email_verified_at ?? now();
        } else {
            $validated['email_verified_at'] = null;
        }

        // Remove non-user fields
        unset($validated['role'], $validated['teams'], $validated['status']);

        // Update user
        $user->update($validated);

        // Update role
        $user->syncRoles([$request->role]);

        // Update teams
        $user->teams()->sync($request->teams ?? []);

        return redirect()->route('users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account.');
        }

        // Delete avatar
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Show user profile page
     */
    public function profile()
    {
        $user = auth()->user();
        $user->load(['roles', 'teams', 'company']);

        return view('users.profile', compact('user'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'current_password' => ['nullable', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Handle password update
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Remove validation fields
        unset($validated['current_password']);

        $user->update($validated);

        return redirect()->route('profile')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Toggle user status (activate/deactivate)
     */
    public function toggleStatus(User $user)
    {
        $this->authorize('update', $user);

        // Prevent deactivating own account
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot deactivate your own account.');
        }

        if ($user->email_verified_at) {
            $user->update(['email_verified_at' => null]);
            $message = 'User deactivated successfully.';
        } else {
            $user->update(['email_verified_at' => now()]);
            $message = 'User activated successfully.';
        }

        return redirect()->back()->with('success', $message);
    }
}
