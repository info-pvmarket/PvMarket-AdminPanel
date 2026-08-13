@extends('layouts.admin')

@section('title', 'Edit Listing')

@php
    $selectedSellType = old('sell_type', $listing->sell_type);
    $quantityUnits = [
        'sell by pieces' => 'pcs',
        'sell by pallets' => 'pallets',
        'sell by containers' => 'container',
    ];
    $selectedQuantityUnit = $quantityUnits[$selectedSellType] ?? 'pcs';
    $listingImageCount = $listingImages->count();
@endphp

@section('styles')
<style>
    :root {
        --or:      #F97316;
        --or-lt:   #FFF7ED;
        --or-d:    #EA6C0A;
        --text:    #1F2937;
        --muted:   #6B7280;
        --border:  #E5E7EB;
        --bg:      #F3F4F6;
        --white:   #FFFFFF;
        --green:   #10B981;
        --red:     #EF4444;
        --blue:    #3B82F6;
        --blue-lt: #EFF6FF;
        --blue-d:  #2563EB;
    }

    .edit-page {
        display: flex;
        gap: 24px;
        padding: 28px;
        background: var(--bg);
        min-height: 100vh;
        align-items: flex-start;
    }

    .edit-main {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .edit-sidebar {
        width: 280px;
        flex-shrink: 0;
        position: sticky;
        top: 80px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* ── Page header ── */
    .page-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 4px;
    }

    .btn-back-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 1px solid var(--border);
        background: var(--white);
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: var(--text);
        flex-shrink: 0;
        transition: background .15s;
    }
    .btn-back-circle:hover { background: var(--bg); }

    .page-header h1 { font-size: 1.35rem; font-weight: 800; color: var(--text); margin: 0; }
    .page-header p  { font-size: .8rem; color: var(--muted); margin: 2px 0 0; }

    /* ── Section card ── */
    .section-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 22px;
        border-bottom: 1px solid var(--border);
    }

    .section-icon {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .icon-blue   { background: var(--blue-lt); color: var(--blue); }
    .icon-green  { background: #D1FAE5;        color: #059669; }
    .icon-orange { background: var(--or-lt);   color: var(--or); }

    .section-title    { font-size: .95rem; font-weight: 700; color: var(--blue); margin: 0; }
    .section-subtitle { font-size: .78rem; color: var(--muted); margin: 2px 0 0; }

    .section-body { padding: 22px; }

    /* ── Notice banner ── */
    .notice-banner {
        background: #FFFBEB;
        border-bottom: 1px solid #FDE68A;
        padding: 10px 22px;
        font-size: .8rem;
        color: #92400E;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* ── Form elements ── */
    .form-row       { display: grid; gap: 16px; }
    .form-row.cols-3 { grid-template-columns: repeat(3,1fr); }
    .form-row.cols-2 { grid-template-columns: repeat(2,1fr); }
    .form-row.cols-1 { grid-template-columns: 1fr; }

    .form-group { display: flex; flex-direction: column; gap: 5px; }

    .form-label { font-size: .8rem; font-weight: 600; color: var(--text); }
    .form-label .req { color: var(--or); }

    .form-control, .form-select {
        padding: 9px 13px;
        border: 1px solid var(--border);
        border-radius: 9px;
        font-size: .88rem;
        color: var(--text);
        outline: none;
        background: #fff;
        width: 100%;
        font-family: inherit;
        transition: border-color .15s, box-shadow .15s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--blue);
        box-shadow: 0 0 0 3px rgba(59,130,246,.1);
    }
    .form-control:disabled {
        background: #F9FAFB;
        color: var(--muted);
        cursor: not-allowed;
    }

    .input-suffix { display: flex; }
    .input-suffix .form-control { border-radius: 9px 0 0 9px; border-right: none; }
    .suffix-label {
        padding: 9px 12px;
        background: #F3F4F6;
        border: 1px solid var(--border);
        border-radius: 0 9px 9px 0;
        font-size: .85rem;
        color: var(--muted);
        white-space: nowrap;
    }

    .error-msg { color: var(--red); font-size: .75rem; margin-top: 3px; }

    /* ── Warehouse pill ── */
    .warehouse-pill {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background: #F9FAFB;
        border: 1px solid var(--border);
        border-radius: 9px;
        margin-bottom: 18px;
        font-size: .85rem;
        color: var(--text);
        font-weight: 500;
    }

    /* ── Inventory action buttons ── */
    .inventory-actions {
        display: flex;
        gap: 8px;
        margin-left: auto;
    }

    .btn-sm-outline {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 7px 13px;
        border: 1px solid var(--border);
        background: #fff;
        border-radius: 8px;
        font-size: .78rem;
        color: var(--text);
        cursor: pointer;
        font-family: inherit;
        transition: border-color .15s;
    }
    .btn-sm-outline:hover { border-color: #9CA3AF; }

    /* ── Toggle rows ── */
    .toggle-section {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin: 0 22px 22px;
    }

    .toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        background: #F9FAFB;
        border: 1px solid var(--border);
        border-radius: 12px;
        transition: border-color .2s;
    }
    .toggle-row:hover { border-color: #D1D5DB; }

    .toggle-left { display: flex; align-items: center; gap: 12px; }

    .toggle-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .toggle-info-title { font-size: .85rem; font-weight: 700; color: var(--text); }
    .toggle-info-sub   { font-size: .73rem; color: var(--muted); margin-top: 2px; }

    /* ── Custom toggle switch ── */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        cursor: pointer;
        flex-shrink: 0;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }

    .toggle-track {
        position: absolute;
        inset: 0;
        background: #D1D5DB;
        border-radius: 24px;
        transition: background .2s;
    }
    .toggle-thumb {
        position: absolute;
        left: 2px; top: 2px;
        width: 20px; height: 20px;
        background: #fff;
        border-radius: 50%;
        transition: transform .2s;
        box-shadow: 0 1px 3px rgba(0,0,0,.2);
    }

    .toggle-switch input:checked ~ .toggle-track { background: var(--blue); }
    .toggle-switch input:checked ~ .toggle-thumb { transform: translateX(20px); }
    .toggle-switch.sold-off-switch input:checked ~ .toggle-track { background: var(--red); }
    .toggle-switch.popular-switch  input:checked ~ .toggle-track { background: #F59E0B; }

    /* ── Price Tiers ── */
    .slots-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }
    .slots-label { font-size: .88rem; font-weight: 700; color: var(--text); }
    .slots-hint  { font-size: .75rem; color: var(--muted); margin-top: 2px; }

    .btn-add-slot {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border: 1px solid var(--border);
        background: var(--white);
        border-radius: 8px;
        font-size: .82rem;
        color: var(--text);
        cursor: pointer;
        font-family: inherit;
        transition: border-color .15s;
    }
    .btn-add-slot:hover { border-color: #9CA3AF; }

    .slot-list-box {
        border: 1px solid var(--border);
        border-radius: 10px;
        overflow: hidden;
        min-height: 52px;
    }

    .slot-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        border-bottom: 1px solid #F3F4F6;
        font-size: .83rem;
    }
    .slot-item:last-child { border-bottom: none; }

    .slot-num {
        width: 24px; height: 24px;
        border-radius: 50%;
        background: var(--blue);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: .72rem; font-weight: 700;
        flex-shrink: 0;
        margin-right: 10px;
    }

    .slot-price-tag { font-size: .9rem; font-weight: 700; color: var(--text); }
    .slot-price-sub { font-size: .72rem; color: var(--muted); }
    .slot-item-empty { padding: 18px; text-align: center; color: #9CA3AF; font-size: .82rem; }

    .btn-icon-sm {
        background: none; border: none; cursor: pointer;
        padding: 4px; color: #9ca3af; font-size: .8rem;
    }
    .btn-icon-sm:hover { color: var(--blue); }

    /* ── Add slot form ── */
    .slot-form-box {
        border: 2px solid var(--blue);
        border-radius: 12px;
        padding: 18px 20px;
        margin-top: 12px;
        background: #F0F7FF;
    }
    .slot-form-title {
        display: flex; align-items: center; justify-content: space-between;
        font-size: .88rem; font-weight: 700; color: var(--text); margin-bottom: 16px;
    }
    .range-badge {
        font-size: .73rem; color: var(--muted);
        background: #F3F4F6; border: 1px solid var(--border);
        border-radius: 6px; padding: 2px 8px;
    }
    .slot-form-row { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 12px; align-items: end; }

    .btn-cancel {
        padding: 8px 18px; background: var(--white);
        border: 1px solid var(--border); border-radius: 8px;
        font-size: .85rem; color: var(--text); cursor: pointer; font-family: inherit;
    }
    .btn-save-slot {
        padding: 8px 20px; background: var(--blue); color: #fff;
        border: none; border-radius: 8px; font-size: .85rem;
        font-weight: 600; cursor: pointer; font-family: inherit; transition: background .15s;
    }
    .btn-save-slot:hover { background: var(--blue-d); }

    /* ── Inventory History ── */
    .inv-history-empty {
        text-align: center; padding: 36px 20px;
        color: var(--muted); font-size: .85rem;
    }

    /* ── Image upload ── */
    .upload-zone {
        border: 2px dashed var(--border);
        border-radius: 12px;
        padding: 22px 14px;
        text-align: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
    }
    .upload-zone:hover { border-color: var(--blue); background: var(--blue-lt); }

    .upload-icon {
        width: 40px; height: 40px;
        border-radius: 50%;
        background: #F3F4F6;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 8px; color: var(--blue);
    }
    .upload-title { font-size: .85rem; font-weight: 600; color: var(--text); margin-bottom: 3px; }
    .upload-sub   { font-size: .72rem; color: var(--muted); }

    /* ── Image grids ── */
    .img-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 12px;
    }

    .img-grid-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid var(--border);
        aspect-ratio: 1;
        background: #F9FAFB;
    }

    .img-grid-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .img-remove-btn {
        position: absolute;
        top: 4px; right: 4px;
        width: 20px; height: 20px;
        background: rgba(0,0,0,.55);
        border: none;
        border-radius: 50%;
        color: #fff;
        font-size: .65rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        transition: background .15s;
    }
    .img-remove-btn:hover { background: var(--red); }

    .img-new-badge {
        position: absolute;
        bottom: 4px; left: 4px;
        background: var(--blue);
        color: #fff;
        font-size: .6rem;
        font-weight: 700;
        padding: 1px 5px;
        border-radius: 4px;
    }

    .img-section-label {
        font-size: .75rem;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 8px;
        margin-top: 4px;
    }

    .img-count-note {
        font-size: .73rem;
        color: var(--muted);
        text-align: center;
        margin-top: 8px;
    }

    /* ── Sidebar ── */
    .summary-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
    }
    .summary-header {
        display: flex; align-items: center; gap: 10px;
        padding: 16px 18px; border-bottom: 1px solid var(--border);
    }
    .summary-header-title { font-size: .9rem; font-weight: 700; color: var(--blue); }
    .summary-header-sub   { font-size: .73rem; color: var(--muted); }

    .summary-body { padding: 16px 18px; }
    .summary-row {
        display: flex; justify-content: space-between; align-items: flex-start;
        padding: 9px 0; border-bottom: 1px solid #F3F4F6; gap: 12px;
    }
    .summary-row:last-child { border-bottom: none; }
    .summary-key { font-size: .8rem; color: var(--muted); }
    .summary-val { font-size: .82rem; font-weight: 600; color: var(--text); text-align: right; max-width: 55%; }

    .btn-update {
        width: 100%; padding: 12px; background: var(--blue); color: #fff;
        border: none; border-radius: 10px; font-size: .9rem; font-weight: 700;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        gap: 8px; margin-top: 14px; font-family: inherit; transition: background .15s;
    }
    .btn-update:hover { background: var(--blue-d); }

    .update-note { text-align: center; font-size: .72rem; color: var(--muted); margin-top: 8px; }

    /* ── Alert ── */
    .alert-error {
        background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b;
        padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-size: .85rem;
    }

    /* ── Sticky bottom bar ── */
    .sticky-submit-bar {
        position: sticky;
        bottom: 0;
        left: 0;
        right: 0;
        background: var(--white);
        border-top: 1px solid var(--border);
        padding: 14px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-shadow: 0 -4px 16px rgba(0,0,0,.06);
        z-index: 50;
        border-radius: 0 0 14px 14px;
        margin-top: 4px;
    }

    .sticky-submit-info {
        font-size: .8rem;
        color: var(--muted);
    }

    .sticky-submit-info strong {
        color: var(--text);
        font-weight: 700;
    }

    .btn-update-main {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 28px;
        background: var(--blue);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: .92rem;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
        transition: background .15s, transform .1s;
        white-space: nowrap;
    }
    .btn-update-main:hover  { background: var(--blue-d); }
    .btn-update-main:active { transform: scale(.98); }

    .btn-update-main .spinner {
        display: none;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255,255,255,.4);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin .6s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    @media (max-width: 900px) {
        .edit-page { flex-direction: column; padding: 16px; }
        .edit-sidebar { width: 100%; position: static; }
        .form-row.cols-3 { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 600px) {
        .form-row.cols-3,
        .form-row.cols-2 { grid-template-columns: 1fr; }
        .slot-form-row { grid-template-columns: 1fr 1fr; }
        .toggle-section { margin: 0 16px 16px; }
        .sticky-submit-bar { flex-direction: column; align-items: stretch; }
        .btn-update-main { justify-content: center; }
    }
    /* ── Modals ── */
.modal-backdrop {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.5); z-index: 1050;
    align-items: center; justify-content: center;
}
.modal-backdrop.open { display: flex; }
.modal-box {
    background: #fff; border-radius: 18px; padding: 26px;
    width: 100%; max-width: 430px; position: relative;
    box-shadow: 0 24px 64px rgba(0,0,0,.2);
    animation: modalIn .18s ease; margin: 16px;
}
@keyframes modalIn {
    from { opacity:0; transform: translateY(12px) scale(.97); }
    to   { opacity:1; transform: translateY(0) scale(1); }
}
.modal-close {
    position: absolute; top: 16px; right: 16px;
    width: 30px; height: 30px; border-radius: 50%;
    border: none; background: #f3f4f6; cursor: pointer;
    color: var(--muted); font-size: 18px;
    display: flex; align-items: center; justify-content: center;
}
.modal-close:hover { background: #e5e7eb; }
.modal-title {
    display: flex; align-items: center; gap: 9px;
    font-size: 16px; font-weight: 800; color: var(--text); margin-bottom: 2px;
}
.modal-subtitle { font-size: 12px; color: #9ca3af; margin-bottom: 20px; }
.modal-form-group { margin-bottom: 18px; }
.modal-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 7px; }
.modal-qty-input, .modal-reason-input {
    width: 100%; padding: 11px 13px;
    border: 1.5px solid var(--border); border-radius: 9px;
    font-size: 14px; color: var(--text); outline: none;
    transition: border-color .15s; box-sizing: border-box; font-family: inherit;
}
.modal-qty-input:focus, .modal-reason-input:focus { border-color: var(--blue); }
.modal-reason-input { min-height: 85px; resize: vertical; }
.adjust-tabs {
    display: flex; margin-bottom: 22px;
    border-radius: 10px; overflow: hidden; border: 1.5px solid var(--border);
}
.adjust-tab {
    flex: 1; padding: 11px 10px; border: none; background: #fff;
    font-size: 13px; font-weight: 600; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    transition: background .15s, color .15s; font-family: inherit; color: var(--muted);
}
.adjust-tab:first-child { border-right: 1.5px solid var(--border); }
.adjust-tab.active.add    { background: var(--green); color: #fff; }
.adjust-tab.active.reduce { background: var(--red);   color: #fff; }
.modal-btn-primary {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 20px; border-radius: 9px; border: none;
    background: var(--blue); color: #fff; font-size: 13px;
    font-weight: 700; cursor: pointer; font-family: inherit; transition: background .12s;
}
.modal-btn-primary:hover { background: var(--blue-d); }
.modal-btn-add    { background: var(--green) !important; }
.modal-btn-add:hover { background: #15803d !important; }
.modal-btn-remove { background: var(--red) !important; }
.modal-btn-remove:hover { background: #b91c1c !important; }
.modal-btn-cancel {
    padding: 9px 18px; border-radius: 9px;
    border: 1.5px solid var(--border); background: #fff;
    color: #374151; font-size: 13px; font-weight: 600;
    cursor: pointer; font-family: inherit;
}
.modal-btn-cancel:hover { background: #f9fafb; }
.modal-btn-danger {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 9px 15px; border-radius: 9px;
    border: 1.5px solid #fecaca; background: #fff;
    color: var(--red); font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit;
}
.modal-btn-danger:hover { background: #fef2f2; }
.inv-toast {
    position: fixed; bottom: 28px; right: 28px;
    background: var(--text); color: #fff;
    padding: 12px 20px; border-radius: 11px;
    font-size: 13px; font-weight: 600;
    box-shadow: 0 8px 28px rgba(0,0,0,.2); z-index: 9999;
    transform: translateY(20px); opacity: 0;
    transition: transform .25s, opacity .25s; pointer-events: none;
}
.inv-toast.show    { transform: translateY(0); opacity: 1; }
.inv-toast.success { background: var(--green); }
.inv-toast.error   { background: var(--red); }
</style>
@endsection

@section('content')
<div class="edit-page">

    <form method="POST" action="{{ route('product_listing.update', $listing->_id) }}"
          id="editForm"
          style="display:contents;"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- ══════════════════════════════
             MAIN COLUMN
        ══════════════════════════════ --}}
        <div class="edit-main">

            {{-- Page header --}}
            <div>
                <div class="page-header">
                    <a href="{{ route('product_listing.index') }}" class="btn-back-circle">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div>
                        <h1>Edit Listing</h1>
                        <p>Update your offer details and pricing</p>
                    </div>
                    <div style="margin-left:auto;">
                        <span style="font-size:.75rem; color:var(--muted); background:var(--white); border:1px solid var(--border); border-radius:20px; padding:4px 12px;">
                            SKU: {{ $listing->sku_code }}
                        </span>
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="alert-error">
                    <ul style="margin:0; padding-left:18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ── Product Details (read-only) ── --}}
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon icon-blue">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="section-title">Product Details</p>
                        <p class="section-subtitle">Select the product you want to list</p>
                    </div>
                </div>
                <div class="section-body">
    <div class="form-row cols-3">
        <div class="form-group">
            <label class="form-label">Main Category <span class="req">*</span></label>
            <select name="main_category_id" class="form-select" id="mainCatSelect">
                @foreach($mainCategories as $cat)
                    <option value="{{ $cat->id }}" {{ (string)$listing->main_category_id === (string)$cat->id ? 'selected' : '' }}>
                        {{ $cat->category_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Sub Category <span class="req">*</span></label>
            <select name="sub_category_id" class="form-select" id="subCatSelect">
                @foreach($subCategories as $sub)
                    <option value="{{ $sub->id }}" {{ (string)$listing->sub_category_id === (string)$sub->id ? 'selected' : '' }}>
                        {{ $sub->sub_category_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Product <span class="req">*</span></label>
            <select name="product_id" class="form-select" id="productSelect">
                @foreach($products as $prod)
                    <option value="{{ $prod->id }}" {{ (string)$listing->product_id === (string)$prod->id ? 'selected' : '' }}>
                        {{ $prod->product_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-row cols-1" style="margin-top:16px;">
        <div class="form-group">
            <label class="form-label">Warehouse <span class="req">*</span></label>
            <select name="warehouse_id" class="form-select">
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ (string)$listing->warehouse_id === (string)$wh->id ? 'selected' : '' }}>
                        {{ $wh->warehouse_name }} — {{ $wh->city }}, {{ $wh->country }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>
                <div class="section-body">
                    <div class="form-row cols-3">
                        <div class="form-group">
                            <label class="form-label">Main Category</label>
                            <input type="text" class="form-control"
       value="{{ $mainCategory ? lang($mainCategory, 'category_name') : $listing->main_category_id }}" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sub Category</label>
                            <input type="text" class="form-control"
       value="{{ $subCategory ? lang($subCategory, 'sub_category_name') : $listing->sub_category_id }}" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Product</label>
                            <input type="text" class="form-control"
       value="{{ $product ? lang($product, 'product_name') : $listing->product_id }}" disabled>
                        </div>
                    </div>
                    <div class="form-row cols-1" style="margin-top:16px;">
                        <div class="form-group">
                            <label class="form-label">Warehouse</label>
                            <input type="text" class="form-control"
       value="{{ $warehouse ? lang($warehouse, 'warehouse_name') : $listing->warehouse_id }}" disabled>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Offer Settings ── --}}
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon icon-blue">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="section-title">Offer Settings</p>
                        <p class="section-subtitle">Configure how you want to sell</p>
                    </div>
                </div>
                <div class="section-body">
                    <div class="form-row cols-3">
                        <div class="form-group">
                            <label class="form-label">Sell Type <span class="req">*</span></label>
                            <select name="sell_type" class="form-select" id="sellTypeSelect" required>
                                @foreach($sellTypes as $val => $label)
                                    <option value="{{ $val }}" {{ $selectedSellType === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Currency <span class="req">*</span></label>
                            <select name="currency_id" class="form-select" id="currencySelect" required>
                                <option value="">Select currency</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency['code'] }}" {{ old('currency_id', strtoupper((string) $listing->currency_id)) === $currency['code'] ? 'selected' : '' }}>
                                        {{ $currency['code'] }}{{ $currency['symbol'] !== '' ? ' (' . $currency['symbol'] . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('currency_id')<div class="error-msg">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Promotion</label>
                            <select name="discount_type" class="form-select">
                                <option value="No Promotion" {{ ($listing->discount_type ?? '') === 'No Promotion' ? 'selected' : '' }}>No Promotion</option>
                                <option value="fixed"        {{ ($listing->discount_type ?? '') === 'fixed'        ? 'selected' : '' }}>Fixed Discount</option>
                                <option value="percentage"   {{ ($listing->discount_type ?? '') === 'percentage'   ? 'selected' : '' }}>Percentage Discount</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row cols-1" style="margin-top:16px;">
                        <div class="form-group">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control"
                                   placeholder="auto-generated if blank"
                                   value="{{ old('slug') }}"/>
                            <span style="font-size:.73rem; color:var(--muted); margin-top:3px;">Leave blank to auto-generate</span>
                        </div>
                    </div>
                </div>

                @php
                    $isOfferOnHold = !filter_var($listing->is_active ?? true, FILTER_VALIDATE_BOOLEAN);
                    $isOfferSoldOff = filter_var($listing->is_sold_off ?? false, FILTER_VALIDATE_BOOLEAN);
                @endphp

                {{-- ══ Three Toggle Rows ══ --}}
                <div class="toggle-section">

                    {{-- 1. Listing status --}}
                    <div class="toggle-row">
                        <div class="toggle-left">
                            <div class="toggle-icon" style="background:#D1FAE5; color:#059669;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <div class="toggle-info-title">Mark as Hold</div>
                                <div class="toggle-info-sub" id="statusSubLabel">
                                    {{ $isOfferOnHold ? 'Listing is on hold and hidden from buyers' : 'Listing is active and visible to buyers' }}
                                </div>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_on_hold" value="1"
                                   id="toggleIsActive" {{ $isOfferOnHold ? 'checked' : '' }}>
                            <span class="toggle-track"></span>
                            <span class="toggle-thumb"></span>
                        </label>
                    </div>

                    {{-- 2. Mark as Sold Off --}}
                    <div class="toggle-row">
                        <div class="toggle-left">
                            <div class="toggle-icon" style="background:#FEE2E2; color:var(--red);">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                            </div>
                            <div>
                                <div class="toggle-info-title">Mark as Sold Off</div>
                                <div class="toggle-info-sub">Marks this offer as completely sold out</div>
                            </div>
                        </div>
                        <label class="toggle-switch sold-off-switch">
                            <input type="checkbox" name="is_sold_off" value="1"
                                   id="toggleSoldOff" {{ $isOfferSoldOff ? 'checked' : '' }}>
                            <span class="toggle-track"></span>
                            <span class="toggle-thumb"></span>
                        </label>
                    </div>

                    {{-- 3. Popular Product --}}
                    <div class="toggle-row">
                        <div class="toggle-left">
                            <div class="toggle-icon" style="background:#FEF3C7; color:#D97706;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="toggle-info-title">Popular Product</div>
                                <div class="toggle-info-sub">Highlights this offer as a popular pick</div>
                            </div>
                        </div>
                        <label class="toggle-switch popular-switch">
                            <input type="checkbox" name="is_popular" value="1"
                                   id="togglePopular" {{ !empty($listing->is_popular) ? 'checked' : '' }}>
                            <span class="toggle-track"></span>
                            <span class="toggle-thumb"></span>
                        </label>
                    </div>
                    {{-- 4. Real Time Price --}}
                    <div class="toggle-row">
                        <div class="toggle-left">
                            <div class="toggle-icon" style="background:#EFF6FF; color:var(--blue);">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="toggle-info-title">Real Time Price</div>
                                <div class="toggle-info-sub">Price updates in real time for buyers</div>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="real_time_price" value="1"
                                   id="toggleRealTimePrice" {{ !empty($listing->real_time_price) ? 'checked' : '' }}>
                            <span class="toggle-track"></span>
                            <span class="toggle-thumb"></span>
                        </label>
                    </div>

                    {{-- 5. Add to Solar Listings --}}
<div class="toggle-row">
    <div class="toggle-left">
        <div class="toggle-icon" style="background:#F0FDF4; color:#16a34a;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 3v1m0 16v1m8.66-13l-.87.5M4.21 17.5l-.87.5M20.66 17.5l-.87-.5M4.21 6.5l-.87-.5M21 12h-1M4 12H3"/>
                <circle cx="12" cy="12" r="4"/>
            </svg>
        </div>
        <div>
            <div class="toggle-info-title">Add to Solar Listings</div>
            <div class="toggle-info-sub">Feature this offer in the Solar Analysis product listings</div>
        </div>
    </div>
    <div style="display:flex; align-items:center; gap:12px;">
        {{-- Tier dropdown --}}
        <div id="solarTierWrapper" style="display:none;">
            @php
                $selectedSolarGridTypes = $selectedSolarGridTypes ?? old('solar_grid_types', $listing->solar_grid_types ?? []);
                $selectedSolarPhaseTypes = $selectedSolarPhaseTypes ?? old('solar_phase_types', $listing->solar_phase_types ?? []);
                $selectedSolarGridTypes = is_array($selectedSolarGridTypes) ? $selectedSolarGridTypes : [$selectedSolarGridTypes];
                $selectedSolarPhaseTypes = is_array($selectedSolarPhaseTypes) ? $selectedSolarPhaseTypes : [$selectedSolarPhaseTypes];
            @endphp
            <select name="solar_tier" id="solarTierSelect"
                    style="padding:6px 12px; border:1px solid var(--border); border-radius:8px;
                           font-size:.82rem; color:var(--text); background:#fff;
                           font-family:inherit; outline:none; cursor:pointer;
                           transition:border-color .15s;">
                <option value="">Select Tier</option>
                <option value="premium" {{ ($listing->solar_tier ?? '') === 'premium' ? 'selected' : '' }}>⭐ Premium</option>
                <option value="recommended" {{ ($listing->solar_tier ?? '') === 'recommended' ? 'selected' : '' }}>✅ Recommended</option>
                <option value="value" {{ ($listing->solar_tier ?? '') === 'value' ? 'selected' : '' }}>💰 Value</option>
            </select>
            <div style="margin-top:8px; display:grid; gap:6px; font-size:.72rem; color:var(--muted);">
                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                    @foreach(['on-grid' => 'On-Grid', 'off-grid' => 'Off-Grid', 'hybrid' => 'Hybrid'] as $value => $label)
                        <label style="display:inline-flex; align-items:center; gap:4px;">
                            <input class="solar-option-input" type="checkbox" name="solar_grid_types[]" value="{{ $value }}" {{ in_array($value, $selectedSolarGridTypes, true) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                    @foreach(['single' => 'Single Phase', 'three' => 'Three Phase'] as $value => $label)
                        <label style="display:inline-flex; align-items:center; gap:4px;">
                            <input class="solar-option-input" type="checkbox" name="solar_phase_types[]" value="{{ $value }}" {{ in_array($value, $selectedSolarPhaseTypes, true) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
        {{-- Toggle --}}
        <label class="toggle-switch">
            <input type="checkbox" name="is_solar_listing" value="1"
                   id="toggleSolarListing"
                   {{ !empty($listing->is_solar_listing) ? 'checked' : '' }}>
            <span class="toggle-track"></span>
            <span class="toggle-thumb"></span>
        </label>
    </div>
</div>

                </div>{{-- end toggle-section --}}
            </div>

            {{-- ── Inventory & Pricing ── --}}
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon icon-green">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="8"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
                        </svg>
                    </div>
                    <div style="flex:1;">
                        <p class="section-title">Inventory &amp; Pricing</p>
                        <p class="section-subtitle">Set quantity, lead time, and price tiers</p>
                    </div>
                    <div class="inventory-actions">
                        <button type="button" class="btn-sm-outline"
    data-id="{{ (string)$listing->_id }}"
    data-sku="{{ $listing->sku_code }}"
    data-stock="{{ $currentStock }}"
    data-unit="pieces"
    onclick="openAdjust(this)">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
    </svg>
    Adjust Stock
</button>
<button type="button" class="btn-sm-outline"
    data-id="{{ (string)$listing->_id }}"
    data-sku="{{ $listing->sku_code }}"
    data-stock="{{ $currentStock }}"
    data-unit="pieces"
    data-threshold="{{ $listing->alert_threshold ?? '' }}"
    data-email="{{ !empty($listing->alert_email_enabled) ? '1' : '0' }}"
    onclick="openAlert(this)">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
    </svg>
    Set Alert
</button>
                    </div>
                </div>
                <div class="section-body">

                    {{-- Warehouse pill --}}
                    <div class="warehouse-pill">
                        <svg width="15" height="15" fill="none" stroke="#6B7280" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5V19a1 1 0 001 1h16a1 1 0 001-1v-8.5M3 10.5L12 4l9 6.5M3 10.5h18"/>
                        </svg>
                        {{ $warehouse ? lang($warehouse, 'warehouse_name') : $listing->warehouse_id }}
                    </div>

                    <div class="form-row cols-3">
                        <div class="form-group">
                            <label class="form-label">Total Quantity <span class="req">*</span></label>
                            <div class="input-suffix">
                                <input type="number" name="total_quantity" class="form-control"
                                       value="{{ $listing->total_quantity }}" min="1" id="totalQtyInput">
                                <span class="suffix-label" id="totalQtyUnit">{{ $selectedQuantityUnit }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lead Time <span class="req">*</span></label>
                            <div class="input-suffix">
                                <input type="number" name="lead_time" class="form-control"
                                       value="{{ $listing->lead_time }}" min="0">
                                <span class="suffix-label">weeks</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Incoterm <span class="req">*</span></label>
                            <select name="incoterm_id" class="form-select">
    <option value="">Select incoterm</option>
    @foreach($incoterms as $incoterm)
        <option value="{{ $incoterm->id }}"
            {{ (string)$listing->incoterms_id === (string)$incoterm->id ? 'selected' : '' }}>
            {{ $incoterm->name }}
        </option>
    @endforeach
</select>
                        </div>
                    </div>

                    {{-- Price Tiers --}}
                    <div style="margin-top:28px;">
                        <div class="slots-header">
                            <div>
                                <div class="slots-label">Price Tiers</div>
                                <div class="slots-hint">Set different prices based on order quantity</div>
                            </div>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span style="font-size:.75rem; color:var(--muted);">
                                    + Add Tier &nbsp;<span id="tierFraction">{{ count($listing->slots ?? []) }}/3</span>
                                </span>
                                <button type="button" class="btn-add-slot" onclick="showSlotForm()">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add Tier
                                </button>
                            </div>
                        </div>

                        <div class="slot-list-box" id="slotList">
                            @if(count($listing->slots ?? []) === 0)
                                <div class="slot-item-empty">No tiers added yet. Click "Add Tier" to create one.</div>
                            @endif
                        </div>

                        @php
                            $tierQuantityError = $errors->first('slots.*.min_quantity')
                                ?: $errors->first('slots.*.max_quantity');
                        @endphp
                        <div id="tierQuantityError" class="error-msg" style="{{ $tierQuantityError ? '' : 'display:none;' }} margin-top:8px;">
                            {{ $tierQuantityError }}
                        </div>

                        {{-- Add Slot Form --}}
                        <div class="slot-form-box" id="slotFormBox" style="display:none;">
                            <div class="slot-form-title">
                                New Price Tier
                                <span class="range-badge">Bounded range</span>
                            </div>
                            <div class="slot-form-row" style="grid-template-columns: 1fr 1fr 1fr 1fr; gap:12px;">
                                <div class="form-group">
                                    <label class="form-label">Min Qty <span class="req">*</span></label>
                                    <input type="number" id="slotMinQty" class="form-control" placeholder="e.g. 1" min="0" disabled>
                                </div>
                                <div class="form-group">
    <label class="form-label">Max Qty</label>
    <div style="display:flex; gap:8px; align-items:center; height:38px; padding:0 13px; border:1px solid var(--border); border-radius:9px; background:#fff;">
        <label style="font-size:.8rem; cursor:pointer; display:flex; align-items:center; gap:4px; white-space:nowrap;">
            <input type="radio" name="maxQtyType" value="specific" id="radioSpecific" disabled> Specific
        </label>
        <label style="font-size:.8rem; cursor:pointer; display:flex; align-items:center; gap:4px; white-space:nowrap;">
            <input type="radio" name="maxQtyType" value="more" id="radioMore" checked disabled> And More
        </label>
    </div>
    <div id="specificMaxWrapper" style="display:none; margin-top:6px;">
        <input type="number" id="slotMaxQty" class="form-control" placeholder="e.g. 500" min="1" disabled>
    </div>
</div>
                                <div class="form-group">
                                    <label class="form-label">Commission % <span class="req">*</span></label>
                                    <div class="input-suffix">
                                        <input type="number" id="slotCommission" class="form-control"
                                               placeholder="Auto" step="0.01" min="0"
                                               disabled
                                               style="border-radius:9px 0 0 9px; border-right:none;"
                                               oninput="recalcTotalPrice()">
                                        <span class="suffix-label">%</span>
                                    </div>
                                    <span style="font-size:.7rem; color:var(--muted); margin-top:3px;" id="commissionSourceLabel"></span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Price / Unit <span class="req">*</span></label>
                                    <input type="number" id="slotPrice" class="form-control"
                                           placeholder="0.00" step="0.01" min="0"
                                           disabled
                                           oninput="recalcTotalPrice()">
                                </div>
                                <div class="form-group" style="grid-column: span 2;">
                                    <label class="form-label">Total Price / Unit</label>
                                    <div class="input-suffix">
                                        <input type="number" id="slotTotalPrice" class="form-control"
                                               placeholder="Auto-calculated" step="0.01" min="0" readonly
                                               disabled
                                               style="background:#F9FAFB; color:var(--muted); border-radius:9px 0 0 9px; border-right:none;">
                                        <span class="suffix-label" style="background:#EFF6FF; color:var(--blue); font-weight:700;">= Price + Commission</span>
                                    </div>
                                    <span style="font-size:.7rem; color:var(--muted); margin-top:3px;" id="totalPriceBreakdown"></span>
                                </div>
                            </div>
                            <div id="slotError" class="error-msg" style="display:none; margin-top:8px;"></div>
                            <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:12px;">
                                <button type="button" class="btn-cancel" onclick="cancelSlot()">Cancel</button>
                                <button type="button" class="btn-save-slot" onclick="saveSlot()">Add Tier</button>
                            </div>
                        </div>

                        <div id="slotInputs"></div>
                    </div>

                    {{-- Inventory History --}}
                    {{-- Inventory History --}}
<div style="margin-top:32px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
        <div style="font-size:.9rem; font-weight:700; color:var(--text);">Inventory History</div>
        <span style="font-size:.78rem; font-weight:600; background:#EFF6FF; color:var(--blue); padding:3px 10px; border-radius:20px; border:1px solid #BFDBFE;">
            Current Stock: {{ number_format($currentStock) }} pcs
        </span>
    </div>
    <div style="font-size:.78rem; color:var(--muted); margin-bottom:16px;">Recent stock movements and adjustments</div>

    @if($inventoryHistory->isEmpty())
        <div class="inv-history-empty">
            <svg width="30" height="30" fill="none" stroke="#D1D5DB" stroke-width="1.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div style="margin-top:8px;">No inventory history yet</div>
        </div>
    @else
        <div style="border:1px solid var(--border); border-radius:10px; overflow:hidden;">
            {{-- Table header --}}
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; padding:10px 16px; background:#F9FAFB; border-bottom:1px solid var(--border); font-size:.75rem; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.04em;">
                <span>Type</span>
                <span>Quantity</span>
                <span>Notes</span>
                <span>Date</span>
            </div>
            {{-- Rows --}}
            @foreach($inventoryHistory as $tx)
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; padding:11px 16px; border-bottom:1px solid #F9FAFB; font-size:.82rem; align-items:center;">
                    {{-- Type badge --}}
                    <span>
                        @if($tx->transaction_type === 'stock_add' || $tx->transaction_type === 'initial_stock')
                            <span style="display:inline-flex; align-items:center; gap:4px; background:#D1FAE5; color:#065F46; padding:2px 10px; border-radius:20px; font-size:.72rem; font-weight:600;">
                                ↑ {{ $tx->transaction_label }}
                            </span>
                        @else
                            <span style="display:inline-flex; align-items:center; gap:4px; background:#FEE2E2; color:#991B1B; padding:2px 10px; border-radius:20px; font-size:.72rem; font-weight:600;">
                                ↓ {{ $tx->transaction_label }}
                            </span>
                        @endif
                    </span>
                    {{-- Quantity --}}
                    <span style="font-weight:700; color:{{ $tx->is_addition ? 'var(--green)' : 'var(--red)' }};">
                        {{ $tx->is_addition ? '+' : '-' }}{{ number_format($tx->quantity) }}
                    </span>
                    {{-- Notes --}}
                    <span style="color:var(--muted);">{{ $tx->notes ?? '—' }}</span>
                    {{-- Date --}}
                    <span style="color:var(--muted); font-size:.75rem;">
                        {{ \Carbon\Carbon::parse($tx->created_at)->format('d M Y, h:i A') }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>

                </div>

                </div>

            <div style="display:flex; justify-content:flex-end;">
                <button type="submit" form="editForm" style="padding:12px 28px; background:#1F2937; color:white; border:none; border-radius:10px; font-size:15px; font-weight:700; cursor:pointer; font-family:inherit;">
                    Update Offer
                </button>
            </div>

        </div>{{-- end .edit-main --}}

        {{-- ══════════════════════════════
             SIDEBAR
        ══════════════════════════════ --}}
        <div class="edit-sidebar">

            {{-- ── Product Images card ── --}}
            <div class="summary-card">
                <div class="summary-header">
                    <div class="section-icon icon-blue" style="width:30px;height:30px;border-radius:8px;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 21"/>
                        </svg>
                    </div>
                    <div>
                        <div class="summary-header-title">Product Images</div>
                        <div class="summary-header-sub" id="imgCardSubtitle">
    {{ $listingImageCount }} of 5 uploaded
</div>
                    </div>
                </div>
                <div style="padding:16px 18px;">

                    {{-- Existing images --}}
                    <input type="hidden" name="image_manifest_present" value="1">
                    @if($listingImages->count() > 0)
    <p class="img-section-label">Current Images</p>
    <div class="img-grid" id="existingImagesGrid">
        @foreach($listingImages as $img)
            @if($img->public_url)
            <div class="img-grid-item" id="img-wrapper-{{ $loop->index }}">
                <img src="{{ $img->public_url }}"
                     alt="{{ $img->image['original_name'] ?? 'image' }}"
                     onerror="this.style.display='none'">
                <button type="button"
                        class="img-remove-btn"
                        onclick="removeExistingImage({{ $loop->index }})"
                        title="Remove image">✕</button>
                <input type="hidden"
                       name="existing_image_ids[]"
                       value="{{ $img->_id }}"
                       id="existing-input-{{ $loop->index }}">
            </div>
            @endif
        @endforeach
    </div>
@endif

                    {{-- New images preview --}}
                    <p id="newImagesLabel" class="img-section-label" style="display:none; margin-top:12px;">New Images</p>
                    <div id="newImagesGrid" class="img-grid" style="display:none;"></div>

                    {{-- Upload zone — always visible --}}
                    <div class="upload-zone" style="margin-top:12px;" onclick="document.getElementById('imgInput').click()">
                        <div class="upload-icon">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                        </div>
                        <div class="upload-title" id="uploadZoneTitle">Add more images</div>
                        <div class="upload-sub">JPEG, PNG, WebP · Max 5MB</div>
                        <input type="file"
                               id="imgInput"
                               name="images[]"
                               multiple
                               accept="image/*"
                               style="display:none;"
                               onchange="previewNewImages(this)">
                    </div>

                    <p id="imgCountNote" class="img-count-note" style="display:none;"></p>

                    @error('images.*')
                        <div class="error-msg" style="margin-top:6px;">{{ $message }}</div>
                    @enderror

                </div>
            </div>

            {{-- ── Offer Summary card ── --}}
            <div class="summary-card">
                <div class="summary-header">
                    <div class="section-icon icon-blue" style="width:30px;height:30px;border-radius:8px;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <div class="summary-header-title">Offer Summary</div>
                        <div class="summary-header-sub">Review before updating</div>
                    </div>
                </div>
                <div class="summary-body">
                    <div class="summary-row">
                        <span class="summary-key">Product</span>
                        <span class="summary-val">{{ $product ? lang($product, 'product_name') : $listing->product_id }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Warehouse</span>
                        <span class="summary-val">{{ $warehouse ? lang($warehouse, 'warehouse_name') : $listing->warehouse_id }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Sell Type</span>
                        <span class="summary-val" id="summSellType">{{ ucwords($listing->sell_type) }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Currency</span>
                        <span class="summary-val" id="summCurrency">{{ $listing->currency_id }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Quantity</span>
                        <span class="summary-val" id="summQty">{{ number_format($listing->total_quantity) }} {{ $selectedQuantityUnit }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Price Tiers</span>
                        <span class="summary-val" id="summTiers">
                            {{ count($listing->slots ?? []) }} {{ count($listing->slots ?? []) === 1 ? 'tier' : 'tiers' }}
                        </span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Images</span>
                        <span class="summary-val" id="summImages">
    {{ $listingImageCount }} image{{ $listingImageCount === 1 ? '' : 's' }}
</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Status</span>
                        <span class="summary-val" id="summStatus"
                              style="color:{{ $isOfferSoldOff ? 'var(--red)' : ($isOfferOnHold ? 'var(--muted)' : 'var(--green)') }};">
                            {{ $isOfferSoldOff ? 'Sold Off' : ($isOfferOnHold ? 'Inactive' : 'Active') }}
                        </span>
                    </div>

                    {{-- Sidebar update button (submits same form) --}}
                    <button type="submit" class="btn-update" onclick="handleUpdate(this)">
                        Update Offer
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <p class="update-note">Changes will be reflected immediately</p>
                </div>
            </div>

        </div>

    </form>
    {{-- ══ Adjust Stock Modal ══ --}}
<div class="modal-backdrop" id="adjustModal" onclick="backdropClose('adjustModal',event)">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('adjustModal')">×</button>
        <div class="modal-title">Adjust Stock</div>
        <div class="modal-subtitle" id="adjustSku"></div>
        <div class="adjust-tabs">
            <button class="adjust-tab active add" id="tabAdd" onclick="switchAdjustTab('add')">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Stock
            </button>
            <button class="adjust-tab" id="tabReduce" onclick="switchAdjustTab('reduce')">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Reduce Stock
            </button>
        </div>
        <div class="modal-form-group">
            <label class="modal-label">Quantity</label>
            <input type="number" class="modal-qty-input" id="adjustQty" min="1" placeholder="Enter quantity" oninput="updateAdjustBtn()">
        </div>
        <div class="modal-form-group">
            <label class="modal-label">Reason <span style="color:#9ca3af;font-weight:400;">(optional)</span></label>
            <textarea class="modal-reason-input" id="adjustReason" placeholder="e.g., Restocked from supplier…"></textarea>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
            <button class="modal-btn-cancel" onclick="closeModal('adjustModal')">Cancel</button>
            <button class="modal-btn-primary modal-btn-add" id="adjustSubmitBtn" onclick="submitAdjust()">+ Add 0</button>
        </div>
    </div>
</div>

{{-- ══ Alert Modal ══ --}}
<div class="modal-backdrop" id="alertModal" onclick="backdropClose('alertModal',event)">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('alertModal')">×</button>
        <div class="modal-title">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            Stock Alert Settings
        </div>
        <div class="modal-subtitle" id="alertSku"></div>
        <div style="background:#f0fdf4;border-radius:12px;padding:14px 18px;margin-bottom:20px;">
            <div style="font-size:12px;color:var(--muted);margin-bottom:4px;">Current Stock</div>
            <div style="font-size:22px;font-weight:800;color:var(--text);" id="alertCurrentStock"></div>
        </div>
        <div class="modal-form-group">
            <label class="modal-label">Alert Threshold</label>
            <div style="display:flex;align-items:stretch;">
                <input type="number" id="alertThreshold" min="0" placeholder="0"
                       style="flex:1;padding:10px 13px;border:1.5px solid var(--border);border-right:none;border-radius:9px 0 0 9px;font-size:14px;color:var(--text);outline:none;font-family:inherit;">
                <span id="alertUnit" style="padding:10px 13px;border:1.5px solid var(--border);border-radius:0 9px 9px 0;background:#f9fafb;color:var(--muted);font-size:13px;display:flex;align-items:center;"></span>
            </div>
            <div style="font-size:11px;color:#9ca3af;margin-top:5px;">You'll be alerted when stock falls to or below this level</div>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;background:#f9fafb;border-radius:11px;padding:14px 16px;">
            <div>
                <div style="font-size:13px;font-weight:700;color:var(--text);">Email Notifications</div>
                <div style="font-size:12px;color:#9ca3af;margin-top:2px;">Receive email alerts for low stock</div>
            </div>
            <label style="position:relative;width:42px;height:24px;display:inline-block;cursor:pointer;flex-shrink:0;">
                <input type="checkbox" id="alertEmailToggle" style="display:none;">
                <span id="alertEmailTrack" style="position:absolute;inset:0;background:#d1d5db;border-radius:12px;transition:background .2s;"></span>
                <span id="alertEmailThumb" style="position:absolute;top:3px;left:3px;width:18px;height:18px;background:#fff;border-radius:50%;transition:transform .2s;box-shadow:0 1px 4px rgba(0,0,0,.2);"></span>
            </label>
        </div>
        <div style="display:flex;align-items:center;gap:10px;margin-top:22px;">
            <button class="modal-btn-danger" id="alertRemoveBtn" onclick="removeAlert()" style="display:none;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/></svg>
                Remove Alert
            </button>
            <button class="modal-btn-cancel" onclick="closeModal('alertModal')">Cancel</button>
            <button class="modal-btn-primary" onclick="saveAlert()">Save Alert</button>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="inv-toast" id="invToast"></div>
</div>
@endsection

@section('scripts')
<script>
const COMMISSIONS = @json($commissionsJson);
function loadCommissionForCategory() {
    const categoryId = '{{ (string)$listing->main_category_id }}';
    const match = COMMISSIONS.find(c => c.category_id === categoryId);
    const commInput = document.getElementById('slotCommission');
    const commLabel = document.getElementById('commissionSourceLabel');
    if (match) {
        commInput.value       = match.percentage;
        commLabel.textContent = `Auto-filled from "${match.category_name}"`;
        commLabel.style.color = 'var(--green)';
    } else {
        commInput.value       = '';
        commLabel.textContent = 'No commission set for this category';
        commLabel.style.color = 'var(--muted)';
    }
    recalcTotalPrice();
}

function recalcTotalPrice() {
    const price       = parseFloat(document.getElementById('slotPrice').value);
    const commission  = parseFloat(document.getElementById('slotCommission').value);
    const totalEl     = document.getElementById('slotTotalPrice');
    const breakdownEl = document.getElementById('totalPriceBreakdown');
    if (!isNaN(price) && !isNaN(commission)) {
        const commAmt = price * (commission / 100);
        const total   = price + commAmt;
        totalEl.value = total.toFixed(2);
        breakdownEl.textContent = `${price.toFixed(2)} + ${commAmt.toFixed(2)} (${commission}%) = ${total.toFixed(2)}`;
        breakdownEl.style.color = 'var(--blue)';
    } else {
        totalEl.value = '';
        breakdownEl.textContent = price && !isNaN(price) ? 'Enter commission % to calculate' : '';
        breakdownEl.style.color = 'var(--muted)';
    }
}

// ── Seed existing slots from PHP ──────────────────────────────
function hasSpecificMaxQuantity(slot) {
    const maxQuantity = Number(slot?.max_quantity);
    return Number.isFinite(maxQuantity) && maxQuantity > 0;
}

// Match the seller dashboard: legacy 0, null, and empty maximum values all
// represent an open-ended "And More" tier.
let slots = @json($listing->slots ?? []).map(slot => ({
    ...slot,
    max_quantity: hasSpecificMaxQuantity(slot) ? Number(slot.max_quantity) : null,
}));
let editingIndex = null;

// Track existing image count for the 5-image cap
let existingImageCount = {{ $listingImageCount }};

// Render existing slots immediately on load
renderSlots();

// ── Live summary ─────────────────────────────────────────────
function getQuantityUnit(sellType) {
    return {
        'sell by pieces': 'pcs',
        'sell by pallets': 'pallets',
        'sell by containers': 'container',
    }[sellType] || 'pcs';
}

function syncQuantityPresentation() {
    const sellType = document.getElementById('sellTypeSelect').value;
    const quantity = document.getElementById('totalQtyInput').value;
    const unit = getQuantityUnit(sellType);

    document.getElementById('totalQtyUnit').textContent = unit;
    document.getElementById('summQty').textContent = quantity
        ? Number(quantity).toLocaleString() + ' ' + unit
        : '—';
}

document.getElementById('sellTypeSelect').addEventListener('change', function () {
    document.getElementById('summSellType').textContent =
        this.options[this.selectedIndex].text;
    syncQuantityPresentation();
});

document.getElementById('currencySelect').addEventListener('change', function () {
    document.getElementById('summCurrency').textContent = this.value;
});

document.getElementById('totalQtyInput').addEventListener('input', function () {
    syncQuantityPresentation();
});

syncQuantityPresentation();

function syncOfferStatusSummary() {
    const summStatus = document.getElementById('summStatus');
    const statusSub  = document.getElementById('statusSubLabel');
    const isOnHold   = document.getElementById('toggleIsActive').checked;
    const isActive   = !isOnHold;
    const isSoldOff  = document.getElementById('toggleSoldOff').checked;

    statusSub.textContent = isActive
        ? 'Listing is active and visible to buyers'
        : 'Listing is on hold and hidden from buyers';

    if (isSoldOff) {
        summStatus.textContent = 'Sold Off';
        summStatus.style.color = 'var(--red)';
    } else if (isActive) {
        summStatus.textContent = 'Active';
        summStatus.style.color = 'var(--green)';
    } else {
        summStatus.textContent = 'Inactive';
        summStatus.style.color = 'var(--muted)';
    }
}

// ── Toggle: Hold ──────────────────────────────────────────────
document.getElementById('toggleIsActive').addEventListener('change', function () {
    syncOfferStatusSummary();
});

// ── Toggle: Sold Off ──────────────────────────────────────────
document.getElementById('toggleSoldOff').addEventListener('change', function () {
    if (this.checked) {
        document.getElementById('togglePopular').checked  = false;
    }

    syncOfferStatusSummary();
});

// ── Toggle: Popular ───────────────────────────────────────────
document.getElementById('togglePopular').addEventListener('change', function () {
    if (this.checked) {
        document.getElementById('toggleSoldOff').checked = false;
        document.getElementById('toggleIsActive').checked = false;
        syncOfferStatusSummary();
    }
});

// ── Max qty radio ─────────────────────────────────────────────
document.querySelectorAll('input[name="maxQtyType"]').forEach(r => {
    r.addEventListener('change', function () {
        const usesSpecificMaximum = this.value === 'specific';
        const maxQuantityInput = document.getElementById('slotMaxQty');

        document.getElementById('specificMaxWrapper').style.display =
            usesSpecificMaximum ? 'block' : 'none';
        maxQuantityInput.disabled = !usesSpecificMaximum;
        if (!usesSpecificMaximum) maxQuantityInput.value = '';
    });
});

// ── Submit/Update handler ─────────────────────────────────────
document.getElementById('editForm').addEventListener('submit', function (event) {
    if (!validatePriceTiersAgainstTotalQuantity()) {
        event.preventDefault();
        return;
    }

    const spinner = document.getElementById('updateSpinner');
    if (spinner) spinner.style.display = 'inline-block';
    document.querySelectorAll('.btn-update, .btn-update-main').forEach(b => b.disabled = true);
});

function handleUpdate(btn) {}

function validatePriceTiersAgainstTotalQuantity() {
    const totalQuantity = Number(document.getElementById('totalQtyInput').value);
    const errorElement = document.getElementById('tierQuantityError');
    const hasInvalidTier = Number.isFinite(totalQuantity) && slots.some(slot =>
        Number(slot.min_quantity) >= totalQuantity ||
        (hasSpecificMaxQuantity(slot) && Number(slot.max_quantity) >= totalQuantity)
    );

    if (hasInvalidTier) {
        errorElement.textContent = 'Each price tier quantity must be less than the total quantity.';
        errorElement.style.display = 'block';
        errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }

    errorElement.textContent = '';
    errorElement.style.display = 'none';
    return true;
}

// ── Remove existing image ─────────────────────────────────────
function removeExistingImage(index) {
    const wrapper = document.getElementById('img-wrapper-' + index);
    const input   = document.getElementById('existing-input-' + index);
    if (wrapper) wrapper.remove();
    if (input)   input.remove();

    existingImageCount = Math.max(0, existingImageCount - 1);
    updateImageSummary();
}

// ── Preview newly selected images ─────────────────────────────
function previewNewImages(input) {
    const maxAllowed = 5 - existingImageCount;
    const uniqueFiles = Array.from(input.files).filter((file, index, allFiles) =>
        allFiles.findIndex(candidate =>
            candidate.name === file.name &&
            candidate.size === file.size &&
            candidate.lastModified === file.lastModified
        ) === index
    );
    const files      = uniqueFiles.slice(0, maxAllowed);
    const grid       = document.getElementById('newImagesGrid');
    const label      = document.getElementById('newImagesLabel');
    const countNote  = document.getElementById('imgCountNote');
    const zoneTitle  = document.getElementById('uploadZoneTitle');

    grid.innerHTML = '';

    // Submit exactly the same unique files shown in the preview. Previously,
    // files beyond the visible limit remained in the native FileList.
    const selectedFiles = new DataTransfer();
    files.forEach(file => selectedFiles.items.add(file));
    input.files = selectedFiles.files;

    if (files.length === 0) {
        grid.style.display  = 'none';
        label.style.display = 'none';
        countNote.style.display = 'none';
        zoneTitle.textContent = 'Add more images';
        updateImageSummary(0);
        return;
    }

    label.style.display = 'block';

    files.forEach(file => {
        const url = URL.createObjectURL(file);
        const div = document.createElement('div');
        div.className = 'img-grid-item';
        div.innerHTML = `
            <img src="${url}" alt="new image">
            <span class="img-new-badge">NEW</span>
        `;
        grid.appendChild(div);
    });

    const uploadZone = document.querySelector('.upload-zone');
    grid.parentNode.insertBefore(label, uploadZone);
    grid.parentNode.insertBefore(grid, uploadZone);

    grid.style.display = 'grid';
    zoneTitle.textContent = 'Change new images';

    if (files.length < uniqueFiles.length) {
        countNote.textContent  = `Showing ${files.length} of ${uniqueFiles.length} unique files selected (max 5 total).`;
        countNote.style.display = 'block';
    } else {
        countNote.style.display = 'none';
    }

    updateImageSummary(files.length);
}

function updateImageSummary(newCount) {
    if (newCount === undefined) newCount = 0;
    const total = existingImageCount + newCount;
    document.getElementById('summImages').textContent =
        total + ' image' + (total !== 1 ? 's' : '');
    document.getElementById('imgCardSubtitle').textContent =
        total + ' of 5 uploaded';
}

// ── Slot form ─────────────────────────────────────────────────
function setSlotEditorInputsEnabled(enabled) {
    document.querySelectorAll('#slotFormBox input, #slotFormBox select, #slotFormBox textarea')
        .forEach(control => control.disabled = !enabled);
}

function showSlotForm() {
    document.getElementById('slotFormBox').style.display = 'block';
    setSlotEditorInputsEnabled(true);
    document.getElementById('slotMinQty').value = '';
    document.getElementById('slotMaxQty').value = '';
    document.getElementById('slotPrice').value  = '';
    document.getElementById('radioMore').checked = true;
    document.getElementById('specificMaxWrapper').style.display = 'none';
    document.getElementById('slotMaxQty').disabled = true;
    document.getElementById('slotError').style.display = 'none';
    editingIndex = null;
    loadCommissionForCategory();
}

function cancelSlot() {
    setSlotEditorInputsEnabled(false);
    document.getElementById('slotFormBox').style.display = 'none';
    editingIndex = null;
}

function saveSlot() {
    const minQty     = parseInt(document.getElementById('slotMinQty').value);
    const price      = parseFloat(document.getElementById('slotPrice').value);
    const commission = parseFloat(document.getElementById('slotCommission').value);
    const totalPrice = parseFloat(document.getElementById('slotTotalPrice').value);
    const maxType    = document.querySelector('input[name="maxQtyType"]:checked').value;
    const maxQty     = maxType === 'specific' ? parseInt(document.getElementById('slotMaxQty').value) : null;
    const errEl      = document.getElementById('slotError');

    if (isNaN(minQty) || minQty < 0) {
        errEl.textContent = 'Please enter a valid minimum quantity.';
        errEl.style.display = 'block'; return;
    }
    if (isNaN(price) || price < 0) {
        errEl.textContent = 'Please enter a valid price.';
        errEl.style.display = 'block'; return;
    }
    if (isNaN(commission) || commission < 0) {
        errEl.textContent = 'Please enter a valid commission percentage.';
        errEl.style.display = 'block'; return;
    }
    if (maxType === 'specific' && (isNaN(maxQty) || maxQty <= minQty)) {
        errEl.textContent = 'Max quantity must be greater than minimum.';
        errEl.style.display = 'block'; return;
    }
    const totalQuantity = Number(document.getElementById('totalQtyInput').value);
    if (Number.isFinite(totalQuantity) &&
        (minQty >= totalQuantity || (maxQty !== null && maxQty >= totalQuantity))) {
        errEl.textContent = 'Each price tier quantity must be less than the total quantity.';
        errEl.style.display = 'block'; return;
    }

    const slot = { min_quantity: minQty, max_quantity: maxQty, price, commission_percentage: commission, total_price: totalPrice };
    if (editingIndex !== null) slots[editingIndex] = slot;
    else slots.push(slot);

    renderSlots();
    cancelSlot();
}

function editSlot(idx) {
    const s = slots[idx];
    showSlotForm();
    document.getElementById('slotMinQty').value    = s.min_quantity;
    document.getElementById('slotPrice').value     = s.price;
    document.getElementById('slotCommission').value = s.commission_percentage ?? '';
    recalcTotalPrice();
    if (hasSpecificMaxQuantity(s)) {
        document.getElementById('radioSpecific').checked = true;
        document.getElementById('specificMaxWrapper').style.display = 'block';
        document.getElementById('slotMaxQty').disabled = false;
        document.getElementById('slotMaxQty').value = s.max_quantity;
    } else {
        document.getElementById('radioMore').checked = true;
    }
    editingIndex = idx;
}

function deleteSlot(idx) {
    slots.splice(idx, 1);
    renderSlots();
}

function renderSlots() {
    const listEl   = document.getElementById('slotList');
    const inputsEl = document.getElementById('slotInputs');

    listEl.innerHTML   = '';
    inputsEl.innerHTML = '';

    if (slots.length === 0) {
        listEl.innerHTML = '<div class="slot-item-empty">No tiers added yet. Click "Add Tier" to create one.</div>';
        document.getElementById('summTiers').textContent    = '0 tiers';
        document.getElementById('tierFraction').textContent = '0/3';
        return;
    }

    slots.forEach((slot, i) => {
        const maxLabel = hasSpecificMaxQuantity(slot)
            ? `${Number(slot.max_quantity).toLocaleString()} pcs`
            : 'And More';

        const row = document.createElement('div');
        row.className = 'slot-item';
        row.innerHTML = `
            <div style="display:flex;align-items:center;flex:1;">
                <div class="slot-num">${i + 1}</div>
                <div>
                    <div style="font-weight:600;font-size:.83rem;">${slot.min_quantity.toLocaleString()} – ${maxLabel}</div>
                    <div style="font-size:.72rem;color:var(--muted);">Fixed range tier</div>
                </div>
            </div>
            <div style="text-align:right;margin-right:12px;">
                <div class="slot-price-tag">${Number(slot.price).toFixed(2)} <span style="font-size:.7rem;color:var(--muted);font-weight:400;">+ ${slot.commission_percentage ?? 0}%</span></div>
                <div class="slot-price-sub">Total: <strong style="color:var(--blue);">${Number(slot.total_price ?? slot.price).toFixed(2)}</strong> / pc</div>
            </div>
            <div style="display:flex;gap:4px;">
                <button type="button" class="btn-icon-sm" onclick="editSlot(${i})" title="Edit">✏️</button>
                <button type="button" class="btn-icon-sm" onclick="deleteSlot(${i})" title="Delete">🗑️</button>
            </div>
        `;
        listEl.appendChild(row);

        inputsEl.innerHTML += `
            <input type="hidden" name="slots[${i}][min_quantity]"          value="${slot.min_quantity}">
            <input type="hidden" name="slots[${i}][max_quantity]"          value="${slot.max_quantity ?? ''}">
            <input type="hidden" name="slots[${i}][price]"                 value="${slot.price}">
            <input type="hidden" name="slots[${i}][commission_percentage]" value="${slot.commission_percentage ?? 0}">
            <input type="hidden" name="slots[${i}][total_price]"           value="${slot.total_price ?? slot.price}">
        `;
    });

    const count = slots.length;
    document.getElementById('summTiers').textContent    = count + (count === 1 ? ' tier' : ' tiers');
    document.getElementById('tierFraction').textContent = count + '/3';
}
// ── Modal state ───────────────────────────────────────────────
let currentAlertListingId  = null;
let currentAdjustListingId = null;
let currentAdjustType      = 'add';

function showToast(msg, type = '') {
    const t = document.getElementById('invToast');
    t.textContent = msg;
    t.className   = 'inv-toast show' + (type ? ' ' + type : '');
    setTimeout(() => { t.className = 'inv-toast'; }, 3200);
}
function closeModal(id)       { document.getElementById(id).classList.remove('open'); }
function backdropClose(id, e) { if (e.target === document.getElementById(id)) closeModal(id); }

// Alert email toggle visual
document.getElementById('alertEmailToggle').addEventListener('change', function () {
    document.getElementById('alertEmailTrack').style.background = this.checked ? 'var(--blue)' : '#d1d5db';
    document.getElementById('alertEmailThumb').style.transform  = this.checked ? 'translateX(18px)' : 'translateX(0)';
});

// ── Adjust Modal ──────────────────────────────────────────────
function openAdjust(el) {
    currentAdjustListingId = el.dataset.id;
    currentAdjustType      = 'add';
    document.getElementById('adjustSku').textContent  = `${el.dataset.sku} — Current stock: ${el.dataset.stock} ${el.dataset.unit}`;
    document.getElementById('adjustQty').value        = '';
    document.getElementById('adjustReason').value     = '';
    switchAdjustTab('add');
    document.getElementById('adjustModal').classList.add('open');
}
function switchAdjustTab(type) {
    currentAdjustType = type;
    const addSvg   = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>`;
    const minusSvg = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/></svg>`;
    document.getElementById('tabAdd').className    = 'adjust-tab' + (type === 'add'    ? ' active add'    : '');
    document.getElementById('tabAdd').innerHTML    = addSvg + ' Add Stock';
    document.getElementById('tabReduce').className = 'adjust-tab' + (type === 'reduce' ? ' active reduce' : '');
    document.getElementById('tabReduce').innerHTML = minusSvg + ' Reduce Stock';
    updateAdjustBtn();
}
function updateAdjustBtn() {
    const qty = parseInt(document.getElementById('adjustQty').value) || 0;
    const btn = document.getElementById('adjustSubmitBtn');
    if (currentAdjustType === 'add') {
        btn.textContent = `+ Add ${qty}`;
        btn.className   = 'modal-btn-primary modal-btn-add';
    } else {
        btn.textContent = `− Remove ${qty}`;
        btn.className   = 'modal-btn-primary modal-btn-remove';
    }
}
function submitAdjust() {
    const qty = parseInt(document.getElementById('adjustQty').value);
    if (!qty || qty < 1) { showToast('Enter a valid quantity.', 'error'); return; }
    fetch(`/admin/inventory/${currentAdjustListingId}/adjust`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ type: currentAdjustType, quantity: qty, notes: document.getElementById('adjustReason').value }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) { showToast(`Stock updated. New stock: ${d.new_stock}`, 'success'); closeModal('adjustModal'); }
        else           { showToast(d.message || 'Error.', 'error'); }
    })
    .catch(() => showToast('Network error.', 'error'));
}

// ── Toggle: Solar Listing → show/hide tier dropdown ──────────
(function() {
    const toggle  = document.getElementById('toggleSolarListing');
    const wrapper = document.getElementById('solarTierWrapper');
    const select  = document.getElementById('solarTierSelect');
    const options = document.querySelectorAll('.solar-option-input');

    function syncTier() {
        if (toggle.checked) {
            wrapper.style.display = 'block';
            select.required = true;
            options.forEach(option => option.disabled = false);
        } else {
            wrapper.style.display = 'none';
            select.required = false;
            select.value = '';
            options.forEach(option => {
                option.checked = false;
                option.disabled = true;
            });
        }
    }

    toggle.addEventListener('change', syncTier);
    syncTier(); // run on page load
})();
// ── Alert Modal ───────────────────────────────────────────────
function openAlert(el) {
    currentAlertListingId = el.dataset.id;
    const threshold = el.dataset.threshold || null;
    document.getElementById('alertSku').textContent          = el.dataset.sku;
    document.getElementById('alertCurrentStock').textContent = `${el.dataset.stock} ${el.dataset.unit}`;
    document.getElementById('alertUnit').textContent         = el.dataset.unit;
    document.getElementById('alertThreshold').value          = threshold || '';
    const emailOn = el.dataset.email === '1';
    document.getElementById('alertEmailToggle').checked      = emailOn;
    document.getElementById('alertEmailTrack').style.background = emailOn ? 'var(--blue)' : '#d1d5db';
    document.getElementById('alertEmailThumb').style.transform  = emailOn ? 'translateX(18px)' : 'translateX(0)';
    document.getElementById('alertRemoveBtn').style.display  = threshold ? 'inline-flex' : 'none';
    document.getElementById('alertModal').classList.add('open');
}
function saveAlert() {
    const threshold = document.getElementById('alertThreshold').value;
    if (threshold === '') { showToast('Please enter a threshold.', 'error'); return; }
    fetch(`/admin/inventory/${currentAlertListingId}/alert`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ threshold: parseInt(threshold), email_enabled: document.getElementById('alertEmailToggle').checked }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) { showToast('Alert saved.', 'success'); closeModal('alertModal'); }
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
        if (d.success) { showToast('Alert removed.', 'success'); closeModal('alertModal'); }
    })
    .catch(() => showToast('Network error.', 'error'));
}
</script>
@endsection
