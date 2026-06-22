{{-- resources/views/admin/page-sections/edit.blade.php --}}
@extends('layouts.admin')
@section('title', $pageLabel . ' – ' . ($isGlobal ? 'Global' : ($country->name ?? $market->name)) . ' – Page Sections')
@section('content')

{{-- Quill CSS --}}
<link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
<style>
    .ql-toolbar.ql-snow {
        border: none !important;
        border-bottom: 1px solid #E2E8F0 !important;
        background: #F8FAFC;
        padding: 10px 12px;
        font-family: inherit;
    }
    .ql-container.ql-snow {
        border: none !important;
        font-family: inherit;
        font-size: 13.5px;
    }
    .ql-editor {
        min-height: 200px;
        font-size: 13.5px;
        font-family: inherit;
        color: #1E293B;
        line-height: 1.7;
        padding: 14px 16px;
    }
    .ql-editor.ql-blank::before {
        color: #CBD5E1;
        font-style: normal;
        font-size: 13.5px;
    }
    .ql-editor table {
        border-collapse: collapse;
        width: 100%;
        margin: 12px 0;
    }
    .ql-editor table th,
    .ql-editor table td {
        border: 1px solid #CBD5E1;
        padding: 8px 12px;
        text-align: left;
        font-size: 13px;
    }
    .ql-editor table th {
        background: #F8FAFC;
        font-weight: 700;
        color: #1E293B;
    }
    .ql-editor table tr:nth-child(even) td {
        background: #F8FAFC;
    }
    .ql-editor blockquote {
        border-left: 4px solid #F59E0B;
        background: #FFFBEB;
        margin: 12px 0;
        padding: 10px 16px;
        border-radius: 0 8px 8px 0;
        color: #92400E;
    }
    .ql-editor hr {
        border: none;
        border-top: 1px solid #E2E8F0;
        margin: 16px 0;
    }
    .quill-wrapper {
        border: 1.5px solid #CBD5E1;
        border-radius: 8px;
        overflow: hidden;
        background: white;
    }
    .quill-wrapper:focus-within {
        border-color: #0EA5E9;
        box-shadow: 0 0 0 3px rgba(14,165,233,.1);
    }
    /* ── Shipping table styles ── */
    .shipping-table-grid {
        display: grid;
        gap: 8px;
        align-items: end;
    }
    .shipping-col-header {
        font-size: 11px;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .4px;
        text-align: center;
        padding: 6px 4px;
    }
    .shipping-vehicle-header {
        font-size: 11px;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .4px;
        padding: 6px 4px;
    }
    .shipping-row {
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 8px;
    }
    .price-input {
        text-align: center;
        font-weight: 600;
        font-size: 14px;
        color: #0f172a;
    }
</style>

