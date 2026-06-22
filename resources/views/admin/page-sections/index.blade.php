@extends('layouts.admin')
@section('title', 'Page Sections - Select Market')
@section('content')
<style>
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 18px;
        background: var(--primary-d);
        color: white;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        flex: 0 0 auto;
        margin-left: auto;
        transition: background .15s, transform .1s, box-shadow .2s;
    }

    .btn-back:hover {
        background: var(--primary);
        box-shadow: 0 4px 12px rgba(14,165,233,.3);
        transform: translateY(-1px);
    }

    .btn-back:active { transform: scale(.97); }

    .market-card {
        background: white;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 24px;
        text-decoration: none;
        color: var(--text);
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        transition: all .2s;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .market-card:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(14,165,233,.15);
    }

    .market-card.global {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border-color: #7dd3fc;
    }

    .market-card.global:hover {
        border-color: var(--primary);
    }

    .market-flag {
        width: 48px;
        height: 32px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid var(--border);
    }

    .market-icon {
        font-size: 32px;
        margin-bottom: 4px;
    }

    .market-name {
        font-size: 16px;
        font-weight: 700;
    }

    .market-code {
        font-size: 12px;
        color: var(--muted);
    }

    .market-meta {
        font-size: 12px;
        color: var(--muted);
        margin-top: auto;
        padding-top: 12px;
        border-top: 1px dashed var(--border);
    }

    .section-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 24px 0 12px;
    }

    .section-label:first-of-type {
        margin-top: 0;
    }
</style>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Page Sections</h1>
    <a href="{{ route('admin.dashboard') }}" class="btn-back">Back</a>
</div>

<p style="color:var(--muted); margin-bottom:24px; font-size:14px;">
    Select Global for default content, or a specific market to manage market-specific page content.
</p>

{{-- Global Section --}}
<div class="section-label">Default Content</div>
<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:20px; margin-bottom:24px;">
    <a href="{{ route('admin.page-sections.pages', 'global') }}" class="market-card global">
        <div class="market-icon">🌍</div>
        <div class="market-name">Global</div>
        <div class="market-code">Default content for all markets</div>
        <div class="market-meta">
            Click to manage global pages
        </div>
    </a>
</div>

{{-- Market-Specific Section --}}
@if($markets->isNotEmpty())
<div class="section-label">Market-Specific Content</div>
<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:20px;">
    @foreach($markets as $market)
        @php
            $data = $marketData[$market->id] ?? null;
            $country = $data['country'] ?? null;
        @endphp
        <a href="{{ route('admin.page-sections.pages', $market->id) }}" class="market-card">
            @if($country && $country->flag_url)
                <img src="{{ $country->flag_url }}" alt="{{ $country->name }}" class="market-flag">
            @else
                <div class="market-icon">🌐</div>
            @endif
            <div class="market-name">{{ $country->name ?? $market->name }}</div>
            @if($market->code)
            <div class="market-code">{{ strtoupper($market->code) }}</div>
            @endif
            <div class="market-meta">
                Click to manage pages
            </div>
        </a>
    @endforeach
</div>
@endif

@endsection
