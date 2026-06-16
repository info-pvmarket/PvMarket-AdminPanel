<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RfqRequest;
use Illuminate\Http\Request;

class RfqRequestController extends Controller
{
    /**
     * Display a listing of RFQ requests.
     */
    public function index(Request $request)
    {
        $query = RfqRequest::query();

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

        $perPage = $request->input('entries', 15);
        $records = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        return view('admin.rfq-requests.index', [
            'records' => $records,
        ]);
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
}
