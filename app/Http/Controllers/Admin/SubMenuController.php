<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MainMenu;
use App\Models\SubMenu;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use MongoDB\BSON\ObjectId;

class SubMenuController extends Controller
{
    public function __construct(protected TranslationService $translator) {}

    private function availableMainMenus()
    {
        return MainMenu::availableForDropdown()
            ->orderBy('category_name')
            ->get();
    }

    public function index(Request $request)
    {
        $query = SubMenu::query();

        if ($request->filled('search')) {
            $query->where(function ($searchQuery) use ($request) {
                $searchQuery->where('sub_category_name', 'like', '%'.$request->search.'%')
                    ->orWhere('slug', 'like', '%'.$request->search.'%');
            });
        }

        $subMenus = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('entries', 10));

        return view('admin.setup.sub-menu.sub-menu', [
            'mode' => 'index',
            'subMenus' => $subMenus,
        ]);
    }

    public function create()
    {
        $mainMenus = $this->availableMainMenus();

        return view('admin.setup.sub-menu.sub-menu', [
            'mode' => 'create',
            'record' => null,
            'mainMenus' => $mainMenus,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.slug' => 'nullable|string|max:255',
            'items.*.alt_tag' => 'nullable|string|max:255',
        ]);

        $categoryId = new ObjectId($request->category_id);
        $submittedSlugs = [];

        foreach ($request->items as $index => $item) {
            $slug = Str::slug(! empty($item['slug']) ? $item['slug'] : $item['name']);
            $this->ensureSlugIsAvailable($categoryId, $slug, "items.$index.slug", null, $submittedSlugs);
            $submittedSlugs[] = $slug;

            $data = [
                'sub_category_name' => $item['name'],
                'category_id' => $categoryId,
                'slug' => $slug,
                'category_name' => MainMenu::find($request->category_id)?->category_name ?? '',
                'is_hold' => false,
                'is_active' => true,
                'stock_value' => false,
                'pallet_applicable' => false,
                'container_applicable' => false,
                'created_by' => new ObjectId(auth()->id()),
            ];

            $data = $this->attachTranslations($data, new SubMenu);
            SubMenu::create($data);
        }

        return redirect()->route('admin.setup.sub-menus.index')
            ->with('success', count($request->items).' sub category(s) created successfully.');
    }

    public function edit($id)
    {
        $record = SubMenu::findOrFail($id);
        $mainMenus = $this->availableMainMenus();

        return view('admin.setup.sub-menu.sub-menu', [
            'mode' => 'edit',
            'record' => $record,
            'mainMenus' => $mainMenus,
        ]);
    }

    public function update(Request $request, $id)
    {
        $subMenu = SubMenu::findOrFail($id);

        $request->validate([
            'sub_category_name' => 'required|string|max:255',
            'category_id' => 'required|string',
            'slug' => 'nullable|string|max:255',
            'alt_tag' => 'nullable|string|max:255',
        ]);

        $categoryId = new ObjectId($request->category_id);
        $slug = Str::slug($request->slug ?: $request->sub_category_name);
        $this->ensureSlugIsAvailable($categoryId, $slug, 'slug', $subMenu);

        $data = [
            'sub_category_name' => $request->sub_category_name,
            'category_id' => $categoryId,
            'slug' => $slug,
            'category_name' => MainMenu::find($request->category_id)?->category_name ?? '',
            'updated_by' => new ObjectId(auth()->id()),
        ];

        $data = $this->attachTranslations($data, $subMenu);
        $subMenu->update($data);

        return redirect()->route('admin.setup.sub-menus.index')
            ->with('success', 'Sub category updated successfully.');
    }

    public function toggleStatus($id)
    {
        $subMenu = SubMenu::findOrFail($id);
        $isActive = ! $subMenu->is_active;
        $subMenu->update([
            'is_active' => $isActive,
            'is_hold' => ! $isActive,
        ]);

        return back()->with('success', 'Active status updated.');
    }

    public function toggleStock($id)
    {
        $subMenu = SubMenu::findOrFail($id);
        $subMenu->update(['stock_value' => ! $subMenu->stock_value]);

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
        $languages = array_keys(config('languages.available'));
        $translatable = $modelInstance->translatable ?? [];

        foreach ($languages as $locale) {
            if ($locale === 'en') {
                continue;
            }

            $translated = [];
            foreach ($translatable as $field) {
                if (! empty($data[$field])) {
                    $translated[$field] = $this->translator->translateText(
                        $data[$field], $locale, 'en'
                    );
                }
            }

            if (! empty($translated)) {
                $data[$locale] = $translated;
            }
        }

        return $data;
    }

    private function ensureSlugIsAvailable(
        ObjectId $categoryId,
        string $slug,
        string $field,
        ?SubMenu $current = null,
        array $submittedSlugs = []
    ): void {
        if ($slug === '') {
            throw ValidationException::withMessages([
                $field => 'Please enter a valid slug.',
            ]);
        }

        if (in_array($slug, $submittedSlugs, true)) {
            throw ValidationException::withMessages([
                $field => 'Each sub category slug must be unique within the selected category.',
            ]);
        }

        $existing = SubMenu::where('category_id', $categoryId)
            ->where('slug', $slug)
            ->first();

        if ($existing && (! $current || (string) $existing->id !== (string) $current->id)) {
            throw ValidationException::withMessages([
                $field => 'This slug is already used by another sub category in the selected category.',
            ]);
        }
    }
}
