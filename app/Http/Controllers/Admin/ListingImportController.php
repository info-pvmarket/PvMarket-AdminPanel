<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\ListingImport;
use App\Models\ListingImportBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use MongoDB\BSON\ObjectId;

class ListingImportController extends Controller 
{
    private array $headings = [
        'category', 'sub_category', 'product_name', 
        'warehouse_name', 'sell_type', 'currency', 
        'promotion', 'total_quantity', 'lead_time', 
        'incoterm', 'tier1_min_qty', 'tier1_max_qty', 
        'tier1_price', 'tier1_commission',
        'tier2_min_qty', 'tier2_max_qty', 'tier2_price',
        'tier2_commission', 'tier3_min_qty', 'tier3_max_qty',
        'tier3_price', 'tier3_commission',
    ];

    public function template()
    {
        return Excel::download(new ProductImportTemplateExport($this->headings), 'listing_import_template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,txt']);

        $batch = ListingImportBatch::create([
            'user_id'       => new ObjectId(Auth::id()),
            'file_name'     => $request->file('file')->getClientOriginalName(),
            'status'        => 'processing',
            'success_count' => 0,
            'error_count'   => 0,
            'errors'        => [],
        ]);

        try {
            Excel::import(new ListingImport((string) $batch->_id, (string) Auth::id()), $request->file('file'));
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
        if (!$b) return response()->json(['error' => 'Not found'], 404);
        return response()->json([
            'status' => $b->status,
            'success_count' => (int) $b->success_count,
            'error_count'=> (int) $b->error_count,
            'errors' => array_slice((array) $b->errors, 0, 50),
        ]);

    }
}