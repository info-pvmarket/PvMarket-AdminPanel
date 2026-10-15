<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\ListingImport;
use App\Models\Incoterm;
use App\Models\ListingImportBatch;
use App\Models\MainMenu;
use App\Models\SubMenu;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use MongoDB\BSON\ObjectId;

class ListingImportController extends Controller
{
    // Labelled as on the website's Create Offer form.
    private array $headings = [
        'Main Category', 'Sub Category', 'Product', 'Warehouse',
        'Sell Type', 'Currency', 'Promotion', 'Total Quantity', 'Lead Time (weeks)', 'Incoterm',
        'Tier 1 Min Qty', 'Tier 1 Max Qty', 'Tier 1 Price / Unit', 'Tier 1 Commission %',
        'Tier 2 Min Qty', 'Tier 2 Max Qty', 'Tier 2 Price / Unit', 'Tier 2 Commission %',
        'Tier 3 Min Qty', 'Tier 3 Max Qty', 'Tier 3 Price / Unit', 'Tier 3 Commission %',
    ];

    // GET user/listings/import/template?category_id=&sub_category_id=
    public function template(Request $request)
    {
        $request->validate([
            'category_id'     => 'required|string',
            'sub_category_id' => 'required|string',
        ]);
        if (! MainMenu::find($request->category_id) || ! SubMenu::find($request->sub_category_id)) {
            return back()->with('error', 'Invalid category or sub-category.');
        }

        $dropdowns = [
            'Sell Type' => ['Sell By Pieces Only', 'Sell By Pallets Only', 'Sell By Containers Only'],
            'Promotion' => ['No Promotion', 'Exclusive Offer', 'Top Discount'],
            'Incoterm'  => Incoterm::orderBy('code')->pluck('code')->filter()->values()->all(),
            'Warehouse' => Warehouse::orderBy('warehouse_name')->pluck('warehouse_name')
                ->filter(fn ($n) => $n && ! str_contains($n, ','))->values()->all(),
        ];

        return Excel::download(
            new ProductImportTemplateExport($this->headings, [], $dropdowns),
            'listing_import_template.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file'            => 'required|file|mimes:xlsx,csv,txt',
            'category_id'     => 'required|string',
            'sub_category_id' => 'required|string',
        ]);
        if (! MainMenu::find($request->category_id) || ! SubMenu::find($request->sub_category_id)) {
            return response()->json(['status' => 'failed', 'message' => 'Invalid category or sub-category.'], 422);
        }

        $batch = ListingImportBatch::create([
            'user_id'         => new ObjectId(Auth::id()),
            'category_id'     => new ObjectId($request->category_id),
            'sub_category_id' => new ObjectId($request->sub_category_id),
            'file_name'       => $request->file('file')->getClientOriginalName(),
            'status'          => 'processing',
            'success_count'   => 0,
            'error_count'     => 0,
            'errors'          => [],
        ]);

        try {
            Excel::import(
                new ListingImport((string) $batch->_id, (string) Auth::id(), $request->category_id, $request->sub_category_id),
                $request->file('file')
            );
        } catch (\Throwable $e) {
            $batch->update(['status' => 'failed']);
            return response()->json(['status' => 'failed', 'message' => 'Import failed: ' . $e->getMessage()], 500);
        }

        $batch->update(['status' => 'completed', 'completed_at' => now()]);
        $batch->refresh();

        return response()->json([
            'batch_id'      => (string) $batch->_id,
            'status'        => 'completed',
            'success_count' => (int) $batch->success_count,
            'error_count'   => (int) $batch->error_count,
            'errors'        => array_slice((array) $batch->errors, 0, 50),
        ]);
    }

    public function status(string $batch)
    {
        $b = ListingImportBatch::find($batch);
        if (! $b) return response()->json(['error' => 'Not found'], 404);
        return response()->json([
            'status'        => $b->status,
            'success_count' => (int) $b->success_count,
            'error_count'   => (int) $b->error_count,
            'errors'        => array_slice((array) $b->errors, 0, 50),
        ]);
    }
}
