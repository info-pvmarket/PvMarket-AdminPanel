@if($mode === 'create')

{{-- ═══ CREATE MODE ═══ --}}
<x-admin.table-form-page
    title="Add Country"
    :back-route="route('admin.setup.countries.index')"
    :action="route('admin.setup.countries.store')"
    enctype="multipart/form-data"
>
    <x-slot name="columns">
        <th style="min-width:180px;">Country Name</th>
        <th style="min-width:120px;">Country Code</th>
        <th style="min-width:200px;">Country Flag</th>
        <th style="min-width:160px;">Alt Tag</th>
        <th style="min-width:160px;">Capital</th>
        <th style="min-width:160px;">Currency</th>
    </x-slot>

    <x-slot name="row">
        <td>
            <input type="text"
                   name="countries[{INDEX}][name]"
                   placeholder="e.g. United Arab Emirates"
                   required/>
        </td>
        <td>
            <input type="text"
                   name="countries[{INDEX}][code]"
                   placeholder="e.g. 971"
                   required/>
        </td>
        <td>
            <input type="file"
                   name="countries[{INDEX}][flag]"
                   accept="image/*"/>
        </td>
        <td>
            <input type="text"
                   name="countries[{INDEX}][alt_tag]"
                   placeholder="e.g. UAE Flag"/>
        </td>
        <td>
            <input type="text"
                   name="countries[{INDEX}][capital]"
                   placeholder="e.g. Abu Dhabi"/>
        </td>
        <td>
            <input type="text"
                   name="countries[{INDEX}][currency]"
                   placeholder="e.g. AED"/>
        </td>
    </x-slot>

</x-admin.table-form-page>
@php return; @endphp

@elseif($mode === 'edit')

{{-- ═══ EDIT MODE ═══ --}}
<x-admin.form-page
    title="Edit Country"
    :back-route="route('admin.setup.countries.index')"
    :action="route('admin.setup.countries.update', $record->id)"
    method="PUT"
    enctype="multipart/form-data"
>
    <div class="form-grid">

        <div class="form-group">
            <label class="form-label">Country Name <span>*</span></label>
            <input type="text" name="name" class="form-input"
                   placeholder="e.g. United Arab Emirates"
                   value="{{ old('name', $record->name) }}" required/>
        </div>

        <div class="form-group">
            <label class="form-label">Country Code <span>*</span></label>
            <input type="text" name="code" class="form-input"
                   placeholder="e.g. 971"
                   value="{{ old('code', $record->code) }}" required/>
        </div>

        <div class="form-group">
            <label class="form-label">Country Flag</label>
            @php
                $flagSrc = $record->flag
                    ? (preg_match('/^https?:\/\//i', $record->flag)
                        ? $record->flag
                        : rtrim(config('filesystems.disks.r2.url'), '/') . '/' . ltrim($record->flag, '/'))
                    : null;
            @endphp
            @if($flagSrc)
                <div style="margin-bottom:8px;">
                    <img src="{{ $flagSrc }}"
                          style="height:40px; width:40px; border-radius:50%;
                                 object-fit:cover; border:1px solid var(--border);"/>
                </div>
            @endif
            <div class="form-file-wrap">
                <input type="file" name="flag" accept="image/*"/>
            </div>
            <span class="form-hint">Leave blank to keep current flag</span>
        </div>

        <div class="form-group">
            <label class="form-label">Alt Tag</label>
            <input type="text" name="alt_tag" class="form-input"
                   placeholder="e.g. UAE Flag"
                   value="{{ old('alt_tag', $record->alt_tag) }}"/>
        </div>

        <div class="form-group">
            <label class="form-label">Capital</label>
            <input type="text" name="capital" class="form-input"
                   placeholder="e.g. Abu Dhabi"
                   value="{{ old('capital', $record->capital) }}"/>
        </div>

        <div class="form-group">
            <label class="form-label">Currency</label>
            <input type="text" name="currency" class="form-input"
                   placeholder="e.g. AED"
                   value="{{ old('currency', $record->currency) }}"/>
        </div>

    </div>
</x-admin.form-page>
@php return; @endphp

@else

{{-- ═══ INDEX MODE ═══ --}}

