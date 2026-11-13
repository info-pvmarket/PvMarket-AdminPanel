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
        $query = $this->filterByAssignedAdmin(BidRequest::visible());

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
            if ($request->status === '0') {
                $query->pending();
            } else {
                $query->where('status', (int) $request->status);
            }
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

    private const IMPORT_COLUMNS = [
        'request_id', 'request_type', 'product_name', 'company_name',
        'quantity', 'currency', 'price_per_piece', 'lead_time_weeks',
    ];

    public function importTemplate()
    {
        return response()->streamDownload(function () {
            $output = fopen('php://output', 'w');
            fputcsv($output, self::IMPORT_COLUMNS, escape: '');
            fclose($output);
        }, 'bid-fair-import-template.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request)
    {
        $request->validate(['bid_csv' => ['required', 'file', 'extensions:csv', 'max:1024']]);

        $file = $request->file('bid_csv')->openFile();
        $file->setCsvControl(',', '"', '');
        $header = $file->fgetcsv();
        if ($header) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0] ?? '');
        }
        if ($header !== self::IMPORT_COLUMNS) {
            return back()->withErrors(['bid_csv' => 'Use the columns in the CSV template.']);
        }

        $rows = [];
        $record = 1;
        while (!$file->eof()) {
            $values = $file->fgetcsv();
            $record++;
            if ($values === false || $values === [null]) {
                continue;
            }
            if (count($values) !== count(self::IMPORT_COLUMNS) || count($rows) >= 500) {
                return back()->withErrors(['bid_csv' => "Record {$record}: invalid column count or more than 500 requests."]);
            }
            $row = array_combine(self::IMPORT_COLUMNS, array_map('trim', $values));
            $validator = validator($row, [
                'request_id' => ['required', 'regex:/^[A-Za-z0-9_-]{1,64}$/'],
                'request_type' => ['required', 'in:bid request,fair request'],
                'product_name' => ['required', 'string', 'max:255'],
                'company_name' => ['required', 'string', 'max:255'],
                'quantity' => ['required', 'integer', 'min:1', 'max:2147483647'],
                'currency' => ['required', 'regex:/^[A-Z]{3}$/'],
                'price_per_piece' => ['required', 'regex:/^\d{1,10}(\.\d{1,6})?$/', 'numeric', 'gt:0'],
                'lead_time_weeks' => ['required', 'integer', 'min:1', 'max:2147483647'],
            ]);
            if ($validator->fails()) {
                return back()->withErrors(['bid_csv' => "Record {$record}: ".$validator->errors()->first()]);
            }

            $id = $row['request_id'];
            if (isset($rows[$id])) {
                return back()->withErrors(['bid_csv' => "Record {$record}: duplicate request ID."]);
            }
            $rows[$id] = $row;
        }

        if (!$rows) {
            return back()->withErrors(['bid_csv' => 'The CSV contains no requests.']);
        }

        $assignedAdmin = new \MongoDB\BSON\ObjectId((string) Auth::id());
        $timestamp = new \MongoDB\BSON\UTCDateTime(now());
        $created = 0;
        foreach ($rows as $row) {
            $id = $row['request_id'];
            if (BidRequest::where('unique_id', $id)->exists()) {
                continue;
            }
            // A stable ID makes concurrent uploads and retries insert each request once.
            $objectId = new \MongoDB\BSON\ObjectId(substr(hash('sha256', 'bid-import:'.$id), 0, 24));
            $price = (float) $row['price_per_piece'];
            $result = BidRequest::raw(fn ($collection) => $collection->updateOne(['_id' => $objectId], ['$setOnInsert' => [
                'unique_id' => $id,
                'request_type' => $row['request_type'],
                'product_name' => $row['product_name'],
                'company_name' => $row['company_name'],
                'selected_pcs_qty' => (int) $row['quantity'],
                'quantity_unit' => 'pieces',
                'purchased_currency' => $row['currency'],
                'bid_price_per_piece' => $price,
                'bid_price' => $price,
                'lead_time' => (int) $row['lead_time_weeks'],
                'status' => 0,
                'is_active' => 1,
                'assigned_admin_id' => $assignedAdmin,
                'created_by' => $assignedAdmin,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]], ['upsert' => true]));
            $created += $result->getUpsertedCount();
        }

        $skipped = count($rows) - $created;
        return redirect()->route('admin.bids.index', ['status' => '0'])
            ->with('success', "Imported {$created} pending requests. Skipped {$skipped} existing request IDs.");
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
        $oldStatus = (int) $bid->status;
        $bid->update([
            'status'       => $request->status,
            'updated_by'   => auth()->id(),
            'completed_at' => $request->status == 3 ? now() : $bid->completed_at,
        ]);
        if ((int) $request->status !== $oldStatus) {
            app(\App\Services\UserNotifier::class)->bidStatusChanged($bid);
        }

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