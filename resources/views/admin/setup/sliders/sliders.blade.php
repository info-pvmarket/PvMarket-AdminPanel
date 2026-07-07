{{-- views/admin/setup/sliders/sliders.blade.php --}}
{{-- Single file handles: index | create | edit --}}

@php
    $sliderTypes = [
        'top'           => 'Top',
        'advertisement' => 'Advertisement',
    ];
@endphp

{{-- ═══════════════════════════════════════
     MODE: INDEX
════════════════════════════════════════ --}}
@if($mode === 'index')

<x-admin.index-page
    title="Sliders"
    create-route="{{ route('admin.setup.sliders.create') }}"
    search-route="{{ route('admin.setup.sliders.index') }}"
    search-placeholder="Search By Slider Name..."
    :total="$records->total()"
    :first-item="$records->firstItem() ?? 0"
    :last-item="$records->lastItem() ?? 0"
    :paginator="$records"
    :show-drag-handle="true"
>
    <x-slot name="columns">
        <th class="center">Slider Image</th>
        <th>Slider</th>
        <th class="center">Slider Type</th>
        <th class="center">Market</th>
        <th>Redirect Link</th>
        <th class="center" style="width:130px;">Action</th>
    </x-slot>

    <x-slot name="rows">
        @forelse ($records as $index => $slider)
        <tr data-id="{{ $slider->id }}" draggable="true">
            {{-- Drag handle --}}
            <td class="center">
                <div class="drag-handle">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9"  cy="5"  r="1" fill="currentColor"/>
                        <circle cx="9"  cy="12" r="1" fill="currentColor"/>
                        <circle cx="9"  cy="19" r="1" fill="currentColor"/>
                        <circle cx="15" cy="5"  r="1" fill="currentColor"/>
                        <circle cx="15" cy="12" r="1" fill="currentColor"/>
                        <circle cx="15" cy="19" r="1" fill="currentColor"/>
                    </svg>
                </div>
            </td>
            
            {{-- S.No --}}
            <td class="center" style="font-weight:700; color:var(--muted); font-size:13px;">
                {{ $records->firstItem() + $index }}
            </td>
            {{-- Image --}}
            <td class="center">
                @if($slider->image)
                    <div class="thumb">
                        <img src="{{ asset('storage/' . $slider->image) }}" alt="{{ $slider->name }}"/>
                    </div>
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
            {{-- Name --}}
            <td style="font-weight:600;">{{ lang($slider, 'name') }}</td>
            {{-- Slider Type badge --}}
<td class="center">
    @if($slider->slider_type)
        @php
            $badgeColors = [
                'top'           => ['bg'=>'#EFF6FF','color'=>'#1D4ED8','border'=>'#BFDBFE'],
                'advertisement' => ['bg'=>'#FDF4FF','color'=>'#7E22CE','border'=>'#E9D5FF'],
            ];
            $bc = $badgeColors[$slider->slider_type] ?? ['bg'=>'#F1F5F9','color'=>'#475569','border'=>'#E2E8F0'];
        @endphp
        <span style="
            display:inline-flex; align-items:center;
            padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;
            letter-spacing:.4px; text-transform:uppercase;
            background:{{ $bc['bg'] }}; color:{{ $bc['color'] }}; border:1px solid {{ $bc['border'] }};
        ">
            {{ $sliderTypes[$slider->slider_type] ?? ucfirst($slider->slider_type) }}
        </span>
    @else
        <span style="color:#CBD5E1;">—</span>
    @endif
</td>
{{-- Market --}}
<td class="center">
    @php
        $market = \App\Models\Market::find($slider->location_id);
        if (!$market && $slider->location_id) {
            $legacyLocation = \App\Models\Location::find($slider->location_id);
            $market = $legacyLocation
                ? \App\Models\Market::where('name', $legacyLocation->country_name)->first()
                : null;
        }
    @endphp

    {{ $market->name ?? '-' }}
