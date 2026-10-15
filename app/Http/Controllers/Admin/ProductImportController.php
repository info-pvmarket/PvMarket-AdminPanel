<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\ProductImport;
use App\Models\MainMenu;
use App\Models\ProductDetailOption;
use App\Models\ProductImportBatch;
use App\Models\SubMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use MongoDB\BSON\ObjectId;

class ProductImportController extends Controller 
{
    // GET  admin/products/import/template?category_id=&sub_category_id=
    public function template(Request $request) 
    {
        $request->validate([
            'category_id' => 'required|string',
            'sub_category_id' => 'required|string',
        ]);

        $specs = ProductDetailOption::where('sub_category_id', new ObjectId($request->sub_category_id))
          ->orderBy('name')->pluck('name')->filter()->values()->toArray();
        
        $headings = array_merge([
            'Product Name', 'Brand', 'Main Category', 'Sub Category', 'Specification',
            'Unit', 'Product Description', 'Pieces Per Pallet', 'Pallets per Container', 'Height', 'Height Unit',
            'Width', 'Width Unit', 'Depth', 'Depth Unit', 'Weight', 'Weight Unit',
        ], $specs);

        return Excel::download(new ProductImportTemplateExport($headings), 'product_import_template.xlsx');
    }

    //POST admin/products/import
    public function import(Request $request) 
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,txt',
            'category_id' => 'required|string',
            'sub_category_id' => 'required|string',
        ]);

        if(!MainMenu:: find($request->category_id) || !SubMenu::find($request->sub_category_id)) {
            return back()->with('error', 'Invalid category or sub-category.');
        }

        $batch = ProductImportBatch::create([
            'user_id' => new ObjectId(Auth::id()),
            'category_id' => new ObjectId($request->category_id),
            'sub_category_id' => new ObjectId($request->sub_category_id),
            'file_name' => $request->file('file')->getClientOriginalName(),
            'status' => 'processing',
            'success_count' => 0,
            'error_count' => 0,
            'errors' => [],
        ]);

        $verify = (Auth::user()->role ?? '') === 'super_admin';

        try {
            Excel::import(
                new ProductImport((string) $batch->_id, $request->category_id, $request->sub_category_id, (string) Auth::id(), $verify),
                $request->file('file')
            );
        } catch (\Throwable $e) {
            $batch->update(['status' => 'failed']);
            return back()->with('import_batch_id', (string) $batch->_id)->with('error', 'Import failed: ' . $e->getMessage());
        }

        $batch->update(['status' => 'completed', 'completed_at' => now()]);

        return back()
            ->with('import_batch_id', (string) $batch->_id)
            ->with('success', 'Import complete.');
    }

    // GET admin/products/import/{batch}/status  (JSON, polled by the UI)

    public function status(string $batch) 
    {
        $b = ProductImportBatch::find($batch);
        if (!$b) {
            return response()->json(['error' => 'Not found'], 404);
        }
        return response()->json([
            'status' => $b->status,
            'success_count' => (int) $b->success_count,
            'error_count' => (int) $b->error_count,
            'errors' => array_slice((array) $b->errors, 0, 50),
        ]);
    }
}