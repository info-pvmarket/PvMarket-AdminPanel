@extends('layouts.admin')

@section('title', 'Manage Listings')

@section('styles')
<style>
    :root {
        --text:      #1F2937;
        --muted:     #6B7280;
        --border:    #E5E7EB;
        --blue:      #3B82F6;
        --green:     #16a34a;
        --red:       #EF4444;
        --bg:        #F3F4F6;
    }

    /* ── Page shell ── */
    .page-wrap { padding: 28px; background: var(--bg); min-height: 100vh; }

    /* ── Header ── */
    .page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 8px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .page-title { font-size: 22px; font-weight: 800; color: var(--text); margin: 0; }

    .page-subtitle { font-size: 12px; color: var(--primary); margin-top: 4px; line-height: 1.8; }

    .header-right {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    /* warehouse dropdown */
    .wh-wrap { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
    .wh-label { font-size: 12px; font-weight: 600; color: var(--muted); }

    .wh-combobox {
        position: relative;
        min-width: 260px;
    }

    .warehouse-search {
        width: 100%;
        padding: 9px 36px 9px 14px;
        border: 1.5px solid var(--border);
        border-radius: 9px;
        font-family: inherit;
        font-size: 13px;
        color: var(--text);
        background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394A3B8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 12px center;
        outline: none;
        transition: border-color .2s;
    }
    .warehouse-search:focus { border-color: var(--primary); }

    .wh-options {
        display: none;
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 80;
        max-height: 260px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 9px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .14);
        padding: 6px;
    }

    .wh-options.open { display: block; }

    .wh-option,
    .wh-empty {
        width: 100%;
        border: 0;
        background: transparent;
        text-align: left;
        font-family: inherit;
        font-size: 13px;
        color: var(--text);
        border-radius: 7px;
        padding: 9px 10px;
    }

    .wh-option {
        cursor: pointer;
    }

    .wh-option:hover,
    .wh-option.active {
        background: #FFF7ED;
        color: var(--primary);
    }

    .wh-empty {
        color: var(--muted);
        cursor: default;
    }

    /* ── Warehouse clear button ── */
    .wh-clear-btn {
        display: none;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        color: var(--muted);
        background: none;
        border: none;
        cursor: pointer;
        font-family: inherit;
        padding: 2px 0;
        text-decoration: underline;
    }
    .wh-clear-btn:hover { color: var(--red); }

    .btn-add {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 22px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 9px;
        font-family: inherit;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
        transition: background .15s;
    }
    .btn-add:hover { background: var(--primary-d); color: white; }

    /* ── Filter tabs ── */
    .filter-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 20px;
        margin-top: 16px;
    }

    .filter-tab {
        display: inline-block;
        padding: 7px 20px;
        border-radius: 25px;
        font-size: 13px;
        cursor: pointer;
        border: 1.5px solid var(--border);
        background: white;
        color: var(--text);
        text-decoration: none;
        transition: all .2s;
        font-family: inherit;
        font-weight: 500;
    }
    .filter-tab.active,
    .filter-tab:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    /* ── Active warehouse indicator ── */
    .wh-active-badge {
        display: none;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        background: #FFF7ED;
        border: 1px solid #FDBA74;
        border-radius: 20px;
        font-size: 12px;
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 12px;
    }

    /* ── Alert ── */
    .alert-success {
        background: #d1fae5;
        border: 1px solid #6ee7b7;
        color: #065f46;
        padding: 12px 18px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    /* ── Listing card ── */
    .listing-card {
        background: white;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 20px 24px;
        margin-bottom: 16px;
        display: flex;
        gap: 20px;
        align-items: flex-start;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        transition: box-shadow .2s;
    }
    .listing-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }

    /* ── Thumbnail column ── */
    .listing-thumb-wrap { position: relative; flex-shrink: 0; }

    .badge-paid {
        position: absolute;
        top: 6px; left: 6px;
        background: var(--blue);
        color: white;
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 4px;
        z-index: 2;
        font-weight: 600;
    }

    .listing-thumb {
        width: 110px;
        height: 110px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: #f3f4f6;
        display: block;
    }

    .status-badge {
        display: block;
        text-align: center;
        margin-top: 8px;
        padding: 3px 0;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        border: 1.5px solid;
    }
    .status-badge.active   { color: var(--green); border-color: var(--green); }
    .status-badge.inactive { color: var(--primary); border-color: var(--primary); }

    /* ── Body column ── */
    .listing-body { flex: 1; min-width: 0; }

    .listing-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 14px;
        gap: 12px;
    }

    .listing-name {
        font-size: 17px;
        font-weight: 700;
        color: var(--text);
        margin: 0;
        line-height: 1.3;
    }

    .listing-sku {
        font-size: 11px;
        color: var(--muted);
        margin-top: 3px;
        font-family: monospace;
    }

    /* ── Action buttons ── */
    .listing-actions { display: flex; gap: 8px; flex-shrink: 0; align-items: center; flex-wrap: wrap; justify-content: flex-end; }

    .icon-btn {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border);
        background: #f9fafb;
        cursor: pointer;
        transition: background .15s;
        text-decoration: none;
    }
    .icon-btn:hover       { background: #f3f4f6; }
    .icon-btn.red:hover   { background: #fee2e2; border-color: #fca5a5; }

    .action-text-btn {
        min-height: 34px;
        padding: 0 13px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: 1px solid var(--border);
        background: #f9fafb;
        color: var(--text);
        cursor: pointer;
        transition: background .15s, border-color .15s, color .15s;
        text-decoration: none;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        white-space: nowrap;
    }
    .action-text-btn.payment {
        background: #EFF6FF;
        border-color: #BFDBFE;
        color: #1D4ED8;
    }
    .action-text-btn.payment:hover {
        background: #DBEAFE;
        border-color: #93C5FD;
    }
    .action-text-btn.verify {
        background: #F0FDF4;
        border-color: #BBF7D0;
        color: #15803D;
    }
    .action-text-btn.verify:hover {
        background: #DCFCE7;
        border-color: #86EFAC;
    }
    .action-text-btn.preview {
        background: #F8FAFC;
        border-color: #CBD5E1;
        color: #334155;
    }
    .action-text-btn.preview:hover {
        background: #F1F5F9;
        border-color: #94A3B8;
    }
    .verification-badge {
        min-height: 34px;
        padding: 0 13px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #D1FAE5;
        border: 1px solid #6EE7B7;
        color: #047857;
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
    }

    /* ── Meta grid ── */
    .listing-meta {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px 24px;
    }

    .meta-item label {
        color: var(--muted);
        font-size: 12px;
        display: block;
        margin-bottom: 2px;
    }
    .meta-item span {
        font-weight: 600;
        font-size: 13px;
        color: var(--text);
    }

    /* ── Flag + country ── */
    .country-cell {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .country-flag {
        width: 20px;
        height: 14px;
        object-fit: cover;
        border-radius: 2px;
        border: 1px solid var(--border);
    }

    /* ── Slots ── */
    .slots-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 16px;
        padding-top: 12px;
        border-top: 1px solid #F3F4F6;
        flex-wrap: wrap;
        gap: 8px;
    }

    .slots-hint { font-size: 12px; color: var(--muted); }

    .view-slots-btn {
        color: var(--primary);
        font-size: 13px;
        font-weight: 600;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        font-family: inherit;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .slots-detail {
        background: #f9fafb;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 12px 16px;
        margin-top: 10px;
        font-size: 13px;
    }

    .slot-row {
        display: grid;
        grid-template-columns: minmax(160px, 1fr) minmax(260px, auto);
        gap: 16px;
        align-items: center;
        padding: 6px 0;
        border-bottom: 1px solid #F3F4F6;
    }
    .slot-row:last-child { border-bottom: none; }
    .slot-pricing {
        display: grid;
        grid-template-columns: repeat(3, minmax(90px, 1fr));
        gap: 10px;
        text-align: right;
    }
    .slot-price-cell small {
        display: block;
        color: var(--muted);
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .3px;
        margin-bottom: 2px;
    }
    .slot-price-cell strong {
        color: var(--text);
        font-size: 12px;
        white-space: nowrap;
    }
    .slot-price-cell.total strong { color: var(--primary); font-weight: 800; }

    /* ── Tags row (popular/sold) ── */
    .listing-tags { display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap; }

    .tag {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 20px;
    }
    .tag-popular { background: #FEF3C7; color: #D97706; }
    .tag-soldoff { background: #FEE2E2; color: var(--red); }
    .tag-realtime { background: #EDE9FE; color: #7C3AED; }

    /* ── Empty state ── */
    .empty-card {
        background: white;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 70px 24px;
        text-align: center;
    }

    .empty-icon { font-size: 52px; margin-bottom: 12px; }
    .empty-text { font-size: 15px; color: #9CA3AF; font-weight: 500; margin-bottom: 20px; }

    /* ── Pagination ── */
    .pagination-wrap { margin-top: 16px; }

    @media (max-width: 700px) {
        .listing-card { flex-direction: column; }
        .listing-thumb { width: 100%; height: 180px; }
        .listing-meta { grid-template-columns: 1fr 1fr; }
        .slot-row { grid-template-columns: 1fr; }
        .slot-pricing { text-align: left; }
        .header-right { width: 100%; }
        .wh-combobox { width: 100%; min-width: unset; }
    }

    /* ── Search bar ── */
.search-wrap {
    position: relative;
    margin-bottom: 16px;
}
.search-wrap > svg {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--muted);
    pointer-events: none;
}
.search-input {
    width: 100%;
    padding: 11px 88px 11px 42px; /* ← increased right padding */
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-family: inherit;
    font-size: 14px;
    color: var(--text);
    background: white;
    outline: none;
    box-sizing: border-box;
    transition: border-color .2s;
}
.search-input:focus { border-color: var(--primary); }

.btn-icon-outline {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    border: 1.5px solid var(--border);
    border-radius: 9px;
    background: white;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
    cursor: pointer;
    text-decoration: none;
    transition: background .15s, border-color .15s;
    white-space: nowrap;
}
.btn-icon-outline:hover { background: #f3f4f6; }

.view-toggle {
    display: flex;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    background: white;
    flex-shrink: 0;
}
.view-toggle-btn {
    width: 36px;
    height: 36px;
    padding: 0;
    background: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--muted);
    transition: background .15s, color .15s;
    flex-shrink: 0;
}
.view-toggle-btn.active,
.view-toggle-btn:hover { background: var(--primary); color: white; }
.view-toggle-btn:first-child { border-right: 1.5px solid var(--border); }
    /* ── Multi-filter bar ── */
.filter-bar {
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.filter-group { display: flex; flex-direction: column; gap: 6px; }

.filter-group-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: .4px;
}

.filter-group-pills { display: flex; gap: 6px; flex-wrap: wrap; }

/* ── Grid view ── */
#listingsWrap.grid-view {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
}
#listingsWrap.grid-view .listing-card {
    flex-direction: column;
    margin-bottom: 0;
}
#listingsWrap.grid-view .listing-thumb {
    width: 100%;
    height: 160px;
}
#listingsWrap.grid-view .listing-meta {
    grid-template-columns: 1fr 1fr;
}
/* ── Grid view: lister info stacks vertically ── */
#listingsWrap.grid-view .lister-info-box {
    flex-direction: column;
    gap: 6px;
}
#listingsWrap.grid-view {
    align-items: stretch;  /* add this */
}
#listingsWrap.grid-view .listing-card {
    height: 100%;  /* add this */
}

