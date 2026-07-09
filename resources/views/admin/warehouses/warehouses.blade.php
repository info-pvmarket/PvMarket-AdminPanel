@if($mode === 'create' || $mode === 'edit')

{{-- ═══════════════════════════════════════════════ --}}
{{-- ══════════   CREATE / EDIT MODE   ════════════ --}}
{{-- ═══════════════════════════════════════════════ --}}

@extends('layouts.admin')
@section('title', $mode === 'create' ? 'Add Warehouse' : 'Edit Warehouse')

@section('styles')
<style>
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; white-space:nowrap; }
    .btn-back:hover { background:#334155; }
    
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,.04); margin-bottom:20px; }
    .form-group { display:flex; flex-direction:column; gap:6px; margin-bottom:20px; }
    .form-label { font-size:13px; font-weight:600; color:var(--text); }
    .form-label span { color:var(--danger); margin-left:2px; }
    .form-input { width:100%; padding:9px 13px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; color:var(--text); outline:none; transition:border-color .2s, box-shadow .2s; background:white; }
    .form-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .form-input::placeholder { color:#CBD5E1; }
    .form-actions { display:flex; justify-content:flex-end; padding-top:20px; border-top:1px solid var(--border); margin-top:8px; }
    .btn-save { display:inline-flex; align-items:center; gap:8px; padding:10px 28px; background:#10B981; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; transition:background .15s, box-shadow .2s; }
    .btn-save:hover { background:#059669; box-shadow:0 4px 14px rgba(16,185,129,.35); }
    .alert-error { padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px; }
</style>
@endsection

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">
        {{ $mode === 'create' ? 'Add Warehouse' : 'Edit Warehouse' }}
    </h1>
    <a href="{{ route('admin.warehouses.index') }}" class="btn-back">← Back</a>
</div>

<div class="content-panel">
    @if($errors->any())
        <div class="alert-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST"
          action="{{ $mode === 'create' ? route('admin.warehouses.store') : route('admin.warehouses.update', $record->id) }}">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <div class="form-group">
            <label class="form-label">Warehouse Name <span>*</span></label>
            <input type="text" name="warehouse_name" class="form-input"
                   placeholder="e.g. Focus Freight International"
                   value="{{ old('warehouse_name', $record->warehouse_name ?? '') }}" required/>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-save">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                Save
            </button>
        </div>
    </form>
</div>
@endsection

@else

{{-- ═══════════════════════════════════════════════ --}}
{{-- ══════════════   INDEX MODE   ════════════════ --}}
{{-- ═══════════════════════════════════════════════ --}}


@section('title', 'Warehouses Payments History')

@section('styles')
<style>
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .table-toolbar { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--border); gap:12px; flex-wrap:wrap; background:#FAFBFD; }
    .search-group { display:flex; align-items:center; gap:8px; }
    .search-group label { font-size:13.5px; color:var(--text); font-weight:600; }
    .search-input-wrap { position:relative; }
    .search-input-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#CBD5E1; pointer-events:none; }
    .search-input { padding:8px 12px 8px 34px; border:1.5px solid var(--border); border-radius:7px; font-family:inherit; font-size:13px; outline:none; width:220px; color:var(--text); transition:border-color .2s; background:white; }
    .search-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .search-input::placeholder { color:#CBD5E1; }
    .entries-group { display:flex; align-items:center; gap:7px; font-size:13.5px; color:var(--muted); font-weight:500; }
    .entries-select { padding:7px 26px 7px 10px; border:1.5px solid var(--border); border-radius:7px; font-family:inherit; font-size:13px; font-weight:600; color:var(--text); background:white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 7px center; -webkit-appearance:none; appearance:none; outline:none; cursor:pointer; }
    .entries-select:hover,.entries-select:focus { border-color:var(--primary); }
    .data-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .data-table tbody tr:last-child td:first-child { border-bottom-left-radius: 12px; }
    .data-table tbody tr:last-child td:last-child { border-bottom-right-radius: 12px; }
    .data-table thead { background: #F0F9FF; }
    .data-table th {
        padding: 11px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        color: var(--primary-d);
        text-transform: uppercase;
        letter-spacing: .5px;
        border-bottom: 2px solid #BAE6FD;
        white-space: nowrap;
    }
    .data-table th.center,
    .data-table td.center { text-align: center; }
    .panel-header-count {
        background: rgba(255,255,255,.2);
        color: white;
        font-size: 12px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
    }
    .panel-header {
        background: linear-gradient(135deg, var(--primary-d) 0%, var(--primary) 100%);
        padding: 14px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .panel-header-title {
        font-size: 14px;
        font-weight: 700;
        color: white;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .data-table td {
        padding: 14px 16px;
        font-size: 13.5px;
        color: var(--text);
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
        transition: background .1s;
    }
    .data-table tr:last-child td { border-bottom: none; }
    .data-table tbody tr:nth-child(odd) td  { background: white; }
    .data-table tbody tr:nth-child(even) td { background: #FAFBFD; }
    .data-table tbody tr:hover td { background: #E0F2FE !important; }
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 18px;
        background: var(--primary-d);
        color: white;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        flex: 0 0 auto;
        margin-left: auto;
        transition: background .15s, transform .1s, box-shadow .2s;
    }
    .btn-back:hover {
        background: var(--primary);
        box-shadow: 0 4px 12px rgba(14,165,233,.3);
        transform: translateY(-1px);
    }
    .badge { display:inline-flex; align-items:center; justify-content:center; padding:5px 16px; border-radius:6px; font-size:12.5px; font-weight:700; min-width:76px; }
    .badge-paid    { background:#10B981; color:white; }
    .badge-pending { background:#F59E0B; color:white; }
    .created-user { display:flex; flex-direction:column; gap:4px; min-width:210px; }
    .created-user-name { font-weight:700; color:var(--text); font-size:13.5px; line-height:1.2; }
    .created-user-meta { display:flex; align-items:center; gap:6px; color:var(--muted); font-size:12px; line-height:1.2; }
    .created-user-meta a { color:var(--primary-d); text-decoration:none; font-weight:600; }
    .created-user-meta a:hover { color:var(--primary); text-decoration:underline; }
    .created-user-empty { color:var(--muted); font-size:12.5px; font-weight:600; }
    .mark-paid-btn { display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:#10B981; background:none; border:none; cursor:pointer; font-family:inherit; padding:0; transition:color .15s; }
    .mark-paid-btn:hover { color:#059669; }
    .action-btns { display:flex; align-items:center; justify-content:center; gap:6px; }
    .action-icon { width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; border:1.5px solid transparent; background:none; border-radius:7px; transition:all .15s; text-decoration:none; }
    .action-icon svg { width:15px; height:15px; }
    .action-icon.edit   { color:#D97706; border-color:#FDE68A; background:#FFFBEB; }
    .action-icon.delete { color:#DC2626; border-color:#FECACA; background:#FEF2F2; }
    .action-icon:hover  { transform:translateY(-2px) scale(1.08); box-shadow:0 3px 8px rgba(0,0,0,.12); }
    .action-icon.edit:hover   { background:#FEF3C7; border-color:#F59E0B; }
    .action-icon.delete:hover { background:#FEE2E2; border-color:#EF4444; }
    .table-footer { padding:12px 20px; font-size:13px; color:var(--muted); border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; background:#FAFBFD; border-radius:0 0 12px 12px; }
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
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Warehouses Payments History</h1>
    <a href="{{ route('admin.dashboard') }}" class="btn-back">← Back</a>
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

    {{-- Blue header strip --}}
<div class="panel-header">
    <div class="panel-header-title">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <!-- Roof -->
            <path d="M3 10L12 3L21 10" />
            <!-- Building body -->
            <path d="M5 10V21H19V10" />
            <!-- Door -->
            <path d="M10 21V14H14V21" />
        </svg>
        All Warehouses
    </div>
    <span class="panel-header-count">{{ $warehouses->total() }} total</span>
</div>

    <div class="table-toolbar">
        <form method="GET" action="{{ route('admin.warehouses.index') }}" class="search-group">
            <label>Search:</label>
            <div class="search-input-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search By Menu" class="search-input"/>
            </div>
        </form>
        <form method="GET" action="{{ route('admin.warehouses.index') }}" class="entries-group">
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
                <th class="center" style="width:80px;">S.No</th>
                <th>Warehouse name</th>
                <th style="width:260px;">Created user</th>
                <th class="center" style="width:180px;">Payment Status</th>
                <th class="center" style="width:180px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($warehouses as $index => $warehouse)
            @php
                $createdUserId = (string) ($warehouse->user_id ?? $warehouse->created_by ?? '');
                $createdUser = $createdUserId !== '' ? ($usersMap[$createdUserId] ?? null) : null;
                $createdUserPhone = $createdUser?->mobile ?? $createdUser?->phone ?? null;
            @endphp
            <tr>
                <td class="center" style="font-weight:700; color:var(--muted); font-size:13px;">
                    {{ $warehouses->firstItem() + $index }}
                </td>
                <td style="font-weight:500;">{{ lang($warehouse, 'warehouse_name') }}</td>
                <td>
                    @if($createdUser)
                        <div class="created-user">
                            <div class="created-user-name">{{ lang($createdUser, 'name') ?: 'Unnamed user' }}</div>
                            <div class="created-user-meta">
                                <span>Email:</span>
                                <a href="mailto:{{ $createdUser->email }}">{{ $createdUser->email }}</a>
                            </div>
                            <div class="created-user-meta">
                                <span>Phone:</span>
                                <span>{{ $createdUserPhone ?: '-' }}</span>
                            </div>
                        </div>
                    @else
                        <span class="created-user-empty">User not available</span>
                    @endif
                </td>
                <td class="center">
                    <span class="badge badge-{{ $warehouse->payment_status ?? 'pending' }}">
                        {{ ucfirst($warehouse->payment_status ?? 'pending') }}
                    </span>
                </td>
                <td class="center">
                    <div class="action-btns">
                        @if(($warehouse->payment_status ?? 'pending') === 'pending')
                            {{-- Mark As Paid --}}
                            <button class="mark-paid-btn"
                                    onclick="document.getElementById('paid-{{ $warehouse->id }}').submit();">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                Mark As Paid
                            </button>
                            <form id="paid-{{ $warehouse->id }}" method="POST"
                                  action="{{ route('admin.warehouses.mark-paid', $warehouse->id) }}" style="display:none;">
                                @csrf @method('PATCH')
                            </form>
                        @endif
                        {{-- Edit --}}
                        <a href="{{ route('admin.warehouses.edit', $warehouse->id) }}"
                           class="action-icon edit" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </a>
                        {{-- Delete --}}
                        <button class="action-icon delete" title="Delete"
                                onclick="if(confirm('Delete this warehouse?')) document.getElementById('del-{{ $warehouse->id }}').submit();">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </button>
                        <form id="del-{{ $warehouse->id }}" method="POST"
                              action="{{ route('admin.warehouses.destroy', $warehouse->id) }}" style="display:none;">
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
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        <p>No warehouses yet.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="table-footer">
    <span>
        {{ $warehouses->firstItem() ?? 0 }}–{{ $warehouses->lastItem() ?? 0 }}
        of {{ $warehouses->total() }} entries
    </span>
    <x-admin.pagination :paginator="$warehouses" />
</div>
</div>

@endsection

@endif
