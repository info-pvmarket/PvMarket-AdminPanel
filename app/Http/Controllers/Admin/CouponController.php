<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::query();
        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . $request->search . '%');
        }
        $coupons = $query->orderBy('created_at', 'desc')
                 ->paginate(request('per_page', 12))
                 ->appends(request()->query());
        return view('admin.setup.coupons.coupons', [
            'mode'    => 'index',
            'coupons' => $coupons,
        ]);
    }

    public function create()
    {
        return view('admin.setup.coupons.coupons', ['mode' => 'create']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'          => 'required|string|max:50|unique:coupons,code',
            'type'          => 'required|in:free,discount',
            'plan_name'     => 'required|string|max:100',
            'products'      => 'required|integer|min:0',
            'warehouses'    => 'required|integer|min:0',
            'duration_days' => 'required|integer|min:1',
            'max_uses'      => 'required|integer|min:1',
            'valid_from'    => 'required|date',
            'valid_until'   => 'required|date|after:valid_from',
            'is_active'     => 'boolean',
        ]);

        Coupon::create([
            'code'          => strtoupper($request->code),
            'type'          => $request->type,
            'plan_name'     => $request->plan_name,
            'products'      => $request->products,
            'warehouses'    => $request->warehouses,
            'duration_days' => $request->duration_days,
            'max_uses'      => $request->max_uses,
            'current_uses'  => 0,
            'valid_from'    => $request->valid_from,
            'valid_until'   => $request->valid_until,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.setup.coupons.index')
                         ->with('success', 'Coupon created successfully.');
    }

    public function edit($id)
    {
        $record = Coupon::findOrFail($id);
        return view('admin.setup.coupons.coupons', [
            'mode'   => 'edit',
            'record' => $record,
        ]);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $request->validate([
            'code'          => 'required|string|max:50|unique:coupons,code,' . $id . ',_id',
            'type'          => 'required|in:free,discount',
            'plan_name'     => 'required|string|max:100',
            'products'      => 'required|integer|min:0',
            'warehouses'    => 'required|integer|min:0',
            'duration_days' => 'required|integer|min:1',
            'max_uses'      => 'required|integer|min:1',
            'valid_from'    => 'required|date',
            'valid_until'   => 'required|date|after:valid_from',
            'is_active'     => 'boolean',
        ]);

        $coupon->update([
            'code'          => strtoupper($request->code),
            'type'          => $request->type,
            'plan_name'     => $request->plan_name,
            'products'      => $request->products,
            'warehouses'    => $request->warehouses,
            'duration_days' => $request->duration_days,
            'max_uses'      => $request->max_uses,
            'valid_from'    => $request->valid_from,
            'valid_until'   => $request->valid_until,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.setup.coupons.index')
                         ->with('success', 'Coupon updated successfully.');
    }

    public function destroy($id)
    {
        Coupon::findOrFail($id)->delete();
        return redirect()->route('admin.setup.coupons.index')
                         ->with('success', 'Coupon deleted.');
    }
}
