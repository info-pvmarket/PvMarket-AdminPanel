<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\FiltersAssignedUsers;

class ScheduleController extends Controller
{
    use FiltersAssignedUsers;

    public function index(Request $request)
    {
        $query = Schedule::query();

        // Filter by assigned admin (sub-admins only see their assigned schedules)
        $this->filterByAssignedAdmin($query);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('event_title', 'like', '%' . $request->search . '%')
                  ->orWhere('requester_email', 'like', '%' . $request->search . '%')
                  ->orWhere('requester_name', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query->orderBy('created_at', 'desc')
                   ->paginate(request('entries', 10))
                   ->appends(request()->query());

        $admins = $this->getAdminsForAssignment();

        return view('admin.schedules.index', compact('schedules', 'admins'));
    }

    /**
     * Assign admin to schedule.
     */
    public function assignAdmin(Request $request, $scheduleId)
    {
        if (!Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Only Super Admin can assign schedules.');
        }

        $schedule = Schedule::findOrFail($scheduleId);
        $schedule->assigned_admin_id = $request->admin_id
            ? new \MongoDB\BSON\ObjectId($request->admin_id)
            : null;
        $schedule->save();

        return back()->with('success', 'Schedule assigned successfully.');
    }
}