<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SubAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = User::whereNotNull('role_id');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name',  'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $subAdmins = $query->with('role')->orderBy('created_at', 'desc')
                   ->paginate($request->get('entries', 10));
        return view('admin.setup.sub-admins.sub-admins', [
    'mode'      => 'index',
    'subAdmins' => $subAdmins,
    'roles'     => \App\Models\Role::orderBy('role')->get(),
]);
    }

    public function create()
    {
        return view('admin.setup.sub-admins.sub-admins', [
    'mode'   => 'create',
    'record' => null,
    'roles'  => \App\Models\Role::orderBy('role')->get(),
]);
    }

    public function store(Request $request)
    {
        $request->validate([
    'name'     => 'required|string|max:255',
    'email'    => 'required|email|unique:mongodb.users,email',
    'password' => 'required|string|min:8',
    'role_id'  => 'required|string',
]);

User::create([
    'name'      => $request->name,
    'email'     => $request->email,
    'password'  => Hash::make($request->password),
    'role_id'   => new \MongoDB\BSON\ObjectId($request->role_id),
    'is_active' => true,
]);

        return redirect()->route('admin.setup.sub-admins.index')
                         ->with('success', 'Sub Admin created successfully.');
    }

    public function edit($id)
    {
        $record = User::findOrFail($id);

        return view('admin.setup.sub-admins.sub-admins', [
    'mode'   => 'edit',
    'record' => $record->load('role'),
    'roles'  => \App\Models\Role::orderBy('role')->get(),
]);
    }

    public function update(Request $request, $id)
    {
        $record = User::where('role', 'sub_admin')->findOrFail($id);

        $request->validate([
    'name'     => 'required|string|max:255',
    'email'    => 'required|email|unique:mongodb.users,email,' . $id . ',_id',
    'password' => 'nullable|string|min:8',
    'role_id'  => 'required|string',
]);

$data = [
    'name'    => $request->name,
    'email'   => $request->email,
    'role_id' => new \MongoDB\BSON\ObjectId($request->role_id),
];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $record->update($data);

        return redirect()->route('admin.setup.sub-admins.index')
                         ->with('success', 'Sub Admin updated successfully.');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return redirect()->route('admin.setup.sub-admins.index')
                         ->with('success', 'Sub Admin deleted.');
    }
    public function export(Request $request)
{
    $query = User::where('role', 'sub_admin');

    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%')
              ->orWhere('email', 'like', '%' . $request->search . '%');
        });
    }

    $subAdmins = $query->orderBy('created_at', 'desc')->get();

    $filename = 'sub_admins_' . now()->format('Y_m_d_His') . '.csv';

    $headers = [
        'Content-Type'        => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        'Pragma'              => 'no-cache',
        'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
        'Expires'             => '0',
    ];

    $callback = function () use ($subAdmins) {
        $handle = fopen('php://output', 'w');

        fputcsv($handle, ['S.No', 'Name', 'Email', 'Created At']);

        foreach ($subAdmins as $index => $admin) {
            fputcsv($handle, [
                $index + 1,
                $admin->name,
                $admin->email,
                $admin->created_at?->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($handle);
    };

    return response()->stream($callback, 200, $headers);
}
}