<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\TranslationService;

class BlogController extends Controller
{
    public function __construct(protected TranslationService $translator) {}

    public function index(Request $request)
    {
        $query = Blog::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $blogs = $query->orderBy('created_at', 'desc')
                       ->paginate($request->get('entries', 10));

        return view('admin.knowledge-hub.blogs.blogs', [
            'mode'  => 'index',
            'blogs' => $blogs,
        ]);
    }

    public function create()
    {
        $allBlogs = Blog::orderBy('title')->get(['_id', 'title']);

        return view('admin.knowledge-hub.blogs.blogs', [
            'mode'     => 'create',
            'record'   => null,
            'allBlogs' => $allBlogs,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'required|string|max:500',
            'alt_tag'         => 'nullable|string|max:255',
            'slug'            => 'nullable|string|max:255',
            'related_blog_id' => 'nullable|string',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'data'            => 'nullable|string',
        ]);

        $data = [
            'title'         => $request->title,
            'alt_tag'       => $request->alt_tag,
            'slug'          => $request->slug
                                  ? Str::slug($request->slug)
                                  : Str::slug($request->title),
            'related_ids'   => $request->related_blog_id
                                  ? [$request->related_blog_id]
                                  : [],
            'data'          => $request->data,
            'content'       => null,
            'is_faq'        => false,
            'is_active'     => true,
            'blog_comments' => [],
            'image'         => $this->buildImageData(null), // empty placeholder
        ];

        if ($request->related_blog_id) {
            $related = Blog::find($request->related_blog_id);
            $data['related_blog_title'] = $related?->title;
        }

        if ($request->hasFile('image')) {
            $data['image'] = $this->buildImageData($request->file('image'), $request->alt_tag);
        }

        $data = $this->attachTranslations($data, new Blog());

        Blog::create($data);

        return redirect()->route('admin.knowledge-hub.blogs.index')
                         ->with('success', 'Blog post created successfully.');
    }

    public function edit($id)
    {
        $record   = Blog::findOrFail($id);
        $allBlogs = Blog::where('_id', '!=', $id)
                        ->orderBy('title')
                        ->get(['_id', 'title']);

        return view('admin.knowledge-hub.blogs.blogs', [
            'mode'     => 'edit',
            'record'   => $record,
            'allBlogs' => $allBlogs,
        ]);
    }

    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        $request->validate([
            'title'           => 'required|string|max:500',
            'alt_tag'         => 'nullable|string|max:255',
            'slug'            => 'nullable|string|max:255',
            'related_blog_id' => 'nullable|string',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'data'            => 'nullable|string',
        ]);

        $data = [
            'title'       => $request->title,
            'alt_tag'     => $request->alt_tag,
            'slug'        => $request->slug
                                ? Str::slug($request->slug)
                                : Str::slug($request->title),
            'related_ids' => $request->related_blog_id
                                ? [$request->related_blog_id]
                                : [],
            'data'        => $request->data,
        ];

        if ($request->related_blog_id) {
            $related = Blog::find($request->related_blog_id);
            $data['related_blog_title'] = $related?->title;
        } else {
            $data['related_blog_title'] = null;
        }

        if ($request->hasFile('image')) {
            // Delete old image file if it exists
            $oldPath = $blog->image['path'] ?? null;
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
            $data['image'] = $this->buildImageData($request->file('image'), $request->alt_tag);
        }

        $data = $this->attachTranslations($data, new Blog());

        $blog->update($data);

        return redirect()->route('admin.knowledge-hub.blogs.index')
                         ->with('success', 'Blog post updated.');
    }

    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);

        $imagePath = $blog->image['path'] ?? null;
        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        $blog->delete();

        return redirect()->route('admin.knowledge-hub.blogs.index')
                         ->with('success', 'Blog post deleted.');
    }

    public function addComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $blog = Blog::findOrFail($id);
        $now  = now()->toDateTimeString();

        $languages    = array_keys(config('languages.available'));
        $translations = [];
        foreach ($languages as $locale) {
            if ($locale === 'en') continue;
            $translations[$locale] = $this->translator->translateText($request->comment, $locale, 'en');
        }

        $newComment = [
            'comment'      => $request->comment,
            'user_id'      => (string) auth()->id(),
            'created_at'   => $now,
            'updated_at'   => $now,
            'translations' => $translations,
        ];

        $comments   = $blog->blog_comments ?? [];
        $comments[] = $newComment;

        $blog->update(['blog_comments' => array_values($comments)]);

        return redirect()->back()->with('success', 'Comment added.');
    }

    public function updateComment(Request $request, $blogId, $commentIndex)
    {
        $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $blog     = Blog::findOrFail($blogId);
        $comments = $blog->blog_comments ?? [];

        if (!isset($comments[$commentIndex])) {
            abort(404, 'Comment not found.');
        }

        $languages    = array_keys(config('languages.available'));
        $translations = [];
        foreach ($languages as $locale) {
            if ($locale === 'en') continue;
            $translations[$locale] = $this->translator->translateText($request->comment, $locale, 'en');
        }

        $comments[$commentIndex]['comment']      = $request->comment;
        $comments[$commentIndex]['translations'] = $translations;
        $comments[$commentIndex]['updated_at']   = now()->toDateTimeString();

        $blog->update(['blog_comments' => array_values($comments)]);

        return redirect()->back()->with('success', 'Comment updated.');
    }

    public function deleteComment(Request $request, $blogId, $commentIndex)
    {
        $blog     = Blog::findOrFail($blogId);
        $comments = $blog->blog_comments ?? [];

        array_splice($comments, $commentIndex, 1);

        $blog->update(['blog_comments' => array_values($comments)]);

        return redirect()->back()->with('success', 'Comment deleted.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Build the image object matching the category_icon_image structure.
     * Pass null $file to get an empty placeholder object.
     */
    private function buildImageData($file, ?string $alt = null): array
    {
        if (!$file) {
            return [
                'size'          => 0,
                'uploaded_at'   => now()->toISOString(),
                'filename'      => '',
                'original_name' => '',
                'path'          => '',
                'url'           => '',
                'mime_type'     => '',
                'alt'           => $alt ?? '',
            ];
        }

        $filename = time() . '_' . $file->getClientOriginalName();
        $path     = $file->storeAs('blogs', $filename, 'public');

        return [
            'size'          => $file->getSize(),
            'uploaded_at'   => now()->toISOString(),
            'filename'      => $filename,
            'original_name' => $file->getClientOriginalName(),
            'path'          => $path,
            'url'           => $path,
            'mime_type'     => $file->getMimeType(),
            'alt'           => $alt ?? '',
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
                    try {
                        $translated[$field] = $this->translator->translateText(
                            $data[$field], $locale, 'en'
                        );
                    } catch (\Exception $e) {
                        // skip failed translations silently
                    }
                }
            }

            if (!empty($translated)) {
                $data[$locale] = $translated;
            }
        }

        return $data;
    }
}