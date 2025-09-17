<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SearchController extends Controller
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->middleware('auth');
        $this->searchService = $searchService;
    }

    /**
     * Display the main search page
     */
    public function index(Request $request): View
    {
        $query = $request->get('q', '');
        $filters = $request->get('filters', []);
        $results = [];
        $searchPerformed = false;

        if (!empty($query)) {
            $results = $this->searchService->globalSearch($query, $filters);
            $this->searchService->saveRecentSearch($query, $filters, 'global');
            $this->searchService->recordSearch($query, 'global', collect($results)->sum('count'));
            $searchPerformed = true;
        }

        $recentSearches = $this->searchService->getRecentSearches();
        $analytics = $this->searchService->getSearchAnalytics();
        
        return view('search.index', compact(
            'query',
            'filters', 
            'results',
            'searchPerformed',
            'recentSearches',
            'analytics'
        ));
    }

    /**
     * Perform AJAX global search
     */
    public function globalSearch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|min:1|max:255',
            'filters' => 'nullable|array',
            'filters.date_from' => 'nullable|date',
            'filters.date_to' => 'nullable|date',
            'filters.status' => 'nullable|array',
            'filters.team_id' => 'nullable|integer|exists:teams,id',
            'filters.user_id' => 'nullable|integer|exists:users,id',
            'filters.amount_from' => 'nullable|numeric|min:0',
            'filters.amount_to' => 'nullable|numeric|min:0',
            'filters.tags' => 'nullable|array',
        ]);

        $results = $this->searchService->globalSearch(
            $validated['query'], 
            $validated['filters'] ?? []
        );

        // Record search analytics
        $totalResults = collect($results)->sum('count');
        $this->searchService->recordSearch($validated['query'], 'global', $totalResults);
        $this->searchService->saveRecentSearch(
            $validated['query'], 
            $validated['filters'] ?? [], 
            'global'
        );

        return response()->json([
            'success' => true,
            'query' => $validated['query'],
            'results' => $results,
            'total_results' => $totalResults,
            'search_time' => microtime(true) - LARAVEL_START,
        ]);
    }

    /**
     * Search within specific entity type
     */
    public function searchEntity(Request $request, string $type): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|min:1|max:255',
            'filters' => 'nullable|array',
            'limit' => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:0',
        ]);

        // Validate entity type
        $allowedTypes = ['leads', 'quotations', 'invoices', 'users'];
        if (!in_array($type, $allowedTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid entity type',
            ], 400);
        }

        // Get model class for the type
        $modelClass = $this->searchService->getModelClass($type);
        if (!$modelClass) {
            return response()->json([
                'success' => false,
                'message' => 'Entity type not supported',
            ], 400);
        }

        $results = $this->searchService->searchModel(
            $modelClass,
            $validated['query'],
            $validated['filters'] ?? [],
            $type
        );

        // Apply pagination if requested
        if (isset($validated['limit'])) {
            $offset = $validated['offset'] ?? 0;
            $paginatedResults = $results->slice($offset, $validated['limit'])->values();
            $total = $results->count();
        } else {
            $paginatedResults = $results;
            $total = $results->count();
        }

        // Record search analytics
        $this->searchService->recordSearch($validated['query'], $type, $total);

        return response()->json([
            'success' => true,
            'type' => $type,
            'query' => $validated['query'],
            'results' => $paginatedResults,
            'total' => $total,
            'limit' => $validated['limit'] ?? null,
            'offset' => $validated['offset'] ?? 0,
        ]);
    }

    /**
     * Get search suggestions for autocomplete
     */
    public function suggestions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2|max:255',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $suggestions = $this->searchService->getSuggestions(
            $validated['query'],
            $validated['limit'] ?? 5
        );

        return response()->json([
            'success' => true,
            'query' => $validated['query'],
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Get recent searches for current user
     */
    public function recentSearches(): JsonResponse
    {
        $recentSearches = $this->searchService->getRecentSearches();

        return response()->json([
            'success' => true,
            'recent_searches' => $recentSearches,
        ]);
    }

    /**
     * Clear recent searches for current user
     */
    public function clearRecentSearches(): JsonResponse
    {
        $this->searchService->clearRecentSearches();

        return response()->json([
            'success' => true,
            'message' => 'Recent searches cleared successfully',
        ]);
    }

    /**
     * Get filter options for specific entity type
     */
    public function filterOptions(Request $request, string $type): JsonResponse
    {
        $allowedTypes = ['leads', 'quotations', 'invoices', 'users'];
        if (!in_array($type, $allowedTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid entity type',
            ], 400);
        }

        $options = $this->searchService->getFilterOptions($type);

        return response()->json([
            'success' => true,
            'type' => $type,
            'filter_options' => $options,
        ]);
    }

    /**
     * Get search analytics for current user/company
     */
    public function analytics(): JsonResponse
    {
        $analytics = $this->searchService->getSearchAnalytics();

        return response()->json([
            'success' => true,
            'analytics' => $analytics,
        ]);
    }

    /**
     * Advanced search page for specific entity type
     */
    public function advanced(Request $request, string $type): View
    {
        $allowedTypes = ['leads', 'quotations', 'invoices', 'users'];
        if (!in_array($type, $allowedTypes)) {
            abort(404, 'Invalid search type');
        }

        $query = $request->get('q', '');
        $filters = $request->get('filters', []);
        $results = collect();
        $searchPerformed = false;

        if (!empty($query)) {
            $modelClass = $this->searchService->getModelClass($type);
            $results = $this->searchService->searchModel($modelClass, $query, $filters, $type);
            $this->searchService->saveRecentSearch($query, $filters, $type);
            $this->searchService->recordSearch($query, $type, $results->count());
            $searchPerformed = true;
        }

        $filterOptions = $this->searchService->getFilterOptions($type);
        $recentSearches = $this->searchService->getRecentSearches();

        return view('search.advanced', compact(
            'type',
            'query',
            'filters',
            'results',
            'searchPerformed',
            'filterOptions',
            'recentSearches'
        ));
    }

    /**
     * Export search results
     */
    public function export(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|min:1|max:255',
            'type' => 'required|string|in:leads,quotations,invoices,users,global',
            'filters' => 'nullable|array',
            'format' => 'required|string|in:csv,xlsx,pdf',
        ]);

        try {
            // Generate filename
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "search_{$validated['type']}_{$timestamp}.{$validated['format']}";

            if ($validated['type'] === 'global') {
                // Global search export - combine all results
                $results = $this->searchService->globalSearch(
                    $validated['query'],
                    $validated['filters'] ?? []
                );

                // For now, redirect back with success message
                // Full export implementation would require ExportService integration
                return back()->with('success', 'Search export functionality will be available soon.');
            } else {
                // Single entity type export
                $modelClass = $this->searchService->getModelClass($validated['type']);
                $results = $this->searchService->searchModel(
                    $modelClass,
                    $validated['query'],
                    $validated['filters'] ?? [],
                    $validated['type']
                );

                // For now, redirect back with success message
                return back()->with('success', 'Search export functionality will be available soon.');
            }
        } catch (\Exception $e) {
            return back()->withErrors(['export' => 'Export failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Save search as template/bookmark
     */
    public function saveSearch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:1|max:100',
            'query' => 'required|string|min:1|max:255',
            'type' => 'required|string|in:leads,quotations,invoices,users,global',
            'filters' => 'nullable|array',
        ]);

        try {
            // For now, we'll store saved searches in cache
            // In a full implementation, this would use a dedicated table
            $userId = auth()->id();
            $savedSearches = cache()->get("saved_searches.user.{$userId}", []);

            $searchData = [
                'id' => uniqid(),
                'name' => $validated['name'],
                'query' => $validated['query'],
                'type' => $validated['type'],
                'filters' => $validated['filters'] ?? [],
                'created_at' => now()->toISOString(),
            ];

            $savedSearches[] = $searchData;

            // Keep only last 20 saved searches
            $savedSearches = array_slice($savedSearches, -20);

            cache()->put("saved_searches.user.{$userId}", $savedSearches, 86400 * 30); // 30 days

            return response()->json([
                'success' => true,
                'message' => 'Search saved successfully',
                'saved_search' => $searchData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save search: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get saved searches for current user
     */
    public function savedSearches(): JsonResponse
    {
        $userId = auth()->id();
        $savedSearches = cache()->get("saved_searches.user.{$userId}", []);

        return response()->json([
            'success' => true,
            'saved_searches' => array_reverse($savedSearches), // Most recent first
        ]);
    }

    /**
     * Delete saved search
     */
    public function deleteSavedSearch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search_id' => 'required|string',
        ]);

        try {
            $userId = auth()->id();
            $savedSearches = cache()->get("saved_searches.user.{$userId}", []);

            $savedSearches = array_filter($savedSearches, function ($search) use ($validated) {
                return $search['id'] !== $validated['search_id'];
            });

            cache()->put("saved_searches.user.{$userId}", array_values($savedSearches), 86400 * 30);

            return response()->json([
                'success' => true,
                'message' => 'Saved search deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete saved search: ' . $e->getMessage(),
            ], 500);
        }
    }
}