.filter-pill {
    padding: 5px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    border: 1.5px solid var(--border);
    background: white;
    color: var(--text);
    cursor: pointer;
    text-decoration: none;
    transition: all .2s;
    font-family: inherit;
}
.filter-pill:hover,
.filter-pill.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}
.filter-pill.active-green  { background: #16a34a; color: white; border-color: #16a34a; }
.filter-pill.active-blue   { background: var(--blue); color: white; border-color: var(--blue); }
.filter-pill.active-purple { background: #8B5CF6; color: white; border-color: #8B5CF6; }
</style>
@endsection

@section('content')
<div class="page-wrap">

    {{-- Flash --}}
    @if(session('success'))
        <div class="alert-success">✓ {{ session('success') }}</div>
    @endif

    {{-- ── Header ── --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Manage Listings</h1>
        <div class="page-subtitle">
            <div>* Make one time payment to use lifetime.</div>
            <div>* Per Offer List charge: $5</div>
            <div>* Unpaid offers: <strong>{{ $unpaidCount }}</strong></div>
        </div>
    </div>

    <div class="header-right">
        {{-- Warehouse filter dropdown --}}
        <div class="wh-wrap">
            @php
                $selectedWh = $warehouseFilter
                    ? $warehouses->first(fn($wh) => (string) $wh->id === (string) $warehouseFilter || (string) $wh->_id === (string) $warehouseFilter)
                    : null;
            @endphp
            <span class="wh-label">
                Filter by Warehouse
                @if($warehouseFilter)
                    <button type="button" class="wh-clear-btn" id="whClearBtn"
                            style="display:inline-flex;" onclick="clearWarehouseFilter()">
                        &nbsp;✕ Clear
                    </button>
                @endif
            </span>
            <form method="GET" action="{{ route('product_listing.index') }}" id="warehouseForm">
                <input type="hidden" name="filter" value="{{ $filter }}" id="warehouseFormFilter">
                <input type="hidden" name="status_filter" value="{{ $statusFilter }}">
                <input type="hidden" name="payment_filter" value="{{ $paymentFilter }}">
                <input type="hidden" name="real_time_price" value="{{ $realTimePriceFilter }}">
                @if(request()->filled('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                <input type="hidden" name="warehouse_id" id="warehouseIdInput" value="{{ $warehouseFilter }}">
                <div class="wh-combobox" id="warehouseCombobox">
                    <input type="text"
                           class="warehouse-search"
                           id="warehouseSearch"
                           value="{{ $selectedWh?->name ?? '' }}"
                           placeholder="Search paid warehouses"
                           autocomplete="off"
                           role="combobox"
                           aria-expanded="false"
                           aria-controls="warehouseOptions">
                    <div class="wh-options" id="warehouseOptions" role="listbox">
                        <button type="button" class="wh-option" data-warehouse-id="" data-label="All Paid Warehouses">
                            All Paid Warehouses
                        </button>
                        @foreach($warehouses as $wh)
                            <button type="button"
                                    class="wh-option {{ (string) $warehouseFilter === (string) $wh->id ? 'active' : '' }}"
                                    data-warehouse-id="{{ $wh->id }}"
                                    data-label="{{ $wh->name }}">
                                {{ $wh->name }}
                            </button>
                        @endforeach
                        <div class="wh-empty" id="warehouseEmpty" style="display:none;">No paid warehouses found</div>
                    </div>
                </div>
            </form>
            @if($warehouseFilter)
                @if($selectedWh)
                    <span style="font-size:11px; color:var(--primary); font-weight:600;">
                        📦 Showing: {{ $selectedWh->name }}
                    </span>
                @endif
            @endif
        </div>

        {{-- Refresh --}}
        <a href="{{ route('product_listing.export', request()->only(['warehouse_id', 'filter', 'status_filter', 'payment_filter', 'real_time_price', 'search'])) }}" class="btn-icon-outline">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export CSV
        </a>

        <a href="{{ route('product_listing.index') }}" class="btn-icon-outline">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="23 4 23 10 17 10"/>
                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
            </svg>
            Refresh
        </a>

        

        {{-- Add button --}}
        <a href="{{ route('product_listing.create') }}" class="btn-add">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            New Listing
        </a>
    </div>
</div>

{{-- ── Search bar + view toggle ── --}}
<form method="GET" action="{{ route('product_listing.index') }}" id="searchForm">
    @foreach(request()->except('search') as $key => $val)
        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
    @endforeach
    <div class="search-wrap">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text"
               name="search"
               class="search-input"
               placeholder="Search by product name, brand, SKU..."
               value="{{ request('search') }}"
               oninput="debounceSearch(this.form)">

        {{-- View toggle inside search bar (right side) --}}
        <div class="view-toggle" style="position:absolute; right:6px; top:50%; transform:translateY(-50%); height:36px;">
            <button type="button" class="view-toggle-btn active" id="btnList" onclick="setView('list')" title="List view">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                    <line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/>
                    <line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
            </button>
            <button type="button" class="view-toggle-btn" id="btnGrid" onclick="setView('grid')" title="Grid view">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                </svg>
            </button>
        </div>
    </div>
</form>

{{-- ── Filter bar ── --}}
<div class="filter-bar">

    {{-- Verification Status --}}
    <div class="filter-group">
        <span class="filter-group-label">Verification</span>
        <div class="filter-group-pills">
            @foreach(['all' => 'All', 'pending' => 'Pending', 'verified' => 'Verified', 'rejected' => 'Rejected'] as $key => $label)
                <a href="{{ route('product_listing.index', array_merge(
                        request()->only(['warehouse_id', 'status_filter', 'payment_filter', 'real_time_price', 'search']),
                        ['filter' => $key]
                    )) }}"
                   class="filter-pill {{ $filter === $key ? 'active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Status --}}
    <div class="filter-group">
        <span class="filter-group-label">Status</span>
        <div class="filter-group-pills">
            @foreach(['all' => 'All', 'active' => 'Active', 'on_hold' => 'On Hold'] as $key => $label)
                <a href="{{ route('product_listing.index', array_merge(
                        request()->only(['warehouse_id', 'filter', 'payment_filter', 'real_time_price', 'search']),
                        ['status_filter' => $key]
                    )) }}"
                   class="filter-pill {{ ($statusFilter ?? 'all') === $key ? 'active-green' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Payment --}}
    <div class="filter-group">
        <span class="filter-group-label">Payment</span>
        <div class="filter-group-pills">
            @foreach(['all' => 'All', 'paid' => 'Paid', 'unpaid' => 'Unpaid'] as $key => $label)
                <a href="{{ route('product_listing.index', array_merge(
                        request()->only(['warehouse_id', 'filter', 'status_filter', 'real_time_price', 'search']),
                        ['payment_filter' => $key]
                    )) }}"
                   class="filter-pill {{ ($paymentFilter ?? 'all') === $key ? 'active-blue' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Real-Time Price --}}
    <div class="filter-group">
        <span class="filter-group-label">Real-Time Price</span>
        <div class="filter-group-pills">
            @foreach(['all' => 'All', 'yes' => 'Yes', 'no' => 'No'] as $key => $label)
                <a href="{{ route('product_listing.index', array_merge(
                        request()->only(['warehouse_id', 'filter', 'status_filter', 'payment_filter', 'search']),
                        ['real_time_price' => $key]
                    )) }}"
                   class="filter-pill {{ ($realTimePriceFilter ?? 'all') === $key ? 'active-purple' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

