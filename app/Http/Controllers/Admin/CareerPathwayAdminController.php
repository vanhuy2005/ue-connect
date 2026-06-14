<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CareerPathway\StartImportRunRequest;
use App\Http\Requests\Admin\CareerPathway\UpdateProgramStatusRequest;
use App\Models\CareerDataQualityIssue;
use App\Models\CareerImportRun;
use App\Models\CareerProgram;
use App\Models\CareerSourceDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class CareerPathwayAdminController extends Controller
{
    /**
     * List historical imports.
     */
    public function importRuns(Request $request): JsonResponse|View
    {
        $query = CareerImportRun::query()
            ->withCount(['sourceDocuments', 'dataQualityIssues'])
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status'));
            });

        $runs = $query
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json(['data' => $runs]);
        }

        $stats = [
            'total' => CareerImportRun::count(),
            'running' => CareerImportRun::where('status', 'running')->count(),
            'completed' => CareerImportRun::where('status', 'completed')->count(),
            'failed' => CareerImportRun::whereIn('status', ['failed', 'aborted'])->count(),
            'issues' => CareerDataQualityIssue::count(),
        ];

        return view('admin.career-pathway.import-runs', [
            'runs' => $runs,
            'stats' => $stats,
            'selectedStatus' => $request->string('status')->toString(),
        ]);
    }

    /**
     * Dispatch the import logic.
     */
    public function startImportRun(StartImportRunRequest $request): RedirectResponse|JsonResponse
    {
        $path = $request->input('path');

        // Use Artisan::queue if jobs are set up, but Artisan::call runs synchronously for testing/simplicity here unless we have a dedicated Job.
        // It's safer to queue it, but the command is synchronous.
        Artisan::queue('career-pathway:import', ['path' => $path]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Import run dispatched.']);
        }

        return redirect()
            ->route('admin.career-pathway.import-runs.index')
            ->with('status', 'Đã đưa tác vụ import vào hàng đợi.');
    }

    /**
     * List parsed PDFs/Markdown docs.
     */
    public function sourceDocuments(Request $request)
    {
        $documents = CareerSourceDocument::orderByDesc('id')->paginate(20);

        return response()->json(['data' => $documents]);
    }

    /**
     * List parsing errors/warnings (includes raw_context).
     */
    public function dataQualityIssues(Request $request): JsonResponse|View
    {
        $query = CareerDataQualityIssue::query()
            ->with(['careerProgram', 'sourceDocument', 'importRun'])
            ->when($request->filled('severity'), function ($query) use ($request): void {
                $query->where('severity', $request->string('severity'));
            })
            ->when($request->filled('issue_type'), function ($query) use ($request): void {
                $query->where('issue_type', $request->string('issue_type'));
            })
            ->when($request->filled('import_run_id'), function ($query) use ($request): void {
                $query->where('import_run_id', $request->integer('import_run_id'));
            });

        $issues = $query
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json(['data' => $issues]);
        }

        $stats = [
            'total' => CareerDataQualityIssue::count(),
            'p0' => CareerDataQualityIssue::where('severity', 'p0')->count(),
            'p1' => CareerDataQualityIssue::where('severity', 'p1')->count(),
            'p2' => CareerDataQualityIssue::where('severity', 'p2')->count(),
            'programs_affected' => CareerDataQualityIssue::whereNotNull('program_id')->distinct('program_id')->count('program_id'),
        ];

        $issueTypeCounts = CareerDataQualityIssue::query()
            ->selectRaw('issue_type, count(*) as total')
            ->groupBy('issue_type')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'issue_type');

        $importRuns = CareerImportRun::query()
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'status', 'started_at']);

        return view('admin.career-pathway.data-quality-issues', [
            'issues' => $issues,
            'stats' => $stats,
            'issueTypeCounts' => $issueTypeCounts,
            'importRuns' => $importRuns,
            'filters' => [
                'severity' => $request->string('severity')->toString(),
                'issue_type' => $request->string('issue_type')->toString(),
                'import_run_id' => $request->string('import_run_id')->toString(),
            ],
        ]);
    }

    /**
     * Change program status.
     */
    public function updateProgramStatus(UpdateProgramStatusRequest $request, CareerProgram $program)
    {
        $oldStatus = $program->status;
        $program->status = $request->input('status');
        $program->save();

        // Invalidate worktree cache since status changed (could hide or show it)
        $program->invalidateWorktreeCache();

        return response()->json([
            'message' => 'Program status updated successfully.',
            'data' => $program,
        ]);
    }
}
