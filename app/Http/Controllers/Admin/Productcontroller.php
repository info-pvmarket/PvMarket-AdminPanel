<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\ProductDetailOption;
use App\Models\MainMenu;
use App\Models\SubMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // ── SKU Generator ─────────────────────────────────
    private function generateSku(string $categoryName): string
    {
        // Remove spaces, take first 3 letters of each word, uppercase
        $words  = preg_split('/\s+/', trim($categoryName));
        $prefix = '';
        foreach ($words as $word) {
            $prefix .= strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $word), 0, 1));
        }
        // Pad or trim to max 3 chars
        $prefix    = substr($prefix, 0, 3);
        $timestamp = now()->format('YmdHis');
        $random    = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

        return "{$prefix}-{$timestamp}-{$random}";
    }

    // ── Shared dropdown data ──────────────────────────
    private function getDropdowns(?string $subCategoryId = null): array
    {
        $brands      = Brand::where('is_active', true)->orderBy('name')->get();
        $mainMenus   = MainMenu::orderBy('name')->get();
        $subMenus    = SubMenu::orderBy('name')->get();

        if ($subCategoryId) {
            $options = ProductDetailOption::where('sub_menu_id', $subCategoryId)
                                          ->orderBy('option_name')
                                          ->get();
        } else {
            $options = collect();
        }

        return compact('brands', 'mainMenus', 'subMenus', 'options');
    }

    // ── Index ─────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('product_name', 'like', "%{$s}%")
                  ->orWhere('brand_name',  'like', "%{$s}%")
                  ->orWhere('sku_code',    'like', "%{$s}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')
                          ->paginate($request->get('entries', 10));

        return view('admin.products.products', [
            'mode'     => 'index',
            'products' => $products,
        ]);
    }

    // ── Create ────────────────────────────────────────
    public function create()
    {
        return view('admin.products.products', array_merge(
            ['mode' => 'create', 'record' => null],
            $this->getDropdowns()
        ));
    }

    // ── Store ─────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'product_name'   => 'required|string|max:500',
            'category_id'    => 'required|string',
            'sub_category_id'=> 'required|string',
            'brand_id'       => 'nullable|string',
            'pieces_per_pallet'    => 'nullable|string|max:100',
            'pallets_per_container'=> 'nullable|string|max:100',
            'product_description'  => 'nullable|string',
        ]);

        // Resolve display names
        $category    = MainMenu::find($request->category_id);
        $subCategory = SubMenu::find($request->sub_category_id);

        $data = [
            'product_name'          => $request->product_name,
            'product_description'   => $request->product_description,
            'category_id'           => $request->category_id,
            'category_name'         => $category?->name,
            'sub_category_id'       => $request->sub_category_id,
            'sub_category_name'     => $subCategory?->name,
            'brand_id'              => $request->brand_id,
            'pieces_per_pallet'     => $request->pieces_per_pallet,
            'pallets_per_container' => $request->pallets_per_container,
            'is_popular'            => $request->boolean('is_popular'),
            'real_time_price'       => $request->boolean('real_time_price'),
            'verification_status'   => Auth::user()->role === 'super_admin' ? 'verified' : 'pending',
            'updated_by'            => Auth::user()->name,
        ];

        // Generate SKU using category name
        $data['sku_code'] = $this->generateSku($category?->name ?? 'PRD');

        if ($request->brand_id) {
            $brand = Brand::find($request->brand_id);
            $data['brand_name'] = $brand?->name;
        }

        if ($request->hasFile('datasheet')) {
    $file = $request->file('datasheet');
    $path = $file->store('products/datasheets', 'public');
    $data['datasheet'] = (object) [   // ← wrap with (object)
        'filename'      => basename($path),
        'original_name' => $file->getClientOriginalName(),
        'path'          => $path,
        'url'           => Storage::disk('public')->url($path),
        'mime_type'     => $file->getClientMimeType(),
        'size'          => $file->getSize(),
        'uploaded_at'   => now()->toISOString(),
    ];
}

        // ── product_details: [{label, value, unit}] ──
        if ($request->has('product_details')) {
            $details = [];
            foreach ($request->product_details as $row) {
                if (!empty($row['label'])) {
                    $details[] = [
                        'label' => $row['label'],
                        'value' => $row['value'] ?? '',
                        'unit'  => $row['unit']  ?? null,
                    ];
                }
            }
            $data['product_details'] = $details;
        }

        // ── measurement_details ───────────────────────
        $data['measurement_details'] = [
            'height'      => $request->height      ? (float) $request->height      : null,
            'height_unit' => $request->height_unit ?? null,
            'width'       => $request->width       ? (float) $request->width       : null,
            'width_unit'  => $request->width_unit  ?? null,
            'depth'       => $request->depth       ? (float) $request->depth       : null,
            'depth_unit'  => $request->depth_unit  ?? null,
            'weight'      => $request->weight      ? (float) $request->weight      : null,
            'weight_unit' => $request->weight_unit ?? null,
            'length'      => $request->length      ? (float) $request->length      : null,
            'length_unit' => $request->length_unit ?? null,
        ];

        Product::create($data);

        return redirect()->route('admin.products.index')
                         ->with('success', 'Product created successfully.');
    }

    // ── Edit ──────────────────────────────────────────
    public function edit($id)
    {
        $record = Product::findOrFail($id);

        return view('admin.products.products', array_merge(
            ['mode' => 'edit', 'record' => $record],
            $this->getDropdowns($record->sub_category_id)
        ));
    }

    // ── Update ────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'product_name'    => 'required|string|max:500',
            'category_id'     => 'required|string',
            'sub_category_id' => 'required|string',
        ]);

        $category    = MainMenu::find($request->category_id);
        $subCategory = SubMenu::find($request->sub_category_id);

        $data = [
            'product_name'          => $request->product_name,
            'product_description'   => $request->product_description,
            'category_id'           => $request->category_id,
            'category_name'         => $category?->name,
            'sub_category_id'       => $request->sub_category_id,
            'sub_category_name'     => $subCategory?->name,
            'brand_id'              => $request->brand_id,
            'pieces_per_pallet'     => $request->pieces_per_pallet,
            'pallets_per_container' => $request->pallets_per_container,
            'is_popular'            => $request->boolean('is_popular'),
            'real_time_price'       => $request->boolean('real_time_price'),
            'updated_by'            => Auth::user()->name,
        ];

        if ($request->brand_id) {
            $brand = Brand::find($request->brand_id);
            $data['brand_name'] = $brand?->name;
        }

        // ── Datasheet (replace if new file uploaded) ──
        if ($request->hasFile('datasheet')) {
            // Delete old file
            if (!empty($product->datasheet['path'])) {
                Storage::disk('public')->delete($product->datasheet['path']);
            }
            $file = $request->file('datasheet');
            $path = $file->store('products/datasheets', 'public');
            $data['datasheet'] = [
                'filename'      => basename($path),
                'original_name' => $file->getClientOriginalName(),
                'path'          => $path,
                'url'           => Storage::disk('public')->url($path),
                'mime_type'     => $file->getClientMimeType(),
                'size'          => $file->getSize(),
                'uploaded_at'   => now()->toISOString(),
            ];
        }

        // ── product_details ───────────────────────────
        if ($request->has('product_details')) {
            $details = [];
            foreach ($request->product_details as $row) {
                if (!empty($row['label'])) {
                    $details[] = [
                        'label' => $row['label'],
                        'value' => $row['value'] ?? '',
                        'unit'  => $row['unit']  ?? null,
                    ];
                }
            }
            $data['product_details'] = $details;
        }

        // ── measurement_details ───────────────────────
        $data['measurement_details'] = [
            'height'      => $request->height      ? (float) $request->height      : null,
            'height_unit' => $request->height_unit ?? null,
            'width'       => $request->width       ? (float) $request->width       : null,
            'width_unit'  => $request->width_unit  ?? null,
            'depth'       => $request->depth       ? (float) $request->depth       : null,
            'depth_unit'  => $request->depth_unit  ?? null,
            'weight'      => $request->weight      ? (float) $request->weight      : null,
            'weight_unit' => $request->weight_unit ?? null,
            'length'      => $request->length      ? (float) $request->length      : null,
            'length_unit' => $request->length_unit ?? null,
        ];

        $product->update($data);

        return redirect()->route('admin.products.index')
                         ->with('success', 'Product updated.');
    }

    // ── AJAX: Get options by sub_category_id ──────────
    public function getOptionsBySubMenu(Request $request)
    {
        $subCategoryId = $request->input('sub_menu_id');

        $options = ProductDetailOption::where('sub_menu_id', $subCategoryId)
                                      ->orderBy('option_name')
                                      ->get(['_id', 'option_name', 'unit_ids']);

        $options = $options->map(function ($option) {
            $unitIds = $option->unit_ids ?? [];
            $units   = collect();
            if (!empty($unitIds)) {
                $units = Unit::whereIn('_id', $unitIds)
                             ->orderBy('unit_name')
                             ->get(['_id', 'unit_name'])
                             ->map(fn($u) => ['unit_name' => $u->unit_name]);
            }
            return [
                'option_name' => $option->option_name,
                'units'       => $units,
            ];
        });

        return response()->json(['options' => $options]);
    }

    // ── AJAX: Get sub categories by category_id ───────
    public function getSubMenusByMainMenu(Request $request)
    {
        $categoryId  = $request->input('main_menu_id');
        $subMenus    = SubMenu::where('main_menu_id', $categoryId)->orderBy('name')->get(['_id', 'name']);
        return response()->json(['subMenus' => $subMenus]);
    }

    // ── Verify ────────────────────────────────────────
    public function verify($id)
    {
        Product::findOrFail($id)->update([
            'verification_status' => 'verified',
            'updated_by'          => Auth::user()->name,
        ]);
        return redirect()->route('admin.products.index')->with('success', 'Product verified.');
    }

    // ── Reject ────────────────────────────────────────
    public function reject($id)
    {
        Product::findOrFail($id)->update([
            'verification_status' => 'rejected',
            'updated_by'          => Auth::user()->name,
        ]);
        return redirect()->route('admin.products.index')->with('success', 'Product rejected.');
    }

    // ── Destroy ───────────────────────────────────────
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if (!empty($product->datasheet['path'])) {
            Storage::disk('public')->delete($product->datasheet['path']);
        }
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }
}