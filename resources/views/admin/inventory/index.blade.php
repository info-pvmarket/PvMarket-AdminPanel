@extends('layouts.admin')

@section('title', 'Inventory')

@section('styles')
<style>
    :root {
    --green: #10B981;
    --red:   #EF4444;
}
    

    /* ── Page shell ─────────────────────────────────────────────── */
    .inv-page { padding: 28px 28px 48px; background: var(--bg); min-height: 100vh; }

    /* ── Header ─────────────────────────────────────────────────── */
    .inv-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 24px;
        gap: 16px;
        flex-wrap: wrap;
    }
    .inv-header h1 { font-size: 22px; font-weight: 800; color: var(--text); margin: 0 0 4px; }
    .inv-header p  { font-size: 13px; color: var(--muted); margin: 0; }

    .btn-outline {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 16px;
        border: 1.5px solid var(--border);
        border-radius: 9px;
        background: #fff;
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        cursor: pointer;
        text-decoration: none;
        transition: background .15s;
        font-family: inherit;
    }
    .btn-outline:hover { background: #f9fafb; color: var(--text); }

    /* ── Stat cards ─────────────────────────────────────────────── */
    .inv-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    @media (max-width: 900px) { .inv-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 500px) { .inv-stats { grid-template-columns: 1fr; } }

    .stat-card {
    background: #fff;
    border-radius: 14px;
    padding: 20px 22px;
    border: 1px solid var(--border);
    display: flex;
    align-items: flex-start;
    gap: 16px;
    position: relative;
    overflow: hidden;
    transition: transform .2s, box-shadow .2s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.08); }
.stat-card {
    color: inherit;
    text-decoration: none;
    cursor: pointer;
}
.stat-card.active-filter {
    box-shadow: 0 0 0 2px var(--primary), 0 8px 24px rgba(0,0,0,.08);
}

.stat-card::after {
    content: '';
    position: absolute;
    top: 12px; right: 12px;
    width: 8px; height: 8px;
    border-radius: 50%;
}

