@if($mode === 'create' || $mode === 'edit')
@extends('layouts.admin')

@section('title', $mode === 'create' ? 'Add Blog' : 'Edit Blog')

@section('styles')
<style>
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; white-space:nowrap; }
    .btn-back:hover { background:#334155; }
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .form-main-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px 28px; margin-bottom:20px; }
    .form-left  { display:flex; flex-direction:column; gap:18px; }
    .form-right { display:flex; flex-direction:column; gap:18px; }
    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-label { font-size:13px; font-weight:600; color:var(--text); }
    .form-label span { color:var(--danger); margin-left:2px; }
    .form-input, .form-select, .form-textarea { width:100%; padding:9px 13px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; color:var(--text); outline:none; transition:border-color .2s, box-shadow .2s; background:white; }
    .form-input:focus, .form-select:focus, .form-textarea:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .form-input::placeholder, .form-textarea::placeholder { color:#CBD5E1; }
    .slug-hint  { font-size:11px; color:var(--primary-d); margin-top:3px; font-weight:500; }
    .form-select { appearance:none; background:white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 12px center; cursor:pointer; }
    .section-divider { border:none; border-top:1px solid var(--border); margin:20px 0; }
    .form-actions { display:flex; justify-content:flex-end; padding-top:20px; border-top:1px solid var(--border); margin-top:20px; }
    .btn-save { display:inline-flex; align-items:center; gap:8px; padding:10px 28px; background:#10B981; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; transition:background .15s, box-shadow .2s; }
    .btn-save:hover { background:#059669; box-shadow:0 4px 14px rgba(16,185,129,.35); }
    .alert-error { padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px; }
    .form-file-wrap { border:1.5px solid var(--border); border-radius:8px; overflow:hidden; display:flex; align-items:center; background:white; transition:border-color .2s; }
    .form-file-wrap:focus-within { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .form-file-wrap input[type="file"] { width:100%; min-width:0; padding:8px 12px; border:none; outline:none; font-family:inherit; font-size:13px; background:transparent; cursor:pointer; }
    .form-file-wrap input[type="file"]::-webkit-file-upload-button { padding:6px 14px; background:var(--light); border:none; border-right:1px solid var(--border); font-family:inherit; font-size:12.5px; font-weight:600; cursor:pointer; margin-right:8px; }
    .img-preview { width:160px; height:100px; max-width:100%; border-radius:8px; border:1px solid var(--border); object-fit:cover; display:block; margin-bottom:8px; background:#F8FAFC; }
    .form-hint { font-size:11px; color:var(--muted); margin-top:3px; }
    .image-size-error { display:none; font-size:12px; color:var(--danger); margin-top:4px; }
    .quill-wrapper { border:1.5px solid #CBD5E1; border-radius:8px; overflow:hidden; background:white; }
    .quill-wrapper:focus-within { border-color:#0EA5E9; box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    @media (max-width: 900px) {
        .form-main-grid { grid-template-columns:1fr; }
    }
</style>
@endsection

@section('content')

<link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
<style>
    .ql-toolbar.ql-snow { border:none !important; border-bottom:1px solid #E2E8F0 !important; background:#F8FAFC; padding:10px 12px; font-family:inherit; }
    .ql-container.ql-snow { border:none !important; font-family:inherit; font-size:13.5px; }
    .ql-editor { min-height:320px; font-size:13.5px; font-family:inherit; color:#1E293B; line-height:1.7; padding:14px 16px; }
    .ql-editor.ql-blank::before { color:#CBD5E1; font-style:normal; font-size:13.5px; }
    .ql-toolbar button { width:28px !important; height:28px !important; padding:3px !important; display:inline-flex !important; align-items:center !important; justify-content:center !important; }
    .ql-toolbar button svg, .ql-toolbar .ql-picker svg { width:16px !important; height:16px !important; }
    .ql-toolbar .ql-picker { font-size:13px; }
    .ql-toolbar .ql-picker-label { padding:2px 6px; display:inline-flex; align-items:center; gap:4px; }
    .ql-snow .ql-stroke { stroke:#475569; }
    .ql-snow .ql-fill  { fill:#475569; }
    .ql-snow .ql-picker { color:#475569; }
    .ql-toolbar button:hover .ql-stroke, .ql-toolbar button.ql-active .ql-stroke { stroke:#0EA5E9; }
    .ql-toolbar button:hover .ql-fill,   .ql-toolbar button.ql-active .ql-fill  { fill:#0EA5E9; }
</style>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">{{ $mode === 'create' ? 'Add Blog' : 'Edit Blog' }}</h1>
    <a href="{{ route('admin.knowledge-hub.blogs.index') }}" class="btn-back">← Back</a>
</div>

<div class="content-panel">

    @if($errors->any())
        <div class="alert-error">{{ $errors->first() }}</div>
    @endif

    <form
        method="POST"
        action="{{ $mode === 'create' ? route('admin.knowledge-hub.blogs.store') : route('admin.knowledge-hub.blogs.update', $record->id) }}"
        enctype="multipart/form-data"
        id="blogForm"
    >
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <div class="form-main-grid">
            <div class="form-left">
                <div class="form-group">
                    <label class="form-label">Title <span>*</span></label>
                    <input type="text" name="title" class="form-input"
                           placeholder="Enter blog title..."
                           value="{{ old('title', $record->title ?? '') }}"
                           id="headingInput" required/>
                </div>
                <div class="form-group">
                    <label class="form-label">Choose Related Blog</label>
                    <select name="related_blog_id" class="form-select">
                        <option value="">Select Related Blog</option>
                        @foreach($allBlogs as $blog)
                            @php
                                $relatedIds = $record->related_ids ?? [];
                                $selected   = in_array($blog->id, (array) $relatedIds);
                            @endphp
                            <option value="{{ $blog->id }}" {{ $selected ? 'selected' : '' }}>
                                {{ Str::limit($blog->title, 60) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-right">
                {{--
                    Pass the current image URL extracted from the image object.
                    The x-image-upload component expects a plain path/url string
                    for its :current-image prop, so we pull image.url from the object.
                --}}
                @php
                    $currentImageUrl = null;
                    if (!empty($record->image['url'])) {
                        $currentImageUrl = $record->image['url'];
                    }
                @endphp
                <x-image-upload
                    name="image"
                    label="Blog Image:"
                    :current-image="$currentImageUrl"
                />
                <span class="form-hint">JPEG, PNG or WebP. Maximum file size: 600 MB.</span>
                <span class="image-size-error" id="imageSizeError" role="alert"></span>

                <div class="form-group">
                    <label class="form-label">Alt Tag</label>
                    <input type="text" name="alt_tag" class="form-input"
                           placeholder="Image alt text"
                           value="{{ old('alt_tag', $record->image['alt'] ?? $record->alt_tag ?? '') }}"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" id="slugInput" class="form-input"
                           placeholder="auto-generated-from-title"
                           value="{{ old('slug', $record->slug ?? '') }}"/>
                    <span class="slug-hint" id="slugPreview"></span>
                </div>
            </div>
        </div>

        <hr class="section-divider">

        <div class="form-group">
            <label class="form-label">Content (data)</label>
            <textarea name="data" id="dataInput" style="display:none;">{{ old('data', $record->data ?? '') }}</textarea>
            <div class="quill-wrapper">
                <div id="quillEditor"></div>
            </div>
        </div>

    </form>

    {{-- ═══ COMMENTS SECTION — outside main form ═══ --}}
    @if($mode === 'edit')
    <hr class="section-divider">

    <div style="margin-top:20px;">
        <label class="form-label" style="margin-bottom:14px; display:block; font-size:15px;">Blog Comments</label>

        @forelse($record->blog_comments ?? [] as $index => $comment)
        <div style="background:#F8FAFC; border:1px solid var(--border); border-radius:8px; padding:14px 16px; margin-bottom:10px; display:flex; align-items:flex-start; justify-content:space-between; gap:12px;">
            <div style="flex:1;">
                <p style="font-size:13.5px; color:var(--text); margin:0 0 6px;">
                    {{ $comment['translations'][app()->getLocale()] ?? $comment['comment'] }}
                </p>
                <span style="font-size:11px; color:var(--muted);">
                    User: {{ $comment['user_id'] }} &bull; {{ $comment['created_at'] }}
                </span>
            </div>
            <form method="POST"
                  action="{{ route('admin.knowledge-hub.blogs.comments.destroy', [$record->id, $index]) }}"
                  onsubmit="return confirm('Delete this comment?')">
                @csrf @method('DELETE')
                <button type="submit" style="background:#FEF2F2; border:1.5px solid #FECACA; color:#DC2626; border-radius:6px; padding:5px 10px; font-size:12px; cursor:pointer; font-family:inherit;">
                    Delete
                </button>
            </form>
        </div>
        @empty
            <p style="font-size:13px; color:var(--muted); margin-bottom:14px;">No comments yet.</p>
        @endforelse

        <form method="POST" action="{{ route('admin.knowledge-hub.blogs.comments.store', $record->id) }}" style="margin-top:16px;">
            @csrf
            <div class="form-group">
                <label class="form-label">Add Comment</label>
                <textarea name="comment" class="form-textarea" rows="3"
                          placeholder="Write a comment..."
                          style="resize:vertical;" required></textarea>
            </div>
            <button type="submit" style="margin-top:10px; padding:8px 20px; background:#0EA5E9; color:white; border:none; border-radius:8px; font-size:13.5px; font-weight:600; cursor:pointer; font-family:inherit;">
                Add Comment
            </button>
        </form>
    </div>
    @endif

    <div class="form-actions">
        <button type="submit" form="blogForm" class="btn-save">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            Save
        </button>
    </div>

</div>

<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
(function () {
    var quill = new Quill('#quillEditor', {
        theme: 'snow',
        placeholder: 'Write your blog content here...',
        modules: {
            toolbar: [
                [{ header: [1,2,3,4,5,6,false] }],
                [{ font: [] }],
                ['bold','italic','underline','strike'],
                [{ color: [] },{ background: [] }],
                [{ list:'ordered' },{ list:'bullet' }],
                [{ align: [] }],
                ['link','image','video'],
                ['clean']
            ]
        }
    });

    var existingContent = document.getElementById('dataInput').value;
    if (existingContent && existingContent.trim() !== '') {
        quill.clipboard.dangerouslyPasteHTML(existingContent);
    }

    document.getElementById('blogForm').addEventListener('submit', function () {
        var html = quill.root.innerHTML;
        document.getElementById('dataInput').value = (html === '<p><br></p>') ? '' : html;
    });

    var headingInput = document.getElementById('headingInput');
    var slugInput    = document.getElementById('slugInput');
    var slugPreview  = document.getElementById('slugPreview');
    var imageInput   = document.querySelector('#blogForm input[name="image"]');
    var imageError   = document.getElementById('imageSizeError');
    var maxImageSize = 600 * 1024 * 1024;

    if (imageInput) {
        imageInput.addEventListener('change', function () {
            var file = this.files && this.files[0];
            var isTooLarge = file && file.size > maxImageSize;
            var message = isTooLarge ? 'The blog image must not be larger than 600 MB.' : '';

            this.setCustomValidity(message);
            imageError.textContent = message;
            imageError.style.display = message ? 'block' : 'none';

            if (message) {
                this.reportValidity();
            }
        });
    }

    function toSlug(str) {
        return str.toLowerCase().trim()
                  .replace(/[^\w\s-]/g, '')
                  .replace(/[\s_-]+/g, '-')
                  .replace(/^-+|-+$/g, '');
    }

    headingInput.addEventListener('input', function () {
        if (!slugInput.dataset.manual) {
            var slug = toSlug(this.value);
            slugInput.value = slug;
            slugPreview.textContent = slug ? 'Preview: ' + slug : '';
        }
    });

    slugInput.addEventListener('input', function () {
        this.dataset.manual = this.value ? 'true' : '';
        slugPreview.textContent = this.value ? 'Preview: ' + toSlug(this.value) : '';
    });
})();
</script>

@endsection

@else

{{-- ═══ INDEX MODE ═══ --}}

@section('title', 'Blogs')

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
    .entries-group { display:flex; align-items:center; gap:7px; font-size:13.5px; color:var(--muted); font-weight:500; }
    .entries-select { padding:7px 26px 7px 10px; border:1.5px solid var(--border); border-radius:7px; font-family:inherit; font-size:13px; font-weight:600; color:var(--text); background:white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 7px center; -webkit-appearance:none; appearance:none; outline:none; cursor:pointer; }
    .entries-select:hover,.entries-select:focus { border-color:var(--primary); }
    .data-table { width:100%; border-collapse:collapse; }
    .data-table thead { background:#F0F9FF; }
    .data-table th { padding:11px 16px; text-align:left; font-size:12px; font-weight:700; color:var(--primary-d); text-transform:uppercase; letter-spacing:.5px; border-bottom:2px solid #BAE6FD; white-space:nowrap; }
    .data-table th.center,.data-table td.center { text-align:center; }
    .data-table td { padding:13px 16px; font-size:13.5px; color:var(--text); border-bottom:1px solid #F1F5F9; vertical-align:middle; }
    .data-table tr:last-child td { border-bottom:none; }
    .data-table tbody tr:nth-child(odd)  td { background:white; }
    .data-table tbody tr:nth-child(even) td { background:#FAFBFD; }
    .data-table tbody tr:hover td { background:#E0F2FE !important; }
    .blog-thumb { width:80px; height:50px; border-radius:6px; object-fit:cover; border:1px solid var(--border); display:block; margin:0 auto; }
    .no-image { font-size:12px; color:#CBD5E1; text-align:center; }
    .truncate { max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:block; }
    .action-btns { display:flex; align-items:center; justify-content:center; gap:8px; }
    .action-icon { width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; border:1.5px solid transparent; background:none; border-radius:7px; transition:all .15s; text-decoration:none; }
    .action-icon svg { width:15px; height:15px; }
    .action-icon.edit   { color:#D97706; border-color:#FDE68A; background:#FFFBEB; }
    .action-icon.delete { color:#DC2626; border-color:#FECACA; background:#FEF2F2; }
    .action-icon:hover  { transform:translateY(-2px) scale(1.08); box-shadow:0 3px 8px rgba(0,0,0,.12); }
    .action-icon.edit:hover   { background:#FEF3C7; border-color:#F59E0B; }
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
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Knowledge Base</h1>
    <a href="{{ route('admin.knowledge-hub.blogs.create') }}"
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
        <form method="GET" action="{{ route('admin.knowledge-hub.blogs.index') }}" class="search-group">
            <label>Search:</label>
            <div class="search-input-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search By Title..." class="search-input"/>
            </div>
        </form>

        <form method="GET" action="{{ route('admin.knowledge-hub.blogs.index') }}" class="entries-group">
            Show
            <select name="entries" class="entries-select" onchange="this.form.submit()">
                @foreach([10,25,50,100] as $n)
                    <option value="{{ $n }}" {{ request('entries',10) == $n ? 'selected':'' }}>{{ $n }}</option>
                @endforeach
            </select>
            entries
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
        </form>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="center" style="width:70px;">S.No</th>
                <th>Title</th>
                <th>Content</th>
                <th class="center" style="width:120px;">Image</th>
                <th class="center" style="width:100px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($blogs as $index => $blog)
            <tr>
                <td class="center" style="font-weight:700; color:var(--muted); font-size:13px;">
                    {{ $blogs->firstItem() + $index }}
                </td>

                <td>
                    <span class="truncate" style="max-width:320px;" title="{{ lang($blog, 'title') }}">
                        {{ lang($blog, 'title') }}
                    </span>
                </td>

                <td>
                    <span class="truncate" style="max-width:380px;" title="{{ strip_tags(lang($blog, 'data')) }}">
                        {{ Str::limit(strip_tags(lang($blog, 'data')), 80) }}
                    </span>
                </td>

                <td class="center">
                    @php
                        $thumbUrl = data_get($blog->image, 'url')
                            ?: data_get($blog->image, 'path')
                            ?: (is_string($blog->image ?? null) ? $blog->image : null);

                        if ($thumbUrl && !preg_match('/^https?:\/\//i', $thumbUrl)) {
                            $thumbUrl = asset('storage/' . ltrim($thumbUrl, '/'));
                        }

                        $thumbAlt = data_get($blog->image, 'alt') ?: $blog->alt_tag ?: $blog->title;
                    @endphp
                    @if($thumbUrl)
                        <img src="{{ $thumbUrl }}"
                             class="blog-thumb"
                             alt="{{ $thumbAlt }}"/>
                    @else
                        <span class="no-image">—</span>
                    @endif
                </td>

                <td>
                    <div class="action-btns">
                        <a href="{{ route('admin.knowledge-hub.blogs.edit', $blog->id) }}"
                           class="action-icon edit" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </a>
                        <button class="action-icon delete" title="Delete"
                            onclick="if(confirm('Delete this blog post?')) document.getElementById('del-{{ $blog->id }}').submit();">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </button>
                        <form id="del-{{ $blog->id }}" method="POST"
                              action="{{ route('admin.knowledge-hub.blogs.destroy', $blog->id) }}"
                              style="display:none;">
                            @csrf @method('DELETE')
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                        </svg>
                        <p>No blog posts yet. Click <strong>Add +</strong> to create one.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="table-footer">
        <span>{{ $blogs->firstItem() ?? 0 }}–{{ $blogs->lastItem() ?? 0 }} of {{ $blogs->total() }} entries</span>
        @if ($blogs->hasPages())
        <nav style="display:flex; align-items:center; gap:4px;">
            @if ($blogs->onFirstPage())
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">‹</span>
            @else
                <a href="{{ $blogs->previousPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">‹</a>
            @endif

            @foreach ($blogs->getUrlRange(1, $blogs->lastPage()) as $page => $url)
                @if ($page == $blogs->currentPage())
                    <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--primary-d);background:var(--primary-d);color:white;font-size:13px;font-weight:700;">{{ $page }}</span>
                @elseif ($page == 1 || $page == $blogs->lastPage() || abs($page - $blogs->currentPage()) <= 2)
                    <a href="{{ $url }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:13px;font-weight:500;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">{{ $page }}</a>
                @elseif ($page == $blogs->currentPage() - 3 || $page == $blogs->currentPage() + 3)
                    <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--muted);font-size:13px;">…</span>
                @endif
            @endforeach

            @if ($blogs->hasMorePages())
                <a href="{{ $blogs->nextPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">›</a>
            @else
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">›</span>
            @endif
        </nav>
        @endif
    </div>

</div>

@endsection

@endif
