{{--
    Shared "SEO Tags" block for the Knowledge Hub forms (blogs, news, events).

    Expects:
      $record          - the model being edited, or null on create
      $seoPreviewPath  - optional storefront path prefix for the canonical hint,
                         e.g. "blogs" / "news" / "events"

    Self-contained on purpose: the three Knowledge Hub forms do not share a
    section/grid convention, so everything here is prefixed `seo-` and only
    relies on the form classes all three already define (.form-group,
    .form-label, .form-input, .form-select, .form-file-wrap, .form-hint).

    Every field is optional. Left blank, the storefront falls back to the
    record's own title, content and main image.
--}}
@php
    $seo = $record->seo ?? null;

    if (is_object($seo) && method_exists($seo, 'getArrayCopy')) {
        $seo = $seo->getArrayCopy();
    } elseif (is_object($seo)) {
        $seo = (array) $seo;
    } elseif (is_string($seo)) {
        $decoded = json_decode($seo, true);
        $seo = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
    }

    $seo = is_array($seo) ? $seo : [];

    $seoOgImage = $seo['og_image'] ?? null;
    if (is_object($seoOgImage) && method_exists($seoOgImage, 'getArrayCopy')) {
        $seoOgImage = $seoOgImage->getArrayCopy();
    } elseif (is_object($seoOgImage)) {
        $seoOgImage = (array) $seoOgImage;
    }
    $seoOgImage = is_array($seoOgImage) ? $seoOgImage : null;

    $seoOgImageUrl = null;
    if ($seoOgImage) {
        $rawSeoImage = $seoOgImage['url'] ?? $seoOgImage['path'] ?? null;
        if (is_string($rawSeoImage) && trim($rawSeoImage) !== '') {
            $seoOgImageUrl = preg_match('/^https?:\/\//i', $rawSeoImage)
                ? $rawSeoImage
                : \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($rawSeoImage, '/'));
        }
    }

    // A brand new record defaults to indexable / followable.
    $seoRobotsIndex  = old('seo_robots_index', array_key_exists('robots_index', $seo) ? (bool) $seo['robots_index'] : true);
    $seoRobotsFollow = old('seo_robots_follow', array_key_exists('robots_follow', $seo) ? (bool) $seo['robots_follow'] : true);
    $seoTwitterCard  = old('seo_twitter_card', $seo['twitter_card'] ?? 'summary_large_image');
    $seoPathPrefix   = $seoPreviewPath ?? 'blogs';
@endphp

<style>
    .seo-block { margin-top:4px; }
    .seo-head { display:flex; align-items:center; justify-content:space-between; cursor:pointer; user-select:none; padding:2px 0; }
    .seo-head-title { font-size:16px; font-weight:800; color:var(--primary-d); }
    .seo-head-toggle { width:24px; height:24px; display:flex; align-items:center; justify-content:center; font-size:18px; color:var(--muted); font-weight:300; }
    .seo-panel { border:1.5px solid var(--border); border-radius:10px; padding:20px; background:#FAFBFD; margin-top:12px; }
    .seo-note { font-size:12px; color:var(--muted); margin:0 0 18px; line-height:1.6; }
    .seo-subhead { font-size:11.5px; font-weight:700; color:var(--primary-d); text-transform:uppercase; letter-spacing:.5px; margin:24px 0 14px; padding-bottom:6px; border-bottom:1px solid var(--border); }
    .seo-subhead.first { margin-top:0; }
    .seo-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px 24px; margin-bottom:18px; }
    .seo-field { margin-bottom:18px; }
    .seo-textarea { resize:vertical; min-height:60px; line-height:1.6; }
    .seo-label-row { display:flex; align-items:baseline; justify-content:space-between; gap:10px; }
    .seo-counter { font-size:11px; color:var(--muted); font-weight:600; white-space:nowrap; }
    .seo-counter.over { color:var(--danger); }
    .seo-error { color:var(--danger); font-size:11.5px; font-weight:600; margin-top:3px; }
    .seo-og-current { display:flex; align-items:center; gap:12px; margin-top:10px; flex-wrap:wrap; }
    .seo-og-current img { height:56px; border-radius:6px; border:1px solid var(--border); object-fit:cover; }
    .seo-remove-row { display:flex; align-items:center; gap:8px; font-size:12px; color:var(--muted); }
    .seo-remove-row input[type="checkbox"] { width:15px; height:15px; accent-color:var(--danger); cursor:pointer; }
    .seo-toggle-row { display:flex; gap:28px; flex-wrap:wrap; }
    .seo-toggle { display:flex; align-items:center; gap:9px; }
    .seo-toggle input[type="checkbox"] { width:17px; height:17px; accent-color:var(--primary); cursor:pointer; }
    .seo-toggle label { font-size:13.5px; font-weight:500; color:var(--text); cursor:pointer; margin:0; }
    @media (max-width: 900px) { .seo-grid { grid-template-columns:1fr; } }
