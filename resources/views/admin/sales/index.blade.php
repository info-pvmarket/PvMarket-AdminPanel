@extends('layouts.admin')
@section('title', 'Sales History')

@section('styles')
<style>
/* ── Page header ── */
.sales-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.sales-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--text);
    flex: 1;
}

.product-filter-select {
    padding: 10px 16px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-family: inherit;
    font-size: 14px;
    color: var(--text);
    background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394A3B8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 12px center;
    appearance: none;
    min-width: 240px;
    cursor: pointer;
    outline: none;
    transition: border-color .2s;
}

.product-filter-select:focus { border-color: var(--primary); }

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #1E293B;
    color: white;
    border: none;
    border-radius: 8px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background .15s;
}

.btn-back:hover { background: #334155; color: white; }

/* ── Controls bar ── */
.controls-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 12px;
}

.search-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}

.search-label { font-size: 14px; font-weight: 500; color: var(--text); }

.search-input {
    padding: 8px 14px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-family: inherit;
    font-size: 13px;
    color: var(--text);
    outline: none;
    min-width: 200px;
    transition: border-color .2s;
}

.search-input:focus { border-color: var(--primary); }

.show-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: var(--text);
}

.show-select {
    padding: 6px 10px;
    border: 1.5px solid var(--border);
    border-radius: 6px;
    font-family: inherit;
    font-size: 13px;
    outline: none;
}

/* ── Table ── */
.sales-table-wrap {
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    max-width: 100%;
    width: 100%;
}


.sales-table-scroll {
    overflow-x: auto;
    width: 100%;
    max-width: 100%;
    -webkit-overflow-scrolling: touch;
}

.sales-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    min-width: 1100px; /* ADD THIS so columns don't squish */
}

.sales-table thead tr {
    background: #F8FAFC;
    border-bottom: 2px solid var(--border);
}

.sales-table thead th {
    padding: 14px 12px;
    text-align: center;
    font-size: 12px;
    font-weight: 700;
    color: var(--primary-d);
    text-transform: uppercase;
    letter-spacing: .4px;
    white-space: nowrap;
    cursor: pointer;
    user-select: none;
}

