@if($mode === 'create' || $mode === 'edit')
@extends('layouts.admin')
{{-- ═══ CREATE / EDIT MODE ═══ --}}

@section('title', $mode === 'create' ? 'Add Specification' : 'Edit Specification')

@section('styles')
<style>
    .page-header-wrap { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
    .page-header-wrap h1 { font-size:22px; font-weight:800; color:var(--text); }
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; white-space:nowrap; }
    .btn-back:hover { background:#334155; }

    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:32px; box-shadow:0 1px 4px rgba(0,0,0,.04); }

    .form-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px 24px; margin-bottom:28px; }

    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-label { font-size:13px; font-weight:600; color:var(--text); }
    .form-label span { color:var(--danger); margin-left:2px; }

    .form-input, .form-select {
        width:100%; padding:9px 13px;
        border:1.5px solid var(--border); border-radius:8px;
        font-family:inherit; font-size:13.5px; color:var(--text);
        outline:none; transition:border-color .2s, box-shadow .2s; background:white;
    }
    .form-input:focus, .form-select:focus {
        border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1);
    }
    .form-input::placeholder { color:#CBD5E1; }
    .form-select { appearance:none; background:white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 12px center; cursor:pointer; }

    .form-actions { display:flex; justify-content:flex-end; padding-top:20px; border-top:1px solid var(--border); margin-top:20px; clear:both; }
    .btn-save { display:inline-flex; align-items:center; gap:8px; padding:10px 28px; background:#10B981; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; transition:background .15s, box-shadow .2s; }
    .btn-save:hover { background:#059669; box-shadow:0 4px 14px rgba(16,185,129,.35); }

    .multi-select-wrap { position:relative; width:100%; }
.multi-select-trigger {
    width:100%; padding:9px 13px;
    border:1.5px solid var(--border); border-radius:8px;
    font-family:inherit; font-size:13.5px; color:var(--text);
    background:white; cursor:pointer;
    display:flex; align-items:center; justify-content:space-between;
    user-select:none;
}
.multi-select-trigger:hover { border-color:var(--primary); }
.multi-select-options {
    display:none;
    position:absolute; top:calc(100% + 4px); left:0; right:0;
    background:white; border:1.5px solid var(--border); border-radius:8px;
    box-shadow:0 4px 16px rgba(0,0,0,.1);
    max-height:200px; overflow-y:auto; z-index:100;
    padding:6px;
}
.multi-select-options.open { display:block; }
.multi-select-item {
    display:flex; align-items:center; gap:10px;
    padding:8px 10px; border-radius:6px;
    font-size:13.5px; color:var(--text);
    cursor:pointer; transition:background .1s;
}
.multi-select-item:hover { background:#F0F9FF; }
.multi-select-item input[type="checkbox"] {
    width:16px; height:16px; accent-color:var(--primary); cursor:pointer;
}
    .alert-error { padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px; }
</style>
@endsection

@section('content')



<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">{{ $mode === 'create' ? 'Add Product specifications' : 'Edit Product specifications' }}</h1>
    <a href="{{ route('admin.products.detail-options.index') }}" class="btn-back">← Back</a>
</div>

<div class="content-panel">

    @if($errors->any())
        <div class="alert-error">{{ $errors->first() }}</div>
    @endif

    <form
        method="POST"
        action="{{ $mode === 'create'
            ? route('admin.products.detail-options.store')
            : route('admin.products.detail-options.update', $record->id) }}"
        id="optionForm"
    >
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        {{-- Top 3-column grid --}}
        <div class="form-grid">

            {{-- Option Name --}}
            <div class="form-group">
                <label class="form-label">Option Name: <span>*</span></label>
                <input
                    type="text"
                    name="option_name"
                    class="form-input"
                    placeholder="e.g. Serial Number"
                    value="{{ old('option_name', $record->name ?? '') }}"
                    required
                />
            </div>

            {{-- Main Menu --}}
            <div class="form-group">
                <label class="form-label">Main Menu:</label>
                <select name="category_id" id="detailMainMenuSelect" class="form-select" onchange="handleDetailMainMenuChange(this.value)">
                    <option value=""> Select Category </option>
                    @foreach($mainMenus as $menu)
                        <option value="{{ $menu->id }}"
                            {{ old('category_id',     $record->category_id     ?? '') == $menu->id ? 'selected' : '' }}>
                            {{ $menu->category_name }} 
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Sub Menu --}}
            <div class="form-group">
                <label class="form-label">Sub Menu:</label>
                <select name="sub_category_id" id="detailSubMenuSelect" class="form-select">
                    <option value=""> Select Sub Category </option>
                    @foreach($subMenus as $menu)
                        <option value="{{ $menu->id }}"
                            {{ old('sub_category_id', $record->sub_category_id ?? '') == $menu->id ? 'selected' : '' }}>
                            {{ $menu->sub_category_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Unit --}}
