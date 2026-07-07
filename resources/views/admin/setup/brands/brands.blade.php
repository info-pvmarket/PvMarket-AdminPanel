{{-- views/admin/setup/brands/brands.blade.php --}}
{{-- Handles: index | create | edit --}}

@extends('layouts.admin')

@if($mode === 'create')

{{-- ═══ CREATE MODE ═══ --}}
@section('title', 'Add Brand')

@section('styles')
<style>
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .form-table { width:100%; border-collapse:collapse; }
    .form-table thead tr { border-bottom:2px solid var(--border); }
    .form-table th { padding:10px 14px; font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.5px; text-align:left; }
    .form-table td { padding:10px 14px; vertical-align:middle; }
    .form-table tbody tr { border-bottom:1px solid #F1F5F9; }
    .form-table tbody tr:last-child { border-bottom:none; }
    .sno { font-size:13px; font-weight:700; color:var(--muted); width:60px; }
    .form-row-input { width:100%; padding:9px 13px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; color:var(--text); outline:none; transition:border-color .2s,box-shadow .2s; background:white; }
    .form-row-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .form-row-input::placeholder { color:#CBD5E1; }
    .form-file-wrap { border:1.5px solid var(--border); border-radius:8px; overflow:hidden; display:flex; align-items:center; background:white; }
    .form-file-wrap input[type="file"] { flex:1; padding:7px 10px; border:none; outline:none; font-family:inherit; font-size:13px; background:transparent; cursor:pointer; }
    .form-file-wrap input[type="file"]::-webkit-file-upload-button { padding:5px 12px; background:var(--light); border:none; border-right:1px solid var(--border); font-family:inherit; font-size:12px; font-weight:600; cursor:pointer; margin-right:8px; }
    .btn-add-row { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--primary); color:white; border:none; border-radius:8px; font-size:13.5px; font-weight:600; cursor:pointer; font-family:inherit; transition:background .15s; }
    .btn-add-row:hover { background:var(--primary-d); }
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; white-space:nowrap; border:1.5px solid var(--text); }
    .btn-save { display:inline-flex; align-items:center; gap:8px; padding:10px 28px; background:#10B981; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; transition:background .15s; }
    .btn-save:hover { background:#059669; }
    .btn-remove { background:none; border:none; cursor:pointer; color:#EF4444; padding:6px; border-radius:6px; transition:background .15s; }
    .btn-remove:hover { background:#FEF2F2; }
    .alert-error { padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Add Brand</h1>
    <a href="{{ route('admin.setup.brands.index') }}" class="btn-back">← Back</a>
</div>

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<div class="content-panel">

    <div style="display:flex; justify-content:flex-end; margin-bottom:16px;">
        <button type="button" class="btn-add-row" onclick="addRow()">+ Add More</button>
    </div>

    <form method="POST" action="{{ route('admin.setup.brands.store') }}"
          enctype="multipart/form-data" id="createForm">
        @csrf

        <table class="form-table">
            <thead>
                <tr>
                    <th style="width:60px;">S.No</th>
                    <th style="min-width:180px;">Brand Name</th>
                    <th style="min-width:220px;">Brand Image</th>
                    <th style="min-width:160px;">Alt Tag</th>
                    <th style="min-width:110px;">Menu Order</th>
                    <th style="min-width:110px;">Show Menu</th>
                    <th style="width:60px;">Action</th>
                </tr>
            </thead>
            <tbody id="rowsBody">
                <tr id="row-1">
                    <td class="sno">1.</td>
                    <td>
                        <input type="text" name="brands[0][name]" class="form-row-input"
                               placeholder="e.g. Jinko Solar" required/>
                    </td>
                    <td>
                        <div class="form-file-wrap">
                            <input type="file" name="brands[0][image]" accept="image/*"/>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="brands[0][alt_tag]" class="form-row-input"
                               placeholder="e.g. Jinko Solar Logo"/>
                    </td>
                    <td>
                        <input type="number" name="brands[0][menu_order]" class="form-row-input"
                               placeholder="e.g. 1" min="0"/>
                    </td>
                    <td>
    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
        <input type="checkbox" name="brands[0][can_show_menu]" value="1"
               style="width:16px;height:16px;cursor:pointer;" checked/>
        <span style="font-size:13px;color:var(--text);">Visible</span>
    </label>
</td>
                    <td>
                        <button type="button" class="btn-remove" onclick="removeRow(this)" title="Remove">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div style="display:flex; justify-content:flex-end; margin-top:24px; padding-top:20px; border-top:1px solid var(--border);">
            <button type="submit" class="btn-save">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                Save
            </button>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
let rowCount = 1;

function addRow() {
    rowCount++;
    const idx = rowCount - 1;
    const body = document.getElementById('rowsBody');
    const tr = document.createElement('tr');
    tr.id = 'row-' + rowCount;
    tr.innerHTML = `
        <td class="sno">${rowCount}.</td>
        <td>
            <input type="text" name="brands[${idx}][name]" class="form-row-input"
                   placeholder="e.g. Jinko Solar" required/>
        </td>
        <td>
            <div class="form-file-wrap">
                <input type="file" name="brands[${idx}][image]" accept="image/*"/>
            </div>
        </td>
        <td>
            <input type="text" name="brands[${idx}][alt_tag]" class="form-row-input"
                   placeholder="e.g. Jinko Solar Logo"/>
        </td>
        <td>
            <input type="number" name="brands[${idx}][menu_order]" class="form-row-input"
                   placeholder="e.g. 1" min="0"/>
        </td>
        <td>
    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
        <input type="checkbox" name="brands[${idx}][can_show_menu]" value="1"
               style="width:16px;height:16px;cursor:pointer;" checked/>
        <span style="font-size:13px;color:var(--text);">Visible</span>
    </label>
</td>
        <td>
            <button type="button" class="btn-remove" onclick="removeRow(this)" title="Remove">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
            </button>
        </td>
    `;
    body.appendChild(tr);
    renumber();
}

function removeRow(btn) {
    if (document.querySelectorAll('#rowsBody tr').length === 1) return;
    btn.closest('tr').remove();
    renumber();
}

function renumber() {
    document.querySelectorAll('#rowsBody tr').forEach((tr, i) => {
        tr.querySelector('.sno').textContent = (i + 1) + '.';
        tr.querySelectorAll('input').forEach(inp => {
            inp.name = inp.name.replace(/brands\[\d+\]/, `brands[${i}]`);
        });
    });
}
</script>
@endsection


@elseif($mode === 'edit')


{{-- ═══ EDIT MODE ═══ --}}
@section('title', 'Edit Brand')

@section('styles')
<style>
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .form-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px 28px; margin-bottom:20px; }
    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-label { font-size:13px; font-weight:600; color:var(--text); }
    .form-label span { color:var(--danger); margin-left:2px; }
    .form-input { width:100%; padding:9px 13px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; color:var(--text); outline:none; transition:border-color .2s,box-shadow .2s; background:white; }
    .form-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .form-input::placeholder { color:#CBD5E1; }
    .form-file-wrap { border:1.5px solid var(--border); border-radius:8px; overflow:hidden; display:flex; align-items:center; background:white; }
    .form-file-wrap input[type="file"] { flex:1; padding:7px 10px; border:none; outline:none; font-family:inherit; font-size:13px; background:transparent; cursor:pointer; }
    .form-file-wrap input[type="file"]::-webkit-file-upload-button { padding:5px 12px; background:var(--light); border:none; border-right:1px solid var(--border); font-family:inherit; font-size:12px; font-weight:600; cursor:pointer; margin-right:8px; }
    .form-hint { font-size:11px; color:var(--muted); margin-top:3px; }
    .btn-save { display:inline-flex; align-items:center; gap:8px; padding:10px 28px; background:#10B981; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; transition:background .15s; }
    .btn-save:hover { background:#059669; }
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; white-space:nowrap; border:1.5px solid var(--text); }
    .alert-error { padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px; }
    .current-img { height:50px; border-radius:6px; border:1px solid var(--border); object-fit:contain; display:block; margin-bottom:8px; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Edit Brand</h1>
    <a href="{{ route('admin.setup.brands.index') }}" class="btn-back">← Back</a>
</div>

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<div class="content-panel">
    <form method="POST"
          action="{{ route('admin.setup.brands.update', $record->id) }}"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-grid">

            {{-- Brand Name --}}
            <div class="form-group">
                <label class="form-label">Brand Name <span>*</span></label>
                <input type="text" name="name" class="form-input"
                       placeholder="e.g. Jinko Solar"
                       value="{{ old('name', $record->name) }}" required/>
            </div>

            {{-- Slug --}}
            <div class="form-group">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-input"
                       placeholder="auto-generated if blank"
                       value="{{ old('slug', $record->slug) }}"/>
                <span class="form-hint">Leave blank to auto-generate from name</span>
            </div>

            {{-- Alt Tag --}}
            <div class="form-group">
                <label class="form-label">Alt Tag</label>
                <input type="text" name="alt_tag" class="form-input"
                       placeholder="e.g. Jinko Solar Logo"
                       value="{{ old('alt_tag', $record->alt_tag) }}"/>
            </div>

            {{-- Brand Image --}}
            <div class="form-group">
                <label class="form-label">Brand Image</label>
                @php
                    $currentImageUrl = $record->image['url'] ?? $record->image['path'] ?? null;
                    $currentImageSrc = $currentImageUrl
                        ? (preg_match('/^https?:\/\//i', $currentImageUrl) ? $currentImageUrl : asset('storage/' . ltrim($currentImageUrl, '/')))
                        : null;
                @endphp
                @if($currentImageSrc)
                    <img src="{{ $currentImageSrc }}"
                         class="current-img"
                         alt="{{ $record->image_alt ?? $record->alt_tag ?? $record->name }}"/>
                @endif
                <div class="form-file-wrap">
                    <input type="file" name="image" accept="image/*"/>
                </div>
                <span class="form-hint">Leave blank to keep current image</span>
            </div>

            {{-- Menu Order --}}
            <div class="form-group">
                <label class="form-label">Menu Order</label>
                <input type="number" name="menu_order" class="form-input"
                       placeholder="e.g. 1" min="0"
                       value="{{ old('menu_order', $record->menu_order ?? 0) }}"/>
                <span class="form-hint">Lower number appears first</span>
            </div>

        {{-- Can Show Menu --}}
<div class="form-group">
    <label class="form-label">Show in Menu</label>
    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:9px 0;">
        <input type="checkbox" name="can_show_menu" value="1"
               style="width:16px;height:16px;cursor:pointer;"
               {{ old('can_show_menu', $record->can_show_menu ?? true) ? 'checked' : '' }}/>
        <span style="font-size:13.5px;color:var(--text);">Visible in menu</span>
    </label>
</div>

        </div>

        <div style="display:flex; justify-content:flex-end; padding-top:20px; border-top:1px solid var(--border);">
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
</div>

@endsection


@else


{{-- ═══ INDEX MODE ═══ --}}
@section('title', 'Brands')

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
    .table-scroll { width:100%; overflow-x:auto; }
    .table-scroll::-webkit-scrollbar { height:8px; }
    .table-scroll::-webkit-scrollbar-track { background:#F1F5F9; }
    .table-scroll::-webkit-scrollbar-thumb { background:#CBD5E1; border-radius:999px; }
    .data-table { width:100%; min-width:720px; border-collapse:collapse; }
    .data-table thead { background:#F0F9FF; }
    .data-table th { padding:11px 16px; text-align:left; font-size:12px; font-weight:700; color:var(--primary-d); text-transform:uppercase; letter-spacing:.5px; border-bottom:2px solid #BAE6FD; white-space:nowrap; }
    .data-table th.center,.data-table td.center { text-align:center; }
    .data-table td { padding:16px; font-size:13.5px; color:var(--text); border-bottom:1px solid #F1F5F9; vertical-align:middle; }
    .data-table tr:last-child td { border-bottom:none; }
    .data-table tbody tr:nth-child(odd) td { background:white; }
    .data-table tbody tr:nth-child(even) td { background:#FAFBFD; }
    .data-table tbody tr:hover td { background:#E0F2FE !important; }
    .brand-img-wrap { display:flex; align-items:center; justify-content:center; height:64px; }
    .brand-img { max-height:52px; max-width:130px; object-fit:contain; border-radius:6px; }
    .brand-img-placeholder { width:104px; height:52px; border:1.5px dashed var(--border); background:var(--light); border-radius:6px; display:flex; align-items:center; justify-content:center; color:#CBD5E1; }
    .brand-name { font-weight:700; font-size:15px; color:var(--text); }
    .brand-slug { font-size:12px; color:var(--muted); margin-top:3px; font-family:monospace; }
    .order-badge { display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:28px; padding:0 8px; background:#F0F9FF; border:1.5px solid #BAE6FD; border-radius:6px; font-size:13px; font-weight:700; color:var(--primary-d); }
    .action-btns { display:flex; align-items:center; justify-content:center; gap:8px; }
    .action-icon { width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; border:1.5px solid transparent; background:none; border-radius:7px; transition:all .15s; text-decoration:none; }
    .action-icon svg { width:15px; height:15px; }
    .action-icon.edit   { color:#D97706; border-color:#FDE68A; background:#FFFBEB; }
    .action-icon.toggle { color:#059669; border-color:#A7F3D0; background:#ECFDF5; }
    .action-icon.delete { color:#DC2626; border-color:#FECACA; background:#FEF2F2; }
    .action-icon:hover { transform:translateY(-2px) scale(1.08); box-shadow:0 3px 8px rgba(0,0,0,.12); }
    .action-icon.edit:hover   { background:#FEF3C7; border-color:#F59E0B; }
    .action-icon.toggle:hover { background:#D1FAE5; border-color:#10B981; }
    .action-icon.delete:hover { background:#FEE2E2; border-color:#EF4444; }
    .table-footer { padding:12px 20px; font-size:13px; color:var(--muted); border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; background:#FAFBFD; }
    .pager { display:flex; align-items:center; gap:4px; flex-wrap:wrap; justify-content:flex-end; }
    .pager-link,
    .pager-current,
    .pager-disabled,
    .pager-ellipsis { display:inline-flex; align-items:center; justify-content:center; min-width:34px; height:34px; padding:0 9px; border-radius:6px; border:1.5px solid var(--border); background:white; font-size:13px; font-weight:600; text-decoration:none; }
    .pager-link { color:var(--text); transition:all .15s; }
    .pager-link:hover { border-color:var(--primary); color:var(--primary); background:var(--primary-l); }
    .pager-current { border-color:var(--primary-d); background:var(--primary-d); color:white; }
    .pager-disabled { color:#CBD5E1; cursor:not-allowed; }
    .pager-ellipsis { color:var(--muted); }
    @media (max-width:1100px) {
        .data-table { min-width:0; table-layout:fixed; }
        .data-table th,
        .data-table td { padding:10px 6px; font-size:12.5px; }
        .data-table th:nth-child(1),
        .data-table td:nth-child(1) { width:44px !important; }
        .data-table th:nth-child(2),
        .data-table td:nth-child(2) { width:96px !important; }
        .data-table th:nth-child(3),
        .data-table td:nth-child(3) { width:auto !important; }
        .data-table th:nth-child(4),
        .data-table td:nth-child(4) { width:64px !important; }
        .data-table th:nth-child(5),
        .data-table td:nth-child(5) { width:78px !important; }
        .data-table th:nth-child(6),
        .data-table td:nth-child(6) { width:82px !important; }
        .brand-img-wrap { height:48px; }
        .brand-img { max-height:40px; max-width:82px; }
        .brand-img-placeholder { width:74px; height:40px; }
        .brand-name { font-size:13px; }
        .brand-slug { font-size:10.5px; line-height:1.2; overflow-wrap:anywhere; }
        .order-badge { min-width:24px; height:24px; padding:0 6px; font-size:12px; }
        .action-btns { gap:4px; }
        .action-icon { width:28px; height:28px; }
        .action-icon svg { width:13px; height:13px; }
    }
    .empty-state { text-align:center; padding:52px 20px; color:var(--muted); }
    .empty-state svg { width:42px; height:42px; margin:0 auto 12px; opacity:.2; display:block; }
    .empty-state p { font-size:14px; font-weight:500; }
    .alert-success { padding:12px 16px; background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; border-radius:8px; font-size:13.5px; font-weight:500; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Brands</h1>
    <a href="{{ route('admin.setup.brands.create') }}"
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
        <form method="GET" action="{{ route('admin.setup.brands.index') }}" class="search-group">
            @if(request('entries'))
                <input type="hidden" name="entries" value="{{ request('entries') }}">
            @endif
            <label>Search:</label>
            <div class="search-input-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search By Brand Name..." class="search-input"/>
            </div>
        </form>

        <form method="GET" action="{{ route('admin.setup.brands.index') }}" class="entries-group">
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

    <div class="table-scroll">
    <table class="data-table">
        <thead>
            <tr>
                <th class="center" style="width:70px;">S.No</th>
                <th class="center" style="width:160px;">Logo</th>
                <th>Brand</th>
                <th class="center" style="width:100px;">Order</th>
                <th class="center" style="width:100px;">Menu</th>
                <th class="center" style="width:130px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($brands as $index => $brand)
            <tr>
                <td class="center" style="font-weight:700; color:var(--muted); font-size:13px;">
                    {{ $brands->firstItem() + $index }}
                </td>
                <td class="center">
                    <div class="brand-img-wrap">
                        @php
                            $brandImageUrl = $brand->image['url'] ?? $brand->image['path'] ?? null;
                            $brandImageSrc = $brandImageUrl
                                ? (preg_match('/^https?:\/\//i', $brandImageUrl) ? $brandImageUrl : asset('storage/' . ltrim($brandImageUrl, '/')))
                                : null;
                        @endphp
                        @if($brandImageSrc)
                            <img src="{{ $brandImageSrc }}"
                                 class="brand-img"
                                 alt="{{ $brand->image_alt ?? $brand->alt_tag ?? $brand->name }}"/>
                        @else
                            <div class="brand-img-placeholder">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                </td>
                <td>
                    <span class="brand-name">{{ lang($brand, 'name') }}</span>
                    @if($brand->slug)
                        <div class="brand-slug">/{{ $brand->slug }}</div>
                    @endif
                </td>
                <td class="center">
                    <span class="order-badge">{{ $brand->menu_order ?? 0 }}</span>
                </td>
                <td class="center">
    @if($brand->can_show_menu ?? true)
        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:#D1FAE5;color:#065F46;border-radius:12px;font-size:12px;font-weight:600;">
            ✓ Yes
        </span>
    @else
        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:#FEE2E2;color:#991B1B;border-radius:12px;font-size:12px;font-weight:600;">
            ✗ No
        </span>
    @endif
</td>
                <td>
                    <div class="action-btns">
                        <a href="{{ route('admin.setup.brands.edit', $brand->id) }}"
                           class="action-icon edit" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </a>
                        <button class="action-icon toggle" title="Toggle active"
                            onclick="document.getElementById('tog-{{ $brand->id }}').submit();">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="5" width="22" height="14" rx="7" ry="7"/>
                                <circle cx="{{ ($brand->is_active ?? true) ? '16' : '8' }}" cy="12" r="3" fill="currentColor"/>
                            </svg>
                        </button>
                        <form id="tog-{{ $brand->id }}" method="POST"
                              action="{{ route('admin.setup.brands.toggle', $brand->id) }}"
                              style="display:none;">
                            @csrf @method('PATCH')
                        </form>
                        <button class="action-icon delete" title="Delete"
                            onclick="if(confirm('Delete {{ addslashes($brand->name) }}?')) document.getElementById('del-{{ $brand->id }}').submit();">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </button>
                        <form id="del-{{ $brand->id }}" method="POST"
                              action="{{ route('admin.setup.brands.destroy', $brand->id) }}"
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
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <p>No brands yet. Click <strong>Add +</strong> to create one.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div class="table-footer">
    <span>{{ $brands->firstItem() ?? 0 }}–{{ $brands->lastItem() ?? 0 }} of {{ $brands->total() }} entries</span>

    @if ($brands->hasPages())
    <nav class="pager">
        @if ($brands->onFirstPage())
            <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">‹</span>
        @else
            <a href="{{ $brands->previousPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">‹</a>
        @endif

        @foreach ($brands->getUrlRange(1, $brands->lastPage()) as $page => $url)
            @if ($page == $brands->currentPage())
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--primary-d);background:var(--primary-d);color:white;font-size:13px;font-weight:700;">{{ $page }}</span>
            @elseif ($page == 1 || $page == $brands->lastPage() || abs($page - $brands->currentPage()) <= 2)
                <a href="{{ $url }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:13px;font-weight:500;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">{{ $page }}</a>
            @elseif ($page == $brands->currentPage() - 3 || $page == $brands->currentPage() + 3)
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--muted);font-size:13px;">…</span>
            @endif
        @endforeach

        @if ($brands->hasMorePages())
            <a href="{{ $brands->nextPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">›</a>
        @else
            <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">›</span>
        @endif
    </nav>
    @endif
</div>
</div>

@endsection

@endif
