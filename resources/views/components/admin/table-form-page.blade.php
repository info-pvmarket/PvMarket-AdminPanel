@props([
    'title'     => 'Add',
    'backRoute' => '#',
    'action'    => '#',
])

@extends('layouts.admin')

@section('title', $title)

@section('styles')
<style>
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .page-header h1 {
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
        transition: background .15s, transform .1s;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .btn-back:hover { background: #334155; transform: translateY(-1px); }

    .content-panel {
        background: white;
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        padding: 20px 24px 24px;
    }

    .form-table-scroll {
        width: 100%;
        overflow-x: auto;
        margin-bottom: 20px;
        padding-bottom: 4px;
    }

    .form-table-scroll::-webkit-scrollbar {
        height: 8px;
    }

    .form-table-scroll::-webkit-scrollbar-track {
        background: #F1F5F9;
        border-radius: 999px;
    }

    .form-table-scroll::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border-radius: 999px;
    }

    /* Add More — right aligned */
    .add-more-wrap {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 16px;
    }

    .btn-add-more {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 18px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        transition: background .15s, box-shadow .2s, transform .1s;
    }

    .btn-add-more:hover {
        background: var(--primary-d);
        box-shadow: 0 4px 12px rgba(14,165,233,.3);
        transform: translateY(-1px);
    }

    /* Table */
    .form-table {
        width: 100%;
        min-width: max-content;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    .form-table thead tr {
        border-bottom: 2px solid #BAE6FD;
        background: #F0F9FF;
    }

    .form-table th {
        padding: 11px 14px;
        font-size: 13px;
        font-weight: 700;
        color: var(--primary-d);
        text-align: left;
        white-space: nowrap;
    }

    .form-table th.center { text-align: center; }

    .form-table tbody tr { border-bottom: 1px solid #F1F5F9; }
    .form-table tbody tr:last-child { border-bottom: none; }
    .form-table tbody tr:hover td { background: #FAFBFD; }

    .form-table td { padding: 10px 14px; vertical-align: middle; }

    .form-table td.sno {
        font-size: 14px;
        font-weight: 700;
        color: var(--muted);
        text-align: center;
        white-space: nowrap;
    }

    /* Inputs inside table */
    .form-table input[type="text"],
    .form-table input[type="url"],
    .form-table input[type="email"],
    .form-table input[type="number"],
    .form-table select,
    .form-table textarea {
        width: 100%;
        padding: 8px 12px;
        border: 1.5px solid var(--border);
        border-radius: 7px;
        font-family: inherit;
        font-size: 13px;
        color: var(--text);
        outline: none;
        transition: border-color .2s, box-shadow .2s;
        background: white;
    }

    .form-table input[type="text"]:focus,
    .form-table input[type="url"]:focus,
    .form-table input[type="email"]:focus,
    .form-table select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(14,165,233,.1);
    }

    .form-table input::placeholder { color: #CBD5E1; }

    /* File input */
    .form-table input[type="file"] {
        padding: 5px 8px;
        font-size: 12.5px;
        border: 1.5px solid var(--border);
        border-radius: 7px;
        background: white;
        cursor: pointer;
        width: 100%;
        font-family: inherit;
    }

    .form-table input[type="file"]::-webkit-file-upload-button {
        padding: 5px 12px;
        background: var(--light);
        border: none;
        border-right: 1px solid var(--border);
        font-size: 12px;
        font-family: inherit;
        cursor: pointer;
        margin-right: 8px;
        transition: background .15s;
    }

    .form-table input[type="file"]::-webkit-file-upload-button:hover {
        background: var(--primary-l);
    }

    /* Remove row button */
    .btn-remove-row {
        background: none;
        border: none;
        cursor: pointer;
        color: #F97316;
        padding: 4px 6px;
        border-radius: 6px;
        font-size: 18px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .15s;
        margin: 0 auto;
    }

    .btn-remove-row:hover {
        background: #FEF3C7;
        color: #DC2626;
        transform: scale(1.15);
    }

    /* Save — right */
    .save-wrap {
        display: flex;
        justify-content: flex-end;
        padding-top: 16px;
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
        transition: background .15s, box-shadow .2s, transform .1s;
    }

    .btn-save:hover {
        background: #059669;
        box-shadow: 0 4px 14px rgba(16,185,129,.35);
        transform: translateY(-1px);
    }

    .btn-save:active { transform: scale(.97); }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">{{ $title }}</h1>
    <a href="{{ $backRoute }}" class="btn-back">← Back</a>
</div>

<div class="content-panel">

    <div class="add-more-wrap">
    {{ $toolbar ?? '' }}
    <button type="button" class="btn-add-more" id="addMoreBtn">
        + Add More
    </button>
</div>

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="tableForm">
        @csrf

        <div class="form-table-scroll">
        <table class="form-table">
            <thead>
                <tr>
                    <th class="center" style="width:60px;">S.No</th>
                    {{ $columns }}
                    <th class="center" style="width:60px;">Actions</th>
                </tr>
            </thead>
            <tbody id="formRows">
                {{-- First row — Blade renders with INDEX = 0 --}}
                <tr data-row="0">
                    <td class="sno">1.</td>
                    {!! str_replace('{INDEX}', '0', $row) !!}
                    <td>
                        <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Remove">✂</button>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>

        <div class="save-wrap">
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

{{-- Row template stored for JS cloning --}}
<script id="rowTemplate" type="text/template">
    {!! $row !!}
</script>

@endsection

@section('scripts')
<script>
    let rowCount = 1;
    const templateHTML = document.getElementById('rowTemplate').innerHTML;

    document.getElementById('addMoreBtn').addEventListener('click', () => {
        const index = rowCount;
        rowCount++;

        const tbody = document.getElementById('formRows');
        const tr    = document.createElement('tr');
        tr.setAttribute('data-row', index);

        // S.No cell
        const tdSno = document.createElement('td');
        tdSno.className = 'sno';
        tdSno.textContent = (index + 1) + '.';
        tr.appendChild(tdSno);

        // Field cells — replace {INDEX} placeholder
        const fieldsHTML = templateHTML.replace(/\{INDEX\}/g, index);
        tr.insertAdjacentHTML('beforeend', fieldsHTML);

        // Action cell
        const tdAction = document.createElement('td');
        tdAction.innerHTML = `<button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Remove">✂</button>`;
        tr.appendChild(tdAction);

        tbody.appendChild(tr);
        updateNumbers();
    });

    function removeRow(btn) {
        const tbody = document.getElementById('formRows');
        if (tbody.querySelectorAll('tr').length > 1) {
            btn.closest('tr').remove();
            updateNumbers();
        }
    }

    function updateNumbers() {
        document.querySelectorAll('#formRows tr').forEach((row, i) => {
            row.querySelector('.sno').textContent = (i + 1) + '.';
        });
    }
</script>
@endsection
