{{-- views/admin/seo-meta/seo-meta.blade.php --}}
{{-- Handles: index | create | edit --}}

@if($mode === 'create')
@extends('layouts.admin')

{{-- ═══ CREATE MODE ═══ --}}
@section('title', 'Add SEO Meta')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-quill.css') }}">
<style>
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .section-divider { border:none; border-top:2px solid #E2E8F0; margin:28px 0; }
    .section-title { font-size:15px; font-weight:700; color:var(--primary-d); margin-bottom:20px; text-transform:uppercase; letter-spacing:.5px; display:flex; align-items:center; gap:8px; }
    .section-title svg { width:18px; height:18px; }
    .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:20px 28px; margin-bottom:20px; }
    .form-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px 28px; margin-bottom:20px; }
    .form-grid-4 { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:20px 28px; margin-bottom:20px; }
    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-group.full-width { grid-column:1/-1; }
    .form-label { font-size:13px; font-weight:600; color:var(--text); }
    .form-label span { color:var(--danger); margin-left:2px; }
    .form-input { width:100%; padding:9px 13px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; color:var(--text); outline:none; transition:border-color .2s,box-shadow .2s; background:white; box-sizing:border-box; }
    .form-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .form-input::placeholder { color:#CBD5E1; }
    .form-textarea { min-height:100px; resize:vertical; }
    .form-select { cursor:pointer; }
    .btn-save { display:inline-flex; align-items:center; gap:8px; padding:10px 28px; background:#10B981; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; transition:background .15s; }
    .btn-save:hover { background:#059669; }
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; white-space:nowrap; border:1.5px solid var(--text); }
    .alert-error { padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px; }
    .checkbox-group { display:flex; align-items:center; gap:10px; padding:8px 0; }
    .checkbox-group input[type="checkbox"] { width:18px; height:18px; cursor:pointer; }
    .checkbox-group label { font-size:13px; color:var(--text); cursor:pointer; }
    .help-text { font-size:11px; color:var(--muted); margin-top:4px; }
    .entity-badge { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:20px; font-size:12px; font-weight:600; background:#F0F9FF; color:var(--primary-d); border:1px solid #BAE6FD; }
    .quill-wrapper { border:1.5px solid var(--border); border-radius:8px; overflow:hidden; background:white; }
    .quill-wrapper:focus-within { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .quill-wrapper .ql-toolbar.ql-snow { border:none; border-bottom:1px solid var(--border); background:#F8FAFC; }
    .quill-wrapper .ql-container.ql-snow { border:none; font-family:inherit; font-size:13.5px; }
    .quill-wrapper .ql-editor { min-height:180px; color:var(--text); line-height:1.7; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Add SEO Meta</h1>
    <a href="{{ route('admin.seo-meta.index') }}" class="btn-back">← Back</a>
</div>

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('admin.seo-meta.store') }}" enctype="multipart/form-data" id="seoMetaForm">
    @csrf

    {{-- Entity Selection Section --}}
    <div class="content-panel" style="margin-bottom:20px;">
        <div class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            Page / Entity Selection
        </div>

        <div class="form-grid-4">
            <div class="form-group">
                <label class="form-label">Market</label>
                <select name="market_id" id="market_id" class="form-input form-select">
                    <option value="">Global (No Market)</option>
                    @foreach($markets as $market)
                        <option value="{{ $market->id }}" {{ old('market_id') == $market->id ? 'selected' : '' }}>
                            {{ $market->name }} ({{ $market->code }})
                        </option>
                    @endforeach
                </select>
                <span class="help-text">Leave empty for global pages like pv.market/</span>
            </div>

            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category_id" id="category_id" class="form-input form-select">
                    <option value="">None (Home Page)</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->category_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Sub Category</label>
                <select name="sub_category_id" id="sub_category_id" class="form-input form-select">
                    <option value="">None</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Brand</label>
                <select name="brand_id" id="brand_id" class="form-input form-select">
                    <option value="">None (All Brands)</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
                <span class="help-text">Brands are filtered based on active listings</span>
            </div>
        </div>
    </div>

    {{-- Basic SEO Meta Section --}}
    <div class="content-panel" style="margin-bottom:20px;">
        <div class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Basic SEO Meta
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Meta Title <span>*</span></label>
                <input type="text" name="meta_title" class="form-input" value="{{ old('meta_title') }}"
                       placeholder="Page title for search engines" required maxlength="70"/>
                <span class="help-text">Recommended: 50-60 characters</span>
            </div>
            <div class="form-group">
                <label class="form-label">Meta Keywords</label>
                <input type="text" name="meta_keywords" class="form-input" value="{{ old('meta_keywords') }}"
                       placeholder="keyword1, keyword2, keyword3"/>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:20px;">
            <label class="form-label">Meta Description</label>
            <textarea name="meta_description" class="form-input form-textarea" placeholder="Brief description for search results" maxlength="160">{{ old('meta_description') }}</textarea>
            <span class="help-text">Recommended: 150-160 characters</span>
        </div>

        <div class="form-group">
            <label class="form-label">Canonical URL</label>
            <input type="url" name="canonical_url" class="form-input" value="{{ old('canonical_url') }}"
                   placeholder="https://pv.market/..."/>
        </div>
    </div>

    {{-- Page Content Section --}}
    <div class="content-panel" style="margin-bottom:20px;">
        <div class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Page Content
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Page Header (H1)</label>
                <input type="text" name="page_header" class="form-input" value="{{ old('page_header') }}"
                       placeholder="Main heading displayed on page"/>
            </div>
            <div class="form-group">
                <label class="form-label">Short Description</label>
                <textarea name="short_description" class="form-input" rows="2" placeholder="Brief intro text">{{ old('short_description') }}</textarea>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:20px;">
            <label class="form-label">Bottom Header</label>
            <input type="text" name="bottom_header" class="form-input" value="{{ old('bottom_header') }}"
                   placeholder="Bottom section heading"/>
        </div>

        <div class="form-group">
            <label class="form-label">Bottom Description</label>
            <textarea name="bottom_description" id="bottomDescriptionInput" style="display:none;">{{ old('bottom_description') }}</textarea>
            <div class="quill-wrapper">
                <div id="bottomDescriptionEditor"></div>
            </div>
        </div>
    </div>

    {{-- Open Graph Section --}}
    <div class="content-panel" style="margin-bottom:20px;">
        <div class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
            Open Graph (Facebook/LinkedIn)
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">OG Title</label>
                <input type="text" name="og_title" class="form-input" value="{{ old('og_title') }}"
                       placeholder="Leave empty to use Meta Title"/>
            </div>
            <div class="form-group">
                <label class="form-label">OG Type</label>
                <select name="og_type" class="form-input form-select">
                    <option value="website" {{ old('og_type') == 'website' ? 'selected' : '' }}>Website</option>
                    <option value="article" {{ old('og_type') == 'article' ? 'selected' : '' }}>Article</option>
                    <option value="product" {{ old('og_type') == 'product' ? 'selected' : '' }}>Product</option>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:20px;">
            <label class="form-label">OG Description</label>
            <textarea name="og_description" class="form-input" rows="2" placeholder="Leave empty to use Meta Description">{{ old('og_description') }}</textarea>
        </div>

        <div class="form-grid-3">
            <div class="form-group">
                <label class="form-label">OG URL</label>
                <input type="url" name="og_url" class="form-input" value="{{ old('og_url') }}"
                       placeholder="https://pv.market/..."/>
            </div>
            <div class="form-group">
                <label class="form-label">Site Name</label>
                <input type="text" name="og_site_name" class="form-input" value="{{ old('og_site_name', 'PV Market') }}"/>
            </div>
            <div class="form-group">
                <label class="form-label">Locale</label>
                <input type="text" name="og_locale" class="form-input" value="{{ old('og_locale', 'en_US') }}"
                       placeholder="en_US"/>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">OG Images</label>
            <input type="file" name="og_images[]" class="form-input" accept="image/*" multiple/>
            <span class="help-text">Recommended size: 1200x630 pixels</span>
        </div>
    </div>

    {{-- Twitter Card Section --}}
    <div class="content-panel" style="margin-bottom:20px;">
        <div class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/></svg>
            Twitter Card
        </div>

        <div class="form-grid-3">
            <div class="form-group">
                <label class="form-label">Card Type</label>
                <select name="twitter_card" class="form-input form-select">
                    <option value="summary_large_image" {{ old('twitter_card') == 'summary_large_image' ? 'selected' : '' }}>Summary Large Image</option>
                    <option value="summary" {{ old('twitter_card') == 'summary' ? 'selected' : '' }}>Summary</option>
                    <option value="app" {{ old('twitter_card') == 'app' ? 'selected' : '' }}>App</option>
                    <option value="player" {{ old('twitter_card') == 'player' ? 'selected' : '' }}>Player</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Twitter Site (@handle)</label>
                <input type="text" name="twitter_site" class="form-input" value="{{ old('twitter_site') }}"
                       placeholder="@pvmarket"/>
            </div>
            <div class="form-group">
                <label class="form-label">Twitter Creator (@handle)</label>
                <input type="text" name="twitter_creator" class="form-input" value="{{ old('twitter_creator') }}"
                       placeholder="@author"/>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Twitter Title</label>
                <input type="text" name="twitter_title" class="form-input" value="{{ old('twitter_title') }}"
                       placeholder="Leave empty to use Meta Title"/>
            </div>
            <div class="form-group">
                <label class="form-label">Twitter Description</label>
                <textarea name="twitter_description" class="form-input" rows="2" placeholder="Leave empty to use Meta Description">{{ old('twitter_description') }}</textarea>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Twitter Images</label>
            <input type="file" name="twitter_images[]" class="form-input" accept="image/*" multiple/>
        </div>
    </div>

    {{-- Robot Meta Section --}}
    <div class="content-panel" style="margin-bottom:20px;">
        <div class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Robot Directives
        </div>

        <div class="form-grid-3">
            <div class="checkbox-group">
                <input type="checkbox" name="robot_index" id="robot_index" value="1" {{ old('robot_index', true) ? 'checked' : '' }}/>
                <label for="robot_index">Index this page</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="robot_follow" id="robot_follow" value="1" {{ old('robot_follow', true) ? 'checked' : '' }}/>
                <label for="robot_follow">Follow links</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="robot_noarchive" id="robot_noarchive" value="1" {{ old('robot_noarchive') ? 'checked' : '' }}/>
                <label for="robot_noarchive">No Archive</label>
            </div>
        </div>

        <div class="form-grid-3">
            <div class="checkbox-group">
                <input type="checkbox" name="robot_nosnippet" id="robot_nosnippet" value="1" {{ old('robot_nosnippet') ? 'checked' : '' }}/>
                <label for="robot_nosnippet">No Snippet</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="robot_noimageindex" id="robot_noimageindex" value="1" {{ old('robot_noimageindex') ? 'checked' : '' }}/>
                <label for="robot_noimageindex">No Image Index</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="robot_nocache" id="robot_nocache" value="1" {{ old('robot_nocache') ? 'checked' : '' }}/>
                <label for="robot_nocache">No Cache</label>
            </div>
        </div>

        <div class="form-grid-3" style="margin-top:16px;">
            <div class="form-group">
                <label class="form-label">Max Snippet</label>
                <input type="number" name="max_snippet" class="form-input" value="{{ old('max_snippet') }}"
                       placeholder="-1 for unlimited"/>
            </div>
            <div class="form-group">
                <label class="form-label">Max Image Preview</label>
                <select name="max_image_preview" class="form-input form-select">
                    <option value="large" {{ old('max_image_preview') == 'large' ? 'selected' : '' }}>Large</option>
                    <option value="standard" {{ old('max_image_preview') == 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="none" {{ old('max_image_preview') == 'none' ? 'selected' : '' }}>None</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Max Video Preview</label>
                <input type="number" name="max_video_preview" class="form-input" value="{{ old('max_video_preview') }}"
                       placeholder="-1 for unlimited"/>
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div style="display:flex; justify-content:flex-end;">
        <button type="submit" class="btn-save">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            Save SEO Meta
        </button>
    </div>
</form>

@endsection

@section('scripts')
<script src="{{ asset('assets/vendor/quill.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bottomDescriptionInput = document.getElementById('bottomDescriptionInput');
    const bottomDescriptionEditor = document.getElementById('bottomDescriptionEditor');
    const seoMetaForm = document.getElementById('seoMetaForm');

    if (bottomDescriptionInput && bottomDescriptionEditor && seoMetaForm) {
        const bottomDescriptionQuill = new Quill(bottomDescriptionEditor, {
            theme: 'snow',
            placeholder: 'Enter bottom section text...',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ color: [] }, { background: [] }],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ align: [] }],
                    ['link'],
                    ['clean']
                ]
            }
        });

        if (bottomDescriptionInput.value.trim()) {
            bottomDescriptionQuill.clipboard.dangerouslyPasteHTML(bottomDescriptionInput.value);
        }

        seoMetaForm.addEventListener('submit', function() {
            const html = bottomDescriptionQuill.root.innerHTML;
            bottomDescriptionInput.value = html === '<p><br></p>' ? '' : html;
        });
    }

    const categorySelect = document.getElementById('category_id');
    const subCategorySelect = document.getElementById('sub_category_id');
    const brandSelect = document.getElementById('brand_id');

    // Function to load brands based on category/subcategory
    function loadBrands() {
        const categoryId = categorySelect.value;
        const subCategoryId = subCategorySelect.value;

        let url = `{{ url('admin/seo-meta-brands') }}?`;
        if (categoryId) url += `category_id=${categoryId}&`;
        if (subCategoryId) url += `sub_category_id=${subCategoryId}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                const currentValue = brandSelect.value;
                brandSelect.innerHTML = '<option value="">None (All Brands)</option>';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    if (item.id === currentValue) option.selected = true;
                    brandSelect.appendChild(option);
                });
            });
    }

    // Load sub categories when category changes
    categorySelect.addEventListener('change', function() {
        const categoryId = this.value;
        subCategorySelect.innerHTML = '<option value="">None</option>';

        if (categoryId) {
            fetch(`{{ url('admin/seo-meta-sub-categories') }}?category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.sub_category_name;
                        subCategorySelect.appendChild(option);
                    });
                });
        }

        // Reload brands when category changes
        loadBrands();
    });

    // Load brands when sub category changes
    subCategorySelect.addEventListener('change', function() {
        loadBrands();
    });
});
</script>
@endsection