</div>

    {{-- ── Listings ── --}}
    <div id="listingsWrap">
    @forelse($listings as $listing)
        @php
    $productId     = is_object($listing->product_id)   ? $listing->product_id->__toString()   : (string)$listing->product_id;
    $warehouseId   = is_object($listing->warehouse_id) ? $listing->warehouse_id->__toString() : (string)$listing->warehouse_id;
    $listingUserId = is_object($listing->user_id)      ? $listing->user_id->__toString()      : (string)$listing->user_id;
    $product       = $productsMap[$productId] ?? null;
    $warehouse     = $warehousesMap[$warehouseId] ?? null;
    $lister        = $usersMap[$listingUserId] ?? null;
    $slots         = $listing->slots ?? [];
    $listingImgs   = $imagesMap[(string)$listing->_id] ?? collect();
    $firstImage    = $listingImgs->first();
    $slotValue = function ($slot, string $key, $default = null) {
        return is_array($slot) ? ($slot[$key] ?? $default) : ($slot->{$key} ?? $default);
    };
    $slotPricing = function ($slot) use ($slotValue) {
        $price = (float) ($slotValue($slot, 'price', 0) ?: 0);
        $commission = (float) ($slotValue($slot, 'commission_percentage', 0) ?: 0);
        $totalPrice = $slotValue($slot, 'total_price');

        if ($totalPrice === null || $totalPrice === '') {
            $totalPrice = $price + ($price * $commission / 100);
        }

        return [
            'price' => $price,
            'commission' => $commission,
            'total_price' => (float) $totalPrice,
        ];
    };
    $firstSlotPricing = count($slots) > 0 ? $slotPricing($slots[0]) : null;
    $verificationStatus = strtolower((string)($listing->verification_status ?? 'pending'));
    $isListingVerified = in_array($verificationStatus, ['verified', 'approved'], true);
    $previewUrl = rtrim(config('services.frontend.url', 'http://localhost:3000'), '/') . '/user/listings/' . $listing->id . '/preview';
