@extends('layouts.admin')

@section('title', 'Region Setup')

@section('styles')
    @include('admin.setup.regions.styles')
@endsection

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800;">Region Setup</h1>
    <a href="{{ route('admin.setup.regions.create') }}" style="padding:10px 20px; background:var(--primary); color:white; border-radius:8px; font-weight:600; text-decoration:none;">+ Add Region</a>
</div>

@if (session('success'))
    <div class="alert-success" role="status">{{ session('success') }}</div>
@endif

<div class="content-panel">
    <div class="table-toolbar">
        <form method="GET" action="{{ route('admin.setup.regions.index') }}" class="search-group">
            <label for="region-search">Search</label>
            <input id="region-search" name="search" value="{{ $search }}" placeholder="Search by slug or name" class="search-input" style="padding-left:12px;">
            <input type="hidden" name="entries" value="{{ $entries }}">
        </form>
        <form method="GET" action="{{ route('admin.setup.regions.index') }}" class="entries-group">
            <label for="region-entries">Show</label>
            <select id="region-entries" name="entries" class="entries-select" onchange="this.form.submit()">
                @foreach ([10, 25, 50, 100] as $size)
                    <option value="{{ $size }}" @selected($entries === $size)>{{ $size }}</option>
                @endforeach
            </select>
            entries
            <input type="hidden" name="search" value="{{ $search }}">
        </form>
    </div>
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th scope="col" class="center">S.No</th>
                    <th scope="col">Name</th>
                    <th scope="col">Storefront path</th>
                    <th scope="col">Country coverage</th>
                    <th scope="col" class="center">Status</th>
                    <th scope="col" class="center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($records as $index => $region)
                    <tr>
                        <td class="center">{{ $records->firstItem() + $index }}</td>
                        <td class="market-name">{{ $region['name'] }}</td>
                        <td><code>{{ $region['path'] }}</code></td>
                        <td>
                            <details>
                                <summary style="cursor:pointer;">{{ count($region['countries']) }} configured locations</summary>
                                {{ implode(', ', $region['countries']) }}
                            </details>
                        </td>
                        <td class="center">
                            <span class="{{ $region['is_active'] ? 'badge-active' : 'badge-inactive' }}">
                                {{ $region['is_active'] ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="center">
                            <div class="action-btns">
                                <a class="action-icon edit" href="{{ route('admin.setup.regions.edit', $region['code']) }}" title="Edit" aria-label="Edit {{ $region['name'] }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('admin.setup.regions.destroy', $region['code']) }}"
                                      onsubmit="return confirm('Delete this region? Its storefront URL will become unavailable.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="action-icon delete" type="submit" title="Delete" aria-label="Delete {{ $region['name'] }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                            <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="center">No regions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-footer">
        <span>{{ $records->firstItem() ?? 0 }}–{{ $records->lastItem() ?? 0 }} of {{ $records->total() }} entries</span>
        {{ $records->links() }}
    </div>
</div>
@endsection