<div class="form-group">
    <label class="form-label">Unit:</label>
    <div class="multi-select-wrap" id="unitDropdown">
        <div class="multi-select-trigger" onclick="toggleMultiSelect()">
            <span id="unitTriggerText"> Select Units </span>
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
        <div class="multi-select-options" id="unitOptions">
            @foreach($units as $unit)
                <label class="multi-select-item">
                    <input type="checkbox" name="unit_ids[]" value="{{ (string)$unit->id }}"
    {{ in_array((string)$unit->id, collect(old('unit_ids', $record->unit_ids ?? []))->map(function($id) {
    if (is_array($id) && isset($id['$oid'])) return $id['$oid'];
    return (string) $id;
})->toArray()) ? 'checked' : '' }}
                        onchange="updateTriggerText()"/>
                    {{ $unit->unit_name }}
                </label>
            @endforeach
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

<script>
function toggleMultiSelect() {
    document.getElementById('unitOptions').classList.toggle('open');
}
function updateTriggerText() {
    const checked = document.querySelectorAll('#unitOptions input:checked');
    const trigger = document.getElementById('unitTriggerText');
    if (checked.length === 0) {
        trigger.textContent = 'Select Units';
    } else {
        trigger.textContent = Array.from(checked).map(c => c.closest('label').textContent.trim()).join(', ');
    }
}
document.addEventListener('click', function(e) {
    if (!document.getElementById('unitDropdown').contains(e.target)) {
        document.getElementById('unitOptions').classList.remove('open');
    }
});
document.addEventListener('DOMContentLoaded', updateTriggerText);
</script>

