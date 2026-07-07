<?php

namespace App\Http\Controllers\Admin;

use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\TranslationService;
use App\Models\Market;

class SliderController extends ResourceController
{
    public function __construct(protected TranslationService $translator) {}

    protected string $model = Slider::class;
    protected string $view  = 'admin.setup.sliders.sliders';
    protected string $route = 'admin.setup.sliders';

    protected array $rules = [
        'name'          => 'required|string|max:255',
        'alt_tag'       => 'nullable|string|max:255',
        'redirect_link' => 'nullable|url',
        'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        'slider_type'   => 'required|string|in:top,advertisement',
    ];

    protected array $fields = ['name', 'alt_tag', 'redirect_link', 'slider_type'];

    public function store(Request $request)
    {
        $request->validate([
    'sliders.*.location_id' => 'required',
    'sliders'                 => 'required|array|min:1',
    'sliders.*.name'          => 'required|string|max:255',
    'sliders.*.slider_type'   => 'required|string|in:top,advertisement',
    'sliders.*.redirect_link' => 'nullable|url',
    'sliders.*.alt_tag'       => 'nullable|string|max:255',
    'sliders.*.image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
]);

        $sortStart   = Slider::count();
        $sliderType  = $request->input('slider_type');

        foreach ($request->sliders as $i => $data) {
            $row = [
                'location_id'   => $data['location_id'],
                'name'          => $data['name'],
                'alt_tag'       => $data['alt_tag']       ?? null,
                'redirect_link' => $data['redirect_link'] ?? null,
                'slider_type'   => $data['slider_type'] ?? null,            
                'is_active'     => true,
                'sort_order'    => $sortStart + $i + 1,
            ];

            if ($request->hasFile("sliders.{$i}.image")) {
                $row['image'] = $request->file("sliders.{$i}.image")->store('sliders', 'public');
            }

            $row = $this->attachTranslations($row, new Slider());
            Slider::create($row);
        }

        return redirect()->route('admin.setup.sliders.index')
                         ->with('success', 'Sliders saved.');
    }

    public function update(Request $request, $id)
    {
        $slider = Slider::findOrFail($id);

        $request->validate([
            'location_id' => 'required',
            'name'          => 'required|string|max:255',
            'alt_tag'       => 'nullable|string|max:255',
            'redirect_link' => 'nullable|url',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'slider_type'   => 'required|string|in:top,advertisement',
        ]);

        $data = $request->only([
    'name',
    'alt_tag',
    'redirect_link',
    'slider_type',
    'location_id'
]);

        if ($request->hasFile('image')) {
            if ($slider->image) Storage::disk('public')->delete($slider->image);
            $data['image'] = $request->file('image')->store('sliders', 'public');
        }

        $data = $this->attachTranslations($data, $slider);
        $slider->update($data);

        return redirect()->route('admin.setup.sliders.index')
                         ->with('success', 'Slider updated.');
    }

    public function toggle($id)
    {
        $slider = Slider::findOrFail($id);
        $slider->update(['is_active' => !$slider->is_active]);
        return redirect()->route('admin.setup.sliders.index');
    }

    public function destroy($id)
    {
        $slider = Slider::findOrFail($id);
        if ($slider->image) Storage::disk('public')->delete($slider->image);
        $slider->delete();
        return redirect()->route('admin.setup.sliders.index')
                         ->with('success', 'Slider deleted.');
    }

    public function reorder(Request $request)
    {
        foreach ($request->input('order', []) as $pos => $id) {
            Slider::where('_id', $id)->update(['sort_order' => $pos + 1]);
        }
        return response()->json(['success' => true]);
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
    $markets = Market::where('is_active', '!=', false)->orderBy('name')->get();

    return view($this->view, [
        'mode' => 'create',
        'markets' => $markets,
    ]);
}
public function edit($id)
{
    $record = Slider::findOrFail($id);

    $markets = Market::where('is_active', '!=', false)->orderBy('name')->get();

    return view($this->view, [
        'mode' => 'edit',
        'record' => $record,
        'markets' => $markets,
    ]);
}
}
