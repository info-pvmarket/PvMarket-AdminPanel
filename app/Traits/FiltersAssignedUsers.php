<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use MongoDB\BSON\ObjectId;
use App\Models\User;

trait FiltersAssignedUsers
{
    /**
     * Filter by users assigned to current admin
     * For: Products (created_by), Listings (user_id), Inventory, Warehouses
     */
    protected function filterByAssignedUsers($query, string $userField = 'user_id')
    {
        $admin = Auth::user();

        

        if ($admin && $admin->isSuperAdmin()) {
            return $query;
        }
        // dd($admin);

        $assignedUserIds = $admin ? $admin->getAssignedUserIds() : [];

        // Filter out empty values
        $assignedUserIds = array_filter($assignedUserIds, fn($id) => !empty($id));

        if (empty($assignedUserIds)) {
            return $query->whereRaw(['_id' => ['$exists' => false]]);
        }

        $objectIds = array_map(fn($id) => new ObjectId($id), $assignedUserIds);
        return $query->whereIn($userField, $objectIds);
    }

    /**
     * Filter by assigned_admin_id field
     * For: RFQs, Bids, Leads
     */
    protected function filterByAssignedAdmin($query)
    {
        $admin = Auth::user();

        if ($admin && $admin->isSuperAdmin()) {
            return $query;
        }

        return $query->where('assigned_admin_id', new ObjectId($admin->_id));
    }

    /**
     * Get list of admins for assignment dropdown
     */
    protected function getAdminsForAssignment()
    {
        $roleIds = \App\Models\Role::whereIn('slug', ['super-admin', 'admin'])->pluck('id');
        $roleObjectIds = $roleIds->map(fn($id) => new ObjectId((string) $id))->toArray();
        return User::whereIn('role_id', $roleObjectIds)->with('role')->get();
    }
}
