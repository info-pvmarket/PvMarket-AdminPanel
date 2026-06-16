<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductDetailOption;
use Illuminate\Http\Request;
use App\Services\TranslationService;
use Illuminate\Support\Facades\Log;

class ProductDetailOptionController extends Controller
{
    public function __construct(protected TranslationService $translator) {}

    private function getDropdownData(): array
    {
        $mainMenus = [];
        $subMenus  = [];
        $units     = [];

        try {
            $mainMenus = \App\Models\MainMenu::orderBy('category_name')->get();
        } catch (\Exception $e) {}

        try {
            $subMenus = \App\Models\SubMenu::orderBy('sub_category_name')->get();
        } catch (\Exception $e) {}

        try {
            $units = \App\Models\Unit::orderBy('unit_name')->get();
        } catch (\Exception $e) {}

        return compact('mainMenus', 'subMenus', 'units');
    }

    public function index(Request $request)
    {
        $search  = $request->input('search');
        $entries = (int) $request->input('entries', 10);

        $query = ProductDetailOption::query();

        if ($search) {
            $query->where('option_name', 'like', "%{$search}%");
        }

        $options = $query->orderBy('_id', 'desc')->paginate($entries)->withQueryString();

        return view('admin.products.product-detail-options', [
            'mode'    => 'index',
            'options' => $options,
        ]);
    }

    public function create()
    {
        return view('admin.products.product-detail-options', array_merge(
            ['mode' => 'create', 'record' => null],
            $this->getDropdownData()
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'option_name'     => 'required|string|max:255',
            'data_type'       => 'required|in:integer,float,small_text,long_text',
            'category_id'     => 'nullable',
            'sub_category_id' => 'nullable',
            'unit_ids'        => 'nullable|array',
        ]);

        $category    = $request->category_id
            ? \App\Models\MainMenu::find($request->category_id)
            : null;

        $subCategory = $request->sub_category_id
            ? \App\Models\SubMenu::find($request->sub_category_id)
            : null;

        $unitNames = [];
        if (!empty($request->unit_ids)) {
            $unitObjectIds = collect($request->unit_ids)
                ->map(fn($id) => new \MongoDB\BSON\ObjectId($id))
                ->toArray();
            $unitNames = \App\Models\Unit::whereIn('_id', $unitObjectIds)
                ->pluck('unit_name')
                ->toArray();
        }

        $data = [
            'name'              => $request->option_name,
            'data_type'         => $request->data_type,
            'is_tag'            => false,
            'is_active'         => true,
            'category_id'       => $request->category_id
                ? new \MongoDB\BSON\ObjectId($request->category_id)
                : null,
            'category_name'     => $category?->category_name,
            'sub_category_id'   => $request->sub_category_id
                ? new \MongoDB\BSON\ObjectId($request->sub_category_id)
                : null,
            'sub_category_name' => $subCategory?->sub_category_name,
            'unit_ids'          => collect($request->unit_ids ?? [])
                ->map(fn($id) => new \MongoDB\BSON\ObjectId($id))
                ->toArray(),
            'unit_names'        => $unitNames,
        ];

        $data = $this->translateAllFields($data, $unitNames);

        ProductDetailOption::create($data);

        return redirect()->route('admin.products.detail-options.index')
            ->with('success', 'Product specification created successfully.');
    }

    public function edit(ProductDetailOption $detailOption)
    {
        return view('admin.products.product-detail-options', array_merge(
            ['mode' => 'edit', 'record' => $detailOption],
            $this->getDropdownData()
        ));
    }

    public function update(Request $request, ProductDetailOption $detailOption)
    {
        $request->validate([
            'option_name'     => 'required|string|max:255',
            'data_type'       => 'required|in:integer,float,small_text,long_text',
            'category_id'     => 'nullable',
            'sub_category_id' => 'nullable',
            'unit_ids'        => 'nullable|array',
        ]);

        $category    = $request->category_id
            ? \App\Models\MainMenu::find($request->category_id)
            : null;

        $subCategory = $request->sub_category_id
            ? \App\Models\SubMenu::find($request->sub_category_id)
            : null;

        $unitNames = [];
        if (!empty($request->unit_ids)) {
            $unitObjectIds = collect($request->unit_ids)
                ->map(fn($id) => new \MongoDB\BSON\ObjectId($id))
                ->toArray();
            $unitNames = \App\Models\Unit::whereIn('_id', $unitObjectIds)
                ->pluck('unit_name')
                ->toArray();
        }

        $data = [
            'name'              => $request->option_name,
            'data_type'         => $request->data_type,
            'category_id'       => $request->category_id
                ? new \MongoDB\BSON\ObjectId($request->category_id)
                : null,
            'category_name'     => $category?->category_name,
            'sub_category_id'   => $request->sub_category_id
                ? new \MongoDB\BSON\ObjectId($request->sub_category_id)
                : null,
            'sub_category_name' => $subCategory?->sub_category_name,
            'unit_ids'          => collect($request->unit_ids ?? [])
                ->map(fn($id) => new \MongoDB\BSON\ObjectId($id))
                ->toArray(),
            'unit_names'        => $unitNames,
        ];

        $data = $this->translateAllFields($data, $unitNames);

        $detailOption->update($data);

        return redirect()->route('admin.products.detail-options.index')
            ->with('success', 'Product specification updated successfully.');
    }

    public function destroy(ProductDetailOption $detailOption)
    {
        $detailOption->delete();

        return redirect()->route('admin.products.detail-options.index')
            ->with('success', 'Product specification deleted successfully.');
    }

    /**
     * Translate all fields for every configured language.
     * Handles both string fields and array fields (unit_names).
     */
    private function translateAllFields(array $data, array $unitNames = []): array
    {
        $languages = array_keys(config('languages.available'));

        // String fields to translate
        $stringFields = [
            'name',
            'category_name',
            'sub_category_name',
        ];

        foreach ($languages as $locale) {
            if ($locale === 'en') continue;

            $translated = [];

            // ── Translate string fields ──────────────────────
            foreach ($stringFields as $field) {
                $original = $data[$field] ?? null;

                if (empty($original) || !is_string($original)) continue;
                if (strlen(trim($original)) < 2) continue;

                try {
                    $result = $this->translator->translateText($original, $locale, 'en');
                    if ($result) {
                        $translated[$field] = $result;
                    }
                } catch (\Exception $e) {
                    Log::error("translateAllFields string [{$locale}][{$field}]: " . $e->getMessage());
                }
            }

            // ── Translate unit_names array ───────────────────
            if (!empty($unitNames)) {
                $translatedUnits = [];
                foreach ($unitNames as $unitName) {
                    if (empty($unitName) || !is_string($unitName)) {
                        $translatedUnits[] = $unitName;
                        continue;
                    }
                    try {
                        $result = $this->translator->translateText($unitName, $locale, 'en');
                        $translatedUnits[] = $result ?: $unitName;
                    } catch (\Exception $e) {
                        Log::error("translateAllFields unit_name [{$locale}]: " . $e->getMessage());
                        $translatedUnits[] = $unitName;
                    }
                }
                $translated['unit_names'] = $translatedUnits;
            }

            // ── Save locale block ────────────────────────────
            if (!empty($translated)) {
                $data[$locale] = $translated;
                Log::info("translateAllFields [{$locale}] saved", $translated);
            }
        }

        return $data;
    }
}