@endphp

        <div class="listing-card" id="card-{{ $listing->id }}">

            {{-- Thumbnail --}}
            <div class="listing-thumb-wrap">
                @if($listing->is_paid)
                    <span class="badge-paid">Paid</span>
                @endif
                <img
                    src="{{ $firstImage && !empty($firstImage->image['url']) ? $firstImage->image['url'] : asset('images/placeholder.png') }}"
                    class="listing-thumb"
                    onerror="this.src='https://placehold.co/110x110/f3f4f6/9ca3af?text=No+Image'"
                >
                <span class="status-badge {{ $listing->is_active ? 'active' : 'inactive' }}">
                    {{ $listing->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            {{-- Body --}}
            <div class="listing-body">
                <div class="listing-top">
                    <div>
                        <h5 class="listing-name">{{ $product ? lang($product, 'product_name') : $listing->product_id }}</h5>
                        <div class="listing-sku">SKU: {{ $listing->sku_code }}</div>
                        @if($lister)
                        <div class="lister-info-box" style="display:flex; align-items:center; gap:16px; margin-top:8px; padding:8px 12px; background:#EFF6FF; border-radius:8px; border:1px solid #BFDBFE; transition: background .2s, border-color .2s, box-shadow .2s; flex-wrap:wrap;" 
     onmouseover="this.style.background='#DBEAFE'; this.style.borderColor='#93C5FD'; this.style.boxShadow='0 2px 8px rgba(59,130,246,0.12)'" 
     onmouseout="this.style.background='#EFF6FF'; this.style.borderColor='#BFDBFE'; this.style.boxShadow='none'">
                            <div style="display:flex; align-items:center; gap:6px; font-size:12px; color:#374151;">
                                <svg width="13" height="13" fill="none" stroke="#6B7280" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                <span style="font-weight:600; color:#111827;">{{ lang($lister, 'name') }}</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:6px; font-size:12px; color:#6B7280;">
                                <svg width="13" height="13" fill="none" stroke="#6B7280" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                                {{ $lister->email }}
                            </div>
                            @if($lister->mobile)
                            <div style="display:flex; align-items:center; gap:6px; font-size:12px; color:#6B7280;">
                                <svg width="13" height="13" fill="none" stroke="#6B7280" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                                    <line x1="12" y1="18" x2="12.01" y2="18"/>
                                </svg>
                                {{ $lister->mobile }}
                            </div>
                            @endif
                        </div>
                        @endif

                        {{-- Tags --}}
                        <div class="listing-tags">
                            @if(!empty($listing->real_time_price))
                                <span class="tag tag-realtime">📈 Real Time</span>
                            @endif
                            @if(!empty($listing->is_popular))
                                <span class="tag tag-popular">⭐ Popular</span>
                            @endif
                            @if(!empty($listing->is_sold_off))
                                <span class="tag tag-soldoff">🚫 Sold Off</span>
                            @endif
                        </div>
                    </div>

                    <div class="listing-actions">

    {{-- Approve Payment --}}
    @if(!$listing->is_paid)
    <form method="POST" action="{{ route('product_listing.approvePayment', $listing->id) }}" style="margin:0;">
        @csrf
        <button type="submit" class="action-text-btn payment" title="Approve Payment"
                onclick="return confirm('Approve payment for this listing?')">
            Approve Payment
        </button>
    </form>
    @endif

    {{-- Verify Listing --}}
    @if(!$isListingVerified)
    <form method="POST" action="{{ route('product_listing.approveListing', $listing->id) }}" style="margin:0;">
        @csrf
        <button type="submit" class="action-text-btn verify" title="Verify Listing"
                onclick="return confirm('Verify this listing?')">
            Verify Listing
        </button>
    </form>
    @else
    <span class="verification-badge">Verified</span>
    @endif

    <a href="{{ $previewUrl }}"
       class="action-text-btn preview"
       title="Preview Page"
       target="_blank"
       rel="noopener noreferrer">
        Preview Page
    </a>

    <a href="{{ route('product_listing.edit', $listing->id) }}"
       class="icon-btn" title="Edit">
                            <svg width="14" height="14" fill="none" stroke="#888" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </a>
                        <form method="POST"
                              action="{{ route('product_listing.destroy', $listing->id) }}"
                              onsubmit="return confirm('Delete this listing?')"
                              style="margin:0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icon-btn red" title="Delete">
                                <svg width="14" height="14" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                    <path d="M10 11v6M14 11v6M9 6V4h6v2"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Meta grid --}}
                <div class="listing-meta">
                    <div class="meta-item">
                        <label>Warehouse</label>
                        <span>{{ lang($warehouse, 'warehouse_name') ?? $listing->warehouse_id }}</span>
                    </div>
                    <div class="meta-item">
                        <label>Lead Time</label>
                        <span>{{ $listing->lead_time }} Weeks</span>
                    </div>
                    <div class="meta-item">
                        <label>Sell Type</label>
                        <span>{{ ucwords($listing->sell_type) }}</span>
                    </div>
                    <div class="meta-item">
                        <label>Location</label>
                        <span>
                            @if($warehouse?->country)
                                <span class="country-cell">
                                    {{ lang($warehouse, 'city') }}, {{ lang($warehouse, 'country') }}
                                </span>
                            @else
                                {{ $listing->warehouse_id }}
                            @endif
                        </span>
                    </div>
                    <div class="meta-item">
                        <label>Total Available</label>
                        <span>{{ number_format($listing->total_quantity) }} pcs</span>
                    </div>
                    <div class="meta-item">
                        <label>Currency</label>
                        <span>{{ $listing->currency_id }}</span>
                    </div>
                    @if($firstSlotPricing)
                        <div class="meta-item">
                            <label>Price</label>
                            <span>{{ $listing->currency_id }} {{ number_format($firstSlotPricing['price'], 2) }}</span>
                        </div>
                        <div class="meta-item">
                            <label>Commission</label>
                            <span>{{ rtrim(rtrim(number_format($firstSlotPricing['commission'], 2), '0'), '.') }}%</span>
                        </div>
                        <div class="meta-item">
                            <label>Total Price</label>
                            <span>{{ $listing->currency_id }} {{ number_format($firstSlotPricing['total_price'], 2) }}</span>
                        </div>
                    @endif
                </div>

                {{-- Slots footer --}}
                @if(count($slots) > 0)
                    <div class="slots-footer">
                        <span class="slots-hint">
                            📦 {{ count($slots) }} price {{ count($slots) === 1 ? 'tier' : 'tiers' }}
                            &nbsp;·&nbsp; Incoterm: {{ $listing->incoterms_id ?? '—' }}
                        </span>
                        <button class="view-slots-btn" onclick="toggleSlots('{{ $listing->id }}')">
                            View Slots ({{ count($slots) }})
                            <span id="arrow-{{ $listing->id }}">▼</span>
                        </button>
                    </div>

                    <div id="slots-{{ $listing->id }}" class="slots-detail" style="display:none;">
                        @foreach($slots as $i => $slot)
                            @php
                                $minQty = $slotValue($slot, 'min_quantity', 0);
                                $maxQty = $slotValue($slot, 'max_quantity');
                                $pricing = $slotPricing($slot);
                            @endphp
                            <div class="slot-row">
                                <span>
                                    <strong>Tier {{ $i + 1 }}:</strong>