@extends('layouts.admin')
@section('title', 'Countries')

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
    .entries-select { padding:7px 26px 7px 10px; border:1.5px solid var(--border); border-radius:7px; font-family:inherit; font-size:13px; font-weight:600; color:var(--text); background:white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 7px center; -webkit-appearance:none; appearance:none; outline:none; cursor:pointer; transition:border-color .2s; }
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
    .flag-img { width:38px; height:38px; border-radius:50%; object-fit:cover; border:1px solid var(--border); }
    .flag-placeholder { width:38px; height:38px; border-radius:50%; background:#F1F5F9; display:inline-flex; align-items:center; justify-content:center; color:#CBD5E1; }
    .action-btns { display:flex; align-items:center; justify-content:center; gap:8px; }
    .action-icon { width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; border:1.5px solid transparent; background:none; border-radius:7px; transition:all .15s; text-decoration:none; }
    .action-icon svg { width:15px; height:15px; }
    .action-icon.edit   { color:#D97706; border-color:#FDE68A; background:#FFFBEB; }
    .action-icon.delete { color:#DC2626; border-color:#FECACA; background:#FEF2F2; }
    .action-icon:hover { transform:translateY(-2px) scale(1.08); box-shadow:0 3px 8px rgba(0,0,0,.12); }
    .action-icon.edit:hover   { background:#FEF3C7; border-color:#F59E0B; }
    .action-icon.delete:hover { background:#FEE2E2; border-color:#EF4444; }
    .default-radio { width:18px; height:18px; cursor:pointer; accent-color:var(--primary); }
    .table-footer { padding:12px 20px; font-size:13px; color:var(--muted); border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; background:#FAFBFD; }
    .pagination { display:flex; gap:3px; list-style:none; }
    .pagination li a,.pagination li span { display:flex; align-items:center; justify-content:center; min-width:32px; height:32px; padding:0 8px; border-radius:6px; border:1.5px solid var(--border); font-size:13px; font-weight:500; text-decoration:none; color:var(--text); background:white; transition:all .15s; }
    .pagination li.active span { background:var(--primary-d); border-color:var(--primary-d); color:white; }
    .pagination li a:hover { border-color:var(--primary); color:var(--primary); background:var(--primary-l); }
    .empty-state { text-align:center; padding:52px 20px; color:var(--muted); }
    .empty-state svg { width:42px; height:42px; margin:0 auto 12px; opacity:.2; display:block; }
    .empty-state p { font-size:14px; font-weight:500; }
    .alert-success { padding:12px 16px; background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; border-radius:8px; font-size:13.5px; font-weight:500; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
    .alert-error { padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; font-weight:500; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
    .btn-import { display:inline-flex; align-items:center; gap:7px; padding:10px 20px; background:#6366F1; color:white; border-radius:8px; font-size:14px; font-weight:600; text-decoration:none; white-space:nowrap; border:none; cursor:pointer; font-family:inherit; transition:background .2s; }
    .btn-import:hover { background:#4F46E5; }
    .btn-import:disabled { background:#A5B4FC; cursor:not-allowed; }
    @keyframes spin { to { transform:rotate(360deg); } }
    .spinner { width:15px; height:15px; border:2px solid rgba(255,255,255,.4); border-top-color:white; border-radius:50%; animation:spin .7s linear infinite; display:none; }
    .btn-import.loading .spinner { display:block; }
    .btn-import.loading .btn-icon { display:none; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; gap:10px; flex-wrap:wrap;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Countries</h1>
    <div style="display:flex; align-items:center; gap:10px;">

        {{-- ── Fetch Countries from API ── --}}
        <form method="POST" action="{{ route('admin.setup.countries.import-api') }}">
            @csrf
            <button type="submit" class="btn-import"
                    onclick="this.classList.add('loading'); this.disabled=true; this.closest('form').submit();">
                <span class="btn-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="8 17 12 21 16 17"/>
                        <line x1="12" y1="21" x2="12" y2="7"/>
                        <path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"/>
                    </svg>
                </span>
                <span class="spinner"></span>
                Fetch Countries
            </button>
        </form>

        {{-- ── Add ── --}}
        <a href="{{ route('admin.setup.countries.create') }}"
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

@if(session('error'))
    <div class="alert-error">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
        </svg>
        {{ session('error') }}
    </div>
@endif

<div class="content-panel">

    <div class="table-toolbar">
        <form method="GET" action="{{ route('admin.setup.countries.index') }}" class="search-group">
            <label>Search:</label>
            <div class="search-input-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search By Country Name..." class="search-input"/>
            </div>
        </form>

        <form method="GET" action="{{ route('admin.setup.countries.index') }}" class="entries-group">
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
                <th class="center" style="width:60px;">S.No</th>
                <th class="center" style="width:110px;">Default</th>
                <th class="center" style="width:90px;">Flag</th>
                <th>Country Code</th>
                <th>Country Name</th>
                <th>Capital</th>
                <th>Currency</th>
                <th class="center" style="width:120px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $country)
            <tr>
                <td class="center" style="font-weight:700; color:var(--muted); font-size:13px;">
                    {{ $records->firstItem() + $index }}
                </td>

                {{-- Default radio --}}
                <td class="center">
                    <form id="def-{{ $country->id }}" method="POST"
                          action="{{ route('admin.setup.countries.default', $country->id) }}"
                          style="display:inline;">
                        @csrf @method('PATCH')
                        <input type="radio"
                               class="default-radio"
                               {{ $country->is_default ? 'checked' : '' }}
                               onchange="this.form.submit()"
                               title="Set as default"/>
                    </form>
                </td>

                {{-- Flag --}}
                <td class="center">
                    @php
                        $flagSrc = $country->flag
                            ? (preg_match('/^https?:\/\//i', $country->flag)
                                ? $country->flag
                                : rtrim(config('filesystems.disks.r2.url'), '/') . '/' . ltrim($country->flag, '/'))
                            : null;
                    @endphp
                    @if($flagSrc)
                        <img src="{{ $flagSrc }}"
                             alt="{{ $country->alt_tag ?? $country->name }}"
                             class="flag-img"/>
                    @else
                        <div class="flag-placeholder">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                        </div>
                    @endif
                </td>

                <td>{{ $country->code }}</td>
                <td style="font-weight:600;">{{ $country->name }}</td>
                <td>{{ $country->capital ?? '—' }}</td>
                <td>{{ $country->currency ?? '—' }}</td>

                {{-- Actions --}}
                <td>
                    <div class="action-btns">
                        <a href="{{ route('admin.setup.countries.edit', $country->id) }}"
                           class="action-icon edit" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </a>
                        <button class="action-icon delete" title="Delete"
                            onclick="if(confirm('Delete {{ addslashes($country->name) }}?')) document.getElementById('del-{{ $country->id }}').submit();">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </button>
                        <form id="del-{{ $country->id }}" method="POST"
                              action="{{ route('admin.setup.countries.destroy', $country->id) }}"
                              style="display:none;">
                            @csrf @method('DELETE')
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="2" y1="12" x2="22" y2="12"/>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                        </svg>
                        <p>No countries yet. Click <strong>Add +</strong> to create one or <strong>Fetch Countries</strong> to import all.</p>
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

@endif
