@if($mode === 'create' || $mode === 'edit')

{{-- ═══════════════════════════════════════════════ --}}
{{-- ══════════   CREATE / EDIT MODE   ════════════ --}}
{{-- ═══════════════════════════════════════════════ --}}

@extends('layouts.admin')
@section('title', $mode === 'create' ? 'Add Offer' : 'Edit Offer')

@section('styles')
<style>
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; white-space:nowrap; }
    .btn-back:hover { background:#334155; }
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,.04); margin-bottom:20px; }
    .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:18px 24px; margin-bottom:20px; }
    .form-group { display:flex; flex-direction:column; gap:6px; margin-bottom:20px; }
    .form-label { font-size:13px; font-weight:600; color:var(--text); }
    .form-label span { color:var(--danger); margin-left:2px; }
    .form-input, .form-select { width:100%; padding:9px 13px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; color:var(--text); outline:none; transition:border-color .2s, box-shadow .2s; background:white; }
    .form-input:focus, .form-select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .form-select { appearance:none; background:white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 12px center; cursor:pointer; }
    .form-actions { display:flex; justify-content:flex-end; padding-top:20px; border-top:1px solid var(--border); margin-top:8px; }
    .btn-save { display:inline-flex; align-items:center; gap:8px; padding:10px 28px; background:#10B981; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; transition:background .15s, box-shadow .2s; }
    .btn-save:hover { background:#059669; box-shadow:0 4px 14px rgba(16,185,129,.35); }
    .alert-error { padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px; }
</style>
@endsection

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">
        {{ $mode === 'create' ? 'Add Offer' : 'Edit Offer' }}
    </h1>
    <a href="{{ route('admin.offers.index') }}" class="btn-back">← Back</a>
</div>

<div class="content-panel">
    @if($errors->any())
        <div class="alert-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST"
          action="{{ $mode === 'create' ? route('admin.offers.store') : route('admin.offers.update', $record->id) }}">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <div class="form-grid-2">
            {{-- Product --}}
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Product <span>*</span></label>
                <select name="product_id" class="form-select" required>
                    <option value="" disabled {{ old('product_id', $record->product_id ?? '') == '' ? 'selected' : '' }}>
                        Select Product
                    </option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}"
                            {{ old('product_id', $record->product_id ?? '') == $product->id ? 'selected' : '' }}>
                            {{ $product->product_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            {{-- Warehouse --}}
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Warehouse <span>*</span></label>
                <select name="warehouse_id" class="form-select" required>
                    <option value="" disabled {{ old('warehouse_id', $record->warehouse_id ?? '') == '' ? 'selected' : '' }}>
                        Select Warehouse
                    </option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}"
                            {{ old('warehouse_id', $record->warehouse_id ?? '') == $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
            </div>
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


@section('title', 'Offers')

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

    /* Status toggle */
    .toggle-switch { position:relative; width:44px; height:24px; display:inline-block; }
    .toggle-switch input { opacity:0; width:0; height:0; }
    .toggle-slider { position:absolute; cursor:pointer; inset:0; background:#CBD5E1; border-radius:24px; transition:.3s; }
    .toggle-slider::before { content:''; position:absolute; height:18px; width:18px; left:3px; bottom:3px; background:white; border-radius:50%; transition:.3s; }
    .toggle-switch input:checked + .toggle-slider { background:#3B82F6; }
    .toggle-switch input:checked + .toggle-slider::before { transform:translateX(20px); }

    .action-btns { display:flex; align-items:center; justify-content:center; gap:6px; }
    .action-icon { width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; border:1.5px solid transparent; background:none; border-radius:7px; transition:all .15s; text-decoration:none; }
    .action-icon svg { width:15px; height:15px; }
    .action-icon.verify { color:#10B981; border-color:#A7F3D0; background:#ECFDF5; }
    .action-icon.delete { color:#DC2626; border-color:#FECACA; background:#FEF2F2; }
    .action-icon:hover  { transform:translateY(-2px) scale(1.08); box-shadow:0 3px 8px rgba(0,0,0,.12); }
    .action-icon.verify:hover { background:#D1FAE5; border-color:#10B981; }
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
    .unique-id { font-family:monospace; font-size:13px; font-weight:600; color:var(--muted); letter-spacing:.5px; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Offers</h1>
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
                <path d="M20 12V22H4V12"/><path d="M22 7H2v5h20V7z"/>
                <path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/>
                <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
            </svg>
            All Offers
        </div>
        <span class="panel-header-count">{{ $offers->total() }} total</span>
    </div>

    <div class="table-toolbar">
        <form method="GET" action="{{ route('admin.offers.index') }}" class="search-group">
            <label>Search:</label>
            <div class="search-input-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search By Menu" class="search-input"/>
            </div>
        </form>
        <form method="GET" action="{{ route('admin.offers.index') }}" class="entries-group">
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
                <th style="width:130px;">Offer Unique Id</th>
                <th>Product Name</th>
                <th style="width:220px;">Warehouse Name</th>
                <th class="center" style="width:160px;">Payment Status</th>
                <th class="center" style="width:90px;">Status</th>
                <th class="center" style="width:120px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($offers as $index => $offer)
            <tr>
                <td class="center" style="font-weight:700; color:var(--muted); font-size:13px;">
                    {{ $offers->firstItem() + $index }}
                </td>
                <td><span class="unique-id">{{ $offer->unique_id }}</span></td>
                <td style="font-weight:500;">{{ lang($offer, 'product_name') }}</td>
                <td style="color:var(--muted);">{{ lang($offer, 'warehouse_name') }}</td>
                <td class="center">
                    <span class="badge badge-{{ $offer->payment_status ?? 'pending' }}">
                        {{ ucfirst($offer->payment_status ?? 'pending') }}
                    </span>
                </td>
                <td class="center">
                    {{-- Status toggle --}}
                    <label class="toggle-switch" title="Toggle status"
                           onclick="document.getElementById('toggle-{{ $offer->id }}').submit(); return false;">
                        <input type="checkbox" {{ $offer->is_active ? 'checked' : '' }} readonly/>
                        <span class="toggle-slider"></span>
                    </label>
                    <form id="toggle-{{ $offer->id }}" method="POST"
                          action="{{ route('admin.offers.toggle-status', $offer->id) }}" style="display:none;">
                        @csrf @method('PATCH')
                    </form>
                </td>
                <td>
                    <div class="action-btns">
                        @if(($offer->payment_status ?? 'pending') === 'pending')
                            {{-- Mark as paid (verify icon) --}}
                            <button class="action-icon verify" title="Mark as Paid"
                                    onclick="document.getElementById('paid-{{ $offer->id }}').submit();">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </button>
                            <form id="paid-{{ $offer->id }}" method="POST"
                                  action="{{ route('admin.offers.mark-paid', $offer->id) }}" style="display:none;">
                                @csrf @method('PATCH')
                            </form>
                        @endif
                        {{-- Delete --}}
                        <button class="action-icon delete" title="Delete"
                                onclick="if(confirm('Delete this offer?')) document.getElementById('del-{{ $offer->id }}').submit();">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </button>
                        <form id="del-{{ $offer->id }}" method="POST"
                              action="{{ route('admin.offers.destroy', $offer->id) }}" style="display:none;">
                            @csrf @method('DELETE')
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M20 12V22H4V12"/><path d="M22 7H2v5h20V7z"/>
                            <path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/>
                            <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                        </svg>
                        <p>No offers yet.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="table-footer">
        <span>
            {{ $offers->firstItem() ?? 0 }}–{{ $offers->lastItem() ?? 0 }}
            of {{ $offers->total() }} entries
        </span>
        {{ $offers->appends(request()->query())->links() }}
    </div>
</div>

@endsection

@endif