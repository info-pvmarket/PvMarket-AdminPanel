<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\TranslationService;

class BrandController extends Controller
{
    public function __construct(protected TranslationService $translator) {}
    public function index(Request $request)
    {
        $query = Brand::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $sort = in_array($request->get('sort'), ['newest', 'oldest'], true)
            ? $request->get('sort')
            : 'newest';

        $brands = $query->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc')
                        ->orderBy('menu_order', 'asc')
                        ->paginate($request->get('entries', 10))
                        ->withQueryString();

        return view('admin.setup.brands.brands', [
            'mode'   => 'index',
            'brands' => $brands,
        ]);
    }

    public function create()
    {
        return view('admin.setup.brands.brands', [
            'mode'   => 'create',
            'record' => null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'brands'              => 'required|array|min:1',
            'brands.*.name'       => 'required|string|max:255',
            'brands.*.alt_tag'    => 'nullable|string|max:255',
            'brands.*.menu_order' => 'nullable|integer|min:0',
            'brands.*.image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        foreach ($request->brands as $i => $data) {
            $imageData = $this->emptyImageData();

            if ($request->hasFile("brands.{$i}.image")) {
                $imageData = $this->buildImageData($request->file("brands.{$i}.image"), 'uploads/brands');
            }

            $rowData = [
    'name'          => $data['name'],
    'slug'          => Str::slug($data['name']),
    'alt_tag'       => $data['alt_tag'] ?? Str::slug($data['name']),
    'menu_order'    => isset($data['menu_order']) ? (int)$data['menu_order'] : 0,
    'image'         => $imageData,
    'is_active'     => true,
    'is_hold'       => false,
];
$rowData = $this->attachTranslations($rowData, new Brand());
Brand::create($rowData);
        }

        return redirect()->route('admin.setup.brands.index')
                         ->with('success', count($request->brands) . ' brand(s) added.');
    }

    public function edit($id)
    {
        $record = Brand::findOrFail($id);
        return view('admin.setup.brands.brands', [
            'mode'   => 'edit',
            'record' => $record,
        ]);
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $request->validate([
            'name'       => 'required|string|max:255',
            'slug'       => 'nullable|string|max:255',
            'alt_tag'    => 'nullable|string|max:255',
            'menu_order' => 'nullable|integer|min:0',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $imageData = $brand->image ?? $this->emptyImageData();

        if ($request->hasFile('image')) {
            // Delete old file if exists
            $oldPath = $brand->image['path'] ?? null;
            if ($oldPath) Storage::disk('public')->delete($oldPath);

            $imageData = $this->buildImageData($request->file('image'), 'uploads/brands');
        }

        $updateData = [
    'name'          => $request->name,
    'slug'          => $request->slug ?: Str::slug($request->name),
    'alt_tag'       => $request->alt_tag ?? Str::slug($request->name),
    'menu_order'    => (int)($request->menu_order ?? 0),
    'image'         => $imageData,
];
$updateData = $this->attachTranslations($updateData, $brand);
$brand->update($updateData);

        return redirect()->route('admin.setup.brands.index')
                         ->with('success', 'Brand updated.');
    }

    public function toggle($id)
    {
        $brand = Brand::findOrFail($id);
        $isActive = !$brand->is_active;
        $brand->update([
            'is_active' => $isActive,
            'is_hold'   => !$isActive,
        ]);

        return redirect()->route('admin.setup.brands.index')
                         ->with('success', 'Active status updated.');
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);

        $oldPath = $brand->image['path'] ?? null;
        if ($oldPath) Storage::disk('public')->delete($oldPath);

        $brand->delete();
        return redirect()->route('admin.setup.brands.index')
                         ->with('success', 'Brand deleted.');
    }

    // ── Helpers ──────────────────────────────────────────────

    private function emptyImageData(): array
    {
        return [
            'size'          => 0,
            'uploaded_at'   => now()->toISOString(),
            'filename'      => '',
            'original_name' => '',
            'path'          => '',
            'url'           => '',
            'mime_type'     => '',
        ];
    }

    private function buildImageData($file, string $folder): array
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        $path     = $file->storeAs($folder, $filename, 'public');

        return [
            'size'          => $file->getSize(),
            'uploaded_at'   => now()->toISOString(),
            'filename'      => $filename,
            'original_name' => $file->getClientOriginalName(),
            'path'          => $path,
            'url'           => $path,
            'mime_type'     => $file->getMimeType(),
        ];
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
