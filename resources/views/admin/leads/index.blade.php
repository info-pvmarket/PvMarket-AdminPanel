@extends('layouts.admin')
@section('title', 'Leads Management')

@section('styles')
<style>
.page-header {
    display: flex; align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.page-title { font-size: 22px; font-weight: 800; color: var(--text); }

/* Filter dropdown */
.type-filter-select {
    padding: 10px 36px 10px 16px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-family: inherit; font-size: 14px;
    color: var(--text); background: white;
    appearance: none; min-width: 280px;
    cursor: pointer; outline: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394A3B8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 12px center;
    transition: border-color .2s;
}
.type-filter-select:focus { border-color: var(--primary); }

.btn-back {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px; background: #1E293B; color: white;
    border: none; border-radius: 8px; font-family: inherit;
    font-size: 14px; font-weight: 600; cursor: pointer;
    text-decoration: none; transition: background .15s;
}
.btn-back:hover { background: #334155; color: white; }

/* Filter row */
.filter-row {
    display: flex; justify-content: center;
    margin-bottom: 24px;
}

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
.leads-table-wrap {
    background: white; border: 1px solid var(--border);
    border-radius: 12px; overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.leads-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.leads-table thead tr { background: #F8FAFC; border-bottom: 2px solid var(--border); }
.leads-table thead th {
    padding: 14px 12px; text-align: center;
    font-size: 12px; font-weight: 700;
    color: var(--primary-d); text-transform: uppercase;
    letter-spacing: .4px; white-space: nowrap;
    cursor: pointer; user-select: none;
}
.leads-table thead th:hover { background: #EFF6FF; }
.leads-table tbody tr { border-bottom: 1px solid var(--border); transition: background .1s; }
.leads-table tbody tr:hover { background: #F8FAFC; }
.leads-table tbody tr:last-child { border-bottom: none; }
.leads-table td { padding: 14px 12px; text-align: center; vertical-align: middle; color: var(--text); }

/* Lead type badges */
.badge-lead-type {
    display: inline-block; padding: 5px 12px;
    border-radius: 20px; font-size: 12px; font-weight: 700;
    color: white;
}
.lt-1 { background: #EF4444; }
.lt-2 { background: #06B6D4; }
.lt-3 { background: #8B5CF6; }
.lt-4 { background: #10B981; }

/* Lead from badge */
.badge-lead-from {
    display: inline-block; padding: 4px 10px;
    background: var(--primary); color: white;
    border-radius: 6px; font-size: 11px; font-weight: 700;
}

/* Status badges */
.badge-status {
    display: inline-block; padding: 5px 12px;
    border-radius: 6px; font-size: 12px; font-weight: 700;
}
.badge-pending   { background: #FFF3E0; color: #E65100; border: 1px solid #FFE0B2; }
.badge-processed { background: #E3F2FD; color: #1565C0; border: 1px solid #BBDEFB; }
.badge-rejected  { background: #FFEBEE; color: #C62828; border: 1px solid #FFCDD2; }

/* Name cell */
.name-cell { font-weight: 500; color: var(--text); }
.email-cell { font-weight: 700; color: var(--text); }

/* Action buttons */
.btn-action {
    width: 32px; height: 32px;
    border-radius: 8px; border: none;
    cursor: pointer; font-size: 14px;
    display: inline-flex; align-items: center; justify-content: center;
    text-decoration: none; transition: all .15s;
}
.btn-edit { background: #FFF9E6; color: #D97706; border: 1px solid #FDE68A; }
.btn-edit:hover { background: #FEF3C7; }

/* Lead data */
.lead-data-text {
    font-size: 11px; color: var(--muted);
    max-width: 120px; white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis;
    display: block;
}

/* S.No */
.sno { font-size: 13px; font-weight: 600; color: var(--muted); }

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

/* Alert */
.alert-success {
    padding: 12px 16px; background: #D1FAE5; color: #065F46;
    border: 1px solid #A7F3D0; border-radius: 8px;
    font-size: 13.5px; margin-bottom: 20px;
}
</style>
@endsection

@section('content')

{{-- Header --}}


<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Leads Management</h1>
    {{-- Type Filter — centred in remaining space --}}
    <div style="flex:1; display:flex; justify-content:center;">
        <select class="type-filter-select" onchange="filterByType(this.value)"
                style="min-width:280px; max-width:360px; width:100%;">
            <option value="all" {{ request('lead_type','all')==='all' ? 'selected':'' }}>All</option>
            <option value="1"   {{ request('lead_type')=='1' ? 'selected':'' }}>Book Free</option>
            <option value="2"   {{ request('lead_type')=='2' ? 'selected':'' }}>Spot Price</option>
            <option value="3"   {{ request('lead_type')=='3' ? 'selected':'' }}>Generic</option>
        </select>
    </div>
    <a href="{{ url()->previous()  }}" class="btn-back">← Back</a>
</div>

@if(session('success'))
    <div class="alert-success">✓ {{ session('success') }}</div>
@endif



{{-- Controls --}}
<div class="controls-bar">
    <div class="search-wrap">
        <span class="search-label">Search:</span>
        <input type="text" class="search-input" id="searchInput"
               placeholder="Search By Email or Name"
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
<div class="leads-table-wrap">
    <table class="leads-table" id="leadsTable">
        <thead>
            <tr>
                <th onclick="sortTable(0)">S.No ⇅</th>
                <th onclick="sortTable(1)">Name ⇅</th>
                <th onclick="sortTable(2)">Email ⇅</th>
                <th onclick="sortTable(3)">Mobile ⇅</th>
                <th>Lead Type</th>
                <th>Lead Data</th>
                <th>Lead From</th>
                <th onclick="sortTable(7)">Status ⇅</th>
                <th>Assigned Admin</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="leadsTableBody">
            @forelse($leads as $lead)
            <tr data-id="{{ $lead->id }}">

                {{-- S.No --}}
                <td><span class="sno">{{ $leads->firstItem() + $loop->index }}</span></td>

                {{-- Name --}}
                <td class="name-cell">{{ $lead->name ?? '-' }}</td>

                {{-- Email --}}
                <td class="email-cell">{{ $lead->email ?? '-' }}</td>

                {{-- Mobile --}}
                <td>
                    @if($lead->phone)
                        {{ $lead->country_code ? $lead->country_code . ' ' : '' }}{{ $lead->phone }}
                    @else
                        -
                    @endif
                </td>

                {{-- Lead Type --}}
                <td>
                    @php
                        $ltClass = match((int)$lead->lead_type) {
                            1 => 'lt-1', 2 => 'lt-2', 3 => 'lt-3', 4 => 'lt-4', default => 'lt-2'
                        };
                        $ltLabel = match((int)$lead->lead_type) {
                            1 => 'Book Free', 2 => 'Spot Price', 3 => 'Contact', 4 => 'Newsletter', default => 'Lead'
                        };
                    @endphp
                    <span class="badge-lead-type {{ $ltClass }}">{{ $ltLabel }}</span>
                </td>

                {{-- Lead Data --}}
                <td>
                    @if($lead->lead_data)
                        <span class="lead-data-text" title="{{ $lead->lead_data }}">
                            {{ Str::limit($lead->lead_data, 30) }}
                        </span>
                    @else
                        <span style="color:var(--muted);">-</span>
                    @endif
                </td>

                {{-- Lead From --}}
                <td>
                    <span class="badge-lead-from">{{ $lead->lead_from ?? 'Website' }}</span>
                </td>

                {{-- Status --}}
                <td>
                    @php
                        $statusClass = match((int)$lead->status) {
                            0 => 'badge-pending', 1 => 'badge-processed', 2 => 'badge-rejected', default => 'badge-pending'
                        };
                        $statusLabel = match((int)$lead->status) {
                            0 => 'Pending', 1 => 'Processed', 2 => 'Rejected', default => 'Pending'
                        };
                    @endphp
                    <span class="badge-status {{ $statusClass }}" id="status-{{ $lead->id }}">
                        {{ $statusLabel }}
                    </span>
                </td>

                {{-- Assigned Admin --}}
                <td>
                    @if(Auth::user()->isSuperAdmin())
                        <form action="{{ route('admin.leads.assign-admin', $lead->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <select name="admin_id" class="show-select" onchange="this.form.submit()" style="min-width:120px;">
                                <option value="">-- Not Assigned --</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->_id }}" {{ (string)$lead->assigned_admin_id === (string)$admin->_id ? 'selected' : '' }}>
                                        {{ $admin->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @else
                        {{ $lead->assignedAdmin?->name ?? 'Not Assigned' }}
                    @endif
                </td>

                {{-- Action --}}
                <td>
                    <a href="{{ route('admin.leads.edit', $lead->id) }}" class="btn-action btn-edit" title="Edit">
                        ✏️
                    </a>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="10">
                    <div class="empty-state">
                        <div class="empty-state-icon">📋</div>
                        <div class="empty-state-text">No leads found.</div>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="table-footer">
    <span>{{ $leads->firstItem() ?? 0 }}–{{ $leads->lastItem() ?? 0 }} of {{ $leads->total() }} entries</span>

    @if ($leads->hasPages())
    <nav style="display:flex; align-items:center; gap:4px;">
        @if ($leads->onFirstPage())
            <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">‹</span>
        @else
            <a href="{{ $leads->previousPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">‹</a>
        @endif

        @foreach ($leads->getUrlRange(1, $leads->lastPage()) as $page => $url)
            @if ($page == $leads->currentPage())
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--primary-d);background:var(--primary-d);color:white;font-size:13px;font-weight:700;">{{ $page }}</span>
            @elseif ($page == 1 || $page == $leads->lastPage() || abs($page - $leads->currentPage()) <= 2)
                <a href="{{ $url }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:13px;font-weight:500;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">{{ $page }}</a>
            @elseif ($page == $leads->currentPage() - 3 || $page == $leads->currentPage() + 3)
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--muted);font-size:13px;">…</span>
            @endif
        @endforeach

        @if ($leads->hasMorePages())
            <a href="{{ $leads->nextPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">›</a>
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
function filterByType(val) {
    const url = new URL(window.location.href);
    val === 'all' ? url.searchParams.delete('lead_type') : url.searchParams.set('lead_type', val);
    window.location.href = url.toString();
}

function filterTable(q) {
    const rows = document.querySelectorAll('#leadsTableBody tr');
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
    const tbody = document.getElementById('leadsTableBody');
    const rows  = Array.from(tbody.querySelectorAll('tr'));
    const dir   = sortDir[col] === 'asc' ? 'desc' : 'asc';
    sortDir[col] = dir;
    rows.sort((a, b) => {
        const at = a.cells[col]?.textContent.trim() ?? '';
        const bt = b.cells[col]?.textContent.trim() ?? '';
        return dir === 'asc' ? at.localeCompare(bt, undefined, {numeric:true}) : bt.localeCompare(at, undefined, {numeric:true});
    });
    rows.forEach(r => tbody.appendChild(r));
}
</script>
@endsection