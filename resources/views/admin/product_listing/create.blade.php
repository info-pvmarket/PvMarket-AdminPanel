@extends('layouts.admin')

@section('title', 'Create New Offer')

@section('styles')
<style>
    :root {
        --or: #F97316;
        --or-lt: #FFF7ED;
        --or-d: #EA6C0A;
        --text: #1F2937;
        --muted: #6B7280;
        --border: #E5E7EB;
        --bg: #F3F4F6;
        --white: #FFFFFF;
        --green: #10B981;
        --red: #EF4444;
        --blue: #3B82F6;
        --blue-lt: #EFF6FF;
        --blue-d: #2563EB;
    }

    .create-page {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 24px;
    padding: 28px;
    background: var(--bg);
    min-height: 100vh;
    align-items: flex-start;
}

.create-main {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.create-sidebar {
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
    }

    .page-header h1 {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--text);
        margin: 0;
    }

    .page-header p {
        font-size: .8rem;
        color: var(--muted);
        margin: 2px 0 0;
    }

    /* ── Step indicator ── */
    .step-bar {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-left: auto;
    }

    .step-dot {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 2px solid var(--border);
        background: var(--white);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .7rem;
        font-weight: 700;
        color: var(--muted);
    }

    .step-dot.done   { background: var(--green);  border-color: var(--green);  color: #fff; }
    .step-dot.active { background: var(--blue);   border-color: var(--blue);   color: #fff; }
    .step-line { width: 20px; height: 2px; background: var(--border); border-radius: 2px; }

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

    .icon-orange { background: var(--or-lt);  color: var(--or); }
    .icon-green  { background: #D1FAE5;       color: #059669; }
    .icon-blue   { background: var(--blue-lt); color: var(--blue); }
    .icon-purple { background: #F5F3FF;       color: #7C3AED; }

    .section-title    { font-size: .95rem; font-weight: 700; color: var(--text); margin: 0; }
    .section-subtitle { font-size: .78rem; color: var(--muted); margin: 2px 0 0; }

    .section-body { padding: 22px; }

    /* ── Form elements ── */
    .form-row { display: grid; gap: 16px; }
    .form-row.cols-4 { grid-template-columns: repeat(4,1fr); }
    .form-row.cols-3 { grid-template-columns: repeat(3,1fr); }
    .form-row.cols-2 { grid-template-columns: repeat(2,1fr); }
    .form-row.cols-1 { grid-template-columns: 1fr; }

    .form-group { display: flex; flex-direction: column; gap: 5px; }

    .form-label {
        font-size: .8rem;
        font-weight: 600;
        color: var(--text);
    }
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

    /* ── Image upload ── */
    .upload-zone {
        border: 2px dashed var(--border);
        border-radius: 12px;
        padding: 28px 16px;
        text-align: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
    }
    .upload-zone:hover { border-color: var(--blue); background: var(--blue-lt); }

    .upload-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #F3F4F6;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        color: var(--blue);
    }

    .upload-title { font-size: .88rem; font-weight: 600; color: var(--text); margin-bottom: 4px; }
    .upload-sub   { font-size: .73rem; color: var(--muted); }

    /* ── Slots ── */
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
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--blue);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .72rem;
        font-weight: 700;
        flex-shrink: 0;
        margin-right: 10px;
    }

    .slot-price-tag { font-size: .9rem; font-weight: 700; color: var(--text); }
    .slot-price-sub { font-size: .72rem; color: var(--muted); }

    .slot-item-empty {
        padding: 18px;
        text-align: center;
        color: #9CA3AF;
        font-size: .82rem;
    }

    .btn-icon-sm {
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
        color: #9ca3af;
        font-size: .8rem;
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
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: .88rem;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 16px;
    }

    .range-badge {
        font-size: .73rem;
        color: var(--muted);
        background: #F3F4F6;
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 2px 8px;
    }

    .slot-form-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; align-items: end; }
    .slot-form-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 14px; }

    .btn-cancel {
        padding: 8px 18px;
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: .85rem;
        color: var(--text);
        cursor: pointer;
        font-family: inherit;
    }

    .btn-save-slot {
        padding: 8px 20px;
        background: var(--blue);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: .85rem;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        transition: background .15s;
    }
    .btn-save-slot:hover { background: var(--blue-d); }

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

    .toggle-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

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
        left: 2px;
        top: 2px;
        width: 20px;
        height: 20px;
        background: #fff;
        border-radius: 50%;
        transition: transform .2s;
        box-shadow: 0 1px 3px rgba(0,0,0,.2);
    }

    .toggle-switch input:checked ~ .toggle-track { background: var(--blue); }
    .toggle-switch input:checked ~ .toggle-thumb { transform: translateX(20px); }

    .toggle-switch.sold-off-switch input:checked ~ .toggle-track  { background: var(--red); }
    .toggle-switch.popular-switch input:checked ~ .toggle-track   { background: #F59E0B; }

    /* ── Sidebar cards ── */
    .summary-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
    }

    .summary-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 16px 18px;
        border-bottom: 1px solid var(--border);
    }

    .summary-header-title {
        font-size: .9rem;
        font-weight: 700;
        color: var(--blue);
    }

    .summary-header-sub {
        font-size: .73rem;
        color: var(--muted);
    }

    .summary-body { padding: 16px 18px; }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 9px 0;
        border-bottom: 1px solid #F3F4F6;
        gap: 12px;
    }
    .summary-row:last-child { border-bottom: none; }

    .summary-key { font-size: .8rem; color: var(--muted); }
    .summary-val { font-size: .82rem; font-weight: 600; color: var(--text); text-align: right; max-width: 55%; }

    .btn-publish {
        width: 100%;
        padding: 12px;
        background: var(--blue);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: .9rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 14px;
        font-family: inherit;
        transition: background .15s;
    }
    .btn-publish:hover { background: var(--blue-d); }

    .publish-note {
        text-align: center;
        font-size: .72rem;
        color: var(--muted);
        margin-top: 8px;
    }

    /* ── Alert ── */
    .alert-error {
        background: #fee2e2;
        border: 1px solid #fca5a5;
        color: #991b1b;
        padding: 12px 18px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: .85rem;
    }

    /* ── Image preview grid ── */
    .img-preview-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 12px;
    }

    .img-preview-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid var(--border);
        aspect-ratio: 1;
    }

    .img-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .img-preview-count {
        font-size: .75rem;
        color: var(--muted);
        text-align: center;
        margin-top: 8px;
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
    .summary-body .btn-publish {
    display: flex !important;
    visibility: visible !important;
}

.btn-add-slot {
    display: inline-flex !important;
    visibility: visible !important;
}

    .btn-submit-main {
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
    .btn-submit-main:hover  { background: var(--blue-d); }
    .btn-submit-main:active { transform: scale(.98); }

    /* ── Loading spinner in button ── */
    .btn-submit-main .spinner {
        display: none;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255,255,255,.4);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin .6s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Dropdown loading state ── */
    .select-loading {
        opacity: .6;
        pointer-events: none;
    }

    @media (max-width: 900px) {
        .create-page { flex-direction: column; padding: 16px; }
        .create-sidebar { width: 100%; position: static; }
        .form-row.cols-4,
        .form-row.cols-3 { grid-template-columns: 1fr 1fr; }
    }

    @media (max-width: 600px) {
        .form-row.cols-4,
        .form-row.cols-3,
        .form-row.cols-2 { grid-template-columns: 1fr; }
        .slot-form-row { grid-template-columns: 1fr 1fr; }
        .toggle-section { margin: 0 16px 16px; }
        .sticky-submit-bar { flex-direction: column; align-items: stretch; }
        .btn-submit-main { justify-content: center; }
    }

    /* ── FORCE BUTTONS VISIBLE ── */
.btn-add-slot,
.btn-publish,
.btn-save-slot,
.btn-cancel {
    display: inline-flex !important;
    visibility: visible !important;
    opacity: 1 !important;
    position: relative !important;
    overflow: visible !important;
    clip: unset !important;
    clip-path: none !important;
    transform: none !important;
    pointer-events: auto !important;
    z-index: 10 !important;
}

.slots-header {
    overflow: visible !important;
}

.section-body {
    overflow: visible !important;
}

.section-card {
    overflow: visible !important;
}

.summary-body {
    overflow: visible !important;
}
</style>
@endsection

@section('content')
<div class="create-page">

    <form method="POST" action="{{ route('product_listing.store') }}"
          id="listingForm"
          style="display:contents;"
          enctype="multipart/form-data">
        @csrf

        {{-- ══════════════════════════════
             MAIN COLUMN
        ══════════════════════════════ --}}
        <div class="create-main">

            {{-- Page header --}}
            <div>
                <div class="page-header">
                    <a href="{{ route('product_listing.index') }}" class="btn-back-circle">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div>
                        <h1>Create New Offer</h1>
                        <p>List your product for sale on the marketplace</p>
                    </div>
                    <div class="step-bar">
                        <div class="step-dot active">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                            </svg>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-dot">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
                            </svg>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-dot">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-dot">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span style="font-size:.72rem; color:var(--muted); margin-left:4px;">0/4</span>
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

            {{-- ── Product Details ── --}}
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon icon-blue">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="section-title" style="color:var(--blue);">Product Details</p>
                        <p class="section-subtitle">Select the product you want to list</p>
                    </div>
                </div>
                <div class="section-body">
                    <div class="form-row cols-3">
                        {{-- Main Category --}}
                        <div class="form-group">
                            <label class="form-label">Main Category <span class="req">*</span></label>
                            <select name="main_category_id" class="form-select" id="mainCatSelect" required>
                                <option value="">Select main category</option>
                                @foreach($mainCategories as $cat)
    <option value="{{ $cat->id }}" {{ old('main_category_id') == $cat->id ? 'selected' : '' }}>
        {{ $cat->category_name }}
    </option>
@endforeach
                            </select>
                            @error('main_category_id')<div class="error-msg">{{ $message }}</div>@enderror
                        </div>

                        {{-- Sub Category (dynamically loaded) --}}
                        <div class="form-group">
                            <label class="form-label">Sub Category <span class="req">*</span></label>
                            <select name="sub_category_id" class="form-select" id="subCatSelect" required>
                                <option value="">Select sub category</option>
                                @foreach($subCategories as $sub)
    <option value="{{ $sub->id }}" {{ old('sub_category_id') == $sub->id ? 'selected' : '' }}>
        {{ $sub->sub_category_name }}
    </option>
@endforeach
                            </select>
                            @error('sub_category_id')<div class="error-msg">{{ $message }}</div>@enderror
                        </div>

                        {{-- Product (dynamically loaded) --}}
                        <div class="form-group">
                            <label class="form-label">Product <span class="req">*</span></label>
                            <select name="product_id" class="form-select" id="productSelect" required>
                                <option value="">Select product</option>
                                @foreach($products as $product)
    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
        {{ $product->product_name }}
    </option>
@endforeach
                            </select>
                            @error('product_id')<div class="error-msg">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Warehouse (dynamically loaded) --}}
                    <div class="form-row cols-1" style="margin-top:16px;">
                        <div class="form-group">
                            <label class="form-label">Select Warehouse <span class="req">*</span></label>
                            <select name="warehouse_id" class="form-select" id="warehouseSelect" required>
                                <option value="">Choose a warehouse</option>
                                @foreach($warehouses as $wh)
    <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
        {{ $wh->warehouse_name }} — {{ $wh->city }}, {{ $wh->country }}
    </option>
