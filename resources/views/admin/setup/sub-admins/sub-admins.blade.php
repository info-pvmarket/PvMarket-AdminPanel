
@extends('layouts.admin')
@if($mode === 'create')

{{-- ═══ CREATE MODE ═══ --}}

@section('title', 'Add Sub Admin')

@section('styles')
<style>
    .page-header-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .page-header-wrap h1 {
        font-size: 22px;
        font-weight: 800;
        color: var(--text);
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 18px;
        background: var(--text);
        color: white;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        transition: background .15s;
        white-space: nowrap;
    }

    .btn-back:hover { background: #334155; }

    .content-panel {
        background: white;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 28px;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }

    .form-grid-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px 24px;
        align-items: start;
    }

    .form-group { display: flex; flex-direction: column; gap: 6px; }

    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
    }

    .form-label span { color: var(--danger); margin-left: 2px; }

    .form-input {
        width: 100%;
        padding: 9px 13px;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        font-family: inherit;
        font-size: 13.5px;
        color: var(--text);
        outline: none;
        transition: border-color .2s, box-shadow .2s;
        background: white;
    }

    .form-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(14,165,233,.1);
    }

    .form-input::placeholder { color: #CBD5E1; }

    /* Password field with generate button */
    .pwd-group { position: relative; }

    .pwd-group .form-input { padding-right: 110px; }

    .generate-btn {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: var(--primary-l);
        color: var(--primary-d);
        border: 1px solid var(--primary-m);
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 9px;
        cursor: pointer;
        font-family: inherit;
        transition: all .15s;
        white-space: nowrap;
    }

    .generate-btn:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .form-hint {
        font-size: 11px;
        color: var(--muted);
        margin-top: 2px;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        padding-top: 20px;
        margin-top: 20px;
        border-top: 1px solid var(--border);
    }

    .btn-save {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 28px;
        background: #10B981;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
        transition: background .15s, box-shadow .2s;
    }

    .btn-save:hover { background: #059669; box-shadow: 0 4px 14px rgba(16,185,129,.35); }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Add Sub Admin</h1>
    <a href="{{ route('admin.setup.sub-admins.index') }}" class="btn-back">← Back</a>
</div>



<div class="content-panel">
    @if($errors->any())
        <div style="padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.setup.sub-admins.store') }}">
        @csrf

        <div class="form-grid-4">

            {{-- Email --}}
            <div class="form-group">
                <label class="form-label">Email: <span>*</span></label>
                <input type="email" name="email" class="form-input"
                       value="{{ old('email') }}"
                       placeholder="admin@example.com" required/>
            </div>

            {{-- Name --}}
            <div class="form-group">
                <label class="form-label">Name: <span>*</span></label>
                <input type="text" name="name" class="form-input"
                       value="{{ old('name') }}"
                       placeholder="Full name" required/>
            </div>
            

            {{-- Password with Generate button --}}
            <div class="form-group">
                <label class="form-label">Password: <span>*</span></label>
                <div class="pwd-group">
                    <input type="text" name="password" id="passwordField"
                           class="form-input"
                           value="{{ old('password') }}"
                           placeholder="Min 8 characters" required/>
                    <button type="button" class="generate-btn" onclick="generatePassword()">
                        ⚡ Generate
                    </button>
                </div>
                <span class="form-hint">Hint: min length 8 characters</span>
            </div>
            {{-- Role --}}
<div class="form-group">
    <label class="form-label">Role: <span>*</span></label>
    <select name="role_id" class="form-input" required>
        <option value=""> Select Role </option>
        @foreach($roles as $role)
            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                {{ $role->role }}
            </option>
        @endforeach
    </select>
</div>

        </div>

        <div class="form-actions">
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
    function generatePassword() {
        const chars   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        const special = '!@#$%^&*';
        let pwd = '';

        // Ensure at least 1 uppercase, 1 lowercase, 1 digit, 1 special
        pwd += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[Math.floor(Math.random() * 26)];
        pwd += 'abcdefghijklmnopqrstuvwxyz'[Math.floor(Math.random() * 26)];
        pwd += '0123456789'[Math.floor(Math.random() * 10)];
        pwd += special[Math.floor(Math.random() * special.length)];

        // Fill remaining to reach length 12
        for (let i = 0; i < 8; i++) {
            pwd += chars[Math.floor(Math.random() * chars.length)];
        }

        // Shuffle
        pwd = pwd.split('').sort(() => Math.random() - 0.5).join('');

        document.getElementById('passwordField').value = pwd;

        // Flash highlight
        const field = document.getElementById('passwordField');
        field.style.borderColor = '#10B981';
        field.style.boxShadow   = '0 0 0 3px rgba(16,185,129,.15)';
        setTimeout(() => {
            field.style.borderColor = '';
            field.style.boxShadow   = '';
        }, 1500);
    }
</script>
@endsection


@elseif($mode === 'edit')

{{-- ═══ EDIT MODE ═══ --}}

@section('title', 'Edit Sub Admin')

@section('styles')
<style>
    .page-header-wrap { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
    .page-header-wrap h1 { font-size:22px; font-weight:800; color:var(--text); }
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; }
    .btn-back:hover { background:#334155; }
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .form-grid-4 { display:grid; grid-template-columns:repeat(4,1fr); gap:20px 24px; align-items: start; }
    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-label { font-size:13px; font-weight:600; color:var(--text); }
    .form-label span { color:var(--danger); margin-left:2px; }
    .form-input { width:100%; padding:9px 13px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; color:var(--text); outline:none; transition:border-color .2s, box-shadow .2s; background:white; }
    .form-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .form-input::placeholder { color:#CBD5E1; }
    .pwd-group { position:relative; }
    .pwd-group .form-input { padding-right:110px; }
    .generate-btn { position:absolute; right:8px; top:50%; transform:translateY(-50%); background:var(--primary-l); color:var(--primary-d); border:1px solid var(--primary-m); border-radius:6px; font-size:11px; font-weight:700; padding:4px 9px; cursor:pointer; font-family:inherit; transition:all .15s; white-space:nowrap; }
    .generate-btn:hover { background:var(--primary); color:white; border-color:var(--primary); }
    .form-hint { font-size:11px; color:var(--muted); margin-top:2px; }
    .form-actions { display:flex; justify-content:flex-end; padding-top:20px; margin-top:20px; border-top:1px solid var(--border); }
    .btn-save { display:inline-flex; align-items:center; gap:8px; padding:10px 28px; background:#10B981; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; transition:background .15s, box-shadow .2s; }
    .btn-save:hover { background:#059669; box-shadow:0 4px 14px rgba(16,185,129,.35); }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Edit Sub Admin</h1>
    <a href="{{ route('admin.setup.sub-admins.index') }}" class="btn-back">← Back</a>
</div>

<div class="content-panel">
    @if($errors->any())
        <div style="padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.setup.sub-admins.update', $record->id) }}">
        @csrf
        @method('PUT')

        <div class="form-grid-4">

            <div class="form-group">
                <label class="form-label">Email: <span>*</span></label>
                <input type="email" name="email" class="form-input"
                       value="{{ old('email', $record->email) }}" required/>
            </div>

            <div class="form-group">
                <label class="form-label">Name: <span>*</span></label>
                <input type="text" name="name" class="form-input"
                       value="{{ old('name', $record->name) }}" required/>
            </div>

            <div class="form-group">
                <label class="form-label">New Password:</label>
                <div class="pwd-group">
                    <input type="text" name="password" id="passwordField"
                           class="form-input" placeholder="Leave blank to keep current"/>
                    <button type="button" class="generate-btn" onclick="generatePassword()">
                        ⚡ Generate
                    </button>
                </div>
                <span class="form-hint">Hint: min length 8 characters</span>
            </div>
            {{-- Role --}}
<div class="form-group">
    <label class="form-label">Role: <span>*</span></label>
    <select name="role_id" class="form-input" required>
        <option value=""> Select Role </option>
        @foreach($roles as $role)
            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                {{ $role->role }}
            </option>
        @endforeach
    </select>
</div>

        </div>

        <div class="form-actions">
            <button type="submit" class="btn-save">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                Save Changes
            </button>
        </div>

    </form>
</div>

@endsection

@section('scripts')
<script>
    function generatePassword() {
        const chars   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        const special = '!@#$%^&*';
        let pwd = '';
        pwd += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[Math.floor(Math.random() * 26)];
        pwd += 'abcdefghijklmnopqrstuvwxyz'[Math.floor(Math.random() * 26)];
        pwd += '0123456789'[Math.floor(Math.random() * 10)];
        pwd += special[Math.floor(Math.random() * special.length)];
        for (let i = 0; i < 8; i++) {
            pwd += chars[Math.floor(Math.random() * chars.length)];
        }
        pwd = pwd.split('').sort(() => Math.random() - 0.5).join('');
        document.getElementById('passwordField').value = pwd;
        const field = document.getElementById('passwordField');
        field.style.borderColor = '#10B981';
        field.style.boxShadow   = '0 0 0 3px rgba(16,185,129,.15)';
        setTimeout(() => { field.style.borderColor = ''; field.style.boxShadow = ''; }, 1500);
    }
</script>
@endsection


@else

{{-- ═══ INDEX MODE ═══ --}}

@section('title', 'Sub Admins')

@section('styles')
<style>
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .table-toolbar { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--border); gap:12px; flex-wrap:wrap; background:#FAFBFD; }
    .search-group { display:flex; align-items:center; gap:8px; }
    .search-group label { font-size:13.5px; color:var(--text); font-weight:600; }
    .search-input-wrap { position:relative; }
    .search-input-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#CBD5E1; pointer-events:none; }
    .search-input { padding:8px 12px 8px 34px; border:1.5px solid var(--border); border-radius:7px; font-family:inherit; font-size:13px; outline:none; width:230px; color:var(--text); transition:border-color .2s,box-shadow .2s; background:white; }
    .search-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .search-input::placeholder { color:#CBD5E1; }
    .entries-group { display:flex; align-items:center; gap:7px; font-size:13.5px; color:var(--muted); font-weight:500; }
    .entries-select { padding:7px 26px 7px 10px; border:1.5px solid var(--border); border-radius:7px; font-family:inherit; font-size:13px; font-weight:600; color:var(--text); background:white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 7px center; -webkit-appearance:none; appearance:none; outline:none; cursor:pointer; transition:border-color .2s; }
    .entries-select:hover,.entries-select:focus { border-color:var(--primary); }
    .data-table { width:100%; border-collapse:collapse; }
    .data-table thead { background:#F0F9FF; }
    .data-table th { padding:11px 16px; text-align:left; font-size:12px; font-weight:700; color:var(--primary-d); text-transform:uppercase; letter-spacing:.5px; border-bottom:2px solid #BAE6FD; white-space:nowrap; }
    .data-table th.center,.data-table td.center { text-align:center; }
    .data-table td { padding:14px 16px; font-size:13.5px; color:var(--text); border-bottom:1px solid #F1F5F9; vertical-align:middle; transition:background .1s; }
    .data-table tr:last-child td { border-bottom:none; }
    .data-table tbody tr:nth-child(odd) td { background:white; }
    .data-table tbody tr:nth-child(even) td { background:#FAFBFD; }
    .data-table tbody tr:hover td { background:#E0F2FE !important; }
    .role-badge { display:inline-flex; align-items:center; justify-content:center; padding:5px 14px; border-radius:6px; font-size:12px; font-weight:700; background:var(--primary); color:white; }
    .action-btns { display:flex; align-items:center; justify-content:center; gap:8px; }
    .action-icon { width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; border:1.5px solid transparent; background:none; border-radius:7px; transition:all .15s; text-decoration:none; }
    .action-icon svg { width:15px; height:15px; }
    .action-icon.edit   { color:#D97706; border-color:#FDE68A; background:#FFFBEB; }
    .action-icon.delete { color:#DC2626; border-color:#FECACA; background:#FEF2F2; }
    .action-icon:hover { transform:translateY(-2px) scale(1.08); box-shadow:0 3px 8px rgba(0,0,0,.12); }
    .action-icon.edit:hover   { background:#FEF3C7; border-color:#F59E0B; }
    .action-icon.delete:hover { background:#FEE2E2; border-color:#EF4444; }
    .table-footer { padding:12px 20px; font-size:13px; color:var(--muted); border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; background:#FAFBFD; }
    .pagination { display:flex; gap:3px; list-style:none; }
    .pagination li a,.pagination li span { display:flex; align-items:center; justify-content:center; min-width:32px; height:32px; padding:0 8px; border-radius:6px; border:1.5px solid var(--border); font-size:13px; font-weight:500; text-decoration:none; color:var(--text); background:white; transition:all .15s; }
    .pagination li.active span { background:var(--primary-d); border-color:var(--primary-d); color:white; }
    .pagination li a:hover { border-color:var(--primary); color:var(--primary); background:var(--primary-l); transform:translateY(-1px); }
    .empty-state { text-align:center; padding:52px 20px; color:var(--muted); }
    .empty-state svg { width:42px; height:42px; margin:0 auto 12px; opacity:.2; display:block; }
    .empty-state p { font-size:14px; font-weight:500; }
    .alert-success { padding:12px 16px; background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; border-radius:8px; font-size:13.5px; font-weight:500; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Sub Admins</h1>
    <div style="display:flex; gap:10px; align-items:center;">

        <a href="{{ route('admin.setup.sub-admins.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
           style="display:inline-flex; align-items:center; gap:7px; padding:10px 20px;
                  background:#059669; color:white; border-radius:8px; font-size:14px;
                  font-weight:600; text-decoration:none; white-space:nowrap;"
           onmouseover="this.style.background='#047857'"
           onmouseout="this.style.background='#059669'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export
        </a>

        <a href="{{ route('admin.setup.sub-admins.create') }}"
           style="display:inline-flex; align-items:center; gap:7px; padding:10px 20px;
                  background:var(--primary); color:white; border-radius:8px; font-size:14px;
                  font-weight:600; text-decoration:none; white-space:nowrap;"
           onmouseover="this.style.background='var(--primary-d)'"
           onmouseout="this.style.background='var(--primary)'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add +
        </a>

    </div>
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
        <form method="GET" action="{{ route('admin.setup.sub-admins.index') }}" class="search-group">
            <label>Search:</label>
            <div class="search-input-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search By Email or Name..." class="search-input"/>
            </div>
        </form>

        <form method="GET" action="{{ route('admin.setup.sub-admins.index') }}" class="entries-group">
            Show
            <select name="entries" class="entries-select" onchange="this.form.submit()">
                @foreach([10,25,50,100] as $n)
                    <option value="{{ $n }}" {{ request('entries',10) == $n ? 'selected' : '' }}>{{ $n }}</option>
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
                <th>Name</th>
                <th>Email</th>
<th style="width:140px;">Role</th>
<th class="center" style="width:120px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subAdmins as $index => $admin)
            <tr>
                <td class="center" style="font-weight:700; color:var(--muted); font-size:13px;">
                    {{ $subAdmins->firstItem() + $index }}
                </td>
                <td style="font-weight:600;">{{ $admin->name }}</td>
                <td>{{ $admin->email }}</td>
<td>
    @if($admin->role)
        <span class="role-badge">{{ $admin->role->role }}</span>
    @else
        <span style="color:#CBD5E1; font-size:12px;">—</span>
    @endif
</td>
<td>
    <div class="action-btns">
                        <a href="{{ route('admin.setup.sub-admins.edit', $admin->id) }}"
                           class="action-icon edit" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </a>
                        <button class="action-icon delete" title="Delete"
                            onclick="if(confirm('Delete {{ $admin->name }}?')) document.getElementById('del-{{ $admin->id }}').submit();">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </button>
                        <form id="del-{{ $admin->id }}" method="POST"
                              action="{{ route('admin.setup.sub-admins.destroy', $admin->id) }}"
                              style="display:none;">
                            @csrf @method('DELETE')
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <p>No sub admins yet. Click <strong>Add +</strong> to create one.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="table-footer">
    <span>{{ $subAdmins->firstItem() ?? 0 }}–{{ $subAdmins->lastItem() ?? 0 }} of {{ $subAdmins->total() }} entries</span>

    @if ($subAdmins->hasPages())
    <nav style="display:flex; align-items:center; gap:4px;">
        @if ($subAdmins->onFirstPage())
            <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">‹</span>
        @else
            <a href="{{ $subAdmins->previousPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">‹</a>
        @endif
        @foreach ($subAdmins->getUrlRange(1, $subAdmins->lastPage()) as $page => $url)
            @if ($page == $subAdmins->currentPage())
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--primary-d);background:var(--primary-d);color:white;font-size:13px;font-weight:700;">{{ $page }}</span>
            @elseif ($page == 1 || $page == $subAdmins->lastPage() || abs($page - $subAdmins->currentPage()) <= 2)
                <a href="{{ $url }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:13px;font-weight:500;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">{{ $page }}</a>
            @elseif ($page == $subAdmins->currentPage() - 3 || $page == $subAdmins->currentPage() + 3)
                <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--muted);font-size:13px;">…</span>
            @endif
        @endforeach
        @if ($subAdmins->hasMorePages())
            <a href="{{ $subAdmins->nextPageUrl() }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:16px;font-weight:600;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">›</a>
        @else
            <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:#CBD5E1;cursor:not-allowed;font-size:16px;">›</span>
        @endif
    </nav>
    @endif
</div>

</div>

@endsection

@endif