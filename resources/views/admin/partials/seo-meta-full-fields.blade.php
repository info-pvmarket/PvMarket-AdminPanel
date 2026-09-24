{{--
    Full "SEO Meta" form block, for the Static Pages screen.

    Expects:
      $seoRecord      - App\Models\SeoMetaData or null when none exists yet
      $seoPreviewPath - storefront path for the canonical hint, e.g. "/about-us"

    Styling: this view (page-sections/edit) uses .section-card / .fields-grid /
    .full-width / .field-label / .field-input - NOT the .form-input / .form-group
    vocabulary of the SEO Meta screen. It also has no hint or error classes, so
    those are defined here, prefixed `smf-`.

    Every field is optional. Leave the block empty and no record is written, so
    the storefront keeps its own fallback title and description.
--}}
@php
    $og      = $seoRecord?->ogMeta;
    $twitter = $seoRecord?->twitterMeta;
    $robots  = $seoRecord?->robotMeta;

    // A page with no record yet should default to indexable / followable.
    $smfIndex  = old('robot_index',  $robots->index  ?? true);
    $smfFollow = old('robot_follow', $robots->follow ?? true);
    $smfPath   = $seoPreviewPath ?? '/';
@endphp

<style>
    .smf-head { display:flex; align-items:center; justify-content:space-between; cursor:pointer; user-select:none; }
    .smf-head h2 { font-size:15px; font-weight:700; margin:0; }
    .smf-toggle { font-size:18px; color:var(--muted); font-weight:300; width:22px; text-align:center; }
    .smf-note { font-size:12px; color:var(--muted); line-height:1.6; margin:12px 0 18px; }
    .smf-group { font-size:11.5px; font-weight:700; color:var(--primary); text-transform:uppercase; letter-spacing:.5px; margin:24px 0 12px; padding-bottom:6px; border-bottom:1px solid var(--border); }
    .smf-group.first { margin-top:0; }
    .smf-hint { display:block; font-size:11px; color:var(--muted); margin-top:4px; }
    .smf-error { display:block; font-size:11.5px; color:var(--danger); font-weight:600; margin-top:4px; }
    .smf-counter { float:right; font-size:11px; color:var(--muted); font-weight:600; }
    .smf-counter.over { color:var(--danger); }
    .smf-checks { display:grid; grid-template-columns:repeat(3, minmax(160px, 1fr)); gap:10px 18px; }
    .smf-check { display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer; }
    .smf-check input { width:16px; height:16px; accent-color:var(--primary); cursor:pointer; }
    .smf-images { display:flex; flex-wrap:wrap; gap:10px; margin-top:10px; }
    .smf-image { position:relative; }
    .smf-image img { height:58px; border-radius:6px; border:1px solid var(--border); object-fit:cover; display:block; }
    .smf-image button { position:absolute; top:-6px; right:-6px; width:20px; height:20px; border-radius:50%; border:none; background:var(--danger); color:white; font-size:12px; line-height:1; cursor:pointer; }
    .smf-image.removing img { opacity:.35; }
    .smf-quill { border:1px solid var(--border); border-radius:8px; overflow:hidden; background:white; }
    @media (max-width: 900px) { .smf-checks { grid-template-columns:1fr 1fr; } }
</style>