</td>
            {{-- Link --}}
            <td>
                @if($slider->redirect_link)
                    <a href="{{ $slider->redirect_link }}" target="_blank" class="redirect-link">Link</a>
                @else
                    <span style="color:#CBD5E1;">—</span>
                @endif
            </td>
            {{-- Actions --}}
            <td>
                <div class="action-btns">
                    <a href="{{ route('admin.setup.sliders.edit', $slider->id) }}"
                       class="action-icon edit" title="Edit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </a>
                    <button class="action-icon toggle" title="Toggle status"
                        onclick="document.getElementById('tog-{{ $slider->id }}').submit();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="5" width="22" height="14" rx="7" ry="7"/>
                            <circle cx="{{ ($slider->is_active ?? true) ? '16' : '8' }}" cy="12" r="3" fill="currentColor"/>
                        </svg>
                    </button>
                    <form id="tog-{{ $slider->id }}" method="POST"
                          action="{{ route('admin.setup.sliders.toggle', $slider->id) }}"
                          style="display:none;">
                        @csrf @method('PATCH')
                    </form>
                    <button class="action-icon delete" title="Delete"
                        onclick="if(confirm('Delete this slider?')) document.getElementById('del-{{ $slider->id }}').submit();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                        </svg>
                    </button>
                    <form id="del-{{ $slider->id }}" method="POST"
                          action="{{ route('admin.setup.sliders.destroy', $slider->id) }}"
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
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    <p>No sliders yet. Click <strong>Add +</strong> to create one.</p>
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

@push('scripts')
<script>
    document.addEventListener('rowsReordered', e => {
        fetch("{{ route('admin.setup.sliders.reorder') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ order: e.detail.ids })
        });
    });
</script>
@endpush


{{-- ═══════════════════════════════════════
     MODE: CREATE  (multi-row table form)
════════════════════════════════════════ --}}
@elseif($mode === 'create')



{{-- Slider Type bar — sits OUTSIDE the component, rendered directly in the page --}}


<x-admin.table-form-page
    title="Add Sliders"
    :back-route="route('admin.setup.sliders.index')"
    :action="route('admin.setup.sliders.store')"
>

    <x-slot name="columns">
    <th style="min-width:160px;">Slider Name</th>
    <th style="min-width:200px;">Slider Image</th>
    <th style="min-width:160px;">Alt Tag</th>
    <th style="min-width:200px;">Redirect Link</th>
    <th style="min-width:140px;">Slider Type <span style="color:#EF4444;">*</span></th>
    <th style="min-width:160px;">Market <span style="color:#EF4444;">*</span></th>
    {{-- ✅ Added --}}
</x-slot>

    <x-slot name="row">
    <td>
        <input type="text" name="sliders[{INDEX}][name]" placeholder="e.g. Jinko Solar" required/>
    </td>
    <td>
        <input type="file" name="sliders[{INDEX}][image]" accept="image/*"/>
    </td>
    <td>
        <input type="text" name="sliders[{INDEX}][alt_tag]" placeholder="e.g. Jinko"/>
    </td>
    <td>
        <input type="url" name="sliders[{INDEX}][redirect_link]" placeholder="https://example.com"/>
    </td>
    {{-- ✅ Added: per-row type select instead of hidden input --}}
    <td>
        <select name="sliders[{INDEX}][slider_type]" class="slider-type-val" required
                style="height:36px; padding:0 10px; border-radius:8px; border:1.5px solid var(--border); font-size:13px; font-weight:600; color:var(--text); font-family:inherit; cursor:pointer; outline:none; min-width:130px;">
            <option value="" disabled selected>Select type</option>
            <option value="top">Top</option>
            <option value="advertisement">Advertisement</option>
        </select>
    </td>
    <td>
    <select name="sliders[{INDEX}][location_id]" required>
        <option value="">Select Market</option>

        @foreach($markets as $market)
            <option value="{{ (string)$market->_id }}">
                {{ $market->name }}@if($market->code) ({{ strtoupper($market->code) }}) @endif
            </option>
        @endforeach
    </select>
</td>
</x-slot>

</x-admin.table-form-page>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Guard on submit — check every row has a type selected
    var form = document.getElementById('tableForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            var missing = Array.from(document.querySelectorAll('.slider-type-val'))
                               .some(function(el) { return !el.value; });
            if (missing) {
                e.preventDefault();
                alert('Please select a Slider Type for every row before saving.');
            }
        });
    }
});
</script>
@endpush