</style>

<div class="seo-block">
    <div class="seo-head" id="seoSectionHead">
        <div class="seo-head-title">SEO Tags</div>
        <div class="seo-head-toggle" id="seoSectionToggle">&minus;</div>
    </div>

    <div id="seoSectionBody">
        <div class="seo-panel">
            <p class="seo-note">
                All fields are optional. Anything left blank falls back automatically &mdash; the page
                title, the opening of the content, and the main image. Fill these in only to override
                what the storefront would otherwise generate.
            </p>

            <div class="seo-subhead first">Search engine</div>

            <div class="form-group seo-field">
                <div class="seo-label-row">
                    <label class="form-label">Meta Title</label>
                    <span class="seo-counter" data-seo-counter-for="seo_meta_title" data-seo-limit="60"></span>
                </div>
                <input type="text" name="seo_meta_title" class="form-input" maxlength="255"
                       placeholder="Defaults to the page title"
                       value="{{ old('seo_meta_title', $seo['meta_title'] ?? '') }}"/>
                <span class="form-hint">Roughly 60 characters shows in full on Google.</span>
                @error('seo_meta_title')<span class="seo-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group seo-field">
                <div class="seo-label-row">
                    <label class="form-label">Meta Description</label>
                    <span class="seo-counter" data-seo-counter-for="seo_meta_description" data-seo-limit="160"></span>
                </div>
                <textarea name="seo_meta_description" class="form-input seo-textarea" rows="3" maxlength="500"
                          placeholder="Defaults to the opening of the content">{{ old('seo_meta_description', $seo['meta_description'] ?? '') }}</textarea>
                <span class="form-hint">Roughly 160 characters shows in full on Google.</span>
                @error('seo_meta_description')<span class="seo-error">{{ $message }}</span>@enderror
            </div>

            <div class="seo-grid">
                <div class="form-group">
                    <label class="form-label">Meta Keywords</label>
                    <input type="text" name="seo_meta_keywords" class="form-input" maxlength="500"
                           placeholder="solar panels, pv modules, bifacial"
                           value="{{ old('seo_meta_keywords', $seo['meta_keywords'] ?? '') }}"/>
                    <span class="form-hint">Comma separated.</span>
                    @error('seo_meta_keywords')<span class="seo-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Canonical URL</label>
                    <input type="text" name="seo_canonical_url" class="form-input" maxlength="2048"
                           placeholder="https://pv.market/{{ $seoPathPrefix }}/your-slug"
                           value="{{ old('seo_canonical_url', $seo['canonical_url'] ?? '') }}"/>
                    <span class="form-hint">Only set this to point at a different page.</span>
                    @error('seo_canonical_url')<span class="seo-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="seo-subhead">Social share &mdash; Facebook, LinkedIn, WhatsApp (Open Graph)</div>

            <div class="seo-grid">
                <div class="form-group">
                    <label class="form-label">OG Title</label>
                    <input type="text" name="seo_og_title" class="form-input" maxlength="255"
                           placeholder="Defaults to the meta title"
                           value="{{ old('seo_og_title', $seo['og_title'] ?? '') }}"/>
                    @error('seo_og_title')<span class="seo-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Share Image Alt Text</label>
                    <input type="text" name="seo_og_image_alt" class="form-input" maxlength="255"
                           placeholder="Describe the share image"
                           value="{{ old('seo_og_image_alt', $seoOgImage['alt'] ?? '') }}"/>
                </div>
            </div>

            <div class="form-group seo-field">
                <label class="form-label">OG Description</label>
                <textarea name="seo_og_description" class="form-input seo-textarea" rows="2" maxlength="500"
                          placeholder="Defaults to the meta description">{{ old('seo_og_description', $seo['og_description'] ?? '') }}</textarea>
                @error('seo_og_description')<span class="seo-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group seo-field">
                <label class="form-label">Social Share Image</label>
                <div class="form-file-wrap">
                    <input type="file" name="seo_og_image" accept=".jpg,.jpeg,.png,.webp"/>
                </div>
                <span class="form-hint">1200 &times; 630 works best. Leave empty to share the main image.</span>
                @error('seo_og_image')<span class="seo-error">{{ $message }}</span>@enderror

                @if($seoOgImageUrl)
                    <div class="seo-og-current">
                        <img src="{{ $seoOgImageUrl }}" alt="{{ $seoOgImage['alt'] ?? 'Current social share image' }}"/>
                        <div class="seo-remove-row">
                            <input type="checkbox" name="seo_og_image_remove" id="seoOgImageRemove" value="1"/>
                            <label for="seoOgImageRemove">Remove this image</label>
                        </div>
                    </div>
                @endif
            </div>

            <div class="seo-subhead">Social share &mdash; X / Twitter</div>

            <div class="seo-grid">
                <div class="form-group">
                    <label class="form-label">Card Type</label>
                    <select name="seo_twitter_card" class="form-select">
                        <option value="summary_large_image" {{ $seoTwitterCard === 'summary_large_image' ? 'selected' : '' }}>
                            Large image
                        </option>
                        <option value="summary" {{ $seoTwitterCard === 'summary' ? 'selected' : '' }}>
                            Summary (small image)
                        </option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Twitter Title</label>
                    <input type="text" name="seo_twitter_title" class="form-input" maxlength="255"
                           placeholder="Defaults to the OG title"
                           value="{{ old('seo_twitter_title', $seo['twitter_title'] ?? '') }}"/>
                    @error('seo_twitter_title')<span class="seo-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-group seo-field">
                <label class="form-label">Twitter Description</label>
                <textarea name="seo_twitter_description" class="form-input seo-textarea" rows="2" maxlength="500"
                          placeholder="Defaults to the OG description">{{ old('seo_twitter_description', $seo['twitter_description'] ?? '') }}</textarea>
                @error('seo_twitter_description')<span class="seo-error">{{ $message }}</span>@enderror
            </div>

            <div class="seo-subhead">Search engine crawling</div>

            <div class="seo-toggle-row">
                <div class="seo-toggle">
                    <input type="checkbox" name="seo_robots_index" id="seoRobotsIndex" value="1"
                           {{ $seoRobotsIndex ? 'checked' : '' }}/>
                    <label for="seoRobotsIndex">Allow search engines to index this page</label>
                </div>
                <div class="seo-toggle">
                    <input type="checkbox" name="seo_robots_follow" id="seoRobotsFollow" value="1"
                           {{ $seoRobotsFollow ? 'checked' : '' }}/>
                    <label for="seoRobotsFollow">Allow search engines to follow its links</label>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    // Collapse / expand, self-contained so it does not depend on a page-level
    // toggleSection() helper that these forms do not have.
    var head   = document.getElementById('seoSectionHead');
    var body   = document.getElementById('seoSectionBody');
    var toggle = document.getElementById('seoSectionToggle');

    if (head && body && toggle) {
        head.addEventListener('click', function () {
            var hidden = body.style.display === 'none';
            body.style.display = hidden ? 'block' : 'none';
            toggle.innerHTML = hidden ? '&minus;' : '+';
        });
    }

    // Live character counters for the two length-sensitive fields.
    document.querySelectorAll('[data-seo-counter-for]').forEach(function (counter) {
        var field = document.querySelector('[name="' + counter.dataset.seoCounterFor + '"]');
        if (!field) return;

        var limit  = parseInt(counter.dataset.seoLimit, 10);
        var update = function () {
            var len = field.value.length;
            counter.textContent = len + ' / ' + limit;
            counter.classList.toggle('over', len > limit);
        };

        field.addEventListener('input', update);
        update();
    });
})();
</script>
