<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadGeneration;
use App\Models\ProductVisit;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\FiltersAssignedUsers;

class LeadController extends Controller
{
    use FiltersAssignedUsers;

    // ── Leads Management Page ─────────────────────────────────────
    public function index(Request $request)
    {
        $query = LeadGeneration::where('is_active', '!=', '0');

        // Filter by assigned admin
        $this->filterByAssignedAdmin($query);

        // Filter by lead type (All / Book Free / Spot Price / etc.)
        if ($request->filled('lead_type') && $request->lead_type !== 'all') {
            $query->where('lead_type', (int)$request->lead_type);
        }

        // Search by email or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', '%' . $search . '%')
                  ->orWhere('name',  'like', '%' . $search . '%');
            });
        }

        $perPage = (int) $request->get('per_page', 10);
        $leads   = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        $admins = $this->getAdminsForAssignment();

        return view('admin.leads.index', compact('leads', 'admins'));
    }

    // ── Update lead status ────────────────────────────────────────
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|integer|min:0|max:2']);

        $lead = LeadGeneration::findOrFail($id);
        $lead->update([
            'status'     => $request->status,
            'updated_by' => auth()->id(),
        ]);

        return response()->json(['success' => true]);
    }

    // ── Edit lead (show form) ─────────────────────────────────────
    public function edit($id)
    {
        $lead = LeadGeneration::findOrFail($id);
        return view('admin.leads.edit', compact('lead'));
    }

    // ── Update lead ───────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'   => 'nullable|string|max:255',
            'email'  => 'nullable|email|max:255',
            'phone'  => 'nullable|string|max:20',
            'status' => 'nullable|integer',
        ]);

        $lead = LeadGeneration::findOrFail($id);
        $lead->update([
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'status'     => $request->status,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.leads.index')->with('success', 'Lead updated successfully.');
    }

    // ── Delete lead ───────────────────────────────────────────────
    public function destroy($id)
    {
        $lead = LeadGeneration::findOrFail($id);
        $lead->update(['is_active' => '0']);
        return back()->with('success', 'Lead removed.');
    }

    // ── Assign Admin ─────────────────────────────────────────────
    public function assignAdmin(Request $request, $leadId)
    {
        if (!Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Only Super Admin can assign.');
        }

        $lead = LeadGeneration::findOrFail($leadId);
        $lead->assigned_admin_id = $request->admin_id
            ? new \MongoDB\BSON\ObjectId($request->admin_id)
            : null;
        $lead->save();

        return back()->with('success', 'Assigned successfully.');
    }

    // ══════════════════════════════════════════════════════════════
    // PRODUCT VISITS PAGE
    // ══════════════════════════════════════════════════════════════

    public function productVisits(Request $request)
    {
        $query = ProductVisit::where('is_active', 1);

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('visit_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('visit_date', '<=', $request->date_to);
        }

        $visits = $query->orderBy('no_of_times', 'desc')->get();

        // Load user and product info
        $userIds    = $visits->pluck('user_id')->filter()->unique()->values()
                ->map(fn($id) => new \MongoDB\BSON\ObjectId((string)$id))->toArray();
$productIds = $visits->pluck('product_id')->filter()->unique()->values()
                ->map(fn($id) => new \MongoDB\BSON\ObjectId((string)$id))->toArray();

$users    = User::whereIn('_id', $userIds)->get()->keyBy(fn($u) => (string)$u->_id);
$products = Product::whereIn('_id', $productIds)->get()->keyBy(fn($p) => (string)$p->_id);

        // Search filter applied after loading
        $search = $request->search;
        $visits = $visits->map(function ($visit) use ($users, $products) {
            $visit->user_info    = $users[(string)$visit->user_id]    ?? null;
            $visit->product_info = $products[(string)$visit->product_id] ?? null;
            return $visit;
        });

        if ($search) {
            $visits = $visits->filter(function ($visit) use ($search) {
                $name  = strtolower($visit->user_info?->name ?? '');
                $email = strtolower($visit->user_info?->email ?? '');
                return str_contains($name, strtolower($search)) || str_contains($email, strtolower($search));
            })->values();
        }

        // Load current visit timer setting
        $perPage     = (int) $request->get('per_page', 10);
$currentPage = (int) $request->get('page', 1);
$total       = $visits->count();
$visits      = new \Illuminate\Pagination\LengthAwarePaginator(
    $visits->forPage($currentPage, $perPage),
    $total,
    $perPage,
    $currentPage,
    ['path' => $request->url(), 'query' => $request->query()]
);

$visitTimerSeconds = (int) \App\Models\Setting::getValue('visit_timer_seconds', 30);

return view('admin.leads.product-visits', compact('visits', 'visitTimerSeconds'));
    }

    // ── Delete product visit ──────────────────────────────────────
    public function destroyVisit($id)
    {
        $visit = ProductVisit::findOrFail($id);
        $visit->update(['is_active' => 0]);
        return response()->json(['success' => true]);
    }

    // ── Export product visits as CSV ──────────────────────────────
    public function exportVisits(Request $request)
    {
        $query = ProductVisit::where('is_active', 1);

        if ($request->filled('date_from')) {
            $query->where('visit_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('visit_date', '<=', $request->date_to);
        }

        $visits     = $query->get();
        $userIds    = $visits->pluck('user_id')->filter()->unique()
                ->map(fn($id) => new \MongoDB\BSON\ObjectId((string)$id))->toArray();
$productIds = $visits->pluck('product_id')->filter()->unique()
                ->map(fn($id) => new \MongoDB\BSON\ObjectId((string)$id))->toArray();
$users      = User::whereIn('_id', $userIds)->get()->keyBy(fn($u) => (string)$u->_id);
$products   = Product::whereIn('_id', $productIds)->get()->keyBy(fn($p) => (string)$p->_id);

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_visits.csv"',
        ];

        $callback = function () use ($visits, $users, $products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['S.No', 'Name', 'Email', 'Mobile', 'Product', 'Visits', 'Visit Date']);

            $i = 1;
            foreach ($visits as $visit) {
                $user    = $users[(string)$visit->user_id]    ?? null;
                $product = $products[(string)$visit->product_id] ?? null;
                fputcsv($handle, [
                    $i++,
                    $user?->name    ?? '-',
                    $user?->email   ?? '-',
                    $user?->phone   ?? '-',
                    $product?->product_name ?? '-',
                    $visit->no_of_times ?? 0,
                    $visit->visit_date  ?? '-',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Save visit timer setting ──────────────────────────────────
    public function saveVisitTimer(Request $request)
    {
        $request->validate([
            'visit_timer_seconds' => 'required|integer|min:1|max:86400',
        ]);

        \App\Models\Setting::setValue('visit_timer_seconds', $request->visit_timer_seconds);

        return response()->json(['success' => true]);
    }
}