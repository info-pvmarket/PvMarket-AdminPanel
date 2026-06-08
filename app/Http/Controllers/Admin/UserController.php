<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\TranslationService;

class UserController extends Controller
{
    public function __construct(protected TranslationService $translator) {}
    public function index(Request $request)
    {
        $query = User::where('role', '!=', 'super_admin')
                     ->where('role', '!=', 'sub_admin');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name',  'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        
        $users = $query->orderBy('created_at', 'desc')
                       ->paginate($request->get('entries', 10));

        return view('admin.users.index', compact('users'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function updateBasic(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:mongodb.users,email,' . $id . ',_id',
            'mobile' => 'nullable|string|max:30',
        ]);

        $data = [
            'name'   => $request->name,
            'email'  => $request->email,
            'mobile' => $request->mobile ?? null,
        ];

        $data = $this->attachTranslations($data, $user);
        $user->update($data);
        return redirect()->route('admin.users.edit', $id)
                         ->with('success', 'Basic details updated.')
                         ->with('active_tab', 'basic');
    }

    public function updateCompany(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'company_name' => 'nullable|string|max:255',
            'vat_id'       => 'nullable|string|max:100',
        ]);

        $user->update([
            'company_name'         => $request->company_name,
            'vat_id'               => $request->vat_id,
            'enable_editable'      => $request->boolean('enable_editable'),
            'allow_document_upload'=> $request->boolean('allow_document_upload'),
            'company_verified'     => $request->boolean('company_verified'),
            'show_verified_batch'  => $request->boolean('show_verified_batch'),
        ]);

        return redirect()->route('admin.users.edit', $id)
                         ->with('success', 'Company details updated.')
                         ->with('active_tab', 'company');
    }

    public function toggleCompanyVerified($id)
    {
        $user = User::findOrFail($id);
        $user->update(['company_verified' => !($user->company_verified ?? false)]);

        return redirect()->route('admin.users.edit', $id)
                         ->with('success', 'Company verification status updated.')
                         ->with('active_tab', session('active_tab', 'basic'));
    }

    public function export(Request $request)
{
    $query = User::query();
 
    // ── Apply the same filters as index ──────────────────────
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('name',  'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }
 
    
 
    $users = $query->orderBy('created_at', 'desc')->get();
 
    // ── Build CSV in memory ───────────────────────────────────
    $filename = 'users_export_' . now()->format('Y-m-d_His') . '.csv';
 
    $headers = [
        'Content-Type'        => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        'Pragma'              => 'no-cache',
        'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
        'Expires'             => '0',
    ];
 
    $callback = function () use ($users) {
        $handle = fopen('php://output', 'w');
 
        // UTF-8 BOM so Excel opens it correctly
        fputs($handle, "\xEF\xBB\xBF");
 
        // ── Column headers ────────────────────────────────────
        fputcsv($handle, [
    'S.No', 'Name', 'Email', 'Mobile', 'Active', 'On Hold', 'Email Verified', 'Registered At',
]);

foreach ($users as $i => $user) {
    fputcsv($handle, [
        $i + 1,
        $user->name,
        $user->email,
        $user->mobile ?? '',
        ($user->is_active ?? false) ? 'Yes' : 'No',
        ($user->is_hold ?? false)   ? 'Yes' : 'No',
        ($user->email_verified ?? false) ? 'Yes' : 'No',
        optional($user->created_at)->format('Y-m-d H:i'),
    ]);
}
 
        fclose($handle);
    };
 
    return response()->stream($callback, 200, $headers);
}
private function attachTranslations(array $data, $modelInstance): array
    {
        $languages    = array_keys(config('languages.available'));
        $translatable = $modelInstance->translatable ?? [];

        foreach ($languages as $locale) {
            if ($locale === 'en') continue;

            $translated = [];
            foreach ($translatable as $field) {
                if (!empty($data[$field])) {
                    $translated[$field] = $this->translator->translateText(
                        $data[$field], $locale, 'en'
                    );
                }
            }

            if (!empty($translated)) {
                $data[$locale] = $translated;
            }
        }

        return $data;
    }
}