<style>
    .btn-back {
        display:inline-flex; align-items:center; gap:7px;
        padding:9px 18px; background:var(--text); color:white;
        border-radius:8px; font-size:13.5px; font-weight:600;
        text-decoration:none; transition:background .15s;
        white-space:nowrap; border:1.5px solid var(--text);
    }
    .btn-back:hover { background:#334155; }
    .field-label {
        font-size:12px; font-weight:600; color:var(--muted);
        display:block; margin-bottom:4px;
    }
    .field-input {
        width:100%; padding:8px 12px; border:1px solid var(--border);
        border-radius:8px; font-size:14px; box-sizing:border-box;
        background:white;
    }
    .field-input:focus { outline:none; border-color:var(--primary); }
    .section-card {
        background:white; border:1px solid var(--border); border-radius:12px;
        padding:24px; margin-bottom:20px; box-shadow:0 1px 4px rgba(0,0,0,.04);
    }
    .section-header {
        display:flex; justify-content:space-between;
        align-items:center; margin-bottom:20px;
        padding-bottom:14px; border-bottom:1px solid var(--border);
    }
    .section-title { font-size:15px; font-weight:700; margin:0; }
    .section-badge {
        font-size:11px; font-weight:600; padding:3px 10px;
        border-radius:20px; background:#f1f5f9; color:var(--muted);
        text-transform:uppercase; letter-spacing:.5px;
    }
    .type-badge {
        font-size:10px; font-weight:700; padding:2px 8px;
        border-radius:20px; background:#ede9fe; color:#6d28d9;
        text-transform:uppercase; letter-spacing:.5px;
    }
    .fields-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .full-width { grid-column:1 / -1; }
    .card-item {
        background:#f8fafc; border:1px solid var(--border);
        border-radius:10px; padding:16px; margin-bottom:12px;
        position:relative;
    }
    .card-item-header {
        font-size:12px; font-weight:700; color:var(--muted);
        text-transform:uppercase; letter-spacing:.4px; margin-bottom:12px;
    }
    .contact-group {
        background:#f8fafc; border:1px solid var(--border);
        border-radius:10px; padding:16px; margin-bottom:12px;
    }
    .contact-group-title {
        font-size:12px; font-weight:700; color:var(--text);
        margin:0 0 12px; text-transform:uppercase; letter-spacing:.4px;
    }
    .section-divider {
        border:none; border-top:1px dashed var(--border);
        margin:20px 0;
    }
    .block-label {
        font-size:13px; font-weight:700; color:var(--text);
        margin:0 0 14px; display:flex; align-items:center; gap:6px;
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
    .market-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--primary);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .market-badge.global {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    }
    .market-badge img {
        width: 18px;
        height: 12px;
        object-fit: cover;
        border-radius: 2px;
    }
</style>

<div class="breadcrumb">
    <a href="{{ route('admin.page-sections.index') }}">All Markets</a>
    <span>&rsaquo;</span>
    <a href="{{ route('admin.page-sections.pages', $marketId) }}">{{ $isGlobal ? 'Global' : ($country->name ?? $market->name) }}</a>
    <span>&rsaquo;</span>
    <span>{{ $pageLabel }}</span>
</div>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div style="display:flex; align-items:center; gap:12px;">
        <h1 style="font-size:22px; font-weight:800; color:var(--text); margin:0;">{{ $pageLabel }}</h1>
        <span class="market-badge {{ $isGlobal ? 'global' : '' }}">
            @if($isGlobal)
                Global
            @else
                @if($country && $country->flag_url)
                    <img src="{{ $country->flag_url }}" alt="{{ $country->name }}">
                @endif
                {{ $country->name ?? $market->name }}
            @endif
        </span>
    </div>
    <a href="{{ route('admin.page-sections.pages', $marketId) }}" class="btn-back">Back to Pages</a>
</div>

@if(session('success'))
    <div style="background:#d1fae5; border:1px solid #6ee7b7; color:#065f46;
                padding:12px 16px; border-radius:8px; margin-bottom:20px;">
        {{ session('success') }}
    </div>
@endif

<form method="POST" action="{{ route('admin.page-sections.update', [$marketId, $page]) }}"
      enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @forelse($sections as $section)
    @php $type = $section->type ?? 'default'; @endphp
    <div class="section-card">

        {{-- Card Header --}}
        <div class="section-header">
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <h2 class="section-title">{{ $section->title ?: ucwords(str_replace('_',' ',$section->section)) }}</h2>
                <span class="section-badge">{{ $section->section }}</span>
                <span class="type-badge">{{ $type }}</span>
            </div>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                <input type="checkbox" name="sections[{{ $section->id }}][is_active]"
                       {{ $section->is_active ? 'checked' : '' }}>
                Active
            </label>
        </div>

        {{-- ════ TYPE: richtext ════ --}}
        @if($type === 'richtext')
        <div style="display:grid; gap:16px;">
            <div>
                <label class="field-label">Section Heading</label>
                <input type="text" class="field-input"
                       name="sections[{{ $section->id }}][title]"
                       value="{{ old("sections.{$section->id}.title", $section->title) }}"
                       placeholder="e.g. About pv.market">
            </div>
            <div>
                <label class="field-label">Content</label>
                <textarea name="sections[{{ $section->id }}][extra][content]"
                    id="quill_input_{{ $section->id }}"
                    style="display:none;">{{ old("sections.{$section->id}.extra.content", $section->extra['content'] ?? '') }}</textarea>
                <div class="quill-wrapper">
                    <div id="quill_editor_{{ $section->id }}"></div>
                </div>
            </div>
        </div>

        {{-- ════ TYPE: text_block ════ --}}
        @elseif($type === 'text_block')
        <div style="display:grid; gap:16px;">
            <div>
                <label class="field-label">Title</label>
                <input type="text" class="field-input"
                       name="sections[{{ $section->id }}][title]"
                       value="{{ old("sections.{$section->id}.title", $section->title) }}">
            </div>
            <div>
                <label class="field-label">Subtitle / Last Updated</label>
                <input type="text" class="field-input"
                       name="sections[{{ $section->id }}][subtitle]"
                       value="{{ old("sections.{$section->id}.subtitle", $section->subtitle) }}"
                       placeholder="e.g. Last Updated: October 28, 2025">
            </div>
            <div>
                <label class="field-label">Content</label>
                <textarea name="sections[{{ $section->id }}][extra][content]"
                    id="quill_input_{{ $section->id }}"
                    style="display:none;">{{ old("sections.{$section->id}.extra.content", $section->extra['content'] ?? '') }}</textarea>
                <div class="quill-wrapper">
                    <div id="quill_editor_{{ $section->id }}"></div>
                </div>
            </div>
        </div>

        {{-- ════ TYPE: cards / cards_no_icon ════ --}}
        @elseif(in_array($type, ['cards', 'cards_no_icon']))
        <div class="fields-grid" style="margin-bottom:20px;">
            <div>
                <label class="field-label">Section Title</label>
                <input type="text" class="field-input"
                       name="sections[{{ $section->id }}][title]"
                       value="{{ old("sections.{$section->id}.title", $section->title) }}">
            </div>
            <div>
                <label class="field-label">Section Subtitle</label>
                <input type="text" class="field-input"
                       name="sections[{{ $section->id }}][subtitle]"
                       value="{{ old("sections.{$section->id}.subtitle", $section->subtitle) }}">
            </div>
            @if(isset($section->extra['description']))
            <div class="full-width">
                <label class="field-label">Section Description</label>
                <textarea class="field-input" rows="3" style="resize:vertical;"
                          name="sections[{{ $section->id }}][extra][description]">{{ old("sections.{$section->id}.extra.description", $section->extra['description'] ?? '') }}</textarea>
            </div>
            @endif
        </div>
        <hr class="section-divider">
        <p class="block-label">🃏 Cards</p>
        @php $items = $section->extra['items'] ?? []; @endphp
        @foreach($items as $i => $item)
        <div class="card-item">
            <div class="card-item-header">Card {{ $i + 1 }}</div>
            <div class="fields-grid">
                @if($type === 'cards')
                <div>
                    <label class="field-label">Icon (emoji)</label>
                    <input type="text" class="field-input"
                           name="sections[{{ $section->id }}][extra][items][{{ $i }}][icon]"
                           value="{{ old("sections.{$section->id}.extra.items.{$i}.icon", $item['icon'] ?? '') }}"
                           placeholder="e.g. 🤖">
                </div>
                @endif
                <div class="{{ $type === 'cards_no_icon' ? 'full-width' : '' }}">
                    <label class="field-label">Title</label>
                    <input type="text" class="field-input"
                           name="sections[{{ $section->id }}][extra][items][{{ $i }}][title]"
                           value="{{ old("sections.{$section->id}.extra.items.{$i}.title", $item['title'] ?? '') }}">
                </div>
                <div class="full-width">
                    <label class="field-label">Description</label>
                    <textarea class="field-input" rows="2" style="resize:vertical;"
                              name="sections[{{ $section->id }}][extra][items][{{ $i }}][desc]">{{ old("sections.{$section->id}.extra.items.{$i}.desc", $item['desc'] ?? '') }}</textarea>
                </div>
            </div>
        </div>
        @endforeach

        {{-- ════ TYPE: shipping_table ════ --}}
        @elseif($type === 'shipping_table')
        @php
            $cols     = $section->extra['columns'] ?? ['Dubai','Sharjah','Ajman / UAQ','FUJ / RAK','Abu Dhabi'];
            $rows     = $section->extra['rows'] ?? [];
            $colCount = count($cols);
        @endphp

        {{-- Meta fields --}}
        <div class="fields-grid" style="margin-bottom:20px;">
            <div>
                <label class="field-label">Section Title</label>
                <input type="text" class="field-input"
                       name="sections[{{ $section->id }}][title]"
                       value="{{ old("sections.{$section->id}.title", $section->title) }}">
            </div>
            <div>
                <label class="field-label">Currency Note</label>
                <input type="text" class="field-input"
                       name="sections[{{ $section->id }}][extra][currency_note]"
                       value="{{ old("sections.{$section->id}.extra.currency_note", $section->extra['currency_note'] ?? 'All prices are in AED') }}"
                       placeholder="e.g. All prices are in AED">
            </div>
            <div class="full-width">
                <label class="field-label">Subtitle / Description</label>
                <input type="text" class="field-input"
                       name="sections[{{ $section->id }}][subtitle]"
                       value="{{ old("sections.{$section->id}.subtitle", $section->subtitle) }}"
                       placeholder="e.g. Fees vary based on weight and destination">
            </div>
        </div>

        <hr class="section-divider">
        <p class="block-label">🚚 Shipping Rates Table</p>

        {{-- Column header editors --}}
        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:14px 16px; margin-bottom:16px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <p style="font-size:11px; font-weight:700; color:#3b82f6; text-transform:uppercase; letter-spacing:.5px; margin:0;">
                    📝 Column Headers (editable)
                </p>
                <div style="display:flex; gap:8px;">
                    <button type="button"
                            onclick="addShippingColumn('{{ $section->id }}')"
                            style="display:inline-flex; align-items:center; gap:5px;
                                   padding:5px 12px; background:#3b82f6; color:white;
                                   border:none; border-radius:7px; font-size:12px;
                                   font-weight:600; cursor:pointer;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Add Column
                    </button>
                    <button type="button"
                            onclick="removeShippingColumn('{{ $section->id }}')"
                            style="display:inline-flex; align-items:center; gap:5px;
                                   padding:5px 12px; background:white; color:#ef4444;
                                   border:1px solid #fca5a5; border-radius:7px; font-size:12px;
                                   font-weight:600; cursor:pointer;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Remove Last
                    </button>
                </div>
            </div>
            {{-- Header row: Vehicle Type label + editable column inputs --}}
            <div id="shipping_header_{{ $section->id }}"
                 style="display:flex; gap:8px; align-items:center;">
                <div style="width:180px; flex-shrink:0; font-size:12px; font-weight:700; color:#64748b; padding:0 4px;">
                    Vehicle Type
                </div>
                <div id="shipping_cols_{{ $section->id }}"
                     style="display:flex; gap:8px; flex:1;">
                    @foreach($cols as $ci => $col)
                    <div class="shipping-col-wrap" style="flex:1; min-width:80px; position:relative;">
                        <input type="text" class="field-input"
                               name="sections[{{ $section->id }}][extra][columns][{{ $ci }}]"
                               value="{{ old("sections.{$section->id}.extra.columns.{$ci}", $col) }}"
                               data-col-index="{{ $ci }}"
                               style="font-size:12px; font-weight:700; text-align:center; background:#fff; border-color:#bfdbfe; width:100%; box-sizing:border-box;"
                               placeholder="City">
                    </div>
                    @endforeach
                </div>
                <div style="width:36px; flex-shrink:0;"></div>{{-- spacer aligns with row delete buttons --}}
            </div>
        </div>

        {{-- Data rows --}}
        <div id="shipping_rows_{{ $section->id }}">
            @foreach($rows as $ri => $row)
            <div class="shipping-row" id="shipping_row_{{ $section->id }}_{{ $ri }}">
                <div style="display:flex; gap:8px; align-items:flex-end;">
                    <div style="width:180px; flex-shrink:0;">
                        <label class="field-label">Vehicle Type</label>
                        <input type="text" class="field-input"
                               name="sections[{{ $section->id }}][extra][rows][{{ $ri }}][vehicle]"
                               value="{{ old("sections.{$section->id}.extra.rows.{$ri}.vehicle", $row['vehicle'] ?? '') }}"
                               placeholder="e.g. 3 TON">
                    </div>
                    <div class="shipping-prices-wrap" style="display:flex; gap:8px; flex:1;">
                        @foreach($cols as $ci => $col)
                        <div class="shipping-price-cell" style="flex:1; min-width:80px;">
                            <label class="field-label" style="text-align:center;">{{ $col }}</label>
                            <input type="number" class="field-input price-input"
                                   name="sections[{{ $section->id }}][extra][rows][{{ $ri }}][prices][{{ $ci }}]"
                                   value="{{ old("sections.{$section->id}.extra.rows.{$ri}.prices.{$ci}", $row['prices'][$ci] ?? '') }}"
                                   placeholder="0" min="0">
                        </div>
                        @endforeach
                    </div>
                    <div style="width:36px; flex-shrink:0; display:flex; align-items:flex-end; padding-bottom:2px;">
                        <button type="button"
                                onclick="removeShippingRow(this)"
                                style="background:none; border:none; cursor:pointer;
                                       color:#ef4444; font-size:20px; line-height:1;
                                       width:36px; height:36px; display:flex;
                                       align-items:center; justify-content:center;
                                       border-radius:6px;"
                                onmouseover="this.style.background='#fef2f2'"
                                onmouseout="this.style.background='none'"
                                title="Remove row">✕</button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <button type="button"
                onclick="addShippingRow('{{ $section->id }}')"
                style="display:inline-flex; align-items:center; gap:6px; margin-top:4px;
                       padding:8px 18px; background:var(--primary); color:white;
                       border:none; border-radius:8px; font-size:13px;
                       font-weight:600; cursor:pointer;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add Vehicle Row
        </button>

        {{-- ════ TYPE: sections ════ --}}
        @elseif($type === 'sections')
        <div style="margin-bottom:20px;">
            <label class="field-label">Section Title</label>
            <input type="text" class="field-input"
                   name="sections[{{ $section->id }}][title]"
                   value="{{ old("sections.{$section->id}.title", $section->title) }}">
        </div>
        <hr class="section-divider">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <p class="block-label" style="margin:0;">📋 Content Sections</p>
            <button type="button"
                    onclick="addSectionItem('{{ $section->id }}')"
                    style="display:inline-flex; align-items:center; gap:6px;
                           padding:7px 16px; background:var(--primary); color:white;
                           border:none; border-radius:8px; font-size:13px;
                           font-weight:600; cursor:pointer;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add Section
            </button>
        </div>
        @php $items = $section->extra['items'] ?? []; @endphp
        <div id="sections_container_{{ $section->id }}">
        @foreach($items as $i => $item)
        <div class="card-item" id="section_item_{{ $section->id }}_{{ $i }}">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <span class="card-item-header" style="margin:0;">Section {{ $i + 1 }}</span>
                <button type="button" onclick="removeSectionItem(this)"
                        style="background:none; border:none; cursor:pointer;
                               color:#ef4444; font-size:18px; line-height:1;"
                        title="Remove">✕</button>
            </div>
            <div style="display:grid; gap:12px;">
                <div>
                    <label class="field-label">Section Title</label>
                    <input type="text" class="field-input"
                           name="sections[{{ $section->id }}][extra][items][{{ $i }}][title]"
                           value="{{ old("sections.{$section->id}.extra.items.{$i}.title", $item['title'] ?? '') }}"
                           placeholder="e.g. General Disclaimer">
                </div>
                <div>
                    <label class="field-label">Content</label>
                    <textarea
                        name="sections[{{ $section->id }}][extra][items][{{ $i }}][content]"
                        id="sec_input_{{ $section->id }}_{{ $i }}"
                        style="display:none;">{{ old("sections.{$section->id}.extra.items.{$i}.content", $item['content'] ?? '') }}</textarea>
                    <div class="quill-wrapper">
                        <div id="sec_editor_{{ $section->id }}_{{ $i }}"></div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        </div>

        {{-- ════ TYPE: faq ════ --}}
        @elseif($type === 'faq')
        <div style="margin-bottom:20px;">
            <label class="field-label">Section Title</label>
            <input type="text" class="field-input"
                   name="sections[{{ $section->id }}][title]"
                   value="{{ old("sections.{$section->id}.title", $section->title) }}">
        </div>
        <hr class="section-divider">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <p class="block-label" style="margin:0;">❓ Questions & Answers</p>
            <button type="button"
                    onclick="addFaqItem('{{ $section->id }}')"
                    style="display:inline-flex; align-items:center; gap:6px;
                           padding:7px 16px; background:var(--primary); color:white;
                           border:none; border-radius:8px; font-size:13px;
                           font-weight:600; cursor:pointer;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add Question
            </button>
        </div>
        @php $items = $section->extra['items'] ?? []; @endphp
        <div id="faq_container_{{ $section->id }}">
        @foreach($items as $i => $item)
        <div class="card-item" id="faq_item_{{ $section->id }}_{{ $i }}">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <span class="card-item-header" style="margin:0;">Question {{ $i + 1 }}</span>
                <button type="button" onclick="removeFaqItem(this)"
                        style="background:none; border:none; cursor:pointer;
                               color:#ef4444; font-size:18px; line-height:1;"
                        title="Remove">✕</button>
            </div>
            <div style="display:grid; gap:12px;">
                <div>
                    <label class="field-label">Question</label>
                    <input type="text" class="field-input"
                           name="sections[{{ $section->id }}][extra][items][{{ $i }}][question]"
                           value="{{ old("sections.{$section->id}.extra.items.{$i}.question", $item['question'] ?? '') }}"
                           placeholder="e.g. What is pv.market?">
                </div>
                <div>
                    <label class="field-label">Answer</label>
                    <textarea
                        name="sections[{{ $section->id }}][extra][items][{{ $i }}][answer]"
                        id="quill_input_faq_{{ $section->id }}_{{ $i }}"
                        style="display:none;">{{ old("sections.{$section->id}.extra.items.{$i}.answer", $item['answer'] ?? '') }}</textarea>
                    <div class="quill-wrapper">
                        <div id="quill_editor_faq_{{ $section->id }}_{{ $i }}"></div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        </div>

        {{-- ════ TYPE: logos ════ --}}
        @elseif($type === 'logos')
        <div style="margin-bottom:20px;">
            <label class="field-label">Section Title</label>
            <input type="text" class="field-input"
                   name="sections[{{ $section->id }}][title]"
                   value="{{ old("sections.{$section->id}.title", $section->title) }}">
        </div>
        <hr class="section-divider">
        <p class="block-label">🏢 Companies</p>
        @php $items = $section->extra['items'] ?? []; @endphp
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:16px;">
        @foreach($items as $i => $item)
        @php $existingLogo = $item['logo'] ?? ''; @endphp
        <div class="card-item" style="padding:16px; display:flex; flex-direction:column; gap:12px;">
            <div class="card-item-header" style="margin-bottom:0;">Company {{ $i + 1 }}</div>

            {{-- Logo preview + upload zone --}}
            <div>
                <label class="field-label">Logo Image</label>
                <div class="logo-upload-zone"
                     onclick="document.getElementById('logo_file_{{ $section->id }}_{{ $i }}').click()"
                     style="border:2px dashed #CBD5E1; border-radius:10px; padding:16px;
                            display:flex; flex-direction:column; align-items:center;
                            justify-content:center; gap:8px; cursor:pointer;
                            background:#f8fafc; min-height:120px; transition:border-color .15s;"
                     onmouseover="this.style.borderColor='#0EA5E9'"
                     onmouseout="this.style.borderColor='#CBD5E1'">

                    {{-- Preview image (shows existing or newly picked) --}}
                    <img id="logo_preview_{{ $section->id }}_{{ $i }}"
                         src="{{ $existingLogo ? Storage::url($existingLogo) : '' }}"
                         alt=""
                         style="max-height:70px; max-width:100%; object-fit:contain; border-radius:6px;
                                display:{{ $existingLogo ? 'block' : 'none' }};">

                    {{-- Upload placeholder (hidden when image exists) --}}
                    <div id="logo_placeholder_{{ $section->id }}_{{ $i }}"
                         style="display:{{ $existingLogo ? 'none' : 'flex' }}; flex-direction:column;
                                align-items:center; gap:4px; color:#94a3b8;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.5" style="opacity:.6">
                            <rect x="3" y="3" width="18" height="18" rx="3"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <path d="M21 15l-5-5L5 21"/>
                        </svg>
                        <span style="font-size:12px; font-weight:600;">Click to upload</span>
                        <span style="font-size:11px;">PNG, JPG, SVG, WEBP</span>
                    </div>

                    {{-- Change label shown below existing image --}}
                    <span id="logo_change_{{ $section->id }}_{{ $i }}"
                          style="font-size:11px; color:#64748b;
                                 display:{{ $existingLogo ? 'block' : 'none' }};">
                        Click to change
                    </span>
                </div>

                {{-- Hidden file input --}}
                <input type="file"
                       id="logo_file_{{ $section->id }}_{{ $i }}"
                       name="sections[{{ $section->id }}][extra][items][{{ $i }}][logo_file]"
                       accept="image/*"
                       style="display:none;"
                       onchange="previewLogo(this, '{{ $section->id }}', '{{ $i }}')">

                {{-- Keep existing path so controller knows current value --}}
                <input type="hidden"
                       name="sections[{{ $section->id }}][extra][items][{{ $i }}][logo]"
                       value="{{ $existingLogo }}">
            </div>

            {{-- Company name --}}
            <div>
                <label class="field-label">Company Name</label>
                <input type="text" class="field-input"
                       name="sections[{{ $section->id }}][extra][items][{{ $i }}][name]"
                       value="{{ old("sections.{$section->id}.extra.items.{$i}.name", $item['name'] ?? '') }}"
                       placeholder="e.g. Orange Group">
            </div>
        </div>
        @endforeach
        </div>

        {{-- ════ TYPE: customer_support ════ --}}
@elseif($type === 'customer_support')
<div class="fields-grid">
    <div>
        <label class="field-label">Card Heading</label>
        <input type="text" class="field-input"
               name="sections[{{ $section->id }}][title]"
               value="{{ old("sections.{$section->id}.title", $section->title) }}"
               placeholder="e.g. We're here to help">
    </div>
    <div>
        <label class="field-label">Subtitle / Description</label>
        <input type="text" class="field-input"
               name="sections[{{ $section->id }}][subtitle]"
               value="{{ old("sections.{$section->id}.subtitle", $section->subtitle) }}"
               placeholder="e.g. Reach out to our support team and we'll get back shortly.">
    </div>
    <div>
        <label class="field-label">Email Address</label>
        <input type="email" class="field-input"
               name="sections[{{ $section->id }}][extra][email]"
               value="{{ old("sections.{$section->id}.extra.email", $section->extra['email'] ?? '') }}"
               placeholder="e.g. info@pv.market">
    </div>
    <div>
        <label class="field-label">Phone Number</label>
        <input type="text" class="field-input"
               name="sections[{{ $section->id }}][extra][phone]"
               value="{{ old("sections.{$section->id}.extra.phone", $section->extra['phone'] ?? '') }}"
               placeholder="e.g. +971 523825549">
    </div>
</div>

        {{-- ════ TYPE: default ════ --}}
        @else
        <div class="fields-grid">
            <div>
                <label class="field-label">Title</label>
                <input type="text" class="field-input"
                       name="sections[{{ $section->id }}][title]"
                       value="{{ old("sections.{$section->id}.title", $section->title) }}">
            </div>
            <div>
                <label class="field-label">Subtitle</label>
                <input type="text" class="field-input"
                       name="sections[{{ $section->id }}][subtitle]"
                       value="{{ old("sections.{$section->id}.subtitle", $section->subtitle) }}">
            </div>
            <div class="full-width">
                <label class="field-label">Description</label>
                <textarea class="field-input" rows="3" style="resize:vertical;"
                          name="sections[{{ $section->id }}][description]">{{ old("sections.{$section->id}.description", $section->description) }}</textarea>
            </div>
            <div>
                <label class="field-label">Button Text</label>
                <input type="text" class="field-input"
                       name="sections[{{ $section->id }}][button_text]"
                       value="{{ old("sections.{$section->id}.button_text", $section->button_text) }}">
            </div>
            <div>
                <label class="field-label">Button Link</label>
                <input type="text" class="field-input"
                       name="sections[{{ $section->id }}][button_link]"
                       value="{{ old("sections.{$section->id}.button_link", $section->button_link) }}">
            </div>

            @if($page === 'contact' && $section->section === 'hero')
            <div style="grid-column:1/-1; margin-top:8px; border-top:1px dashed var(--border); padding-top:16px;">
                <p class="block-label">📞 Contact Information</p>
                <div class="contact-group">
                    <p class="contact-group-title">✉️ Email</p>
                    <div class="fields-grid">
                        <div>
                            <label class="field-label">Email Address</label>
                            <input type="email" class="field-input"
                                   name="sections[{{ $section->id }}][extra][email]"
                                   value="{{ old("sections.{$section->id}.extra.email", $section->extra['email'] ?? '') }}">
                        </div>
                        <div>
                            <label class="field-label">Email Sub-label</label>
                            <input type="text" class="field-input"
                                   name="sections[{{ $section->id }}][extra][email_label]"
                                   value="{{ old("sections.{$section->id}.extra.email_label", $section->extra['email_label'] ?? '') }}">
                        </div>
                    </div>
                </div>
                <div class="contact-group">
                    <p class="contact-group-title">📞 Phone</p>
                    <div class="fields-grid">
                        <div>
                            <label class="field-label">Phone Number</label>
                            <input type="text" class="field-input"
                                   name="sections[{{ $section->id }}][extra][phone]"
                                   value="{{ old("sections.{$section->id}.extra.phone", $section->extra['phone'] ?? '') }}">
                        </div>
                        <div>
                            <label class="field-label">Phone Sub-label</label>
                            <input type="text" class="field-input"
                                   name="sections[{{ $section->id }}][extra][phone_label]"
                                   value="{{ old("sections.{$section->id}.extra.phone_label", $section->extra['phone_label'] ?? '') }}">
                        </div>
                    </div>
                </div>
                <div class="contact-group">
                    <p class="contact-group-title">📍 Location</p>
                    <div>
                        <label class="field-label">Address</label>
                        <input type="text" class="field-input"
                               name="sections[{{ $section->id }}][extra][address]"
                               value="{{ old("sections.{$section->id}.extra.address", $section->extra['address'] ?? '') }}">
                    </div>
                </div>
                <div class="contact-group">
                    <p class="contact-group-title">🔗 Social Links (Optional)</p>
                    <div class="fields-grid">
                        @foreach(['facebook'=>'Facebook','twitter'=>'Twitter','instagram'=>'Instagram','linkedin'=>'LinkedIn','youtube'=>'YouTube'] as $sKey => $sLabel)
                        <div>
                            <label class="field-label">{{ $sLabel }}</label>
                            <input type="text" class="field-input"
                                   name="sections[{{ $section->id }}][extra][social_{{ $sKey }}]"
                                   placeholder="https://..."
                                   value="{{ old("sections.{$section->id}.extra.social_{$sKey}", $section->extra['social_'.$sKey] ?? '') }}">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

    </div>{{-- /.section-card --}}
    @empty
        <div class="section-card" style="text-align:center; color:var(--muted); padding:40px;">
            No sections found. Run the seeder to create them.
        </div>
    @endforelse

    {{-- SEO Settings --}}
    <div class="section-card">
        <h2 style="font-size:15px; font-weight:700; margin:0 0 16px;">SEO Settings</h2>
        <div class="fields-grid">
            <div>
                <label class="field-label">SEO Title</label>
                <input type="text" class="field-input" name="seo_title"
                       value="{{ old('seo_title', $setting->seo_title) }}">
            </div>
            <div>
                <label class="field-label">SEO Keywords</label>
                <input type="text" class="field-input" name="seo_keywords"
                       value="{{ old('seo_keywords', $setting->seo_keywords) }}">
            </div>
            <div class="full-width">
                <label class="field-label">SEO Description</label>
                <textarea class="field-input" name="seo_description" rows="3"
                          style="resize:vertical;">{{ old('seo_description', $setting->seo_description) }}</textarea>
            </div>
            <div class="full-width">
                <label style="display:flex; align-items:center; gap:8px; font-size:14px; cursor:pointer;">
                    <input type="checkbox" name="is_published"
                           {{ $setting->is_published ? 'checked' : '' }}>
                    Published (visible on site)
                </label>
            </div>
        </div>
    </div>

    <div style="display:flex; gap:12px; margin-bottom:40px;">
        <button type="submit"
                style="background:var(--primary); color:white; border:none; padding:10px 24px;
                       border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">
            Save Changes
        </button>
        <a href="{{ route('admin.page-sections.pages', $marketId) }}"
           style="background:white; color:var(--text); border:1px solid var(--border);
                  padding:10px 24px; border-radius:8px; font-size:14px; font-weight:600;
                  text-decoration:none; display:inline-flex; align-items:center;">
            Cancel
        </a>
    </div>

</form>

<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
(function () {

    var quillInstances = {};

    var toolbarFull = [
        [{ header: [1, 2, 3, false] }],
        ['bold', 'italic', 'underline'],
        [{ color: [] }],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['link'],
        ['clean']
    ];

    var toolbarSimple = [
        ['bold', 'italic', 'underline'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['link'],
        ['clean']
    ];

    window.initQuill = function (editorEl, inputEl, toolbar) {
        var quill = new Quill(editorEl, {
            theme: 'snow',
            placeholder: 'Write content here...',
            modules: { toolbar: toolbar }
        });

        var existing = inputEl ? inputEl.value.trim() : '';
        if (existing !== '') {
            setTimeout(function () {
                quill.clipboard.dangerouslyPasteHTML(existing);
                quill.blur();
            }, 50);
        }

        quill.on('text-change', function () {
            if (inputEl) {
                var html = quill.root.innerHTML;
                inputEl.value = (html === '<p><br></p>') ? '' : html;
            }
        });

        return quill;
    };

    // ── Init richtext + text_block editors ──
    document.querySelectorAll('[id^="quill_editor_"]').forEach(function (editorEl) {
        if (editorEl.id.startsWith('quill_editor_faq_')) return;
        var sectionId = editorEl.id.replace('quill_editor_', '');
        var inputEl   = document.getElementById('quill_input_' + sectionId);
        if (!inputEl) return;
        quillInstances['section_' + sectionId] = window.initQuill(editorEl, inputEl, toolbarFull);
    });

    // ── Init sections type editors ──
    document.querySelectorAll('[id^="sec_editor_"]').forEach(function (editorEl) {
        var key     = editorEl.id.replace('sec_editor_', '');
        var inputEl = document.getElementById('sec_input_' + key);
        if (!inputEl) return;
        quillInstances['sec_' + key] = window.initQuill(editorEl, inputEl, toolbarFull);
    });

    // ── Init FAQ answer editors ──
    document.querySelectorAll('[id^="quill_editor_faq_"]').forEach(function (editorEl) {
        var key     = editorEl.id.replace('quill_editor_faq_', '');
        var inputEl = document.getElementById('quill_input_faq_' + key);
        if (!inputEl) return;
        quillInstances['faq_' + key] = window.initQuill(editorEl, inputEl, toolbarSimple);
    });

    // ── Safety net: sync all editors on submit ──
    var form = document.querySelector('form[method="POST"]');
    if (form) {
        form.addEventListener('submit', function () {
            document.querySelectorAll('[id^="quill_editor_"]').forEach(function (editorEl) {
                var qi = Quill.find(editorEl);
                if (!qi) return;
                var inputId = editorEl.id.startsWith('quill_editor_faq_')
                    ? 'quill_input_faq_' + editorEl.id.replace('quill_editor_faq_', '')
                    : 'quill_input_'     + editorEl.id.replace('quill_editor_', '');
                var inp = document.getElementById(inputId);
                if (inp) { var h = qi.root.innerHTML; inp.value = (h === '<p><br></p>') ? '' : h; }
            });
            document.querySelectorAll('[id^="sec_editor_"]').forEach(function (editorEl) {
                var qi = Quill.find(editorEl);
                if (!qi) return;
                var inp = document.getElementById('sec_input_' + editorEl.id.replace('sec_editor_', ''));
                if (inp) { var h = qi.root.innerHTML; inp.value = (h === '<p><br></p>') ? '' : h; }
            });
        });
    }

    // ════ SHIPPING TABLE ════

    // ── helpers ──────────────────────────────────────────────────────────────

    /** Return current column labels from the header inputs for a sectionId */
    function getShippingCols(sectionId) {
        var colsWrap = document.getElementById('shipping_cols_' + sectionId);
        if (!colsWrap) return [];
        return Array.from(colsWrap.querySelectorAll('.shipping-col-wrap input'))
                    .map(function (el) { return el.value || ''; });
    }

    /** Rebuild every price-cell label inside all data rows to match current headers */
    function syncColLabels(sectionId) {
        var cols = getShippingCols(sectionId);
        var container = document.getElementById('shipping_rows_' + sectionId);
        if (!container) return;
        container.querySelectorAll('.shipping-row').forEach(function (row) {
            var cells = row.querySelectorAll('.shipping-price-cell');
            cells.forEach(function (cell, ci) {
                var lbl = cell.querySelector('.field-label');
                if (lbl) lbl.textContent = cols[ci] || ('Col ' + (ci + 1));
            });
        });
    }

    /** Reindex all name attrs after rows are added/removed */
    window.renumberShippingRows = function (sectionId) {
        var container = document.getElementById('shipping_rows_' + sectionId);
        if (!container) return;
        container.querySelectorAll('.shipping-row').forEach(function (row, ri) {
            row.querySelectorAll('input').forEach(function (el) {
                if (el.name) {
                    el.name = el.name.replace(
                        /sections\[([^\]]+)\]\[extra\]\[rows\]\[\d+\]/,
                        'sections[$1][extra][rows][' + ri + ']'
                    );
                }
            });
        });
    };

    /** Reindex column header name attrs after add/remove col */
    function renumberShippingCols(sectionId) {
        var colsWrap = document.getElementById('shipping_cols_' + sectionId);
        if (!colsWrap) return;
        colsWrap.querySelectorAll('.shipping-col-wrap input').forEach(function (el, ci) {
            el.name = 'sections[' + sectionId + '][extra][columns][' + ci + ']';
            el.dataset.colIndex = ci;
        });
        // also reindex price inputs in every data row
        var container = document.getElementById('shipping_rows_' + sectionId);
        if (!container) return;
        container.querySelectorAll('.shipping-row').forEach(function (row, ri) {
            row.querySelectorAll('.shipping-price-cell input').forEach(function (el, ci) {
                el.name = 'sections[' + sectionId + '][extra][rows][' + ri + '][prices][' + ci + ']';
            });
        });
    }

    // ── Add Column ───────────────────────────────────────────────────────────

    window.addShippingColumn = function (sectionId) {
        var colsWrap  = document.getElementById('shipping_cols_' + sectionId);
        var container = document.getElementById('shipping_rows_' + sectionId);
        if (!colsWrap) return;

        var ci = colsWrap.querySelectorAll('.shipping-col-wrap').length;

        // 1. Add header input
        var wrap = document.createElement('div');
        wrap.className = 'shipping-col-wrap';
        wrap.style.cssText = 'flex:1; min-width:80px; position:relative;';
        wrap.innerHTML = `
            <input type="text" class="field-input"
                   name="sections[${sectionId}][extra][columns][${ci}]"
                   data-col-index="${ci}"
                   value="New City"
                   style="font-size:12px; font-weight:700; text-align:center;
                          background:#fff; border-color:#bfdbfe; width:100%; box-sizing:border-box;"
                   placeholder="City">`;
        colsWrap.appendChild(wrap);

        // 2. Add a price cell to every existing data row
        if (container) {
            container.querySelectorAll('.shipping-row').forEach(function (row, ri) {
                var pricesWrap = row.querySelector('.shipping-prices-wrap');
                if (!pricesWrap) return;
                var cell = document.createElement('div');
                cell.className = 'shipping-price-cell';
                cell.style.cssText = 'flex:1; min-width:80px;';
                cell.innerHTML = `
                    <label class="field-label" style="text-align:center;">New City</label>
                    <input type="number" class="field-input price-input"
                           name="sections[${sectionId}][extra][rows][${ri}][prices][${ci}]"
                           placeholder="0" min="0">`;
                pricesWrap.appendChild(cell);
            });
        }

        // 3. Keep col header input live-synced to row labels
        var newInput = wrap.querySelector('input');
        newInput.addEventListener('input', function () { syncColLabels(sectionId); });
    };

    // ── Remove Last Column ───────────────────────────────────────────────────

    window.removeShippingColumn = function (sectionId) {
        var colsWrap  = document.getElementById('shipping_cols_' + sectionId);
        var container = document.getElementById('shipping_rows_' + sectionId);
        if (!colsWrap) return;

        var colWraps = colsWrap.querySelectorAll('.shipping-col-wrap');
        if (colWraps.length <= 1) {
            alert('You need at least one column.');
            return;
        }

        // Remove last header
        colWraps[colWraps.length - 1].remove();

        // Remove last price cell from every data row
        if (container) {
            container.querySelectorAll('.shipping-row').forEach(function (row) {
                var cells = row.querySelectorAll('.shipping-price-cell');
                if (cells.length > 0) cells[cells.length - 1].remove();
            });
        }

        renumberShippingCols(sectionId);
    };

    // ── Add Row ──────────────────────────────────────────────────────────────

    window.addShippingRow = function (sectionId) {
        var container = document.getElementById('shipping_rows_' + sectionId);
        if (!container) return;

        var cols = getShippingCols(sectionId);
        var ri   = container.querySelectorAll('.shipping-row').length;

        var priceCells = cols.map(function (label, ci) {
            return `
                <div class="shipping-price-cell" style="flex:1; min-width:80px;">
                    <label class="field-label" style="text-align:center;">${label || ('Col ' + (ci+1))}</label>
                    <input type="number" class="field-input price-input"
                           name="sections[${sectionId}][extra][rows][${ri}][prices][${ci}]"
                           placeholder="0" min="0">
                </div>`;
        }).join('');

        var row = document.createElement('div');
        row.className = 'shipping-row';
        row.id = 'shipping_row_' + sectionId + '_' + ri;
        row.innerHTML = `
            <div style="display:flex; gap:8px; align-items:flex-end;">
                <div style="width:180px; flex-shrink:0;">
                    <label class="field-label">Vehicle Type</label>
                    <input type="text" class="field-input"
                           name="sections[${sectionId}][extra][rows][${ri}][vehicle]"
                           placeholder="e.g. 3 TON">
                </div>
                <div class="shipping-prices-wrap" style="display:flex; gap:8px; flex:1;">
                    ${priceCells}
                </div>
                <div style="width:36px; flex-shrink:0; display:flex; align-items:flex-end; padding-bottom:2px;">
                    <button type="button" onclick="removeShippingRow(this)"
                            style="background:none; border:none; cursor:pointer;
                                   color:#ef4444; font-size:20px; line-height:1;
                                   width:36px; height:36px; display:flex;
                                   align-items:center; justify-content:center;
                                   border-radius:6px;"
                            onmouseover="this.style.background='#fef2f2'"
                            onmouseout="this.style.background='none'"
                            title="Remove row">✕</button>
                </div>
            </div>`;
        container.appendChild(row);
        renumberShippingRows(sectionId);
    };

    // ── Remove Row ───────────────────────────────────────────────────────────

    window.removeShippingRow = function (btn) {
        var row = btn.closest('.shipping-row');
        if (!row) return;
        var container = row.parentElement;
        row.remove();
        if (container) {
            var sectionId = container.id.replace('shipping_rows_', '');
            renumberShippingRows(sectionId);
        }
    };

    // ── Wire up live label sync for existing header inputs on page load ──────
    document.querySelectorAll('[id^="shipping_cols_"]').forEach(function (colsWrap) {
        var sectionId = colsWrap.id.replace('shipping_cols_', '');
        colsWrap.querySelectorAll('input').forEach(function (input) {
            input.addEventListener('input', function () { syncColLabels(sectionId); });
        });
    });

    // ════ FAQ ════

    window.addFaqItem = function (sectionId) {
        var container = document.getElementById('faq_container_' + sectionId);
        if (!container) return;
        var i        = container.querySelectorAll('.card-item').length;
        var editorId = 'quill_editor_faq_' + sectionId + '_' + i;
        var inputId  = 'quill_input_faq_'  + sectionId + '_' + i;

        var card = document.createElement('div');
        card.className = 'card-item';
        card.id = 'faq_item_' + sectionId + '_' + i;
        card.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <span class="card-item-header" style="margin:0;">Question ${i + 1}</span>
                <button type="button" onclick="removeFaqItem(this)"
                        style="background:none;border:none;cursor:pointer;color:#ef4444;font-size:18px;line-height:1;">✕</button>
            </div>
            <div style="display:grid;gap:12px;">
                <div>
                    <label class="field-label">Question</label>
                    <input type="text" class="field-input"
                           name="sections[${sectionId}][extra][items][${i}][question]"
                           placeholder="e.g. What is pv.market?">
                </div>
                <div>
                    <label class="field-label">Answer</label>
                    <textarea name="sections[${sectionId}][extra][items][${i}][answer]"
                              id="${inputId}" style="display:none;"></textarea>
                    <div class="quill-wrapper"><div id="${editorId}"></div></div>
                </div>
            </div>`;
        container.appendChild(card);

        var editorEl = document.getElementById(editorId);
        var inputEl  = document.getElementById(inputId);
        if (editorEl && inputEl) {
            quillInstances['faq_' + sectionId + '_' + i] = window.initQuill(editorEl, inputEl, toolbarSimple);
        }
        renumberFaqItems(sectionId);
    };

    window.removeFaqItem = function (btn) {
        var card = btn.closest('.card-item');
        if (!card) return;
        var container = card.parentElement;
        card.remove();
        if (container) {
            var sectionId = container.id.replace('faq_container_', '');
            renumberFaqItems(sectionId);
        }
    };

    window.renumberFaqItems = function (sectionId) {
        var container = document.getElementById('faq_container_' + sectionId);
        if (!container) return;
        container.querySelectorAll('.card-item').forEach(function (card, idx) {
            var h = card.querySelector('.card-item-header');
            if (h) h.textContent = 'Question ' + (idx + 1);
            card.querySelectorAll('input,textarea').forEach(function (el) {
                if (el.name) el.name = el.name.replace(
                    /sections\[([^\]]+)\]\[extra\]\[items\]\[\d+\]/,
                    'sections[$1][extra][items][' + idx + ']'
                );
            });
        });
    };

    // ════ SECTIONS TYPE ════

    window.addSectionItem = function (sectionId) {
        var container = document.getElementById('sections_container_' + sectionId);
        if (!container) return;
        var i        = container.querySelectorAll('.card-item').length;
        var editorId = 'sec_editor_' + sectionId + '_' + i;
        var inputId  = 'sec_input_'  + sectionId + '_' + i;

        var card = document.createElement('div');
        card.className = 'card-item';
        card.id = 'section_item_' + sectionId + '_' + i;
        card.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <span class="card-item-header" style="margin:0;">Section ${i + 1}</span>
                <button type="button" onclick="removeSectionItem(this)"
                        style="background:none;border:none;cursor:pointer;color:#ef4444;font-size:18px;line-height:1;">✕</button>
            </div>
            <div style="display:grid;gap:12px;">
                <div>
                    <label class="field-label">Section Title</label>
                    <input type="text" class="field-input"
                           name="sections[${sectionId}][extra][items][${i}][title]"
                           placeholder="e.g. General Disclaimer">
                </div>
                <div>
                    <label class="field-label">Content</label>
                    <textarea name="sections[${sectionId}][extra][items][${i}][content]"
                              id="${inputId}" style="display:none;"></textarea>
                    <div class="quill-wrapper"><div id="${editorId}"></div></div>
                </div>
            </div>`;
        container.appendChild(card);

        var editorEl = document.getElementById(editorId);
        var inputEl  = document.getElementById(inputId);
        if (editorEl && inputEl) {
            quillInstances['sec_' + sectionId + '_' + i] = window.initQuill(editorEl, inputEl, toolbarFull);
        }
        renumberSectionItems(sectionId);
    };

    window.removeSectionItem = function (btn) {
        var card = btn.closest('.card-item');
        if (!card) return;
        var container = card.parentElement;
        card.remove();
        if (container) {
            var sectionId = container.id.replace('sections_container_', '');
            renumberSectionItems(sectionId);
        }
    };

    window.renumberSectionItems = function (sectionId) {
        var container = document.getElementById('sections_container_' + sectionId);
        if (!container) return;
        container.querySelectorAll('.card-item').forEach(function (card, idx) {
            var h = card.querySelector('.card-item-header');
            if (h) h.textContent = 'Section ' + (idx + 1);
            card.querySelectorAll('input,textarea').forEach(function (el) {
                if (el.name) el.name = el.name.replace(
                    /sections\[([^\]]+)\]\[extra\]\[items\]\[\d+\]/,
                    'sections[$1][extra][items][' + idx + ']'
                );
            });
        });
    };

    // ════ LOGO IMAGE PREVIEW ════

    window.previewLogo = function (input, sectionId, i) {
        if (!input.files || !input.files[0]) return;
        var file    = input.files[0];
        var preview = document.getElementById('logo_preview_'     + sectionId + '_' + i);
        var holder  = document.getElementById('logo_placeholder_' + sectionId + '_' + i);
        var change  = document.getElementById('logo_change_'      + sectionId + '_' + i);

        var reader = new FileReader();
        reader.onload = function (e) {
            if (preview) {
                preview.src          = e.target.result;
                preview.style.display = 'block';
            }
            if (holder) holder.style.display = 'none';
            if (change) change.style.display = 'block';
        };
        reader.readAsDataURL(file);
    };

// Restore scroll to top after all editors init
    setTimeout(function () { window.scrollTo(0, 0); }, 100);

})();
</script>

@endsection