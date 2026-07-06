<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectApprovalLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MongoDB\BSON\ObjectId;

class ProjectApprovalController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'pending'); // all | pending | approved | rejected
        $search = $request->get('search', '');

        $query = Project::query()->with(['submitter']);

        if ($filter !== 'all') {
            $query->where('status', $filter);
        }

        if ($search) {
            $query->where('project_name', 'like', "%{$search}%");
        }

        $projects = $query->orderByDesc('created_at')->get()->map(function ($p) {
            $p->submitter_name = $p->submitter->name ?? $p->customer_name ?? 'N/A';
            return $p;
        });

        $totalCount    = Project::count();
        $pendingCount  = Project::status('pending')->count();
        $approvedCount = Project::status('approved')->count();
        $rejectedCount = Project::status('rejected')->count();

        if ($request->ajax()) {
            $payload = $projects->values()->map(fn($p) => [
                '_id'             => (string) $p->_id,
                'project_name'    => $p->project_name,
                'project_type'    => $p->project_type,
                'capacity_kw'     => $p->capacity_kw,
                'submitter_name'  => $p->submitter_name,
                'status'          => $p->status,
                'status_label'    => $p->status_label,
                'created_at'      => $p->created_at?->format('d M Y, H:i'),
            ]);
            return response()->json(['projects' => $payload]);
        }

        $perPage   = 15;
        $page      = $request->get('page', 1);
        $allItems  = $projects->values();
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $allItems->forPage($page, $perPage),
            $allItems->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.project-approvals.index', compact(
            'filter', 'search', 'totalCount', 'pendingCount', 'approvedCount', 'rejectedCount'
        ) + ['projects' => $paginated]);
    }

    // ── Approve ───────────────────────────────────────────────────
    public function approve(Request $request, string $projectId)
    {
        $request->validate(['notes' => 'nullable|string|max:500']);

        $project = Project::findOrFail($projectId);
        $project->status        = 'approved';
        $project->reviewed_by   = new ObjectId(Auth::id());
        $project->reviewed_at   = now();
        $project->review_notes  = $request->notes;
        $project->save();

        ProjectApprovalLog::create([
            'project_id'    => new ObjectId($projectId),
            'action'        => 'approved',
            'performed_by'  => new ObjectId(Auth::id()),
            'notes'         => $request->notes,
        ]);

        return response()->json(['success' => true, 'message' => 'Project approved.']);
    }

    // ── Reject ────────────────────────────────────────────────────
    public function reject(Request $request, string $projectId)
    {
        $request->validate(['notes' => 'required|string|max:500']);

        $project = Project::findOrFail($projectId);
        $project->status        = 'rejected';
        $project->reviewed_by   = new ObjectId(Auth::id());
        $project->reviewed_at   = now();
        $project->review_notes  = $request->notes;
        $project->save();

        ProjectApprovalLog::create([
            'project_id'    => new ObjectId($projectId),
            'action'        => 'rejected',
            'performed_by'  => new ObjectId(Auth::id()),
            'notes'         => $request->notes,
        ]);

        return response()->json(['success' => true, 'message' => 'Project rejected.']);
    }

    // ── History ───────────────────────────────────────────────────
    // ── View Details ──────────────────────────────────────────────
    public function show(string $projectId)
    {
        $project = Project::with(['submitter', 'reviewer'])->findOrFail($projectId);

        $logs = ProjectApprovalLog::forProject($projectId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($log) => [
                'action'     => $log->action_label,
                'notes'      => $log->notes,
                'created_at' => $log->created_at?->format('d M Y, H:i'),
            ]);

        return response()->json([
            'success' => true,
            'project' => [
                '_id'            => (string) $project->_id,
                'project_name'   => $project->project_name,
                'customer_name'  => $project->customer_name,
                'project_type'   => $project->project_type,
                'capacity_kw'    => $project->capacity_kw,
                'location'       => $project->location,
                'description'    => $project->description,
                'status'         => $project->status,
                'status_label'   => $project->status_label,
                'submitted_by'   => $project->submitter->name ?? $project->customer_name ?? 'N/A',
                'submitted_at'   => $project->created_at?->format('d M Y, H:i'),
                'reviewed_by'    => $project->reviewer->name ?? null,
                'reviewed_at'    => $project->reviewed_at?->format('d M Y, H:i'),
                'review_notes'   => $project->review_notes,
            ],
            'logs' => $logs,
        ]);
    }
}