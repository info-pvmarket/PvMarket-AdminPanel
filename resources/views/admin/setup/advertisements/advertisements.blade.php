{{-- views/admin/setup/advertisements/advertisements.blade.php --}}

@if($mode === 'index')

<x-admin.index-page
    title="Advertisements"
    create-route="{{ route('admin.setup.advertisements.create') }}"
    search-route="{{ route('admin.setup.advertisements.index') }}"
    search-placeholder="Search By Ad Name..."
    :total="$records->total()"
    :first-item="$records->firstItem() ?? 0"
    :last-item="$records->lastItem() ?? 0"
    :paginator="$records"
>
    <x-slot name="columns">
    <th class="center">Advertisement Image</th>
    <th>Advertisement</th>
    <th class="center">Location</th>
    <th>Redirect Link</th>
    <th class="center" style="width:130px;">Action</th>
</x-slot>

    <x-slot name="rows">
        @forelse ($records as $index => $ad)
        <tr>
            <td class="center" style="font-weight:700;color:var(--muted);font-size:13px;width:70px;">
                {{ $records->firstItem() + $index }}
            </td>
            <td class="center">
                @if($ad->image)
                    <div class="thumb"><img src="{{ asset('storage/' . $ad->image) }}" alt="{{ $ad->alt_tag }}"/></div>
                @else
                    <div class="thumb-placeholder">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                    </div>
                @endif
            </td>
            <td style="font-weight:600;">{{ $ad->title }}</td>
            <td class="center">
    @php
        $location = \App\Models\Location::find($ad->location_id);
    @endphp

    {{ $location->country_name ?? '-' }}
</td>
            <td>
                @if($ad->redirect_link)
                    <a href="{{ $ad->redirect_link }}" target="_blank" class="redirect-link">Link</a>
                @else
                    <span style="color:#CBD5E1;">—</span>
                @endif
            </td>
            <td>
                <div class="action-btns">
                    <a href="{{ route('admin.setup.advertisements.edit', $ad->id) }}" class="action-icon edit" title="Edit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </a>
                    <button class="action-icon toggle" title="Toggle"
                        onclick="document.getElementById('tog-{{ $ad->id }}').submit();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="5" width="22" height="14" rx="7" ry="7"/>
                            <circle cx="{{ ($ad->is_active ?? true) ? '16' : '8' }}" cy="12" r="3" fill="currentColor"/>
                        </svg>
                    </button>
                    <form id="tog-{{ $ad->id }}" method="POST" action="{{ route('admin.setup.advertisements.toggle', $ad->id) }}" style="display:none;">
                        @csrf @method('PATCH')
                    </form>
                    <button class="action-icon delete" title="Delete"
                        onclick="if(confirm('Delete?')) document.getElementById('del-{{ $ad->id }}').submit();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                        </svg>
                    </button>
                    <form id="del-{{ $ad->id }}" method="POST" action="{{ route('admin.setup.advertisements.destroy', $ad->id) }}" style="display:none;">
                        @csrf @method('DELETE')
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    <p>No advertisements yet. Click <strong>Add +</strong> to create one.</p>
                </div>
            </td>
        </tr>
        @endforelse
    </x-slot>
<x-slot name="pagination">
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
    </x-slot>

</x-admin.index-page>

@elseif($mode === 'create')

<x-admin.form-page
    title="Add Advertisement"
    :back-route="route('admin.setup.advertisements.index')"
    :action="route('admin.setup.advertisements.store')"
>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Title <span>*</span></label>
            <input type="text" name="title" class="form-input" placeholder="Enter advertisement title" value="{{ old('title') }}" required/>
        </div>
        <div class="form-group">
    <label class="form-label">
        Country <span>*</span>
    </label>

    <select
        name="location_id"
        class="form-input"
        required
    >
        <option value="">Select Country</option>

        @foreach($locations as $location)
            <option value="{{ (string)$location->_id }}">
                {{ $location->country_name }}
            </option>
        @endforeach
    </select>
</div>
        <div class="form-group">
            <label class="form-label">Advertisement Image</label>
            <div class="form-file-wrap"><input type="file" name="image" accept="image/*"/></div>
            <span class="form-hint">Recommended: 550×200px (JPG, PNG)</span>
        </div>
        <div class="form-group">
            <label class="form-label">Alt Tag</label>
            <input type="text" name="alt_tag" class="form-input" placeholder="Enter image alt tag" value="{{ old('alt_tag') }}"/>
        </div>
        <div class="form-group">
            <label class="form-label">Redirect Link</label>
            <input type="url" name="redirect_link" class="form-input" placeholder="https://example.com" value="{{ old('redirect_link') }}"/>
        </div>
    </div>
</x-admin.form-page>

@elseif($mode === 'edit')

<x-admin.form-page
    title="Edit Advertisement"
    :back-route="route('admin.setup.advertisements.index')"
    :action="route('admin.setup.advertisements.update', $record->id)"
    method="PUT"
>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Title <span>*</span></label>
            <input type="text" name="title" class="form-input" placeholder="Enter advertisement title" value="{{ old('title', $record->title) }}" required/>
        </div>
        <div class="form-group">
    <label class="form-label">
        Country <span>*</span>
    </label>

    <select
        name="location_id"
        class="form-input"
        required
    >
        <option value="">Select Country</option>

        @foreach($locations as $location)
            <option
                value="{{ (string)$location->_id }}"
                {{ (string)$record->location_id === (string)$location->_id ? 'selected' : '' }}
            >
                {{ $location->country_name }}
            </option>
        @endforeach
    </select>
</div>
        <div class="form-group">
            <label class="form-label">Advertisement Image</label>
            @if($record->image)
                <img src="{{ asset('storage/' . $record->image) }}" style="height:45px;border-radius:6px;border:1px solid var(--border);margin-bottom:8px;" />
            @endif
            <div class="form-file-wrap"><input type="file" name="image" accept="image/*"/></div>
            <span class="form-hint">Leave blank to keep current image</span>
        </div>
        <div class="form-group">
            <label class="form-label">Alt Tag</label>
            <input type="text" name="alt_tag" class="form-input" placeholder="Enter image alt tag" value="{{ old('alt_tag', $record->alt_tag) }}"/>
        </div>
        <div class="form-group">
            <label class="form-label">Redirect Link</label>
            <input type="url" name="redirect_link" class="form-input" placeholder="https://example.com" value="{{ old('redirect_link', $record->redirect_link) }}"/>
        </div>
    </div>
</x-admin.form-page>

@endif