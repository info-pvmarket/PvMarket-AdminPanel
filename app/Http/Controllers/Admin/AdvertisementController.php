<?php

namespace App\Http\Controllers\Admin;

use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Services\TranslationService;
use App\Models\Location;    

class AdvertisementController extends ResourceController
{
    public function __construct(protected TranslationService $translator) {}
    protected string $model  = Advertisement::class;
    protected string $view   = 'admin.setup.advertisements.advertisements';
    protected string $route  = 'admin.setup.advertisements';

    protected array $rules = [
    'title'         => 'required|string|max:255',
    'location_id'   => 'required',
    'alt_tag'       => 'nullable|string|max:255',
    'redirect_link' => 'nullable|url',
    'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
];

    protected array $fields = [
    'title',
    'alt_tag',
    'redirect_link',
    'location_id'
];

    // Override store/update to handle image upload
    public function store(Request $request)
    {
        $request->validate($this->rules);
        $data = $request->only($this->fields);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('advertisements', 'public');
        }
        $data['is_active'] = true;
        $data = $this->attachTranslations($data, new Advertisement());
        Advertisement::create($data);
        return redirect()->route($this->route . '.index')->with('success', 'Advertisement created.');
    }

    public function update(Request $request, $id)
    {
        $ad = Advertisement::findOrFail($id);
        $request->validate($this->rules);
        $data = $request->only($this->fields);
        if ($request->hasFile('image')) {
            if ($ad->image) Storage::disk('public')->delete($ad->image);
            $data['image'] = $request->file('image')->store('advertisements', 'public');
        }
        $data = $this->attachTranslations($data, $ad);
        $ad->update($data);
        return redirect()->route($this->route . '.index')->with('success', 'Advertisement updated.');
    }

    public function toggle($id)
    {
        $ad = Advertisement::findOrFail($id);
        $ad->update(['is_active' => !$ad->is_active]);
        return redirect()->route($this->route . '.index');
    }

    public function destroy($id)
    {
        $ad = Advertisement::findOrFail($id);
        if ($ad->image) Storage::disk('public')->delete($ad->image);
        $ad->delete();
        return redirect()->route($this->route . '.index')->with('success', 'Deleted.');
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
public function create()
{
    return view($this->view, [
        'mode'      => 'create',
        'locations' => Location::orderBy('country_name')->get(),
    ]);
}
public function edit($id)
{
    return view($this->view, [
        'mode'      => 'edit',
        'record'    => Advertisement::findOrFail($id),
        'locations' => Location::orderBy('country_name')->get(),
    ]);
}
}