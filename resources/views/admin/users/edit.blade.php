@extends('layouts.admin')

@section('title', 'Update User Details')

@section('styles')
<style>
    /* ── Page outer header ── */
    .page-outer-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 0;
    }

    .page-outer-header h1 {
        font-size: 22px;
        font-weight: 800;
        color: var(--text);
        padding-top: 4px;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 18px;
        background: var(--text);
        color: white;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        transition: background .15s;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .btn-back:hover { background: #334155; }

    /* ── Main card ── */
    .user-edit-card {
        background: white;
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
        margin-top: 20px;
    }

    /* ── Card top: avatar + company verified ── */
    .card-top {
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 28px 28px 0;
        position: relative;
        min-height: 140px;
        border-bottom: 1px solid var(--border);
    }

    /* Company verified — left */
    .company-verified-wrap {
        position: absolute;
        left: 28px;
        top: 28px;
        text-align: center;
    }

    .company-verified-label {
        font-size: 13.5px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 8px;
    }

    .verified-toggle-btn {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 22px;
        padding: 4px;
        border-radius: 6px;
        transition: transform .15s, background .15s;
        display: block;
        margin: 0 auto;
    }

    .verified-toggle-btn:hover { transform: scale(1.15); background: var(--light); }

    /* Avatar center */
    .avatar-center {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding-bottom: 16px;
    }

    .avatar-ring {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        padding: 4px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        box-shadow: 0 4px 16px rgba(102,126,234,.3);
    }

    .avatar-inner {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        border: 3px solid white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 800;
        color: white;
        overflow: hidden;
    }

    .avatar-inner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .avatar-name {
        font-size: 18px;
        font-weight: 800;
        color: var(--text);
    }

    .avatar-email {
        font-size: 13px;
        color: var(--muted);
        font-weight: 500;
    }

    /* ── Tabs ── */
    .tabs-nav {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        padding: 0 20px;
        background: #F0F9FF;
        border-bottom: 2px solid var(--border);
        overflow-x: auto;
    }

    .tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 14px 20px;
        font-family: inherit;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--muted);
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        cursor: pointer;
        white-space: nowrap;
        transition: all .15s;
    }

    .tab-btn svg { width: 16px; height: 16px; }
    .tab-btn:hover { color: var(--primary-d); }

    .tab-btn.active {
        color: var(--primary-d);
        border-bottom-color: var(--primary-d);
        background: white;
    }

    /* ── Tab content ── */
    .tab-content { display: none; padding: 28px; }
    .tab-content.active { display: block; }

    /* ── Form section title ── */
    .section-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 20px;
    }

    /* ── Form layout ── */
    .form-row {
        display: flex;
        gap: 24px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .form-col { flex: 1; min-width: 200px; display: flex; flex-direction: column; gap: 6px; }
    .form-col-fixed { width: 320px; flex-shrink: 0; display: flex; flex-direction: column; gap: 6px; }

    .form-label { font-size: 13px; font-weight: 600; color: var(--text); }

    .form-input {
        width: 100%;
        padding: 9px 13px;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        font-family: inherit;
        font-size: 13.5px;
        color: var(--text);
        outline: none;
        transition: border-color .2s, box-shadow .2s;
        background: white;
    }

    .form-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(14,165,233,.1);
    }

    .form-input::placeholder { color: #CBD5E1; }
    .form-input:disabled { background: var(--light); color: var(--muted); }

    /* Checkboxes row */
    .checkbox-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 24px;
        margin-bottom: 20px;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13.5px;
        font-weight: 500;
        color: var(--text);
        cursor: pointer;
    }

    .checkbox-item input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: var(--primary);
    }

    /* Save button */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        padding-top: 16px;
        border-top: 1px solid var(--border);
        margin-top: 8px;
    }

    .btn-save {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 28px;
        background: #10B981;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
        transition: background .15s, box-shadow .2s;
    }

    .btn-save:hover { background: #059669; box-shadow: 0 4px 14px rgba(16,185,129,.35); }

    /* Placeholder tabs */
    .tab-placeholder {
        text-align: center;
        padding: 48px 20px;
        color: var(--muted);
    }

    .tab-placeholder svg { width: 44px; height: 44px; margin: 0 auto 12px; opacity:.15; display:block; }
    .tab-placeholder p { font-size: 14px; font-weight: 500; }

    /* Alert */
    .alert-success {
        padding: 12px 16px; background: #D1FAE5; color: #065F46;
        border: 1px solid #A7F3D0; border-radius: 8px; font-size: 13.5px;
        font-weight: 500; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
    }

    .alert-error {
        padding: 12px 16px; background: #FEE2E2; color: #991B1B;
        border: 1px solid #FECACA; border-radius: 8px; font-size: 13.5px;
        font-weight: 500; margin-bottom: 20px;
    }

    /* ── Data tables ── */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .data-table th {
        background: #F8FAFC;
        padding: 12px 14px;
        text-align: left;
        font-weight: 700;
        color: var(--text);
        border-bottom: 2px solid var(--border);
        white-space: nowrap;
    }

    .data-table td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }

    .data-table tr:hover { background: #F8FAFC; }

    .data-table .product-thumb {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid var(--border);
    }

    .data-table .product-name {
        font-weight: 600;
        color: var(--text);
    }

    /* ── Listing Card Styles (matching manage-listings) ── */
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

    .listing-thumb-wrap { position: relative; flex-shrink: 0; }

    .badge-paid {
        position: absolute;
        top: 6px; left: 6px;
        background: #3B82F6;
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

    .status-badge-card {
        display: block;
        text-align: center;
        margin-top: 8px;
        padding: 3px 0;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        border: 1.5px solid;
    }
    .status-badge-card.active   { color: #16a34a; border-color: #16a34a; }
    .status-badge-card.inactive { color: var(--primary); border-color: var(--primary); }

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

    .listing-actions { display: flex; gap: 8px; flex-shrink: 0; }

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

    .listing-tags { display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap; }

    .tag {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 20px;
    }
    .tag-popular { background: #FEF3C7; color: #D97706; }
    .tag-soldoff { background: #FEE2E2; color: #EF4444; }
    .tag-pending { background: #FEF3C7; color: #92400E; }
    .tag-approved { background: #D1FAE5; color: #065F46; }
    .tag-rejected { background: #FEE2E2; color: #991B1B; }
    .tag-realtime { background: #DBEAFE; color: #1D4ED8; }

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

    .slot-row-detail {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        border-bottom: 1px solid #F3F4F6;
    }
    .slot-row-detail:last-child { border-bottom: none; }
    .slot-price { color: var(--primary); font-weight: 700; }

    /* Filter pills */
    .filter-bar-inline {
        background: white;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 14px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }
    .filter-bar-left  { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .filter-bar-right { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }

    .filter-group { display: flex; flex-direction: column; gap: 6px; }

    .filter-group-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .filter-group-pills { display: flex; gap: 6px; flex-wrap: wrap; }

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

    /* Grid view for listings */
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

    .empty-card {
        background: white;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 70px 24px;
        text-align: center;
    }
    .empty-icon { font-size: 52px; margin-bottom: 12px; }
    .empty-text { font-size: 15px; color: #9CA3AF; font-weight: 500; margin-bottom: 20px; }

    /* Badge styles */
    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .badge-success { background: #D1FAE5; color: #065F46; }
    .badge-warning { background: #FEF3C7; color: #92400E; }
    .badge-danger { background: #FEE2E2; color: #991B1B; }
    .badge-info { background: #DBEAFE; color: #1E40AF; }
    .badge-secondary { background: #F1F5F9; color: #475569; }

    /* Action buttons */
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        border: none;
        transition: all .15s;
    }

    .btn-action-success { background: #10B981; color: white; }
    .btn-action-success:hover { background: #059669; }

    .btn-action-danger { background: #EF4444; color: white; }
    .btn-action-danger:hover { background: #DC2626; }

    .btn-action-warning { background: #F59E0B; color: white; }
    .btn-action-warning:hover { background: #D97706; }

    .btn-action-info { background: #3B82F6; color: white; }
    .btn-action-info:hover { background: #2563EB; }

    .btn-action-secondary { background: #64748B; color: white; }
    .btn-action-secondary:hover { background: #475569; }

    /* Filter bar */
    .filter-bar {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 20px;
        padding: 16px;
        background: #F8FAFC;
        border-radius: 10px;
        border: 1px solid var(--border);
    }

    .filter-bar label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text);
    }

    .filter-bar select {
        padding: 8px 12px;
        border: 1.5px solid var(--border);
        border-radius: 6px;
        font-family: inherit;
        font-size: 13px;
        background: white;
        min-width: 140px;
    }

    .filter-bar .btn-filter {
        padding: 8px 16px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }

    /* Document card */
    .document-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: 10px;
        margin-bottom: 12px;
    }

    .document-info {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .document-icon {
        width: 48px;
        height: 48px;
        background: #DBEAFE;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2563EB;
    }

    .document-details h4 {
        font-size: 14px;
        font-weight: 700;
        color: var(--text);
        margin: 0 0 4px;
    }

    .document-details p {
        font-size: 12px;
        color: var(--muted);
        margin: 0;
    }

    .document-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Slots accordion */
    .slots-container {
        margin-top: 8px;
    }

    .slot-row {
        display: flex;
        gap: 16px;
        padding: 8px 12px;
        background: #F1F5F9;
        border-radius: 6px;
        margin-bottom: 6px;
        font-size: 12px;
    }

    .slot-row span {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .slot-label {
        font-weight: 600;
        color: var(--muted);
    }

    /* Stats summary */
    .stats-summary {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }

    .stat-card {
        flex: 1;
        padding: 16px 20px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 10px;
        text-align: center;
    }

    .stat-card .stat-value {
        font-size: 28px;
        font-weight: 800;
        color: var(--primary-d);
    }

    .stat-card .stat-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
    }
</style>
@endsection

@section('content')

@php
    // Determine active tab from request or session
    $activeTab = request('active_tab') ?? session('active_tab', 'basic');
@endphp

{{-- ── Outer header ── --}}
<div class="page-outer-header">
    <h1>Update {{ ucfirst($user->user_type ?? 'User') }} Details</h1>
    <a href="{{ route('admin.users.index') }}" class="btn-back">← Back</a>
</div>

{{-- Global success/error messages --}}
@if(session('success'))
    <div class="alert-success" style="margin-top:16px;">✓ {{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert-error" style="margin-top:16px;">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert-error" style="margin-top:16px;">{{ $errors->first() }}</div>
@endif

{{-- ── Main edit card ── --}}
<div class="user-edit-card">

    {{-- Top: avatar + company verified --}}
    <div class="card-top">

        {{-- Company Verified toggle (left) --}}
        <div class="company-verified-wrap">
            <div class="company-verified-label">Company Verified</div>
            <form method="POST" action="{{ route('admin.users.toggle-verified', $user->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="active_tab" value="{{ $activeTab }}">
                <button type="submit" class="verified-toggle-btn"
                        title="{{ ($user->company_verified ?? false) ? 'Click to unverify' : 'Click to verify' }}">
                    @if($user->company_verified ?? false)
                        <span style="color:#10B981; font-size:26px;">✓</span>
                    @else
                        <span style="color:#F97316; font-size:26px;">✗</span>
                    @endif
                </button>
            </form>
        </div>

        {{-- Avatar center --}}
        <div class="avatar-center">
            <div class="avatar-ring">
                <div class="avatar-inner">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}"/>
                    @else
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    @endif
                </div>
            </div>
            <div class="avatar-name">{{ $user->name }}</div>
            <div class="avatar-email">({{ $user->email }})</div>
        </div>

    </div>

    {{-- ── Tabs navigation ── --}}
    <div class="tabs-nav" id="tabsNav">

        <button class="tab-btn {{ $activeTab === 'basic' ? 'active' : '' }}"
                onclick="switchTab('basic', this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            Basic Details
        </button>

        <button class="tab-btn {{ $activeTab === 'company' ? 'active' : '' }}"
                onclick="switchTab('company', this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Company Details
        </button>

        <button class="tab-btn {{ $activeTab === 'documents' ? 'active' : '' }}"
                onclick="switchTab('documents', this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            Documents
            @if(count($documents) > 0)
                <span class="badge badge-info" style="margin-left:4px;">{{ count($documents) }}</span>
            @endif
        </button>

        <button class="tab-btn {{ $activeTab === 'listings' ? 'active' : '' }}"
                onclick="switchTab('listings', this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <line x1="3" y1="9" x2="21" y2="9"/>
                <line x1="9" y1="21" x2="9" y2="9"/>
            </svg>
            Listings
            @if($listings->count() > 0)
                <span class="badge badge-info" style="margin-left:4px;">{{ $listings->count() }}</span>
            @endif
        </button>

        <button class="tab-btn {{ $activeTab === 'purchases' ? 'active' : '' }}"
                onclick="switchTab('purchases', this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
            Purchases
            @if($purchases->count() > 0)
                <span class="badge badge-info" style="margin-left:4px;">{{ $purchases->count() }}</span>
            @endif
        </button>

        <button class="tab-btn {{ $activeTab === 'sales' ? 'active' : '' }}"
                onclick="switchTab('sales', this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            Sales
            @if($sales->count() > 0)
                <span class="badge badge-info" style="margin-left:4px;">{{ $sales->count() }}</span>
            @endif
        </button>

    </div>

    {{-- ══════════════════════════
         TAB: Basic Details
    ══════════════════════════ --}}
    <div class="tab-content {{ $activeTab === 'basic' ? 'active' : '' }}"
         id="tab-basic">

        <form method="POST" action="{{ route('admin.users.update-basic', $user->id) }}">
            @csrf @method('PUT')

            <div class="form-row">
                <div class="form-col">
                    <label class="form-label">Email:</label>
                    <input type="email" name="email" class="form-input"
                           value="{{ old('email', $user->email) }}" required/>
                </div>
                <div class="form-col">
                    <label class="form-label">Name:</label>
                    <input type="text" name="name" class="form-input"
       value="{{ old('name', lang($user, 'name')) }}" required/>
                </div>
                <div class="form-col">
                    <label class="form-label">Mobile:</label>
                    <input type="text" name="mobile" class="form-input"
                           placeholder="+92 - XXXXXXXXXX"
                           value="{{ old('mobile', $user->mobile ?? '') }}"/>
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

    {{-- ══════════════════════════
         TAB: Company Details
    ══════════════════════════ --}}
    <div class="tab-content {{ $activeTab === 'company' ? 'active' : '' }}"
         id="tab-company">

        <div class="section-title">Company Details</div>

        <form method="POST" action="{{ route('admin.users.update-company', $user->id) }}">
            @csrf @method('PUT')

            {{-- Checkboxes row --}}
            <div class="checkbox-row">
                <label class="checkbox-item">
                    <input type="checkbox" name="enable_editable" value="1"
                           {{ ($user->enable_editable ?? false) ? 'checked' : '' }}/>
                    Enable Editable
                </label>
                <label class="checkbox-item">
                    <input type="checkbox" name="allow_document_upload" value="1"
                           {{ ($user->allow_document_upload ?? false) ? 'checked' : '' }}/>
                    Allow Document Upload
                </label>
                <label class="checkbox-item">
                    <input type="checkbox" name="company_verified" value="1"
                           {{ ($user->company_verified ?? false) ? 'checked' : '' }}/>
                    Company VERIFIED
                </label>
                <label class="checkbox-item">
                    <input type="checkbox" name="show_verified_batch" value="1"
                           {{ ($user->show_verified_batch ?? false) ? 'checked' : '' }}/>
                    Show verified Batch
                </label>
            </div>

            {{-- Company Name + VAT ID --}}
            <div class="form-row">
                <div class="form-col">
                    <label class="form-label">Company Name:</label>
                    <input type="text" name="company_name" class="form-input"
                           value="{{ old('company_name', $user->company_name ?? '') }}"/>
                </div>
                <div class="form-col">
                    <label class="form-label">VAT ID:</label>
                    <input type="text" name="vat_id" class="form-input"
                           value="{{ old('vat_id', $user->vat_id ?? '') }}"/>
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

    {{-- ══════════════════════════
         TAB: Documents
    ══════════════════════════ --}}
    <div class="tab-content {{ $activeTab === 'documents' ? 'active' : '' }}" id="tab-documents">

        <div class="section-title">Documents</div>

        @if(count($documents) > 0)
            @foreach($documents as $index => $doc)
                <div class="document-card">
                    <div class="document-info">
                        <div class="document-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                            </svg>
                        </div>
                        <div class="document-details">
                            <h4>{{ $doc['name'] ?? 'Document ' . ($index + 1) }}</h4>
                            <p>
                                Uploaded: {{ isset($doc['uploaded_at']) ? \Carbon\Carbon::parse($doc['uploaded_at'])->format('M d, Y H:i') : 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <div class="document-actions">
                        {{-- Status badge --}}
                        @php
                            $status = $doc['status'] ?? 'pending';
                            $badgeClass = match($status) {
                                'verified' => 'badge-success',
                                'rejected' => 'badge-danger',
                                default => 'badge-warning',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>

                        {{-- View document --}}
                        @if(isset($doc['url']) || isset($doc['path']))
                            <a href="{{ asset('storage/' . ($doc['path'] ?? $doc['url'])) }}"
                               target="_blank"
                               class="btn-action btn-action-info">
                                View
                            </a>
                        @endif

                        {{-- Verification actions --}}
                        @if($status !== 'verified')
                            <form method="POST"
                                  action="{{ route('admin.users.verify-document', ['userId' => $user->id, 'docIndex' => $index]) }}"
                                  style="display:inline;">
                                @csrf
                                <input type="hidden" name="status" value="verified"/>
                                <button type="submit" class="btn-action btn-action-success">Mark Verified</button>
                            </form>
                        @endif

                        @if($status !== 'rejected')
                            <form method="POST"
                                  action="{{ route('admin.users.verify-document', ['userId' => $user->id, 'docIndex' => $index]) }}"
                                  style="display:inline;">
                                @csrf
                                <input type="hidden" name="status" value="rejected"/>
                                <button type="submit" class="btn-action btn-action-danger">Reject</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div class="tab-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                <p>No documents uploaded yet.</p>
            </div>
        @endif
    </div>

    {{-- ══════════════════════════
         TAB: Listings
    ══════════════════════════ --}}
    <div class="tab-content {{ $activeTab === 'listings' ? 'active' : '' }}" id="tab-listings">

        {{-- Header with title and view toggle --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:12px;">
            <div>
                <div class="section-title" style="margin-bottom:4px;">Product Listings</div>
                <div style="font-size:12px; color:var(--muted);">
                    Unpaid listings: <strong style="color:#F59E0B;">{{ $listings->where('is_paid', false)->count() }}</strong>
                </div>
            </div>
            <div class="view-toggle">
                <button type="button" class="view-toggle-btn active" id="btnListUser" onclick="setViewUser('list')" title="List view">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                        <line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/>
                        <line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                    </svg>
                </button>
                <button type="button" class="view-toggle-btn" id="btnGridUser" onclick="setViewUser('grid')" title="Grid view">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Filter bar --}}
        <div class="filter-bar-inline">
            {{-- LEFT: Verification Status --}}
            <div class="filter-bar-left">
                <span class="filter-group-label" style="margin-right:4px;">Verification</span>
                @foreach(['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label)
                    <a href="{{ route('admin.users.edit', array_merge(
                            ['id' => $user->id],
                            request()->only(['listing_status', 'listing_payment', 'listing_realtime']),
                            ['listing_filter' => $key, 'active_tab' => 'listings']
                        )) }}"
                       class="filter-pill {{ $listingFilter === $key ? 'active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- RIGHT: Status + Payment + Real Time --}}
            <div class="filter-bar-right">
                {{-- Status --}}
                <div class="filter-group">
                    <span class="filter-group-label">Status</span>
                    <div class="filter-group-pills">
                        @foreach(['all' => 'All', 'active' => 'Active', 'on_hold' => 'On Hold'] as $key => $label)
                            <a href="{{ route('admin.users.edit', array_merge(
                                    ['id' => $user->id],
                                    request()->only(['listing_filter', 'listing_payment', 'listing_realtime']),
                                    ['listing_status' => $key, 'active_tab' => 'listings']
                                )) }}"
                               class="filter-pill {{ $listingStatus === $key ? 'active' : '' }}">
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
                            <a href="{{ route('admin.users.edit', array_merge(
                                    ['id' => $user->id],
                                    request()->only(['listing_filter', 'listing_status', 'listing_realtime']),
                                    ['listing_payment' => $key, 'active_tab' => 'listings']
                                )) }}"
                               class="filter-pill {{ $listingPayment === $key ? 'active' : '' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Real Time Price --}}
                <div class="filter-group">
                    <span class="filter-group-label">Real Time Price</span>
                    <div class="filter-group-pills">
                        @foreach(['all' => 'All', 'enabled' => 'Enabled', 'disabled' => 'Disabled'] as $key => $label)
                            <a href="{{ route('admin.users.edit', array_merge(
                                    ['id' => $user->id],
                                    request()->only(['listing_filter', 'listing_status', 'listing_payment']),
                                    ['listing_realtime' => $key, 'active_tab' => 'listings']
                                )) }}"
                               class="filter-pill {{ $listingRealtime === $key ? 'active' : '' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats summary --}}
        <div class="stats-summary">
            <div class="stat-card">
                <div class="stat-value">{{ $listings->count() }}</div>
                <div class="stat-label">Total Listings</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $listings->where('verification_status', 'approved')->count() }}</div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $listings->where('verification_status', 'pending')->count() }}</div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $listings->where('is_paid', true)->count() }}</div>
                <div class="stat-label">Paid</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $listings->where('real_time_price', true)->count() }}</div>
                <div class="stat-label">Real Time Price</div>
            </div>
        </div>

        {{-- Listings Cards --}}
        <div id="listingsWrap">
        @forelse($listings as $listing)
            @php
                $listingId = (string)$listing->_id;
                $productId = (string)$listing->product_id;
                $warehouseId = (string)$listing->warehouse_id;
                $product = $listingProductsMap[$productId] ?? null;
                $warehouse = $listingWarehousesMap[$warehouseId] ?? null;
                $images = $listingImagesMap[$listingId] ?? collect();
                $firstImage = $images->first();
                $slots = $listing->slots ?? [];
                $vStatus = $listing->verification_status ?? 'pending';
            @endphp

            <div class="listing-card" id="card-{{ $listingId }}">

                {{-- Thumbnail --}}
                <div class="listing-thumb-wrap">
                    @if($listing->is_paid)
                        <span class="badge-paid">Paid</span>
                    @endif
                    <img
                        src="{{ $firstImage && !empty($firstImage->image['path']) ? asset('storage/' . $firstImage->image['path']) : asset('images/placeholder.png') }}"
                        class="listing-thumb"
                        onerror="this.src='https://placehold.co/110x110/f3f4f6/9ca3af?text=No+Image'"
                    >
                    <span class="status-badge-card {{ $listing->is_active ? 'active' : 'inactive' }}">
                        {{ $listing->is_active ? 'Active' : 'On Hold' }}
                    </span>
                </div>

                {{-- Body --}}
                <div class="listing-body">
                    <div class="listing-top">
                        <div>
                            <h5 class="listing-name">{{ $product ? lang($product, 'product_name') : 'Unknown Product' }}</h5>
                            <div class="listing-sku">SKU: {{ $listing->sku_code }}</div>

                            {{-- Tags --}}
                            <div class="listing-tags">
                                @if($vStatus === 'pending')
                                    <span class="tag tag-pending">⏳ Pending Approval</span>
                                @elseif($vStatus === 'approved')
                                    <span class="tag tag-approved">✓ Approved</span>
                                @elseif($vStatus === 'rejected')
                                    <span class="tag tag-rejected">✗ Rejected</span>
                                @endif
                                @if(!empty($listing->is_popular))
                                    <span class="tag tag-popular">⭐ Popular</span>
                                @endif
                                @if(!empty($listing->real_time_price))
                                    <span class="tag tag-realtime">📈 Real Time Price</span>
                                @endif
                                @if(!empty($listing->is_sold_off))
                                    <span class="tag tag-soldoff">🚫 Sold Off</span>
                                @endif
                            </div>
                        </div>

                        <div class="listing-actions">
                            {{-- Approve Payment --}}
                            @if(!$listing->is_paid)
                            <form method="POST" action="{{ route('admin.users.listing-approve-payment', ['userId' => $user->id, 'listingId' => $listingId]) }}" style="margin:0;">
                                @csrf
                                <input type="hidden" name="listing_filter" value="{{ $listingFilter }}">
                                <input type="hidden" name="listing_status" value="{{ $listingStatus }}">
                                <input type="hidden" name="listing_payment" value="{{ $listingPayment }}">
                                <input type="hidden" name="listing_realtime" value="{{ $listingRealtime }}">
                                <button type="submit" class="icon-btn" title="Approve Payment"
                                        style="background:#EFF6FF; border-color:#BFDBFE;"
                                        onclick="return confirm('Approve payment for this listing?')">
                                    <svg width="14" height="14" fill="none" stroke="#3B82F6" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1"/>
                                        <circle cx="12" cy="12" r="9"/>
                                    </svg>
                                </button>
                            </form>
                            @else
                            <span class="icon-btn" title="Payment Approved" style="background:#D1FAE5; border-color:#6EE7B7; cursor:default;">
                                <svg width="14" height="14" fill="none" stroke="#059669" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            @endif

                            {{-- Approve Listing --}}
                            @if($vStatus !== 'approved')
                            <form method="POST" action="{{ route('admin.users.listing-approve', ['userId' => $user->id, 'listingId' => $listingId]) }}" style="margin:0;">
                                @csrf
                                <input type="hidden" name="listing_filter" value="{{ $listingFilter }}">
                                <input type="hidden" name="listing_status" value="{{ $listingStatus }}">
                                <input type="hidden" name="listing_payment" value="{{ $listingPayment }}">
                                <input type="hidden" name="listing_realtime" value="{{ $listingRealtime }}">
                                <button type="submit" class="icon-btn" title="Approve Listing"
                                        style="background:#F0FDF4; border-color:#BBF7D0;"
                                        onclick="return confirm('Approve this listing?')">
                                    <svg width="14" height="14" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </button>
                            </form>
                            @else
                            <span class="icon-btn" title="Listing Approved" style="background:#D1FAE5; border-color:#6EE7B7; cursor:default;">
                                <svg width="14" height="14" fill="none" stroke="#059669" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </span>
                            @endif

                            {{-- Toggle Hold --}}
                            <form method="POST" action="{{ route('admin.users.listing-toggle', ['userId' => $user->id, 'listingId' => $listingId]) }}" style="margin:0;">
                                @csrf @method('PATCH')
                                <input type="hidden" name="listing_filter" value="{{ $listingFilter }}">
                                <input type="hidden" name="listing_status" value="{{ $listingStatus }}">
                                <input type="hidden" name="listing_payment" value="{{ $listingPayment }}">
                                <input type="hidden" name="listing_realtime" value="{{ $listingRealtime }}">
                                <button type="submit" class="icon-btn" title="{{ $listing->is_active ? 'Put On Hold' : 'Activate' }}"
                                        style="background:{{ $listing->is_active ? '#FEF3C7' : '#D1FAE5' }}; border-color:{{ $listing->is_active ? '#FCD34D' : '#6EE7B7' }};">
                                    @if($listing->is_active)
                                        <svg width="14" height="14" fill="none" stroke="#D97706" stroke-width="2" viewBox="0 0 24 24">
                                            <rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>
                                        </svg>
                                    @else
                                        <svg width="14" height="14" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24">
                                            <polygon points="5 3 19 12 5 21 5 3"/>
                                        </svg>
                                    @endif
                                </button>
                            </form>

                            {{-- Edit --}}
                            <a href="{{ route('product_listing.edit', $listingId) }}" class="icon-btn" title="Edit">
                                <svg width="14" height="14" fill="none" stroke="#888" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    {{-- Meta grid --}}
                    <div class="listing-meta">
                        <div class="meta-item">
                            <label>Warehouse</label>
                            <span>{{ $warehouse ? lang($warehouse, 'warehouse_name') : 'N/A' }}</span>
                        </div>
                        <div class="meta-item">
                            <label>Lead Time</label>
                            <span>{{ $listing->lead_time ?? 0 }} Weeks</span>
                        </div>
                        <div class="meta-item">
                            <label>Sell Type</label>
                            <span>{{ ucwords($listing->sell_type ?? 'N/A') }}</span>
                        </div>
                        <div class="meta-item">
                            <label>Location</label>
                            <span>
                                @if($warehouse)
                                    {{ lang($warehouse, 'city') }}, {{ lang($warehouse, 'country') }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        <div class="meta-item">
                            <label>Total Available</label>
                            <span>{{ number_format($listing->total_quantity ?? 0) }} pcs</span>
                        </div>
                        <div class="meta-item">
                            <label>Currency</label>
                            <span>{{ $listing->currency_id ?? 'USD' }}</span>
                        </div>
                    </div>

                    {{-- Slots footer --}}
                    @if(count($slots) > 0)
                        <div class="slots-footer">
                            <span class="slots-hint">
                                📦 {{ count($slots) }} price {{ count($slots) === 1 ? 'tier' : 'tiers' }}
                            </span>
                            <button class="view-slots-btn" onclick="toggleSlotsUser('{{ $listingId }}')">
                                View Slots ({{ count($slots) }})
                                <span id="arrow-{{ $listingId }}">▼</span>
                            </button>
                        </div>

                        <div id="slots-{{ $listingId }}" class="slots-detail" style="display:none;">
                            @foreach($slots as $i => $slot)
                                <div class="slot-row-detail">
                                    <span>
                                        <strong>Tier {{ $i + 1 }}:</strong>
                                        @php
                                            $minQty = is_array($slot) ? ($slot['min_quantity'] ?? 0) : ($slot->min_quantity ?? 0);
                                            $maxQty = is_array($slot) ? ($slot['max_quantity'] ?? null) : ($slot->max_quantity ?? null);
                                            $price = is_array($slot) ? ($slot['price'] ?? 0) : ($slot->price ?? 0);
                                        @endphp
                                        Min {{ number_format($minQty) }}
                                        @if(!empty($maxQty))
                                            – Max {{ number_format($maxQty) }} pcs
                                        @else
                                            pcs &amp; above
                                        @endif
                                    </span>
                                    <span class="slot-price">
                                        {{ $listing->currency_id ?? 'USD' }} {{ number_format($price, 2) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                </div>{{-- end .listing-body --}}
            </div>

        @empty
            <div class="empty-card">
                <div class="empty-icon">📦</div>
                <div class="empty-text">No listings found for this user.</div>
            </div>
        @endforelse
        </div>
    </div>

    {{-- ══════════════════════════
         TAB: Purchases
    ══════════════════════════ --}}
    <div class="tab-content {{ $activeTab === 'purchases' ? 'active' : '' }}" id="tab-purchases">

        <div class="section-title">Purchases</div>

        {{-- Stats summary --}}
        <div class="stats-summary">
            <div class="stat-card">
                <div class="stat-value">{{ $purchases->count() }}</div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $purchases->sum('total_qty') }}</div>
                <div class="stat-label">Items Purchased</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $purchases->where('payment_verified', true)->count() }}</div>
                <div class="stat-label">Verified Payments</div>
            </div>
        </div>

        @if($purchases->count() > 0)
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Seller</th>
                            <th>Warehouse</th>
                            <th>Qty</th>
                            <th>Price/pc</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchases as $order)
                            @php
                                $listingId = (string)$order->offer_id;
                                $listing = $purchaseListingsMap[$listingId] ?? null;
                                $productId = $listing ? (string)$listing->product_id : null;
                                $warehouseId = $listing ? (string)$listing->warehouse_id : null;
                                $product = $productId ? ($purchaseProductsMap[$productId] ?? null) : null;
                                $warehouse = $warehouseId ? ($purchaseWarehousesMap[$warehouseId] ?? null) : null;
                                $sellerUserId = $listing ? (string)$listing->user_id : null;
                                $seller = $sellerUserId ? ($sellerUsersMap[$sellerUserId] ?? null) : null;

                                $total = ($order->total_qty ?? 0) * ($order->each_qty_price ?? 0);
                                $currency = $order->purchased_currency ?? 'USD';
                            @endphp
                            <tr>
                                <td><strong>Order{{ str_pad($order->unique_id ?? $order->_id, 6, '0', STR_PAD_LEFT) }}</strong></td>
                                <td>
                                    <span class="product-name">{{ lang($product, 'product_name') ?? 'N/A' }}</span>
                                </td>
                                <td>{{ $seller->company_name ?? $seller->name ?? 'N/A' }}</td>
                                <td>{{ lang($warehouse, 'warehouse_name') ?? 'N/A' }}</td>
                                <td>{{ number_format($order->total_qty ?? 0) }}</td>
                                <td>{{ $currency }} {{ number_format($order->each_qty_price ?? 0, 2) }}</td>
                                <td><strong>{{ $currency }} {{ number_format($total, 2) }}</strong></td>
                                <td>
                                    @if($order->payment_verified)
                                        <span class="badge badge-success">Verified</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $orderStatus = match($order->order_status ?? 0) {
                                            1 => ['Confirmed', 'badge-info'],
                                            2 => ['Processing', 'badge-warning'],
                                            3 => ['Shipped', 'badge-info'],
                                            4 => ['Delivered', 'badge-success'],
                                            5 => ['Cancelled', 'badge-danger'],
                                            default => ['Pending', 'badge-secondary'],
                                        };
                                    @endphp
                                    <span class="badge {{ $orderStatus[1] }}">{{ $orderStatus[0] }}</span>
                                </td>
                                <td>{{ $order->created_at ? $order->created_at->format('M d, Y') : 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="tab-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
                <p>No purchases found for this user.</p>
            </div>
        @endif
    </div>

    {{-- ══════════════════════════
         TAB: Sales
    ══════════════════════════ --}}
    <div class="tab-content {{ $activeTab === 'sales' ? 'active' : '' }}" id="tab-sales">

        <div class="section-title">Sales</div>

        {{-- Stats summary --}}
        <div class="stats-summary">
            <div class="stat-card">
                <div class="stat-value">{{ $sales->count() }}</div>
                <div class="stat-label">Total Sales</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $sales->sum('total_qty') }}</div>
                <div class="stat-label">Items Sold</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $sales->where('payment_verified', true)->count() }}</div>
                <div class="stat-label">Verified Payments</div>
            </div>
        </div>

        @if($sales->count() > 0)
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Buyer</th>
                            <th>Warehouse</th>
                            <th>Qty</th>
                            <th>Price/pc</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $order)
                            @php
                                $listingId = (string)$order->offer_id;
                                $listing = $salesListingsMap[$listingId] ?? null;
                                $productId = $listing ? (string)$listing->product_id : null;
                                $warehouseId = $listing ? (string)$listing->warehouse_id : null;
                                $product = $productId ? ($listingProductsMap[$productId] ?? null) : null;
                                $warehouse = $warehouseId ? ($listingWarehousesMap[$warehouseId] ?? null) : null;
                                $buyerUserId = (string)$order->user_id;
                                $buyer = $buyerUsersMap[$buyerUserId] ?? null;

                                $total = ($order->total_qty ?? 0) * ($order->sell_each_qty_price ?? $order->each_qty_price ?? 0);
                                $currency = $order->selled_currency ?? $order->purchased_currency ?? 'USD';
                            @endphp
                            <tr>
                                <td><strong>Order{{ str_pad($order->unique_id ?? $order->_id, 6, '0', STR_PAD_LEFT) }}</strong></td>
                                <td>
                                    <span class="product-name">{{ lang($product, 'product_name') ?? 'N/A' }}</span>
                                </td>
                                <td>{{ $buyer->company_name ?? $buyer->name ?? 'N/A' }}</td>
                                <td>{{ lang($warehouse, 'warehouse_name') ?? 'N/A' }}</td>
                                <td>{{ number_format($order->total_qty ?? 0) }}</td>
                                <td>{{ $currency }} {{ number_format($order->sell_each_qty_price ?? $order->each_qty_price ?? 0, 2) }}</td>
                                <td><strong>{{ $currency }} {{ number_format($total, 2) }}</strong></td>
                                <td>
                                    @if($order->payment_verified)
                                        <span class="badge badge-success">Verified</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $orderStatus = match($order->order_status ?? 0) {
                                            1 => ['Confirmed', 'badge-info'],
                                            2 => ['Processing', 'badge-warning'],
                                            3 => ['Shipped', 'badge-info'],
                                            4 => ['Delivered', 'badge-success'],
                                            5 => ['Cancelled', 'badge-danger'],
                                            default => ['Pending', 'badge-secondary'],
                                        };
                                    @endphp
                                    <span class="badge {{ $orderStatus[1] }}">{{ $orderStatus[0] }}</span>
                                </td>
                                <td>{{ $order->created_at ? $order->created_at->format('M d, Y') : 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="tab-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
                <p>No sales data available for this user.</p>
            </div>
        @endif
    </div>

</div>

@endsection

@section('scripts')
<script>
    function switchTab(tabName, btn) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        // Remove active from all buttons
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        // Show selected
        document.getElementById('tab-' + tabName).classList.add('active');
        btn.classList.add('active');

        // Update URL with active tab (without page reload)
        const url = new URL(window.location);
        url.searchParams.set('active_tab', tabName);
        window.history.replaceState({}, '', url);
    }

    // Restore tab from URL on page load
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('active_tab');
        if (activeTab) {
            const btn = document.querySelector(`.tab-btn[onclick*="'${activeTab}'"]`);
            if (btn) {
                switchTab(activeTab, btn);
            }
        }

        // Restore listing view preference
        const savedView = localStorage.getItem('userListingView');
        if (savedView === 'grid') setViewUser('grid');
    });

    // ── Grid / List view toggle for user listings ───────────────────────
    function setViewUser(type) {
        const listingsWrap = document.getElementById('listingsWrap');
        const btnList = document.getElementById('btnListUser');
        const btnGrid = document.getElementById('btnGridUser');

        if (!listingsWrap || !btnList || !btnGrid) return;

        if (type === 'grid') {
            listingsWrap.classList.add('grid-view');
            btnGrid.classList.add('active');
            btnList.classList.remove('active');
            localStorage.setItem('userListingView', 'grid');
        } else {
            listingsWrap.classList.remove('grid-view');
            btnList.classList.add('active');
            btnGrid.classList.remove('active');
            localStorage.setItem('userListingView', 'list');
        }
    }

    // ── Slot toggle for user listings ───────────────────────────────────
    function toggleSlotsUser(id) {
        const el = document.getElementById('slots-' + id);
        const arrow = document.getElementById('arrow-' + id);
        if (!el || !arrow) return;

        const open = el.style.display !== 'none' && el.style.display !== '';
        el.style.display = open ? 'none' : 'block';
        arrow.textContent = open ? '▼' : '▲';
    }
</script>
@endsection
