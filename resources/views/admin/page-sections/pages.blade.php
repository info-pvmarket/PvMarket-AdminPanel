@extends('layouts.admin')
@section('title', ($isGlobal ? 'Global' : ($country->name ?? $market->name)) . ' - Page Sections')
@section('content')
<style>
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
        white-space: nowrap;
        flex: 0 0 auto;
        transition: background .15s, transform .1s;
    }

    .btn-back:hover {
        background: #334155;
    }

    .btn-back:active { transform: scale(.97); }

    .page-card {
        background: white;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 24px;
        text-decoration: none;
        color: var(--text);
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        transition: all .2s;
    }

    .page-card:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(14,165,233,.15);
    }

    .page-icon {
        font-size: 28px;
        margin-bottom: 10px;
    }

    .page-name {
        font-size: 15px;
        font-weight: 700;
    }

    .page-meta {
        font-size: 12px;
        color: var(--muted);
        margin-top: 4px;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 16px;
    }

    .breadcrumb a {
        color: var(--primary);
        text-decoration: none;
    }

    .breadcrumb a:hover {
        text-decoration: underline;
    }

    .copy-section {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
    }

    .copy-section h3 {
        font-size: 14px;
        font-weight: 700;
        color: #0369a1;
        margin: 0 0 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .copy-form {
        display: flex;
        gap: 12px;
        align-items: flex-end;
        flex-wrap: wrap;
    }

    .copy-select-wrap {
        flex: 1;
        min-width: 200px;
    }

    .copy-select-wrap label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        display: block;
        margin-bottom: 4px;
    }

    .copy-select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #bae6fd;
        border-radius: 8px;
        font-size: 14px;
        background: white;
    }

    .copy-select:focus {
        outline: none;
        border-color: var(--primary);
    }

    .copy-btn {
        padding: 9px 18px;
        background: #0284c7;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s;
    }

    .copy-btn:hover {
        background: #0369a1;
    }

    .copy-warning {
        font-size: 12px;
        color: #92400e;
        background: #fef3c7;
        padding: 8px 12px;
        border-radius: 6px;
        margin-top: 12px;
    }

    .market-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--primary);
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .market-badge.global {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    }

    .market-badge img {
        width: 20px;
        height: 14px;
        object-fit: cover;
        border-radius: 2px;
    }
</style>

<div class="breadcrumb">
    <a href="{{ route('admin.page-sections.index') }}">All Markets</a>
    <span>&rsaquo;</span>
    <span>{{ $isGlobal ? 'Global' : ($country->name ?? $market->name) }}</span>
</div>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div style="display:flex; align-items:center; gap:12px;">
        <h1 style="font-size:22px; font-weight:800; color:var(--text); margin:0;">Page Sections</h1>
        <span class="market-badge {{ $isGlobal ? 'global' : '' }}">
            @if($isGlobal)
                <span>🌍</span>
                Global
            @else
                @if($country && $country->flag_url)
                    <img src="{{ $country->flag_url }}" alt="{{ $country->name }}">
                @else
                    <span>🌐</span>
                @endif
                {{ $country->name ?? $market->name }}
            @endif
        </span>
    </div>
    <a href="{{ route('admin.page-sections.index') }}" class="btn-back">Back to Markets</a>
</div>

@if(session('success'))
    <div style="background:#d1fae5; border:1px solid #6ee7b7; color:#065f46;
                padding:12px 16px; border-radius:8px; margin-bottom:20px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background:#fee2e2; border:1px solid #fca5a5; color:#991b1b;
                padding:12px 16px; border-radius:8px; margin-bottom:20px;">
        {{ session('error') }}
    </div>
@endif

{{-- Copy from another market --}}
@if($markets->count() > 0 || !$isGlobal)
<div class="copy-section">
    <h3>
        <span>📋</span>
        Copy Content from Another Source
    </h3>
    <form action="{{ route('admin.page-sections.copy-from', $marketId) }}" method="POST" class="copy-form"
          onsubmit="return confirm('This will REPLACE all existing page sections for {{ $isGlobal ? 'Global' : ($country->name ?? $market->name) }} with content from the selected source. Continue?');">
        @csrf
        <div class="copy-select-wrap">
            <label>Source</label>
            <select name="source_market_id" class="copy-select" required>
                <option value="">Select a source to copy from...</option>
                @if(!$isGlobal)
                <option value="global">🌍 Global (Default Content)</option>
                @endif
                @foreach($markets as $m)
                    @if($isGlobal || $m->id !== $market->id)
                        @php
                            $mData = $marketData[$m->id] ?? null;
                            $mCountry = $mData['country'] ?? null;
                        @endphp
                        <option value="{{ $m->id }}">{{ $mCountry->name ?? $m->name }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <button type="submit" class="copy-btn">Copy All Pages</button>
    </form>
    <div class="copy-warning">
        This will replace all existing page content for {{ $isGlobal ? 'Global' : ($country->name ?? $market->name) }} with content from the selected source.
    </div>
</div>
@endif

<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:20px;">
    @foreach($pages as $key => $label)
        <a href="{{ route('admin.page-sections.edit', [$marketId, $key]) }}" class="page-card">
            <div class="page-icon">
                {{ ['home'=>'🏠','about'=>'ℹ️','contact'=>'📞','terms'=>'📄', 'disclaimer'=>'⚠️','delivery'=>'🚚','privacy'=>'🔒','faq'=>'❓', 'customer_support'=>'💬'][$key] ?? '📝' }}
            </div>
            <div class="page-name">{{ $label }}</div>
            <div class="page-meta">Edit page content</div>
        </a>
    @endforeach
</div>

@endsection
