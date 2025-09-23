<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationController extends Controller
{

    /**
     * Display the organization hierarchy.
     */
    public function index(): View
    {
        $user = auth()->user();
        $company = $user->company;

        // Load the complete organization structure
        $company->load([
            'users' => function ($query) {
                $query->whereHas('roles', function ($roleQuery) {
                    $roleQuery->whereIn('name', ['company_manager', 'finance_manager']);
                })
                ->orderBy('name');
            },
            'teams' => function ($query) {
                $query->with([
                    'manager',
                    'coordinator',
                    'users' => function ($userQuery) {
                        $userQuery->orderBy('name');
                    }
                ])
                ->orderBy('name');
            }
        ]);

        // Get hierarchy statistics
        $stats = [
            'total_users' => User::forCompany()->count(),
            'active_users' => User::forCompany()->where('is_active', true)->count(),
            'total_teams' => Team::forCompany()->count(),
            'active_teams' => Team::forCompany()->where('is_active', true)->count(),
            'managers' => User::forCompany()
                ->whereHas('roles', function ($query) {
                    $query->whereIn('name', ['company_manager', 'sales_manager']);
                })
                ->count(),
            'coordinators' => User::forCompany()
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'sales_coordinator');
                })
                ->count(),
            'executives' => User::forCompany()
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'sales_executive');
                })
                ->count(),
        ];

        return view('organization.index', compact('company', 'stats'));
    }

    /**
     * Display the organization chart view.
     */
    public function chart(): View
    {
        $user = auth()->user();
        $company = $user->company;

        // Build hierarchical structure for the chart
        $hierarchy = $this->buildHierarchy($company);

        return view('organization.chart', compact('company', 'hierarchy'));
    }

    /**
     * Build the hierarchical organization structure.
     */
    private function buildHierarchy(Company $company): array
    {
        // Start with company level
        $hierarchy = [
            'id' => 'company-' . $company->id,
            'name' => $company->name,
            'type' => 'company',
            'children' => []
        ];

        // Add company managers
        $companyManagers = User::forCompany($company->id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'company_manager');
            })
            ->with('roles')
            ->get();

        foreach ($companyManagers as $manager) {
            $managerNode = [
                'id' => 'user-' . $manager->id,
                'name' => $manager->name,
                'title' => 'Company Manager',
                'type' => 'manager',
                'email' => $manager->email,
                'children' => []
            ];

            // Add teams managed by this manager
            $teams = Team::forCompany($company->id)
                ->where('manager_id', $manager->id)
                ->with(['coordinator', 'users'])
                ->get();

            foreach ($teams as $team) {
                $teamNode = [
                    'id' => 'team-' . $team->id,
                    'name' => $team->name,
                    'type' => 'team',
                    'territory' => $team->territory,
                    'children' => []
                ];

                // Add coordinator if exists
                if ($team->coordinator) {
                    $coordinatorNode = [
                        'id' => 'coordinator-' . $team->coordinator->id,
                        'name' => $team->coordinator->name,
                        'title' => 'Team Coordinator',
                        'type' => 'coordinator',
                        'email' => $team->coordinator->email,
                        'children' => []
                    ];

                    // Add team members under coordinator
                    foreach ($team->users as $user) {
                        if ($user->id !== $team->coordinator->id && $user->id !== $team->manager_id) {
                            $coordinatorNode['children'][] = [
                                'id' => 'member-' . $user->id,
                                'name' => $user->name,
                                'title' => $user->title ?: 'Sales Executive',
                                'type' => 'member',
                                'email' => $user->email,
                                'children' => []
                            ];
                        }
                    }

                    $teamNode['children'][] = $coordinatorNode;
                } else {
                    // Add team members directly under team
                    foreach ($team->users as $user) {
                        if ($user->id !== $team->manager_id) {
                            $teamNode['children'][] = [
                                'id' => 'member-' . $user->id,
                                'name' => $user->name,
                                'title' => $user->title ?: 'Sales Executive',
                                'type' => 'member',
                                'email' => $user->email,
                                'children' => []
                            ];
                        }
                    }
                }

                $managerNode['children'][] = $teamNode;
            }

            $hierarchy['children'][] = $managerNode;
        }

        // Add teams without managers directly under company
        $teamsWithoutManager = Team::forCompany($company->id)
            ->whereNull('manager_id')
            ->with(['coordinator', 'users'])
            ->get();

        foreach ($teamsWithoutManager as $team) {
            $teamNode = [
                'id' => 'team-' . $team->id,
                'name' => $team->name,
                'type' => 'team',
                'territory' => $team->territory,
                'children' => []
            ];

            // Add team members
            foreach ($team->users as $user) {
                $teamNode['children'][] = [
                    'id' => 'member-' . $user->id,
                    'name' => $user->name,
                    'title' => $user->title ?: 'Sales Executive',
                    'type' => 'member',
                    'email' => $user->email,
                    'children' => []
                ];
            }

            $hierarchy['children'][] = $teamNode;
        }

        return $hierarchy;
    }
}