.sales-table thead th:hover { background: #EFF6FF; }

.th-sort { display: inline-flex; align-items: center; gap: 4px; }

.sales-table tbody tr {
    border-bottom: 1px solid var(--border);
    transition: background .1s;
}

.sales-table tbody tr:hover { background: #F8FAFC; }
.sales-table tbody tr:last-child { border-bottom: none; }

.sales-table td {
    padding: 14px 12px;
    text-align: center;
    vertical-align: middle;
    color: var(--text);
}

/* ── Order id link ── */
.order-id-link {
    color: var(--primary);
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    display: block;
    gap: 4px;
}

.order-id-link:hover { text-decoration: underline; }

/* ── Product cell ── */
.product-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.product-img {
    width: 56px;
    height: 56px;
    object-fit: contain;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: #F8FAFC;
}

.product-img-placeholder {
    width: 56px;
    height: 56px;
    background: #F1F5F9;
    border: 1px solid var(--border);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: var(--muted);
}

.product-code {
    padding: 2px 8px;
    background: #F1F5F9;
    border: 1px solid var(--border);
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    color: var(--text);
}

/* ── Qty cell ── */
.qty-cell { text-align: center; }
.qty-total { font-weight: 700; margin-bottom: 4px; }
.qty-val   { color: var(--primary-d); font-weight: 800; }
.qty-unit  { color: var(--primary); font-size: 12px; }
.qty-price-label { font-size: 12px; color: var(--muted); margin-top: 4px; }
.qty-price-val   { color: var(--primary-d); font-weight: 700; }

/* ── Total cost ── */
.total-cost { font-weight: 700; font-size: 14px; }

/* ── Payment method badge ── */
.badge-payment {
    display: inline-block;
    padding: 5px 12px;
    background: #FF9800;
    color: white;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 4px;
}

.proof-link {
    color: var(--primary);
    font-size: 12px;
    cursor: pointer;
    text-decoration: none;
}

.proof-link:hover { text-decoration: underline; }

/* ── Partial payment ── */
.partial-amount { font-weight: 600; margin-bottom: 6px; }
.dash { color: var(--muted); }

/* ── Status badges ── */
.badge-verified {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 6px;
}

.badge-not-verified { background: #FF9800; color: white; }
.badge-is-verified  { background: #22C55E; color: white; }

.mark-verified-btn {
    background: none;
    border: none;
    color: var(--primary);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    display: flex;
    align-items: center;
    gap: 4px;
    margin: 0 auto;
}

.mark-verified-btn:hover { text-decoration: underline; }

/* ── Order status badge ── */
.badge-order-status {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    line-height: 1.4;
    text-align: center;
}

.status-orange { background: #FFF3E0; color: #E65100; border: 1px solid #FFE0B2; }
.status-blue   { background: #E3F2FD; color: #1565C0; border: 1px solid #BBDEFB; }
.status-purple { background: #F3E5F5; color: #6A1B9A; border: 1px solid #E1BEE7; }
.status-green  { background: #E8F5E9; color: #2E7D32; border: 1px solid #C8E6C9; }
.status-red    { background: #FFEBEE; color: #C62828; border: 1px solid #FFCDD2; }

/* ── Company cell ── */
.company-name { font-weight: 600; font-size: 13px; }

/* ── Date cell ── */
.date-cell { font-size: 12px; color: var(--text); line-height: 1.6; }

/* ── Empty state ── */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--muted);
}

.empty-state-icon { font-size: 48px; margin-bottom: 12px; }
.empty-state-text { font-size: 15px; font-weight: 500; }

/* ── Pagination / count ── */
.table-footer {
    padding: 14px 20px;
    background: #F8FAFC;
    border-top: 1px solid var(--border);
    font-size: 13px;
    color: var(--muted);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* ── Order row expand ── */
.order-id-chevron {
    font-size: 10px;
    color: var(--muted);
    transition: transform .2s;
}

.order-id-link.expanded .order-id-chevron { transform: rotate(180deg); }

.order-detail-row td {
    padding: 0 !important;
    background: #F8FAFC;
    border-bottom: 1px solid var(--border);
}

.order-detail-panel {
    padding: 16px 20px;
    text-align: left;
}

.collapse-trigger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-top: 8px;
    padding: 6px 12px;
    background: #F1F5F9;
    color: var(--primary-d);
    border: 1px solid var(--border);
    border-radius: 6px;
    font-family: inherit;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s, border-color .15s;
}

.collapse-trigger:hover {
    background: #EFF6FF;
    border-color: var(--primary);
}

.collapse-trigger .collapse-chevron {
    font-size: 9px;
    transition: transform .2s;
}

.collapse-trigger.expanded .collapse-chevron { transform: rotate(180deg); }

.collapse-panel {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    align-items: start;
}

@media (max-width: 900px) {
    .collapse-panel { grid-template-columns: 1fr; }
}

.collapse-section {
    background: white;
    border: 1px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
}

.collapse-section-head {
    padding: 12px 16px;
    background: #F8FAFC;
    border-bottom: 1px solid var(--border);
    font-size: 12px;
    font-weight: 700;
    color: var(--primary-d);
    text-transform: uppercase;
    letter-spacing: .35px;
}

.collapse-section-body {
    padding: 16px;
}

.status-update-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.status-update-form .field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.status-update-form label {
    font-size: 11px;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: .3px;
}

.status-update-form .field {
    width: 100%;
}

.status-select {
    padding: 6px 10px;
    border: 1.5px solid var(--border);
    border-radius: 6px;
    font-size: 12px;
    font-family: inherit;
    cursor: pointer;
    outline: none;
    width: 100%;
    background: white;
    margin-top: 6px;
}

.status-select:focus { border-color: var(--primary); }

.status-note-input {
    padding: 6px 10px;
    border: 1.5px solid var(--border);
    border-radius: 6px;
    font-size: 12px;
    font-family: inherit;
    outline: none;
    width: 100%;
    resize: vertical;
    min-height: 50px;
    box-sizing: border-box;
    margin-top: 6px;
}

.status-note-input:focus { border-color: var(--primary); }

.btn-save-note {
    margin-top: 6px;
    padding: 5px 12px;
    background: var(--primary-d);
    color: white;
    border: none;
    border-radius: 6px;
    font-family: inherit;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
    width: 100%;
}

.btn-save-note:hover { background: var(--primary); }
.btn-save-note:disabled { opacity: .6; cursor: not-allowed; }

/* ── Notes history collapse ── */
.notes-collapse-trigger {
    margin-top: 8px;
    padding: 0;
    background: transparent;
    color: #f97316;
    border: none;
    font-family: inherit;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    width: fit-content;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.notes-collapse-trigger:hover {
    background: transparent;
    border-color: transparent;
    text-decoration: underline;
}

.notes-collapse-trigger.expanded .collapse-chevron {
    transform: rotate(180deg);
}

.notes-collapse-trigger .collapse-chevron {
    font-size: 9px;
    transition: transform .2s;
    margin-left: 6px;
}

.val-bottom { vertical-align: bottom; }

.order-status-cell {
    display: flex;
    flex-direction: column;
    min-width: 180px;
}

.order-status-bottom {
    margin-top: auto;
    width: fit-content;
    text-align: left;
}

.notes-trigger-cell {
    padding: 8px 16px;
    background: #FAFBFD;
    border-top: 1px solid var(--border);
    text-align: left;
}

.notes-cell {
    padding: 10px 16px !important;
    background: #F8FAFC;
    border: 1px solid var(--border);
    border-top: none;
    text-align: left;
}

.notes-empty {
    font-size: 12px;
    color: var(--muted);
    font-style: italic;
    text-align: center;
    padding: 8px 0;
}

.notes-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 280px;
    overflow-y: auto;
    padding: 4px 0;
}

.note-item {
    border: 1px solid var(--border);
    border-radius: 6px;
    background: #FAFBFD;
    padding: 8px 12px;
    overflow: hidden;
    flex: 1 1 100%;
    min-width: 0;
}

.note-item-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.note-item-body {
    margin: 0;
    padding: 0;
    font-size: 13px;
    color: var(--text);
    line-height: 1.4;
    white-space: pre-wrap;
    word-break: break-word;
    flex: 1 1 auto;
    min-width: 120px;
}

.note-item-body.muted { color: var(--muted); font-style: italic; }

.note-item-head {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 6px;
    padding: 4px 0 0;
    flex-wrap: wrap;
}

.note-item-date {
    font-size: 10px;
    color: var(--muted);
    white-space: nowrap;
}

.note-item-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    padding: 6px 8px;
    background: white;
    border-top: 1px solid var(--border);
    flex-wrap: wrap;
}

.note-item-date {
    font-size: 10px;
    color: var(--muted);
    white-space: nowrap;
}

.note-item-body {
    margin: 0;
    padding: 6px 8px;
    font-size: 12px;
    color: var(--text);
    line-height: 1.4;
    white-space: pre-wrap;
    word-break: break-word;
    text-align: left;
}

.note-item-body.muted {
    color: var(--muted);
    font-style: italic;
}

.btn-update-status {
    padding: 8px 18px;
    background: var(--primary-d);
    color: white;
    border: none;
    border-radius: 6px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}

.btn-update-status:hover { background: var(--primary); }
.btn-update-status:disabled { opacity: .6; cursor: not-allowed; }

.notes-history-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 280px;
    overflow-y: auto;
}

.notes-history-empty {
    font-size: 13px;
    color: var(--muted);
    font-style: italic;
    text-align: center;
    padding: 12px 0;
}

.note-history-item {
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    background: #FAFBFD;
}

.note-history-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 8px 12px;
    background: white;
    border-bottom: 1px solid var(--border);
    flex-wrap: wrap;
}

.note-history-date {
    font-size: 11px;
    color: var(--muted);
    white-space: nowrap;
}

.note-history-body {
    margin: 0;
    padding: 10px 12px;
    font-size: 13px;
    color: var(--text);
    line-height: 1.5;
    white-space: pre-wrap;
    word-break: break-word;
}

.note-history-body.muted {
    color: var(--muted);
    font-style: italic;
}

.history-collapse-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 12px 16px;
    background: #F8FAFC;
    border: none;
    border-bottom: 1px solid var(--border);
    font-family: inherit;
    font-size: 12px;
    font-weight: 700;
    color: var(--primary-d);
    text-transform: uppercase;
    letter-spacing: .35px;
    cursor: pointer;
    text-align: left;
}

.history-collapse-trigger:hover { background: #EFF6FF; }

.history-collapse-trigger .collapse-chevron {
    font-size: 9px;
    transition: transform .2s;
}

.history-collapse-trigger.expanded .collapse-chevron { transform: rotate(180deg); }

.history-collapse-body {
    border-top: none;
}

.history-collapse-body .collapse-section-body {
    padding-top: 12px;
}
</style>
@endsection

@section('content')

{{-- ── Page Header ── --}}
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Sales History</h1>
    <a href="{{ route('admin.dashboard') }}" class="btn-back">← Back</a>
</div>

{{-- Product Filter — centred --}}
<div style="display:flex; justify-content:center; margin-bottom:24px;">
    <select class="product-filter-select" id="productFilter"
            onchange="filterByProduct(this.value)"
            style="min-width:320px; max-width:500px; width:100%;">
        <option value="">Select Product</option>
        @foreach($products as $product)
            <option value="{{ $product->id }}"
                {{ request('product_id') == $product->id ? 'selected' : '' }}>
                {{ $product->product_name }}
            </option>
        @endforeach
    </select>
</div>

{{-- ── Controls Bar ── --}}
<div class="controls-bar">
    <div class="search-wrap">
        <span class="search-label">Search:</span>
        <input type="text"
               class="search-input"
               id="searchInput"
               placeholder="Search By order Id"
               value="{{ request('search') }}"
               oninput="filterTable(this.value)"/>
    </div>

    <div class="show-wrap">
        Show
        <select class="show-select" id="showEntries" onchange="changePageSize(this.value)">
            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
            <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
            <option value="100">100</option>
        </select>
        entries
    </div>
</div>

{{-- ── Table ── --}}
<div class="sales-table-wrap">
    <div class="sales-table-scroll"> 
    <table class="sales-table" id="salesTable">
        <thead>
            <tr>
                <th onclick="sortTable(0)"><span class="th-sort">Order Id </span></th>
                <th onclick="sortTable(1)"><span class="th-sort">Product </span></th>
                <th onclick="sortTable(2)"><span class="th-sort">Total Qty/Price per Qty </span></th>
                <th onclick="sortTable(3)"><span class="th-sort">Total Cost </span></th>
                <th>Payment Method</th>
                <th>Partial Payment Amount</th>
                <th onclick="sortTable(6)"><span class="th-sort">Buyer </span></th>
                <th onclick="sortTable(7)"><span class="th-sort">Seller </span></th>
                <th>Order Status</th>
                <th>Delivery Charge</th>
                <th onclick="sortTable(10)"><span class="th-sort">Ordered At </span></th>
            </tr>
        </thead>
        <tbody id="salesTableBody">
            @forelse($orders as $order)
            @php
                $product   = $order->product_info;
                // Get first image from listing images array
                $listingImages = $order->listing_images ?? [];
                $imgUrl = null;
                if (!empty($listingImages) && is_array($listingImages)) {
                    $firstImg = $listingImages[0] ?? null;
                    if (is_array($firstImg) && !empty($firstImg['url'])) {
                        $imgUrl = $firstImg['url'];
                    } elseif (is_array($firstImg) && !empty($firstImg['path'])) {
                        $imgUrl = asset('storage/' . $firstImg['path']);
                    }
                }

                $currency  = (!empty($order->payment_currency) && $order->payment_currency !== 'null')
                             ? $order->payment_currency
                             : ((!empty($order->purchased_currency) && $order->purchased_currency !== 'null')
                                ? $order->purchased_currency : 'USD');

                $total     = (!empty($order->payment_currency_total) && $order->payment_currency_total !== 'null')
                             ? $order->payment_currency_total
                             : (floatval($order->each_qty_price ?? 0) * intval($order->total_qty ?? 0));

                $statusColor = \App\Models\Order::statusColorClass($order->order_status);
                $statusLabel  = \App\Models\Order::statusLabelFromMixed($order->order_status);
                $statusInt    = \App\Models\Order::statusToInt($order->order_status);
            @endphp
            @php
                $statusHistory = $order->status_notes ?? [];
                if (!is_array($statusHistory)) $statusHistory = [];
                $statusHistory = array_reverse($statusHistory);
                $historyCount  = count($statusHistory);
            @endphp
            <tr data-id="{{ $order->id }}">

                {{-- Order ID --}}
                <td>
                    <span class="order-id-link">{{ $order->unique_id ?? 'ORD-' . substr($order->id, -6) }}</span>
                </td>

                {{-- Product --}}
                <td>
                    <div class="product-cell">
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}" alt="Product" class="product-img"
                                 onerror="this.style.display='none'"/>
                        @else
                            <div class="product-img-placeholder">No img</div>
                        @endif
                        @if($product)
                            <span class="product-code">
                                {{ $product->product_name }}
                            </span>
                        @endif
                    </div>
                </td>

                {{-- Qty/Price --}}
                <td class="qty-cell">
                    <div class="qty-total">
                        Total Qty: <span class="qty-val">{{ $order->total_qty ?? '-' }}</span>
                        <span class="qty-unit">piece</span>
                    </div>
                    <div class="qty-price-label">
                        Each Qty Price:
                        <span class="qty-price-val">
                            {{ $order->each_qty_price ?? '-' }}
                            ({{ $currency }})
                        </span>
                    </div>
                </td>

                {{-- Total Cost --}}
<td class="total-cost">
    {{ $total ?? '-' }}<br/>
    <span style="font-size:11px; color:var(--muted);">
        ({{ $currency !== 'null' && $currency ? $currency : 'USD' }})
    </span>
</td>

                {{-- Payment Method --}}
<td>
    <span class="badge-payment">
        {{ match((int)$order->payment_method) {
            1 => 'Offline Transaction',
            2 => 'Online',
            3 => 'Stripe',
            default => 'Offline Transaction'
        } }}
    </span>

    @if((int)$order->payment_method === 1)
        <br/>
        @if($order->transaction_upload)
            <a href="{{ route('admin.sales.proof', $order->id) }}"
               class="proof-link"
               target="_blank"
               title="View payment proof">
                📄 Proof
            </a>
        @else
            <span style="font-size:11px; color:#CBD5E1;">No proof uploaded</span>
        @endif
    @endif
</td>

                {{-- Partial Payment --}}
                <td>
                    @if($order->partial_payment_amount)
                        <div class="partial-amount">
    {{ $order->partial_payment_amount }}({{ $currency }})
</div>
                        <span class="badge-verified {{ $order->payment_verified ? 'badge-is-verified' : 'badge-not-verified' }}">
                            {{ $order->payment_verified ? 'Verified' : 'Not Verified' }}
                        </span>
                        @if(!$order->payment_verified)
                            <br/>
                            <button class="mark-verified-btn"
                                    onclick="markVerified('{{ $order->id }}', this)">
                                Mark Verified ✓
                            </button>
                        @endif
                    @else
                        <span class="dash">-</span>
                    @endif
                </td>

                {{-- Buyer --}}
                <td class="company-name">
                    {{ $order->buyer_name ?? '-' }}
                </td>

                {{-- Seller --}}
                <td class="company-name">
                    {{ $order->seller_name ?? '-' }}
                </td>

                {{-- Order Status --}}
                <td class="order-status-cell val-bottom">
                    <div class="order-status-top">
                        <span class="badge-order-status {{ $statusColor }}" id="status-badge-{{ $order->id }}">
                            {{ $statusLabel }}
                        </span>
                        <br/>
                        <select class="status-select"
                                id="status-select-{{ $order->id }}"
                                onchange="updateStatus('{{ $order->id }}', this.value)">
                            <option value="Pending" {{ $statusInt === 0 ? 'selected' : '' }}>Pending</option>
                            <option value="Confirmed" {{ $statusInt === 1 ? 'selected' : '' }}>Confirmed</option>
                            <option value="Shipped" {{ $statusInt === 2 ? 'selected' : '' }}>Shipped</option>
                            <option value="Delivered" {{ $statusInt === 3 ? 'selected' : '' }}>Delivered</option>
                            <option value="Cancelled" {{ $statusInt === 4 ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <textarea class="status-note-input"
                                  id="status-note-{{ $order->id }}"
                                  rows="2"
                                  placeholder="Add a note…"></textarea>
                        <button type="button"
                                class="btn-save-note"
                                onclick="updateNote('{{ $order->id }}')">
                            Save note
                        </button>
                    </div>
                </td>

                {{-- Delivery Charge --}}
                <td>
                    {{ $order->delivery_charge ? $order->delivery_charge . '(' . $currency . ')' : '-' }}
                </td>

                {{-- Ordered At --}}
                <td class="date-cell">
                    @if($order->created_at)
                        {{ \Carbon\Carbon::parse($order->created_at)->format('d-m-Y') }}<br/>
                        {{ \Carbon\Carbon::parse($order->created_at)->format('h:i A') }}
                    @else
                        -
                    @endif
                </td>

            </tr>

            {{-- Notes trigger row --}}
            <tr id="notes-trigger-row-{{ $order->id }}">
                <td colspan="11" class="notes-trigger-cell">
                    <button type="button"
                            class="notes-collapse-trigger"
                            id="notes-trigger-{{ $order->id }}"
                            onclick="toggleNotesPanel('{{ $order->id }}')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="8 6 12 2 16 6"></polyline>
                            <polyline points="8 10 12 6 16 10"></polyline>
                            <polyline points="8 14 12 10 16 14"></polyline>
                            <polyline points="8 18 12 14 16 18"></polyline>
                        </svg>
                        <span id="notes-count-{{ $order->id }}">View notes ({{ $historyCount }})</span>
                        <span class="collapse-chevron">▼</span>
                    </button>
                </td>
            </tr>

            {{-- Notes panel row (hidden by default) --}}
            <tr id="notes-row-{{ $order->id }}" style="display:none;">
                <td colspan="11" class="notes-cell">
                    <div class="notes-list" id="notes-list-{{ $order->id }}">
                        @forelse($statusHistory as $entry)
                            @php
                                $entryStatus = $entry['order_status'] ?? 0;
                                $entryNote   = trim((string) ($entry['note'] ?? ''));
                                $entryAt     = $entry['created_at'] ?? null;
                                if ($entryAt && !($entryAt instanceof \Carbon\Carbon)) {
                                    try { $entryAt = \Carbon\Carbon::parse($entryAt); } catch (\Exception $e) { $entryAt = null; }
                                }
                            @endphp
                            <div class="note-item">
                                <div class="note-item-row">
                                    <span class="badge-order-status {{ \App\Models\Order::statusColorClassFromMixed($entryStatus) }}">
                                        {{ \App\Models\Order::statusLabelFromMixed($entryStatus) }}
                                    </span>
                                    <p class="note-item-body {{ $entryNote === '' ? 'muted' : '' }}">
                                        {{ $entryNote !== '' ? $entryNote : 'No note provided.' }}
                                    </p>
                                </div>
                                <div class="note-item-head">
                                    @if($entryAt)
                                        <span class="note-item-date">{{ $entryAt->format('d-m-Y h:i A') }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="notes-empty">No notes yet.</div>
                        @endforelse
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11">
                    <div class="empty-state">
                        <div class="empty-state-icon">📦</div>
                        <div class="empty-state-text">No sales records found.</div>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
     </div>

    {{-- Footer count --}}
    <div class="table-footer">
    <span>{{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} entries</span>

    @if ($orders->hasPages())
    <nav style="display:flex; align-items:center; gap:4px;">
        @if ($orders->onFirstPage())
            <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">‹</span>
        @else
            <a href="{{ $orders->previousPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">‹</a>
        @endif

        @foreach ($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
            @if ($page == $orders->currentPage())
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--primary-d);background:var(--primary-d);color:white;font-size:13px;font-weight:700;">{{ $page }}</span>
            @elseif ($page == 1 || $page == $orders->lastPage() || abs($page - $orders->currentPage()) <= 2)
                <a href="{{ $url }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:13px;font-weight:500;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">{{ $page }}</a>
            @elseif ($page == $orders->currentPage() - 3 || $page == $orders->currentPage() + 3)
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--muted);font-size:13px;">…</span>
            @endif
        @endforeach

        @if ($orders->hasMorePages())
            <a href="{{ $orders->nextPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">›</a>
        @else
            <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">›</span>
        @endif
    </nav>
    @endif
</div>
</div>

@endsection

@section('scripts')
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

// ── Filter by product dropdown ──
function filterByProduct(productId) {
    const url = new URL(window.location.href);
    if (productId) {
        url.searchParams.set('product_id', productId);
    } else {
        url.searchParams.delete('product_id');
    }
    window.location.href = url.toString();
}

// ── Search filter (client-side) ──
function filterTable(query) {
    const rows  = document.querySelectorAll('#salesTableBody tr[data-id]');
    const q     = query.toLowerCase();
    let visible = 0;

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const show = text.includes(q);
        row.style.display = show ? '' : 'none';
        const detail = document.getElementById('order-detail-' + row.dataset.id);
        if (detail) {
            if (!show) {
                detail.style.display = 'none';
            } else {
                const trigger = row.querySelector('.collapse-trigger') || row.querySelector('.order-id-link');
                detail.style.display = trigger?.classList.contains('expanded') ? '' : 'none';
            }
        }
        if (show) visible++;
    });
}

// ── Change page size (reload with param) ──
function changePageSize(size) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', size);
    window.location.href = url.toString();
}

// ── Sort table columns ──
let sortDir = {};
function sortTable(colIndex) {
    const tbody = document.getElementById('salesTableBody');
    const rows  = Array.from(tbody.querySelectorAll('tr[data-id]'));
    const dir   = sortDir[colIndex] === 'asc' ? 'desc' : 'asc';
    sortDir[colIndex] = dir;

    rows.sort((a, b) => {
        const aText = a.cells[colIndex]?.textContent.trim() ?? '';
        const bText = b.cells[colIndex]?.textContent.trim() ?? '';
        return dir === 'asc'
            ? aText.localeCompare(bText, undefined, { numeric: true })
            : bText.localeCompare(aText, undefined, { numeric: true });
    });

    rows.forEach(r => {
        tbody.appendChild(r);
        const detail = document.getElementById('order-detail-' + r.dataset.id);
        if (detail) tbody.appendChild(detail);
    });
}

// ── Mark payment verified ──
async function markVerified(orderId, btn) {
    if (!confirm('Mark this payment as verified?')) return;

    btn.disabled    = true;
    btn.textContent = 'Verifying...';

    try {
        const res  = await fetch(`/admin/sales/${orderId}/verify-payment`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        });
        const data = await res.json();

        if (data.success) {
            // Update badge in row
            const row    = btn.closest('tr');
            const badge  = row.querySelector('.badge-verified');
            if (badge) {
                badge.textContent = 'Verified';
                badge.classList.remove('badge-not-verified');
                badge.classList.add('badge-is-verified');
            }
            btn.parentElement.removeChild(btn);
        } else {
            btn.disabled    = false;
            btn.textContent = 'Mark Verified ✓';
            alert('Error: ' + (data.message || 'Could not verify'));
        }
    } catch (e) {
        btn.disabled    = false;
        btn.textContent = 'Mark Verified ✓';
        alert('Network error');
    }
}

const STATUS_LABELS = {
    0: 'Pending under payment verification',
    1: 'Confirmed',
    2: 'Shipped',
    3: 'Delivered',
    4: 'Cancelled',
};

const STATUS_COLORS = {
    0: 'status-orange',
    1: 'status-blue',
    2: 'status-purple',
    3: 'status-green',
    4: 'status-red',
};

const STATUS_SHORT = {
    0: 'Pending',
    1: 'Confirmed',
    2: 'Shipped',
    3: 'Delivered',
    4: 'Cancelled',
};

function resolveStatusColor(status) {
    if (typeof status === 'string') {
        const lower = status.toLowerCase().trim();
        const map = {
            'pending under payment verification': 'status-orange',
            'pending': 'status-orange',
            'confirmed': 'status-blue',
            'shipped': 'status-purple',
            'delivered': 'status-green',
            'cancelled': 'status-red',
        };
        return map[lower] || 'status-orange';
    }
    return STATUS_COLORS[parseInt(status, 10)] || 'status-orange';
}

function resolveStatusLabel(status) {
    if (typeof status === 'string') {
        return status;
    }
    return STATUS_SHORT[parseInt(status, 10)] || 'Unknown';
}

// ── Update order status ──
async function updateStatus(orderId, status) {
    try {
        const res = await fetch(`/admin/sales/${orderId}/status`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body:    JSON.stringify({ order_status: status }),
        });
        const data = await res.json();

        if (!res.ok || !data.success) {
            alert(data.message || 'Failed to update status');
            return;
        }

        const badge = document.getElementById('status-badge-' + orderId);
        if (badge) {
            badge.textContent = data.status_label || resolveStatusLabel(status);
            badge.className = 'badge-order-status ' + (data.status_color || resolveStatusColor(status));
        }
    } catch (e) {
        alert('Network error updating status');
    }
}

// ── Save note with current status ──
window.updateNote = async function updateNote(orderId) {
    const noteEl   = document.getElementById('status-note-' + orderId);
    const selectEl = document.getElementById('status-select-' + orderId);
    const btn      = noteEl?.parentElement?.querySelector('.btn-save-note');
    const note     = (noteEl?.value ?? '').trim();
    const status   = selectEl?.value ?? 'Pending';

    if (!note) {
        alert('Please write a note before saving.');
        noteEl?.focus();
        return;
    }

    if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }

    try {
        const res = await fetch(`/admin/sales/${orderId}/note`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body:    JSON.stringify({ status_note: note, order_status: status }),
        });
        const data = await res.json();

        if (!res.ok || !data.success) {
            alert(data.message || 'Failed to save note');
            if (btn) { btn.disabled = false; btn.textContent = 'Save note'; }
            return;
        }

        // Clear textarea and update the badge to reflect the saved status
        if (noteEl) noteEl.value = '';
        const badge = document.getElementById('status-badge-' + orderId);
        if (badge) {
            badge.textContent = data.status_label || resolveStatusLabel(status);
            badge.className = 'badge-order-status ' + (data.status_color || resolveStatusColor(status));
        }

        // Re-render the notes panel
        renderNotesList(orderId, data.status_notes || []);

        // Auto-expand the notes row so the user sees the new entry
        const row     = document.getElementById('notes-row-' + orderId);
        const trigger = document.getElementById('notes-trigger-' + orderId);
        if (row && trigger) {
            row.style.display = 'table-row';
            trigger.classList.add('expanded');
        }

        if (btn) {
            btn.textContent = 'Saved ✓';
            setTimeout(() => { btn.textContent = 'Save note'; btn.disabled = false; }, 1200);
        }
    } catch (e) {
        alert('Network error saving note');
        if (btn) { btn.disabled = false; btn.textContent = 'Save note'; }
    }
}

