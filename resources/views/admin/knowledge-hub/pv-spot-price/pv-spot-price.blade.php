@if($mode === 'create' || $mode === 'edit')
@extends('layouts.admin')
{{-- ═══ CREATE / EDIT MODE ═══ --}}

@section('title', $mode === 'create' ? 'Add Spot Price' : 'Edit Spot Price')

@section('styles')
<style>
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; white-space:nowrap; }
    .btn-back:hover { background:#334155; }

    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,.04); }

    /* Top header row */
    .top-row { display:flex; align-items:flex-end; gap:24px; margin-bottom:24px; }

    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-label { font-size:13px; font-weight:600; color:var(--text); }
    .form-label span { color:var(--danger); margin-left:2px; }

    .form-input {
        padding:9px 13px;
        border:1.5px solid var(--border); border-radius:8px;
        font-family:inherit; font-size:13.5px; color:var(--text);
        outline:none; transition:border-color .2s, box-shadow .2s; background:white;
    }

    .form-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .form-input::placeholder { color:#CBD5E1; }

    /* Add More button */
    .add-more-wrap { display:flex; justify-content:flex-end; margin-bottom:14px; }

    .btn-add-more {
        display:inline-flex; align-items:center; gap:6px;
        padding:8px 18px; background:var(--primary); color:white;
        border:none; border-radius:8px; font-size:13.5px; font-weight:600;
        cursor:pointer; font-family:inherit; transition:background .15s, box-shadow .2s;
    }

    .btn-add-more:hover { background:var(--primary-d); box-shadow:0 4px 12px rgba(14,165,233,.3); }

    /* Spot price table */
    .sp-table { width:100%; border-collapse:collapse; }
    .sp-table thead { background:#F0F9FF; }
    .sp-table th { padding:10px 14px; font-size:12px; font-weight:700; color:var(--primary-d); text-transform:uppercase; letter-spacing:.5px; border-bottom:2px solid #BAE6FD; white-space:nowrap; text-align:left; }
    .sp-table th.center { text-align:center; }
    .sp-table td { padding:10px 14px; border-bottom:1px solid #F1F5F9; vertical-align:middle; }
    .sp-table tr:last-child td { border-bottom:none; }
    .sp-table tbody tr:hover td { background:#FAFBFD; }
    .sp-table td.sno { color:var(--muted); font-weight:700; font-size:13px; text-align:center; width:60px; white-space:nowrap; }

    .sp-table input[type="text"],
    .sp-table input[type="number"] {
        width:100%; padding:8px 10px;
        border:1.5px solid var(--border); border-radius:7px;
        font-family:inherit; font-size:13px; color:var(--text);
        outline:none; transition:border-color .2s; background:white;
    }

    .sp-table input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .sp-table input::placeholder { color:#CBD5E1; }

    /* Remove row button */
    .btn-remove-row {
        background:none; border:none; cursor:pointer;
        color:#F97316; padding:4px 6px; border-radius:6px;
        font-size:18px; display:flex; align-items:center; justify-content:center;
        margin:0 auto; transition:all .15s;
    }
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; }

    .btn-remove-row:hover { background:#FEF3C7; color:#DC2626; transform:scale(1.15); }

    /* Save */
    .save-wrap { display:flex; justify-content:flex-end; padding-top:20px; border-top:1px solid var(--border); margin-top:16px; }
    .btn-save { display:inline-flex; align-items:center; gap:8px; padding:10px 28px; background:#10B981; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; transition:background .15s, box-shadow .2s; }
    .btn-save:hover { background:#059669; box-shadow:0 4px 14px rgba(16,185,129,.35); }

    .alert-error { padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">
        {{ $mode === 'create' ? 'Add Spot Price' : 'Edit Spot Price' }}
    </h1>
    <a href="{{ route('admin.knowledge-hub.pv-spot-price.index') }}" class="btn-back">← Back</a>
</div>



<div class="content-panel">

    @if($errors->any())
        <div class="alert-error">{{ $errors->first() }}</div>
    @endif

    <form
        method="POST"
        action="{{ $mode === 'create'
            ? route('admin.knowledge-hub.pv-spot-price.store')
            : route('admin.knowledge-hub.pv-spot-price.update', $record->id) }}"
        id="spotForm"
    >
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        {{-- ── Top: Heading + Upload Date + Add More ── --}}
        <div style="display:flex; align-items:flex-end; gap:24px; margin-bottom:24px; flex-wrap:wrap;">

            <div class="form-group" style="flex:1; min-width:220px;">
                <label class="form-label">Heading <span>*</span></label>
                <input
                    type="text"
                    name="heading"
                    class="form-input"
                    style="width:100%;"
                    placeholder="e.g. Above 3MW (FOB)"
                    value="{{ old('heading', $record->heading ?? '') }}"
                    required
                />
            </div>

            <div class="form-group">
                <label class="form-label">Upload Date <span>*</span></label>
                <input
                    type="date"
                    name="upload_date"
                    class="form-input"
                    value="{{ old('upload_date', isset($record) ? \Carbon\Carbon::parse($record->upload_date)->format('Y-m-d') : date('Y-m-d')) }}"
                    required
                />
            </div>

            <button type="button" class="btn-add-more" id="addMoreBtn">
                + Add More
            </button>

        </div>

        {{-- ── Items Table ── --}}
        <table class="sp-table">
            <thead>
                <tr>
                    <th class="center">S.No</th>
                    <th style="min-width:160px;">Item</th>
                    <th style="min-width:110px;">High</th>
                    <th style="min-width:110px;">Low</th>
                    <th style="min-width:110px;">Average</th>
                    <th style="min-width:110px;">Change</th>
                    <th style="min-width:90px;">Ordering</th>
                    <th class="center" style="width:60px;">Actions</th>
                </tr>
            </thead>
            <tbody id="itemsBody">

                @php
                    $existingItems = $record->items ?? [];
                    if (empty($existingItems)) {
                        $existingItems = [['item'=>'','high'=>'','low'=>'','average'=>'','change'=>'','ordering'=>1]];
                    }
                @endphp

                @foreach($existingItems as $i => $item)
                <tr data-row="{{ $i }}">
                    <td class="sno">{{ $i + 1 }}.</td>
                    <td><input type="text"   name="items[{{ $i }}][item]"     placeholder="e.g. 182mm Mono"  value="{{ $item['item']     ?? '' }}"/></td>
                    <td><input type="number" name="items[{{ $i }}][high]"     placeholder="0.000" step="0.001" value="{{ $item['high']     ?? '' }}"/></td>
                    <td><input type="number" name="items[{{ $i }}][low]"      placeholder="0.000" step="0.001" value="{{ $item['low']      ?? '' }}"/></td>
                    <td><input type="number" name="items[{{ $i }}][average]"  placeholder="0.000" step="0.001" value="{{ $item['average']  ?? '' }}"/></td>
                    <td><input type="number" name="items[{{ $i }}][change]"   placeholder="0.000" step="0.001" value="{{ $item['change']   ?? '' }}"/></td>
                    <td><input type="number" name="items[{{ $i }}][ordering]" placeholder="{{ $i + 1 }}"       value="{{ $item['ordering'] ?? ($i + 1) }}" min="1"/></td>
                    <td>
                        <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Remove">✂</button>
                    </td>
                </tr>
                @endforeach

            </tbody>
        </table>

        <div class="save-wrap">
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

@endsection

@section('scripts')
<script>
    let rowCount = document.querySelectorAll('#itemsBody tr').length;

    document.getElementById('addMoreBtn').addEventListener('click', () => {
        const i     = rowCount;
        rowCount++;
        const tbody = document.getElementById('itemsBody');

        const tr = document.createElement('tr');
        tr.setAttribute('data-row', i);
        tr.innerHTML = `
            <td class="sno">${i + 1}.</td>
            <td><input type="text"   name="items[${i}][item]"     placeholder="e.g. 182mm Mono"  /></td>
            <td><input type="number" name="items[${i}][high]"     placeholder="0.000" step="0.001" /></td>
            <td><input type="number" name="items[${i}][low]"      placeholder="0.000" step="0.001" /></td>
            <td><input type="number" name="items[${i}][average]"  placeholder="0.000" step="0.001" /></td>
            <td><input type="number" name="items[${i}][change]"   placeholder="0.000" step="0.001" /></td>
            <td><input type="number" name="items[${i}][ordering]" placeholder="${i + 1}" value="${i + 1}" min="1" /></td>
            <td><button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Remove">✂</button></td>
        `;
        tbody.appendChild(tr);
        updateNumbers();
    });

    function removeRow(btn) {
        const tbody = document.getElementById('itemsBody');
        if (tbody.querySelectorAll('tr').length > 1) {
            btn.closest('tr').remove();
            updateNumbers();
        }
    }

    function updateNumbers() {
        document.querySelectorAll('#itemsBody tr').forEach((row, i) => {
            row.querySelector('.sno').textContent = (i + 1) + '.';
        });
    }
</script>
@endsection

@else

{{-- ═══ INDEX MODE ═══ --}}

@section('title', 'PV Spot Price')

@section('styles')
<style>
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .table-toolbar { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--border); gap:12px; flex-wrap:wrap; background:#FAFBFD; }
    .search-group { display:flex; align-items:center; gap:8px; }
    .search-group label { font-size:13.5px; color:var(--text); font-weight:600; }
    .search-input-wrap { position:relative; }
    .search-input-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#CBD5E1; pointer-events:none; }
    .search-input { padding:8px 12px 8px 34px; border:1.5px solid var(--border); border-radius:7px; font-family:inherit; font-size:13px; outline:none; width:230px; color:var(--text); transition:border-color .2s; background:white; }
    .search-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .search-input::placeholder { color:#CBD5E1; }
    .entries-group { display:flex; align-items:center; gap:7px; font-size:13.5px; color:var(--muted); font-weight:500; }
    .entries-select { padding:7px 26px 7px 10px; border:1.5px solid var(--border); border-radius:7px; font-family:inherit; font-size:13px; font-weight:600; color:var(--text); background:white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 7px center; -webkit-appearance:none; appearance:none; outline:none; cursor:pointer; }
    .entries-select:hover,.entries-select:focus { border-color:var(--primary); }
    .data-table { width:100%; border-collapse:collapse; }
    .data-table thead { background:#F0F9FF; }
    .data-table th { padding:11px 16px; text-align:left; font-size:12px; font-weight:700; color:var(--primary-d); text-transform:uppercase; letter-spacing:.5px; border-bottom:2px solid #BAE6FD; white-space:nowrap; }
    .data-table th.center,.data-table td.center { text-align:center; }
    .data-table td { padding:14px 16px; font-size:13.5px; color:var(--text); border-bottom:1px solid #F1F5F9; vertical-align:middle; }
    .data-table tr:last-child td { border-bottom:none; }
    .data-table tbody tr:nth-child(odd) td { background:white; }
    .data-table tbody tr:nth-child(even) td { background:#FAFBFD; }
    .data-table tbody tr:hover td { background:#E0F2FE !important; }
    .action-btns { display:flex; align-items:center; justify-content:center; gap:8px; }
    .action-icon { width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; border:1.5px solid transparent; background:none; border-radius:7px; transition:all .15s; text-decoration:none; }
    .action-icon svg { width:15px; height:15px; }
    .action-icon.edit   { color:#D97706; border-color:#FDE68A; background:#FFFBEB; }
    .action-icon.delete { color:#DC2626; border-color:#FECACA; background:#FEF2F2; }
    .action-icon:hover  { transform:translateY(-2px) scale(1.08); box-shadow:0 3px 8px rgba(0,0,0,.12); }
    .action-icon.edit:hover   { background:#FEF3C7; border-color:#F59E0B; }
    .action-icon.delete:hover { background:#FEE2E2; border-color:#EF4444; }
    .table-footer { padding:12px 20px; font-size:13px; color:var(--muted); border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; background:#FAFBFD; }
    .pagination { display:flex; gap:3px; list-style:none; }
    .pagination li a,.pagination li span { display:flex; align-items:center; justify-content:center; min-width:32px; height:32px; padding:0 8px; border-radius:6px; border:1.5px solid var(--border); font-size:13px; font-weight:500; text-decoration:none; color:var(--text); background:white; transition:all .15s; }
    .pagination li.active span { background:var(--primary-d); border-color:var(--primary-d); color:white; }
    .pagination li a:hover { border-color:var(--primary); color:var(--primary); background:var(--primary-l); }
    .empty-state { text-align:center; padding:52px 20px; color:var(--muted); }
    .empty-state svg { width:42px; height:42px; margin:0 auto 12px; opacity:.2; display:block; }
    .empty-state p { font-size:14px; font-weight:500; }
    .alert-success { padding:12px 16px; background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; border-radius:8px; font-size:13.5px; font-weight:500; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">PV Spot Price</h1>
    <a href="{{ route('admin.knowledge-hub.pv-spot-price.create') }}"
       style="display:inline-flex; align-items:center; gap:7px; padding:10px 20px;
              background:var(--primary); color:white; border-radius:8px; font-size:14px;
              font-weight:600; text-decoration:none; white-space:nowrap;"
       onmouseover="this.style.background='var(--primary-d)'"
       onmouseout="this.style.background='var(--primary)'">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Add
    </a>
</div>

@if(session('success'))
    <div class="alert-success">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        {{ session('success') }}
    </div>
@endif

<div class="content-panel">
    <div class="table-toolbar">
        <form method="GET" action="{{ route('admin.knowledge-hub.pv-spot-price.index') }}" class="search-group">
            <label>Search:</label>
            <div class="search-input-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search By Menu..." class="search-input"/>
            </div>
        </form>

        <form method="GET" action="{{ route('admin.knowledge-hub.pv-spot-price.index') }}" class="entries-group">
            Show
            <select name="entries" class="entries-select" onchange="this.form.submit()">
                @foreach([10,25,50,100] as $n)
                    <option value="{{ $n }}" {{ request('entries',10) == $n ? 'selected':'' }}>{{ $n }}</option>
                @endforeach
            </select>
            entries
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
        </form>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="center" style="width:80px;">S.No</th>
<th class="center">Heading</th>
<th class="center" style="width:160px;">Upload Date</th>
<th class="center" style="width:110px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($spotPrices as $index => $sp)
            <tr>
                <td class="center" style="font-weight:700; color:var(--muted); font-size:13px;">
                    {{ $spotPrices->firstItem() + $index }}
                </td>
               <td class="center" style="font-weight:600;">{{ lang($sp, 'heading') }}</td>
<td class="center" style="color:var(--muted);">{{ $sp->upload_date }}</td>
                <td>
                    <div class="action-btns">
                        <a href="{{ route('admin.knowledge-hub.pv-spot-price.edit', $sp->id) }}"
                           class="action-icon edit" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </a>
                        <button class="action-icon delete" title="Delete"
                            onclick="if(confirm('Delete this spot price?')) document.getElementById('del-{{ $sp->id }}').submit();">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </button>
                        <form id="del-{{ $sp->id }}" method="POST"
                              action="{{ route('admin.knowledge-hub.pv-spot-price.destroy', $sp->id) }}"
                              style="display:none;">
                            @csrf @method('DELETE')
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                        <p>No PV spot prices yet. Click <strong>Add</strong> to create one.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="table-footer">
    <span>{{ $spotPrices->firstItem() ?? 0 }}–{{ $spotPrices->lastItem() ?? 0 }} of {{ $spotPrices->total() }} entries</span>

    @if ($spotPrices->hasPages())
    <nav style="display:flex; align-items:center; gap:4px;">
        @if ($spotPrices->onFirstPage())
            <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">‹</span>
        @else
            <a href="{{ $spotPrices->previousPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">‹</a>
        @endif

        @foreach ($spotPrices->getUrlRange(1, $spotPrices->lastPage()) as $page => $url)
            @if ($page == $spotPrices->currentPage())
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--primary-d);background:var(--primary-d);color:white;font-size:13px;font-weight:700;">{{ $page }}</span>
            @elseif ($page == 1 || $page == $spotPrices->lastPage() || abs($page - $spotPrices->currentPage()) <= 2)
                <a href="{{ $url }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:13px;font-weight:500;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">{{ $page }}</a>
            @elseif ($page == $spotPrices->currentPage() - 3 || $page == $spotPrices->currentPage() + 3)
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--muted);font-size:13px;">…</span>
            @endif
        @endforeach

        @if ($spotPrices->hasMorePages())
            <a href="{{ $spotPrices->nextPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">›</a>
        @else
            <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">›</span>
        @endif
    </nav>
    @endif

</div>
</div>

@endsection

@endif