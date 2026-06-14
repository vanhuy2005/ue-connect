<?php

namespace App\Http\Controllers;

use App\Services\CareerPathwaySearchService;
use Illuminate\Http\Request;

class CareerPathwaySearchController extends Controller
{
    public function __construct(private CareerPathwaySearchService $searchService) {}

    public function index(Request $request)
    {
        $query = $request->query('q', '');
        $filters = $request->except(['q', 'page', 'per_page']);
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 15);

        $paginator = $this->searchService->search($query, $filters, $perPage, $page);

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'query' => $query,
                'total' => $paginator->total(),
                'types' => $filters['type'] ?? 'all',
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function courses(Request $request)
    {
        $query = $request->query('q', '');
        $filters = $request->except('q');

        return response()->json(['data' => $this->searchService->searchCourses($query, $filters)]);
    }

    public function programs(Request $request)
    {
        $query = $request->query('q', '');
        $filters = $request->except('q');

        return response()->json(['data' => $this->searchService->searchPrograms($query, $filters)]);
    }

    public function positions(Request $request)
    {
        $query = $request->query('q', '');
        $filters = $request->except('q');

        return response()->json(['data' => $this->searchService->searchPositions($query, $filters)]);
    }

    public function skills(Request $request)
    {
        $query = $request->query('q', '');
        $filters = $request->except('q');

        return response()->json(['data' => $this->searchService->searchSkills($query, $filters)]);
    }

    public function seniorPathways(Request $request)
    {
        $query = $request->query('q', '');
        $filters = $request->except('q');

        return response()->json(['data' => $this->searchService->searchSeniorPathways($query, $filters)]);
    }
}