Min {{ number_format($minQty) }}
@if(!empty($maxQty))
    – Max {{ number_format($maxQty) }} pcs
@else
    pcs &amp; above
@endif
                                </span>
                                <div class="slot-pricing">
                                    <span class="slot-price-cell">
                                        <small>Price</small>
                                        <strong>{{ $listing->currency_id }} {{ number_format($pricing['price'], 2) }}</strong>
                                    </span>
                                    <span class="slot-price-cell">
                                        <small>Commission</small>
                                        <strong>{{ rtrim(rtrim(number_format($pricing['commission'], 2), '0'), '.') }}%</strong>
                                    </span>
                                    <span class="slot-price-cell total">
                                        <small>Total Price</small>
                                        <strong>{{ $listing->currency_id }} {{ number_format($pricing['total_price'], 2) }}</strong>
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>{{-- end .listing-body --}}
        </div>

    @empty
        <div class="empty-card">
            <div class="empty-icon">📦</div>
            <div class="empty-text">
                @if($warehouseFilter)
                    No listings found for this warehouse.
                @else
                    No listings found.
                @endif
            </div>
            <a href="{{ route('product_listing.create') }}" class="btn-add"
               style="display:inline-flex; margin:0 auto;">
                + Add Your First Offer
            </a>
        </div>
    @endforelse
    </div>

    
    {{-- Pagination --}}
    <div class="pagination-wrap" style="display:flex; justify-content:flex-end;">
        <x-admin.pagination :paginator="$listings" />
    </div>