<script>
function handleDetailMainMenuChange(mainMenuId) {
    if (!mainMenuId) return;

    const subSelect = document.getElementById('detailSubMenuSelect');
    subSelect.innerHTML = '<option value="">Loading...</option>';

    fetch('{{ route("admin.products.sub-menus-by-main") }}?main_menu_id=' + mainMenuId, {
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(r => r.json())
    .then(data => {
        subSelect.innerHTML = '<option value=""> Select Sub Menu </option>';
        data.subMenus.forEach(sm => {
    const id = sm._id?.$oid || sm._id || sm.id;
    subSelect.innerHTML += `<option value="${id}">${sm.sub_category_name}</option>`;
});
        // Re-select the previously saved sub_menu_id in edit mode (if it matches)
        const savedSubMenuId = '{{ old('sub_category_id', $record->sub_category_id ?? '') }}';
        if (savedSubMenuId) {
            subSelect.value = savedSubMenuId;
        }
    })
    .catch(() => {
        subSelect.innerHTML = '<option value=""> Select Sub Menu </option>';
    });
}

// On page load in edit mode: if a main menu is already selected, filter sub menus
document.addEventListener('DOMContentLoaded', function () {
    const mainSelect = document.getElementById('detailMainMenuSelect');
    if (mainSelect && mainSelect.value) {
        handleDetailMainMenuChange(mainSelect.value);
    }
});
</script>


@endsection



@else

{{-- ═══ INDEX MODE ═══ --}}

@section('title', 'Product Specifications')

@section('styles')
<style>
    .page-title { font-size:22px; font-weight:800; color:var(--text); }
    .page-title span { color:var(--primary); }

    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .table-toolbar { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--border); gap:12px; flex-wrap:wrap; background:#FAFBFD; }
    .filter-form { display:flex; align-items:center; gap:8px; flex:1 1 720px; flex-wrap:wrap; }
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
    .filter-select { min-width:180px; padding:8px 30px 8px 11px; border:1.5px solid var(--border); border-radius:7px; font-family:inherit; font-size:13px; color:var(--text); background:white; outline:none; cursor:pointer; }
    .filter-select:hover,.filter-select:focus { border-color:var(--primary); }
    .filter-btn,.filter-reset { display:inline-flex; align-items:center; justify-content:center; min-height:35px; padding:7px 14px; border-radius:7px; font-size:13px; font-weight:700; text-decoration:none; cursor:pointer; }
    .filter-btn { border:1px solid var(--primary); background:var(--primary); color:white; }
    .filter-reset { border:1px solid var(--border); background:white; color:var(--muted); }
    .filter-reset:hover { border-color:var(--danger); color:var(--danger); }

    .table-scroll { width:100%; overflow-x:auto; }
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
    .action-icon.delete { color:#DC2626; border-color:#FECACA; background:#FEF2F2; }
    .action-icon:hover  { transform:translateY(-2px) scale(1.08); box-shadow:0 3px 8px rgba(0,0,0,.12); }
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
    <h1 class="page-title">
        <span>Products</span> - Specifications
    </h1>
    <a href="{{ route('admin.products.detail-options.create') }}"
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
        <form method="GET" action="{{ route('admin.products.detail-options.index') }}" class="filter-form" id="specificationFilters">
            <div class="search-group">
                <label>Search:</label>
                <div class="search-input-wrap">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by option..." class="search-input"/>
                </div>
            </div>

            <select name="category_id" id="specificationCategoryFilter" class="filter-select"
                    aria-label="Filter by category"
                    onchange="document.getElementById('specificationSubCategoryFilter').value=''; this.form.submit();">
                <option value="">All Categories</option>
                @foreach($mainMenus as $menu)
                    <option value="{{ (string) $menu->_id }}" @selected(request('category_id') === (string) $menu->_id)>
                        {{ $menu->category_name }}
                    </option>
                @endforeach
            </select>

            <select name="sub_category_id" id="specificationSubCategoryFilter" class="filter-select"
                    aria-label="Filter by subcategory" onchange="this.form.submit();">
                <option value="">All Subcategories</option>
                @foreach($subMenus as $menu)
                    <option value="{{ (string) $menu->_id }}" @selected(request('sub_category_id') === (string) $menu->_id)>
                        {{ $menu->sub_category_name }}
                    </option>
                @endforeach
            </select>

            <input type="hidden" name="entries" value="{{ request('entries', 10) }}">
            <button type="submit" class="filter-btn">Filter</button>

            @if(request()->filled('search') || request()->filled('category_id') || request()->filled('sub_category_id'))
                <a href="{{ route('admin.products.detail-options.index', ['entries' => request('entries', 10)]) }}" class="filter-reset">Reset</a>
            @endif
        </form>

        <form method="GET" action="{{ route('admin.products.detail-options.index') }}" class="entries-group">
            Show
            <select name="entries" class="entries-select" onchange="this.form.submit()">
                @foreach([10,25,50,100] as $n)
                    <option value="{{ $n }}" {{ request('entries',10) == $n ? 'selected':'' }}>{{ $n }}</option>
                @endforeach
            </select>
            entries
            @foreach(['search', 'category_id', 'sub_category_id'] as $filter)
                @if(request()->filled($filter))
                    <input type="hidden" name="{{ $filter }}" value="{{ request($filter) }}">
                @endif
            @endforeach
        </form>
    </div>

    <div class="table-scroll">
    <table class="data-table">
    <thead>
        <tr>
            <th class="center" style="width:60px;">S.No</th>
<th class="center">Option Name</th>
<th class="center">Category</th>
<th class="center">Subcategory</th>
<th class="center" style="width:90px;">Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($options as $index => $option)
        <tr>
            <td class="center" style="font-weight:700; color:var(--muted); font-size:13px;">
                {{ $options->firstItem() + $index }}
            </td>
            <td class="center" style="font-weight:500;">{{ lang($option, 'name') }}</td>
            <td class="center">{{ lang($option, 'category_name') ?: ($categoryNames[(string) ($option->category_id ?? '')] ?? '—') }}</td>
            <td class="center">{{ lang($option, 'sub_category_name') ?: ($subCategoryNames[(string) ($option->sub_category_id ?? '')] ?? '—') }}</td>
            <td>
                <div class="action-btns">
                    <a href="{{ route('admin.products.detail-options.edit', $option->id) }}"
                       class="action-icon edit" title="Edit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </a>
                    <button class="action-icon delete" title="Delete"
                        onclick="if(confirm('Delete this option?')) document.getElementById('del-{{ $option->id }}').submit();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                        </svg>
                    </button>
                    <form id="del-{{ $option->id }}" method="POST"
                          action="{{ route('admin.products.detail-options.destroy', $option->id) }}"
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
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <p>No product specifications yet. Click <strong>Add +</strong> to create one.</p>
                </div>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
    </div>


    <div class="table-footer">
    <span>{{ $options->firstItem() ?? 0 }}–{{ $options->lastItem() ?? 0 }} of {{ $options->total() }} entries</span>
    @if ($options->hasPages())
    <nav style="display:flex; align-items:center; gap:4px;">
        @if ($options->onFirstPage())
            <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">‹</span>
        @else
            <a href="{{ $options->previousPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">‹</a>
        @endif
        @foreach ($options->getUrlRange(1, $options->lastPage()) as $page => $url)
            @if ($page == $options->currentPage())
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--primary-d);background:var(--primary-d);color:white;font-size:13px;font-weight:700;">{{ $page }}</span>
            @elseif ($page == 1 || $page == $options->lastPage() || abs($page - $options->currentPage()) <= 2)
                <a href="{{ $url }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:13px;font-weight:500;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">{{ $page }}</a>
            @elseif ($page == $options->currentPage() - 3 || $page == $options->currentPage() + 3)
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--muted);font-size:13px;">…</span>
            @endif
        @endforeach
        @if ($options->hasMorePages())
            <a href="{{ $options->nextPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">›</a>
        @else
            <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">›</span>
        @endif
    </nav>
    @endif
</div>

</div>

@endsection

@endif
