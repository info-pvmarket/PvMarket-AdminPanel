<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MainMenu;
use App\Models\ProductDetailOption;
use App\Models\SubMenu;
use Illuminate\Http\Request;
use App\Services\TranslationService;
use Illuminate\Support\Facades\Log;
use MongoDB\BSON\ObjectId;

class ProductDetailOptionController extends Controller
{
    public function __construct(protected TranslationService $translator) {}

    private function getCategoryDropdownData(?ObjectId $categoryId = null): array
    {
        $mainMenus = collect();
        $subMenus = collect();

        try {
            $mainMenus = MainMenu::availableForDropdown()->orderBy('category_name')->get();
        } catch (\Exception $e) {}

        try {
            $subMenusQuery = SubMenu::availableForDropdown()->orderBy('sub_category_name');
            if ($categoryId) {
                $subMenusQuery->where('category_id', $categoryId);
            }
            $subMenus = $subMenusQuery->get();
        } catch (\Exception $e) {}

        return [
            'mainMenus' => $mainMenus,
            'subMenus' => $subMenus,
            'categoryNames' => $mainMenus->mapWithKeys(
                fn ($menu) => [(string) $menu->_id => $menu->category_name]
            ),
            'subCategoryNames' => $subMenus->mapWithKeys(
                fn ($menu) => [(string) $menu->_id => $menu->sub_category_name]
            ),
        ];
    }

    private function getDropdownData(): array
    {
        $data = $this->getCategoryDropdownData();
        $units = collect();

        try {
            $units = \App\Models\Unit::orderBy('unit_name')->get();
        } catch (\Exception $e) {}

        return array_merge($data, compact('units'));
    }

    private function objectId(mixed $value): ?ObjectId
    {
        $value = trim((string) $value);

        return preg_match('/^[a-f0-9]{24}$/i', $value)
            ? new ObjectId($value)
            : null;
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $entries = (int) $request->input('entries', 10);
        $entries = in_array($entries, [10, 25, 50, 100], true) ? $entries : 10;
        $categoryId = $this->objectId($request->input('category_id'));
        $subCategoryId = $this->objectId($request->input('sub_category_id'));

        $query = ProductDetailOption::query();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($subCategoryId) {
            $query->where('sub_category_id', $subCategoryId);
        }

        $options = $query->orderBy('_id', 'desc')->paginate($entries)->withQueryString();

        return view('admin.products.product-detail-options', array_merge([
            'mode' => 'index',
            'options' => $options,
        ], $this->getCategoryDropdownData($categoryId)));
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
