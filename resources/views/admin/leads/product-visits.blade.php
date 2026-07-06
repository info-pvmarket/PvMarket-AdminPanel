@extends('layouts.admin')
@section('title', 'Product Visits')

@section('styles')
<style>
.page-header {
    display: flex; align-items: center;
    justify-content: space-between;
    margin-bottom: 12px; flex-wrap: wrap; gap: 12px;
}
.page-title { font-size: 22px; font-weight: 800; color: var(--text); }

/* Date filters + export */
.header-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

.date-input {
    padding: 8px 12px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-family: inherit;
    font-size: 13px;
    color: var(--text);
    outline: none;
    cursor: pointer;
    transition: border-color .2s;
}
.date-input:focus { border-color: var(--primary); }

.btn-export {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 18px;
    background: var(--primary); color: white;
    border: none; border-radius: 8px;
    font-family: inherit; font-size: 13px;
    font-weight: 600; cursor: pointer;
    text-decoration: none; transition: background .15s;
}
.btn-export:hover { background: var(--primary-d); }

/* Controls */
.controls-bar {
    display: flex; align-items: center;
    justify-content: space-between;
    margin-bottom: 16px; flex-wrap: wrap; gap: 12px;
}
.search-wrap { display: flex; align-items: center; gap: 8px; }
.search-label { font-size: 14px; font-weight: 500; color: var(--text); }
.search-input {
    padding: 8px 14px; border: 1.5px solid var(--border);
    border-radius: 8px; font-family: inherit; font-size: 13px;
    color: var(--text); outline: none; min-width: 220px;
    transition: border-color .2s;
}
.search-input:focus { border-color: var(--primary); }
.show-wrap { display: flex; align-items: center; gap: 8px; font-size: 14px; color: var(--text); }
.show-select { padding: 6px 10px; border: 1.5px solid var(--border); border-radius: 6px; font-family: inherit; font-size: 13px; outline: none; }

