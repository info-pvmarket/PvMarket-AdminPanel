@extends('layouts.admin')

@section('title', 'RFQ Requests')

@section('styles')
<style>
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .table-toolbar { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--border); gap:12px; flex-wrap:wrap; background:#FAFBFD; }
    .search-group { display:flex; align-items:center; gap:8px; }
    .search-group label { font-size:13.5px; color:var(--text); font-weight:600; }
    .search-input-wrap { position:relative; }
    .search-input-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#CBD5E1; pointer-events:none; }
    .search-input { padding:8px 12px 8px 34px; border:1.5px solid var(--border); border-radius:7px; font-family:inherit; font-size:13px; outline:none; width:280px; color:var(--text); transition:border-color .2s; background:white; }
    .search-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .search-input::placeholder { color:#CBD5E1; }
    .filter-group { display:flex; align-items:center; gap:12px; }
    .filter-select { padding:8px 30px 8px 12px; border:1.5px solid var(--border); border-radius:7px; font-family:inherit; font-size:13px; font-weight:500; color:var(--text); background:white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 10px center; -webkit-appearance:none; appearance:none; outline:none; cursor:pointer; }
    .filter-select:hover,.filter-select:focus { border-color:var(--primary); }
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
    .rfq-no { font-weight:700; font-size:13px; color:var(--primary-d); font-family:monospace; }
    .company-name { font-weight:600; color:var(--text); }
    .contact-info { font-size:12px; color:var(--muted); margin-top:2px; }
    .badge { display:inline-flex; align-items:center; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; letter-spacing:.4px; text-transform:uppercase; }
    .badge-pending { background:#FEF3C7; color:#92400E; border:1px solid #FDE68A; }
    .badge-reviewed { background:#DBEAFE; color:#1E40AF; border:1px solid #BFDBFE; }
    .badge-quoted { background:#E0E7FF; color:#3730A3; border:1px solid #C7D2FE; }
    .badge-accepted { background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; }
    .badge-rejected { background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; }
    .badge-expired { background:#F3F4F6; color:#4B5563; border:1px solid #E5E7EB; }
    .items-count { display:inline-flex; align-items:center; gap:4px; padding:4px 10px; background:#F1F5F9; border-radius:6px; font-size:12px; font-weight:600; color:#475569; }
    .action-btns { display:flex; align-items:center; justify-content:center; gap:8px; }
    .action-icon { width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; border:1.5px solid transparent; background:none; border-radius:7px; transition:all .15s; text-decoration:none; }
    .action-icon svg { width:15px; height:15px; }
    .action-icon.view   { color:#0EA5E9; border-color:#BAE6FD; background:#F0F9FF; }
    .action-icon.delete { color:#DC2626; border-color:#FECACA; background:#FEF2F2; }
    .action-icon:hover { transform:translateY(-2px) scale(1.08); box-shadow:0 3px 8px rgba(0,0,0,.12); }
    .action-icon.view:hover   { background:#E0F2FE; border-color:#0EA5E9; }
    .action-icon.delete:hover { background:#FEE2E2; border-color:#EF4444; }
    .table-footer { padding:12px 20px; font-size:13px; color:var(--muted); border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; background:#FAFBFD; }
    .empty-state { text-align:center; padding:52px 20px; color:var(--muted); }
    .empty-state svg { width:42px; height:42px; margin:0 auto 12px; opacity:.2; display:block; }
    .empty-state p { font-size:14px; font-weight:500; }
    .alert-success { padding:12px 16px; background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; border-radius:8px; font-size:13.5px; font-weight:500; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
    .date-info { font-size:12px; color:var(--muted); }
    .date-info strong { color:var(--text); font-weight:600; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">RFQ Requests</h1>
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
        <form method="GET" action="{{ route('admin.rfq-requests.index') }}" class="search-group">
            <label>Search:</label>
            <div class="search-input-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by RFQ No, Company, Contact..." class="search-input"/>
            </div>
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            @if(request('entries'))
                <input type="hidden" name="entries" value="{{ request('entries') }}">
            @endif
        </form>

        <div class="filter-group">
            <form method="GET" action="{{ route('admin.rfq-requests.index') }}" style="display:flex; align-items:center; gap:8px;">
                <label style="font-size:13px; font-weight:600; color:var(--text);">Status:</label>
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                    <option value="quoted" {{ request('status') === 'quoted' ? 'selected' : '' }}>Quoted</option>
                    <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                @if(request('entries'))
                    <input type="hidden" name="entries" value="{{ request('entries') }}">
                @endif
            </form>

            <form method="GET" action="{{ route('admin.rfq-requests.index') }}" class="entries-group">
                Show
                <select name="entries" class="entries-select" onchange="this.form.submit()">
                    @foreach([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" {{ request('entries', 15) == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
                entries
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
            </form>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="center" style="width:60px;">S.No</th>
                <th>RFQ No</th>
                <th>Company / Contact</th>
                <th>Items</th>
                <th>Delivery</th>
                <th>Quote Needed By</th>
                <th class="center">Status</th>
                <th class="center">Assigned Admin</th>
                <th class="center" style="width:100px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $rfq)
            <tr>
                <td class="center" style="font-weight:700; color:var(--muted); font-size:13px;">
                    {{ $records->firstItem() + $index }}
                </td>
                <td>
                    <span class="rfq-no">{{ $rfq->rfq_no }}</span>
                    <div class="date-info" style="margin-top:4px;">
                        {{ $rfq->created_at ? $rfq->created_at->format('d M Y, h:i A') : '—' }}
                    </div>
                </td>
                <td>
                    <div class="company-name">{{ $rfq->company_name }}</div>
                    <div class="contact-info">
                        {{ $rfq->contact_person }} &bull; {{ $rfq->business_email }}
                    </div>
                    <div class="contact-info">{{ $rfq->phone }}</div>
                </td>
                <td>
                    <span class="items-count">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M3 9h18M9 21V9"/>
                        </svg>
                        {{ $rfq->total_items ?? count($rfq->items ?? []) }} items
                    </span>
                </td>
                <td>
                    <div style="font-weight:600;">{{ $rfq->country_of_delivery }}</div>
                    <div class="date-info">
                        <strong>Destination:</strong> {{ $rfq->destination ?? '—' }}
                    </div>
                    <div class="date-info">
                        <strong>Date:</strong> {{ $rfq->delivery_date ? $rfq->delivery_date->format('d M Y') : '—' }}
                    </div>
                </td>
                <td>
                    @if($rfq->quote_needed_by)
                        <div style="font-weight:600; {{ $rfq->quote_needed_by->isPast() ? 'color:#DC2626;' : '' }}">
                            {{ $rfq->quote_needed_by->format('d M Y') }}
                        </div>
                        @if($rfq->quote_needed_by->isFuture())
                            <div class="date-info">{{ $rfq->quote_needed_by->diffForHumans() }}</div>
                        @else
                            <div class="date-info" style="color:#DC2626;">Expired</div>
                        @endif
                    @else
                        <span style="color:#94A3B8;">—</span>
                    @endif
                </td>
                <td class="center">
                    @php
                        $statusClass = [
                            'pending' => 'badge-pending',
                            'reviewed' => 'badge-reviewed',
                            'quoted' => 'badge-quoted',
                            'accepted' => 'badge-accepted',
                            'rejected' => 'badge-rejected',
                            'expired' => 'badge-expired',
                        ][$rfq->status] ?? 'badge-pending';
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $rfq->status ?? 'pending' }}</span>
                </td>
                <td class="center">
                    @if(Auth::user()->isSuperAdmin())
                        <form action="{{ route('admin.rfq-requests.assign-admin', $rfq->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <select name="admin_id" class="filter-select" onchange="this.form.submit()" style="min-width:140px;">
                                <option value="">-- Not Assigned --</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->_id }}" {{ (string)$rfq->assigned_admin_id === (string)$admin->_id ? 'selected' : '' }}>
                                        {{ $admin->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @else
                        {{ $rfq->assignedAdmin?->name ?? 'Not Assigned' }}
                    @endif
                </td>
                <td>
                    <div class="action-btns">
                        <a href="{{ route('admin.rfq-requests.show', $rfq->id) }}"
                           class="action-icon view" title="View Details">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </a>
                        <button class="action-icon delete" title="Delete"
                            onclick="if(confirm('Delete RFQ {{ $rfq->rfq_no }}?')) document.getElementById('del-{{ $rfq->id }}').submit();">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </button>
                        <form id="del-{{ $rfq->id }}" method="POST"
                              action="{{ route('admin.rfq-requests.destroy', $rfq->id) }}"
                              style="display:none;">
                            @csrf @method('DELETE')
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                            <rect x="9" y="3" width="6" height="4" rx="1"/>
                            <path d="M9 12h6M9 16h4"/>
                        </svg>
                        <p>No RFQ requests found.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="table-footer">
        <span>{{ $records->firstItem() ?? 0 }}–{{ $records->lastItem() ?? 0 }} of {{ $records->total() }} entries</span>

        @if ($records->hasPages())
        <nav style="display:flex; align-items:center; gap:4px;">
            @if ($records->onFirstPage())
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">‹</span>
            @else
                <a href="{{ $records->previousPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">‹</a>
            @endif

            @foreach ($records->getUrlRange(1, $records->lastPage()) as $page => $url)
                @if ($page == $records->currentPage())
                    <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--primary-d);background:var(--primary-d);color:white;font-size:13px;font-weight:700;">{{ $page }}</span>
                @elseif ($page == 1 || $page == $records->lastPage() || abs($page - $records->currentPage()) <= 2)
                    <a href="{{ $url }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:13px;font-weight:500;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">{{ $page }}</a>
                @elseif ($page == $records->currentPage() - 3 || $page == $records->currentPage() + 3)
                    <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--muted);font-size:13px;">…</span>
                @endif
            @endforeach

            @if ($records->hasMorePages())
                <a href="{{ $records->nextPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">›</a>
            @else
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">›</span>
            @endif
        </nav>
        @endif
    </div>
</div>

@endsection
