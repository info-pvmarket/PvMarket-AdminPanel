{{-- views/admin/setup/markets/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Market - ' . $record->name)

@section('styles')
<style>
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,.04); margin-bottom:20px; }
    .section-title { font-size:16px; font-weight:700; color:var(--text); margin-bottom:16px; padding-bottom:10px; border-bottom:2px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
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
    .alert-success { padding:12px 16px; background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; border-radius:8px; font-size:13.5px; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
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
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Edit Market: {{ $record->name }}</h1>
    <a href="{{ route('admin.setup.markets.index') }}" class="btn-back">← Back</a>
</div>

@if(session('success'))
    <div class="alert-success">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('admin.setup.markets.update', $record->id) }}">
    @csrf
    @method('PUT')

    <div class="content-panel">
        <h3 class="section-title">Basic Information</h3>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Market Code <span>*</span></label>
                <select name="code" class="form-input" required>
                    <option value="">Select country</option>
                    @if(strtolower((string) $record->code) === 'global')
                        <option value="global" {{ old('code', $record->code) === 'global' ? 'selected' : '' }}>
                            Global Market (GLOBAL)
                        </option>
                    @endif
                    @foreach($marketCountries as $country)
                        <option value="{{ strtolower($country['code']) }}"
                            {{ strtolower((string) old('code', $record->code)) === strtolower($country['code']) ? 'selected' : '' }}>
                            {{ $country['name'] }} ({{ $country['code'] }})
                        </option>
                    @endforeach
                </select>
                <div class="form-hint">The selected two-letter country code is stored as the market code</div>
            </div>

            <div class="form-group">
                <label class="form-label">Market Name <span>*</span></label>
                <input type="text" name="name" class="form-input" placeholder="e.g. Global, India"
                       value="{{ old('name', $record->name) }}" required/>
            </div>
        </div>
    </div>

    <div class="content-panel">
        <h3 class="section-title">Product Filtering</h3>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Default Country</label>
                <select name="default_country_code" class="form-input">
                    <option value="">-- All Countries (Global) --</option>
                    @foreach($countries as $country)
                        <option value="{{ $country->code }}"
                            {{ old('default_country_code', $settings->default_country_code ?? strtoupper((string) $record->code)) == $country->code ? 'selected' : '' }}>
                            {{ $country->name }} ({{ $country->code }})
                        </option>
                    @endforeach
                </select>
                <div class="form-hint">Products will default to warehouses in this country</div>
            </div>

            <div class="form-group">
                <label class="form-label">Auto-filter Products</label>
                <div class="toggle-wrap">
                    <input type="checkbox" name="filter_by_country" id="filter_by_country"
                           class="toggle-input" value="1"
                           {{ old('filter_by_country', $settings->filter_by_country ?? strtolower((string) $record->code) !== 'global') ? 'checked' : '' }}/>
                    <label for="filter_by_country" class="toggle-slider"></label>
                    <span class="toggle-label">Only show products from default country</span>
                </div>
            </div>
        </div>
    </div>

    <div class="content-panel">
        <h3 class="section-title">Contact Information</h3>

        <div class="form-grid-3">
            <div class="form-group">
                <label class="form-label">Contact Email</label>
                <input type="email" name="contact_email" class="form-input" placeholder="e.g. india@pv.market"
                       value="{{ old('contact_email', $settings->contact_email ?? '') }}"/>
            </div>

            <div class="form-group">
                <label class="form-label">Contact Phone</label>
                <input type="text" name="contact_phone" class="form-input" placeholder="e.g. +91 9876543210"
                       value="{{ old('contact_phone', $settings->contact_phone ?? '') }}"/>
            </div>

            <div class="form-group">
                <label class="form-label">Contact Address</label>
                <input type="text" name="contact_address" class="form-input" placeholder="e.g. Mumbai, India"
                       value="{{ old('contact_address', $settings->contact_address ?? '') }}"/>
            </div>

            <div class="form-group full">
                <label class="form-label">Calendly Link</label>
                <input type="url" name="calendly_link" class="form-input" placeholder="e.g. https://calendly.com/pv-market/consultation"
                       value="{{ old('calendly_link', $settings->calendly_link ?? '') }}" maxlength="2048"/>
                <div class="form-hint">Optional scheduling link for this market</div>
            </div>
        </div>
    </div>

    <div style="display:flex; justify-content:flex-end; margin-bottom:20px;">
        <button type="submit" class="btn-save">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            Save Changes
        </button>
    </div>
</form>

@endsection
