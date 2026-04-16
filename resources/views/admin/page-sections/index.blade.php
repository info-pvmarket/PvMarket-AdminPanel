@extends('layouts.admin')
@section('title', 'Page Sections')
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
</style>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Page Templates</h1>
    <a href="{{ route('admin.dashboard') }}" class="btn-back">← Back</a>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:20px;">
    @foreach($pages as $key => $label)
        @if(!in_array($key, []))
        <a href="{{ route('admin.page-sections.edit', $key) }}"
           style="background:white; border:1px solid var(--border); border-radius:12px;
                  padding:24px; text-decoration:none; color:var(--text);
                  box-shadow:0 1px 4px rgba(0,0,0,.04); transition:all .2s;"
           onmouseover="this.style.borderColor='var(--primary)'; this.style.transform='translateY(-2px)'"
           onmouseout="this.style.borderColor='var(--border)'; this.style.transform=''">
            <div style="font-size:28px; margin-bottom:10px;">
                {{ ['home'=>'🏠','about'=>'ℹ️','contact'=>'📞','terms'=>'📄', 'disclaimer'=>'⚠️','delivery'=>'🚚','privacy'=>'🔒','faq'=>'❓', 'customer_support'=>'💬'][$key] ?? '📝' }}
            </div>
            <div style="font-size:15px; font-weight:700;">{{ $label }}</div>
            <div style="font-size:12px; color:var(--muted); margin-top:4px;">Edit page content</div>
        </a>
        @endif
        {{-- Temporarily commented: terms, privacy --}}
    @endforeach
</div>

@endsection