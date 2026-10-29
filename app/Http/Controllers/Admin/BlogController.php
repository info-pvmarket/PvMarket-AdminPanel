<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\TranslationService;
use App\Rules\RichTextImages;
use MongoDB\Driver\Exception\BulkWriteException;
use MongoDB\BSON\Regex;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Jobs\TranslateBlogJob;

class BlogController extends Controller
{
    use \App\Traits\HandlesSeoMeta;

    public function __construct(protected TranslationService $translator) {}

    public function index(Request $request)
    {
        $query = $this->nonFaqBlogsQuery();

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
        $allBlogs = $this->nonFaqBlogsQuery()
                         ->orderBy('title')
                         ->get(['_id', 'title']);

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
            'image'           => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'data'            => ['nullable', 'string', new RichTextImages],
        ] + $this->seoValidationRules(), [
            'image.required' => 'Upload a content-specific cover image before publishing a new blog.',
            'image.max' => 'The blog image must not be larger than 5 MB.',
        ] + $this->seoValidationMessages());

        $slug = $request->slug
            ? Str::slug($request->slug)
            : Str::slug($request->title);

        $this->validateNormalizedSlug($slug);

        if ($this->slugTaken($slug)) {
            return back()->withInput()
                ->withErrors(['slug' => 'A blog post with this slug already exists. Please choose a different title or slug.']);
        }

        $data = [
            'title'         => $request->title,
            'alt_tag'       => $request->alt_tag,
            'slug'          => $slug,
            'slug_normalized' => $slug,
            'slug_routes' => [$slug],
            'translation_revision' => (string) Str::uuid(),
            'related_ids'   => $request->related_blog_id
                                  ? [$request->related_blog_id]
                                  : [],
            'data'          => $request->data,
            'content'       => null,
            'is_faq'        => false,
            'is_active'     => true,
            'blog_comments' => [],
            'image'         => $this->buildImageData(null), // empty placeholder
            'seo'           => $this->buildSeoData($request, null, 'blogs'),
        ];

        if ($request->related_blog_id) {
            $related = Blog::find($request->related_blog_id);
            $data['related_blog_title'] = $related?->title;
        }

        if ($request->hasFile('image')) {
            $data['image'] = $this->buildImageData($request->file('image'), $request->alt_tag);
        }

        try {
            $blog = Blog::create($data);
        } catch (BulkWriteException $e) {
            if (! $this->isDuplicateSlugError($e)) {
                throw $e;
            }

            // A13: the pre-check above lost a race with another request for
            // the same slug (e.g. a delayed Create retried by the user). The
            // blogs_slug_normalized_unique is the authoritative guard; surface it
            // as the same friendly validation error rather than a 500.
            return back()->withInput()
                ->withErrors(['slug' => 'A blog post with this slug already exists. Please choose a different title or slug.']);
        }

        return $this->savedResponse($blog, 'Blog post created successfully.', true);
    }

    public function edit($id)
    {
        $record   = Blog::findOrFail($id);
        $allBlogs = $this->nonFaqBlogsQuery()
                        ->where('_id', '!=', $id)
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
        $oldImage = $blog->image;
        $oldSeo = $this->normalizeSeoArray($blog->seo);

        $request->validate([
            'title'           => 'required|string|max:500',
            'alt_tag'         => 'nullable|string|max:255',
            'slug'            => 'nullable|string|max:255',
            'related_blog_id' => 'nullable|string',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'data'            => ['nullable', 'string', new RichTextImages((string) ($blog->data ?? ''))],
        ] + $this->seoValidationRules(), [
            'image.max' => 'The blog image must not be larger than 5 MB.',
        ] + $this->seoValidationMessages());

        $slug = Str::slug($request->slug ?: $blog->slug ?: $request->title);

        $this->validateNormalizedSlug($slug);

        if ($this->slugTaken($slug, excludeId: $id)) {
            return back()->withInput()
                ->withErrors(['slug' => 'A blog post with this slug already exists. Please choose a different title or slug.']);
        }

        $data = [
            'title'       => $request->title,
            'alt_tag'     => $request->alt_tag,
            'slug'        => $slug,
            'slug_normalized' => $slug,
            'slug_routes' => array_values(array_unique(array_filter(array_merge(
                (array) ($blog->slug_routes ?? []),
                [Str::slug((string) $blog->slug), $slug],
            )))),
            'related_ids' => $request->related_blog_id
                                ? [$request->related_blog_id]
                                : [],
            'data'        => $request->data,
            'seo'         => $this->buildSeoData($request, $blog->seo, 'blogs', deleteReplacedImages: false),
        ];

        if ($slug !== (string) $blog->slug && ! empty($data['seo']['canonical_url'])) {
            $oldPath = '/blogs/'.(string) $blog->slug;
            $canonicalPath = rtrim((string) parse_url($data['seo']['canonical_url'], PHP_URL_PATH), '/');
            if ((string) $blog->slug !== '' && str_ends_with($canonicalPath, $oldPath)) {
                $data['seo']['canonical_url'] = Str::replaceLast($oldPath, '/blogs/'.$slug, $data['seo']['canonical_url']);
            }
        }

        if ($request->related_blog_id) {
            $related = Blog::find($request->related_blog_id);
            $data['related_blog_title'] = $related?->title;
        } else {
            $data['related_blog_title'] = null;
        }

        if ($request->hasFile('image')) {
            $data['image'] = $this->buildImageData($request->file('image'), $request->alt_tag);
        }

        $blog->fill($data);
        $translate = $blog->isDirty(['title', 'data']);
        if ($translate) $blog->translation_revision = (string) Str::uuid();

        try {
            $blog->save();
        } catch (BulkWriteException $e) {
            if (! $this->isDuplicateSlugError($e)) {
                throw $e;
            }

            return back()->withInput()
                ->withErrors(['slug' => 'A blog post with this slug already exists. Please choose a different title or slug.']);
        }

        // A concurrent slug conflict must not delete images still referenced
        // by the unchanged record. Cleanup happens only after a successful save.
        try {
            if ($request->hasFile('image') && ! empty($oldImage['path'])) {
                Storage::disk(($oldImage['disk'] ?? null) === 'r2' ? 'r2' : 'public')->delete($oldImage['path']);
            }
            if (($request->hasFile('seo_og_image') || $request->boolean('seo_og_image_remove'))
                && ! empty($oldSeo['og_image']['path'])) {
                Storage::disk('public')->delete($oldSeo['og_image']['path']);
            }
        } catch (\Throwable $exception) {
            Log::warning('Blog saved but replaced image cleanup failed.', ['blog_id' => (string) $blog->getKey()]);
        }

        return $this->savedResponse($blog, 'Blog post updated.', $translate);
    }

    /**
     * A13: application-level check so a plain duplicate slug gets a clear
     * validation message instead of reaching the database at all. This can
     * still race with a concurrent request for the same slug; the
     * blogs_slug_normalized_unique index (see migrations) and the BulkWriteException
     * handling around Blog::create()/update() are the authoritative guard
     * for that case.
     */
    private function slugTaken(string $slug, ?string $excludeId = null): bool
    {
        if ($slug === '29') return true; // Reserved historical Türkiye address.
        if ($slug === '') {
            return false;
        }

        $query = Blog::whereRaw(['$or' => [
            ['slug_normalized' => $slug],
            ['slug_routes' => $slug],
            ['slug' => new Regex('^'.preg_quote($slug, '/').'$', 'i')],
        ]]);

        if ($excludeId !== null) {
            $query->where('_id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * True when a BulkWriteException is a MongoDB duplicate-key error
     * (code 11000) on the blogs_slug_normalized_unique index, rather than some other
     * write failure that should keep propagating as a real error.
     */
    private function isDuplicateSlugError(BulkWriteException $e): bool
    {
        $writeResult = $e->getWriteResult();
        foreach ($writeResult?->getWriteErrors() ?? [] as $writeError) {
            if ($writeError->getCode() === 11000
                && (str_contains($writeError->getMessage(), 'blogs_slug_normalized_unique')
                    || str_contains($writeError->getMessage(), 'blogs_slug_routes_unique'))) {
                return true;
            }
        }

        return false;
    }

    private function validateNormalizedSlug(string $slug): void
    {
        if ($slug === '' || strlen($slug) > 255) {
            throw ValidationException::withMessages(['slug' => 'Provide a title or slug that produces a non-empty URL slug of at most 255 characters.']);
        }
    }

    private function savedResponse(Blog $blog, string $message, bool $translate)
    {
        $response = redirect()->route('admin.knowledge-hub.blogs.index')->with('success', $message);
        if (! $translate) return $response;

        try {
            foreach (array_keys(config('languages.available', [])) as $locale) {
                if ($locale === 'en') continue;
                TranslateBlogJob::dispatch((string) $blog->getKey(), $blog->translation_revision, $locale);
            }
        } catch (\Throwable $exception) {
            // The source is already saved: report that fact rather than invite
            // another Create attempt. Never include service credentials in logs.
            Log::warning('Blog saved but translations could not be queued.', ['blog_id' => (string) $blog->getKey()]);
            return $response->with('warning', 'Your blog is saved, but background translations could not all be queued. Contact support; do not create another copy.');
        }

        return $response->with('success', $message.' Translations will update in the background.');
    }

    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);

        $imagePath = $blog->image['path'] ?? null;
        if ($imagePath) {
            Storage::disk(($blog->image['disk'] ?? null) === 'r2' ? 'r2' : 'public')->delete($imagePath);
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
        $path     = $file->storeAs('blogs', $filename, 'r2');

        abort_unless(is_string($path) && $path !== '', 503, 'Image upload failed. Please try again.');

        return [
            'disk'          => 'r2',
            'size'          => $file->getSize(),
            'uploaded_at'   => now()->toISOString(),
            'filename'      => $filename,
            'original_name' => $file->getClientOriginalName(),
            'path'          => $path,
            'url'           => Storage::disk('r2')->url($path),
            'mime_type'     => $file->getMimeType(),
            'alt'           => $alt ?? '',
        ];
    }

    private function nonFaqBlogsQuery()
    {
        return Blog::query()
            ->where(function ($query) {
                $query->where('is_faq', false)
                      ->orWhereNull('is_faq')
                      ->orWhereRaw(['is_faq' => ['$exists' => false]]);
            });
    }

}
