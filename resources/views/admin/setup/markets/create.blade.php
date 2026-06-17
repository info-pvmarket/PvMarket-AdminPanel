{{-- views/admin/setup/markets/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Add Market')

@section('styles')
<style>
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .section-title { font-size:16px; font-weight:700; color:var(--text); margin-bottom:16px; padding-bottom:10px; border-bottom:2px solid var(--border); }
    .form-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:20px 28px; margin-bottom:24px; }
    .form-grid-3 { display:grid; grid-template-columns:repeat(3, 1fr); gap:20px 28px; margin-bottom:24px; }
    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-group.full { grid-column:1 / -1; }
    .form-label { font-size:13px; font-weight:600; color:var(--text); }
    .form-label span { color:var(--danger); margin-left:2px; }
    .form-input { width:100%; padding:10px 14px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; color:var(--text); outline:none; transition:border-color .2s,box-shadow .2s; background:white; box-sizing:border-box; }
    .form-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .form-input::placeholder { color:#CBD5E1; }
    .form-hint { font-size:11px; color:var(--muted); margin-top:2px; }
    .btn-save { display:inline-flex; align-items:center; gap:8px; padding:10px 28px; background:#10B981; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; transition:background .15s; }
    .btn-save:hover { background:#059669; }
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; white-space:nowrap; border:1.5px solid var(--text); }
    .alert-error { padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px; }
    .toggle-wrap { display:flex; align-items:center; gap:10px; }
    .toggle-input { display:none; }
    .toggle-slider { width:44px; height:24px; background:#CBD5E1; border-radius:12px; position:relative; cursor:pointer; transition:background .2s; }
    .toggle-slider::after { content:''; position:absolute; width:18px; height:18px; background:white; border-radius:50%; top:3px; left:3px; transition:transform .2s; box-shadow:0 1px 3px rgba(0,0,0,.15); }
    .toggle-input:checked + .toggle-slider { background:#10B981; }
    .toggle-input:checked + .toggle-slider::after { transform:translateX(20px); }
    .toggle-label { font-size:13px; font-weight:500; color:var(--text); }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Add Market</h1>
    <a href="{{ route('admin.setup.markets.index') }}" class="btn-back">← Back</a>
</div>

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('admin.setup.markets.store') }}">
    @csrf

    <div class="content-panel" style="margin-bottom:20px;">
        <h3 class="section-title">Basic Information</h3>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Market Code <span>*</span></label>
                <input type="text" name="code" class="form-input" placeholder="e.g. global, in, uk"
                       value="{{ old('code') }}" required maxlength="10" style="text-transform:lowercase;"/>
                <div class="form-hint">Unique identifier (lowercase, max 10 chars)</div>
            </div>

            <div class="form-group">
                <label class="form-label">Market Name <span>*</span></label>
                <input type="text" name="name" class="form-input" placeholder="e.g. Global, India, United Kingdom"
                       value="{{ old('name') }}" required/>
            </div>

            <div class="form-group">
                <label class="form-label">Default Currency <span>*</span></label>
                <input type="text" name="default_currency" class="form-input" placeholder="e.g. USD, INR, GBP"
                       value="{{ old('default_currency', 'USD') }}" required maxlength="10" style="text-transform:uppercase;"/>
            </div>

            <div class="form-group">
                <label class="form-label">Default Locale <span>*</span></label>
                <input type="text" name="default_locale" class="form-input" placeholder="e.g. en-US, en-IN, en-GB"
                       value="{{ old('default_locale', 'en-US') }}" required maxlength="10"/>
            </div>

            <div class="form-group">
                <label class="form-label">Status</label>
                <div class="toggle-wrap">
                    <input type="checkbox" name="is_active" id="is_active" class="toggle-input" value="1" checked/>
                    <label for="is_active" class="toggle-slider"></label>
                    <span class="toggle-label">Active</span>
                </div>
            </div>
        </div>
    </div>

    <div class="content-panel" style="margin-bottom:20px;">
        <h3 class="section-title">Site Settings (Optional)</h3>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Site Name</label>
                <input type="text" name="site_name" class="form-input" placeholder="e.g. pv.market India"
                       value="{{ old('site_name') }}"/>
            </div>

            <div class="form-group">
                <label class="form-label">Metadata Base URL</label>
                <input type="url" name="metadata_base" class="form-input" placeholder="e.g. https://pvmarket.in"
                       value="{{ old('metadata_base') }}"/>
            </div>

            <div class="form-group full">
                <label class="form-label">Site Description</label>
                <input type="text" name="site_description" class="form-input"
                       placeholder="e.g. India's Digital Market Place for Solar & Renewable Energy Products"
                       value="{{ old('site_description') }}"/>
            </div>
        </div>
    </div>

    <div class="content-panel" style="margin-bottom:20px;">
        <h3 class="section-title">Contact Information (Optional)</h3>

        <div class="form-grid-3">
            <div class="form-group">
                <label class="form-label">Contact Email</label>
                <input type="email" name="contact_email" class="form-input" placeholder="e.g. india@pv.market"
                       value="{{ old('contact_email') }}"/>
            </div>

            <div class="form-group">
                <label class="form-label">Contact Phone</label>
                <input type="text" name="contact_phone" class="form-input" placeholder="e.g. +91 9876543210"
                       value="{{ old('contact_phone') }}"/>
            </div>

            <div class="form-group">
                <label class="form-label">Contact Address</label>
                <input type="text" name="contact_address" class="form-input" placeholder="e.g. Mumbai, India"
                       value="{{ old('contact_address') }}"/>
            </div>
        </div>
    </div>

    <div class="content-panel" style="margin-bottom:20px;">
        <h3 class="section-title">Analytics (Optional)</h3>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">GTM Container ID</label>
                <input type="text" name="gtm_container_id" class="form-input" placeholder="e.g. GTM-XXXXXXX"
                       value="{{ old('gtm_container_id') }}"/>
            </div>

            <div class="form-group">
                <label class="form-label">Google Analytics ID</label>
                <input type="text" name="google_analytics_id" class="form-input" placeholder="e.g. G-XXXXXXXXXX"
                       value="{{ old('google_analytics_id') }}"/>
            </div>
        </div>
    </div>

    <div style="display:flex; justify-content:flex-end;">
        <button type="submit" class="btn-save">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            Create Market
        </button>
    </div>
</form>

@endsection
