@props([
    'title'               => '',
    'createRoute'         => '',
    'searchRoute'         => '',
    'searchPlaceholder'   => 'Search...',
    'total'               => 0,
    'firstItem'           => 0,
    'lastItem'            => 0,
    'paginator'           => null,
    'showDragHandle'      => false,
])

@extends('layouts.admin')

@section('title', $title)

@section('styles')
<style>
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: nowrap;
        margin-bottom: 20px;
    }

    .page-header h1 {
        font-size: 22px;
        font-weight: 800;
        color: var(--text);
        flex: 1; 
    }

    .btn-add {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 20px;
        background: var(--primary);
        color: white;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: background .15s, transform .1s, box-shadow .2s;
        white-space: nowrap;
    }

    .btn-add:hover {
        background: var(--primary-d);
        box-shadow: 0 4px 12px rgba(14,165,233,.35);
        transform: translateY(-1px);
    }

    .content-panel {
        background: white;
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }

    .table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        border-bottom: 1px solid var(--border);
        gap: 12px;
        flex-wrap: wrap;
        background: #FAFBFD;
    }

    .search-group { display: flex; align-items: center; gap: 8px; }
    .search-group label { font-size: 13.5px; color: var(--text); font-weight: 600; }
    .search-input-wrap { position: relative; }

    .search-input-wrap svg {
        position: absolute; left: 10px; top: 50%;
        transform: translateY(-50%); color: #CBD5E1; pointer-events: none;
    }

    .search-input {
        padding: 8px 12px 8px 34px;
        border: 1.5px solid var(--border); border-radius: 7px;
        font-family: inherit; font-size: 13px; outline: none; width: 230px;
        color: var(--text); transition: border-color .2s, box-shadow .2s; background: white;
    }

    .search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(14,165,233,.1); }
    .search-input::placeholder { color: #CBD5E1; }

    .entries-group {
        display: flex; align-items: center; gap: 7px;
        font-size: 13.5px; color: var(--muted); font-weight: 500;
    }

    .entries-select {
        padding: 7px 26px 7px 10px;
        border: 1.5px solid var(--border); border-radius: 7px;
        font-family: inherit; font-size: 13px; font-weight: 600; color: var(--text);
        background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 7px center;
        -webkit-appearance: none; appearance: none; outline: none; cursor: pointer; transition: border-color .2s;
    }

    .entries-select:hover, .entries-select:focus { border-color: var(--primary); }

    .data-table { width: 100%; border-collapse: collapse; }
    .data-table thead { background: #F0F9FF; }

    .data-table th {
        padding: 11px 16px; text-align: left; font-size: 12px; font-weight: 700;
        color: var(--primary-d); text-transform: uppercase; letter-spacing: .5px;
        border-bottom: 2px solid #BAE6FD; white-space: nowrap;
    }

    .data-table th.center,
    .data-table td.center { text-align: center; }

    .data-table td {
        padding: 14px 16px; font-size: 13.5px; color: var(--text);
        border-bottom: 1px solid #F1F5F9; vertical-align: middle; transition: background .1s;
    }

    .data-table tr:last-child td { border-bottom: none; }
    .data-table tbody tr:nth-child(odd) td  { background: white; }
    .data-table tbody tr:nth-child(even) td { background: #FAFBFD; }
    .data-table tbody tr:hover td { background: #E0F2FE !important; }

    .drag-handle {
        cursor: grab; color: #CBD5E1;
        display: flex; align-items: center; justify-content: center;
        padding: 4px; border-radius: 4px; transition: color .15s, background .15s;
    }

    .drag-handle:hover { color: var(--primary); background: var(--primary-l); }

    .action-btns { display: flex; align-items: center; justify-content: center; gap: 8px; }

    .action-icon {
        width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
        cursor: pointer; border: 1.5px solid transparent; background: none; border-radius: 7px;
        transition: all .15s; text-decoration: none;
    }

    .action-icon svg { width: 15px; height: 15px; }
    .action-icon.edit   { color: #D97706; border-color: #FDE68A; background: #FFFBEB; }
    .action-icon.toggle { color: #059669; border-color: #A7F3D0; background: #ECFDF5; }
    .action-icon.delete { color: #DC2626; border-color: #FECACA; background: #FEF2F2; }
    .action-icon.email  { color: #2563EB; border-color: #BFDBFE; background: #EFF6FF; }

    .action-icon:hover { transform: translateY(-2px) scale(1.08); box-shadow: 0 3px 8px rgba(0,0,0,.12); }
    .action-icon.edit:hover   { background: #FEF3C7; border-color: #F59E0B; }
    .action-icon.toggle:hover { background: #D1FAE5; border-color: #10B981; }
    .action-icon.delete:hover { background: #FEE2E2; border-color: #EF4444; }
    .action-icon.email:hover  { background: #DBEAFE; border-color: #3B82F6; }

    .thumb {
        width: 80px; height: 50px; border-radius: 6px; border: 1px solid var(--border);
        background: var(--light); display: flex; align-items: center; justify-content: center;
        overflow: hidden; margin: 0 auto;
    }

    .thumb img { width: 100%; height: 100%; object-fit: cover; }

    .thumb-placeholder {
        width: 80px; height: 50px; border-radius: 6px; border: 1.5px dashed var(--border);
        background: var(--light); display: flex; align-items: center; justify-content: center;
        color: #CBD5E1; margin: 0 auto;
    }

    .redirect-link { color: var(--primary-d); font-weight: 500; font-size: 13.5px; text-decoration: none; }
    .redirect-link:hover { color: var(--primary); text-decoration: underline; }

    .table-footer {
        padding: 12px 20px; font-size: 13px; color: var(--muted);
        border-top: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px; background: #FAFBFD;
    }

    .pagination { display: flex; gap: 3px; list-style: none; }

    .pagination li a,
    .pagination li span {
        display: flex; align-items: center; justify-content: center;
        min-width: 32px; height: 32px; padding: 0 8px; border-radius: 6px;
        border: 1.5px solid var(--border); font-size: 13px; font-weight: 500;
        text-decoration: none; color: var(--text); background: white; transition: all .15s;
    }

    .pagination li.active span { background: var(--primary-d); border-color: var(--primary-d); color: white; }
    .pagination li a:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-l); transform: translateY(-1px); }

    .empty-state { text-align: center; padding: 52px 20px; color: var(--muted); }
    .empty-state svg { width: 42px; height: 42px; margin: 0 auto 12px; opacity: .2; display: block; }
    .empty-state p { font-size: 14px; font-weight: 500; }

    .dragging { opacity: .4; }
    .drag-over td { background: #E0F2FE !important; border-top: 2px solid var(--primary) !important; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">{{ $title }}</h1>
    @if($createRoute)
        <a href="{{ $createRoute }}" class="btn-add">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add +
        </a>
    @endif
</div>

<div class="content-panel">

    <div class="table-toolbar">
        <form method="GET" action="{{ $searchRoute }}" class="search-group">
            <label>Search:</label>
            <div class="search-input-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="{{ $searchPlaceholder }}"
                    class="search-input"
                />
            </div>
            @foreach(request()->except(['search','entries','page']) as $key => $val)
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endforeach
        </form>

        <form method="GET" action="{{ $searchRoute }}" class="entries-group">
            Show
            <select name="entries" class="entries-select" onchange="this.form.submit()">
                @foreach([10,25,50,100] as $n)
                    <option value="{{ $n }}" {{ request('entries', 10) == $n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
            </select>
            entries
            @foreach(request()->except(['entries','page']) as $key => $val)
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endforeach
        </form>
    </div>

    <table class="data-table" id="dataTable">
        <thead>
            <tr>
                @if($showDragHandle)
                    <th style="width:40px;"></th>
                @endif
                <th class="center" style="width:70px;">S.No</th>
                {{ $columns }}
            </tr>
        </thead>
        <tbody id="sortableBody">
            {{ $rows }}
        </tbody>
    </table>

    <div class="table-footer">
        <span>{{ $firstItem }}–{{ $lastItem }} of {{ $total }} entries</span>
        @if(isset($pagination) && trim((string) $pagination) !== '')
            {{ $pagination }}
        @elseif($paginator)
            <x-admin.pagination :paginator="$paginator->appends(request()->query())" />
        @endif
    </div>

</div>

@endsection

@section('scripts')
@if($showDragHandle)
<script>
    const tbody = document.getElementById('sortableBody');
    let dragRow  = null;

    tbody.querySelectorAll('tr[data-id]').forEach(row => {
        row.addEventListener('dragstart', () => {
            dragRow = row;
            setTimeout(() => row.classList.add('dragging'), 0);
        });
        row.addEventListener('dragend', () => {
            row.classList.remove('dragging');
            tbody.querySelectorAll('tr').forEach(r => r.classList.remove('drag-over'));
            document.dispatchEvent(new CustomEvent('rowsReordered', {
                detail: { ids: [...tbody.querySelectorAll('tr[data-id]')].map(r => r.dataset.id) }
            }));
        });
        row.addEventListener('dragover', e => {
            e.preventDefault();
            if (row !== dragRow) {
                tbody.querySelectorAll('tr').forEach(r => r.classList.remove('drag-over'));
                row.classList.add('drag-over');
                const mid = row.getBoundingClientRect().top + row.getBoundingClientRect().height / 2;
                if (e.clientY < mid) tbody.insertBefore(dragRow, row);
                else tbody.insertBefore(dragRow, row.nextSibling);
            }
        });
    });
</script>
@endif
@endsection
