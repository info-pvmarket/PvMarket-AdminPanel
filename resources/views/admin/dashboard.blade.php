@extends('layouts.admin')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('styles')
<style>
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
    .stat-card { background: white; border: 1px solid var(--border); border-radius: 12px; padding: 20px; display: flex; justify-content: space-between; align-items: center; text-decoration: none; cursor: pointer; transition: all .2s; }
    .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.1); transform: translateY(-2px); }
    .stat-card.primary { border-left: 4px solid var(--primary); }
    .stat-card.purple { border-left: 4px solid #8B5CF6; }
    .stat-card.success { border-left: 4px solid var(--success); }
    .stat-card.info { border-left: 4px solid #06B6D4; }
    .stat-card.warning { border-left: 4px solid var(--accent); }
    .stat-label { font-size: 13px; color: var(--muted); font-weight: 500; margin-bottom: 6px; }
    .stat-value { font-size: 28px; font-weight: 800; color: var(--text); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
    .stat-icon.primary { background: var(--primary-l); color: var(--primary); }
    .stat-icon.purple { background: #EDE9FE; color: #8B5CF6; }
    .stat-icon.success { background: #D1FAE5; color: var(--success); }
    .stat-icon.info { background: #CFFAFE; color: #06B6D4; }
    .stat-icon.warning { background: #FFF7ED; color: var(--accent); }

    .pending-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 24px; }
    .pending-card { background: white; border: 1px solid var(--border); border-radius: 12px; padding: 20px; text-decoration: none; display: block; cursor: pointer; transition: all .2s; }
    .pending-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.1); transform: translateY(-2px); }
    .pending-card.warning { border-left: 4px solid #FBBF24; }
    .pending-card.info { border-left: 4px solid #06B6D4; }
    .pending-card.sales { border-left: 4px solid var(--success); }
    .pending-header { display: flex; justify-content: space-between; align-items: center; }
    .pending-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .pending-icon.warning { background: #FEF3C7; color: #D97706; }
    .pending-icon.info { background: #CFFAFE; color: #0891B2; }
    .pending-icon.sales { background: #D1FAE5; color: var(--success); }
    .pending-value { font-size: 32px; font-weight: 800; margin-top: 4px; }
    .pending-value.warning { color: #D97706; }
    .pending-value.info { color: #0891B2; }
    .pending-value.sales { color: var(--success); }
    .pending-link { display: inline-flex; align-items: center; gap: 6px; margin-top: 14px; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; transition: all .15s; }
    .pending-link.warning { background: #FEF3C7; color: #D97706; border: 1px solid #FDE68A; }
    .pending-link.warning:hover { background: #FDE68A; }
    .pending-link.info { background: #CFFAFE; color: #0891B2; border: 1px solid #A5F3FC; }
    .pending-link.info:hover { background: #A5F3FC; }
    .pending-link.sales { background: #D1FAE5; color: var(--success); border: 1px solid #A7F3D0; }
    .pending-link.sales:hover { background: #A7F3D0; }

    .tables-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .table-card { background: white; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: all .2s; cursor: pointer; }
    .table-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.1); transform: translateY(-2px); }
    .table-header { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid var(--border); }
    .table-title { font-size: 15px; font-weight: 700; color: var(--text); }
    .table-link { font-size: 13px; color: var(--primary); text-decoration: none; font-weight: 600; }
    .table-link:hover { text-decoration: underline; }
    .mini-table { width: 100%; border-collapse: collapse; }
    .mini-table th { padding: 10px 16px; font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .5px; text-align: left; background: #F8FAFC; border-bottom: 1px solid var(--border); }
    .mini-table td { padding: 12px 16px; font-size: 13px; color: var(--text); border-bottom: 1px solid #F1F5F9; vertical-align: middle; }
    .mini-table tr:last-child td { border-bottom: none; }
    .mini-table .fw-medium { font-weight: 600; }
    .mini-table .text-muted { color: var(--muted); font-size: 12px; }
    .badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; }
    .badge-success { background: #D1FAE5; color: #059669; }
    .badge-warning { background: #FEF3C7; color: #D97706; }
    .badge-danger { background: #FEE2E2; color: #DC2626; }
    .badge-info { background: #CFFAFE; color: #0891B2; }
    .badge-primary { background: var(--primary-l); color: var(--primary); }
    .badge-secondary { background: #F1F5F9; color: #64748B; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--muted); }
    .empty-state svg { width: 48px; height: 48px; margin-bottom: 12px; opacity: .5; }
    .empty-state p { font-size: 14px; }

    @media (max-width: 1200px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .pending-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .stats-grid, .pending-grid, .tables-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
<div style="margin-bottom: 24px;">
    <h1 style="font-size: 24px; font-weight: 800; color: var(--text); margin-bottom: 6px;">Welcome back, {{ Auth::user()->name }}!</h1>
    <p style="font-size: 14px; color: var(--muted);">Here's what's happening with your platform today.</p>
</div>

{{-- Statistics Cards --}}
<div class="stats-grid">
    <a href="{{ route('product_listing.index') }}" class="stat-card primary">
        <div>
            <div class="stat-label">Total Listings</div>
            <div class="stat-value">{{ number_format($listingsCount) }}</div>
        </div>
        <div class="stat-icon primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        </div>
    </a>
    <a href="{{ route('product_listing.index') }}?real_time_price=yes" class="stat-card purple">
        <div>
            <div class="stat-label">Real-Time Pricings</div>
            <div class="stat-value">{{ number_format($realTimePricingsCount) }}</div>
        </div>
        <div class="stat-icon purple">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
    </a>
    <a href="{{ route('admin.products.index') }}" class="stat-card success">
        <div>
            <div class="stat-label">Total Products</div>
            <div class="stat-value">{{ number_format($productsCount) }}</div>
        </div>
        <div class="stat-icon success">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
        </div>
    </a>
    <a href="{{ route('admin.inventory.index') }}" class="stat-card info">
        <div>
            <div class="stat-label">Total Stocks</div>
            <div class="stat-value">{{ number_format($totalStocks) }}</div>
        </div>
        <div class="stat-icon info">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.89 1.45l8 4A2 2 0 0 1 22 7.24v9.53a2 2 0 0 1-1.11 1.79l-8 4a2 2 0 0 1-1.79 0l-8-4a2 2 0 0 1-1.1-1.8V7.24a2 2 0 0 1 1.11-1.79l8-4a2 2 0 0 1 1.78 0z"/><polyline points="2.32 6.16 12 11 21.68 6.16"/><line x1="12" y1="22.76" x2="12" y2="11"/></svg>
        </div>
    </a>
</div>

{{-- Pending Approvals --}}
<div class="pending-grid">
    <a href="{{ route('admin.products.index') }}?verification_status=pending" class="pending-card warning">
        <div class="pending-header">
            <div>
                <div class="stat-label">Pending Products</div>
                <div class="pending-value warning">{{ $pendingProductsCount }}</div>
            </div>
            <div class="pending-icon warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <span class="pending-link warning">
            View All
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </span>
    </a>
    <a href="{{ route('product_listing.index') }}?filter=pending" class="pending-card info">
        <div class="pending-header">
            <div>
                <div class="stat-label">Pending Listings</div>
                <div class="pending-value info">{{ $pendingListingsCount }}</div>
            </div>
            <div class="pending-icon info">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <span class="pending-link info">
            View All
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </span>
    </a>
    <a href="{{ route('admin.sales.index') }}" class="pending-card sales">
        <div class="pending-header">
            <div>
                <div class="stat-label">Total Sales</div>
                <div class="pending-value sales">{{ $salesCount }}</div>
            </div>
            <div class="pending-icon sales">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            </div>
        </div>
        <span class="pending-link sales">
            View All
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </span>
    </a>
</div>

{{-- Tables Grid --}}
<div class="tables-grid">
    {{-- Latest Users --}}
    <div class="table-card" onclick="window.location='{{ route('admin.users.index') }}'">
        <div class="table-header">
            <span class="table-title">Latest Users</span>
            <span class="table-link">View All →</span>
        </div>
        @if($latestUsers->isEmpty())
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 1 4-4h2a4 4 0 0 1 4 4v2"/><circle cx="21" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <p>No users found</p>
        </div>
        @else
        <table class="mini-table">
            <thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Joined</th></tr></thead>
            <tbody>
                @foreach($latestUsers as $user)
                <tr>
                    <td class="fw-medium">{{ Str::limit($user->name, 20) }}</td>
                    <td>{{ Str::limit($user->email, 22) }}</td>
                    <td><span class="badge {{ $user->is_active ? 'badge-success' : 'badge-secondary' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="text-muted">{{ $user->created_at?->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Latest Listings --}}
    <div class="table-card" onclick="window.location='{{ route('product_listing.index') }}'">
        <div class="table-header">
            <span class="table-title">Latest Listings</span>
            <span class="table-link">View All →</span>
        </div>
        @if($latestListings->isEmpty())
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            <p>No listings found</p>
        </div>
        @else
        <table class="mini-table">
            <thead><tr><th>Product</th><th>Seller</th><th>Qty</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($latestListings as $listing)
                <tr>
                    <td>
                        <div class="fw-medium">{{ Str::limit($listing->product?->product_name ?? 'N/A', 18) }}</div>
                        <div class="text-muted">{{ $listing->created_at?->diffForHumans() }}</div>
                    </td>
                    <td>{{ $listing->user?->name ?? 'N/A' }}</td>
                    <td>{{ number_format($listing->total_quantity ?? 0) }}</td>
                    <td>
                        @if($listing->verification_status === 'approved')
                        <span class="badge badge-success">Approved</span>
                        @elseif($listing->verification_status === 'pending')
                        <span class="badge badge-warning">Pending</span>
                        @else
                        <span class="badge badge-danger">Rejected</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Latest Products --}}
    <div class="table-card" onclick="window.location='{{ route('admin.products.index') }}'">
        <div class="table-header">
            <span class="table-title">Latest Products</span>
            <span class="table-link">View All →</span>
        </div>
        @if($latestProducts->isEmpty())
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            <p>No products found</p>
        </div>
        @else
        <table class="mini-table">
            <thead><tr><th>Product</th><th>Brand</th><th>Category</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($latestProducts as $product)
                <tr>
                    <td>
                        <div class="fw-medium">{{ Str::limit($product->product_name, 18) }}</div>
                        <div class="text-muted">{{ $product->created_at?->diffForHumans() }}</div>
                    </td>
                    <td>{{ $product->brand_name ?? '-' }}</td>
                    <td>{{ $product->category_name ?? '-' }}</td>
                    <td>
                        @if($product->verification_status === 'verified')
                        <span class="badge badge-success">Verified</span>
                        @elseif($product->verification_status === 'pending')
                        <span class="badge badge-warning">Pending</span>
                        @else
                        <span class="badge badge-danger">Rejected</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Latest Inventory --}}
    <div class="table-card" onclick="window.location='{{ route('admin.inventory.index') }}'">
        <div class="table-header">
            <span class="table-title">Latest Inventory</span>
            <span class="table-link">View All →</span>
        </div>
        @if($latestInventory->isEmpty())
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12.89 1.45l8 4A2 2 0 0 1 22 7.24v9.53a2 2 0 0 1-1.11 1.79l-8 4a2 2 0 0 1-1.79 0l-8-4a2 2 0 0 1-1.1-1.8V7.24a2 2 0 0 1 1.11-1.79l8-4a2 2 0 0 1 1.78 0z"/></svg>
            <p>No inventory transactions</p>
        </div>
        @else
        <table class="mini-table">
            <thead><tr><th>SKU</th><th>Type</th><th>Qty</th><th>Date</th></tr></thead>
            <tbody>
                @foreach($latestInventory as $inv)
                <tr>
                    <td class="fw-medium">{{ $inv->listing?->sku_code ?? 'N/A' }}</td>
                    <td>
                        @if(in_array($inv->transaction_type, ['initial_stock', 'stock_add']))
                        <span class="badge badge-success">{{ ucfirst(str_replace('_', ' ', $inv->transaction_type)) }}</span>
                        @else
                        <span class="badge badge-danger">{{ ucfirst(str_replace('_', ' ', $inv->transaction_type)) }}</span>
                        @endif
                    </td>
                    <td>{{ number_format($inv->quantity ?? 0) }}</td>
                    <td class="text-muted">{{ $inv->created_at?->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Latest Bid/Fair Requests --}}
    <div class="table-card" onclick="window.location='{{ route('admin.bids.index') }}'">
        <div class="table-header">
            <span class="table-title">Latest Bid/Fair Requests</span>
            <span class="table-link">View All →</span>
        </div>
        @if($latestBidRequests->isEmpty())
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            <p>No bid/fair requests</p>
        </div>
        @else
        <table class="mini-table">
            <thead><tr><th>ID</th><th>Product</th><th>Type</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($latestBidRequests as $bid)
                <tr>
                    <td>
                        <div class="fw-medium">{{ $bid->unique_id }}</div>
                        <div class="text-muted">{{ $bid->created_at?->diffForHumans() }}</div>
                    </td>
                    <td>{{ Str::limit($bid->product_name ?? 'N/A', 18) }}</td>
                    <td>
                        @if($bid->request_type === 'fair request')
                        <span class="badge badge-info">Fair</span>
                        @else
                        <span class="badge badge-primary">Bid</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $statusClass = match((int)$bid->status) {
                                0 => 'badge-warning',
                                1 => 'badge-success',
                                2 => 'badge-danger',
                                3 => 'badge-info',
                                default => 'badge-secondary',
                            };
                            $statusLabel = match((int)$bid->status) {
                                0 => 'Pending',
                                1 => 'Accepted',
                                2 => 'Rejected',
                                3 => 'Completed',
                                default => 'Unknown',
                            };
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Latest RFQ Requests --}}
    <div class="table-card" onclick="window.location='{{ route('admin.rfq-requests.index') }}'">
        <div class="table-header">
            <span class="table-title">Latest RFQ Requests</span>
            <span class="table-link">View All →</span>
        </div>
        @if($latestRfqRequests->isEmpty())
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            <p>No RFQ requests</p>
        </div>
        @else
        <table class="mini-table">
            <thead><tr><th>RFQ No</th><th>Company</th><th>Items</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($latestRfqRequests as $rfq)
                <tr>
                    <td>
                        <div class="fw-medium">{{ $rfq->rfq_no }}</div>
                        <div class="text-muted">{{ $rfq->created_at?->diffForHumans() }}</div>
                    </td>
                    <td>{{ Str::limit($rfq->company_name ?? 'N/A', 18) }}</td>
                    <td>{{ $rfq->total_items ?? 0 }}</td>
                    <td>
                        @php
                            $rfqStatusClass = match(strtolower($rfq->status ?? '')) {
                                'pending' => 'badge-warning',
                                'processing' => 'badge-info',
                                'completed' => 'badge-success',
                                'cancelled' => 'badge-danger',
                                default => 'badge-secondary',
                            };
                        @endphp
                        <span class="badge {{ $rfqStatusClass }}">{{ ucfirst($rfq->status ?? 'Pending') }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