@elseif($mode === 'edit')


{{-- ═══ EDIT MODE ═══ --}}
@section('title', 'Edit SEO Meta')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-quill.css') }}">
<style>
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .section-divider { border:none; border-top:2px solid #E2E8F0; margin:28px 0; }
    .section-title { font-size:15px; font-weight:700; color:var(--primary-d); margin-bottom:20px; text-transform:uppercase; letter-spacing:.5px; display:flex; align-items:center; gap:8px; }
    .section-title svg { width:18px; height:18px; }
    .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:20px 28px; margin-bottom:20px; }
    .form-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px 28px; margin-bottom:20px; }
    .form-grid-4 { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:20px 28px; margin-bottom:20px; }
    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-group.full-width { grid-column:1/-1; }
    .form-label { font-size:13px; font-weight:600; color:var(--text); }
    .form-label span { color:var(--danger); margin-left:2px; }
    .form-input { width:100%; padding:9px 13px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; color:var(--text); outline:none; transition:border-color .2s,box-shadow .2s; background:white; box-sizing:border-box; }
    .form-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .form-input::placeholder { color:#CBD5E1; }
    .form-input:disabled { background:#F8FAFC; cursor:not-allowed; }
    .form-textarea { min-height:100px; resize:vertical; }
    .form-select { cursor:pointer; }
    .btn-save { display:inline-flex; align-items:center; gap:8px; padding:10px 28px; background:#10B981; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; transition:background .15s; }
    .btn-save:hover { background:#059669; }
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; white-space:nowrap; border:1.5px solid var(--text); }
    .alert-error { padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px; }
    .checkbox-group { display:flex; align-items:center; gap:10px; padding:8px 0; }
    .checkbox-group input[type="checkbox"] { width:18px; height:18px; cursor:pointer; }
    .checkbox-group label { font-size:13px; color:var(--text); cursor:pointer; }
    .help-text { font-size:11px; color:var(--muted); margin-top:4px; }
    .entity-badge { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:20px; font-size:12px; font-weight:600; background:#F0F9FF; color:var(--primary-d); border:1px solid #BAE6FD; }
    .entity-info { display:flex; flex-wrap:wrap; gap:10px; padding:16px; background:#F8FAFC; border-radius:8px; margin-bottom:20px; }
    .image-preview { display:flex; flex-wrap:wrap; gap:12px; margin-top:12px; }
    .image-preview-item { position:relative; width:120px; height:80px; border-radius:8px; overflow:hidden; border:1px solid var(--border); }
    .image-preview-item img { width:100%; height:100%; object-fit:cover; }
    .image-preview-item .delete-btn { position:absolute; top:4px; right:4px; width:20px; height:20px; background:#EF4444; color:white; border:none; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:12px; }
    .quill-wrapper { border:1.5px solid var(--border); border-radius:8px; overflow:hidden; background:white; }
    .quill-wrapper:focus-within { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .quill-wrapper .ql-toolbar.ql-snow { border:none; border-bottom:1px solid var(--border); background:#F8FAFC; }
    .quill-wrapper .ql-container.ql-snow { border:none; font-family:inherit; font-size:13.5px; }
    .quill-wrapper .ql-editor { min-height:180px; color:var(--text); line-height:1.7; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Edit SEO Meta</h1>
    <a href="{{ route('admin.seo-meta.index') }}" class="btn-back">← Back</a>
</div>

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('admin.seo-meta.update', $record->id) }}" enctype="multipart/form-data" id="seoMetaForm">
    @csrf
    @method('PUT')

    {{-- Entity Info (Read-only) --}}
    <div class="content-panel" style="margin-bottom:20px;">
        <div class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            Page / Entity
        </div>

        <div class="entity-info">
            <span class="entity-badge">
                <strong>Type:</strong> {{ $record->page_type }}
            </span>
            @if($record->market)
                <span class="entity-badge">
                    <strong>Market:</strong> {{ $record->market->name }} ({{ $record->market->code }})
                </span>
            @else
                <span class="entity-badge">
                    <strong>Market:</strong> Global
                </span>
            @endif
            @if($record->category)
                <span class="entity-badge">
                    <strong>Category:</strong> {{ $record->category->category_name }}
                </span>
            @endif
            @if($record->subCategory)
                <span class="entity-badge">
                    <strong>Sub Category:</strong> {{ $record->subCategory->sub_category_name }}
                </span>
            @endif
            @if($record->brand)
                <span class="entity-badge">
                    <strong>Brand:</strong> {{ $record->brand->name }}
                </span>
            @endif
        </div>
    </div>

    {{-- Basic SEO Meta Section --}}
    <div class="content-panel" style="margin-bottom:20px;">
        <div class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Basic SEO Meta
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Meta Title <span>*</span></label>
                <input type="text" name="meta_title" class="form-input"
                       value="{{ old('meta_title', $record->meta_title) }}"
                       placeholder="Page title for search engines" required maxlength="70"/>
                <span class="help-text">Recommended: 50-60 characters</span>
            </div>
            <div class="form-group">
                <label class="form-label">Meta Keywords</label>
                <input type="text" name="meta_keywords" class="form-input"
                       value="{{ old('meta_keywords', $record->meta_keywords) }}"
                       placeholder="keyword1, keyword2, keyword3"/>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:20px;">
            <label class="form-label">Meta Description</label>
            <textarea name="meta_description" class="form-input form-textarea" placeholder="Brief description for search results" maxlength="160">{{ old('meta_description', $record->meta_description) }}</textarea>
            <span class="help-text">Recommended: 150-160 characters</span>
        </div>

        <div class="form-group">
            <label class="form-label">Canonical URL</label>
            <input type="url" name="canonical_url" class="form-input"
                   value="{{ old('canonical_url', $record->canonical_url) }}"
                   placeholder="https://pv.market/..."/>
        </div>
    </div>

    {{-- Page Content Section --}}
    <div class="content-panel" style="margin-bottom:20px;">
        <div class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Page Content
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Page Header (H1)</label>
                <input type="text" name="page_header" class="form-input"
                       value="{{ old('page_header', $record->page_header) }}"
                       placeholder="Main heading displayed on page"/>
            </div>
            <div class="form-group">
                <label class="form-label">Short Description</label>
                <textarea name="short_description" class="form-input" rows="2" placeholder="Brief intro text">{{ old('short_description', $record->short_description) }}</textarea>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:20px;">
            <label class="form-label">Bottom Header</label>
            <input type="text" name="bottom_header" class="form-input"
                   value="{{ old('bottom_header', $record->bottom_header) }}"
                   placeholder="Bottom section heading"/>
        </div>

        <div class="form-group">
            <label class="form-label">Bottom Description</label>
            <textarea name="bottom_description" id="bottomDescriptionInput" style="display:none;">{{ old('bottom_description', $record->bottom_description) }}</textarea>
            <div class="quill-wrapper">
                <div id="bottomDescriptionEditor"></div>
            </div>
        </div>
    </div>

    {{-- Open Graph Section --}}
    <div class="content-panel" style="margin-bottom:20px;">
        <div class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
            Open Graph (Facebook/LinkedIn)
        </div>

        @php $ogMeta = $record->ogMeta; @endphp

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">OG Title</label>
                <input type="text" name="og_title" class="form-input"
                       value="{{ old('og_title', $ogMeta->og_title ?? '') }}"
                       placeholder="Leave empty to use Meta Title"/>
            </div>
            <div class="form-group">
                <label class="form-label">OG Type</label>
                <select name="og_type" class="form-input form-select">
                    <option value="website" {{ old('og_type', $ogMeta->og_type ?? '') == 'website' ? 'selected' : '' }}>Website</option>
                    <option value="article" {{ old('og_type', $ogMeta->og_type ?? '') == 'article' ? 'selected' : '' }}>Article</option>
                    <option value="product" {{ old('og_type', $ogMeta->og_type ?? '') == 'product' ? 'selected' : '' }}>Product</option>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:20px;">
            <label class="form-label">OG Description</label>
            <textarea name="og_description" class="form-input" rows="2" placeholder="Leave empty to use Meta Description">{{ old('og_description', $ogMeta->og_description ?? '') }}</textarea>
        </div>

        <div class="form-grid-3">
            <div class="form-group">
                <label class="form-label">OG URL</label>
                <input type="url" name="og_url" class="form-input"
                       value="{{ old('og_url', $ogMeta->og_url ?? '') }}"
                       placeholder="https://pv.market/..."/>
            </div>
            <div class="form-group">
                <label class="form-label">Site Name</label>
                <input type="text" name="og_site_name" class="form-input"
                       value="{{ old('og_site_name', $ogMeta->og_site_name ?? 'PV Market') }}"/>
            </div>
            <div class="form-group">
                <label class="form-label">Locale</label>
                <input type="text" name="og_locale" class="form-input"
                       value="{{ old('og_locale', $ogMeta->og_locale ?? 'en_US') }}"
                       placeholder="en_US"/>
            </div>
        </div>

        @if($ogMeta && $ogMeta->images->count() > 0)
        <div class="form-group">
            <label class="form-label">Current OG Images</label>
            <div class="image-preview">
                @foreach($ogMeta->images as $image)
                <div class="image-preview-item">
                    <img src="{{ asset('storage/' . $image->image_url) }}" alt="{{ $image->image_alt }}"/>
                    <button type="button" class="delete-btn" onclick="markForDeletion('og', '{{ $image->id }}', this)">×</button>
                    <input type="hidden" name="delete_og_images[]" value="" disabled/>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="form-group">
            <label class="form-label">Add New OG Images</label>
            <input type="file" name="og_images[]" class="form-input" accept="image/*" multiple/>
            <span class="help-text">Recommended size: 1200x630 pixels</span>
        </div>
    </div>

    {{-- Twitter Card Section --}}
    <div class="content-panel" style="margin-bottom:20px;">
        <div class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/></svg>
            Twitter Card
        </div>

        @php $twitterMeta = $record->twitterMeta; @endphp

        <div class="form-grid-3">
            <div class="form-group">
                <label class="form-label">Card Type</label>
                <select name="twitter_card" class="form-input form-select">
                    <option value="summary_large_image" {{ old('twitter_card', $twitterMeta->twitter_card ?? '') == 'summary_large_image' ? 'selected' : '' }}>Summary Large Image</option>
                    <option value="summary" {{ old('twitter_card', $twitterMeta->twitter_card ?? '') == 'summary' ? 'selected' : '' }}>Summary</option>
                    <option value="app" {{ old('twitter_card', $twitterMeta->twitter_card ?? '') == 'app' ? 'selected' : '' }}>App</option>
                    <option value="player" {{ old('twitter_card', $twitterMeta->twitter_card ?? '') == 'player' ? 'selected' : '' }}>Player</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Twitter Site (@handle)</label>
                <input type="text" name="twitter_site" class="form-input"
                       value="{{ old('twitter_site', $twitterMeta->twitter_site ?? '') }}"
                       placeholder="@pvmarket"/>
            </div>
            <div class="form-group">
                <label class="form-label">Twitter Creator (@handle)</label>
                <input type="text" name="twitter_creator" class="form-input"
                       value="{{ old('twitter_creator', $twitterMeta->twitter_creator ?? '') }}"
                       placeholder="@author"/>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Twitter Title</label>
                <input type="text" name="twitter_title" class="form-input"
                       value="{{ old('twitter_title', $twitterMeta->twitter_title ?? '') }}"
                       placeholder="Leave empty to use Meta Title"/>
            </div>
            <div class="form-group">
                <label class="form-label">Twitter Description</label>
                <textarea name="twitter_description" class="form-input" rows="2" placeholder="Leave empty to use Meta Description">{{ old('twitter_description', $twitterMeta->twitter_description ?? '') }}</textarea>
            </div>
        </div>

        @if($twitterMeta && $twitterMeta->images->count() > 0)
        <div class="form-group">
            <label class="form-label">Current Twitter Images</label>
            <div class="image-preview">
                @foreach($twitterMeta->images as $image)
                <div class="image-preview-item">
                    <img src="{{ asset('storage/' . $image->image_url) }}" alt="{{ $image->image_alt }}"/>
                    <button type="button" class="delete-btn" onclick="markForDeletion('twitter', '{{ $image->id }}', this)">×</button>
                    <input type="hidden" name="delete_twitter_images[]" value="" disabled/>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="form-group">
            <label class="form-label">Add New Twitter Images</label>
            <input type="file" name="twitter_images[]" class="form-input" accept="image/*" multiple/>
        </div>
    </div>

    {{-- Robot Meta Section --}}
    <div class="content-panel" style="margin-bottom:20px;">
        <div class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Robot Directives
        </div>

        @php $robotMeta = $record->robotMeta; @endphp

        <div class="form-grid-3">
            <div class="checkbox-group">
                <input type="checkbox" name="robot_index" id="robot_index" value="1" {{ old('robot_index', $robotMeta->index ?? true) ? 'checked' : '' }}/>
                <label for="robot_index">Index this page</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="robot_follow" id="robot_follow" value="1" {{ old('robot_follow', $robotMeta->follow ?? true) ? 'checked' : '' }}/>
                <label for="robot_follow">Follow links</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="robot_noarchive" id="robot_noarchive" value="1" {{ old('robot_noarchive', $robotMeta->noarchive ?? false) ? 'checked' : '' }}/>
                <label for="robot_noarchive">No Archive</label>
            </div>
        </div>

        <div class="form-grid-3">
            <div class="checkbox-group">
                <input type="checkbox" name="robot_nosnippet" id="robot_nosnippet" value="1" {{ old('robot_nosnippet', $robotMeta->nosnippet ?? false) ? 'checked' : '' }}/>
                <label for="robot_nosnippet">No Snippet</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="robot_noimageindex" id="robot_noimageindex" value="1" {{ old('robot_noimageindex', $robotMeta->noimageindex ?? false) ? 'checked' : '' }}/>
                <label for="robot_noimageindex">No Image Index</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="robot_nocache" id="robot_nocache" value="1" {{ old('robot_nocache', $robotMeta->nocache ?? false) ? 'checked' : '' }}/>
                <label for="robot_nocache">No Cache</label>
            </div>
        </div>

        <div class="form-grid-3" style="margin-top:16px;">
            <div class="form-group">
                <label class="form-label">Max Snippet</label>
                <input type="number" name="max_snippet" class="form-input"
                       value="{{ old('max_snippet', $robotMeta->max_snippet ?? '') }}"
                       placeholder="-1 for unlimited"/>
            </div>
            <div class="form-group">
                <label class="form-label">Max Image Preview</label>
                <select name="max_image_preview" class="form-input form-select">
                    <option value="large" {{ old('max_image_preview', $robotMeta->max_image_preview ?? 'large') == 'large' ? 'selected' : '' }}>Large</option>
                    <option value="standard" {{ old('max_image_preview', $robotMeta->max_image_preview ?? '') == 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="none" {{ old('max_image_preview', $robotMeta->max_image_preview ?? '') == 'none' ? 'selected' : '' }}>None</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Max Video Preview</label>
                <input type="number" name="max_video_preview" class="form-input"
                       value="{{ old('max_video_preview', $robotMeta->max_video_preview ?? '') }}"
                       placeholder="-1 for unlimited"/>
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div style="display:flex; justify-content:flex-end;">
        <button type="submit" class="btn-save">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            Save Changes
        </button>
    </div>
</form>

@endsection

@section('scripts')
<script src="{{ asset('assets/vendor/quill.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bottomDescriptionInput = document.getElementById('bottomDescriptionInput');
    const bottomDescriptionEditor = document.getElementById('bottomDescriptionEditor');
    const seoMetaForm = document.getElementById('seoMetaForm');

    if (!bottomDescriptionInput || !bottomDescriptionEditor || !seoMetaForm) {
        return;
    }

    const bottomDescriptionQuill = new Quill(bottomDescriptionEditor, {
        theme: 'snow',
        placeholder: 'Enter bottom section text...',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ color: [] }, { background: [] }],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['link'],
                ['clean']
            ]
        }
    });

    if (bottomDescriptionInput.value.trim()) {
        bottomDescriptionQuill.clipboard.dangerouslyPasteHTML(bottomDescriptionInput.value);
    }

    seoMetaForm.addEventListener('submit', function() {
        const html = bottomDescriptionQuill.root.innerHTML;
        bottomDescriptionInput.value = html === '<p><br></p>' ? '' : html;
    });
});

function markForDeletion(type, imageId, btn) {
    const container = btn.closest('.image-preview-item');
    const input = container.querySelector('input[type="hidden"]');
    input.value = imageId;
    input.disabled = false;
    container.style.opacity = '0.3';
    btn.style.display = 'none';
}
</script>
@endsection


@else


{{-- ═══ INDEX MODE ═══ --}}
@section('title', 'SEO Meta Management')

@section('styles')
<style>
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .table-toolbar { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--border); gap:12px; flex-wrap:wrap; background:#FAFBFD; }
    .search-group { display:flex; align-items:center; gap:8px; }
    .search-group label { font-size:13.5px; color:var(--text); font-weight:600; }
    .search-input-wrap { position:relative; }
    .search-input-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#CBD5E1; pointer-events:none; }
    .search-input { padding:8px 12px 8px 34px; border:1.5px solid var(--border); border-radius:7px; font-family:inherit; font-size:13px; outline:none; width:230px; color:var(--text); transition:border-color .2s; background:white; }
    .search-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .search-input::placeholder { color:#CBD5E1; }
    .filter-group { display:flex; align-items:center; gap:10px; }
    .filter-select { padding:7px 26px 7px 10px; border:1.5px solid var(--border); border-radius:7px; font-family:inherit; font-size:13px; font-weight:500; color:var(--text); background:white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 7px center; -webkit-appearance:none; appearance:none; outline:none; cursor:pointer; }
    .filter-select:hover,.filter-select:focus { border-color:var(--primary); }
    .entries-group { display:flex; align-items:center; gap:7px; font-size:13.5px; color:var(--muted); font-weight:500; }
    .entries-select { padding:7px 26px 7px 10px; border:1.5px solid var(--border); border-radius:7px; font-family:inherit; font-size:13px; font-weight:600; color:var(--text); background:white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 7px center; -webkit-appearance:none; appearance:none; outline:none; cursor:pointer; }
    .data-table { width:100%; border-collapse:collapse; }
    .data-table thead { background:#F0F9FF; }
    .data-table th { padding:11px 16px; text-align:left; font-size:12px; font-weight:700; color:var(--primary-d); text-transform:uppercase; letter-spacing:.5px; border-bottom:2px solid #BAE6FD; white-space:nowrap; }
    .data-table th.center,.data-table td.center { text-align:center; }
    .data-table td { padding:14px 16px; font-size:13.5px; color:var(--text); border-bottom:1px solid #F1F5F9; vertical-align:middle; }
    .data-table tr:last-child td { border-bottom:none; }
    .data-table tbody tr:nth-child(odd) td { background:white; }
    .data-table tbody tr:nth-child(even) td { background:#FAFBFD; }
    .data-table tbody tr:hover td { background:#E0F2FE !important; }
    .page-type-badge { display:inline-flex; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:600; letter-spacing:.3px; }
    .page-type-badge.home { background:#DCFCE7; color:#166534; }
    .page-type-badge.category { background:#DBEAFE; color:#1E40AF; }
    .page-type-badge.subcategory { background:#E0E7FF; color:#3730A3; }
    .page-type-badge.brand { background:#FEF3C7; color:#92400E; }
    .page-type-badge.product { background:#FCE7F3; color:#9D174D; }
    .location-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:600; background:#F5F3FF; color:#7C3AED; }
    .meta-preview { max-width:300px; }
    .meta-title { font-weight:600; color:var(--text); font-size:13px; margin-bottom:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .meta-desc { font-size:12px; color:var(--muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .action-btns { display:flex; align-items:center; justify-content:center; gap:8px; }
    .action-icon { width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; border:1.5px solid transparent; background:none; border-radius:7px; transition:all .15s; text-decoration:none; }
    .action-icon svg { width:15px; height:15px; }
    .action-icon.edit { color:#D97706; border-color:#FDE68A; background:#FFFBEB; }
    .action-icon.delete { color:#DC2626; border-color:#FECACA; background:#FEF2F2; }
    .action-icon:hover { transform:translateY(-2px) scale(1.08); box-shadow:0 3px 8px rgba(0,0,0,.12); }
    .action-icon.edit:hover { background:#FEF3C7; border-color:#F59E0B; }
    .action-icon.delete:hover { background:#FEE2E2; border-color:#EF4444; }
    .table-footer { padding:12px 20px; font-size:13px; color:var(--muted); border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; background:#FAFBFD; }
    .empty-state { text-align:center; padding:52px 20px; color:var(--muted); }
    .empty-state svg { width:42px; height:42px; margin:0 auto 12px; opacity:.2; display:block; }
    .empty-state p { font-size:14px; font-weight:500; }
    .alert-success { padding:12px 16px; background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; border-radius:8px; font-size:13.5px; font-weight:500; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">SEO Meta Management</h1>
    <a href="{{ route('admin.seo-meta.create') }}"
       style="display:inline-flex; align-items:center; gap:7px; padding:10px 20px;
              background:var(--primary); color:white; border-radius:8px; font-size:14px;
              font-weight:600; text-decoration:none; white-space:nowrap;"
       onmouseover="this.style.background='var(--primary-d)'"
       onmouseout="this.style.background='var(--primary)'">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Add +
    </a>
</div>

@if(session('success'))
    <div class="alert-success">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        {{ session('success') }}
    </div>
@endif

<div class="content-panel">
    <div class="table-toolbar">
        <form method="GET" action="{{ route('admin.seo-meta.index') }}" class="search-group">
            <label>Search:</label>
            <div class="search-input-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search title, description..." class="search-input"/>
            </div>
            @if(request('market_id'))<input type="hidden" name="market_id" value="{{ request('market_id') }}">@endif
            @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
        </form>

        <div class="filter-group">
            <form method="GET" action="{{ route('admin.seo-meta.index') }}" style="display:flex; gap:10px;">
                <select name="market_id" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Markets</option>
                    <option value="global" {{ request('market_id') == 'global' ? 'selected' : '' }}>Global</option>
                    @foreach($markets as $market)
                        <option value="{{ $market->id }}" {{ request('market_id') == $market->id ? 'selected' : '' }}>
                            {{ $market->name }} ({{ $market->code }})
                        </option>
                    @endforeach
                </select>

                <select name="type" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="home" {{ request('type') == 'home' ? 'selected' : '' }}>Home</option>
                    <option value="category" {{ request('type') == 'category' ? 'selected' : '' }}>Category</option>
                    <option value="subcategory" {{ request('type') == 'subcategory' ? 'selected' : '' }}>Sub Category</option>
                    <option value="brand" {{ request('type') == 'brand' ? 'selected' : '' }}>Brand</option>
                </select>

                @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
            </form>
        </div>

        <form method="GET" action="{{ route('admin.seo-meta.index') }}" class="entries-group">
            Show
            <select name="entries" class="entries-select" onchange="this.form.submit()">
                @foreach([10, 25, 50, 100] as $n)
                    <option value="{{ $n }}" {{ request('entries', 10) == $n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
            </select>
            entries
            @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
            @if(request('market_id'))<input type="hidden" name="market_id" value="{{ request('market_id') }}">@endif
            @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
        </form>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="center" style="width:50px;">#</th>
                <th style="width:100px;">Market</th>
                <th style="width:100px;">Type</th>
                <th>Meta Preview</th>
                <th style="width:150px;">Entity</th>
                <th class="center" style="width:100px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $record)
            <tr>
                <td class="center" style="font-weight:700; color:var(--muted); font-size:13px;">
                    {{ $records->firstItem() + $index }}
                </td>
                <td>
                    @if($record->market)
                        <span class="location-badge">{{ $record->market->code }}</span>
                    @else
                        <span class="location-badge" style="background:#DCFCE7; color:#166534;">Global</span>
                    @endif
                </td>
                <td>
                    @php
                        $typeClass = match($record->page_type) {
                            'Global Home', 'Market Home' => 'home',
                            'Category' => 'category',
                            'Sub Category' => 'subcategory',
                            'Brand' => 'brand',
                            'Product' => 'product',
                            default => 'home'
                        };
                    @endphp
                    <span class="page-type-badge {{ $typeClass }}">{{ $record->page_type }}</span>
                </td>
                <td>
                    <div class="meta-preview">
                        <div class="meta-title">{{ $record->meta_title }}</div>
                        <div class="meta-desc">{{ $record->meta_description ?? 'No description' }}</div>
                    </div>
                </td>
                <td>
                    @if($record->category)
                        <span style="font-size:12px; color:var(--muted);">{{ $record->category->category_name }}</span>
                    @endif
                    @if($record->subCategory)
                        <span style="font-size:12px; color:var(--muted);">/ {{ $record->subCategory->sub_category_name }}</span>
                    @endif
                    @if($record->brand)
                        <span style="font-size:12px; color:var(--muted);">/ {{ $record->brand->name }}</span>
                    @endif
                </td>
                <td>
                    <div class="action-btns">
                        <a href="{{ route('admin.seo-meta.edit', $record->id) }}"
                           class="action-icon edit" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </a>
                        <button class="action-icon delete" title="Delete"
                            onclick="if(confirm('Delete this SEO Meta entry?')) document.getElementById('del-{{ $record->id }}').submit();">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </button>
                        <form id="del-{{ $record->id }}" method="POST"
                              action="{{ route('admin.seo-meta.destroy', $record->id) }}"
                              style="display:none;">
                            @csrf @method('DELETE')
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <p>No SEO Meta entries yet. Click <strong>Add +</strong> to create one.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="table-footer">
        <span>{{ $records->firstItem() ?? 0 }}–{{ $records->lastItem() ?? 0 }} of {{ $records->total() }} entries</span>

        @if ($records->hasPages())
        <nav style="display:flex; align-items:center; gap:4px;">
            @if ($records->onFirstPage())
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">‹</span>
            @else
                <a href="{{ $records->previousPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;">‹</a>
            @endif

            @foreach ($records->getUrlRange(1, $records->lastPage()) as $page => $url)
                @if ($page == $records->currentPage())
                    <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--primary-d);background:var(--primary-d);color:white;font-size:13px;font-weight:700;">{{ $page }}</span>
                @elseif ($page == 1 || $page == $records->lastPage() || abs($page - $records->currentPage()) <= 2)
                    <a href="{{ $url }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:13px;font-weight:500;transition:all .15s;">{{ $page }}</a>
                @elseif ($page == $records->currentPage() - 3 || $page == $records->currentPage() + 3)
                    <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--muted);font-size:13px;">…</span>
                @endif
            @endforeach

            @if ($records->hasMorePages())
                <a href="{{ $records->nextPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;">›</a>
            @else
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">›</span>
            @endif
        </nav>
        @endif
    </div>
</div>

@endsection

@endif