// ── Toggle the notes history panel ──
function toggleNotesPanel(orderId) {
    const panel   = document.getElementById('notes-row-' + orderId);
    const trigger = document.getElementById('notes-trigger-' + orderId);
    if (!panel || !trigger) return;

    const isHidden = panel.style.display === 'none';
    panel.style.display = isHidden ? 'table-row' : 'none';
    trigger.classList.toggle('expanded', isHidden);
}

// ── Re-render the notes list (newest first) ──
function renderNotesList(orderId, history) {
    const list    = document.getElementById('notes-list-' + orderId);
    const countEl = document.getElementById('notes-count-' + orderId);
    if (!list) return;

    // Server returns oldest→newest; show newest first
    const items = (history || []).slice().reverse();

    if (countEl) countEl.textContent = 'View notes (' + items.length + ')';

    if (!items.length) {
        list.innerHTML = '<div class="notes-empty">No notes yet.</div>';
        return;
    }

    list.innerHTML = items.map(entry => {
        const st    = entry.order_status ?? 0;
        const note  = (entry.note || '').trim();
        const color = resolveStatusColor(st);
        const label = resolveStatusLabel(st);
        const date  = entry.created_at ? formatNoteDate(entry.created_at) : '';

        return `<div class="note-item">
            <div class="note-item-row">
                <span class="badge-order-status ${color}">${escapeHtml(label)}</span>
                <p class="note-item-body ${note ? '' : 'muted'}">${note ? escapeHtml(note) : 'No note provided.'}</p>
            </div>
            <div class="note-item-head">
                ${date ? `<span class="note-item-date">${escapeHtml(date)}</span>` : ''}
            </div>
        </div>`;
    }).join('');
}

function formatNoteDate(iso) {
    try {
        const d = new Date(iso);
        return d.toLocaleString('en-GB', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit', hour12: true,
        });
    } catch {
        return '';
    }
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
</script>
@endsection