@endforeach
                            </select>
                            @error('warehouse_id')<div class="error-msg">{{ $message }}</div>@enderror
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
                        <p class="section-title" style="color:var(--blue);">Offer Settings</p>
                        <p class="section-subtitle">Configure how you want to sell</p>
                    </div>
                </div>
                <div class="section-body">
                    <div class="form-row cols-3">
                        <div class="form-group">
                            <label class="form-label">Sell Type <span class="req">*</span></label>
                            <select name="sell_type" class="form-select" id="sellTypeSelect" required>
                                <option value="">Select sell type</option>
                                @foreach($sellTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('sell_type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sell_type')<div class="error-msg">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Currency <span class="req">*</span></label>
                            <select name="currency_id" class="form-select" id="currencySelect" required>
                                <option value="">Select currency</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency['code'] }}" {{ old('currency_id') == $currency['code'] ? 'selected' : '' }}>
                                        {{ $currency['code'] }}{{ $currency['symbol'] !== '' ? ' (' . $currency['symbol'] . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('currency_id')<div class="error-msg">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Promotion</label>
                            <select name="discount_type" class="form-select">
                                @foreach($discountTypes as $dt)
                                    <option value="{{ $dt }}" {{ old('discount_type') == $dt ? 'selected' : '' }}>
                                        {{ $dt }}
                                    </option>
                                @endforeach
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

                {{-- ══ Three Toggle Rows ══ --}}
                <div class="toggle-section">

                    {{-- 1. Hold this offer --}}
                    <div class="toggle-row">
                        <div class="toggle-left">
                            <div class="toggle-icon icon-green">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <div class="toggle-info-title">Hold this offer</div>
                                <div class="toggle-info-sub" id="holdSubLabel">Offer is active and visible to buyers</div>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_on_hold" value="1" id="toggleIsActive">
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
                            <input type="checkbox" name="is_sold_off" value="1" id="toggleSoldOff">
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
                            <input type="checkbox" name="is_popular" value="1" id="togglePopular">
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
                            <input type="checkbox" name="real_time_price" value="1" id="toggleRealTimePrice">
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
                $selectedSolarGridTypes = old('solar_grid_types', []);
                $selectedSolarPhaseTypes = old('solar_phase_types', []);
                $selectedSolarGridTypes = is_array($selectedSolarGridTypes) ? $selectedSolarGridTypes : [$selectedSolarGridTypes];
                $selectedSolarPhaseTypes = is_array($selectedSolarPhaseTypes) ? $selectedSolarPhaseTypes : [$selectedSolarPhaseTypes];
            @endphp
            <select name="solar_tier" id="solarTierSelect"
                    style="padding:6px 12px; border:1px solid var(--border); border-radius:8px;
                           font-size:.82rem; color:var(--text); background:#fff;
                           font-family:inherit; outline:none; cursor:pointer;
                           transition:border-color .15s;">
                <option value="">Select Tier</option>
                <option value="premium"     {{-- edit only: {{ ($listing->solar_tier ?? '') === 'premium'     ? 'selected' : '' }} --}}>⭐ Premium</option>
                <option value="recommended" {{-- edit only: {{ ($listing->solar_tier ?? '') === 'recommended' ? 'selected' : '' }} --}}>✅ Recommended</option>
                <option value="value"       {{-- edit only: {{ ($listing->solar_tier ?? '') === 'value'       ? 'selected' : '' }} --}}>💰 Value</option>
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
                   {{-- edit only: {{ !empty($listing->is_solar_listing) ? 'checked' : '' }} --}}>
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
                            <circle cx="12" cy="12" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
                        </svg>
                    </div>
                    <div>
                        <p class="section-title" style="color:var(--blue);">Inventory &amp; Pricing</p>
                        <p class="section-subtitle">Set quantity, lead time, and price tiers</p>
                    </div>
                </div>
                <div class="section-body">

                    {{-- Warehouse pill (dynamic) --}}
                    <div class="warehouse-pill" id="warehousePill" style="{{ old('warehouse_id') ? '' : 'display:none;' }}">
                        <svg width="15" height="15" fill="none" stroke="#6B7280" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5V19a1 1 0 001 1h16a1 1 0 001-1v-8.5M3 10.5L12 4l9 6.5M3 10.5h18"/>
                        </svg>
                        <span id="warehousePillName">—</span>
                    </div>

                    <div class="form-row cols-3">
                        <div class="form-group">
                            <label class="form-label">Total Quantity <span class="req">*</span></label>
                            <div class="input-suffix">
                                <input type="number" name="total_quantity" class="form-control"
                                       value="{{ old('total_quantity') }}" min="1" required placeholder="e.g. 4500"
                                       id="totalQtyInput">
                                <span class="suffix-label" id="totalQtyUnit">pcs</span>
                            </div>
                            @error('total_quantity')<div class="error-msg">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lead Time <span class="req">*</span></label>
                            <div class="input-suffix">
                                <input type="number" name="lead_time" class="form-control"
                                       value="{{ old('lead_time') }}" min="1" required placeholder="e.g. 10">
                                <span class="suffix-label">weeks</span>
                            </div>
                            @error('lead_time')<div class="error-msg">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Incoterm <span class="req">*</span></label>
                            <select name="incoterm_id" class="form-select" required>
    <option value="">Select incoterm</option>
    @foreach($incoterms as $incoterm)
        <option value="{{ $incoterm->id }}" {{ old('incoterm_id') == $incoterm->id ? 'selected' : '' }}>
            {{ $incoterm->name }}
        </option>
    @endforeach
</select>
                            @error('incoterm')<div class="error-msg">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Price Tiers --}}
                    <div style="margin-top: 28px;">
                        <div class="slots-header">
                            <div>
                                <div class="slots-label">Price Tiers</div>
                                <div class="slots-hint">Set different prices based on order quantity</div>
                            </div>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span style="font-size:.75rem; color:var(--muted);">+ Add Tier &nbsp;<span id="tierFraction">0/3</span></span>
                                <button type="button" class="btn-add-slot" onclick="showSlotForm()">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add Tier
                                </button>
                            </div>
                        </div>

                        <div class="slot-list-box" id="slotList">
                            <div class="slot-item-empty" id="slotEmptyMsg">No tiers added yet. Click "Add Tier" to create one.</div>
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
                            <div class="slot-form-row" style="grid-template-columns: 1fr 1fr 1fr 1fr; gap:12px; align-items: flex-start">
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
                                <div class="form-group" style="justify-content: flex-start;">
    <label class="form-label">Commission % <span class="req">*</span></label>
    <div class="input-suffix">
        <input type="number" id="slotCommission" class="form-control"
               placeholder="Auto" step="0.01" min="0"
               disabled
               style="border-radius:9px 0 0 9px; border-right:none;"
               oninput="recalcTotalPrice()">
        <span class="suffix-label">%</span>
    </div>
    <span style="font-size:.7rem; color:var(--muted); margin-top:3px; min-height:16px; display:block;" id="commissionSourceLabel"></span>
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
                </div>

                </div>

            <div style="display:flex; justify-content:flex-end;">
                <button type="submit" form="listingForm" style="padding:12px 28px; background:#1F2937; color:white; border:none; border-radius:10px; font-size:15px; font-weight:700; cursor:pointer; font-family:inherit;">
                    Submit Offer
                </button>
            </div>

        </div>{{-- end .create-main --}}

        {{-- ══════════════════════════════
             SIDEBAR
        ══════════════════════════════ --}}
        <div class="create-sidebar">

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
                        <div class="summary-header-sub">Upload up to 5 photos</div>
                    </div>
                </div>
                <div style="padding:16px 18px;">

                    {{-- Preview of selected images --}}
                    <div id="newImagesPreview" class="img-preview-grid" style="display:none;"></div>
                    <p id="imgCountLabel" class="img-preview-count" style="display:none;"></p>

                    {{-- Upload zone --}}
                    <div class="upload-zone" id="uploadZone" onclick="document.getElementById('imgInput').click()">
                        <div class="upload-icon">
                            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                        </div>
                        <div class="upload-title" id="uploadTitle">Drag &amp; drop images</div>
                        <div class="upload-sub">or click to browse<br>JPEG, PNG, WebP · Max 5MB each</div>
                        <input type="file" id="imgInput" name="images[]" multiple accept="image/*"
                               style="display:none;" onchange="previewNewImages(this)">
                    </div>

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
                        <div class="summary-header-sub">Review before publishing</div>
                    </div>
                </div>
                <div class="summary-body">
                    <div class="summary-row">
                        <span class="summary-key">Product</span>
                        <span class="summary-val" id="summProduct">Not selected</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Warehouse</span>
                        <span class="summary-val" id="summWarehouse">Not selected</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Sell Type</span>
                        <span class="summary-val" id="summSellType">Not set</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Currency</span>
                        <span class="summary-val" id="summCurrency">Not set</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Quantity</span>
                        <span class="summary-val" id="summQty">—</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Price Tiers</span>
                        <span class="summary-val" id="summTiers">0 tiers</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Images</span>
                        <span class="summary-val" id="summImages">None</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Status</span>
                        <span class="summary-val" id="summStatus" style="color:var(--green);">Active</span>
                    </div>

                    {{-- Sidebar publish button (submits same form) --}}
                    <button type="submit" form="listingForm" class="btn-publish" style="background:var(--or); margin-top:14px;">
                        Publish Offer
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <p class="publish-note">Your offer will be reviewed before going live</p>
                </div>
            </div>

        </div>

    </form>