<div class="section-card">
    <div class="smf-head" id="smfHead">
        <h2>SEO Tags</h2>
        <span class="smf-toggle" id="smfToggle">&minus;</span>
    </div>

    <div id="smfBody">
        <p class="smf-note">
            All fields are optional &mdash; leave the block empty and this page keeps the
            site-wide default title and description. Anything you set here is rendered into
            the page's <code>&lt;head&gt;</code> server-side, so search engines and social
            previews pick it up.
        </p>

        {{-- ─────────────── Search engine ─────────────── --}}
        <div class="smf-group first">Search engine</div>

        <div class="fields-grid">
            <div class="full-width">
                <label class="field-label">
                    Meta Title
                    <span class="smf-counter" data-smf-counter-for="meta_title" data-smf-limit="60"></span>
                </label>
                <input type="text" class="field-input" name="meta_title" maxlength="255"
                       placeholder="e.g. About PV.market | B2B Solar Marketplace"
                       value="{{ old('meta_title', $seoRecord->meta_title ?? '') }}">
                <span class="smf-hint">Around 60 characters shows in full on Google.</span>
                @error('meta_title')<span class="smf-error">{{ $message }}</span>@enderror
            </div>

            <div class="full-width">
                <label class="field-label">
                    Meta Description
                    <span class="smf-counter" data-smf-counter-for="meta_description" data-smf-limit="160"></span>
                </label>
                <textarea class="field-input" name="meta_description" rows="3" maxlength="500"
                          style="resize:vertical;"
                          placeholder="One or two sentences describing this page">{{ old('meta_description', $seoRecord->meta_description ?? '') }}</textarea>
                <span class="smf-hint">Around 160 characters shows in full on Google.</span>
                @error('meta_description')<span class="smf-error">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="field-label">Meta Keywords</label>
                <input type="text" class="field-input" name="meta_keywords" maxlength="500"
                       placeholder="solar marketplace, pv modules, b2b"
                       value="{{ old('meta_keywords', $seoRecord->meta_keywords ?? '') }}">
                <span class="smf-hint">Comma separated.</span>
                @error('meta_keywords')<span class="smf-error">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="field-label">Canonical URL</label>
                <input type="text" class="field-input" name="canonical_url" maxlength="500"
                       placeholder="https://pv.market{{ $smfPath }}"
                       value="{{ old('canonical_url', $seoRecord->canonical_url ?? '') }}">
                <span class="smf-hint">Only set this to point at a different page.</span>
                @error('canonical_url')<span class="smf-error">{{ $message }}</span>@enderror
            </div>
        </div>

        {{-- ─────────────── On-page copy ─────────────── --}}
        <div class="smf-group">On-page copy</div>

        <div class="fields-grid">
            <div>
                <label class="field-label">Page Header (H1)</label>
                <input type="text" class="field-input" name="page_header" maxlength="255"
                       placeholder="Overrides the page's visible heading"
                       value="{{ old('page_header', $seoRecord->page_header ?? '') }}">
                @error('page_header')<span class="smf-error">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="field-label">Bottom Header</label>
                <input type="text" class="field-input" name="bottom_header" maxlength="255"
                       placeholder="Heading above the footer copy"
                       value="{{ old('bottom_header', $seoRecord->bottom_header ?? '') }}">
                @error('bottom_header')<span class="smf-error">{{ $message }}</span>@enderror
            </div>

            <div class="full-width">
                <label class="field-label">Short Description (intro)</label>
                <textarea class="field-input" name="short_description" rows="2" style="resize:vertical;"
                          placeholder="Intro paragraph shown under the heading">{{ old('short_description', $seoRecord->short_description ?? '') }}</textarea>
                @error('short_description')<span class="smf-error">{{ $message }}</span>@enderror
            </div>

            <div class="full-width">
                <label class="field-label">Bottom Description</label>
                <textarea name="bottom_description" id="smfBottomInput"
                          style="display:none;">{{ old('bottom_description', $seoRecord->bottom_description ?? '') }}</textarea>
                <div class="smf-quill"><div id="smfBottomEditor"></div></div>
                <span class="smf-hint">Long-form copy rendered at the bottom of the page.</span>
                @error('bottom_description')<span class="smf-error">{{ $message }}</span>@enderror
            </div>
        </div>

        {{-- ─────────────── Open Graph ─────────────── --}}
        <div class="smf-group">Social share &mdash; Facebook, LinkedIn, WhatsApp (Open Graph)</div>

        <div class="fields-grid">
            <div>
                <label class="field-label">OG Title</label>
                <input type="text" class="field-input" name="og_title" maxlength="255"
                       placeholder="Defaults to the meta title"
                       value="{{ old('og_title', $og->og_title ?? '') }}">
                @error('og_title')<span class="smf-error">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="field-label">OG Type</label>
                @php $smfOgType = old('og_type', $og->og_type ?? 'website'); @endphp
                <select class="field-input" name="og_type">
                    <option value="website" {{ $smfOgType === 'website' ? 'selected' : '' }}>Website</option>
                    <option value="article" {{ $smfOgType === 'article' ? 'selected' : '' }}>Article</option>
                    <option value="product" {{ $smfOgType === 'product' ? 'selected' : '' }}>Product</option>
                </select>
            </div>

            <div class="full-width">
                <label class="field-label">OG Description</label>
                <textarea class="field-input" name="og_description" rows="2" maxlength="500"
                          style="resize:vertical;"
                          placeholder="Defaults to the meta description">{{ old('og_description', $og->og_description ?? '') }}</textarea>
                @error('og_description')<span class="smf-error">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="field-label">OG Site Name</label>
                <input type="text" class="field-input" name="og_site_name" maxlength="255"
                       value="{{ old('og_site_name', $og->og_site_name ?? 'PV Market') }}">
            </div>

            <div>
                <label class="field-label">OG Locale</label>
                <input type="text" class="field-input" name="og_locale" maxlength="20"
                       value="{{ old('og_locale', $og->og_locale ?? 'en_US') }}">
            </div>

            <div class="full-width">
                <label class="field-label">OG URL</label>
                <input type="text" class="field-input" name="og_url" maxlength="500"
                       placeholder="https://pv.market{{ $smfPath }}"
                       value="{{ old('og_url', $og->og_url ?? '') }}">
                @error('og_url')<span class="smf-error">{{ $message }}</span>@enderror
            </div>

            <div class="full-width">
                <label class="field-label">Share Images</label>
                <input type="file" class="field-input" name="og_images[]" accept="image/*" multiple>
                <span class="smf-hint">1200 &times; 630 works best. You can select more than one.</span>
                @error('og_images.*')<span class="smf-error">{{ $message }}</span>@enderror

                @if($og && $og->images->count() > 0)
                    <div class="smf-images">
                        @foreach($og->images as $image)
                            <div class="smf-image" data-smf-image>
                                <img src="{{ asset('storage/' . $image->image_url) }}"
                                     alt="{{ $image->image_alt ?? 'Open Graph image' }}">
                                <button type="button" title="Remove this image"
                                        onclick="smfMarkImageForDeletion(this)">&times;</button>
                                <input type="hidden" name="delete_og_images[]"
                                       value="{{ $image->id }}" disabled>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ─────────────── Twitter ─────────────── --}}
        <div class="smf-group">Social share &mdash; X / Twitter</div>

        <div class="fields-grid">
            <div>
                <label class="field-label">Card Type</label>
                @php $smfCard = old('twitter_card', $twitter->twitter_card ?? 'summary_large_image'); @endphp
                <select class="field-input" name="twitter_card">
                    <option value="summary_large_image" {{ $smfCard === 'summary_large_image' ? 'selected' : '' }}>Large image</option>
                    <option value="summary" {{ $smfCard === 'summary' ? 'selected' : '' }}>Summary (small image)</option>
                    <option value="app" {{ $smfCard === 'app' ? 'selected' : '' }}>App</option>
                    <option value="player" {{ $smfCard === 'player' ? 'selected' : '' }}>Player</option>
                </select>
            </div>

            <div>
                <label class="field-label">Twitter Title</label>
                <input type="text" class="field-input" name="twitter_title" maxlength="255"
                       placeholder="Defaults to the OG title"
                       value="{{ old('twitter_title', $twitter->twitter_title ?? '') }}">
                @error('twitter_title')<span class="smf-error">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="field-label">Twitter Site (&#64;handle)</label>
                <input type="text" class="field-input" name="twitter_site" maxlength="255"
                       placeholder="&#64;pvmarket"
                       value="{{ old('twitter_site', $twitter->twitter_site ?? '') }}">
            </div>

            <div>
                <label class="field-label">Twitter Creator (&#64;handle)</label>
                <input type="text" class="field-input" name="twitter_creator" maxlength="255"
                       placeholder="&#64;author"
                       value="{{ old('twitter_creator', $twitter->twitter_creator ?? '') }}">
            </div>

            <div class="full-width">
                <label class="field-label">Twitter Description</label>
                <textarea class="field-input" name="twitter_description" rows="2" maxlength="500"
                          style="resize:vertical;"
                          placeholder="Defaults to the OG description">{{ old('twitter_description', $twitter->twitter_description ?? '') }}</textarea>
                @error('twitter_description')<span class="smf-error">{{ $message }}</span>@enderror
            </div>

            <div class="full-width">
                <label class="field-label">Twitter Images</label>
                <input type="file" class="field-input" name="twitter_images[]" accept="image/*" multiple>
                <span class="smf-hint">Leave empty to reuse the Open Graph image.</span>
                @error('twitter_images.*')<span class="smf-error">{{ $message }}</span>@enderror

                @if($twitter && $twitter->images->count() > 0)
                    <div class="smf-images">
                        @foreach($twitter->images as $image)
                            <div class="smf-image" data-smf-image>
                                <img src="{{ asset('storage/' . $image->image_url) }}"
                                     alt="{{ $image->image_alt ?? 'Twitter image' }}">
                                <button type="button" title="Remove this image"
                                        onclick="smfMarkImageForDeletion(this)">&times;</button>
                                <input type="hidden" name="delete_twitter_images[]"
                                       value="{{ $image->id }}" disabled>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ─────────────── Robots ─────────────── --}}
        <div class="smf-group">Search engine crawling</div>

        <div class="smf-checks" style="margin-bottom:18px;">
            {{-- Each checkbox pairs with a hidden 0 so an unticked box still posts. --}}
            <label class="smf-check">
                <input type="hidden" name="robot_index" value="0">
                <input type="checkbox" name="robot_index" value="1" {{ $smfIndex ? 'checked' : '' }}>
                Index this page
            </label>
            <label class="smf-check">
                <input type="hidden" name="robot_follow" value="0">
                <input type="checkbox" name="robot_follow" value="1" {{ $smfFollow ? 'checked' : '' }}>
                Follow its links
            </label>
            <label class="smf-check">
                <input type="hidden" name="robot_noarchive" value="0">
                <input type="checkbox" name="robot_noarchive" value="1"
                       {{ old('robot_noarchive', $robots->noarchive ?? false) ? 'checked' : '' }}>
                No archive
            </label>
            <label class="smf-check">
                <input type="hidden" name="robot_nosnippet" value="0">
                <input type="checkbox" name="robot_nosnippet" value="1"
                       {{ old('robot_nosnippet', $robots->nosnippet ?? false) ? 'checked' : '' }}>
                No snippet
            </label>
            <label class="smf-check">
                <input type="hidden" name="robot_noimageindex" value="0">
                <input type="checkbox" name="robot_noimageindex" value="1"
                       {{ old('robot_noimageindex', $robots->noimageindex ?? false) ? 'checked' : '' }}>
                No image index
            </label>
            <label class="smf-check">
                <input type="hidden" name="robot_nocache" value="0">
                <input type="checkbox" name="robot_nocache" value="1"
                       {{ old('robot_nocache', $robots->nocache ?? false) ? 'checked' : '' }}>
                No cache
            </label>
        </div>

        <div class="fields-grid">
            <div>
                <label class="field-label">Max Snippet</label>
                <input type="number" class="field-input" name="max_snippet" min="-1"
                       placeholder="-1 for unlimited"
                       value="{{ old('max_snippet', $robots->max_snippet ?? '') }}">
                @error('max_snippet')<span class="smf-error">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="field-label">Max Image Preview</label>
                @php $smfPreview = old('max_image_preview', $robots->max_image_preview ?? 'large'); @endphp
                <select class="field-input" name="max_image_preview">
                    <option value="large"    {{ $smfPreview === 'large' ? 'selected' : '' }}>Large</option>
                    <option value="standard" {{ $smfPreview === 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="none"     {{ $smfPreview === 'none' ? 'selected' : '' }}>None</option>
                </select>
            </div>

            <div>
                <label class="field-label">Max Video Preview</label>
                <input type="number" class="field-input" name="max_video_preview" min="-1"
                       placeholder="-1 for unlimited"
                       value="{{ old('max_video_preview', $robots->max_video_preview ?? '') }}">
                @error('max_video_preview')<span class="smf-error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>
</div>

<script>
// Mark an existing share image for deletion: enable its hidden input so the id posts.
function smfMarkImageForDeletion(button) {
    var wrapper = button.closest('[data-smf-image]');
    if (!wrapper) return;

    var input = wrapper.querySelector('input[type="hidden"]');
    if (!input) return;

    var removing = wrapper.classList.toggle('removing');
    input.disabled = !removing;
    button.title = removing ? 'Keep this image' : 'Remove this image';
}

(function () {
    // Collapse / expand.
    var head = document.getElementById('smfHead');
    var body = document.getElementById('smfBody');
    var toggle = document.getElementById('smfToggle');

    if (head && body && toggle) {
        head.addEventListener('click', function () {
            var hidden = body.style.display === 'none';
            body.style.display = hidden ? 'block' : 'none';
            toggle.innerHTML = hidden ? '&minus;' : '+';
        });
    }

    // Live counters on the two length-sensitive fields.
    document.querySelectorAll('[data-smf-counter-for]').forEach(function (counter) {
        var field = document.querySelector('[name="' + counter.dataset.smfCounterFor + '"]');
        if (!field) return;

        var limit = parseInt(counter.dataset.smfLimit, 10);
        var update = function () {
            var length = field.value.length;
            counter.textContent = length + ' / ' + limit;
            counter.classList.toggle('over', length > limit);
        };

        field.addEventListener('input', update);
        update();
    });

    // Rich text for the bottom copy. window.initQuill is defined by the inline
    // script after the form, which has already run by DOMContentLoaded.
    document.addEventListener('DOMContentLoaded', function () {
        var editor = document.getElementById('smfBottomEditor');
        var input = document.getElementById('smfBottomInput');

        if (editor && input && typeof window.initQuill === 'function') {
            window.initQuill(editor, input, [
                [{ header: [2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link'],
                ['clean'],
            ]);
        }
    });
})();
</script>