{{-- ═══════════════════════════════════════
     MODE: EDIT  (single record form)
════════════════════════════════════════ --}}
@elseif($mode === 'edit')

<x-admin.form-page
    title="Edit Slider"
    :back-route="route('admin.setup.sliders.index')"
    :action="route('admin.setup.sliders.update', $record->id)"
    method="PUT"
>
    {{-- ── Slider Type dropdown — above the form grid ── --}}
    <div style="display:flex; align-items:center; gap:10px; padding:14px 18px; background:#F8FAFC; border:1.5px solid var(--border); border-radius:10px; margin-bottom:24px;">
        <label for="slider_type_edit" style="font-size:13px; font-weight:700; color:var(--text); white-space:nowrap; min-width:90px;">
            Slider Type <span style="color:#EF4444;">*</span>
        </label>
        <select
            id="slider_type_edit"
            name="slider_type"
            required
            style="height:38px; padding:0 36px 0 12px; border-radius:8px; border:1.5px solid var(--border); font-size:13px; font-weight:600; color:var(--text); background:#fff url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394A3B8' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E&quot;) no-repeat right 10px center; appearance:none; -webkit-appearance:none; cursor:pointer; outline:none; transition:border-color .15s; min-width:180px;"
            onfocus="this.style.borderColor='var(--primary)'"
            onblur="this.style.borderColor='var(--border)'"
        >
            <option value="" disabled> Select type </option>
            @foreach($sliderTypes as $value => $label)
                <option value="{{ $value }}" {{ old('slider_type', $record->slider_type) === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-grid">

        {{-- Slider Name --}}
        <div class="form-group">
            <label class="form-label">Slider Name <span>*</span></label>
            <input
                type="text"
                name="name"
                class="form-input"
                placeholder="e.g. Jinko Solar"
                value="{{ old('name', $record->name) }}"
                required
            />
        </div>

        {{-- Image --}}
        <div class="form-group">
            <label class="form-label">Slider Image</label>
            @if($record->image)
                <div style="margin-bottom:8px;">
                    <img
                        src="{{ asset('storage/' . $record->image) }}"
                        style="height:50px; border-radius:6px; border:1px solid var(--border);"
                        alt="{{ $record->name }}"
                    />
                </div>
            @endif
            <div class="form-file-wrap">
                <input type="file" name="image" accept="image/*"/>
            </div>
            <span class="form-hint">Leave blank to keep current image</span>
        </div>

        {{-- Alt Tag --}}
        <div class="form-group">
            <label class="form-label">Alt Tag</label>
            <input
                type="text"
                name="alt_tag"
                class="form-input"
                placeholder="e.g. Jinko Solar Banner"
                value="{{ old('alt_tag', $record->alt_tag) }}"
            />
        </div>

        {{-- Redirect Link --}}
        <div class="form-group">
            <label class="form-label">Redirect Link</label>
            <input
                type="url"
                name="redirect_link"
                class="form-input"
                placeholder="https://example.com"
                value="{{ old('redirect_link', $record->redirect_link) }}"
            />
        </div>
        <div class="form-group">
    <label class="form-label">
        Market <span>*</span>
    </label>

    @php
        $selectedMarketId = (string) old('location_id', $record->location_id);
        if (!$markets->contains(fn($market) => (string) $market->_id === $selectedMarketId) && $record->location_id) {
            $legacyLocation = \App\Models\Location::find($record->location_id);
            $matchedMarket = $legacyLocation
                ? $markets->first(fn($market) => strcasecmp($market->name, $legacyLocation->country_name) === 0)
                : null;
            if ($matchedMarket) {
                $selectedMarketId = (string) $matchedMarket->_id;
            }
        }
    @endphp

    <select
        name="location_id"
        class="form-input"
        required
    >
        <option value="">Select Market</option>

        @foreach($markets as $market)
            <option
                value="{{ (string)$market->_id }}"
                {{ $selectedMarketId === (string)$market->_id ? 'selected' : '' }}
            >
                {{ $market->name }}@if($market->code) ({{ strtoupper($market->code) }}) @endif
            </option>
        @endforeach

    </select>
</div>

    </div>
</x-admin.form-page>

@endif
