<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MainMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\TranslationService;

class MainMenuController extends Controller
{
    public function __construct(protected TranslationService $translator) {}

    public function index(Request $request)
{
    $query = MainMenu::query();

    if ($request->filled('search')) {
        $query->where('category_name', 'like', '%' . $request->search . '%');
    }

    $menus = $query->orderBy('sort_order', 'asc')   // ← was created_at desc
                   ->orderBy('created_at', 'asc')   // fallback for nulls
                   ->paginate($request->get('entries', 10));

    return view('admin.setup.main-menu.main-menu', [
        'mode'  => 'index',
        'menus' => $menus,
    ]);
}
public function reorder(Request $request)
{
    $request->validate([
        'order'   => 'required|array',
        'order.*' => 'string', // MongoDB IDs are strings
    ]);

    foreach ($request->order as $position => $id) {
        MainMenu::where('_id', $id)
                ->update(['sort_order' => $position + 1]);
    }

    return response()->json(['success' => true]);
}

    public function create()
    {
        return view('admin.setup.main-menu.main-menu', [
            'mode'   => 'create',
            'record' => null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items'           => 'required|array|min:1',
            'items.*.name'    => 'required|string|max:255',
            'items.*.icon'    => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'items.*.alt_tag' => 'nullable|string|max:255',
        ]);

        foreach ($request->items as $index => $item) {
            $iconData = [
                'size'          => 0,
                'uploaded_at'   => now()->toISOString(),
                'filename'      => '',
                'original_name' => '',
                'path'          => '',
                'url'           => '',
                'mime_type'     => '',
            ];

            if ($request->hasFile("items.{$index}.icon")) {
                $file     = $request->file("items.{$index}.icon");
                $filename = time() . '_' . $file->getClientOriginalName();
                $path     = $file->storeAs('uploads/categories', $filename, 'public');

                $iconData = [
                    'size'          => $file->getSize(),
                    'uploaded_at'   => now()->toISOString(),
                    'filename'      => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'path'          => $path,
                    'url'           => $path,
                    'mime_type'     => $file->getMimeType(),
                ];
            }

            $data = [
                'category_name'       => $item['name'],
                'slug'                => Str::slug($item['name']),
                'category_icon_image' => $iconData,
                'icon_alt_tag'        => $item['alt_tag'] ?? Str::slug($item['name']),
                'is_hold'             => false,
                'stock_value'         => false,
                'created_by'          => auth()->id(),
                'is_active'           => true,
            ];

            $data = $this->attachTranslations($data, new MainMenu());
            MainMenu::create($data);
        }

        return redirect()->route('admin.setup.main-menus.index')
                         ->with('success', count($request->items) . ' menu(s) created successfully.');
    }

    public function edit($id)
    {
        $record = MainMenu::findOrFail($id);

        return view('admin.setup.main-menu.main-menu', [
            'mode'   => 'edit',
            'record' => $record,
        ]);
    }

    public function update(Request $request, $id)
    {
        $menu = MainMenu::findOrFail($id);

        $request->validate([
            'category_name'   => 'required|string|max:255',
            'slug'            => 'nullable|string|max:255',
            'alt_tag'         => 'nullable|string|max:255',
            'icon'            => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        // ── Icon ──────────────────────────────────────────────
        $iconData = $menu->category_icon_image ?? [
            'size'          => 0,
            'uploaded_at'   => now()->toISOString(),
            'filename'      => '',
            'original_name' => '',
            'path'          => '',
            'url'           => '',
            'mime_type'     => '',
        ];

        if ($request->hasFile('icon')) {
            $file     = $request->file('icon');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path     = $file->storeAs('uploads/categories', $filename, 'public');

            $iconData = [
                'size'          => $file->getSize(),
                'uploaded_at'   => now()->toISOString(),
                'filename'      => $filename,
                'original_name' => $file->getClientOriginalName(),
                'path'          => $path,
                'url'           => $path,
                'mime_type'     => $file->getMimeType(),
            ];
        }

        // ── Build data ────────────────────────────────────────
        $data = [
            'category_name'       => $request->category_name,
            'slug'                => $request->slug ?: Str::slug($request->category_name),
            'category_icon_image' => $iconData,
            'icon_alt_tag'        => $request->alt_tag ?? Str::slug($request->category_name),
        ];

        $data = $this->attachTranslations($data, $menu);
        $menu->update($data);

        return redirect()->route('admin.setup.main-menus.index')
                         ->with('success', 'Main menu updated successfully.');
    }

    public function toggleStatus($id)
    {
        $menu = MainMenu::findOrFail($id);
        $isActive = !$menu->is_active;
        $menu->update([
            'is_active' => $isActive,
            'is_hold'   => !$isActive,
        ]);

        return back()->with('success', 'Active status updated.');
    }

    public function toggleStock($id)
    {
        $menu = MainMenu::findOrFail($id);
        $menu->update(['stock_value' => !$menu->stock_value]);

        return back()->with('success', 'Stock value updated.');
    }

    public function destroy($id)
    {
        $menu = MainMenu::findOrFail($id);

        $iconPath = $menu->category_icon_image['path'] ?? null;
        if ($iconPath) Storage::disk('public')->delete($iconPath);


        $menu->delete();

        return redirect()->route('admin.setup.main-menus.index')
                         ->with('success', 'Category deleted.');
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