</div>
@endsection

@section('scripts')
<script>
const COMMISSIONS = @json($commissionsJson);

// ── Auto-populate commission from selected category ───────────
function loadCommissionForCategory() {
    const mainCatSelect = document.getElementById('mainCatSelect');
    if (!mainCatSelect) return;
    const selectedCatId = mainCatSelect.value;
    if (!selectedCatId) return;
    const match = COMMISSIONS.find(c => c.category_id === selectedCatId);
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

// ── Recalculate total price whenever price or commission changes ──
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

let slots        = [];
let editingIndex = null;

// ── Cascade dropdowns ──────────────────────────────────────────
document.getElementById('mainCatSelect').addEventListener('change', function () {
    const mainCatId   = this.value;
    const subSelect   = document.getElementById('subCatSelect');
    const prodSelect  = document.getElementById('productSelect');

    subSelect.innerHTML  = '<option value="">Loading...</option>';
    prodSelect.innerHTML = '<option value="">Select product</option>';
    subSelect.classList.add('select-loading');

    if (!mainCatId) {
        subSelect.innerHTML = '<option value="">Select sub category</option>';
        subSelect.classList.remove('select-loading');
        return;
    }

    fetch(`/api/subcategories/${mainCatId}`)
    .then(r => r.json())
    .then(data => {
        subSelect.innerHTML = '<option value="">Select sub category</option>';
        data.forEach(sub => {
    const id = sub._id?.$oid ?? sub._id ?? sub.id;
    subSelect.innerHTML += `<option value="${id}">${sub.sub_category_name ?? sub.name}</option>`;
});
        subSelect.classList.remove('select-loading');
        prodSelect.innerHTML = '<option value="">Select product</option>';
    })
    .catch(() => {
        subSelect.innerHTML = '<option value="">Error loading</option>';
        subSelect.classList.remove('select-loading');
    });

    loadCommissionForCategory();
});

document.getElementById('subCatSelect').addEventListener('change', function () {
    const subCatId   = this.value;
    const prodSelect = document.getElementById('productSelect');

    prodSelect.innerHTML = '<option value="">Loading...</option>';
    prodSelect.classList.add('select-loading');

    if (!subCatId) {
        prodSelect.innerHTML = '<option value="">Select product</option>';
        prodSelect.classList.remove('select-loading');
        return;
    }

   console.log('Fetching products for subCatId:', subCatId);
fetch(`/api/products/${subCatId}`)
    .then(r => r.json())
    .then(data => {
        prodSelect.innerHTML = '<option value="">Select product</option>';
        data.forEach(p => {
    const id = p._id?.$oid ?? p._id ?? p.id;
    prodSelect.innerHTML += `<option value="${id}">${p.product_name}</option>`;
});
        prodSelect.classList.remove('select-loading');
    })
    .catch(() => {
        prodSelect.innerHTML = '<option value="">Error loading</option>';
        prodSelect.classList.remove('select-loading');
    });
});

// ── Product select → summary ──────────────────────────────────
document.getElementById('productSelect').addEventListener('change', function () {
    document.getElementById('summProduct').textContent =
        this.value ? this.options[this.selectedIndex].text : 'Not selected';
});

// ── Warehouse pill + summary ──────────────────────────────────
document.getElementById('warehouseSelect').addEventListener('change', function () {
    const text     = this.options[this.selectedIndex].text;
    const pill     = document.getElementById('warehousePill');
    const pillName = document.getElementById('warehousePillName');
    if (this.value) {
        pillName.textContent = text;
        pill.style.display   = 'flex';
        document.getElementById('summWarehouse').textContent = text;
        document.getElementById('summProduct').textContent =
            document.getElementById('productSelect').options[document.getElementById('productSelect').selectedIndex]?.text || 'Not selected';
    } else {
        pill.style.display = 'none';
        document.getElementById('summWarehouse').textContent = 'Not selected';
    }
});

document.getElementById('productSelect').addEventListener('change', function () {
    document.getElementById('summProduct').textContent =
        this.value ? this.options[this.selectedIndex].text : 'Not selected';
});

// ── Live summary ─────────────────────────────────────────────
document.getElementById('sellTypeSelect').addEventListener('change', function () {
    document.getElementById('summSellType').textContent =
        this.value ? this.options[this.selectedIndex].text : 'Not set';
    document.getElementById('totalQtyUnit').textContent = {
        'sell by pieces': 'pcs',
        'sell by pallets': 'pallets',
        'sell by container': 'container',
    }[this.value] || 'pcs';
});

document.getElementById('sellTypeSelect').dispatchEvent(new Event('change'));

document.getElementById('currencySelect').addEventListener('change', function () {
    document.getElementById('summCurrency').textContent = this.value || 'Not set';
});

document.getElementById('totalQtyInput').addEventListener('input', function () {
    document.getElementById('summQty').textContent =
        this.value ? Number(this.value).toLocaleString() + ' pcs' : '—';
    updateStickyLabel();
});

function syncOfferStatusSummary() {
    const summStatus = document.getElementById('summStatus');
    const holdSub = document.getElementById('holdSubLabel');
    const isOnHold = document.getElementById('toggleIsActive').checked;
    const isSoldOff = document.getElementById('toggleSoldOff').checked;

    holdSub.textContent = isOnHold
        ? 'Offer is on hold and hidden from buyers'
        : 'Offer is active and visible to buyers';

    if (isSoldOff) {
        summStatus.textContent = 'Sold Off';
        summStatus.style.color = 'var(--red)';
    } else if (isOnHold) {
        summStatus.textContent = 'On Hold';
        summStatus.style.color = 'var(--muted)';
    } else {
        summStatus.textContent = 'Active';
        summStatus.style.color = 'var(--green)';
    }
}

// ── Toggle: Hold this offer → update Status in summary ───────
document.getElementById('toggleIsActive').addEventListener('change', function () {
    syncOfferStatusSummary();
});

// ── Toggle: Sold Off ─────────────────────────────────────────
document.getElementById('toggleSoldOff').addEventListener('change', function () {
    const popularToggle = document.getElementById('togglePopular');

    if (this.checked) {
        popularToggle.checked = false;
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

// ── Image preview ─────────────────────────────────────────────
function previewNewImages(input) {
    const preview    = document.getElementById('newImagesPreview');
    const countLabel = document.getElementById('imgCountLabel');
    const uploadTitle = document.getElementById('uploadTitle');
    const files      = Array.from(input.files).slice(0, 5);

    preview.innerHTML = '';

    if (files.length === 0) {
        preview.style.display  = 'none';
        countLabel.style.display = 'none';
        uploadTitle.textContent = 'Drag & drop images';
        document.getElementById('summImages').textContent = 'None';
        return;
    }

    files.forEach(file => {
        const url = URL.createObjectURL(file);
        const div = document.createElement('div');
        div.className = 'img-preview-item';
        div.innerHTML = `<img src="${url}" alt="preview">`;
        preview.appendChild(div);
    });

    preview.style.display   = 'grid';
    countLabel.style.display = 'block';
    countLabel.textContent   = files.length + ' image' + (files.length > 1 ? 's' : '') + ' selected';
    uploadTitle.textContent  = 'Change images';
    document.getElementById('summImages').textContent = files.length + ' image' + (files.length > 1 ? 's' : '');
}

// ── Sticky bar label updater ──────────────────────────────────
function updateStickyLabel() {
    const product  = document.getElementById('productSelect').value;
    const qty      = document.getElementById('totalQtyInput').value;
    const label    = document.getElementById('stickyStatusLabel');

    if (!product) {
        label.textContent = 'Select a product to continue.';
    } else if (!qty) {
        label.textContent = 'Add quantity to continue.';
    } else {
        label.textContent = 'Everything looks good — ready to publish!';
    }
}

document.getElementById('productSelect').addEventListener('change', updateStickyLabel);
document.getElementById('totalQtyInput').addEventListener('input', updateStickyLabel);

// ── Submit handler (shows spinner) ───────────────────────────
document.getElementById('listingForm').addEventListener('submit', function (event) {
    if (!validatePriceTiersAgainstTotalQuantity()) {
        event.preventDefault();
        return;
    }

    const spinner = document.getElementById('submitSpinner');
    if (spinner) spinner.style.display = 'inline-block';
    document.querySelectorAll('.btn-publish, .btn-submit-main').forEach(b => b.disabled = true);
});

function handleSubmit(btn) {}

function validatePriceTiersAgainstTotalQuantity() {
    const totalQuantity = Number(document.getElementById('totalQtyInput').value);
    const errorElement = document.getElementById('tierQuantityError');
    const hasInvalidTier = Number.isFinite(totalQuantity) && slots.some(slot =>
        Number(slot.min_quantity) > totalQuantity ||
        (slot.max_quantity !== null && Number(slot.max_quantity) > totalQuantity)
    );

    if (hasInvalidTier) {
        errorElement.textContent = 'Each price tier quantity must be less than or equal to the total quantity.';
        errorElement.style.display = 'block';
        errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }

    errorElement.textContent = '';
    errorElement.style.display = 'none';
    return true;
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

function saveSlot() {
    const minQty  = parseInt(document.getElementById('slotMinQty').value);
    const price   = parseFloat(document.getElementById('slotPrice').value);
    const maxType = document.querySelector('input[name="maxQtyType"]:checked').value;
    const maxQty  = maxType === 'specific' ? parseInt(document.getElementById('slotMaxQty').value) : null;
    const errEl   = document.getElementById('slotError');

    if (isNaN(minQty) || minQty < 0) {
        errEl.textContent = 'Please enter a valid minimum quantity.';
        errEl.style.display = 'block'; return;
    }
    if (isNaN(price) || price < 0) {
        errEl.textContent = 'Please enter a valid price.';
        errEl.style.display = 'block'; return;
    }
    if (maxType === 'specific' && (isNaN(maxQty) || maxQty <= minQty)) {
        errEl.textContent = 'Max quantity must be greater than minimum.';
        errEl.style.display = 'block'; return;
    }
    const totalQuantity = Number(document.getElementById('totalQtyInput').value);
    if (Number.isFinite(totalQuantity) &&
        (minQty > totalQuantity || (maxQty !== null && maxQty > totalQuantity))) {
        errEl.textContent = 'Each price tier quantity must be less than or equal to the total quantity.';
        errEl.style.display = 'block'; return;
    }

    const slot = { min_quantity: minQty, max_quantity: maxQty, price };
    if (editingIndex !== null) slots[editingIndex] = slot;
    else slots.push(slot);

    renderSlots();
    cancelSlot();
}

function editSlot(idx) {
    const s = slots[idx];
    showSlotForm();
    document.getElementById('slotMinQty').value = s.min_quantity;
    document.getElementById('slotPrice').value  = s.price;
    if (s.max_quantity !== null) {
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
        document.getElementById('summTiers').textContent = '0 tiers';
        document.getElementById('tierFraction').textContent = '0/3';
        return;
    }

    slots.forEach((slot, i) => {
        const maxLabel = slot.max_quantity !== null
            ? `${slot.max_quantity.toLocaleString()} pcs`
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
                <div class="slot-price-tag">${slot.price.toFixed(2)}</div>
                <div class="slot-price-sub">per pc</div>
            </div>
            <div style="display:flex;gap:4px;">
                <button type="button" class="btn-icon-sm" onclick="editSlot(${i})" title="Edit">✏️</button>
                <button type="button" class="btn-icon-sm" onclick="deleteSlot(${i})" title="Delete">🗑️</button>
            </div>
        `;
        listEl.appendChild(row);

        inputsEl.innerHTML += `
            <input type="hidden" name="slots[${i}][min_quantity]" value="${slot.min_quantity}">
            <input type="hidden" name="slots[${i}][max_quantity]" value="${slot.max_quantity ?? ''}">
            <input type="hidden" name="slots[${i}][price]" value="${slot.price}">
        `;
    });

    const count = slots.length;
    document.getElementById('summTiers').textContent     = count + (count === 1 ? ' tier' : ' tiers');
    document.getElementById('tierFraction').textContent  = count + '/3';
}
</script>
@endsection
