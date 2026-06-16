<?php

namespace App\Http\Controllers\Admin;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Services\TranslationService;

class NewsController extends Controller
{
    public function __construct(protected TranslationService $translator) {}
    public function index(Request $request)
    {
        $query = News::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $news = $query->orderBy('created_at', 'desc')
                      ->paginate($request->get('entries', 10));

        return view('admin.knowledge-hub.news.news', [
            'mode' => 'index',
            'news' => $news,
        ]);
    }

    public function create()
    {
        return view('admin.knowledge-hub.news.news', ['mode' => 'create']);
    }

    public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'slug'    => 'nullable|string|max:255',
        'content' => 'nullable|string',
        'image'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        'alt_tag' => 'nullable|string|max:255',
    ]);

    $data = [
    'title'     => $request->title,
    'slug'      => $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->title),
    'content'   => $request->content,
    'alt_tag'   => $request->alt_tag,
    'is_active' => true,
];

    $data['image'] = $this->emptyImageData();

if ($request->hasFile('image')) {
    $data['image'] = $this->buildImageData($request->file('image'), 'news');
}

    $data = $this->attachTranslations($data, new News()); 

    News::create($data);

    return redirect()->route('admin.knowledge-hub.news.index')
                     ->with('success', 'News article created successfully.');
}

    public function edit($id)
    {
        $record = News::findOrFail($id);
        return view('admin.knowledge-hub.news.news', [
            'mode'   => 'edit',
            'record' => $record,
        ]);
    }

    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'slug'    => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'image'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'alt_tag' => 'nullable|string|max:255',
        ]);

        $slug = $request->filled('slug')
            ? Str::slug($request->slug)
            : Str::slug($request->heading);

        $data = [
    'title'   => $request->title,
    'slug'    => $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->title),
    'content' => $request->content,
    'alt_tag' => $request->alt_tag,
];

        $data['image'] = $news->image ?? $this->emptyImageData();

if ($request->hasFile('image')) {
    $oldPath = $news->image['path'] ?? null;
    if ($oldPath) Storage::disk('public')->delete($oldPath);

    $data['image'] = $this->buildImageData($request->file('image'), 'news');
}

        $data = $this->attachTranslations($data, $news);

        $news->update($data);

        return redirect()->route('admin.knowledge-hub.news.index')
                         ->with('success', 'News article updated successfully.');
    }

    public function destroy($id)
    {
        $news = News::findOrFail($id);
        $oldPath = $news->image['path'] ?? null;
if ($oldPath) Storage::disk('public')->delete($oldPath);
        $news->delete();

        return redirect()->route('admin.knowledge-hub.news.index')
                         ->with('success', 'News article deleted.');
    }
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