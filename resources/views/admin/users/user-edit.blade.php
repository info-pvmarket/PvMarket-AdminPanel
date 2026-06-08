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
</style>
@endsection

@section('content')

{{-- ── Outer header ── --}}
<div class="page-outer-header">
    <h1>Update {{ ucfirst($user->user_type ?? 'User') }} Details</h1>
    <a href="{{ route('admin.users.index') }}" class="btn-back">← Back</a>
</div>

{{-- ── Main edit card ── --}}
<div class="user-edit-card">

    {{-- Top: avatar + company verified --}}
    <div class="card-top">

        {{-- Company Verified toggle (left) --}}
        <div class="company-verified-wrap">
            <div class="company-verified-label">Company Verified</div>
            <form method="POST" action="{{ route('admin.users.toggle-verified', $user->id) }}">
                @csrf @method('PATCH')
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

        <button class="tab-btn {{ session('active_tab', 'basic') === 'basic' ? 'active' : '' }}"
                onclick="switchTab('basic', this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            Basic Details
        </button>

        <button class="tab-btn {{ session('active_tab') === 'company' ? 'active' : '' }}"
                onclick="switchTab('company', this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Company Details
        </button>

        <button class="tab-btn" onclick="switchTab('documents', this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            Documents
        </button>

        <button class="tab-btn" onclick="switchTab('products', this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            </svg>
            Products
        </button>

        <button class="tab-btn" onclick="switchTab('purchases', this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
            Purchases
        </button>

        <button class="tab-btn" onclick="switchTab('sales', this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            Sales
        </button>

    </div>

    {{-- ══════════════════════════
         TAB: Basic Details
    ══════════════════════════ --}}
    <div class="tab-content {{ session('active_tab', 'basic') === 'basic' ? 'active' : '' }}"
         id="tab-basic">

        @if(session('success') && session('active_tab') === 'basic')
            <div class="alert-success">✓ {{ session('success') }}</div>
        @endif

        @if($errors->any() && session('active_tab', 'basic') === 'basic')
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

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
    <div class="tab-content {{ session('active_tab') === 'company' ? 'active' : '' }}"
         id="tab-company">

        @if(session('success') && session('active_tab') === 'company')
            <div class="alert-success">✓ {{ session('success') }}</div>
        @endif

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
         TAB: Documents (placeholder)
    ══════════════════════════ --}}
    <div class="tab-content" id="tab-documents">
        <div class="tab-placeholder">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            <p>No documents uploaded yet.</p>
        </div>
    </div>

    {{-- ══════════════════════════
         TAB: Products (placeholder)
    ══════════════════════════ --}}
    <div class="tab-content" id="tab-products">
        <div class="tab-placeholder">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            </svg>
            <p>No products listed by this user.</p>
        </div>
    </div>

    {{-- ══════════════════════════
         TAB: Purchases (placeholder)
    ══════════════════════════ --}}
    <div class="tab-content" id="tab-purchases">
        <div class="tab-placeholder">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
            <p>No purchases found for this user.</p>
        </div>
    </div>

    {{-- ══════════════════════════
         TAB: Sales (placeholder)
    ══════════════════════════ --}}
    <div class="tab-content" id="tab-sales">
        <div class="tab-placeholder">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            <p>No sales data available for this user.</p>
        </div>
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
    }
</script>
@endsection