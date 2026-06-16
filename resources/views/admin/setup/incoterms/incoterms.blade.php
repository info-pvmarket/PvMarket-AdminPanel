{{--
    views/admin/setup/incoterms/incoterms.blade.php
    ─────────────────────────────────────────────────
    ONE file handles all 3 modes:
      $mode = 'index'  → shows the table list
      $mode = 'create' → shows multi-row add form
      $mode = 'edit'   → shows single-record edit form

    Uses 3 reusable components:
      <x-admin.index-page>       — list table
      <x-admin.table-form-page>  — multi-row add form
      <x-admin.form-page>        — single-record edit form
--}}

{{-- ═══════════════════════════════════════
     MODE: INDEX
════════════════════════════════════════ --}}
@if($mode === 'index')

<x-admin.index-page
    title="Incoterms"
    create-route="{{ route('admin.setup.incoterms.create') }}"
    search-route="{{ route('admin.setup.incoterms.index') }}"
    search-placeholder="Search By Term Name..."
    :total="$records->total()"
    :first-item="$records->firstItem() ?? 0"
    :last-item="$records->lastItem() ?? 0"
    :paginator="$records"
>
    <x-slot name="columns">
    <th class="center">Code</th> 
    <th class="center">Term Name</th>
    <th class="center" style="width:120px;">Action</th>
</x-slot>

    <x-slot name="rows">
        @forelse ($records as $index => $term)
        <tr>
            <td class="center" style="font-weight:700;color:var(--muted);font-size:13px;width:70px;">
                {{ $records->firstItem() + $index }}
            </td>
            <td class="center" style="font-weight:600;">{{ $term->code }}</td>
            <td class="center" style="font-weight:600;">{{ $term->name }}</td>
            <td>
                <div class="action-btns">
                    <a href="{{ route('admin.setup.incoterms.edit', $term->id) }}"
                       class="action-icon edit" title="Edit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </a>
                    <button class="action-icon delete" title="Delete"
                        onclick="if(confirm('Delete?')) document.getElementById('del-{{ $term->id }}').submit();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                        </svg>
                    </button>
                    <form id="del-{{ $term->id }}" method="POST"
                          action="{{ route('admin.setup.incoterms.destroy', $term->id) }}"
                          style="display:none;">
                        @csrf @method('DELETE')
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="3">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                        <rect x="9" y="3" width="6" height="4" rx="2"/>
                    </svg>
                    <p>No incoterms yet. Click <strong>Add +</strong> to create one.</p>
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


{{-- ═══════════════════════════════════════
     MODE: CREATE  (multi-row table form)
════════════════════════════════════════ --}}
@elseif($mode === 'create')

<x-admin.table-form-page
    title="Add Incoterm"
    :back-route="route('admin.setup.incoterms.index')"
    :action="route('admin.setup.incoterms.store')"
>
    <x-slot name="columns">
        <th style="min-width:160px;">Code</th>
        <th style="min-width:400px;">Term Name</th>
    </x-slot>

    <x-slot name="row">
        <input
            type="text"
            name="items[{INDEX}][code]"
            class="form-input"
            placeholder="e.g. EXW"
            required
        />
    </td>
        <td>
            <input
                type="text"
                name="items[{INDEX}][name]"
                class="form-input"
                placeholder="e.g. Free on Board (FOB)"
                required
            />
        </td>
    </x-slot>
</x-admin.table-form-page>


{{-- ═══════════════════════════════════════
     MODE: EDIT  (single record form)
════════════════════════════════════════ --}}
@elseif($mode === 'edit')

<x-admin.form-page
    title="Edit Incoterm"
    :back-route="route('admin.setup.incoterms.index')"
    :action="route('admin.setup.incoterms.update', $record->id)"
    method="PUT"
>
    <div class="form-grid">
        <div class="form-group" style="max-width:200px;">   {{-- ← add --}}
    <label class="form-label">Code <span>*</span></label>
    <input
        type="text"
        name="code"
        class="form-input"
        placeholder="e.g. EXW"
        value="{{ old('code', $record->code) }}"
        required
    />
</div>
        <div class="form-group full-width" style="max-width:500px;">
            <label class="form-label">Term Name <span>*</span></label>
            <input
                type="text"
                name="name"
                class="form-input"
                placeholder="e.g. Free on Board (FOB)"
                value="{{ old('name', $record->name) }}"
                required
            />
        </div>
    </div>
</x-admin.form-page>

@endif