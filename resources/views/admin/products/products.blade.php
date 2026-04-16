@if($mode === 'create' || $mode === 'edit')
@extends('layouts.admin')

@section('title', $mode === 'create' ? 'Add Product' : 'Edit Product')

@section('styles')
<style>
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; white-space:nowrap; border:1.5px solid var(--text); }
    .btn-back:hover { background:#334155; }
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 18px;
        background: var(--text);
        color: white;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        transition: background .15s, transform .1s;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,.04); margin-bottom:20px; }
    .form-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:18px 24px; margin-bottom:20px; }
    .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:18px 24px; margin-bottom:20px; }
    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-label { font-size:13px; font-weight:600; color:var(--text); }
    .form-label span { color:var(--danger); margin-left:2px; }
    .form-input, .form-select { width:100%; padding:9px 13px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; color:var(--text); outline:none; transition:border-color .2s, box-shadow .2s; background:white; }
    .form-input:focus, .form-select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .form-input::placeholder { color:#CBD5E1; }
    .form-select { appearance:none; background:white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 12px center; cursor:pointer; }
    .form-file-wrap { border:1.5px solid var(--border); border-radius:8px; overflow:hidden; display:flex; align-items:center; background:white; }
    .form-file-wrap input[type="file"] { flex:1; padding:8px 12px; border:none; outline:none; font-family:inherit; font-size:13px; background:transparent; cursor:pointer; }
    .form-file-wrap input[type="file"]::-webkit-file-upload-button { padding:6px 14px; background:var(--light); border:none; border-right:1px solid var(--border); font-family:inherit; font-size:12.5px; font-weight:600; cursor:pointer; margin-right:8px; }
    .section-header { display:flex; align-items:center; justify-content:space-between; margin:24px 0 16px; cursor:pointer; }
    .section-title { font-size:18px; font-weight:800; color:var(--primary-d); }
    .section-toggle { width:24px; height:24px; display:flex; align-items:center; justify-content:center; font-size:18px; color:var(--muted); font-weight:300; }
    .checkbox-row { display:flex; align-items:center; gap:10px; margin-bottom:16px; }
    .checkbox-row input[type="checkbox"] { width:18px; height:18px; accent-color:var(--primary); cursor:pointer; }
    .checkbox-row label { font-size:14px; font-weight:500; color:var(--text); cursor:pointer; }
    .toggle-wrap { display:flex; align-items:center; gap:12px; margin-bottom:16px; }
    .toggle-switch { position:relative; width:44px; height:24px; }
    .toggle-switch input { opacity:0; width:0; height:0; }
    .toggle-slider { position:absolute; cursor:pointer; inset:0; background:#CBD5E1; border-radius:24px; transition:.3s; }
    .toggle-slider::before { content:''; position:absolute; height:18px; width:18px; left:3px; bottom:3px; background:white; border-radius:50%; transition:.3s; }
    .toggle-switch input:checked + .toggle-slider { background:var(--primary); }
    .toggle-switch input:checked + .toggle-slider::before { transform:translateX(20px); }
    .toggle-label { font-size:14px; font-weight:500; color:var(--text); }
    .details-table { width:100%; border-collapse:collapse; margin-top:12px; }
    .details-table thead { background:#F0F9FF; }
    .details-table th { padding:10px 14px; font-size:12px; font-weight:700; color:var(--primary-d); text-transform:uppercase; letter-spacing:.5px; border-bottom:2px solid #BAE6FD; text-align:left; }
    .details-table th.center { text-align:center; }
    .details-table td { padding:10px 14px; border-bottom:1px solid #F1F5F9; vertical-align:middle; }
    .details-table tr:last-child td { border-bottom:none; }
    .details-table td.sno { color:var(--muted); font-weight:700; font-size:13px; text-align:center; width:60px; }
    .details-table input[type="text"],
    .details-table input[type="number"],
    .details-table select { width:100%; padding:8px 12px; border:1.5px solid var(--border); border-radius:7px; font-family:inherit; font-size:13px; color:var(--text); outline:none; transition:border-color .2s; background:white; }
    .details-table select { appearance:none; background:white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 10px center; cursor:pointer; padding-right:28px; }
    .details-table input:focus, .details-table select:focus { border-color:var(--primary); }
    .details-table input::placeholder { color:#CBD5E1; }
    .quill-wrapper { border:1.5px solid #CBD5E1; border-radius:8px; overflow:hidden; background:white; }
    .quill-wrapper:focus-within { border-color:#0EA5E9; box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .form-actions { display:flex; justify-content:flex-end; padding-top:20px; border-top:1px solid var(--border); margin-top:20px; }
    .btn-save { display:inline-flex; align-items:center; gap:8px; padding:10px 28px; background:#10B981; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; transition:background .15s, box-shadow .2s; }
    .btn-save:hover { background:#059669; box-shadow:0 4px 14px rgba(16,185,129,.35); }
    .img-preview { height:50px; border-radius:6px; border:1px solid var(--border); object-fit:contain; display:block; margin-bottom:8px; }
    .form-hint { font-size:11px; color:var(--muted); margin-top:3px; }
    .alert-error { padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px; }
    .divider { border:none; border-top:1px solid var(--border); margin:20px 0; }
    .details-loading { text-align:center; padding:32px; color:var(--muted); font-size:14px; }
    .details-empty { text-align:center; padding:32px; color:var(--muted); font-size:14px; background:#FAFBFD; border-radius:8px; border:1.5px dashed var(--border); }
    .measure-row { display:flex; gap:8px; }
    .measure-row .form-input { flex:1; }
    .measure-row .unit-input { width:90px; flex-shrink:0; }
</style>
@endsection

@section('content')

<link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
<style>
    .ql-toolbar.ql-snow { border:none !important; border-bottom:1px solid #E2E8F0 !important; background:#F8FAFC; padding:10px 12px; font-family:inherit; }
    .ql-container.ql-snow { border:none !important; font-family:inherit; font-size:13.5px; }
    .ql-editor { min-height:180px; font-size:13.5px; font-family:inherit; color:#1E293B; line-height:1.7; padding:14px 16px; }
    .ql-editor.ql-blank::before { color:#CBD5E1; font-style:normal; font-size:13.5px; }
    .ql-toolbar.ql-snow .ql-formats { margin-right:10px; }
    .ql-toolbar button { width:28px !important; height:28px !important; padding:3px !important; display:inline-flex !important; align-items:center !important; justify-content:center !important; }
    .ql-toolbar button svg, .ql-toolbar .ql-picker svg { width:16px !important; height:16px !important; }
    .ql-snow .ql-stroke { stroke:#475569; } .ql-snow .ql-fill { fill:#475569; } .ql-snow .ql-picker { color:#475569; }
    .ql-toolbar button:hover .ql-stroke, .ql-toolbar button.ql-active .ql-stroke { stroke:#0EA5E9; }
    .ql-toolbar button:hover .ql-fill,   .ql-toolbar button.ql-active .ql-fill   { fill:#0EA5E9; }
</style>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">
        {{ $mode === 'create' ? 'Add' : 'Edit' }} Product
    </h1>
    <a href="{{ route('admin.products.index') }}" class="btn-back">← Back</a>
</div>

<div class="content-panel">

    @if($errors->any())
        <div class="alert-error">{{ $errors->first() }}</div>
    @endif

    <form
        method="POST"
        action="{{ $mode === 'create' ? route('admin.products.store') : route('admin.products.update', $record->id) }}"
        enctype="multipart/form-data"
        id="productForm"
    >
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        {{-- ── Row 1: Product Name / Datasheet / Brand ── --}}
        <div class="form-grid-3">
            <div class="form-group">
                <label class="form-label">Product Name <span>*</span></label>
                <input type="text" name="product_name" class="form-input"
                       placeholder="e.g. Jinko Tiger Pro 580W"
                       value="{{ old('product_name', $record->product_name ?? '') }}" required/>
            </div>
            <div class="form-group">
                <label class="form-label">Datasheet</label>
                <div class="form-file-wrap">
                    <input type="file" name="datasheet" accept=".pdf,.jpg,.png,.webp"/>
                </div>
                @if(isset($record) && !empty($record->datasheet))
                    <span class="form-hint">{{ $record->datasheet['original_name'] ?? 'File uploaded' }}</span>
                @endif
            </div>
            <div class="form-group">
                <label class="form-label">Brand</label>
                <select name="brand_id" class="form-select">
                    <option value="">Select Brand</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}"
                            {{ old('brand_id', $record->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ── Row 2: Category / Sub Category ── --}}
        <div class="form-grid-2" style="margin-bottom:20px;">
            <div class="form-group">
                <label class="form-label">Category <span>*</span></label>
                <select name="category_id" id="categorySelect" class="form-select" required
                        onchange="handleCategoryChange(this.value)">
                    <option value="" disabled {{ old('category_id', $record->category_id ?? '') == '' ? 'selected' : '' }}>
                        Select Category
                    </option>
                    @foreach($mainMenus as $menu)
                        <option value="{{ $menu->id }}"
                            {{ old('category_id', $record->category_id ?? '') == $menu->id ? 'selected' : '' }}>
                            {{ $menu->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Sub Category <span>*</span></label>
                <select name="sub_category_id" id="subCategorySelect" class="form-select" required
                        onchange="handleSubCategoryChange(this.value)">
                    <option value="" disabled {{ old('sub_category_id', $record->sub_category_id ?? '') == '' ? 'selected' : '' }}>
                        Select Sub Category
                    </option>
                    @foreach($subMenus as $menu)
                        <option value="{{ $menu->id }}"
                            {{ old('sub_category_id', $record->sub_category_id ?? '') == $menu->id ? 'selected' : '' }}>
                            {{ $menu->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ── Row 3: Pieces per Pallet / Pallets per Container ── --}}
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">
                    1 Pallet =
                    <span title="Number of units per pallet" style="cursor:help; color:var(--primary-d); font-weight:400;">ℹ</span>
                </label>
                <input type="text" name="pieces_per_pallet" class="form-input"
                       placeholder="e.g. 36 pcs"
                       value="{{ old('pieces_per_pallet', $record->pieces_per_pallet ?? '') }}"/>
            </div>
            <div class="form-group">
                <label class="form-label">
                    1 Container =
                    <span title="Number of units per container" style="cursor:help; color:var(--primary-d); font-weight:400;">ℹ</span>
                </label>
                <input type="text" name="pallets_per_container" class="form-input"
                       placeholder="e.g. 756 pcs"
                       value="{{ old('pallets_per_container', $record->pallets_per_container ?? '') }}"/>
            </div>
        </div>

        {{-- ── Product Description (Quill) ── --}}
        <div class="form-group" style="margin-bottom:20px;">
            <label class="form-label">Product Description</label>
            <textarea name="product_description" id="descriptionInput" style="display:none;">{{ old('product_description', $record->product_description ?? '') }}</textarea>
            <div class="quill-wrapper"><div id="quillEditor"></div></div>
        </div>

        <hr class="divider">

        {{-- ── Popular Product ── --}}
        <div class="section-header" onclick="toggleSection('popular')">
            <div class="section-title">Popular Product</div>
            <div class="section-toggle" id="toggle-popular">−</div>
        </div>
        <div id="section-popular">
            <div class="checkbox-row">
                <input type="checkbox" name="is_popular" id="isPopular" value="1"
                       {{ old('is_popular', $record->is_popular ?? false) ? 'checked' : '' }}/>
                <label for="isPopular">Mark as Popular Product</label>
            </div>
        </div>

        <hr class="divider">

        {{-- ── Real Time Price ── --}}
        <div class="section-header" onclick="toggleSection('realtime')">
            <div class="section-title">Real Time Price</div>
            <div class="section-toggle" id="toggle-realtime">−</div>
        </div>
        <div id="section-realtime">
            <div class="toggle-wrap">
                <label class="toggle-switch">
                    <input type="checkbox" name="real_time_price" value="1"
                           {{ old('real_time_price', $record->real_time_price ?? false) ? 'checked' : '' }}/>
                    <span class="toggle-slider"></span>
                </label>
                <span class="toggle-label">Enable Real Time Price</span>
            </div>
        </div>

        <hr class="divider">

        {{-- ── Product Details ── --}}
        <div class="section-header" onclick="toggleSection('details')">
            <div class="section-title">Product Details</div>
            <div class="section-toggle" id="toggle-details">−</div>
        </div>
        <div id="section-details">
            <div id="productDetailsWrapper">
                @if($mode === 'edit' && isset($record) && !empty($record->product_details))
                    {{-- Edit mode: render saved {label, value, unit} rows --}}
                    <table class="details-table">
                        <thead>
                            <tr>
                                <th class="center">S.No</th>
                                <th>Label</th>
                                <th style="width:200px;">Value</th>
                                <th style="width:160px;">Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($record->product_details as $i => $detail)
                            <tr>
                                <td class="sno">{{ $i + 1 }}.</td>
                                <td>
                                    <input type="text"
                                           name="product_details[{{ $i }}][label]"
                                           value="{{ $detail['label'] ?? '' }}"
                                           placeholder="Label"/>
                                </td>
                                <td>
                                    <input type="text"
                                           name="product_details[{{ $i }}][value]"
                                           value="{{ $detail['value'] ?? '' }}"
                                           placeholder="Enter value"/>
                                </td>
                                <td>
                                    <input type="text"
                                           name="product_details[{{ $i }}][unit]"
                                           value="{{ $detail['unit'] ?? '' }}"
                                           placeholder="e.g. W, kg, mm"/>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="details-empty" id="detailsEmptyMsg">
                        ← Please select a <strong>Sub Category</strong> above to load product detail options.
                    </div>
                @endif
            </div>
        </div>

        <hr class="divider">

        {{-- ── Measurement Details ── --}}
        <div class="section-header" onclick="toggleSection('measurement')">
            <div class="section-title">Measurement Details</div>
            <div class="section-toggle" id="toggle-measurement">−</div>
        </div>
        <div id="section-measurement">
            @php $m = $record->measurement_details ?? []; @endphp
            <div class="form-grid-3" style="margin-bottom:16px;">
                <div class="form-group">
                    <label class="form-label">Height</label>
                    <div class="measure-row">
                        <input type="number" name="height" class="form-input" step="0.01"
                               placeholder="e.g. 2278"
                               value="{{ old('height', $m['height'] ?? '') }}"/>
                        <input type="text" name="height_unit" class="form-input unit-input"
                               placeholder="mm"
                               value="{{ old('height_unit', $m['height_unit'] ?? '') }}"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Width</label>
                    <div class="measure-row">
                        <input type="number" name="width" class="form-input" step="0.01"
                               placeholder="e.g. 1134"
                               value="{{ old('width', $m['width'] ?? '') }}"/>
                        <input type="text" name="width_unit" class="form-input unit-input"
                               placeholder="mm"
                               value="{{ old('width_unit', $m['width_unit'] ?? '') }}"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Depth</label>
                    <div class="measure-row">
                        <input type="number" name="depth" class="form-input" step="0.01"
                               placeholder="e.g. 35"
                               value="{{ old('depth', $m['depth'] ?? '') }}"/>
                        <input type="text" name="depth_unit" class="form-input unit-input"
                               placeholder="mm"
                               value="{{ old('depth_unit', $m['depth_unit'] ?? '') }}"/>
                    </div>
                </div>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Weight</label>
                    <div class="measure-row">
                        <input type="number" name="weight" class="form-input" step="0.01"
                               placeholder="e.g. 32.5"
                               value="{{ old('weight', $m['weight'] ?? '') }}"/>
                        <input type="text" name="weight_unit" class="form-input unit-input"
                               placeholder="kg"
                               value="{{ old('weight_unit', $m['weight_unit'] ?? '') }}"/>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-save">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                Save
            </button>
        </div>

    </form>
</div>

<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
(function () {
    var quill = new Quill('#quillEditor', {
        theme: 'snow',
        placeholder: 'Write product description here...',
        modules: {
            toolbar: [
                [{ header: [1,2,3,4,5,6,false] }],
                [{ font: [] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ color: [] }, { background: [] }],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['link', 'image', 'video'],
                ['clean']
            ]
        }
    });
    var existing = document.getElementById('descriptionInput').value;
    if (existing && existing.trim()) quill.clipboard.dangerouslyPasteHTML(existing);
    document.getElementById('productForm').addEventListener('submit', function () {
        var html = quill.root.innerHTML;
        document.getElementById('descriptionInput').value = (html === '<p><br></p>') ? '' : html;
    });
})();

function toggleSection(name) {
    var section = document.getElementById('section-' + name);
    var toggle  = document.getElementById('toggle-' + name);
    var isHidden = section.style.display === 'none';
    section.style.display = isHidden ? 'block' : 'none';
    toggle.textContent    = isHidden ? '−' : '+';
}

// ── Category change: reload sub categories ────────────
function handleCategoryChange(categoryId) {
    if (!categoryId) return;

    const subSelect = document.getElementById('subCategorySelect');

    fetch('{{ route("admin.products.sub-menus-by-main") }}?main_menu_id=' + categoryId, {
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(r => r.json())
    .then(data => {
        subSelect.innerHTML = '<option value="" disabled selected>Select Sub Category</option>';
        data.subMenus.forEach(sm => {
            const id = sm.id || sm._id;
            subSelect.innerHTML += `<option value="${id}">${sm.name}</option>`;
        });
        clearProductDetails();
    })
    .catch(() => clearProductDetails());
}

// ── Sub Category change: load product detail options ──
function handleSubCategoryChange(subCategoryId) {
    if (!subCategoryId) {
        clearProductDetails();
        return;
    }

    const wrapper = document.getElementById('productDetailsWrapper');
    wrapper.innerHTML = '<div class="details-loading">⏳ Loading options...</div>';

    fetch('{{ route("admin.products.options-by-submenu") }}?sub_menu_id=' + subCategoryId, {
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.options || data.options.length === 0) {
            wrapper.innerHTML = '<div class="details-empty">No options found for this sub category. Add options from the <strong>Product Detail Options</strong> page first.</div>';
            return;
        }
        renderDetailsTable(data.options, savedDetails);
    })
    .catch(() => {
        wrapper.innerHTML = '<div class="details-empty">Failed to load options. Please try again.</div>';
    });
}

function clearProductDetails() {
    document.getElementById('productDetailsWrapper').innerHTML =
        '<div class="details-empty" id="detailsEmptyMsg">← Please select a <strong>Sub Category</strong> above to load product detail options.</div>';
}

// Renders {label, value, unit} rows from AJAX options.
// label is pre-filled from option_name.
// If the option has attached units, shows a dropdown; otherwise a free-text input.
// Saved edit-mode values keyed by label — populated by Blade below
var savedDetails = @if($mode === 'edit' && isset($record) && !empty($record->product_details))
    (function() {
        var map = {};
        @foreach($record->product_details as $detail)
            map[{{ json_encode($detail['label'] ?? '') }}] = {
                value : {{ json_encode($detail['value'] ?? '') }},
                unit  : {{ json_encode($detail['unit']  ?? '') }}
            };
        @endforeach
        return map;
    })()
@else
    {}
@endif;

function renderDetailsTable(options, savedMap) {
    savedMap = savedMap || {};

    let html = `
        <table class="details-table">
            <thead>
                <tr>
                    <th class="center">S.No</th>
                    <th>Label</th>
                    <th style="width:200px;">Value</th>
                    <th style="width:160px;">Unit</th>
                </tr>
            </thead>
            <tbody>`;

    options.forEach((option, i) => {
        const label     = option.option_name || '';
        const saved     = savedMap[label] || {};
        const savedVal  = saved.value || '';
        const savedUnit = saved.unit  || '';

        let unitField = '';
        if (option.units && option.units.length > 0) {
            let opts = '<option value="">Select</option>';
            option.units.forEach(u => {
                const uname = u.unit_name || '';
                const sel   = (savedUnit === uname) ? 'selected' : '';
                opts += `<option value="${uname}" ${sel}>${uname}</option>`;
            });
            unitField = `<select name="product_details[${i}][unit]">${opts}</select>`;
        } else {
            unitField = `<input type="text"
                                name="product_details[${i}][unit]"
                                value="${savedUnit}"
                                placeholder="e.g. W, kg, mm"/>`;
        }

        html += `
            <tr>
                <td class="sno">${i + 1}.</td>
                <td>
                    <input type="text"
                           name="product_details[${i}][label]"
                           value="${label}"
                           placeholder="Label"/>
                </td>
                <td>
                    <input type="text"
                           name="product_details[${i}][value]"
                           value="${savedVal}"
                           placeholder="Enter value"/>
                </td>
                <td>${unitField}</td>
            </tr>`;
    });

    html += '</tbody></table>';
    document.getElementById('productDetailsWrapper').innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function () {
    @if($mode === 'edit' && isset($record) && $record->sub_category_id)
        handleSubCategoryChange('{{ $record->sub_category_id }}');
    @endif
});
</script>

@endsection

@else


{{-- ══════════════   INDEX MODE   ════════════════ --}}



@section('title', 'Products')

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
    .data-table td { padding:14px 16px; font-size:13.5px; color:var(--text); border-bottom:1px solid #F1F5F9; vertical-align:middle; }
    .data-table tr:last-child td { border-bottom:none; }
    .data-table tbody tr:nth-child(odd)  td { background:white; }
    .data-table tbody tr:nth-child(even) td { background:#FAFBFD; }
    .data-table tbody tr:hover td { background:#E0F2FE !important; }
    .product-thumb { width:80px; height:60px; object-fit:contain; border-radius:6px; border:1px solid var(--border); display:block; margin:0 auto; }
    .no-image { font-size:12px; color:#CBD5E1; text-align:center; }
    .badge { display:inline-flex; align-items:center; justify-content:center; padding:5px 14px; border-radius:6px; font-size:12px; font-weight:700; min-width:80px; }
    .badge-verified { background:#10B981; color:white; }
    .badge-rejected { background:#EF4444; color:white; }
    .badge-pending  { background:#F59E0B; color:white; }
    .updater-badge { background:var(--primary); color:white; padding:4px 10px; border-radius:5px; font-size:11px; font-weight:700; white-space:nowrap; display:inline-block; max-width:100px; overflow:hidden; text-overflow:ellipsis; }
    .action-btns { display:flex; align-items:center; justify-content:center; gap:6px; }
    .action-icon { width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; border:1.5px solid transparent; background:none; border-radius:7px; transition:all .15s; text-decoration:none; }
    .action-icon svg { width:15px; height:15px; }
    .action-icon.verify { color:#10B981; border-color:#A7F3D0; background:#ECFDF5; }
    .action-icon.reject { color:#6B7280; border-color:#E5E7EB; background:#F9FAFB; }
    .action-icon.edit   { color:#D97706; border-color:#FDE68A; background:#FFFBEB; }
    .action-icon.delete { color:#DC2626; border-color:#FECACA; background:#FEF2F2; }
    .action-icon:hover  { transform:translateY(-2px) scale(1.08); box-shadow:0 3px 8px rgba(0,0,0,.12); }
    .action-icon.verify:hover { background:#D1FAE5; border-color:#10B981; }
    .action-icon.reject:hover { background:#F3F4F6; border-color:#9CA3AF; }
    .action-icon.edit:hover   { background:#FEF3C7; border-color:#F59E0B; }
    .action-icon.delete:hover { background:#FEE2E2; border-color:#EF4444; }
    .table-footer { padding:12px 20px; font-size:13px; color:var(--muted); border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; background:#FAFBFD; }
    .pagination { display:flex; gap:3px; list-style:none; }
    .pagination li a,.pagination li span { display:flex; align-items:center; justify-content:center; min-width:32px; height:32px; padding:0 8px; border-radius:6px; border:1.5px solid var(--border); font-size:13px; font-weight:500; text-decoration:none; color:var(--text); background:white; transition:all .15s; }
    .pagination li.active span { background:var(--primary-d); border-color:var(--primary-d); color:white; }
    .pagination li a:hover { border-color:var(--primary); color:var(--primary); background:var(--primary-l); }
    .empty-state { text-align:center; padding:52px 20px; color:var(--muted); }
    .empty-state svg { width:42px; height:42px; margin:0 auto 12px; opacity:.2; display:block; }
    .empty-state p { font-size:14px; font-weight:500; }
    .alert-success { padding:12px 16px; background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; border-radius:8px; font-size:13.5px; font-weight:500; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Products</h1>
    <a href="{{ route('admin.products.create') }}"
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
        <form method="GET" action="{{ route('admin.products.index') }}" class="search-group">
            <label>Search:</label>
            <div class="search-input-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Product, Brand..." class="search-input"/>
            </div>
        </form>
        <form method="GET" action="{{ route('admin.products.index') }}" class="entries-group">
            Show
            <select name="entries" class="entries-select" onchange="this.form.submit()">
                @foreach([10,25,50,100] as $n)
                    <option value="{{ $n }}" {{ request('entries',10) == $n ? 'selected' : '' }}>{{ $n }}</option>
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
                <th>Product Name</th>
                <th style="width:120px;">Brand</th>
                <th class="center" style="width:160px;">Verification Status</th>
                <th class="center" style="width:140px;">Updated By</th>
                <th class="center" style="width:130px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $index => $product)
            <tr>
                <td class="center" style="font-weight:700; color:var(--muted); font-size:13px;">
                    {{ $products->firstItem() + $index }}
                </td>
                <td style="font-weight:600; max-width:300px;">{{ $product->product_name }}</td>
                <td style="color:var(--muted);">{{ $product->brand_name ?? '—' }}</td>
                <td class="center">
                    <span class="badge badge-{{ $product->verification_status ?? 'pending' }}">
                        {{ ucfirst($product->verification_status ?? 'pending') }}
                    </span>
                </td>
                <td class="center">
                    <span class="updater-badge" title="{{ $product->updated_by ?? '' }}">
                        {{ $product->updated_by ?? '—' }}
                    </span>
                </td>
                <td>
                    <div class="action-btns">
                        @if(($product->verification_status ?? 'pending') === 'pending')
                            {{-- Verify --}}
                            <button class="action-icon verify" title="Verify"
                                    onclick="document.getElementById('verify-{{ $product->id }}').submit();">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </button>
                            <form id="verify-{{ $product->id }}" method="POST"
                                  action="{{ route('admin.products.verify', $product->id) }}" style="display:none;">
                                @csrf @method('PATCH')
                            </form>
                            {{-- Reject --}}
                            <button class="action-icon reject" title="Reject"
                                    onclick="document.getElementById('reject-{{ $product->id }}').submit();">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <circle cx="12" cy="12" r="9"/>
                                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                                </svg>
                            </button>
                            <form id="reject-{{ $product->id }}" method="POST"
                                  action="{{ route('admin.products.reject', $product->id) }}" style="display:none;">
                                @csrf @method('PATCH')
                            </form>
                        @endif
                        {{-- Edit --}}
                        <a href="{{ route('admin.products.edit', $product->id) }}"
                           class="action-icon edit" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </a>
                        {{-- Delete --}}
                        <button class="action-icon delete" title="Delete"
                                onclick="if(confirm('Delete this product?')) document.getElementById('del-{{ $product->id }}').submit();">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </button>
                        <form id="del-{{ $product->id }}" method="POST"
                              action="{{ route('admin.products.destroy', $product->id) }}" style="display:none;">
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
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        </svg>
                        <p>No products yet. Click <strong>Add +</strong> to create one.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="table-footer">
        <span>
            {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}
            of {{ $products->total() }} entries
        </span>
        {{ $products->appends(request()->query())->links() }}
    </div>
</div>

@endsection

@endif