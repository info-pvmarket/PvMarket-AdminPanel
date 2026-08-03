<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\MainMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Commission::query();

        if ($request->filled('search')) {
            $query->where('category_name', 'like', '%' . $request->search . '%');
        }

        $commissions = $query->orderBy('created_at', 'desc')
                             ->paginate($request->get('entries', 10));

        return view('admin.setup.commissions.commissions', [
            'mode'        => 'index',
            'commissions' => $commissions,
        ]);
    }

    public function create()
    {
        $categories = MainMenu::availableForDropdown()
            ->orderBy('category_name')
            ->get(['_id', 'category_name']);

        return view('admin.setup.commissions.commissions', [
            'mode'       => 'create',
            'record'     => null,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items'                         => 'required|array|min:1',
            'items.*.category_id'           => 'required|string',
            'items.*.commission_percentage' => 'required|numeric|min:0|max:100',
        ]);

        foreach ($request->items as $item) {
            $category = MainMenu::find($item['category_id']);

            Commission::create([
                'category_id'             => $item['category_id'],
                'category_name'           => $category?->category_name ?? '',
                'commission_percentage'   => $item['commission_percentage'],
                'slug'                    => Str::slug($category?->category_name ?? $item['category_id']),
                'created_by'              => auth()->id(),
            ]);
        }

        return redirect()->route('admin.setup.commissions.index')
                         ->with('success', count($request->items) . ' commission(s) created successfully.');
    }

    public function edit($id)
    {
        $record     = Commission::findOrFail($id);
        $categories = MainMenu::availableForDropdown()
            ->orderBy('category_name')
            ->get(['_id', 'category_name']);

        return view('admin.setup.commissions.commissions', [
            'mode'       => 'edit',
            'record'     => $record,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id'           => 'required|string',
            'commission_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $commission = Commission::findOrFail($id);
        $category   = MainMenu::find($request->category_id);

        $commission->update([
            'category_id'           => $request->category_id,
            'category_name'         => $category?->category_name ?? '',
            'commission_percentage' => $request->commission_percentage,
            'slug'                  => Str::slug($category?->category_name ?? $request->category_id),
            'updated_by'            => auth()->id(),
        ]);

        return redirect()->route('admin.setup.commissions.index')
                         ->with('success', 'Commission updated successfully.');
    }

    public function destroy($id)
    {
        Commission::findOrFail($id)->delete();

        return redirect()->route('admin.setup.commissions.index')
                         ->with('success', 'Commission deleted.');
    }
}
