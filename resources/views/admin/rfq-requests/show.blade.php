@extends('layouts.admin')

@section('title', 'RFQ Details - ' . $record->rfq_no)

@section('styles')
<style>
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:24px; box-shadow:0 1px 4px rgba(0,0,0,.04); margin-bottom:20px; }
    .panel-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; padding-bottom:16px; border-bottom:1px solid var(--border); }
    .panel-title { font-size:16px; font-weight:700; color:var(--text); display:flex; align-items:center; gap:10px; }
    .panel-title svg { color:var(--primary); }
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; white-space:nowrap; border:1.5px solid var(--text); }
    .btn-back:hover { background:#1E293B; }
    .info-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:20px; }
    .info-group { display:flex; flex-direction:column; gap:4px; }
    .info-label { font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px; }
    .info-value { font-size:14px; font-weight:500; color:var(--text); }
    .info-value.highlight { font-weight:700; color:var(--primary-d); }
    .badge { display:inline-flex; align-items:center; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:700; letter-spacing:.4px; text-transform:uppercase; }
    .badge-pending { background:#FEF3C7; color:#92400E; border:1px solid #FDE68A; }
    .badge-reviewed { background:#DBEAFE; color:#1E40AF; border:1px solid #BFDBFE; }
    .badge-quoted { background:#E0E7FF; color:#3730A3; border:1px solid #C7D2FE; }
    .badge-accepted { background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; }
    .badge-rejected { background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; }
    .badge-expired { background:#F3F4F6; color:#4B5563; border:1px solid #E5E7EB; }
    .items-table { width:100%; border-collapse:collapse; margin-top:16px; }
    .items-table thead { background:#F0F9FF; }
    .items-table th { padding:12px 14px; text-align:left; font-size:12px; font-weight:700; color:var(--primary-d); text-transform:uppercase; letter-spacing:.5px; border-bottom:2px solid #BAE6FD; }
    .items-table td { padding:14px; font-size:13.5px; color:var(--text); border-bottom:1px solid #F1F5F9; }
    .items-table tr:last-child td { border-bottom:none; }
    .items-table tbody tr:nth-child(odd) td { background:white; }
    .items-table tbody tr:nth-child(even) td { background:#FAFBFD; }
    .items-table tbody tr:hover td { background:#E0F2FE !important; }
    .category-badge { display:inline-flex; padding:3px 10px; border-radius:6px; font-size:11px; font-weight:600; background:#E0F2FE; color:#0369A1; }
    .notes-box { background:#FFFBEB; border:1px solid #FDE68A; border-radius:8px; padding:14px 16px; font-size:13.5px; color:#92400E; line-height:1.6; }
    .status-form { display:flex; align-items:center; gap:12px; }
    .status-select { padding:8px 32px 8px 14px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; font-weight:600; color:var(--text); background:white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 10px center; -webkit-appearance:none; appearance:none; cursor:pointer; }
    .status-select:focus { border-color:var(--primary); outline:none; box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .btn-update { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background:#10B981; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; transition:background .15s; }
    .btn-update:hover { background:#059669; }
    .alert-success { padding:12px 16px; background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; border-radius:8px; font-size:13.5px; font-weight:500; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
    @media (max-width: 768px) {
        .info-grid { grid-template-columns:1fr 1fr; }
    }
    @media (max-width: 480px) {
        .info-grid { grid-template-columns:1fr; }
    }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <div>
        <h1 style="font-size:22px; font-weight:800; color:var(--text); margin-bottom:4px;">RFQ Details</h1>
        <span style="font-size:14px; color:var(--muted);">{{ $record->rfq_no }}</span>
    </div>
    <a href="{{ route('admin.rfq-requests.index') }}" class="btn-back">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back to List
    </a>
</div>

@if(session('success'))
    <div class="alert-success">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        {{ session('success') }}
    </div>
@endif

{{-- Status Update --}}
<div class="content-panel">
    <div class="panel-header" style="margin-bottom:0; padding-bottom:0; border-bottom:none;">
        <div style="display:flex; align-items:center; gap:16px;">
            <span class="panel-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
                Status
            </span>
            @php
                $statusClass = [
                    'pending' => 'badge-pending',
                    'reviewed' => 'badge-reviewed',
                    'quoted' => 'badge-quoted',
                    'accepted' => 'badge-accepted',
                    'rejected' => 'badge-rejected',
                    'expired' => 'badge-expired',
                ][$record->status] ?? 'badge-pending';
            @endphp
            <span class="badge {{ $statusClass }}">{{ $record->status ?? 'pending' }}</span>
        </div>
        <form method="POST" action="{{ route('admin.rfq-requests.update-status', $record->id) }}" class="status-form">
            @csrf
            <select name="status" class="status-select">
                <option value="pending" {{ $record->status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="reviewed" {{ $record->status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                <option value="quoted" {{ $record->status === 'quoted' ? 'selected' : '' }}>Quoted</option>
                <option value="accepted" {{ $record->status === 'accepted' ? 'selected' : '' }}>Accepted</option>
                <option value="rejected" {{ $record->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="expired" {{ $record->status === 'expired' ? 'selected' : '' }}>Expired</option>
            </select>
            <button type="submit" class="btn-update">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Update
            </button>
        </form>
    </div>
</div>

{{-- Contact Information --}}
<div class="content-panel">
    <div class="panel-header">
        <span class="panel-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            Contact Information
        </span>
    </div>
    <div class="info-grid">
        <div class="info-group">
            <span class="info-label">Company Name</span>
            <span class="info-value highlight">{{ $record->company_name }}</span>
        </div>
        <div class="info-group">
            <span class="info-label">Contact Person</span>
            <span class="info-value">{{ $record->contact_person }}</span>
        </div>
        <div class="info-group">
            <span class="info-label">Buyer Type</span>
            <span class="info-value">{{ $record->buyer_type ?? '—' }}</span>
        </div>
        <div class="info-group">
            <span class="info-label">Business Email</span>
            <span class="info-value">
                <a href="mailto:{{ $record->business_email }}" style="color:var(--primary); text-decoration:none;">
                    {{ $record->business_email }}
                </a>
            </span>
        </div>
        <div class="info-group">
            <span class="info-label">Phone</span>
            <span class="info-value">
                <a href="tel:{{ $record->phone }}" style="color:var(--primary); text-decoration:none;">
                    {{ $record->phone }}
                </a>
            </span>
        </div>
        <div class="info-group">
            <span class="info-label">Created At</span>
            <span class="info-value">{{ $record->created_at ? $record->created_at->format('d M Y, h:i A') : '—' }}</span>
        </div>
    </div>
</div>

{{-- Delivery & Payment Information --}}
<div class="content-panel">
    <div class="panel-header">
        <span class="panel-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="3" width="15" height="13" rx="2"/>
                <path d="M16 8h4l3 3v5a2 2 0 0 1-2 2h-1"/>
                <circle cx="5.5" cy="18.5" r="2.5"/>
                <circle cx="18.5" cy="18.5" r="2.5"/>
            </svg>
            Delivery & Payment Details
        </span>
    </div>
    <div class="info-grid">
        <div class="info-group">
            <span class="info-label">Country of Delivery</span>
            <span class="info-value highlight">{{ $record->country_of_delivery }}</span>
        </div>
        <div class="info-group">
            <span class="info-label">Destination</span>
            <span class="info-value">{{ $record->destination ?? '—' }}</span>
        </div>
        <div class="info-group">
            <span class="info-label">Delivery Date</span>
            <span class="info-value">{{ $record->delivery_date ? $record->delivery_date->format('d M Y') : '—' }}</span>
        </div>
        <div class="info-group">
            <span class="info-label">Incoterm</span>
            <span class="info-value">{{ $record->incoterm ?? '—' }}</span>
        </div>
        <div class="info-group">
            <span class="info-label">Payment Terms</span>
            <span class="info-value">{{ $record->payment_terms ?? '—' }}</span>
        </div>
        <div class="info-group">
            <span class="info-label">Currency</span>
            <span class="info-value">{{ $record->currency ?? '—' }}</span>
        </div>
        <div class="info-group">
            <span class="info-label">Quote Needed By</span>
            <span class="info-value" style="{{ $record->quote_needed_by && $record->quote_needed_by->isPast() ? 'color:#DC2626;' : '' }}">
                {{ $record->quote_needed_by ? $record->quote_needed_by->format('d M Y') : '—' }}
                @if($record->quote_needed_by && $record->quote_needed_by->isPast())
                    <span style="font-size:11px;">(Expired)</span>
                @endif
            </span>
        </div>
    </div>
</div>

{{-- Items --}}
<div class="content-panel">
    <div class="panel-header">
        <span class="panel-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <path d="M3 9h18M9 21V9"/>
            </svg>
            Requested Items
            <span style="background:var(--primary-l); color:var(--primary-d); padding:2px 10px; border-radius:20px; font-size:12px; font-weight:700;">
                {{ $record->total_items ?? count($record->items ?? []) }} items
            </span>
        </span>
    </div>

    @if(!empty($record->items) && count($record->items) > 0)
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:50px;">#</th>
                <th>Category</th>
                <th>Sub Category</th>
                <th>Brand</th>
                <th>Model</th>
                <th style="text-align:right;">Quantity</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->items as $index => $item)
            <tr>
                <td style="font-weight:700; color:var(--muted);">{{ $index + 1 }}</td>
                <td>
                    <span class="category-badge">{{ $item['category_name'] ?? '—' }}</span>
                </td>
                <td>{{ $item['sub_category_name'] ?? '—' }}</td>
                <td style="font-weight:600;">{{ $item['brand_name'] ?? '—' }}</td>
                <td>{{ $item['model'] ?? '—' }}</td>
                <td style="text-align:right; font-weight:700; font-size:15px;">{{ $item['quantity'] ?? 0 }}</td>
                <td>{{ $item['unit'] ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align:center; padding:40px; color:var(--muted);">
        <p>No items in this RFQ request.</p>
    </div>
    @endif
</div>

{{-- Notes --}}
@if($record->notes)
<div class="content-panel">
    <div class="panel-header">
        <span class="panel-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            Additional Notes
        </span>
    </div>
    <div class="notes-box">
        {{ $record->notes }}
    </div>
</div>
@endif

@endsection
