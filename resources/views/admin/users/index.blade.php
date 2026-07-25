@extends('layouts.admin')

@section('title', 'User Management')

@section('styles')
<style>
    /* ── Page header ── */
    .page-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        gap: 0;
    }

    .page-header h1 {
        font-size: 22px;
        font-weight: 800;
        color: var(--text);
        white-space: nowrap;
        flex: 0 0 auto;
    }

    /* Filter sits right next to title */
    .page-header .filter-form {
        margin-left: 16px;
        flex: 0 0 auto;
    }

    /* Filter dropdown */
    .filter-select {
        padding: 9px 36px 9px 14px;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        font-family: inherit;
        font-size: 13.5px;
        font-weight: 500;
        color: var(--text);
        background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 12px center;
        -webkit-appearance: none;
        appearance: none;
        outline: none;
        cursor: pointer;
        min-width: 180px;
        transition: border-color .2s, box-shadow .2s;
    }

    .filter-select:focus, .filter-select:hover {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(14,165,233,.1);
    }

    /* Export button */
    .btn-export {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 18px;
        background: #059669;
        color: white;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        flex: 0 0 auto;
        border: none;
        cursor: pointer;
        transition: background .15s, transform .1s, box-shadow .2s;
    }

    .btn-export:hover {
        background: #047857;
        box-shadow: 0 4px 12px rgba(5,150,105,.3);
        transform: translateY(-1px);
    }

    .btn-export:active { transform: scale(.97); }

    /* Back button — margin-left auto pushes it to the far right */
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

    /* ── Content panel ── */
    .content-panel {
        background: white;
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }

    /* Blue header strip */
    .panel-header {
        background: linear-gradient(135deg, var(--primary-d) 0%, var(--primary) 100%);
        padding: 14px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .panel-header-title {
        font-size: 14px;
        font-weight: 700;
        color: white;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .panel-header-count {
        background: rgba(255,255,255,.2);
        color: white;
        font-size: 12px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
    }

    /* Toolbar */
    .table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        border-bottom: 1px solid var(--border);
        gap: 12px;
        flex-wrap: wrap;
        background: #FAFBFD;
    }

    .search-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .search-group label {
        font-size: 13.5px;
        color: var(--text);
        font-weight: 600;
    }

    .search-input-wrap { position: relative; }

    .search-input-wrap svg {
        position: absolute;
        left: 10px; top: 50%;
        transform: translateY(-50%);
        color: #CBD5E1;
        pointer-events: none;
    }

    .search-input {
        padding: 8px 12px 8px 34px;
        border: 1.5px solid var(--border);
        border-radius: 7px;
        font-family: inherit;
        font-size: 13px;
        outline: none;
        width: 230px;
        color: var(--text);
        transition: border-color .2s, box-shadow .2s;
        background: white;
    }

    .search-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(14,165,233,.1);
    }

    .search-input::placeholder { color: #CBD5E1; }

    .entries-group {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 13.5px;
        color: var(--muted);
        font-weight: 500;
    }

    .entries-select {
        padding: 7px 26px 7px 10px;
        border: 1.5px solid var(--border);
        border-radius: 7px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 7px center;
        -webkit-appearance: none;
        appearance: none;
        outline: none;
        cursor: pointer;
        transition: border-color .2s;
    }

    .entries-select:focus, .entries-select:hover { border-color: var(--primary); }

    /* Table */
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table thead { background: #F0F9FF; }

    .data-table th {
        padding: 11px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        color: var(--primary-d);
        text-transform: uppercase;
        letter-spacing: .5px;
        border-bottom: 2px solid #BAE6FD;
        white-space: nowrap;
    }

    .data-table th.center,
    .data-table td.center { text-align: center; }

    .data-table td {
        padding: 14px 16px;
        font-size: 13.5px;
        color: var(--text);
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
        transition: background .1s;
    }

    .data-table tr:last-child td { border-bottom: none; }
    .data-table tbody tr:nth-child(odd) td  { background: white; }
    .data-table tbody tr:nth-child(even) td { background: #FAFBFD; }
    .data-table tbody tr:hover td { background: #E0F2FE !important; }

    .user-name { font-weight: 700; color: var(--text); font-size: 14px; margin-bottom: 4px; }

    .company-tag {
        display: inline-block;
        padding: 2px 8px; border-radius: 4px;
        font-size: 11px; font-weight: 700;
        background: var(--accent); color: white;
    }

    .user-email { color: var(--primary-d); font-weight: 500; font-size: 13.5px; transition: color .15s; }
    .user-email:hover { color: var(--primary); text-decoration: underline; }

    /* Badges */
    .badge {
        display: inline-flex; align-items: center; justify-content: center;
        padding: 5px 14px; border-radius: 6px;
        font-size: 12px; font-weight: 700; min-width: 70px;
    }

    .badge-buyer   { background: #10B981; color: white; }
    .badge-seller  { background: #1E293B; color: white; }
    .badge-admin   { background: var(--accent); color: white; }
    .badge-role    { background: #1E293B; color: white; }

    .badge-verified {
        display: inline-block; background: #3B82F6; color: white;
        border-radius: 5px; padding: 4px 12px;
        font-size: 12px; font-weight: 700;
        min-width: 140px; text-align: center; margin-top: 5px;
    }

    .badge-not-verified {
        display: inline-block; background: #F59E0B; color: white;
        border-radius: 5px; padding: 4px 12px;
        font-size: 12px; font-weight: 700;
        min-width: 140px; text-align: center; margin-top: 5px;
    }

    /* Action icons */
    .action-btns { display: flex; align-items: center; justify-content: center; gap: 8px; }

    .action-icon {
        width: 32px; height: 32px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; border: 1.5px solid transparent;
        background: none; border-radius: 7px;
        transition: all .15s; text-decoration: none;
    }

    .action-icon svg { width: 15px; height: 15px; }

    .action-icon.edit   { color: #D97706; border-color: #FDE68A; background: #FFFBEB; }
    .action-icon.toggle { color: #059669; border-color: #A7F3D0; background: #ECFDF5; }
    .action-icon.delete { color: #DC2626; border-color: #FECACA; background: #FEF2F2; }
    .action-icon.email  { color: #2563EB; border-color: #BFDBFE; background: #EFF6FF; }

    .action-icon:hover { transform: translateY(-2px) scale(1.08); box-shadow: 0 3px 8px rgba(0,0,0,.12); }
    .action-icon.edit:hover   { background: #FEF3C7; border-color: #F59E0B; }
    .action-icon.toggle:hover { background: #D1FAE5; border-color: #10B981; }
    .action-icon.delete:hover { background: #FEE2E2; border-color: #EF4444; }
    .action-icon.email:hover  { background: #DBEAFE; border-color: #3B82F6; }

    /* Table footer */
    .table-footer {
        padding: 12px 20px; font-size: 13px; color: var(--muted);
        border-top: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px; background: #FAFBFD;
    }

    /* Pagination */
    .pagination { display: flex; gap: 3px; list-style: none; }

    .pagination li a,
    .pagination li span {
        display: flex; align-items: center; justify-content: center;
        min-width: 32px; height: 32px; padding: 0 8px;
        border-radius: 6px; border: 1.5px solid var(--border);
        font-size: 13px; font-weight: 500;
        text-decoration: none; color: var(--text); background: white;
        transition: all .15s;
    }

    .pagination li.active span { background: var(--primary-d); border-color: var(--primary-d); color: white; }
    .pagination li a:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-l); transform: translateY(-1px); }

    /* Empty */
    .empty-state { text-align: center; padding: 52px 20px; color: var(--muted); }
    .empty-state svg { width: 42px; height: 42px; margin: 0 auto 12px; opacity: .2; display: block; }
    .empty-state p { font-size: 14px; font-weight: 500; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">

    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Users Management</h1>

    <div style="display:flex; align-items:center; gap:12px;">

        {{-- Filter dropdown --}}
        <form method="GET" action="{{ route('admin.users.index') }}" id="filterForm">
            <select name="role_id" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->_id }}" {{ (string) request('role_id') === (string) $role->_id ? 'selected' : '' }}>
                        {{ $role->role }}
                    </option>
                @endforeach
            </select>
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            @if(request('entries'))
                <input type="hidden" name="entries" value="{{ request('entries') }}">
            @endif
        </form>

        {{-- Export button — passes current filters so export matches what's on screen --}}
        <a href="{{ route('admin.users.export', array_filter(['role_id' => request('role_id'), 'search' => request('search')])) }}"
           class="btn-export">
            {{-- Download / spreadsheet icon --}}
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export Users & Companies CSV
        </a>

        <a href="{{ route('admin.dashboard') }}" class="btn-back">← Back</a>
    </div>

</div>

{{-- ── Content Panel ── --}}
<div class="content-panel">

    {{-- Blue header strip --}}
    <div class="panel-header">
        <div class="panel-header-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            All Users
        </div>
        <span class="panel-header-count">{{ $users->total() }} total</span>
    </div>

    {{-- Toolbar --}}
    <div class="table-toolbar">
        <form method="GET" action="{{ route('admin.users.index') }}" class="search-group">
            <label>Search:</label>
            <div class="search-input-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search By Email or Name..."
                    class="search-input"
                />
            </div>
            @if(request('role_id'))
                <input type="hidden" name="role_id" value="{{ request('role_id') }}">
            @endif
        </form>

        <form method="GET" action="{{ route('admin.users.index') }}" class="entries-group">
            Show
            <select name="entries" class="entries-select" onchange="this.form.submit()">
                <option value="10"  {{ request('entries', 10) == 10  ? 'selected' : '' }}>10</option>
                <option value="25"  {{ request('entries', 10) == 25  ? 'selected' : '' }}>25</option>
                <option value="50"  {{ request('entries', 10) == 50  ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('entries', 10) == 100 ? 'selected' : '' }}>100</option>
            </select>
            entries
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            @if(request('role_id'))
                <input type="hidden" name="role_id" value="{{ request('role_id') }}">
            @endif
        </form>
    </div>

    {{-- Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th class="center" style="width:70px;">S.No</th>
                <th>Name</th>
                <th>Email</th>
                <th class="center">Role</th>
                <th class="center">Assigned Admin</th>
                <th class="center" style="width:160px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $index => $user)
            <tr>
                <td class="center" style="font-weight:700; color:var(--muted); font-size:13px;">
                    {{ $users->firstItem() + $index }}
                </td>
                <td>
                    <div class="user-name">{{ $user->name }}</div>
                    @if($user->company_type)
                        <span class="company-tag">{{ $user->company_type }}</span>
                    @endif
                </td>
                <td>
                    <a href="mailto:{{ $user->email }}" class="user-email">{{ $user->email }}</a>
                </td>
                <td class="center">
                    <span class="badge badge-role">
                        {{ $user->role_display_name }}
                    </span>
                    <br>
                    @if($user->isCompanyVerified())
                        <span class="badge-verified">Company Verified</span>
                    @else
                        <span class="badge-not-verified">Company Not Verified</span>
                    @endif
                </td>
                <td class="center">
                    @if(Auth::user()->isSuperAdmin())
                        <form action="{{ route('admin.users.assign-admin', $user->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <select name="admin_id" class="filter-select" onchange="this.form.submit()" style="min-width:140px;">
                                <option value="">-- Not Assigned --</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->_id }}" {{ (string)$user->assigned_admin_id === (string)$admin->_id ? 'selected' : '' }}>
                                        {{ $admin->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @else
                        {{ $user->assignedAdmin?->name ?? 'Not Assigned' }}
                    @endif
                </td>
                <td>
                    <div class="action-btns">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="action-icon edit" title="Edit user">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </a>
                        <a href="#" class="action-icon toggle" title="Toggle status">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="5" width="22" height="14" rx="7" ry="7"/>
                                <circle cx="16" cy="12" r="3" fill="currentColor"/>
                            </svg>
                        </a>
                        <a href="#" class="action-icon delete" title="Delete user">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </a>
                        <a href="mailto:{{ $user->email }}" class="action-icon email" title="Send email">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                        </svg>
                        <p>No users found.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="table-footer">
    <span>
        Showing <strong>{{ $users->firstItem() ?? 0 }}</strong> to
        <strong>{{ $users->lastItem() ?? 0 }}</strong> of
        <strong>{{ $users->total() }}</strong> entries
    </span>
    <x-admin.pagination :paginator="$users" />
</div>

</div>

@endsection
