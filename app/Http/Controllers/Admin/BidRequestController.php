<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BidRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\FiltersAssignedUsers;

class BidRequestController extends Controller
{
    use FiltersAssignedUsers;

    // ── Index — list all bid/fair requests ────────────────────────
    public function index(Request $request)
    {
        $query = BidRequest::where('is_active', 1);

        // Filter by assigned admin
        $this->filterByAssignedAdmin($query);

        // Search by product name or request id
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('product_name', 'like', '%' . $s . '%')
                  ->orWhere('unique_id',   'like', '%' . $s . '%')
                  ->orWhere('company_name','like', '%' . $s . '%');
            });
        }

        // Filter by request type
        if ($request->filled('request_type') && $request->request_type !== 'all') {
            $query->where('request_type', $request->request_type);
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', (int)$request->status);
        }

        $perPage = in_array((int)$request->get('per_page', 10), [10, 25, 50, 100])
    ? (int)$request->get('per_page', 10)
    : 10;

$bids = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        $bidsJson = $bids->getCollection()->map(function ($b) {
    return [
        'id'                  => (string)$b->id,
        'unique_id'           => $b->unique_id ?? ('Req' . str_pad($b->id, 5, '0', STR_PAD_LEFT)),
        'product_name'        => $b->product_name ?? '-',
        'company_name'        => $b->company_name ?? '-',
        'selected_pcs_qty'    => $b->selected_pcs_qty ?? '-',
        'quantity_unit'       => $b->quantity_unit ?? '',
        'bid_price_per_piece' => $b->bid_price_per_piece ?? '-',
        'final_price_per_pcs' => $b->final_price_per_pcs ?? '-',
        'request_type'        => $b->request_type ?? 'bid request',
        'purchased_currency'  => $b->purchased_currency ?? 'USD',
        'lead_time'           => $b->lead_time ?? '-',
        'status'              => $b->status ?? 0,
    ];
})->values();

$admins = $this->getAdminsForAssignment();

        return view('admin.bids.index', compact('bids', 'bidsJson', 'admins'));
    }

    // ── Show single bid detail ────────────────────────────────────
    public function show($id)
    {
        $bid     = BidRequest::findOrFail($id);
        $product = $bid->product_id ? Product::find($bid->product_id) : null;

        return view('admin.bids.show', compact('bid', 'product'));
    }

    // ── Update status ─────────────────────────────────────────────
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|integer|min:0|max:3']);

        $bid = BidRequest::findOrFail($id);
        $bid->update([
            'status'       => $request->status,
            'updated_by'   => auth()->id(),
            'completed_at' => $request->status == 3 ? now() : $bid->completed_at,
        ]);

        return response()->json(['success' => true, 'status' => $request->status]);
    }

    // ── Soft delete ───────────────────────────────────────────────
    public function destroy($id)
    {
        $bid = BidRequest::findOrFail($id);
        $bid->update(['is_active' => 0]);
        return response()->json(['success' => true]);
    }

    // ── Assign Admin ─────────────────────────────────────────────
    public function assignAdmin(Request $request, $bidId)
    {
        if (!Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Only Super Admin can assign.');
        }

        $bid = BidRequest::findOrFail($bidId);
        $bid->assigned_admin_id = $request->admin_id
            ? new \MongoDB\BSON\ObjectId($request->admin_id)
            : null;
        $bid->save();

        return back()->with('success', 'Assigned successfully.');
    }
}