/* Table */
.visits-table-wrap {
    background: white; border: 1px solid var(--border);
    border-radius: 12px; overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.visits-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.visits-table thead tr { background: #F8FAFC; border-bottom: 2px solid var(--border); }
.visits-table thead th {
    padding: 14px 12px; text-align: center;
    font-size: 12px; font-weight: 700;
    color: var(--primary-d); text-transform: uppercase;
    letter-spacing: .4px; white-space: nowrap;
    cursor: pointer; user-select: none;
}
.visits-table thead th:hover { background: #EFF6FF; }
.visits-table tbody tr { border-bottom: 1px solid var(--border); transition: background .1s; }
.visits-table tbody tr:hover { background: #F8FAFC; }
.visits-table tbody tr:last-child { border-bottom: none; }
.visits-table td { padding: 14px 12px; text-align: center; vertical-align: middle; color: var(--text); }

/* Name cell */
.name-cell { font-weight: 500; color: var(--text); }
.email-cell { font-weight: 700; color: var(--text); }

/* Product name */
.product-name-cell {
    color: var(--muted); font-size: 13px;
    max-width: 180px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.product-link {
    display: inline-block;
    max-width: 220px;
    padding: 0;
    border: 0;
    background: transparent;
    color: var(--primary-d);
    font: inherit;
    font-weight: 700;
    text-decoration: underline;
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: middle;
}
.product-link:hover { color: var(--primary); }
.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 10px;
    border-radius: 999px;
    background: #ECFDF5;
    color: #047857;
    font-size: 12px;
    font-weight: 700;
}

/* Visit count badge */
.visit-count {
    display: inline-block;
    padding: 4px 12px;
    background: #EFF6FF;
    color: var(--primary-d);
    border-radius: 20px;
    font-size: 13px;
    font-weight: 800;
}

.view-notes-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 0;
    border: 0;
    background: none;
    color: var(--primary);
    font-family: inherit;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}
.visit-notes-detail-row > td {
    padding: 0 14px 16px;
    background: #F8FAFC;
    text-align: left;
}
.visit-notes-expanded {
    margin-top: 4px;
    padding: 14px 16px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: #F9FAFB;
    font-size: 13px;
}
.visit-note-product-card {
    padding: 14px 0;
    border-bottom: 1px solid #F3F4F6;
}
.visit-note-product-card:first-child { padding-top: 0; }
.visit-note-product-card:last-child { padding-bottom: 0; border-bottom: 0; }
.visit-note-product-head {
    display: grid;
    grid-template-columns: minmax(220px, 1fr) auto;
    gap: 16px;
    align-items: start;
    margin-bottom: 12px;
}
.visit-note-product-title {
    color: var(--text);
    font-size: 14px;
    font-weight: 800;
}
.visit-note-product-meta {
    margin-top: 3px;
    color: var(--muted);
    font-size: 12px;
}
.visit-note-status-actions {
    display: flex;
    align-items: flex-start;
    gap: 8px;
}
.visit-expanded-empty {
    color: var(--muted);
    text-align: center;
    padding: 20px;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #1E293B;
    color: white;
    border: none;
    border-radius: 8px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background .15s;
}

.btn-back:hover { background: #334155; color: white; }

/* Action */
.btn-action {
    width: 32px; height: 32px;
    border-radius: 8px; border: none;
    cursor: pointer; font-size: 14px;
    display: inline-flex; align-items: center; justify-content: center;
    transition: all .15s;
}
.btn-delete { background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; }
.btn-delete:hover { background: #FEE2E2; }

/* Footer */
.table-footer {
    padding: 14px 20px; background: #F8FAFC;
    border-top: 1px solid var(--border);
    font-size: 13px; color: var(--muted);
    display: flex; align-items: center; justify-content: space-between;
}

/* Empty */
.empty-state { text-align: center; padding: 60px 20px; color: var(--muted); }
.empty-state-icon { font-size: 48px; margin-bottom: 12px; }
.empty-state-text { font-size: 15px; font-weight: 500; }

.visit-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: rgba(15, 23, 42, .45);
}
.visit-modal-overlay.is-open { display: flex; }
.visit-modal {
    width: min(1120px, 100%);
    max-height: 82vh;
    overflow: hidden;
    background: white;
    border-radius: 12px;
    box-shadow: 0 24px 70px rgba(15, 23, 42, .25);
}
.visit-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 20px;
    border-bottom: 1px solid var(--border);
}
.visit-modal-title { font-size: 18px; font-weight: 800; color: var(--text); }
.visit-modal-close {
    width: 34px;
    height: 34px;
    border: 0;
    border-radius: 8px;
    background: #F1F5F9;
    color: var(--text);
    font-size: 20px;
    cursor: pointer;
}
.visit-modal-close:hover { background: #E2E8F0; }
.visit-modal-body {
    max-height: calc(82vh - 74px);
    overflow: auto;
    padding: 16px 20px 20px;
}
.visit-history-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.visit-history-table th {
    padding: 10px 12px;
    background: #F8FAFC;
    color: var(--primary-d);
    text-align: left;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: .35px;
}
.visit-history-table td {
    padding: 12px;
    border-bottom: 1px solid var(--border);
    color: var(--text);
    text-align: left;
    vertical-align: top;
}
.visit-history-table tr:last-child td { border-bottom: 0; }
.visit-status-select,
.visit-note-input,
.visit-note-textarea {
    width: 100%;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    font-family: inherit;
    font-size: 13px;
    outline: none;
    transition: border-color .15s;
}
.visit-status-select {
    min-width: 126px;
    padding: 8px 10px;
    background: white;
}
.visit-note-input,
.visit-note-textarea {
    min-height: 58px;
    padding: 8px 10px;
    resize: vertical;
}
.visit-status-select:focus,
.visit-note-input:focus,
.visit-note-textarea:focus { border-color: var(--primary); }
.visit-notes-panel {
    min-width: 330px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.note-compose {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 8px;
    align-items: start;
}
.visit-notes-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.visit-note-item {
    padding: 10px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: #F8FAFC;
}
.visit-note-meta {
    margin-bottom: 6px;
    color: var(--muted);
    font-size: 11px;
    font-weight: 700;
}
.visit-note-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
}
.visit-empty-notes {
    padding: 10px;
    border: 1px dashed var(--border);
    border-radius: 8px;
    color: var(--muted);
    font-size: 12px;
}
.btn-save-visit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 78px;
    padding: 8px 12px;
    border: 0;
    border-radius: 8px;
    background: var(--primary);
    color: white;
    font-family: inherit;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}
.btn-save-visit:hover { background: var(--primary-d); }
.btn-save-visit:disabled { opacity: .65; cursor: not-allowed; }
.btn-add-note,
.btn-save-note,
.btn-delete-note {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 66px;
    padding: 8px 10px;
    border: 0;
    border-radius: 8px;
    color: white;
    font-family: inherit;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}
.btn-add-note,
.btn-save-note { background: var(--primary); }
.btn-add-note:hover,
.btn-save-note:hover { background: var(--primary-d); }
.btn-delete-note { background: #DC2626; }
.btn-delete-note:hover { background: #B91C1C; }
.btn-add-note:disabled,
.btn-save-note:disabled,
.btn-delete-note:disabled { opacity: .65; cursor: not-allowed; }
.visit-save-message {
    display: block;
    margin-top: 6px;
    color: #047857;
    font-size: 12px;
    font-weight: 700;
}
.visit-save-message.error { color: #DC2626; }
.visit-note-message {
    grid-column: 1 / -1;
    color: #047857;
    font-size: 12px;
    font-weight: 700;
}
.visit-note-message.error { color: #DC2626; }

@media (max-width: 900px) {
    .visit-note-product-head { grid-template-columns: 1fr; }
    .visit-note-status-actions { flex-wrap: wrap; }
    .visit-notes-panel { min-width: 0; }
    .note-compose { grid-template-columns: 1fr; }
}
</style>
@endsection

@section('content')


{{-- Header: Title left, dates+export right, all in ONE row --}}
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text); flex-shrink:0;">Product Visits</h1>

    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <input type="date" class="date-input" id="dateFrom"
               value="{{ request('date_from') }}"
               onchange="applyDateFilter()"/>

        <input type="date" class="date-input" id="dateTo"
               value="{{ request('date_to') }}"
               onchange="applyDateFilter()"/>

        <a href="{{ route('admin.leads.visits.export') }}?{{ http_build_query(request()->all()) }}"
           class="btn-export">
            📥 Export
        </a>
    <a href="{{ route('admin.dashboard') }}" class="btn-back">← Back</a>
</div>
</div>

{{-- Controls --}}
<div class="controls-bar">
    <div class="search-wrap">
        <span class="search-label">Search:</span>
        <input type="text" class="search-input" id="searchInput"
               placeholder="Search By Email, Name, or Product"
               oninput="filterTable(this.value)"/>
    </div>
    <div class="show-wrap">
        Show
        <select class="show-select" onchange="changePageSize(this.value)">
            <option value="10"  {{ request('per_page',10)==10  ? 'selected':'' }}>10</option>
            <option value="25"  {{ request('per_page',10)==25  ? 'selected':'' }}>25</option>
            <option value="50"  {{ request('per_page',10)==50  ? 'selected':'' }}>50</option>
            <option value="100">100</option>
        </select>
        entries
    </div>
</div>

{{-- Table --}}
<div class="visits-table-wrap">
    <table class="visits-table" id="visitsTable">
        <thead>
            <tr>
                <th onclick="sortTable(0)">S.No ⇅</th>
                <th onclick="sortTable(1)">Name ⇅</th>
                <th onclick="sortTable(2)">Email ⇅</th>
                <th>Mobile</th>
                <th onclick="sortTable(4)">Product ⇅</th>
                <th onclick="sortTable(5)">Visits ⇅</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="visitsTableBody">
            @forelse($visits as $visit)
            @php
                $user = $visit->user_info;
                $latestProductVisit = $visit->latest_product_visit;
                $visitItems = $visit->product_visit_items ?? [];
                $visitProductsTarget = 'visit-products-' . $visit->id;
            @endphp
            <tr class="visit-main-row" data-id="{{ $visit->id }}">

                {{-- S.No --}}
                <td><span style="font-size:13px;font-weight:600;color:var(--muted);">{{ $visits->firstItem() + $loop->index }}</span></td>

                {{-- Name --}}
                <td class="name-cell">{{ $user?->name ?? '-' }}</td>

                {{-- Email --}}
                <td class="email-cell">{{ $user?->email ?? '-' }}</td>

                {{-- Mobile --}}
                <td>{{ $user?->phone ?? '-' }}</td>

                {{-- Product --}}
                <td>
                    @if($latestProductVisit)
                        <button type="button"
                                class="product-link"
                                title="{{ $latestProductVisit['product_name'] }}"
                                data-products-target="{{ $visitProductsTarget }}"
                                onclick="openVisitProducts(this)">
                            {{ Str::limit($latestProductVisit['product_name'], 38) }}
                        </button>
                        <script type="application/json" id="{{ $visitProductsTarget }}">@json($visitItems)</script>
                    @else
                        <span style="color:var(--muted);">-</span>
                    @endif
                </td>

                {{-- Visit count --}}
                <td>
                    <span class="visit-count">{{ $visit->no_of_times ?? 0 }}</span>
                </td>

                {{-- Action --}}
                <td>
                    <button class="btn-action btn-delete"
                            onclick="deleteVisit('{{ $visit->id }}', this)"
                            title="Remove">
                        🗑
                    </button>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="7">
                    <div class="empty-state">
                        <div class="empty-state-icon">👁️</div>
                        <div class="empty-state-text">No product visit records found.</div>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="table-footer">
    <span id="countLabel">{{ $visits->firstItem() ?? 0 }}–{{ $visits->lastItem() ?? 0 }} of {{ $visits->total() }} entries</span>

    @if ($visits->hasPages())
    <nav style="display:flex; align-items:center; gap:4px;">
        @if ($visits->onFirstPage())
            <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">‹</span>
        @else
            <a href="{{ $visits->previousPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">‹</a>
        @endif

        @foreach ($visits->getUrlRange(1, $visits->lastPage()) as $page => $url)
            @if ($page == $visits->currentPage())
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--primary-d);background:var(--primary-d);color:white;font-size:13px;font-weight:700;">{{ $page }}</span>
            @elseif ($page == 1 || $page == $visits->lastPage() || abs($page - $visits->currentPage()) <= 2)
                <a href="{{ $url }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:13px;font-weight:500;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">{{ $page }}</a>
            @elseif ($page == $visits->currentPage() - 3 || $page == $visits->currentPage() + 3)
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--muted);font-size:13px;">…</span>
            @endif
        @endforeach

        @if ($visits->hasMorePages())
            <a href="{{ $visits->nextPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">›</a>
        @else
            <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">›</span>
        @endif
    </nav>
    @endif
</div>
</div>

<div class="visit-modal-overlay" id="visitProductsModal" aria-hidden="true">
    <div class="visit-modal" role="dialog" aria-modal="true" aria-labelledby="visitProductsModalTitle">
        <div class="visit-modal-header">
            <div class="visit-modal-title" id="visitProductsModalTitle">Product Visit History</div>
            <button type="button" class="visit-modal-close" onclick="closeVisitProductsModal()" aria-label="Close">x</button>
        </div>
        <div class="visit-modal-body">
            <table class="visit-history-table">
                <thead>
                    <tr>
                        <th>Visited Date & Time</th>
                        <th>Product</th>
                        <th>Status</th>
                        <th>Action</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody id="visitProductsModalBody"></tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const VISIT_CSRF = document.querySelector('meta[name="csrf-token"]').content;
const footerCountLabel = document.querySelector('.table-footer > span');
if (footerCountLabel && !footerCountLabel.id) {
    footerCountLabel.id = 'countLabel';
}

function applyDateFilter() {
    const from = document.getElementById('dateFrom').value;
    const to   = document.getElementById('dateTo').value;
    const url  = new URL(window.location.href);
    from ? url.searchParams.set('date_from', from) : url.searchParams.delete('date_from');
    to   ? url.searchParams.set('date_to',   to)   : url.searchParams.delete('date_to');
    window.location.href = url.toString();
}

function filterTable(q) {
    const rows = document.querySelectorAll('#visitsTableBody tr.visit-main-row');
    q = q.toLowerCase();
    let v = 0;
    rows.forEach(r => {
        const show = r.textContent.toLowerCase().includes(q);
        r.style.display = show ? '' : 'none';
        if (show) v++;
    });
    document.getElementById('countLabel').textContent = `${v ? 1 : 0}–${v} of ${rows.length} entries`;
}

function changePageSize(size) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', size);
    window.location.href = url.toString();
}

let sortDir = {};
function sortTable(col) {
    const tbody = document.getElementById('visitsTableBody');
    const rows  = Array.from(tbody.querySelectorAll('tr.visit-main-row'));
    const dir   = sortDir[col] === 'asc' ? 'desc' : 'asc';
    sortDir[col] = dir;
    rows.sort((a, b) => {
        const at = a.cells[col]?.textContent.trim() ?? '';
        const bt = b.cells[col]?.textContent.trim() ?? '';
        return dir === 'asc'
            ? at.localeCompare(bt, undefined, {numeric:true})
            : bt.localeCompare(at, undefined, {numeric:true});
    });
    rows.forEach(r => tbody.appendChild(r));
}

async function deleteVisit(id, btn) {
    if (!confirm('Remove this visit record?')) return;
    btn.disabled = true;
    try {
        const res  = await fetch(`/admin/leads/visits/${id}`, {
            method:  'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': VISIT_CSRF },
        });
        const data = await res.json();
        if (data.success) {
            btn.closest('tr').remove();
        } else {
            btn.disabled = false;
            alert('Error removing record');
        }
    } catch (e) {
        btn.disabled = false;
        alert('Network error');
    }
}

function toggleVisitNotes(btn) {
    const detail = document.getElementById(btn.dataset.notesTarget);
    if (!detail) return;

    const open = detail.style.display !== 'none' && detail.style.display !== '';
    detail.style.display = open ? 'none' : 'table-row';
    setNotesArrow(btn, !open);

    if (!open) {
        renderVisitNotesExpanded(detail, btn.dataset.productsTarget);
    }
}

function renderVisitNotesExpanded(detail, productsTarget) {
    const panel = detail.querySelector('.visit-notes-expanded');
    const items = getVisitItems(productsTarget);
    const item = items.find(entry => String(entry.item_index ?? '') === String(detail.dataset.itemIndex));
    panel.dataset.productsTarget = productsTarget;

    if (!item) {
        panel.innerHTML = '<div class="visit-expanded-empty">No product visit notes found.</div>';
        return;
    }

    panel.innerHTML = renderVisitNoteProductCard(item);
}

function renderVisitNoteProductCard(item) {
    const canSave = item.visit_id && item.item_index !== null && item.item_index !== undefined;

    return `
        <div class="visit-note-product-card">
            <div class="visit-note-product-head">
                <div>
                    <div class="visit-note-product-title">${escapeHtml(item.product_name || '-')}</div>
                    <div class="visit-note-product-meta">Visited: ${escapeHtml(item.visited_at || '-')}</div>
                </div>
            </div>
            <div class="visit-notes-panel"
                 data-visit-id="${escapeHtml(item.visit_id || '')}"
                 data-item-index="${escapeHtml(item.item_index ?? '')}"
                 data-can-save="${canSave ? '1' : '0'}">
                <div class="note-compose">
                    <textarea class="visit-note-input" placeholder="Add note..." ${canSave ? '' : 'disabled'}></textarea>
                    <button type="button"
                            class="btn-add-note"
                            ${canSave ? '' : 'disabled'}
                            onclick="addVisitProductNote(this)">
                        Add
                    </button>
                    <span class="visit-note-message" aria-live="polite"></span>
                </div>
                <div class="visit-notes-list">
                    ${renderVisitNotes(item.notes || [], canSave)}
                </div>
            </div>
        </div>
    `;
}

function setNotesArrow(btn, open) {
    const arrow = btn.querySelector('[id^="notes-arrow-"]');
    if (arrow) {
        arrow.textContent = open ? '^' : 'v';
    }
}

function resetNotesArrow(notesTarget) {
    document.querySelectorAll('.view-notes-btn').forEach(btn => {
        if (btn.dataset.notesTarget === notesTarget) {
            setNotesArrow(btn, false);
        }
    });
}

function getVisitItems(productsTarget) {
    const dataEl = document.getElementById(productsTarget);
    try {
        return JSON.parse(dataEl?.textContent || '[]');
    } catch (e) {
        return [];
    }
}

function setVisitItems(productsTarget, items) {
    const dataEl = document.getElementById(productsTarget);
    if (dataEl) {
        dataEl.textContent = JSON.stringify(items);
    }
}

function openVisitProducts(btn) {
    const dataEl = document.getElementById(btn.dataset.productsTarget);
    const modal = document.getElementById('visitProductsModal');
    const body = document.getElementById('visitProductsModalBody');
    let items = [];

    try {
        items = JSON.parse(dataEl?.textContent || '[]');
    } catch (e) {
        items = [];
    }

    body.dataset.productsTarget = btn.dataset.productsTarget;
    body.innerHTML = '';
    if (!items.length) {
        body.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--muted);padding:28px;">No product visit history found.</td></tr>';
    } else {
        items.forEach((item, index) => {
            const status = item.status === 'contacted' ? 'contacted' : 'pending';
            const canSave = item.visit_id && item.item_index !== null && item.item_index !== undefined;
            const itemIndex = item.item_index ?? index;
            const notesCount = Array.isArray(item.notes) ? item.notes.length : 0;
            const notesRowId = `visit-popup-notes-${String(itemIndex).replace(/[^a-zA-Z0-9_-]/g, '-')}`;
            const row = document.createElement('tr');
            row.dataset.visitId = item.visit_id || '';
            row.dataset.itemIndex = itemIndex;
            row.dataset.notesTarget = notesRowId;
            row.innerHTML = `
                <td>${escapeHtml(item.visited_at || '-')}</td>
                <td>${escapeHtml(item.product_name || '-')}</td>
                <td>
                    <select class="visit-status-select">
                        <option value="pending" ${status === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="contacted" ${status === 'contacted' ? 'selected' : ''}>Contacted</option>
                    </select>
                </td>
                <td>
                    <button type="button"
                            class="btn-save-visit"
                            data-visit-id="${escapeHtml(item.visit_id || '')}"
                            data-item-index="${escapeHtml(item.item_index ?? '')}"
                            ${canSave ? '' : 'disabled'}
                            onclick="saveVisitProduct(this)">
                        Save
                    </button>
                    <span class="visit-save-message" aria-live="polite"></span>
                </td>
                <td>
                    <button type="button"
                            class="view-notes-btn"
                            data-products-target="${escapeHtml(btn.dataset.productsTarget)}"
                            data-notes-target="${escapeHtml(notesRowId)}"
                            data-item-index="${escapeHtml(itemIndex)}"
                            onclick="toggleVisitNotes(this)">
                        View Notes (<span class="notes-count">${notesCount}</span>)
                        <span id="notes-arrow-${escapeHtml(notesRowId)}">v</span>
                    </button>
                </td>
            `;
            body.appendChild(row);

            const notesRow = document.createElement('tr');
            notesRow.id = notesRowId;
            notesRow.className = 'visit-notes-detail-row';
            notesRow.dataset.itemIndex = itemIndex;
            notesRow.style.display = 'none';
            notesRow.innerHTML = `
                <td colspan="5">
                    <div class="visit-notes-expanded"
                         data-products-target="${escapeHtml(btn.dataset.productsTarget)}">
                        <div class="visit-expanded-empty">No product visit notes found.</div>
                    </div>
                </td>
            `;
            body.appendChild(notesRow);
        });
    }

    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
}

async function saveVisitProduct(btn) {
    const row = btn.closest('.visit-note-product-card') || btn.closest('tr');
    const status = row.querySelector('.visit-status-select').value;
    const message = row.querySelector('.visit-save-message');
    const visitId = btn.dataset.visitId;
    const itemIndex = btn.dataset.itemIndex;
    const context = btn.closest('[data-products-target]');

    if (!visitId || itemIndex === '') {
        message.textContent = 'Cannot save this row';
        message.classList.add('error');
        return;
    }

    btn.disabled = true;
    message.textContent = 'Saving...';
    message.classList.remove('error');

    try {
        const res = await fetch(`/admin/leads/visits/${visitId}/products/${itemIndex}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': VISIT_CSRF,
            },
            body: JSON.stringify({ status }),
        });
        const data = await res.json();

        if (!res.ok || !data.success) {
            throw new Error(data.message || 'Error saving row');
        }

        updateStoredVisitItemStatus(context?.dataset.productsTarget, itemIndex, status);
        message.textContent = 'Saved';
        setTimeout(() => {
            if (message.textContent === 'Saved') {
                message.textContent = '';
            }
        }, 1600);
    } catch (e) {
        message.textContent = 'Error saving';
        message.classList.add('error');
    } finally {
        btn.disabled = false;
    }
}

async function addVisitProductNote(btn) {
    const panel = btn.closest('.visit-notes-panel');
    const input = panel.querySelector('.visit-note-input');
    const message = panel.querySelector('.visit-note-message');
    const text = input.value.trim();

    if (!text) {
        showNoteMessage(message, 'Enter a note', true);
        return;
    }

    btn.disabled = true;
    showNoteMessage(message, 'Adding...', false);

    try {
        const data = await sendVisitNoteRequest(
            `/admin/leads/visits/${panel.dataset.visitId}/products/${panel.dataset.itemIndex}/notes`,
            'POST',
            { text }
        );

        input.value = '';
        renderNotesIntoPanel(panel, data.notes || []);
        updateStoredVisitItemNotes(panel, data.notes || []);
        showNoteMessage(message, 'Added', false);
    } catch (e) {
        showNoteMessage(message, 'Error adding note', true);
    } finally {
        btn.disabled = false;
    }
}

async function saveVisitProductNote(btn) {
    const noteItem = btn.closest('.visit-note-item');
    const panel = btn.closest('.visit-notes-panel');
    const message = panel.querySelector('.visit-note-message');
    const textarea = noteItem.querySelector('.visit-note-textarea');
    const text = textarea.value.trim();

    if (!text) {
        showNoteMessage(message, 'Note cannot be empty', true);
        return;
    }

    btn.disabled = true;
    showNoteMessage(message, 'Saving note...', false);

    try {
        const data = await sendVisitNoteRequest(
            `/admin/leads/visits/${panel.dataset.visitId}/products/${panel.dataset.itemIndex}/notes/${noteItem.dataset.noteIndex}`,
            'PATCH',
            { text }
        );

        renderNotesIntoPanel(panel, data.notes || []);
        updateStoredVisitItemNotes(panel, data.notes || []);
        showNoteMessage(message, 'Note saved', false);
    } catch (e) {
        showNoteMessage(message, 'Error saving note', true);
    } finally {
        btn.disabled = false;
    }
}

async function deleteVisitProductNote(btn) {
    if (!confirm('Delete this note?')) return;

    const noteItem = btn.closest('.visit-note-item');
    const panel = btn.closest('.visit-notes-panel');
    const message = panel.querySelector('.visit-note-message');

    btn.disabled = true;
    showNoteMessage(message, 'Deleting note...', false);

    try {
        const data = await sendVisitNoteRequest(
            `/admin/leads/visits/${panel.dataset.visitId}/products/${panel.dataset.itemIndex}/notes/${noteItem.dataset.noteIndex}`,
            'DELETE'
        );

        renderNotesIntoPanel(panel, data.notes || []);
        updateStoredVisitItemNotes(panel, data.notes || []);
        showNoteMessage(message, 'Note deleted', false);
    } catch (e) {
        showNoteMessage(message, 'Error deleting note', true);
        btn.disabled = false;
    }
}

async function sendVisitNoteRequest(url, method, payload = null) {
    const res = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': VISIT_CSRF,
        },
        body: payload ? JSON.stringify(payload) : null,
    });
    const data = await res.json();

    if (!res.ok || !data.success) {
        throw new Error(data.message || 'Note request failed');
    }

    return data;
}

function renderNotesIntoPanel(panel, notes) {
    panel.querySelector('.visit-notes-list').innerHTML = renderVisitNotes(notes, panel.dataset.canSave !== '0');
}

function renderVisitNotes(notes, canSave) {
    if (!Array.isArray(notes) || !notes.length) {
        return '<div class="visit-empty-notes">No notes yet.</div>';
    }

    return notes.map((note, index) => {
        const noteIndex = note.note_index ?? index;
        const meta = note.updated_at && note.updated_at !== '-'
            ? `Updated ${escapeHtml(note.updated_at)}`
            : '';

        return `
            <div class="visit-note-item" data-note-index="${escapeHtml(noteIndex)}">
                ${meta ? `<div class="visit-note-meta">${meta}</div>` : ''}
                <textarea class="visit-note-textarea" ${canSave ? '' : 'disabled'}>${escapeHtml(note.text || '')}</textarea>
                <div class="visit-note-actions">
                    <button type="button"
                            class="btn-save-note"
                            ${canSave ? '' : 'disabled'}
                            onclick="saveVisitProductNote(this)">
                        Save
                    </button>
                    <button type="button"
                            class="btn-delete-note"
                            ${canSave ? '' : 'disabled'}
                            onclick="deleteVisitProductNote(this)">
                        Delete
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

function updateStoredVisitItemStatus(productsTarget, itemIndex, status) {
    if (!productsTarget) return;

    const items = getVisitItems(productsTarget);
    const item = items.find(entry => String(entry.item_index ?? '') === String(itemIndex));
    if (!item) return;

    item.status = status;
    item.status_label = status === 'contacted' ? 'Contacted' : 'Pending';
    setVisitItems(productsTarget, items);
}

function updateStoredVisitItemNotes(panel, notes) {
    const context = panel.closest('[data-products-target]');
    const productsTarget = context?.dataset.productsTarget;
    if (!productsTarget) return;

    const items = getVisitItems(productsTarget);
    const item = items.find(entry => String(entry.item_index ?? '') === String(panel.dataset.itemIndex));
    if (!item) return;

    item.notes = notes;
    setVisitItems(productsTarget, items);
    updateNotesCount(productsTarget, items);
}

function updateNotesCount(productsTarget, items = null) {
    const currentItems = items || getVisitItems(productsTarget);

    document.querySelectorAll('.view-notes-btn').forEach(btn => {
        if (btn.dataset.productsTarget !== productsTarget) return;
        const item = currentItems.find(entry => String(entry.item_index ?? '') === String(btn.dataset.itemIndex ?? ''));
        const notesCount = item && Array.isArray(item.notes) ? item.notes.length : 0;
        btn.querySelectorAll('.notes-count').forEach(el => {
            el.textContent = notesCount;
        });
    });
}

function showNoteMessage(message, text, isError) {
    message.textContent = text;
    message.classList.toggle('error', isError);

    if (!isError && !text.endsWith('...')) {
        setTimeout(() => {
            if (message.textContent === text) {
                message.textContent = '';
            }
        }, 1600);
    }
}

function closeVisitProductsModal() {
    const modal = document.getElementById('visitProductsModal');
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

document.getElementById('visitProductsModal').addEventListener('click', (event) => {
    if (event.target.id === 'visitProductsModal') {
        closeVisitProductsModal();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeVisitProductsModal();
    }
});
</script>
@endsection