</div>
@endsection

@section('scripts')
<script>

    // ── Search debounce ───────────────────────────────────────────
let searchTimer;
function debounceSearch(form) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => form.submit(), 500);
}

// ── Grid / List view toggle ───────────────────────────────────
function setView(type) {
    const listingsWrap = document.getElementById('listingsWrap');
    const btnList = document.getElementById('btnList');
    const btnGrid = document.getElementById('btnGrid');

    if (type === 'grid') {
        listingsWrap.classList.add('grid-view');
        btnGrid.classList.add('active');
        btnList.classList.remove('active');
        localStorage.setItem('listingView', 'grid');
    } else {
        listingsWrap.classList.remove('grid-view');
        btnList.classList.add('active');
        btnGrid.classList.remove('active');
        localStorage.setItem('listingView', 'list');
    }
}

// Restore saved view on load
document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem('listingView');
    if (saved === 'grid') setView('grid');
});
// ── Slot toggle ───────────────────────────────────────────────
function toggleSlots(id) {
    const el    = document.getElementById('slots-' + id);
    const arrow = document.getElementById('arrow-' + id);
    const open  = el.style.display !== 'none' && el.style.display !== '';
    el.style.display   = open ? 'none' : 'block';
    arrow.textContent  = open ? '▼' : '▲';
}

