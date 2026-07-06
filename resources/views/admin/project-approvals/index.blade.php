@extends('layouts.admin')

@section('title', 'Project Approvals')

@section('styles')
<style>
    :root { --green: #10B981; --red: #EF4444; }
    .inv-page { padding: 28px 28px 48px; background: var(--bg); min-height: 100vh; }
    .inv-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 24px; gap: 16px; flex-wrap: wrap; }
    .inv-header h1 { font-size: 22px; font-weight: 800; color: var(--text); margin: 0 0 4px; }
    .inv-header p  { font-size: 13px; color: var(--muted); margin: 0; }

    .btn-outline { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border: 1.5px solid var(--border); border-radius: 9px; background: #fff; font-size: 13px; font-weight: 600; color: var(--text); cursor: pointer; text-decoration: none; transition: background .15s; font-family: inherit; }
    .btn-outline:hover { background: #f9fafb; }

    .inv-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    @media (max-width: 900px) { .inv-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 500px) { .inv-stats { grid-template-columns: 1fr; } }

    .stat-card { background: #fff; border-radius: 14px; padding: 20px 22px; border: 1px solid var(--border); display: flex; align-items: flex-start; gap: 16px; position: relative; overflow: hidden; transition: transform .2s, box-shadow .2s; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.08); }
    .stat-card.card-blue   { background: linear-gradient(135deg, #eff6ff 0%, #fff 70%); border: 1px solid #bfdbfe; }
    .stat-card.card-yellow { background: linear-gradient(135deg, #fffbeb 0%, #fff 70%); border: 1px solid #fde68a; }
    .stat-card.card-green  { background: linear-gradient(135deg, #f0fdf4 0%, #fff 70%); border: 1px solid #bbf7d0; }
    .stat-card.card-red    { background: linear-gradient(135deg, #fef2f2 0%, #fff 70%); border: 1px solid #fecaca; }
    .stat-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .stat-icon.blue   { background: #eff6ff; color: #3b82f6; }
    .stat-icon.yellow { background: #fffbeb; color: #d97706; }
    .stat-icon.green  { background: #f0fdf4; color: var(--green); }
    .stat-icon.red    { background: #fef2f2; color: var(--red); }
    .stat-label { font-size: 12px; color: var(--muted); font-weight: 500; margin-bottom: 4px; }
    .stat-value { font-size: 28px; font-weight: 800; color: var(--text); line-height: 1; }

    .inv-toolbar { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
    .inv-search-wrap { position: relative; flex: 1; min-width: 200px; }
    .inv-search-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }
    .inv-search { width: 100%; padding: 9px 12px 9px 36px; border: 1.5px solid var(--border); border-radius: 8px; font-size: 13px; color: var(--text); background: #f9fafb; outline: none; box-sizing: border-box; font-family: inherit; }
    .inv-search:focus { border-color: var(--primary); background: #fff; }

    .filter-pills { display: flex; gap: 8px; flex-wrap: wrap; }
    .filter-pill { padding: 7px 18px; border-radius: 20px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: background .15s, color .15s; font-family: inherit; }
    .filter-pill.active { background: var(--primary); color: #fff; }
    .filter-pill:not(.active) { background: var(--bg); color: var(--muted); }
    .filter-pill:not(.active):hover { background: #e5e7eb; color: var(--text); }

    .inv-table-wrap { background: #fff; border-radius: 14px; border: 1px solid var(--border); overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
    .inv-table { width: 100%; border-collapse: collapse; }
    .inv-table thead { background: #F0F9FF; }
    .inv-table thead th { padding: 13px 18px; text-align: left; font-size: 12px; font-weight: 700; color: var(--primary-d); text-transform: uppercase; letter-spacing: .5px; border-bottom: 2px solid #BAE6FD; white-space: nowrap; }
    .inv-table tbody tr:nth-child(odd) td  { background: white; }
    .inv-table tbody tr:nth-child(even) td { background: #FAFBFD; }
    .inv-table tbody tr:hover td { background: #E0F2FE !important; }
    .inv-table tbody td { padding: 15px 18px; font-size: 13px; color: #374151; vertical-align: middle; }

    .sku-wrap { display: flex; align-items: center; gap: 10px; }
    .sku-icon { width: 34px; height: 34px; background: #eef2ff; border-radius: 9px; display: flex; align-items: center; justify-content: center; color: var(--blue); flex-shrink: 0; }
    .sku-code   { font-weight: 700; color: var(--text); font-size: 13px; }
    .sku-status { font-size: 11px; color: var(--muted); margin-top: 1px; }

    .status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
    .status-badge.pending  { background: #fffbeb; color: #d97706; }
    .status-badge.approved { background: #f0fdf4; color: var(--green); }
    .status-badge.rejected { background: #fef2f2; color: var(--red); }

    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; }
.detail-item .di-label { font-size: 11px; color: #9ca3af; margin-bottom: 3px; text-transform: uppercase; letter-spacing: .4px; }
.detail-item .di-value { font-size: 13px; font-weight: 600; color: var(--text); }
.detail-item.full { grid-column: 1 / -1; }

    .action-btn { width: 34px; height: 34px; border-radius: 8px; border: 1.5px solid var(--border); background: #fff; display: flex; align-items: center; justify-content: center; color: var(--muted); cursor: pointer; transition: all .18s; }
    .action-btn.approve-btn:hover { background: #f0fdf4; border-color: #bbf7d0; color: var(--green); transform: translateY(-2px); }
    .action-btn.reject-btn:hover  { background: #fef2f2; border-color: #fecaca; color: var(--red); transform: translateY(-2px); }
    .action-btn.history-btn:hover { background: #FFFBEB; border-color: #FDE68A; color: #D97706; transform: translateY(-2px); }
    .action-btns { display: flex; align-items: center; gap: 6px; }
    .action-btn.view-btn:hover { background: #EFF6FF; border-color: #BFDBFE; color: #3B82F6; transform: translateY(-2px); }

    .inv-empty { text-align: center; padding: 60px 20px; color: var(--muted); font-size: 14px; }

    .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1050; align-items: center; justify-content: center; }
    .modal-backdrop.open { display: flex; }
    .modal-box { background: #fff; border-radius: 18px; padding: 26px; width: 100%; max-width: 430px; position: relative; box-shadow: 0 24px 64px rgba(0,0,0,.2); margin: 16px; }
    .modal-close { position: absolute; top: 16px; right: 16px; width: 30px; height: 30px; border-radius: 50%; border: none; background: #f3f4f6; cursor: pointer; color: var(--muted); font-size: 18px; display: flex; align-items: center; justify-content: center; }
    .modal-title { display: flex; align-items: center; gap: 9px; font-size: 16px; font-weight: 800; color: var(--text); margin-bottom: 2px; }
    .modal-subtitle { font-size: 12px; color: #9ca3af; margin-bottom: 20px; }
    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 7px; }
    .reason-input { width: 100%; min-height: 85px; padding: 11px 13px; border: 1.5px solid var(--border); border-radius: 9px; font-size: 13px; color: var(--text); outline: none; resize: vertical; box-sizing: border-box; font-family: inherit; }
    .reason-input:focus { border-color: var(--primary); }
    .btn-cancel { padding: 9px 18px; border-radius: 9px; border: 1.5px solid var(--border); background: #fff; color: #374151; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; }
    .btn-primary { display: inline-flex; align-items: center; gap: 6px; padding: 9px 20px; border-radius: 9px; border: none; background: var(--primary); color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit; }
    .btn-approve { background: var(--green) !important; }
    .btn-reject  { background: var(--red) !important; }

    .history-list { max-height: 360px; overflow-y: auto; }
    .history-item { display: flex; align-items: flex-start; gap: 13px; padding: 14px 0; border-bottom: 1px solid #f3f4f6; }
    .history-item:last-child { border-bottom: none; }
    .history-icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .history-icon.approved { background: #f0fdf4; color: var(--green); }
    .history-icon.rejected { background: #fef2f2; color: var(--red); }
    .history-icon.submitted { background: #eff6ff; color: #3b82f6; }
    .history-body { flex: 1; }
    .history-type   { font-size: 13px; font-weight: 700; color: var(--text); }
    .history-detail { font-size: 12px; color: var(--muted); margin-top: 2px; }
    .history-meta-row { font-size: 11px; color: #9ca3af; margin-top: 3px; }

    .inv-toast { position: fixed; bottom: 28px; right: 28px; background: var(--text); color: #fff; padding: 12px 20px; border-radius: 11px; font-size: 13px; font-weight: 600; box-shadow: 0 8px 28px rgba(0,0,0,.2); z-index: 9999; transform: translateY(20px); opacity: 0; transition: transform .25s, opacity .25s; pointer-events: none; }
    .inv-toast.show    { transform: translateY(0); opacity: 1; }
    .inv-toast.success { background: var(--green); }
    .inv-toast.error   { background: var(--red); }
</style>
@endsection

@section('content')
<div class="inv-page">

    <div class="inv-header">
        <div>
            <h1>Project Approvals</h1>
            <p>Review and approve submitted solar projects</p>
        </div>
        <button class="btn-outline" onclick="refreshProjects()">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
            </svg>
            Refresh
        </button>
    </div>

    <div class="inv-stats">
        <div class="stat-card card-blue">
            <div class="stat-icon blue">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </div>
            <div><div class="stat-label">Total Projects</div><div class="stat-value">{{ $totalCount }}</div></div>
        </div>
        <div class="stat-card card-yellow">
            <div class="stat-icon yellow">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div><div class="stat-label">Pending</div><div class="stat-value">{{ $pendingCount }}</div></div>
        </div>
        <div class="stat-card card-green">
            <div class="stat-icon green">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div><div class="stat-label">Approved</div><div class="stat-value">{{ $approvedCount }}</div></div>
        </div>
        <div class="stat-card card-red">
            <div class="stat-icon red">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div><div class="stat-label">Rejected</div><div class="stat-value">{{ $rejectedCount }}</div></div>
        </div>
    </div>

    <div class="inv-toolbar">
        <div class="inv-search-wrap">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" class="inv-search" id="paSearch" placeholder="Search by project name..." value="{{ $search }}" oninput="debounceSearch()">
        </div>
        <div class="filter-pills">
            <button class="filter-pill {{ $filter === 'all'      ? 'active' : '' }}" onclick="setFilter('all', this)">All</button>
            <button class="filter-pill {{ $filter === 'pending'  ? 'active' : '' }}" onclick="setFilter('pending', this)">Pending</button>
            <button class="filter-pill {{ $filter === 'approved' ? 'active' : '' }}" onclick="setFilter('approved', this)">Approved</button>
            <button class="filter-pill {{ $filter === 'rejected' ? 'active' : '' }}" onclick="setFilter('rejected', this)">Rejected</button>
        </div>
    </div>

    <div class="inv-table-wrap">
        <table class="inv-table">
            <thead>
                <tr><th>Project</th><th>Type</th><th>Capacity</th><th>Submitted By</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody id="paTableBody">
                @forelse($projects as $p)
                    <tr>
                        <td>
                            <div class="sku-wrap">
                                <div class="sku-icon">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                </div>
                                <div>
                                    <div class="sku-code">{{ $p->project_name }}</div>
                                    <div class="sku-status">{{ $p->created_at?->format('d M Y') }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $p->project_type ?? 'N/A' }}</td>
                        <td>{{ $p->capacity_kw ? number_format($p->capacity_kw, 1) . ' kW' : 'N/A' }}</td>
                        <td>{{ $p->submitter_name }}</td>
                        <td><span class="status-badge {{ $p->status }}">{{ $p->status_label }}</span></td>
                        <td>
                            <div class="action-btns">
    <button class="action-btn approve-btn" title="Accept" data-id="{{ (string) $p->_id }}" data-name="{{ $p->project_name }}" onclick="openApprove(this)">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    </button>
    <button class="action-btn reject-btn" title="Reject" data-id="{{ (string) $p->_id }}" data-name="{{ $p->project_name }}" onclick="openReject(this)">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <button class="action-btn view-btn" title="View Details" data-id="{{ (string) $p->_id }}" onclick="openView(this)">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
    </button>
</div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="inv-empty">No projects found.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination-wrap" style="display:flex; justify-content:flex-end;">
    <x-admin.pagination :paginator="$projects" />
</div>

{{-- Approve Modal --}}
<div class="modal-backdrop" id="approveModal" onclick="backdropClose('approveModal',event)">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('approveModal')">×</button>
        <div class="modal-title">Approve Project</div>
        <div class="modal-subtitle" id="approveProjectName"></div>
        <div class="form-group">
            <label class="form-label">Notes <span style="color:#9ca3af;font-weight:400;">(optional)</span></label>
            <textarea class="reason-input" id="approveNotes" placeholder="Any comments…"></textarea>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button class="btn-cancel" onclick="closeModal('approveModal')">Cancel</button>
            <button class="btn-primary btn-approve" onclick="submitApprove()">Approve</button>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal-backdrop" id="rejectModal" onclick="backdropClose('rejectModal',event)">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('rejectModal')">×</button>
        <div class="modal-title">Reject Project</div>
        <div class="modal-subtitle" id="rejectProjectName"></div>
        <div class="form-group">
            <label class="form-label">Reason <span style="color:#9ca3af;font-weight:400;">(required)</span></label>
            <textarea class="reason-input" id="rejectNotes" placeholder="Why is this being rejected?"></textarea>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button class="btn-cancel" onclick="closeModal('rejectModal')">Cancel</button>
            <button class="btn-primary btn-reject" onclick="submitReject()">Reject</button>
        </div>
    </div>
</div>

{{-- View Details Modal --}}
<div class="modal-backdrop" id="viewModal" onclick="backdropClose('viewModal',event)">
    <div class="modal-box" style="max-width:560px;">
        <button class="modal-close" onclick="closeModal('viewModal')">×</button>
        <div class="modal-title">Project Details</div>
        <div class="modal-subtitle" id="viewProjectName"></div>

        <div id="viewDetailsBody" style="margin-bottom:20px;">
            <div style="text-align:center;padding:30px;color:#9ca3af;">Loading…</div>
        </div>

        <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:10px;">Approval History</div>
        <div class="history-list" id="viewHistoryList"></div>
    </div>
</div>

<div class="inv-toast" id="paToast"></div>
@endsection

@section('scripts')
<script>
let currentProjectId = null;
let currentFilter    = '{{ $filter }}';
let searchTimer      = null;

function showToast(msg, type = '') {
    const t = document.getElementById('paToast');
    t.textContent = msg;
    t.className   = 'inv-toast show' + (type ? ' ' + type : '');
    setTimeout(() => { t.className = 'inv-toast'; }, 3200);
}

function closeModal(id)       { document.getElementById(id).classList.remove('open'); }
function backdropClose(id, e) { if (e.target === document.getElementById(id)) closeModal(id); }

function setFilter(f, el) {
    currentFilter = f;
    document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    window.location = `{{ route('admin.project-approvals.index') }}?filter=${f}`;
}

function debounceSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        const search = document.getElementById('paSearch').value;
        window.location = `{{ route('admin.project-approvals.index') }}?filter=${currentFilter}&search=${encodeURIComponent(search)}`;
    }, 400);
}

function refreshProjects() { window.location.reload(); }

function openApprove(el) {
    currentProjectId = el.dataset.id;
    document.getElementById('approveProjectName').textContent = el.dataset.name;
    document.getElementById('approveNotes').value = '';
    document.getElementById('approveModal').classList.add('open');
}

function submitApprove() {
    fetch(`/admin/project-approvals/${currentProjectId}/approve`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ notes: document.getElementById('approveNotes').value }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) { showToast('Project approved.', 'success'); closeModal('approveModal'); setTimeout(() => location.reload(), 600); }
        else showToast(d.message || 'Error.', 'error');
    })
    .catch(() => showToast('Network error.', 'error'));
}

function openReject(el) {
    currentProjectId = el.dataset.id;
    document.getElementById('rejectProjectName').textContent = el.dataset.name;
    document.getElementById('rejectNotes').value = '';
    document.getElementById('rejectModal').classList.add('open');
}

function submitReject() {
    const notes = document.getElementById('rejectNotes').value.trim();
    if (!notes) { showToast('Please provide a rejection reason.', 'error'); return; }
    fetch(`/admin/project-approvals/${currentProjectId}/reject`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ notes }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) { showToast('Project rejected.', 'success'); closeModal('rejectModal'); setTimeout(() => location.reload(), 600); }
        else showToast(d.message || 'Error.', 'error');
    })
    .catch(() => showToast('Network error.', 'error'));
}

function openView(el) {
    currentProjectId = el.dataset.id;
    document.getElementById('viewProjectName').textContent = '';
    document.getElementById('viewDetailsBody').innerHTML = '<div style="text-align:center;padding:30px;color:#9ca3af;">Loading…</div>';
    document.getElementById('viewHistoryList').innerHTML = '';
    document.getElementById('viewModal').classList.add('open');

    fetch(`/admin/project-approvals/${currentProjectId}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(d => {
            if (!d.success) { showToast('Failed to load details.', 'error'); return; }
            const p = d.project;

            document.getElementById('viewProjectName').textContent = p.project_name;

            document.getElementById('viewDetailsBody').innerHTML = `
                <div class="detail-grid">
                    <div class="detail-item"><div class="di-label">Customer</div><div class="di-value">${p.customer_name ?? 'N/A'}</div></div>
                    <div class="detail-item"><div class="di-label">Type</div><div class="di-value">${p.project_type ?? 'N/A'}</div></div>
                    <div class="detail-item"><div class="di-label">Capacity</div><div class="di-value">${p.capacity_kw ? p.capacity_kw + ' kW' : 'N/A'}</div></div>
                    <div class="detail-item"><div class="di-label">Location</div><div class="di-value">${p.location ?? 'N/A'}</div></div>
                    <div class="detail-item"><div class="di-label">Status</div><div class="di-value"><span class="status-badge ${p.status}">${p.status_label}</span></div></div>
                    <div class="detail-item"><div class="di-label">Submitted By</div><div class="di-value">${p.submitted_by}</div></div>
                    <div class="detail-item"><div class="di-label">Submitted At</div><div class="di-value">${p.submitted_at ?? 'N/A'}</div></div>
                    ${p.reviewed_by ? `<div class="detail-item"><div class="di-label">Reviewed By</div><div class="di-value">${p.reviewed_by}</div></div>` : ''}
                    ${p.reviewed_at ? `<div class="detail-item"><div class="di-label">Reviewed At</div><div class="di-value">${p.reviewed_at}</div></div>` : ''}
                    ${p.description ? `<div class="detail-item full"><div class="di-label">Description</div><div class="di-value" style="font-weight:400;">${p.description}</div></div>` : ''}
                    ${p.review_notes ? `<div class="detail-item full"><div class="di-label">Review Notes</div><div class="di-value" style="font-weight:400;">${p.review_notes}</div></div>` : ''}
                </div>
            `;

            if (d.logs.length === 0) {
                document.getElementById('viewHistoryList').innerHTML = '<div style="text-align:center;padding:16px;color:#9ca3af;font-size:12px;">No history yet.</div>';
            } else {
                const iconMap = {
                    Approved: `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>`,
                    Rejected: `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
                    Submitted: `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/></svg>`,
                };
                document.getElementById('viewHistoryList').innerHTML = d.logs.map(log => `
                    <div class="history-item">
                        <div class="history-icon ${log.action.toLowerCase()}">${iconMap[log.action] ?? ''}</div>
                        <div class="history-body">
                            <div class="history-type">${log.action}</div>
                            ${log.notes ? `<div class="history-detail">${log.notes}</div>` : ''}
                            <div class="history-meta-row">${log.created_at ?? ''}</div>
                        </div>
                    </div>
                `).join('');
            }
        })
        .catch(() => showToast('Network error.', 'error'));
}
</script>
@endsection