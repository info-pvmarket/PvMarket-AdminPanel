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
               placeholder="Search By Email or Name"
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
                <th onclick="sortTable(5)">Pending ⇅</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="visitsTableBody">
            @forelse($visits as $visit)
            @php
                $user    = $visit->user_info;
                $product = $visit->product_info;
            @endphp
            <tr data-id="{{ $visit->id }}">

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
                    @if($product)
                        <span class="product-name-cell" title="{{ lang($product, 'product_name') }}">
    {{ Str::limit(lang($product, 'product_name'), 30) }}...
</span>
                    @else
                        <span style="color:var(--muted);">-</span>
                    @endif
                </td>

                {{-- Visit count (Pending column) --}}
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
    <span>{{ $visits->firstItem() ?? 0 }}–{{ $visits->lastItem() ?? 0 }} of {{ $visits->total() }} entries</span>

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

@endsection

@section('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function applyDateFilter() {
    const from = document.getElementById('dateFrom').value;
    const to   = document.getElementById('dateTo').value;
    const url  = new URL(window.location.href);
    from ? url.searchParams.set('date_from', from) : url.searchParams.delete('date_from');
    to   ? url.searchParams.set('date_to',   to)   : url.searchParams.delete('date_to');
    window.location.href = url.toString();
}

function filterTable(q) {
    const rows = document.querySelectorAll('#visitsTableBody tr');
    q = q.toLowerCase();
    let v = 0;
    rows.forEach(r => {
        const show = r.textContent.toLowerCase().includes(q);
        r.style.display = show ? '' : 'none';
        if (show) v++;
    });
    document.getElementById('countLabel').textContent = `1–${v} of ${rows.length} entries`;
}

function changePageSize(size) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', size);
    window.location.href = url.toString();
}

let sortDir = {};
function sortTable(col) {
    const tbody = document.getElementById('visitsTableBody');
    const rows  = Array.from(tbody.querySelectorAll('tr'));
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
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
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
</script>
@endsection