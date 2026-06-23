@extends('layouts.admin')
@section('title', 'Bid/Fair Price Requests')

@section('styles')
<style>
/* ── Layout ── */
.page-header {
    display: flex; align-items: center;
    justify-content: space-between;
    margin-bottom: 24px; flex-wrap: wrap; gap: 12px;
}
.page-title { font-size: 22px; font-weight: 800; color: var(--text); }

/* ── Controls ── */
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

/* ── Table ── */
.bids-table-wrap {
    background: white; border: 1px solid var(--border);
    border-radius: 12px; overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    max-width: 100%;          
    width: 100%;
}

.bids-table-scroll {
    overflow-x: auto;
    width: 100%;
    max-width: 100%; 
     -webkit-overflow-scrolling: touch;

}
.bids-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 1000px; }

.bids-table thead tr { background: #F8FAFC; border-bottom: 2px solid var(--border); }
.bids-table thead th {
    padding: 14px 12px; text-align: center;
    font-size: 12px; font-weight: 600;
    color: var(--primary-d); white-space: nowrap;
    cursor: pointer; user-select: none;
    border-right: 1px solid var(--border);
    text-transform: uppercase;
    letter-spacing: .4px;
}
.bids-table thead th:last-child { border-right: none; }
.bids-table thead th:hover { background: #EFF6FF; }

.bids-table tbody tr { border-bottom: 1px solid var(--border); transition: background .1s; }
.bids-table tbody tr:nth-child(even) { background: #FAFBFD; }
.bids-table tbody tr:hover { background: #EFF6FF; }
.bids-table tbody tr:last-child { border-bottom: none; }
.bids-table td {
    padding: 14px 12px; text-align: center;
    vertical-align: middle; color: var(--text);
    border-right: 1px solid var(--border);
}
.bids-table td:last-child { border-right: none; }

/* ── S.No & Req ID ── */
.req-id { font-size: 13px; font-weight: 600; color: var(--text); }

/* ── Product name cell ── */
.product-name-cell {
    text-align: left; font-size: 13px;
    color: var(--text); line-height: 1.5;
}

/* ── Quantity ── */
.qty-cell { font-size: 13px; font-weight: 500; }

/* ── Price ── */
.price-cell { font-size: 13px; font-weight: 600; color: var(--text); }

/* ── Request type badges ── */
.badge-request {
    display: inline-block; padding: 5px 14px;
    border-radius: 6px; font-size: 12px; font-weight: 700;
    white-space: nowrap;
}
.badge-bid  { background: #0EA5E9; color: white; }
.badge-fair { background: #06B6D4; color: white; }

/* ── Currency ── */
.currency-cell { font-size: 13px; font-weight: 500; color: var(--muted); }

/* ── Lead time ── */
.lead-time-cell { font-size: 13px; font-weight: 600; }

/* ── Status badges ── */
.badge-status {
    display: inline-block; padding: 5px 14px;
    border-radius: 6px; font-size: 12px; font-weight: 700;
}
.status-pending   { background: #FFF3E0; color: #E65100; border: 1px solid #FFE0B2; }
.status-accepted  { background: #E8F5E9; color: #2E7D32; border: 1px solid #C8E6C9; }
.status-rejected  { background: #FFEBEE; color: #C62828; border: 1px solid #FFCDD2; }
.status-completed { background: #E3F2FD; color: #1565C0; border: 1px solid #BBDEFB; }

/* ── Date cell ── */
.date-cell { font-size: 12px; color: var(--text); line-height: 1.5; white-space: nowrap; }

/* ── Action buttons ── */
.action-wrap { display: flex; align-items: center; justify-content: center; gap: 6px; }
.btn-action {
    width: 32px; height: 32px; border-radius: 8px; border: none;
    cursor: pointer; font-size: 16px;
    display: inline-flex; align-items: center; justify-content: center;
    text-decoration: none; transition: all .15s;
}
.btn-view   { background: #FFF9E6; color: #D97706; border: 1px solid #FDE68A; }
.btn-view:hover   { background: #FEF3C7; }
.btn-delete { background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; }
.btn-delete:hover { background: #FEE2E2; }

/* ── Footer ── */
.table-footer {
    padding: 14px 20px; background: #F8FAFC;
    border-top: 1px solid var(--border);
    font-size: 13px; color: var(--muted);
    display: flex; align-items: center; justify-content: space-between;
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

/* ── Empty ── */
.empty-state { text-align: center; padding: 60px 20px; color: var(--muted); }
.empty-state-icon { font-size: 48px; margin-bottom: 12px; }
.empty-state-text { font-size: 15px; font-weight: 500; }

/* ── Modal ── */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.5); z-index: 1000;
    align-items: center; justify-content: center;
}
.modal-overlay.open { display: flex; }
.modal-box {
    background: white; border-radius: 16px;
    padding: 28px; max-width: 560px; width: 90%;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
    animation: modalIn .2s ease;
}
@keyframes modalIn { from { opacity:0; transform:scale(.95); } to { opacity:1; transform:scale(1); } }
.modal-title { font-size: 18px; font-weight: 800; color: var(--text); margin-bottom: 20px; }
.modal-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border); font-size: 13px; }
.modal-row:last-of-type { border-bottom: none; }
.modal-label { color: var(--muted); font-weight: 500; }
.modal-value { font-weight: 600; color: var(--text); text-align: right; }
.modal-footer { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
.btn-modal-close {
    padding: 9px 20px; background: #F1F5F9; color: var(--text);
    border: 1.5px solid var(--border); border-radius: 8px;
    font-family: inherit; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all .15s;
}
.btn-modal-close:hover { background: var(--border); }
.btn-modal-save {
    padding: 9px 20px; background: var(--primary); color: white;
    border: none; border-radius: 8px;
    font-family: inherit; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: background .15s;
}
.btn-modal-save:hover { background: var(--primary-d); }
.modal-status-select {
    padding: 8px 12px; border: 1.5px solid var(--border);
    border-radius: 8px; font-family: inherit; font-size: 13px;
    outline: none; background: white; width: 100%; margin-top: 12px;
}

/* ── Alert ── */
.alert-success {
    padding: 12px 16px; background: #D1FAE5; color: #065F46;
    border: 1px solid #A7F3D0; border-radius: 8px;
    font-size: 13.5px; margin-bottom: 20px;
}
</style>
@endsection

@section('content')

{{-- Header --}}
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Bid/Fair Price Requests</h1>
    <a href="{{ route('admin.dashboard') }}" class="btn-back">← Back</a>
</div>


@if(session('success'))
    <div class="alert-success">✓ {{ session('success') }}</div>
@endif

{{-- Controls --}}
<div class="controls-bar">
    <div class="search-wrap">
        <span class="search-label">Search:</span>
        <input type="text" class="search-input" id="searchInput"
               placeholder="Search By product or company..."
               value="{{ request('search') }}"
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
<div class="bids-table-wrap">
    <div class="bids-table-scroll">
    <table class="bids-table" id="bidsTable">
        <thead>
            <tr>
                <th onclick="sortTable(0)">S.No </th>
                <th onclick="sortTable(1)">Product Name </th>
                <th onclick="sortTable(2)">Company Name </th>
                <th onclick="sortTable(3)">Quantity </th>
                <th onclick="sortTable(4)">Price Per piece </th>
                <th>Request Type</th>
                <th onclick="sortTable(6)">Currency </th>
                <th onclick="sortTable(7)">Expected Lead Time(Weeks) </th>
                <th>Status</th>
                <th>Assigned Admin</th>
                <th onclick="sortTable(10)">Requested </th>
                <th onclick="sortTable(11)">Completed </th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="bidsTableBody">
            @forelse($bids as $i => $bid)
            <tr data-id="{{ $bid->id }}">

                {{-- S.No --}}
                <td><span class="req-id">{{ $bid->unique_id ?? 'Req' . str_pad($i+1, 5, '0', STR_PAD_LEFT) }}</span></td>

                {{-- Product Name --}}
                <td class="product-name-cell">{{ $bid->product_name ?? '-' }}</td>

                {{-- Company Name --}}
                <td>{{ $bid->company_name ?? '-' }}</td>

                {{-- Quantity --}}
                <td class="qty-cell">
                    {{ $bid->selected_pcs_qty ?? '-' }}
                    {{ $bid->quantity_unit ? $bid->quantity_unit : '' }}
                </td>

                {{-- Price Per Piece --}}
                <td class="price-cell">
                    {{ $bid->bid_price_per_piece ?? '-' }}
                </td>

                {{-- Request Type --}}
                <td>
                    <span class="badge-request {{ $bid->request_type === 'fair request' ? 'badge-fair' : 'badge-bid' }}">
                        {{ $bid->request_type === 'fair request' ? 'Fair Request' : 'Bid Request' }}
                    </span>
                </td>

                {{-- Currency --}}
                <td class="currency-cell">{{ $bid->purchased_currency ?? 'USD' }}</td>

                {{-- Lead Time --}}
                <td class="lead-time-cell">{{ $bid->lead_time ?? '-' }}</td>

                {{-- Status --}}
                <td>
                    @php
                        $sClass = match((int)$bid->status) {
                            0=>'status-pending', 1=>'status-accepted',
                            2=>'status-rejected', 3=>'status-completed',
                            default=>'status-pending'
                        };
                        $sLabel = match((int)$bid->status) {
                            0=>'Pending', 1=>'Accepted',
                            2=>'Rejected', 3=>'Completed',
                            default=>'Pending'
                        };
                    @endphp
                    <span class="badge-status {{ $sClass }}" id="status-badge-{{ $bid->id }}">
                        {{ $sLabel }}
                    </span>
                </td>

                {{-- Assigned Admin --}}
                <td>
                    @if(Auth::user()->isSuperAdmin())
                        <form action="{{ route('admin.bids.assign-admin', $bid->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <select name="admin_id" class="show-select" onchange="this.form.submit()" style="min-width:120px;">
                                <option value="">-- Not Assigned --</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->_id }}" {{ (string)$bid->assigned_admin_id === (string)$admin->_id ? 'selected' : '' }}>
                                        {{ $admin->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @else
                        {{ $bid->assignedAdmin?->name ?? 'Not Assigned' }}
                    @endif
                </td>

                {{-- Requested At --}}
                <td class="date-cell">
                    @if($bid->created_at)
                        {{ \Carbon\Carbon::parse($bid->created_at)->format('n/j/Y') }}<br/>
                        {{ \Carbon\Carbon::parse($bid->created_at)->format('g:i:s A') }}
                    @else -
                    @endif
                </td>

                {{-- Completed At --}}
                <td class="date-cell" id="completed-{{ $bid->id }}">
                    @if($bid->completed_at)
                        {{ \Carbon\Carbon::parse($bid->completed_at)->format('n/j/Y') }}<br/>
                        {{ \Carbon\Carbon::parse($bid->completed_at)->format('g:i:s A') }}
                    @else
                        {{ \Carbon\Carbon::parse($bid->created_at ?? now())->format('n/j/Y') }}<br/>
                        {{ \Carbon\Carbon::parse($bid->created_at ?? now())->format('g:i:s A') }}
                    @endif
                </td>

                {{-- Actions --}}
                <td>
                    <div class="action-wrap">
                        <button class="btn-action btn-view"
                                onclick="openDetail('{{ $bid->id }}')"
                                title="View Details">
                            👁
                        </button>
                        <button class="btn-action btn-delete"
                                onclick="deleteBid('{{ $bid->id }}', this)"
                                title="Delete">
                            ✕
                        </button>
                    </div>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="13">
                    <div class="empty-state">
                        <div class="empty-state-icon">📋</div>
                        <div class="empty-state-text">No bid/fair price requests found.</div>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
 </div>
    <div class="table-footer">
    <span>{{ $bids->firstItem() ?? 0 }}–{{ $bids->lastItem() ?? 0 }} of {{ $bids->total() }} entries</span>
    @if ($bids->hasPages())
    <nav style="display:flex; align-items:center; gap:4px;">
        @if ($bids->onFirstPage())
            <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">‹</span>
        @else
            <a href="{{ $bids->previousPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">‹</a>
        @endif

        @foreach ($bids->getUrlRange(1, $bids->lastPage()) as $page => $url)
            @if ($page == $bids->currentPage())
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--primary-d);background:var(--primary-d);color:white;font-size:13px;font-weight:700;">{{ $page }}</span>
            @elseif ($page == 1 || $page == $bids->lastPage() || abs($page - $bids->currentPage()) <= 2)
                <a href="{{ $url }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:13px;font-weight:500;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">{{ $page }}</a>
            @elseif ($page == $bids->currentPage() - 3 || $page == $bids->currentPage() + 3)
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--muted);font-size:13px;">…</span>
            @endif
        @endforeach

        @if ($bids->hasMorePages())
            <a href="{{ $bids->nextPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">›</a>
        @else
            <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">›</span>
        @endif
    </nav>
    @endif
</div>
</div>

{{-- ── Detail / Status Modal ── --}}
<div class="modal-overlay" id="detailModal">
    <div class="modal-box">
        <div class="modal-title">Bid Request Details</div>

        <div id="modalContent">
            {{-- Filled by JS --}}
        </div>

        <div class="modal-footer">
            <button class="btn-modal-close" onclick="closeModal()">Close</button>
            <button class="btn-modal-save" onclick="saveStatus()">Save Status</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const CSRF    = document.querySelector('meta[name="csrf-token"]').content;
let currentId = null;

const bidsData = @json($bidsJson);

// ── Open detail modal ──
function openDetail(id) {
    currentId    = id;
    const bid    = bidsData.find(b => b.id === id);
    if (!bid) return;

    document.getElementById('modalContent').innerHTML = `
        <div class="modal-row"><span class="modal-label">Request ID</span><span class="modal-value">${bid.unique_id}</span></div>
        <div class="modal-row"><span class="modal-label">Product</span><span class="modal-value">${bid.product_name}</span></div>
        <div class="modal-row"><span class="modal-label">Company</span><span class="modal-value">${bid.company_name}</span></div>
        <div class="modal-row"><span class="modal-label">Quantity</span><span class="modal-value">${bid.selected_pcs_qty} ${bid.quantity_unit}</span></div>
        <div class="modal-row"><span class="modal-label">Bid Price/Piece</span><span class="modal-value">${bid.bid_price_per_piece} ${bid.purchased_currency}</span></div>
        <div class="modal-row"><span class="modal-label">Final Price/Piece</span><span class="modal-value">${bid.final_price_per_pcs}</span></div>
        <div class="modal-row"><span class="modal-label">Request Type</span><span class="modal-value">${bid.request_type}</span></div>
        <div class="modal-row"><span class="modal-label">Lead Time</span><span class="modal-value">${bid.lead_time} Weeks</span></div>
        <div class="modal-row">
            <span class="modal-label">Update Status</span>
            <select class="modal-status-select" id="modalStatusSelect">
                <option value="0" ${bid.status==0?'selected':''}>Pending</option>
                <option value="1" ${bid.status==1?'selected':''}>Accepted</option>
                <option value="2" ${bid.status==2?'selected':''}>Rejected</option>
                <option value="3" ${bid.status==3?'selected':''}>Completed</option>
            </select>
        </div>
    `;

    document.getElementById('detailModal').classList.add('open');
}

function closeModal() {
    document.getElementById('detailModal').classList.remove('open');
    currentId = null;
}

// ── Save status from modal ──
async function saveStatus() {
    if (!currentId) return;
    const status = document.getElementById('modalStatusSelect').value;

    try {
        const res  = await fetch(`/admin/bids/${currentId}/status`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body:    JSON.stringify({ status: parseInt(status) }),
        });
        const data = await res.json();

        if (data.success) {
            const labels = {0:'Pending',1:'Accepted',2:'Rejected',3:'Completed'};
            const classes = {0:'status-pending',1:'status-accepted',2:'status-rejected',3:'status-completed'};
            const badge = document.getElementById(`status-badge-${currentId}`);
            if (badge) {
                badge.textContent = labels[status];
                badge.className   = `badge-status ${classes[status]}`;
            }
            closeModal();
        }
    } catch (e) {
        alert('Network error updating status');
    }
}

// ── Delete bid ──
async function deleteBid(id, btn) {
    if (!confirm('Delete this request?')) return;
    btn.disabled = true;
    try {
        const res  = await fetch(`/admin/bids/${id}`, {
            method:  'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();
        if (data.success) {
            document.querySelector(`tr[data-id="${id}"]`).remove();
        } else {
            btn.disabled = false;
        }
    } catch (e) {
        btn.disabled = false;
        alert('Network error');
    }
}

// ── Search filter ──
function filterTable(q) {
    const rows = document.querySelectorAll('#bidsTableBody tr');
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
    const tbody = document.getElementById('bidsTableBody');
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

// Close modal on overlay click
document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endsection