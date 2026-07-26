@if($mode === 'create')
@extends('layouts.admin')
{{-- ═══ CREATE MODE ═══ --}}
@section('title', 'Add Sub Category')

@section('styles')
<style>
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .form-group { display:flex; flex-direction:column; gap:6px; margin-bottom:20px; }
    .form-label { font-size:13px; font-weight:600; color:var(--text); }
    .form-input { width:100%; padding:9px 13px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; color:var(--text); outline:none; transition:border-color .2s,box-shadow .2s; background:white; }
    .form-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    select.form-input { cursor:pointer; }
    .form-table { width:100%; border-collapse:collapse; margin-top:20px; }
    .form-table thead tr { border-bottom:2px solid var(--border); }
    .form-table th { padding:10px 14px; font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.5px; text-align:left; }
    .form-table td { padding:10px 14px; vertical-align:middle; border-bottom:1px solid #F1F5F9; }
    .form-table tbody tr:last-child td { border-bottom:none; }
    .sno { font-size:13px; font-weight:700; color:var(--muted); width:60px; }
    .form-row-input { width:100%; padding:9px 13px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; color:var(--text); outline:none; transition:border-color .2s; background:white; }
    .form-row-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .form-row-input::placeholder { color:#CBD5E1; }
    .form-file-wrap { border:1.5px solid var(--border); border-radius:8px; overflow:hidden; display:flex; align-items:center; background:white; }
    .form-file-wrap input[type="file"] { flex:1; padding:7px 10px; border:none; outline:none; font-family:inherit; font-size:13px; background:transparent; cursor:pointer; }
    .form-file-wrap input[type="file"]::-webkit-file-upload-button { padding:5px 12px; background:var(--light); border:none; border-right:1px solid var(--border); font-family:inherit; font-size:12px; font-weight:600; cursor:pointer; margin-right:8px; }
    .btn-add-row { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--primary); color:white; border:none; border-radius:8px; font-size:13.5px; font-weight:600; cursor:pointer; font-family:inherit; transition:background .15s; }
    .btn-add-row:hover { background:var(--primary-d); }
    .btn-save { display:inline-flex; align-items:center; gap:8px; padding:10px 28px; background:#10B981; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; transition:background .15s; }
    .btn-save:hover { background:#059669; }
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; white-space:nowrap; border:1.5px solid var(--text); }
    .btn-back:hover { background:#334155; border-color:#334155; }
    .btn-remove { background:none; border:none; cursor:pointer; color:#EF4444; padding:6px; border-radius:6px; transition:background .15s; }
    .btn-remove:hover { background:#FEF2F2; }
    .alert-error { padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px; }
    .toolbar-row { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; flex-wrap:wrap; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Add Sub Category</h1>
    <a href="{{ route('admin.setup.sub-menus.index') }}" class="btn-back">← Back</a>
</div>

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<div class="content-panel">
    <form method="POST" action="{{ route('admin.setup.sub-menus.store') }}" enctype="multipart/form-data" id="createForm">
        @csrf

        <div class="toolbar-row">
            <div class="form-group" style="margin-bottom:0; min-width:320px;">
                <label class="form-label">Choose Category</label>
                <select name="category_id" class="form-input" required>
                    <option value="">Select Category</option>
                    @foreach($mainMenus as $menu)
                        <option value="{{ $menu->id }}" {{ old('category_id') == $menu->id ? 'selected' : '' }}>
                            {{ $menu->category_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="button" class="btn-add-row" onclick="addRow()">+ Add More</button>
        </div>

        <table class="form-table">
            <thead>
                <tr>
                    <th style="width:60px;">S.No</th>
                    <th>Sub Category Name</th>
                    <th>Alt Tag</th>
                    <th style="width:60px;">Actions</th>
                </tr>
            </thead>
            <tbody id="rowsBody">
                <tr id="row-1">
                    <td class="sno">1.</td>
                    <td><input type="text" name="items[0][name]" class="form-row-input" placeholder="Sub Category Name" required/></td>
                    <td><input type="text" name="items[0][alt_tag]" class="form-row-input" placeholder="Alt Tag"/></td>
                    <td>
                        <button type="button" class="btn-remove" onclick="removeRow(this)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 6h18M8 6V4h8v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
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
        <td><input type="text" name="items[${idx}][name]" class="form-row-input" placeholder="Sub Category Name" required/></td>
        <td><input type="text" name="items[${idx}][alt_tag]" class="form-row-input" placeholder="Alt Tag"/></td>
        <td>
            <button type="button" class="btn-remove" onclick="removeRow(this)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18M8 6V4h8v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
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
        tr.querySelectorAll('input[type="text"]').forEach(inp => {
            inp.name = inp.name.replace(/items\[\d+\]/, `items[${i}]`);
        });
        tr.querySelectorAll('input[type="file"]').forEach(inp => {
            inp.name = inp.name.replace(/items\[\d+\]/, `items[${i}]`);
        });
    });
}
</script>
@endsection


@elseif($mode === 'edit')

{{-- ═══ EDIT MODE ═══ --}}
@section('title', 'Update Sub Category')

@section('styles')
<style>
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px; }
    .form-grid-1 { margin-bottom:20px; }
    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-label { font-size:13px; font-weight:600; color:var(--text); }
    .form-label span { color:var(--danger); }
    .form-input { width:100%; padding:9px 13px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; color:var(--text); outline:none; transition:border-color .2s,box-shadow .2s; background:white; }
    .form-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .form-input::placeholder { color:#CBD5E1; }
    textarea.form-input { resize:vertical; min-height:100px; }
    select.form-input { cursor:pointer; }
    .form-file-wrap { border:1.5px solid var(--border); border-radius:8px; overflow:hidden; display:flex; align-items:center; background:white; }
    .form-file-wrap input[type="file"] { flex:1; padding:7px 10px; border:none; outline:none; font-family:inherit; font-size:13px; background:transparent; cursor:pointer; }
    .form-file-wrap input[type="file"]::-webkit-file-upload-button { padding:5px 12px; background:var(--light); border:none; border-right:1px solid var(--border); font-family:inherit; font-size:12px; font-weight:600; cursor:pointer; margin-right:8px; }
    .form-hint { font-size:11px; color:var(--muted); margin-top:3px; }
    .current-icon { height:40px; border-radius:6px; border:1px solid var(--border); object-fit:contain; display:block; margin-bottom:8px; }
    .section-title { font-size:16px; font-weight:800; color:var(--primary-d); margin-bottom:16px; padding-bottom:10px; border-bottom:2px solid var(--primary-l); }
    .section-divider { border:none; border-top:1px solid var(--border); margin:24px 0; }
    .btn-save { display:inline-flex; align-items:center; gap:8px; padding:10px 28px; background:#10B981; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; transition:background .15s; }
    .btn-save:hover { background:#059669; }
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; white-space:nowrap; border:1.5px solid var(--text); }
    .btn-back:hover { background:#334155; border-color:#334155; }
    .alert-error { padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Update Sub Category</h1>
    <a href="{{ route('admin.setup.sub-menus.index') }}" class="btn-back">← Back</a>
</div>

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<div class="content-panel">
    <form method="POST"
          action="{{ route('admin.setup.sub-menus.update', (string) $record->id) }}"
          enctype="multipart/form-data"
          id="editForm">
        @csrf
        @method('PUT')

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Sub Category Name <span>*</span></label>
                <input type="text" name="sub_category_name" class="form-input"
                       value="{{ old('sub_category_name', $record->sub_category_name) }}" required/>
            </div>
            <div class="form-group">
                <label class="form-label">Category <span>*</span></label>
                <select name="category_id" class="form-input" required>
                    <option value="">Select Category</option>
                    @foreach($mainMenus as $menu)
                        <option value="{{ $menu->id }}"
                            {{ old('category_id', (string) $record->category_id) == (string) $menu->id ? 'selected' : '' }}>
                            {{ $menu->category_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; padding-top:20px; border-top:1px solid var(--border); margin-top:20px;">
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


@else

{{-- ═══ INDEX MODE ═══ --}}
@section('title', 'Sub Categories')

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
    .data-table tbody tr:nth-child(odd) td { background:white; }
    .data-table tbody tr:nth-child(even) td { background:#FAFBFD; }
    .data-table tbody tr:hover td { background:#E0F2FE !important; }
    .action-btns { display:flex; align-items:center; justify-content:center; gap:8px; }
    .action-icon { width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; border:1.5px solid transparent; background:none; border-radius:7px; transition:all .15s; text-decoration:none; }
    .action-icon svg { width:15px; height:15px; }
    .action-icon.edit   { color:#D97706; border-color:#FDE68A; background:#FFFBEB; }
    .action-icon.toggle { color:#10B981; border-color:#A7F3D0; background:#ECFDF5; }
    .action-icon.toggle.off { color:#94A3B8; border-color:#E2E8F0; background:#F8FAFC; }
    .action-icon.delete { color:#DC2626; border-color:#FECACA; background:#FEF2F2; }
    .action-icon:hover  { transform:translateY(-2px) scale(1.08); box-shadow:0 3px 8px rgba(0,0,0,.12); }
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
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Sub Categories</h1>
    <a href="{{ route('admin.setup.sub-menus.create') }}"
       style="display:inline-flex; align-items:center; gap:7px; padding:10px 20px;
              background:var(--primary); color:white; border-radius:8px; font-size:14px;
              font-weight:600; text-decoration:none; white-space:nowrap;"
       onmouseover="this.style.background='var(--primary-d)'"
       onmouseout="this.style.background='var(--primary)'">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Add 
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
        <form method="GET" action="{{ route('admin.setup.sub-menus.index') }}" class="search-group">
            <label>Search:</label>
            <div class="search-input-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search By Sub Category..." class="search-input"/>
            </div>
        </form>

        <form method="GET" action="{{ route('admin.setup.sub-menus.index') }}" class="entries-group">
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
                <th>Sub Category</th>
                <th>Category</th>
                <th class="center" style="width:100px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subMenus as $index => $sub)
            <tr>
                <td class="center" style="font-weight:700; color:var(--muted); font-size:13px;">
                    {{ $subMenus->firstItem() + $index }}
                </td>
                <td style="font-weight:600;">{{ lang($sub, 'sub_category_name') }}</td>
                <td>
                    <span style="display:inline-block; padding:3px 10px; background:var(--primary-l);
                                 color:var(--primary-d); border-radius:6px; font-size:12px; font-weight:600;">
                        {{ $sub->category_name ?? '—' }}
                    </span>
                </td>
                <td>
                    <div class="action-btns">
                        <a href="{{ route('admin.setup.sub-menus.edit', $sub->id) }}"
                           class="action-icon edit" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </a>

                        <form method="POST"
                              action="{{ route('admin.setup.sub-menus.toggle', $sub->id) }}"
                              style="display:contents;">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="action-icon toggle {{ $sub->is_active ? '' : 'off' }}"
                                    title="{{ $sub->is_active ? 'Active — click to deactivate' : 'Inactive — click to activate' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="1" y="5" width="22" height="14" rx="7"/>
                                    <circle cx="{{ $sub->is_active ? '16' : '8' }}" cy="12" r="4"
                                            fill="currentColor" stroke="none"/>
                                </svg>
                            </button>
                        </form>

                        <button class="action-icon delete" title="Delete"
                            onclick="if(confirm('Delete this sub category?')) document.getElementById('del-{{ $sub->id }}').submit();">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </button>
                        <form id="del-{{ $sub->id }}" method="POST"
                              action="{{ route('admin.setup.sub-menus.destroy', $sub->id) }}"
                              style="display:none;">
                            @csrf @method('DELETE')
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 6h16M4 12h10M4 18h6"/>
                        </svg>
                        <p>No sub categories yet. Click <strong>Add Sub Category</strong> to create one.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="table-footer">
    <span>{{ $subMenus->firstItem() ?? 0 }}–{{ $subMenus->lastItem() ?? 0 }} of {{ $subMenus->total() }} entries</span>

    @if ($subMenus->hasPages())
    <nav style="display:flex; align-items:center; gap:4px;">
        @if ($subMenus->onFirstPage())
            <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">‹</span>
        @else
            <a href="{{ $subMenus->previousPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">‹</a>
        @endif
        @foreach ($subMenus->getUrlRange(1, $subMenus->lastPage()) as $page => $url)
            @if ($page == $subMenus->currentPage())
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--primary-d);background:var(--primary-d);color:white;font-size:13px;font-weight:700;">{{ $page }}</span>
            @elseif ($page == 1 || $page == $subMenus->lastPage() || abs($page - $subMenus->currentPage()) <= 2)
                <a href="{{ $url }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:13px;font-weight:500;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">{{ $page }}</a>
            @elseif ($page == $subMenus->currentPage() - 3 || $page == $subMenus->currentPage() + 3)
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--muted);font-size:13px;">…</span>
            @endif
        @endforeach
        @if ($subMenus->hasMorePages())
            <a href="{{ $subMenus->nextPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">›</a>
        @else
            <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">›</span>
        @endif
    </nav>
    @endif
</div>
</div>

@endsection
@endif
