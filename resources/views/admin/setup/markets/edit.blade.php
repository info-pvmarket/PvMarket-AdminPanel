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
    .btn-small { display:inline-flex; align-items:center; gap:5px; padding:6px 14px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; font-family:inherit; transition:all .15s; text-decoration:none; border:none; }
    .btn-primary { background:var(--primary); color:white; }
    .btn-primary:hover { background:var(--primary-d); }
    .btn-danger { background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; }
    .btn-danger:hover { background:#FEE2E2; }
    .btn-success { background:#ECFDF5; color:#059669; border:1px solid #A7F3D0; }
    .btn-success:hover { background:#D1FAE5; }
    .alert-error { padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px; }
    .alert-success { padding:12px 16px; background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; border-radius:8px; font-size:13.5px; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
    .toggle-wrap { display:flex; align-items:center; gap:10px; }
    .toggle-input { display:none; }
    .toggle-slider { width:44px; height:24px; background:#CBD5E1; border-radius:12px; position:relative; cursor:pointer; transition:background .2s; }
    .toggle-slider::after { content:''; position:absolute; width:18px; height:18px; background:white; border-radius:50%; top:3px; left:3px; transition:transform .2s; box-shadow:0 1px 3px rgba(0,0,0,.15); }
    .toggle-input:checked + .toggle-slider { background:#10B981; }
    .toggle-input:checked + .toggle-slider::after { transform:translateX(20px); }
    .toggle-label { font-size:13px; font-weight:500; color:var(--text); }

    /* Domain list styles */
    .domain-list { margin-top:16px; }
    .domain-item { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; background:#F8FAFC; border:1px solid var(--border); border-radius:8px; margin-bottom:8px; }
    .domain-item:last-child { margin-bottom:0; }
    .domain-info { display:flex; align-items:center; gap:12px; }
    .domain-name { font-family:monospace; font-size:14px; font-weight:600; color:var(--text); }
    .domain-badge { padding:3px 8px; border-radius:4px; font-size:10px; font-weight:700; text-transform:uppercase; }
    .domain-badge.primary { background:#DBEAFE; color:#1E40AF; }
    .domain-actions { display:flex; align-items:center; gap:8px; }
    .add-domain-form { display:flex; align-items:flex-end; gap:12px; margin-top:16px; padding-top:16px; border-top:1px dashed var(--border); }
    .add-domain-form .form-group { flex:1; margin-bottom:0; }
    .empty-domains { text-align:center; padding:24px; color:var(--muted); font-size:13px; }
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
                <input type="text" name="code" class="form-input" placeholder="e.g. global, in, uk"
                       value="{{ old('code', $record->code) }}" required maxlength="10" style="text-transform:lowercase;"/>
                <div class="form-hint">Unique identifier (lowercase, max 10 chars)</div>
            </div>

            <div class="form-group">
                <label class="form-label">Market Name <span>*</span></label>
                <input type="text" name="name" class="form-input" placeholder="e.g. Global, India"
                       value="{{ old('name', $record->name) }}" required/>
            </div>

            <div class="form-group">
                <label class="form-label">Default Currency <span>*</span></label>
                <input type="text" name="default_currency" class="form-input" placeholder="e.g. USD, INR"
                       value="{{ old('default_currency', $record->default_currency) }}" required maxlength="10" style="text-transform:uppercase;"/>
            </div>

            <div class="form-group">
                <label class="form-label">Default Locale <span>*</span></label>
                <input type="text" name="default_locale" class="form-input" placeholder="e.g. en-US, en-IN"
                       value="{{ old('default_locale', $record->default_locale) }}" required maxlength="10"/>
            </div>

            <div class="form-group">
                <label class="form-label">Status</label>
                <div class="toggle-wrap">
                    <input type="checkbox" name="is_active" id="is_active" class="toggle-input" value="1"
                           {{ old('is_active', $record->is_active) ? 'checked' : '' }}/>
                    <label for="is_active" class="toggle-slider"></label>
                    <span class="toggle-label">Active</span>
                </div>
            </div>
        </div>
    </div>

    <div class="content-panel">
        <h3 class="section-title">Site Settings</h3>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Site Name</label>
                <input type="text" name="site_name" class="form-input" placeholder="e.g. pv.market India"
                       value="{{ old('site_name', $settings->site_name ?? '') }}"/>
            </div>

            <div class="form-group">
                <label class="form-label">Metadata Base URL</label>
                <input type="url" name="metadata_base" class="form-input" placeholder="e.g. https://pvmarket.in"
                       value="{{ old('metadata_base', $settings->metadata_base ?? '') }}"/>
            </div>

            <div class="form-group full">
                <label class="form-label">Site Description</label>
                <input type="text" name="site_description" class="form-input"
                       placeholder="e.g. India's Digital Market Place for Solar & Renewable Energy Products"
                       value="{{ old('site_description', $settings->site_description ?? '') }}"/>
            </div>

            <div class="form-group full">
                <label class="form-label">Available Currencies</label>
                <input type="text" name="available_currencies" class="form-input"
                       placeholder="e.g. USD, EUR, GBP, INR (comma-separated)"
                       value="{{ old('available_currencies', implode(', ', $settings->available_currencies ?? [])) }}"/>
                <div class="form-hint">Comma-separated list of currency codes</div>
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
                            {{ old('default_country_code', $settings->default_country_code ?? '') == $country->code ? 'selected' : '' }}>
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
                           {{ old('filter_by_country', $settings->filter_by_country ?? false) ? 'checked' : '' }}/>
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
        </div>
    </div>

    <div class="content-panel">
        <h3 class="section-title">Social Links</h3>

        <div class="form-grid-3">
            <div class="form-group">
                <label class="form-label">Facebook</label>
                <input type="url" name="social_facebook" class="form-input" placeholder="https://facebook.com/..."
                       value="{{ old('social_facebook', $settings->social_links['facebook'] ?? '') }}"/>
            </div>

            <div class="form-group">
                <label class="form-label">Twitter</label>
                <input type="url" name="social_twitter" class="form-input" placeholder="https://twitter.com/..."
                       value="{{ old('social_twitter', $settings->social_links['twitter'] ?? '') }}"/>
            </div>

            <div class="form-group">
                <label class="form-label">LinkedIn</label>
                <input type="url" name="social_linkedin" class="form-input" placeholder="https://linkedin.com/..."
                       value="{{ old('social_linkedin', $settings->social_links['linkedin'] ?? '') }}"/>
            </div>

            <div class="form-group">
                <label class="form-label">Instagram</label>
                <input type="url" name="social_instagram" class="form-input" placeholder="https://instagram.com/..."
                       value="{{ old('social_instagram', $settings->social_links['instagram'] ?? '') }}"/>
            </div>

            <div class="form-group">
                <label class="form-label">YouTube</label>
                <input type="url" name="social_youtube" class="form-input" placeholder="https://youtube.com/..."
                       value="{{ old('social_youtube', $settings->social_links['youtube'] ?? '') }}"/>
            </div>
        </div>
    </div>

    <div class="content-panel">
        <h3 class="section-title">Analytics</h3>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">GTM Container ID</label>
                <input type="text" name="gtm_container_id" class="form-input" placeholder="e.g. GTM-XXXXXXX"
                       value="{{ old('gtm_container_id', $settings->gtm_container_id ?? '') }}"/>
            </div>

            <div class="form-group">
                <label class="form-label">Google Analytics ID</label>
                <input type="text" name="google_analytics_id" class="form-input" placeholder="e.g. G-XXXXXXXXXX"
                       value="{{ old('google_analytics_id', $settings->google_analytics_id ?? '') }}"/>
            </div>
        </div>
    </div>

    <div class="content-panel">
        <h3 class="section-title">Features</h3>

        <div style="display:flex; gap:32px; flex-wrap:wrap;">
            <div class="toggle-wrap">
                <input type="checkbox" name="feature_rfq" id="feature_rfq" class="toggle-input" value="1"
                       {{ old('feature_rfq', $settings->features['rfq_enabled'] ?? true) ? 'checked' : '' }}/>
                <label for="feature_rfq" class="toggle-slider"></label>
                <span class="toggle-label">RFQ Enabled</span>
            </div>

            <div class="toggle-wrap">
                <input type="checkbox" name="feature_checkout" id="feature_checkout" class="toggle-input" value="1"
                       {{ old('feature_checkout', $settings->features['checkout_enabled'] ?? true) ? 'checked' : '' }}/>
                <label for="feature_checkout" class="toggle-slider"></label>
                <span class="toggle-label">Checkout Enabled</span>
            </div>

            <div class="toggle-wrap">
                <input type="checkbox" name="feature_bidding" id="feature_bidding" class="toggle-input" value="1"
                       {{ old('feature_bidding', $settings->features['bidding_enabled'] ?? true) ? 'checked' : '' }}/>
                <label for="feature_bidding" class="toggle-slider"></label>
                <span class="toggle-label">Bidding Enabled</span>
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

{{-- Domain Management Section --}}
<div class="content-panel">
    <h3 class="section-title">
        <span>Domain Mappings</span>
        <span style="font-size:12px; font-weight:500; color:var(--muted);">{{ count($domains) }} domain(s)</span>
    </h3>

    @if(count($domains) > 0)
        <div class="domain-list">
            @foreach($domains as $domain)
                <div class="domain-item">
                    <div class="domain-info">
                        <span class="domain-name">{{ $domain->domain }}</span>
                        @if($domain->is_primary)
                            <span class="domain-badge primary">Primary</span>
                        @endif
                    </div>
                    <div class="domain-actions">
                        @if(!$domain->is_primary)
                            <form method="POST" action="{{ route('admin.setup.markets.domains.primary', [$record->id, $domain->id]) }}" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-small btn-success" title="Set as Primary">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                    Set Primary
                                </button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.setup.markets.domains.remove', [$record->id, $domain->id]) }}" style="display:inline;"
                              onsubmit="return confirm('Remove domain {{ $domain->domain }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-small btn-danger" title="Remove">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                </svg>
                                Remove
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-domains">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 8px; opacity:.3; display:block;">
                <circle cx="12" cy="12" r="10"/>
                <line x1="2" y1="12" x2="22" y2="12"/>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
            </svg>
            No domains configured yet
        </div>
    @endif

    <form method="POST" action="{{ route('admin.setup.markets.domains.add', $record->id) }}" class="add-domain-form">
        @csrf
        <div class="form-group">
            <label class="form-label">Add Domain</label>
            <input type="text" name="domain" class="form-input" placeholder="e.g. pvmarket.in or www.pvmarket.in" required/>
        </div>
        <div class="toggle-wrap" style="margin-bottom:6px;">
            <input type="checkbox" name="is_primary" id="new_is_primary" class="toggle-input" value="1"/>
            <label for="new_is_primary" class="toggle-slider"></label>
            <span class="toggle-label">Primary</span>
        </div>
        <button type="submit" class="btn-small btn-primary">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add Domain
        </button>
    </form>
</div>

@endsection
