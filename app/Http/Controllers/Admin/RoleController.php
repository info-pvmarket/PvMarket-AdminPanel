<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    // ──────────────────────────────────────────
    //  INDEX
    // ──────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Role::query();

        if ($search = $request->input('search')) {
            $query->where('role', 'like', "%{$search}%");
        }

        $records = $query->latest()->paginate(15)->withQueryString();

        return view('admin.setup.roles.roles', [
            'mode'    => 'index',
            'records' => $records,
        ]);
    }

    // ──────────────────────────────────────────
    //  CREATE
    // ──────────────────────────────────────────
    public function create()
    {
        return view('admin.setup.roles.roles', [
            'mode' => 'create',
            'adminPermissions' => Role::getAdminPermissions(),
            'groupedPermissions' => Role::getGroupedAdminPermissions(),
        ]);
    }

    // ──────────────────────────────────────────
    //  STORE  (bulk — table-form submits rows[])
    // ──────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'roles'                      => ['required', 'array', 'min:1'],
            'roles.*.role'               => ['required', 'string', 'max:100'],
            'roles.*.slug'               => ['required', 'string', 'max:100'],
            'roles.*.guard_name'         => ['required', 'string', 'in:web,api,admin'],
            'roles.*.access_types'       => ['nullable', 'array'],
            'roles.*.access_types.*'     => ['string', 'in:buyer,seller,projects'],
            'roles.*.can_access_admin'   => ['nullable', 'boolean'],
            'roles.*.admin_permissions'  => ['nullable', 'array'],
            'roles.*.admin_permissions.*'=> ['string'],
        ]);

        foreach ($request->input('roles') as $row) {
            Role::create([
                'role'              => trim($row['role']),
                'slug'              => Str::slug($row['slug']),
                'guard_name'        => $row['guard_name'],
                'access_types'      => $row['access_types'] ?? [],
                'can_access_admin'  => !empty($row['can_access_admin']),
                'admin_permissions' => $row['admin_permissions'] ?? [],
            ]);
        }

        return redirect()
            ->route('admin.setup.roles.index')
            ->with('success', 'Role(s) created successfully.');
    }

    // ──────────────────────────────────────────
    //  EDIT
    // ──────────────────────────────────────────
    public function edit(string $id)
    {
        $record = Role::findOrFail($id);

        return view('admin.setup.roles.roles', [
            'mode'   => 'edit',
            'record' => $record,
            'adminPermissions' => Role::getAdminPermissions(),
            'groupedPermissions' => Role::getGroupedAdminPermissions(),
        ]);
    }

    // ──────────────────────────────────────────
    //  UPDATE
    // ──────────────────────────────────────────
    public function update(Request $request, string $id)
    {
        $record = Role::findOrFail($id);

        $request->validate([
            'role'               => ['required', 'string', 'max:100'],
            'slug'               => ['required', 'string', 'max:100'],
            'guard_name'         => ['required', 'string', 'in:web,api,admin'],
            'access_types'       => ['nullable', 'array'],
            'access_types.*'     => ['string', 'in:buyer,seller,projects'],
            'can_access_admin'   => ['nullable', 'boolean'],
            'admin_permissions'  => ['nullable', 'array'],
            'admin_permissions.*'=> ['string'],
        ]);

        $record->update([
            'role'              => trim($request->role),
            'slug'              => Str::slug($request->slug),
            'guard_name'        => $request->guard_name,
            'access_types'      => $request->access_types ?? [],
            'can_access_admin'  => $request->boolean('can_access_admin'),
            'admin_permissions' => $request->admin_permissions ?? [],
        ]);

        return redirect()
            ->route('admin.setup.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    // ──────────────────────────────────────────
    //  TOGGLE  (is_active)
    // ──────────────────────────────────────────
    public function toggle(string $id)
    {
        $record = Role::findOrFail($id);

        $record->update([
            'is_active' => ! ($record->is_active ?? true),
        ]);

        return back()->with('success', 'Role status updated.');
    }

    // ──────────────────────────────────────────
    //  DESTROY
    // ──────────────────────────────────────────
    public function destroy(string $id)
    {
        $record = Role::findOrFail($id);
        $record->delete();

        return redirect()
            ->route('admin.setup.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}