.stat-card.card-blue   { background: linear-gradient(135deg, #eff6ff 0%, #fff 70%); border: 1px solid #bfdbfe; }
.stat-card.card-yellow { background: linear-gradient(135deg, #fffbeb 0%, #fff 70%); border: 1px solid #fde68a; }
.stat-card.card-red    { background: linear-gradient(135deg, #fef2f2 0%, #fff 70%); border: 1px solid #fecaca; }
.stat-card.card-green  { background: linear-gradient(135deg, #f0fdf4 0%, #fff 70%); border: 1px solid #bbf7d0; }

.stat-card.card-blue::after   { background: #3b82f6; }
.stat-card.card-yellow::after { background: #d97706; }
.stat-card.card-red::after    { background: #dc2626; }
.stat-card.card-green::after  { background: #16a34a; }

.stat-label { font-size: 12px; color: var(--muted); font-weight: 500; margin-bottom: 4px; }
.stat-value { font-size: 28px; font-weight: 800; line-height: 1; }
.stat-sub   { font-size: 11px; color: var(--muted); margin-top: 3px; }

.stat-card.card-blue   .stat-value { color: #3b82f6; }
.stat-card.card-yellow .stat-value { color: #d97706; }
.stat-card.card-red    .stat-value { color: #dc2626; }
.stat-card.card-green  .stat-value { color: #16a34a; }
    .stat-icon {
        width: 42px; height: 42px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .stat-icon.blue   { background: #eff6ff; color: #3b82f6; }
    .stat-icon.yellow { background: #fffbeb; color: var(--yellow); }
    .stat-icon.red    { background: #fef2f2; color: var(--red); }
    .stat-icon.green  { background: #f0fdf4; color: var(--green); }

    .stat-label { font-size: 12px; color: var(--muted); font-weight: 500; margin-bottom: 4px; }
    .stat-value { font-size: 28px; font-weight: 800; color: var(--text); line-height: 1; }
    .stat-sub   { font-size: 11px; color: var(--muted); margin-top: 3px; }

    /* ── Toolbar ─────────────────────────────────────────────────── */
    .inv-toolbar {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .inv-search-wrap {
        position: relative;
        flex: 1;
        min-width: 200px;
    }
    .inv-search-wrap svg {
        position: absolute; left: 12px; top: 50%;
        transform: translateY(-50%); color: #9ca3af; pointer-events: none;
    }
    .inv-search {
        width: 100%;
        padding: 9px 12px 9px 36px;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        font-size: 13px; color: var(--text);
        background: #f9fafb;
        outline: none;
        transition: border-color .15s;
        box-sizing: border-box;
        font-family: inherit;
    }
    .inv-search:focus { border-color: var(--primary); background: #fff; }

    .sort-form { margin-left: auto; }
    .sort-select {
        min-width: 150px;
        padding: 8px 34px 8px 12px;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        background: #fff;
        color: var(--text);
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    /* ── Table card ─────────────────────────────────────────────── */
    .inv-table-wrap {
        background: #fff;
        border-radius: 14px;
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }
    .inv-table thead { background: #F0F9FF; }
    .inv-table thead th {
        padding: 13px 18px;
        text-align: left;
        font-size: 12px; font-weight: 700;
        color: var(--primary-d);
        text-transform: uppercase; letter-spacing: .5px;
        border-bottom: 2px solid #BAE6FD;
        white-space: nowrap;
    }
    .inv-table tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: background .1s;
    }
    .inv-table tbody tr:last-child { border-bottom: none; }
    .inv-table tbody tr:hover { background: #fafafa; }
    .inv-table tbody td {
        padding: 15px 18px;
        font-size: 13px; color: #374151;
        vertical-align: middle;
    }
    .inv-table { width: 100%; border-collapse: collapse; }
    .inv-table tbody tr:nth-child(odd) td  { background: white; }
    .inv-table tbody tr:nth-child(even) td { background: #FAFBFD; }
    .inv-table tbody tr:hover td { background: #E0F2FE !important; }
    .inv-table th:last-child,
    .inv-table td:last-child { width: 130px; }

    /* SKU cell */
    .sku-wrap { display: flex; align-items: center; gap: 10px; }
    .sku-icon {
        width: 34px; height: 34px;
        background: #eef2ff; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        color: var(--blue); flex-shrink: 0;
    }
    .sku-code   { font-weight: 700; color: var(--text); font-size: 13px; }
    .sku-code:hover { color: var(--primary); text-decoration: underline; }
    .product-name { color: var(--text); font-weight: 600; font-size: 13px; }
    .sku-status { font-size: 11px; color: var(--muted); margin-top: 1px; }

    /* Warehouse */
    .wh-wrap { display: flex; align-items: center; gap: 7px; color: var(--muted); font-size: 13px; }

    /* Stock */
    .stock-strong { font-weight: 700; color: var(--text); }
    .stock-unit   { color: var(--muted); margin-left: 3px; }

    /* Alert column */
    .alert-text   { font-size: 12px; color: var(--muted); }
    .alert-set    { color: var(--primary); font-weight: 600; }

    /* Action icons */
    .action-btn {
        width: 34px; height: 34px;
        border-radius: 8px;
        border: 1.5px solid var(--border);
        background: #fff;
        display: flex; align-items: center; justify-content: center;
        color: var(--muted); cursor: pointer;
        transition: all .18s;
    }
    /* first btn = adjust stock → blue */
    .action-btn:nth-child(1):hover {
        background: #EFF6FF; border-color: #BFDBFE;
        color: #3B82F6; transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(59,130,246,.2);
    }
    /* second btn = history → amber */
    .action-btn:nth-child(2):hover {
        background: #FFFBEB; border-color: #FDE68A;
        color: #D97706; transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(217,119,6,.2);
    }
    /* third btn = alert bell → orange */
    .action-btn:nth-child(3):hover {
        background: #FFF7ED; border-color: #FDBA74;
        color: #EA580C; transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(234,88,12,.2);
    }
    .action-btn.bell-active         { border-color: #fed7aa; background: #fff7ed; color: var(--primary); }
    .action-btn.bell-active:hover   { background: #ffedd5; }
    .action-btns { display: flex; align-items: center; gap: 6px; }
    

    /* Empty state */
    .inv-empty {
        text-align: center;
        padding: 60px 20px;
        color: var(--muted);
        font-size: 14px;
    }
    .inv-empty svg { opacity: .3; margin-bottom: 12px; }

    /* ── Modal Base ─────────────────────────────────────────────── */
    .modal-backdrop {
        display: none;
        position: fixed; inset: 0;
        background: rgba(0,0,0,.5);
        z-index: 1050;
        align-items: center; justify-content: center;
    }
    .modal-backdrop.open { display: flex; }
    .modal-box {
        background: #fff;
        border-radius: 18px;
        padding: 26px;
        width: 100%; max-width: 430px;
        position: relative;
        box-shadow: 0 24px 64px rgba(0,0,0,.2);
        animation: modalIn .18s ease;
        margin: 16px;
    }
    @keyframes modalIn {
        from { opacity: 0; transform: translateY(12px) scale(.97); }
        to   { opacity: 1; transform: translateY(0)   scale(1); }
    }
    .modal-close {
        position: absolute; top: 16px; right: 16px;
        width: 30px; height: 30px;
        border-radius: 50%; border: none; background: #f3f4f6;
        cursor: pointer; color: var(--muted); font-size: 18px; line-height: 1;
        display: flex; align-items: center; justify-content: center;
        transition: background .12s;
    }
    .modal-close:hover { background: #e5e7eb; color: var(--text); }
    .modal-title {
        display: flex; align-items: center; gap: 9px;
        font-size: 16px; font-weight: 800; color: var(--text);
        margin-bottom: 2px;
    }
    .modal-subtitle { font-size: 12px; color: #9ca3af; margin-bottom: 20px; }

    /* ── Alert Modal ────────────────────────────────────────────── */
    .stock-info-card {
        background: #f0fdf4;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 20px;
    }
    .stock-info-card .sic-label { font-size: 12px; color: var(--muted); margin-bottom: 4px; }
    .stock-info-card .sic-value { font-size: 22px; font-weight: 800; color: var(--text); }

    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 7px; }
    .form-hint  { font-size: 11px; color: #9ca3af; margin-top: 5px; }

    .input-suffix-wrap { display: flex; align-items: stretch; }
    .input-suffix-wrap input {
    flex: 1;
    padding: 10px 13px;
    border: 1.5px solid var(--border); border-right: none;
    border-radius: 9px 0 0 9px;
    font-size: 14px; color: var(--text);
    outline: none; transition: border-color .15s;
    font-family: inherit;
    box-sizing: border-box;
}
    .input-suffix-wrap input:focus { border-color: var(--primary); }
    .input-suffix {
        padding: 10px 13px;
        border: 1.5px solid var(--border); border-left: none;
        border-radius: 0 9px 9px 0;
        background: #f9fafb; color: var(--muted); font-size: 13px;
        display: flex; align-items: center;
    }

    .toggle-row {
        display: flex; align-items: center; justify-content: space-between;
        background: #f9fafb; border-radius: 11px; padding: 14px 16px;
    }
    .toggle-row .tr-info .tr-title { font-size: 13px; font-weight: 700; color: var(--text); }
    .toggle-row .tr-info .tr-sub   { font-size: 12px; color: #9ca3af; margin-top: 2px; }

    .toggle { position: relative; width: 42px; height: 24px; display: inline-block; cursor: pointer; flex-shrink: 0; }
    .toggle input { display: none; }
    .toggle-track {
        position: absolute; inset: 0;
        background: #d1d5db; border-radius: 12px;
        transition: background .2s;
    }
    .toggle input:checked ~ .toggle-track { background: var(--primary); }
    .toggle-thumb {
        position: absolute; top: 3px; left: 3px;
        width: 18px; height: 18px;
        background: #fff; border-radius: 50%;
        transition: transform .2s;
        box-shadow: 0 1px 4px rgba(0,0,0,.2);
    }
    .toggle input:checked ~ .toggle-thumb { transform: translateX(18px); }

    .modal-footer { display: flex; align-items: center; gap: 10px; margin-top: 22px; flex-wrap: wrap; }

    .btn-danger-outline {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 9px 15px; border-radius: 9px;
        border: 1.5px solid #fecaca; background: #fff;
        color: var(--red); font-size: 13px; font-weight: 600; cursor: pointer;
        transition: background .12s; font-family: inherit;
    }
    .btn-danger-outline:hover { background: #fef2f2; }

    .btn-cancel {
    padding: 9px 18px; border-radius: 9px;
    border: 1.5px solid var(--border); background: #fff;
    color: #374151; font-size: 13px; font-weight: 600; cursor: pointer;
    font-family: inherit; transition: background .12s;
}
    .btn-cancel:hover { background: #f9fafb; }

    .btn-primary {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 9px 20px; border-radius: 9px;
        border: none; background: var(--primary);
        color: #fff; font-size: 13px; font-weight: 700; cursor: pointer;
        transition: background .12s; font-family: inherit;
    }
    .btn-primary:hover { background: var(--primary-d); }

    /* ── History Modal ──────────────────────────────────────────── */
    .history-meta {
        display: flex; align-items: center; justify-content: space-between;
        font-size: 12px; color: var(--muted); margin-bottom: 16px;
    }
    .history-list { max-height: 360px; overflow-y: auto; }
.history-list::-webkit-scrollbar { width: 4px; }
.history-list::-webkit-scrollbar-track { background: #f9fafb; border-radius: 4px; }
.history-list::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
    .history-item {
        display: flex; align-items: flex-start; gap: 13px;
        padding: 14px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .history-item:last-child { border-bottom: none; }
    .history-icon {
        width: 36px; height: 36px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .history-icon.add    { background: #fffbeb; color: var(--yellow); }
    .history-icon.remove { background: #fef2f2; color: var(--red); }
    .history-icon.init   { background: #f0fdf4; color: var(--green); }
    .history-body { flex: 1; }
    .history-type   { font-size: 13px; font-weight: 700; color: var(--text); }
    .history-detail { font-size: 12px; color: var(--muted); margin-top: 2px; }
    .history-meta-row { font-size: 11px; color: #9ca3af; margin-top: 3px; }
    .history-qty    { font-size: 14px; font-weight: 800; white-space: nowrap; }
    .history-qty.pos { color: var(--green); }
    .history-qty.neg { color: var(--red); }

    /* ── Adjust Stock Modal ─────────────────────────────────────── */
    .adjust-tabs { display: flex; margin-bottom: 22px; border-radius: 10px; overflow: hidden; border: 1.5px solid var(--border); }
    .adjust-tab {
        flex: 1; padding: 11px 10px;
        border: none; background: #fff;
        font-size: 13px; font-weight: 600; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 6px;
        transition: background .15s, color .15s;
        font-family: inherit; color: var(--muted);
    }
    .adjust-tab:first-child { border-right: 1.5px solid var(--border); }
    .adjust-tab.active.add    { background: var(--green); color: #fff; }
    .adjust-tab.active.reduce { background: var(--red);   color: #fff; }

    .qty-input {
        width: 100%;
        padding: 11px 13px;
        border: 1.5px solid var(--border); border-radius: 9px;
        font-size: 14px; color: var(--text); outline: none;
        transition: border-color .15s; box-sizing: border-box;
        font-family: inherit;
    }
    .qty-input:focus { border-color: var(--primary); }

    .reason-input {
        width: 100%; min-height: 85px;
        padding: 11px 13px;
        border: 1.5px solid var(--border); border-radius: 9px;
        font-size: 13px; color: var(--text); outline: none; resize: vertical;
        transition: border-color .15s; box-sizing: border-box;
        font-family: inherit;
    }
    .reason-input:focus { border-color: var(--primary); }

    .btn-add-stock    { background: var(--green)  !important; }
    .btn-add-stock:hover { background: #15803d !important; }
    .btn-remove-stock { background: var(--red)    !important; }
    .btn-remove-stock:hover { background: #b91c1c !important; }

    /* ── Toast ──────────────────────────────────────────────────── */
    .inv-toast {
        position: fixed; bottom: 28px; right: 28px;
        background: var(--text); color: #fff;
        padding: 12px 20px; border-radius: 11px;
        font-size: 13px; font-weight: 600;
        box-shadow: 0 8px 28px rgba(0,0,0,.2);
        z-index: 9999;
        transform: translateY(20px); opacity: 0;
        transition: transform .25s, opacity .25s;
        pointer-events: none;
    }
    .inv-toast.show    { transform: translateY(0); opacity: 1; }
    .inv-toast.success { background: var(--green); }
    .inv-toast.error   { background: var(--red); }
</style>
@endsection

@section('content')
<div class="inv-page">

    {{-- ── Header ── --}}
    <div class="inv-header">
        <div>
            <h1>Inventory</h1>
            <p>Manage stock levels and alerts for your listings</p>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
    <button class="btn-outline" onclick="openGlobalAlert()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        Set Alert
    </button>
    <button class="btn-outline" onclick="refreshListings()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <polyline points="23 4 23 10 17 10"/>
            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
        </svg>
        Refresh
    </button>
</div>
    </div>

    {{-- ── Stat Cards ── --}}
    <div class="inv-stats" id="statCards">
        <a href="{{ route('admin.inventory.index', array_filter(['filter' => 'all', 'sort' => $sort, 'search' => $search])) }}"
           class="stat-card card-blue {{ $filter === 'all' ? 'active-filter' : '' }}">
            <div class="stat-icon blue">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73L13 2.27a2 2 0 0 0-2 0L4 6.27A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                </svg>
            </div>
            <div>
                <div class="stat-label">Total Listings</div>
                <div class="stat-value">{{ $totalListings }}</div>
            </div>
        </a>
        <a href="{{ route('admin.inventory.index', array_filter(['filter' => 'low_stock', 'sort' => $sort, 'search' => $search])) }}"
           class="stat-card card-yellow {{ $filter === 'low_stock' ? 'active-filter' : '' }}">
            <div class="stat-icon yellow">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div>
                <div class="stat-label">Low Stock</div>
                <div class="stat-value">{{ $lowStockCount }}</div>
            </div>
        </a>
        <a href="{{ route('admin.inventory.index', array_filter(['filter' => 'out_of_stock', 'sort' => $sort, 'search' => $search])) }}"
           class="stat-card card-red {{ $filter === 'out_of_stock' ? 'active-filter' : '' }}">
            <div class="stat-icon red">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
            <div>
                <div class="stat-label">Out of Stock</div>
                <div class="stat-value">{{ $outOfStockCount }}</div>
            </div>
        </a>
        <a href="{{ route('admin.inventory.index', array_filter(['filter' => 'recent_movements', 'sort' => $sort, 'search' => $search])) }}"
           class="stat-card card-green {{ $filter === 'recent_movements' ? 'active-filter' : '' }}">
            <div class="stat-icon green">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="17 1 21 5 17 9"/>
                    <path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                    <polyline points="7 23 3 19 7 15"/>
                    <path d="M21 13v2a4 4 0 0 1-4 4H3"/>
                </svg>
            </div>
            <div>
                <div class="stat-label">Recent Movements</div>
                <div class="stat-value">{{ $recentMovements }}</div>
                <div class="stat-sub">(24h)</div>
            </div>
        </a>
    </div>

    {{-- ── Toolbar ── --}}
    <div class="inv-toolbar">
        <div class="inv-search-wrap">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" class="inv-search" id="invSearch"
                   placeholder="Search by SKU..."
                   value="{{ $search }}"
                   oninput="debounceSearch()">
        </div>
        <form method="GET" action="{{ route('admin.inventory.index') }}" class="sort-form">
            <input type="hidden" name="filter" value="{{ $filter }}">
            @if($search !== '')
                <input type="hidden" name="search" value="{{ $search }}">
            @endif
            <select name="sort" class="sort-select" aria-label="Sort inventory" onchange="this.form.submit()">
                <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>Latest first</option>
                <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest first</option>
            </select>
        </form>
    </div>

    {{-- ── Table ── --}}
    <div class="inv-table-wrap">
        <table class="inv-table">
            <thead>
                <tr>
                    <th>SKU Code</th>
                    <th>Product Name</th>
                    <th>Stock</th>
                    <th>Warehouse</th>
                    <th>Alert</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="invTableBody">
                @forelse($listings as $listing)
                    @php
                        $stock     = $listing->current_stock ?? 0;
                        $unit      = $listing->stock_unit ?? 'pieces';
                        $wh        = $listing->warehouse_display_name ?? 'N/A';
                        $threshold = $listing->alert_threshold;
                        $alertSet  = $threshold !== null;
                    @endphp
                    <tr>
                        {{-- SKU --}}
                        <td>
                            <div class="sku-wrap">
                                <div class="sku-icon">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M21 16V8a2 2 0 0 0-1-1.73L13 2.27a2 2 0 0 0-2 0L4 6.27A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                                    </svg>
                                </div>
                                <div>
                                    <a class="sku-code"
                                       href="{{ route('product_listing.index', [
                                           'listing_id' => (string) $listing->_id,
                                           'search' => $listing->sku_code,
                                       ]) }}">
                                        {{ $listing->sku_code }}
                                    </a>
                                    <div class="sku-status">{{ $listing->is_active ? 'Active' : 'Inactive' }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Product --}}
                        <td>
                            <span class="product-name">{{ $listing->product_name }}</span>
                        </td>

                        {{-- Stock --}}
                        <td>
                            <span class="stock-strong">{{ number_format($stock) }}</span>
                            <span class="stock-unit">{{ $unit }}</span>
                        </td>

                        {{-- Warehouse --}}
                        <td>
                            <div class="wh-wrap">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                    <polyline points="9 22 9 12 15 12 15 22"/>
                                </svg>
                                {{ $wh }}
                            </div>
                        </td>

                        {{-- Alert --}}
                        <td>
                            @if($alertSet)
                                <span class="alert-text alert-set">Alert at {{ $threshold }}</span>
                            @else
                                <span class="alert-text">Not set</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        {{-- Actions --}}
<td>
    <div class="action-btns">
        <button class="action-btn" title="Adjust Stock"
                data-id="{{ (string) $listing->_id }}"
                data-sku="{{ $listing->sku_code }}"
                data-stock="{{ $stock }}"
                data-unit="{{ $unit }}"
                onclick="openAdjust(this)">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 16V8a2 2 0 0 0-1-1.73L13 2.27a2 2 0 0 0-2 0L4 6.27A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                <line x1="12" y1="22.08" x2="12" y2="12"/>
            </svg>
        </button>

        <button class="action-btn" title="Inventory History"
                data-id="{{ (string) $listing->_id }}"
                data-sku="{{ $listing->sku_code }}"
                onclick="openHistory(this)">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="12 8 12 12 14 14"/>
                <path d="M3.05 11a9 9 0 1 0 .5-3.5"/>
                <polyline points="3 4 3 11 10 11"/>
            </svg>
        </button>

        <button class="action-btn {{ $alertSet ? 'bell-active' : '' }}" title="Stock Alert"
                data-id="{{ (string) $listing->_id }}"
                data-sku="{{ $listing->sku_code }}"
                data-stock="{{ $stock }}"
                data-unit="{{ $unit }}"
                data-threshold="{{ $alertSet ? $threshold : '' }}"
                data-email="{{ $listing->alert_email_enabled ? '1' : '0' }}"
                onclick="openAlert(this)">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
        </button>
    </div>
</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="inv-empty">
                                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="display:block;margin:0 auto 12px;">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73L13 2.27a2 2 0 0 0-2 0L4 6.27A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                </svg>
                                No inventory records found.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>{{-- end .inv-page --}}

<div class="pagination-wrap" style="display:flex; justify-content:flex-end;">
        <x-admin.pagination :paginator="$listings" />
    </div>


{{-- ══════════════════════════════════════════════
     MODAL: Stock Alert Settings
══════════════════════════════════════════════════ --}}
{{-- ══════════════════════════════════════════════
     MODAL: Stock Alert Settings (per-row)
══════════════════════════════════════════════════ --}}
<div class="modal-backdrop" id="alertModal" onclick="backdropClose('alertModal',event)">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('alertModal')">×</button>
        <div class="modal-title">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            Stock Alert Settings
        </div>
        <div class="modal-subtitle" id="alertSku"></div>

        <div class="stock-info-card">
            <div class="sic-label">Current Stock</div>
            <div class="sic-value" id="alertCurrentStock"></div>
        </div>

        <div class="form-group">
            <label class="form-label">Alert Threshold</label>
            <div class="input-suffix-wrap">
                <input type="number" id="alertThreshold" min="0" placeholder="0">
                <span class="input-suffix" id="alertUnit"></span>
            </div>
            <div class="form-hint">You'll be alerted when stock falls to or below this level</div>
        </div>

        <div class="toggle-row">
            <div class="tr-info">
                <div class="tr-title">Email Notifications</div>
                <div class="tr-sub">Receive email alerts for low stock</div>
            </div>
            <label class="toggle">
                <input type="checkbox" id="alertEmailToggle">
                <span class="toggle-track"></span>
                <span class="toggle-thumb"></span>
            </label>
        </div>

        <div class="modal-footer">
            <button class="btn-danger-outline" id="alertRemoveBtn" onclick="removeAlert()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6M14 11v6M9 6V4h6v2"/>
                </svg>
                Remove Alert
            </button>
            <button class="btn-cancel" style="margin-left:auto;" onclick="closeModal('alertModal')">Cancel</button>
            <button class="btn-primary" onclick="saveAlert()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                Save Alert
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     MODAL: Global Stock Alert Settings
══════════════════════════════════════════════════ --}}
<div class="modal-backdrop" id="globalAlertModal" onclick="backdropClose('globalAlertModal',event)">
    <div class="modal-box" style="max-width:460px;">
        <button class="modal-close" onclick="closeModal('globalAlertModal')">×</button>
        <div class="modal-title">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            Global Stock Alert
        </div>
        <div class="modal-subtitle">Set default thresholds across all inventory units</div>

        <div class="form-group">
            <label class="form-label">Alert Threshold — Pieces</label>
            <div class="input-suffix-wrap">
                <input type="number" id="globalThresholdPieces" min="0" placeholder="0">
                <span class="input-suffix">pieces</span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Alert Threshold — Pallets</label>
            <div class="input-suffix-wrap">
                <input type="number" id="globalThresholdPallets" min="0" placeholder="0">
                <span class="input-suffix">pallets</span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Alert Threshold — Containers</label>
            <div class="input-suffix-wrap">
                <input type="number" id="globalThresholdContainers" min="0" placeholder="0">
                <span class="input-suffix">containers</span>
            </div>
        </div>

        <div class="form-hint" style="margin-top:-10px; margin-bottom:18px;">
            You'll be alerted when stock falls to or below any of these levels
        </div>

        <div class="toggle-row">
            <div class="tr-info">
                <div class="tr-title">Email Notifications</div>
                <div class="tr-sub">Receive email alerts for low stock</div>
            </div>
            <label class="toggle">
                <input type="checkbox" id="globalAlertEmailToggle">
                <span class="toggle-track"></span>
                <span class="toggle-thumb"></span>
            </label>
        </div>

        <div class="modal-footer">
            <button class="btn-cancel" style="margin-left:auto;" onclick="closeModal('globalAlertModal')">Cancel</button>
            <button class="btn-primary" onclick="saveGlobalAlert()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                Save Alert
            </button>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════
     MODAL: Inventory History
══════════════════════════════════════════════════ --}}
<div class="modal-backdrop" id="historyModal" onclick="backdropClose('historyModal',event)">
    <div class="modal-box" style="max-width:500px;">
        <button class="modal-close" onclick="closeModal('historyModal')">×</button>
        <div class="modal-title">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="12 8 12 12 14 14"/>
                <path d="M3.05 11a9 9 0 1 0 .5-3.5"/>
                <polyline points="3 4 3 11 10 11"/>
            </svg>
            Inventory History
        </div>
        <div class="modal-subtitle" id="historySku"></div>

        <div class="history-meta">
            <span id="historyCount"></span>
            <button class="btn-outline" style="padding:5px 12px;font-size:12px;" onclick="loadHistory(currentHistoryId)">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <polyline points="23 4 23 10 17 10"/>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                </svg>
                Refresh
            </button>
        </div>

        <div class="history-list" id="historyList">
            <div style="text-align:center;padding:30px;color:#9ca3af;">Loading…</div>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════
     MODAL: Adjust Stock
══════════════════════════════════════════════════ --}}
<div class="modal-backdrop" id="adjustModal" onclick="backdropClose('adjustModal',event)">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('adjustModal')">×</button>
        <div class="modal-title">Adjust Stock</div>
        <div class="modal-subtitle" id="adjustSku"></div>

        <div class="adjust-tabs">
            <button class="adjust-tab active add" id="tabAdd" onclick="switchAdjustTab('add')">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add Stock
            </button>
            <button class="adjust-tab" id="tabReduce" onclick="switchAdjustTab('reduce')">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Reduce Stock
            </button>
        </div>

        <div class="form-group">
            <label class="form-label">Quantity</label>
            <input type="number" class="qty-input" id="adjustQty" min="1"
                   placeholder="Enter quantity" oninput="updateAdjustBtn()">
        </div>

        <div class="form-group">
            <label class="form-label">
                Reason
                <span style="color:#9ca3af;font-weight:400;"> (optional)</span>
            </label>
            <textarea class="reason-input" id="adjustReason"
                      placeholder="e.g., Restocked from supplier, Damaged items removed…"></textarea>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
    <button class="btn-cancel" style="margin-left:0;" onclick="closeModal('adjustModal')">Cancel</button>
    <button class="btn-primary btn-add-stock" id="adjustSubmitBtn" onclick="submitAdjust()">
        + Add 0
    </button>
</div>
    </div>
</div>

{{-- Toast --}}
<div class="inv-toast" id="invToast"></div>
@endsection

@section('scripts')
<script>
// ── State ──────────────────────────────────────────────────────────────────
let currentAlertListingId  = null;
let currentAdjustListingId = null;
let currentAdjustType      = 'add';
let currentHistoryId       = null;
let currentFilter          = '{{ $filter }}';
let currentSort            = '{{ $sort }}';
let searchTimer            = null;


// ── Toast ──────────────────────────────────────────────────────────────────
function showToast(msg, type = '') {
    const t = document.getElementById('invToast');
    t.textContent = msg;
    t.className   = 'inv-toast show' + (type ? ' ' + type : '');
    setTimeout(() => { t.className = 'inv-toast'; }, 3200);
}

// ── Modal helpers ──────────────────────────────────────────────────────────
function closeModal(id)       { document.getElementById(id).classList.remove('open'); }
function backdropClose(id, e) { if (e.target === document.getElementById(id)) closeModal(id); }

function debounceSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(fetchListings, 350);
}

function refreshListings() { fetchListings(); }

function fetchListings() {
    const search = document.getElementById('invSearch').value;
    fetch(`{{ route('admin.inventory.index') }}?filter=${currentFilter}&sort=${currentSort}&search=${encodeURIComponent(search)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => renderTable(d.listings))
    .catch(() => showToast('Failed to refresh.', 'error'));
}

function renderTable(listings) {
    const tbody = document.getElementById('invTableBody');
    if (!listings || listings.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6"><div class="inv-empty">No inventory records found.</div></td></tr>`;
        return;
    }

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    })[character]);

    const boxIcon = `<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73L13 2.27a2 2 0 0 0-2 0L4 6.27A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>`;
    const whIcon  = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>`;
    const bellIcon = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>`;
    const histIcon = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="12 8 12 12 14 14"/><path d="M3.05 11a9 9 0 1 0 .5-3.5"/><polyline points="3 4 3 11 10 11"/></svg>`;

    tbody.innerHTML = listings.map(l => {
        const stock    = l.current_stock ?? 0;
        const unit     = l.stock_unit ?? 'pieces';
        // ✅ Use warehouse_display_name (set by the controller), not warehouse?.name
        const wh       = l.warehouse_display_name ?? 'N/A';
        const at       = l.alert_threshold;
        const alertSet = at !== null && at !== undefined;
        const sku      = escapeHtml(l.sku_code);
        const product  = escapeHtml(l.product_name || 'N/A');
        const listUrl  = escapeHtml(l.listing_url || '#');

        return `<tr>
            <td><div class="sku-wrap">
                <div class="sku-icon">${boxIcon}</div>
                <div>
                    <a class="sku-code" href="${listUrl}">${sku}</a>
                    <div class="sku-status">${l.is_active ? 'Active' : 'Inactive'}</div>
                </div>
            </div></td>
            <td><span class="product-name">${product}</span></td>
            <td><span class="stock-strong">${stock.toLocaleString()}</span> <span class="stock-unit">${unit}</span></td>
            <td><div class="wh-wrap">${whIcon} ${wh}</div></td>
            <td>${alertSet
                ? `<span class="alert-text alert-set">Alert at ${at}</span>`
                : `<span class="alert-text">Not set</span>`
            }</td>
            <td><div class="action-btns">
    <button class="action-btn" title="Adjust Stock"
            data-id="${l._id}" data-sku="${l.sku_code}" data-stock="${stock}" data-unit="${unit}"
            onclick="openAdjust(this)">
        ${boxIcon}
    </button>
    <button class="action-btn" title="Inventory History"
            data-id="${l._id}" data-sku="${l.sku_code}"
            onclick="openHistory(this)">
        ${histIcon}
    </button>
    <button class="action-btn ${alertSet ? 'bell-active' : ''}" title="Stock Alert"
            data-id="${l._id}" data-sku="${l.sku_code}" data-stock="${stock}" data-unit="${unit}"
            data-threshold="${alertSet ? at : ''}" data-email="${l.alert_email_enabled ? '1' : '0'}"
            onclick="openAlert(this)">
        ${bellIcon}
    </button>
</div></td>
        </tr>`;
    }).join('');
}

// ── Global Alert Modal ─────────────────────────────────────────────────────
function openGlobalAlert() {
    document.getElementById('globalThresholdPieces').value     = '';
    document.getElementById('globalThresholdPallets').value    = '';
    document.getElementById('globalThresholdContainers').value = '';
    document.getElementById('globalAlertEmailToggle').checked  = false;
    const track = document.querySelector('#globalAlertModal .toggle-track');
    const thumb  = document.querySelector('#globalAlertModal .toggle-thumb');
    if (track) track.style.background = '#d1d5db';
    if (thumb)  thumb.style.transform  = 'translateX(0)';
    document.getElementById('globalAlertModal').classList.add('open');
}

function saveGlobalAlert() {
    const pieces     = document.getElementById('globalThresholdPieces').value.trim();
    const pallets    = document.getElementById('globalThresholdPallets').value.trim();
    const containers = document.getElementById('globalThresholdContainers').value.trim();

    if (pieces === '' && pallets === '' && containers === '') {
        showToast('Please enter at least one threshold.', 'error');
        return;
    }

    const thresholds = {};
    if (pieces     !== '') thresholds['pieces']     = parseInt(pieces);
    if (pallets    !== '') thresholds['pallets']    = parseInt(pallets);
    if (containers !== '') thresholds['containers'] = parseInt(containers);

    const emailEnabled = document.getElementById('globalAlertEmailToggle').checked;

    const btn = document.querySelector('#globalAlertModal .btn-primary');
    const originalHTML = btn.innerHTML;
    btn.disabled   = true;
    btn.textContent = 'Saving…';

    fetch('{{ route("admin.inventory.globalAlert") }}', {
        method : 'POST',
        headers: {
            'Content-Type' : 'application/json',
            'X-CSRF-TOKEN' : document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ thresholds, email_enabled: emailEnabled }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            showToast(d.message, 'success');
            closeModal('globalAlertModal');
            fetchListings();   // re-renders table so alert column reflects new values
        } else {
            showToast(d.message || 'Error saving global alert.', 'error');
        }
    })
    .catch(() => showToast('Network error.', 'error'))
    .finally(() => {
        btn.disabled  = false;
        btn.innerHTML = originalHTML;
    });
}

// ── Alert Modal ────────────────────────────────────────────────────────────
function openAlert(el) {
    const threshold    = el.dataset.threshold || null;
    const emailEnabled = el.dataset.email === '1';
    currentAlertListingId = el.dataset.id;
    document.getElementById('alertSku').textContent          = el.dataset.sku;
    document.getElementById('alertCurrentStock').textContent = `${el.dataset.stock} ${el.dataset.unit}`;
    document.getElementById('alertUnit').textContent         = el.dataset.unit;
    document.getElementById('alertThreshold').value          = threshold !== null ? threshold : '';
    document.getElementById('alertEmailToggle').checked      = emailEnabled;
    document.getElementById('alertRemoveBtn').style.display  = threshold !== null ? 'inline-flex' : 'none';

    // Fix toggle visual state on every open
    const track = document.querySelector('#alertModal .toggle-track');
    const thumb  = document.querySelector('#alertModal .toggle-thumb');
    if (track) track.style.background = emailEnabled ? 'var(--primary)' : '#d1d5db';
    if (thumb)  thumb.style.transform  = emailEnabled ? 'translateX(18px)' : 'translateX(0)';

    document.getElementById('alertModal').classList.add('open');
}

function saveAlert() {
    const threshold = document.getElementById('alertThreshold').value;
    if (threshold === '') { showToast('Please enter a threshold.', 'error'); return; }
    fetch(`/admin/inventory/${currentAlertListingId}/alert`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({
            threshold    : parseInt(threshold),
            email_enabled: document.getElementById('alertEmailToggle').checked,
        }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) { showToast('Alert saved.', 'success'); closeModal('alertModal'); fetchListings(); }
        else           { showToast(d.message || 'Error saving alert.', 'error'); }
    })
    .catch(() => showToast('Network error.', 'error'));
}

function removeAlert() {
    fetch(`/admin/inventory/${currentAlertListingId}/alert`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) { showToast('Alert removed.', 'success'); closeModal('alertModal'); fetchListings(); }
    })
    .catch(() => showToast('Network error.', 'error'));
}

// ── History Modal ──────────────────────────────────────────────────────────
function openHistory(el) {
    currentHistoryId = el.dataset.id;
    document.getElementById('historySku').textContent   = el.dataset.sku;
    document.getElementById('historyCount').textContent = '';
    document.getElementById('historyList').innerHTML    = '<div style="text-align:center;padding:30px;color:#9ca3af;">Loading…</div>';
    document.getElementById('historyModal').classList.add('open');
    loadHistory(el.dataset.id);
}

function loadHistory(listingId) {
    fetch(`/admin/inventory/${listingId}/history`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => {
        if (!d.success) { showToast('Failed to load history.', 'error'); return; }

        document.getElementById('historySku').textContent   = `${d.sku} — Current stock: ${d.current_stock} ${d.stock_unit}`;
        document.getElementById('historyCount').textContent = `${d.transactions.length} transaction${d.transactions.length !== 1 ? 's' : ''}`;

        if (d.transactions.length === 0) {
            document.getElementById('historyList').innerHTML = '<div style="text-align:center;padding:30px;color:#9ca3af;">No transactions yet.</div>';
            return;
        }

        const upDownIcon = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="17 11 12 6 7 11"/><polyline points="17 18 12 13 7 18"/></svg>`;

        document.getElementById('historyList').innerHTML = d.transactions.map(tx => {
            const isAdd    = tx.is_addition;
            const cls      = tx.transaction_type === 'initial_stock' ? 'init' : isAdd ? 'add' : 'remove';
            const qtyClass = isAdd ? 'pos' : 'neg';
            const qtySign  = isAdd ? '+' : '−';

            let detailLine = tx.notes ? `<div class="history-detail">${tx.notes}</div>` : '';
            if (tx.transaction_type === 'initial_stock') {
                detailLine = `<div class="history-detail">0 ${d.stock_unit} → ${tx.quantity} ${d.stock_unit}</div>` + detailLine;
            }

            return `<div class="history-item">
                <div class="history-icon ${cls}">${upDownIcon}</div>
                <div class="history-body">
                    <div class="history-type">${tx.label}</div>
                    ${detailLine}
                    <div class="history-meta-row">${tx.created_at ?? ''}</div>
                </div>
                <div class="history-qty ${qtyClass}">${qtySign}${tx.quantity} ${d.stock_unit}</div>
            </div>`;
        }).join('');
    })
    .catch(() => showToast('Network error.', 'error'));
}

// ── Adjust Modal ───────────────────────────────────────────────────────────
function openAdjust(el) {
    currentAdjustListingId = el.dataset.id;
    currentAdjustType      = 'add';
    document.getElementById('adjustSku').textContent = `${el.dataset.sku} — Current stock: ${el.dataset.stock} ${el.dataset.unit}`;
    document.getElementById('adjustQty').value       = '';
    document.getElementById('adjustReason').value    = '';
    switchAdjustTab('add');
    document.getElementById('adjustModal').classList.add('open');
}

function switchAdjustTab(type) {
    currentAdjustType = type;
    const tabAdd    = document.getElementById('tabAdd');
    const tabReduce = document.getElementById('tabReduce');
    const addSvg    = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>`;
    const minusSvg  = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/></svg>`;

    tabAdd.className    = 'adjust-tab' + (type === 'add'    ? ' active add'    : '');
    tabAdd.innerHTML    = addSvg + ' Add Stock';
    tabReduce.className = 'adjust-tab' + (type === 'reduce' ? ' active reduce' : '');
    tabReduce.innerHTML = minusSvg + ' Reduce Stock';
    updateAdjustBtn();
}

function updateAdjustBtn() {
    const qty = parseInt(document.getElementById('adjustQty').value) || 0;
    const btn = document.getElementById('adjustSubmitBtn');
    if (currentAdjustType === 'add') {
        btn.textContent = `+ Add ${qty}`;
        btn.className   = 'btn-primary btn-add-stock';
    } else {
        btn.textContent = `− Remove ${qty}`;
        btn.className   = 'btn-primary btn-remove-stock';
    }
}

function submitAdjust() {
    const qty = parseInt(document.getElementById('adjustQty').value);
    if (!qty || qty < 1) { showToast('Enter a valid quantity.', 'error'); return; }

    fetch(`/admin/inventory/${currentAdjustListingId}/adjust`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({
            type    : currentAdjustType,
            quantity: qty,
            notes   : document.getElementById('adjustReason').value,
        }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            showToast(`Stock updated. New stock: ${d.new_stock}`, 'success');
            closeModal('adjustModal');
            fetchListings();
        } else {
            showToast(d.message || 'Error.', 'error');
        }
    })
    .catch(() => showToast('Network error.', 'error'));
}
</script>
@endsection
