@extends('layouts.admin')

@section('title', 'Currencies')

@section('styles')
<style>
    .page-header { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:20px; flex-wrap:wrap; }
    .page-title { font-size:22px; font-weight:800; color:var(--text); margin:0; }
    .page-subtitle { margin:5px 0 0; color:var(--muted); font-size:13.5px; }
    .content-grid { display:grid; grid-template-columns:minmax(0,1fr) 320px; gap:20px; align-items:start; }
    .panel { background:#fff; border:1px solid var(--border); border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .panel-header { padding:18px 20px; border-bottom:1px solid var(--border); background:#FAFBFD; }
    .panel-title { margin:0; color:var(--text); font-size:15px; font-weight:800; }
    .panel-copy { margin:4px 0 0; color:var(--muted); font-size:12.5px; }
    .panel-body { padding:20px; }
    .form-group { display:flex; flex-direction:column; gap:7px; flex:1; }
    .form-label { font-size:12px; font-weight:700; color:var(--text); text-transform:uppercase; letter-spacing:.4px; }
    .form-control { width:100%; padding:10px 12px; border:1.5px solid var(--border); border-radius:8px; background:#fff; color:var(--text); font:inherit; font-size:13.5px; outline:none; box-sizing:border-box; }
    .form-control:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .btn { display:inline-flex; align-items:center; justify-content:center; gap:7px; min-height:40px; padding:9px 16px; border-radius:8px; border:0; font:inherit; font-size:13px; font-weight:700; cursor:pointer; text-decoration:none; }
    .btn-primary { background:var(--primary); color:#fff; }
    .btn-primary:hover { background:var(--primary-d); }
    .btn-save { min-height:38px; padding:8px 12px; color:#075985; background:#E0F2FE; border:1px solid #7DD3FC; }
    .btn-save:hover { background:#BAE6FD; }
    .btn-delete { color:#B91C1C; background:#FEF2F2; border:1px solid #FECACA; }
    .btn-delete:hover { background:#FEE2E2; }
    .data-table { width:100%; border-collapse:collapse; }
    .data-table th { padding:11px 18px; background:#F0F9FF; color:var(--primary-d); border-bottom:2px solid #BAE6FD; text-align:left; font-size:12px; text-transform:uppercase; letter-spacing:.5px; }
    .data-table td { padding:15px 18px; border-bottom:1px solid #F1F5F9; color:var(--text); font-size:13.5px; vertical-align:middle; }
    .data-table tr:last-child td { border-bottom:0; }
    .data-table tbody tr:hover td { background:#F8FAFC; }
    .code-badge { display:inline-flex; align-items:center; justify-content:center; min-width:64px; padding:6px 12px; border-radius:7px; background:#F5F3FF; border:1px solid #DDD6FE; color:#6D28D9; font-family:monospace; font-size:13px; font-weight:800; letter-spacing:.7px; }
    .symbol-form { display:flex; align-items:center; gap:8px; max-width:220px; }
    .symbol-input { width:100px; text-align:center; font-size:16px; font-weight:700; }
    .actions { display:flex; align-items:center; justify-content:flex-end; gap:8px; }
    .empty-state { padding:48px 20px; color:var(--muted); text-align:center; font-size:13.5px; }
    .alert { padding:12px 16px; border-radius:8px; font-size:13.5px; font-weight:600; margin-bottom:18px; }
    .alert-success { background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; }
    .alert-error { background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; }
    .global-summary { margin-top:14px; padding:13px 15px; border-radius:8px; background:#F8FAFC; border:1px solid #E2E8F0; }
    .global-summary strong { color:var(--text); }
    .global-summary p { color:var(--muted); font-size:12.5px; margin:4px 0 0; }
    @media(max-width:900px) { .content-grid { grid-template-columns:1fr; } }
    @media(max-width:600px) { .actions { align-items:stretch; flex-direction:column; } .btn { width:100%; } }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Currency Management</h1>
        <p class="page-subtitle">Manage the common currency list used across every market and frontend.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="content-grid">
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Global currencies</h2>
            <p class="panel-copy">Every code and symbol below is returned to all markets by the site configuration API.</p>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:90px;">S.No</th>
                    <th>Currency Code</th>
                    <th>Symbol</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($currencies as $index => $currency)
                    <tr>
                        <td style="font-weight:700; color:var(--muted);">{{ $index + 1 }}</td>
                        <td><span class="code-badge">{{ $currency->code }}</span></td>
                        <td>
                            <form method="POST" action="{{ route('admin.setup.currencies.symbol', $currency->code) }}" class="symbol-form">
                                @csrf
                                @method('PATCH')
                                <input
                                    id="symbol-{{ strtolower($currency->code) }}"
                                    name="symbol"
                                    type="text"
                                    class="form-control symbol-input"
                                    value="{{ $currency->symbol }}"
                                    placeholder="e.g. $"
                                    maxlength="16"
                                    aria-label="{{ $currency->code }} currency symbol"
                                    required
                                >
                                <button type="submit" class="btn btn-save">Save</button>
                            </form>
                        </td>
                        <td>
                            <div class="actions">
                                <form method="POST" action="{{ route('admin.setup.currencies.destroy', $currency->code) }}"
                                      onsubmit="return confirm('Remove {{ $currency->code }} from the global currency list?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-delete">Remove</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4"><div class="empty-state">No global currencies are configured.</div></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <aside class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Add Currency</h2>
            <p class="panel-copy">The new currency will immediately become available to every market.</p>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('admin.setup.currencies.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="code">Currency Code</label>
                    <input id="code" name="code" type="text" class="form-control" value="{{ old('code') }}"
                           placeholder="e.g. AED" minlength="3" maxlength="10" pattern="[A-Za-z0-9]+"
                           autocomplete="off" required style="text-transform:uppercase;">
                </div>
                <div class="form-group" style="margin-top:14px;">
                    <label class="form-label" for="symbol">Currency Symbol</label>
                    <input id="symbol" name="symbol" type="text" class="form-control" value="{{ old('symbol') }}"
                           placeholder="e.g. د.إ, $, €, ₹" maxlength="16" autocomplete="off" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; margin-top:14px;">Add Currency</button>
            </form>

            <div class="global-summary">
                <strong>Common currency list</strong>
                <p>{{ $currencies->count() }} currencies available across all markets.</p>
            </div>
        </div>
    </aside>
</div>
@endsection
