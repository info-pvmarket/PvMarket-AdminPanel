<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RfqRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\FiltersAssignedUsers;

class RfqRequestController extends Controller
{
    use FiltersAssignedUsers;

    /**
     * Display a listing of RFQ requests.
     */
    public function index(Request $request)
    {
        $records = $this->rfqQuery($request)->paginate($request->input('entries', 15))->withQueryString();

        return view('admin.rfq-requests.index', [
            'records' => $records,
            'admins' => $this->getAdminsForAssignment(),
        ]);
    }

    private function rfqQuery(Request $request)
    {
        $query = RfqRequest::query();

        // Filter by assigned admin
        $this->filterByAssignedAdmin($query);

        // Search by RFQ number, company name, contact person, or email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('rfq_no', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('business_email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->orderBy('_id', 'desc');
    }

    public function export(Request $request)
    {
        $records = $this->rfqQuery($request)->with('assignedAdmin');

        return response()->streamDownload(function () use ($records) {
            $output = fopen('php://output', 'w');
            try {
                fwrite($output, "\xEF\xBB\xBF");
                fputcsv($output, [
                    'RFQ No', 'Company', 'Contact Person', 'Business Email', 'Phone', 'Items',
                    'Country of Delivery', 'Destination', 'Delivery Date', 'Quote Needed By',
                    'Status', 'Assigned Admin', 'Created Date',
                ], escape: '');

                foreach ($records->lazy() as $record) {
                    $row = [
                        $record->rfq_no,
                        $record->company_name,
                        $record->contact_person,
                        $record->business_email,
                        $record->phone,
                        $record->total_items ?? count($record->items ?? []),
                        $record->country_of_delivery,
                        $record->destination,
                        $record->delivery_date?->format('Y-m-d'),
                        $record->quote_needed_by?->format('Y-m-d'),
                        $record->status,
                        $record->assignedAdmin?->name,
                        $record->created_at?->format('Y-m-d'),
                    ];

                    fputcsv($output, array_map($this->csvCell(...), $row), escape: '');
                }
            } finally {
                fclose($output);
            }
        }, 'rfq-requests.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }

    private function csvCell($value): string
    {
        $value = (string) $value;

        // RFQ submissions can contain text that spreadsheet apps interpret as a formula.
        return preg_match('/^(?:\s*[=+@-]|[\t\r\n])/', $value) ? "'".$value : $value;
    }

    /**
     * Display the specified RFQ request.
     */
    public function show(string $id)
    {
        $record = RfqRequest::findOrFail($id);

        return view('admin.rfq-requests.show', [
            'record' => $record,
        ]);
    }

    /**
     * Update the status of an RFQ request.
     */
    public function updateStatus(Request $request, string $id)
    {
        $record = RfqRequest::findOrFail($id);

        $request->validate([
            'status' => ['required', 'string', 'in:pending,reviewed,quoted,accepted,rejected,expired'],
        ]);

        $record->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'RFQ status updated successfully.');
    }

    /**
     * Remove the specified RFQ request.
     */
    public function destroy(string $id)
    {
        $record = RfqRequest::findOrFail($id);
        $record->delete();

        return redirect()
            ->route('admin.rfq-requests.index')
            ->with('success', 'RFQ request deleted successfully.');
    }

    /**
     * Assign admin to RFQ request.
     */
    public function assignAdmin(Request $request, $rfqId)
    {
        if (!Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Only Super Admin can assign.');
        }

        $rfq = RfqRequest::findOrFail($rfqId);
        $rfq->assigned_admin_id = $request->admin_id
            ? new \MongoDB\BSON\ObjectId($request->admin_id)
            : null;
        $rfq->save();

        return back()->with('success', 'Assigned successfully.');
    }
}
