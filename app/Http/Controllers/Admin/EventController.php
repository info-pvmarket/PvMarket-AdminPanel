<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\TranslationService;

class EventController extends Controller
{

        public function __construct(protected TranslationService $translator) {}
    public function index(Request $request)
    {
        $query = Event::query();

        if ($request->filled('search')) {
            $query->where('heading', 'like', '%' . $request->search . '%');
        }

        $events = $query->orderBy('created_at', 'desc')
                        ->paginate($request->get('entries', 10));

        return view('admin.knowledge-hub.events.events', [
            'mode'   => 'index',
            'events' => $events,
        ]);
    }

    public function create()
    {
        return view('admin.knowledge-hub.events.events', [
            'mode'   => 'create',
            'record' => null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'heading'     => 'required|string|max:500',
            'place'       => 'nullable|string|max:255',
            'event_date'  => 'nullable|date',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'alt_tag'     => 'nullable|string|max:255',
        ]);

        $data = [
            'heading'     => $request->heading,
            'place'       => $request->place,
            'event_date'  => $request->event_date,
            'description' => $request->description,
            'alt_tag'     => $request->alt_tag,
        ];

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $data['image'] = $this->buildImageData($request->file('image'), 'events');
        }

        $data = $this->attachTranslations($data, new Event());

        Event::create($data);

        return redirect()->route('admin.knowledge-hub.events.index')
                         ->with('success', 'Event created successfully.');
    }

    public function edit($id)
    {
        $record = Event::findOrFail($id);

        return view('admin.knowledge-hub.events.events', [
            'mode'   => 'edit',
            'record' => $record,
        ]);
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $request->validate([
            'heading'     => 'required|string|max:500',
            'place'       => 'nullable|string|max:255',
            'event_date'  => 'nullable|date',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'alt_tag'     => 'nullable|string|max:255',
        ]);

        $data = [
            'heading'     => $request->heading,
            'place'       => $request->place,
            'event_date'  => $request->event_date,
            'description' => $request->description,
            'alt_tag'     => $request->alt_tag,
        ];

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($event->image) Storage::disk('public')->delete($event->image);
            $data['image'] = $this->buildImageData($request->file('image'), 'events');
        }

        $data = $this->attachTranslations($data, new Event());

        $event->update($data);

        return redirect()->route('admin.knowledge-hub.events.index')
                         ->with('success', 'Event updated successfully.');
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        if ($event->image) Storage::disk('public')->delete($event->image);
        $event->delete();

        return redirect()->route('admin.knowledge-hub.events.index')
                         ->with('success', 'Event deleted successfully.');
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
                    $data[$field], $locale, 'en' // ← translateText, not translate
                );
            }
        }

        if (!empty($translated)) {
            $data[$locale] = $translated;
        }
    }

    return $data;
}

private function buildImageData($file, string $folder): string
{
    $extension = $file->getClientOriginalExtension() ?: 'jpg';
    $filename = time() . '_' . uniqid() . '.' . $extension;
    $path = $folder . '/' . $filename;

    Storage::disk('public')->put($path, file_get_contents($file->getPathname()));

    return $path;
}
}