// ── Warehouse filter ──────────────────────────────────────────
function applyWarehouseFilter(warehouseId = '') {
    const warehouseInput = document.getElementById('warehouseIdInput');
    if (warehouseInput) {
        warehouseInput.value = warehouseId;
    }

    const filterInput = document.getElementById('warehouseFormFilter');
    if (filterInput) {
        filterInput.value = '{{ $filter }}';
    }
    document.getElementById('warehouseForm').submit();
}

function clearWarehouseFilter() {
    const url = new URL(window.location.href);
    url.searchParams.delete('warehouse_id');
    window.location.href = url.toString();
}

function initWarehouseSearch() {
    const searchInput = document.getElementById('warehouseSearch');
    const optionsBox = document.getElementById('warehouseOptions');
    const emptyState = document.getElementById('warehouseEmpty');
    const options = Array.from(document.querySelectorAll('#warehouseOptions .wh-option'));

    if (!searchInput || !optionsBox || !options.length) {
        return;
    }

    const openOptions = () => {
        optionsBox.classList.add('open');
        searchInput.setAttribute('aria-expanded', 'true');
    };

    const closeOptions = () => {
        optionsBox.classList.remove('open');
        searchInput.setAttribute('aria-expanded', 'false');
    };

    const filterOptions = () => {
        const term = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        options.forEach((option) => {
            const label = (option.dataset.label || option.textContent || '').toLowerCase();
            const visible = label.includes(term);
            option.style.display = visible ? 'block' : 'none';
            if (visible) visibleCount += 1;
        });

        if (emptyState) {
            emptyState.style.display = visibleCount ? 'none' : 'block';
        }
    };

    options.forEach((option) => {
        option.addEventListener('click', () => {
            searchInput.value = option.dataset.warehouseId ? (option.dataset.label || '') : '';
            applyWarehouseFilter(option.dataset.warehouseId || '');
        });
    });

    searchInput.addEventListener('focus', () => {
        filterOptions();
        openOptions();
    });

    searchInput.addEventListener('input', () => {
        filterOptions();
        openOptions();
    });

    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeOptions();
            return;
        }

        if (event.key === 'Enter') {
            event.preventDefault();
            const firstVisible = options.find((option) => option.style.display !== 'none');
            if (firstVisible) {
                searchInput.value = firstVisible.dataset.warehouseId ? (firstVisible.dataset.label || '') : '';
                applyWarehouseFilter(firstVisible.dataset.warehouseId || '');
            }
        }
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('#warehouseCombobox')) {
            closeOptions();
        }
    });
}

document.addEventListener('DOMContentLoaded', initWarehouseSearch);
</script>
@endsection
