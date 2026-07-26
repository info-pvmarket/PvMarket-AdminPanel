<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubMenu;
use App\Models\MainMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\TranslationService;

class SubMenuController extends Controller
{
    public function __construct(protected TranslationService $translator) {}

    private function availableMainMenus()
    {
        return MainMenu::where('is_active', true)
            ->orderBy('category_name')
            ->get();
    }

    public function index(Request $request)
    {
        $query = SubMenu::query();

        if ($request->filled('search')) {
            $query->where('sub_category_name', 'like', '%' . $request->search . '%');
        }

        $subMenus = $query->orderBy('created_at', 'desc')
                          ->paginate($request->get('entries', 10));

        return view('admin.setup.sub-menu.sub-menu', [
            'mode'     => 'index',
            'subMenus' => $subMenus,
        ]);
    }

    public function create()
    {
        $mainMenus = $this->availableMainMenus();

        return view('admin.setup.sub-menu.sub-menu', [
            'mode'      => 'create',
            'record'    => null,
            'mainMenus' => $mainMenus,
        ]);
    }

    public function store(Request $request)
{
    $request->validate([
        'category_id'     => 'required|string',
        'items'           => 'required|array|min:1',
        'items.*.name'    => 'required|string|max:255',
        'items.*.alt_tag' => 'nullable|string|max:255',
    ]);

    foreach ($request->items as $index => $item) {

        $data = [
            'sub_category_name'    => $item['name'],
            'category_id'          => new \MongoDB\BSON\ObjectId($request->category_id),
            'slug'                 => Str::slug($item['name']),
            'category_name'        => MainMenu::find($request->category_id)?->category_name ?? '',
            'is_hold'              => false,
            'is_active'            => true,
            'stock_value'          => false,
            'pallet_applicable'    => false,
            'container_applicable' => false,
            'created_by'           => new \MongoDB\BSON\ObjectId(auth()->id()),
        ];

        $data = $this->attachTranslations($data, new SubMenu());
        SubMenu::create($data);
    }

    return redirect()->route('admin.setup.sub-menus.index')
                     ->with('success', count($request->items) . ' sub category(s) created successfully.');
}

    public function edit($id)
    {
        $record    = SubMenu::findOrFail($id);
        $mainMenus = $this->availableMainMenus();

        if ($record->category_id && !$mainMenus->contains('id', (string) $record->category_id)) {
            $currentMenu = MainMenu::find($record->category_id);
            if ($currentMenu) {
                $mainMenus->push($currentMenu);
                $mainMenus = $mainMenus->sortBy('category_name')->values();
            }
        }

        return view('admin.setup.sub-menu.sub-menu', [
            'mode'      => 'edit',
            'record'    => $record,
            'mainMenus' => $mainMenus,
        ]);
    }

    public function update(Request $request, $id)
{
    $subMenu = SubMenu::findOrFail($id);

    $request->validate([
        'sub_category_name' => 'required|string|max:255',
        'category_id'       => 'required|string',
        'slug'              => 'nullable|string|max:255',
        'alt_tag'           => 'nullable|string|max:255',
    ]);

    $data = [
        'sub_category_name'    => $request->sub_category_name,
        'category_id'          => new \MongoDB\BSON\ObjectId($request->category_id),
        'slug'                 => $request->slug ?: Str::slug($request->sub_category_name),
        'category_name'        => MainMenu::find($request->category_id)?->category_name ?? '',
        'updated_by'           => new \MongoDB\BSON\ObjectId(auth()->id()),
    ];

    $data = $this->attachTranslations($data, $subMenu);
    $subMenu->update($data);

    return redirect()->route('admin.setup.sub-menus.index')
                     ->with('success', 'Sub category updated successfully.');
}

    public function toggleStatus($id)
{
    $subMenu = SubMenu::findOrFail($id);
    $isActive = !$subMenu->is_active;
    $subMenu->update([
        'is_active' => $isActive,
        'is_hold'   => !$isActive,
    ]);

    return back()->with('success', 'Active status updated.');
}

    public function toggleStock($id)
    {
        $subMenu = SubMenu::findOrFail($id);
        $subMenu->update(['stock_value' => !$subMenu->stock_value]);

        return back()->with('success', 'Stock value updated.');
    }

    public function destroy($id)
{
    $subMenu = SubMenu::findOrFail($id);
    $subMenu->delete();

    return redirect()->route('admin.setup.sub-menus.index')
                     ->with('success', 'Sub category deleted.');
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
