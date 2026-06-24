{{-- views/admin/setup/roles/roles.blade.php --}}
{{-- Handles: index | create | edit --}}

@if($mode === 'create')
@extends('layouts.admin')

{{-- ═══ CREATE MODE ═══ --}}
@section('title', 'Add User Roles')

@section('styles')
<style>
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .form-table { width:100%; border-collapse:collapse; }
    .form-table thead tr { border-bottom:2px solid var(--border); }
    .form-table th { padding:10px 14px; font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.5px; text-align:left; }
    .form-table td { padding:10px 14px; vertical-align:middle; }
    .form-table tbody tr { border-bottom:1px solid #F1F5F9; }
    .form-table tbody tr:last-child { border-bottom:none; }
    .sno { font-size:13px; font-weight:700; color:var(--muted); width:60px; }
    .form-row-input { width:100%; padding:9px 13px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; color:var(--text); outline:none; transition:border-color .2s,box-shadow .2s; background:white; box-sizing:border-box; }
    .form-row-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .form-row-input::placeholder { color:#CBD5E1; }
    .form-row-select { width:100%; padding:9px 13px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; color:var(--text); outline:none; transition:border-color .2s,box-shadow .2s; background:white; cursor:pointer; box-sizing:border-box; }
    .form-row-select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .checkbox-group { display:flex; gap:16px; align-items:center; flex-wrap:wrap; }
    .checkbox-item { display:flex; align-items:center; gap:6px; cursor:pointer; }
    .checkbox-item input[type="checkbox"] { width:16px; height:16px; accent-color:var(--primary); cursor:pointer; }
    .checkbox-item label { font-size:13px; font-weight:500; color:var(--text); cursor:pointer; }
    .btn-add-row { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--primary); color:white; border:none; border-radius:8px; font-size:13.5px; font-weight:600; cursor:pointer; font-family:inherit; transition:background .15s; }
    .btn-add-row:hover { background:var(--primary-d); }
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; white-space:nowrap; border:1.5px solid var(--text); }
    .btn-save { display:inline-flex; align-items:center; gap:8px; padding:10px 28px; background:#10B981; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; transition:background .15s; }
    .btn-save:hover { background:#059669; }
    .btn-remove { background:none; border:none; cursor:pointer; color:#EF4444; padding:6px; border-radius:6px; transition:background .15s; }
    .btn-remove:hover { background:#FEF2F2; }
    .alert-error { padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Add User Roles</h1>
    <a href="{{ route('admin.setup.roles.index') }}" class="btn-back">← Back</a>
</div>

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<div class="content-panel">

    <div style="display:flex; justify-content:flex-end; margin-bottom:16px;">
        <button type="button" class="btn-add-row" onclick="addRow()">+ Add More</button>
    </div>

    <form method="POST" action="{{ route('admin.setup.roles.store') }}" id="createForm">
        @csrf

        <table class="form-table">
            <thead>
                <tr>
                    <th style="width:60px;">S.No</th>
                    <th style="min-width:200px;">Role <span style="color:#EF4444;">*</span></th>
                    <th style="min-width:200px;">Slug <span style="color:#EF4444;">*</span></th>
                    <th style="min-width:160px;">Guard Name <span style="color:#EF4444;">*</span></th>
                    <th style="min-width:220px;">Access Types</th>
                    <th style="width:60px;">Action</th>
                </tr>
            </thead>
            <tbody id="rowsBody">
                <tr id="row-1">
                    <td class="sno">1.</td>
                    <td>
                        <input type="text" name="roles[0][role]" class="form-row-input role-name-input"
                               placeholder="e.g. Super Admin" required/>
                    </td>
                    <td>
                        <input type="text" name="roles[0][slug]" class="form-row-input role-slug-input"
                               placeholder="e.g. super-admin" required/>
                    </td>
                    <td>
                        <select name="roles[0][guard_name]" class="form-row-select guard-name-val" required>
                            <option value="" disabled selected>Select guard</option>
                            <option value="web">web</option>
                            <option value="api">api</option>
                            <option value="admin">admin</option>
                        </select>
                    </td>
                    <td>
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" name="roles[0][access_types][]" value="buyer" id="buyer-0"/>
                                <label for="buyer-0">Buyer</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="roles[0][access_types][]" value="seller" id="seller-0"/>
                                <label for="seller-0">Seller</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="roles[0][access_types][]" value="projects" id="projects-0"/>
                                <label for="projects-0">Projects</label>
                            </div>
                        </div>
                    </td>
                    <td>
                        <button type="button" class="btn-remove" onclick="removeRow(this)" title="Remove">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div style="display:flex; justify-content:flex-end; margin-top:24px; padding-top:20px; border-top:1px solid var(--border);">
            <button type="submit" class="btn-save">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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

@push('scripts')
<script>
let rowCount = 1;

function addRow() {
    rowCount++;
    const idx = rowCount - 1;
    const body = document.getElementById('rowsBody');
    const tr   = document.createElement('tr');
    tr.id      = 'row-' + rowCount;
    tr.innerHTML = `
        <td class="sno">${rowCount}.</td>
        <td>
            <input type="text" name="roles[${idx}][role]" class="form-row-input role-name-input"
                   placeholder="e.g. Super Admin" required/>
        </td>
        <td>
            <input type="text" name="roles[${idx}][slug]" class="form-row-input role-slug-input"
                   placeholder="e.g. super-admin" required/>
        </td>
        <td>
            <select name="roles[${idx}][guard_name]" class="form-row-select guard-name-val" required>
                <option value="" disabled selected>Select guard</option>
                <option value="web">web</option>
                <option value="api">api</option>
                <option value="admin">admin</option>
            </select>
        </td>
        <td>
            <div class="checkbox-group">
                <div class="checkbox-item">
                    <input type="checkbox" name="roles[${idx}][access_types][]" value="buyer" id="buyer-${idx}"/>
                    <label for="buyer-${idx}">Buyer</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" name="roles[${idx}][access_types][]" value="seller" id="seller-${idx}"/>
                    <label for="seller-${idx}">Seller</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" name="roles[${idx}][access_types][]" value="projects" id="projects-${idx}"/>
                    <label for="projects-${idx}">Projects</label>
                </div>
            </div>
        </td>
        <td>
            <button type="button" class="btn-remove" onclick="removeRow(this)" title="Remove">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
            </button>
        </td>
    `;
    body.appendChild(tr);
    renumber();
    bindSlugAuto(tr);
}

function removeRow(btn) {
    if (document.querySelectorAll('#rowsBody tr').length === 1) return;
    btn.closest('tr').remove();
    renumber();
}

function renumber() {
    document.querySelectorAll('#rowsBody tr').forEach((tr, i) => {
        tr.querySelector('.sno').textContent = (i + 1) + '.';
        tr.querySelectorAll('input, select').forEach(el => {
            el.name = el.name.replace(/roles\[\d+\]/, `roles[${i}]`);
            // Update checkbox IDs
            if (el.id && (el.id.startsWith('buyer-') || el.id.startsWith('seller-') || el.id.startsWith('projects-'))) {
                const prefix = el.id.split('-')[0];
                el.id = prefix + '-' + i;
            }
        });
        // Update labels for attribute
        tr.querySelectorAll('label').forEach(label => {
            const forAttr = label.getAttribute('for');
            if (forAttr && (forAttr.startsWith('buyer-') || forAttr.startsWith('seller-') || forAttr.startsWith('projects-'))) {
                const prefix = forAttr.split('-')[0];
                label.setAttribute('for', prefix + '-' + i);
            }
        });
    });
}

function bindSlugAuto(tr) {
    const roleInput = tr.querySelector('.role-name-input');
    const slugInput = tr.querySelector('.role-slug-input');
    if (!roleInput || !slugInput) return;

    roleInput.addEventListener('input', function () {
        if (!slugInput.dataset.userEdited) {
            slugInput.value = this.value.toLowerCase().trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-');
        }
    });

    slugInput.addEventListener('input', function () {
        this.dataset.userEdited = 'true';
    });
}

// Bind auto-slug on the first default row
document.querySelectorAll('#rowsBody tr').forEach(tr => bindSlugAuto(tr));

// Guard on submit
document.getElementById('createForm').addEventListener('submit', function (e) {
    const missing = Array.from(document.querySelectorAll('.guard-name-val'))
                        .some(el => !el.value);
    if (missing) {
        e.preventDefault();
        alert('Please select a Guard Name for every row before saving.');
    }
});
</script>
@endpush


@elseif($mode === 'edit')


{{-- ═══ EDIT MODE ═══ --}}
@section('title', 'Edit User Role')

@section('styles')
<style>
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .form-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px 28px; margin-bottom:20px; }
    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-label { font-size:13px; font-weight:600; color:var(--text); }
    .form-label span { color:var(--danger); margin-left:2px; }
    .form-input { width:100%; padding:9px 13px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; color:var(--text); outline:none; transition:border-color .2s,box-shadow .2s; background:white; box-sizing:border-box; }
    .form-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .form-input::placeholder { color:#CBD5E1; }
    .form-hint { font-size:11px; color:var(--muted); margin-top:3px; }
    .checkbox-group { display:flex; gap:20px; align-items:center; flex-wrap:wrap; margin-top:8px; }
    .checkbox-item { display:flex; align-items:center; gap:8px; cursor:pointer; }
    .checkbox-item input[type="checkbox"] { width:18px; height:18px; accent-color:var(--primary); cursor:pointer; }
    .checkbox-item label { font-size:14px; font-weight:500; color:var(--text); cursor:pointer; }
    .btn-save { display:inline-flex; align-items:center; gap:8px; padding:10px 28px; background:#10B981; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; transition:background .15s; }
    .btn-save:hover { background:#059669; }
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; white-space:nowrap; border:1.5px solid var(--text); }
    .alert-error { padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px; }

    /* Toggle Switch */
    .toggle-switch { position:relative; display:inline-block; width:50px; height:26px; }
    .toggle-switch input { opacity:0; width:0; height:0; }
    .toggle-slider { position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background:#CBD5E1; transition:.3s; border-radius:26px; }
    .toggle-slider:before { position:absolute; content:""; height:20px; width:20px; left:3px; bottom:3px; background:white; transition:.3s; border-radius:50%; box-shadow:0 2px 4px rgba(0,0,0,.15); }
    .toggle-switch input:checked + .toggle-slider { background:var(--primary); }
    .toggle-switch input:checked + .toggle-slider:before { transform:translateX(24px); }

    /* Permissions Container */
    .permissions-container { display:flex; flex-direction:column; gap:16px; }

    .permission-group { background:#F8FAFC; border-radius:10px; border:1px solid var(--border); overflow:hidden; }
    .permission-group-header { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; background:#E2E8F0; border-bottom:1px solid var(--border); }
    .group-title { font-size:13px; font-weight:700; color:var(--text); text-transform:uppercase; letter-spacing:0.5px; }
    .btn-group-toggle { padding:4px 10px; font-size:11px; font-weight:600; border-radius:4px; cursor:pointer; background:white; color:var(--primary); border:1px solid var(--primary); transition:all .15s; }
    .btn-group-toggle:hover { background:var(--primary); color:white; }

    .permission-group-items { display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:10px; padding:14px; }
    .permission-item { display:flex; align-items:center; gap:10px; padding:10px 14px; background:white; border-radius:8px; border:1px solid #E2E8F0; transition:all .15s; cursor:pointer; }
    .permission-item:hover { border-color:var(--primary); background:#F0F9FF; }
    .permission-item input[type="checkbox"] { width:18px; height:18px; accent-color:var(--primary); cursor:pointer; flex-shrink:0; }
    .permission-item label { font-size:13px; font-weight:500; color:var(--text); cursor:pointer; line-height:1.3; }
    .permission-item:has(input:checked) { border-color:var(--primary); background:#EFF6FF; }

    .btn-select-all, .btn-select-none { padding:6px 14px; font-size:12px; font-weight:600; border-radius:6px; cursor:pointer; transition:all .15s; margin-right:8px; }
    .btn-select-all { background:#10B981; color:white; border:none; }
    .btn-select-all:hover { background:#059669; }
    .btn-select-none { background:white; color:var(--text); border:1px solid var(--border); }
    .btn-select-none:hover { background:#F1F5F9; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">Edit User Role</h1>
    <a href="{{ route('admin.setup.roles.index') }}" class="btn-back">← Back</a>
</div>

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<div class="content-panel">
    <form method="POST"
          action="{{ route('admin.setup.roles.update', $record->id) }}"
          id="editForm">
        @csrf
        @method('PUT')

        <div class="form-grid">

            {{-- Role --}}
            <div class="form-group">
                <label class="form-label">Role <span>*</span></label>
                <input type="text" name="role" class="form-input" id="edit_role_name"
                       placeholder="e.g. Super Admin"
                       value="{{ old('role', $record->role) }}" required/>
            </div>

            {{-- Slug --}}
            <div class="form-group">
                <label class="form-label">Slug <span>*</span></label>
                <input type="text" name="slug" class="form-input" id="edit_slug"
                       placeholder="e.g. super-admin"
                       value="{{ old('slug', $record->slug) }}" required/>
                <span class="form-hint">Lowercase, hyphen-separated</span>
            </div>

            {{-- Guard Name --}}
            <div class="form-group">
                <label class="form-label">Guard Name <span>*</span></label>
                <select name="guard_name" class="form-input" id="edit_guard_name"
                        required style="cursor:pointer;">
                    <option value="" disabled>Select guard</option>
                    <option value="web"   {{ old('guard_name', $record->guard_name) === 'web'   ? 'selected' : '' }}>web</option>
                    <option value="api"   {{ old('guard_name', $record->guard_name) === 'api'   ? 'selected' : '' }}>api</option>
                    <option value="admin" {{ old('guard_name', $record->guard_name) === 'admin' ? 'selected' : '' }}>admin</option>
                </select>
            </div>

        </div>

        {{-- Access Types --}}
        <div class="form-group" style="margin-bottom:20px;">
            <label class="form-label">Access Types</label>
            @php
                $accessTypes = old('access_types', $record->access_types ?? []);
            @endphp
            <div class="checkbox-group">
                <div class="checkbox-item">
                    <input type="checkbox" name="access_types[]" value="buyer" id="edit_buyer"
                           {{ in_array('buyer', $accessTypes) ? 'checked' : '' }}/>
                    <label for="edit_buyer">Buyer</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" name="access_types[]" value="seller" id="edit_seller"
                           {{ in_array('seller', $accessTypes) ? 'checked' : '' }}/>
                    <label for="edit_seller">Seller</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" name="access_types[]" value="projects" id="edit_projects"
                           {{ in_array('projects', $accessTypes) ? 'checked' : '' }}/>
                    <label for="edit_projects">Projects</label>
                </div>
            </div>
        </div>

        {{-- Can Access Admin Toggle --}}
        <div class="form-group" style="margin-bottom:20px;">
            <label class="form-label">Admin Panel Access</label>
            <div style="display:flex; align-items:center; gap:12px; margin-top:8px;">
                <label class="toggle-switch">
                    <input type="checkbox" name="can_access_admin" id="can_access_admin" value="1"
                           {{ old('can_access_admin', $record->can_access_admin ?? false) ? 'checked' : '' }}
                           onchange="toggleAdminPermissions()"/>
                    <span class="toggle-slider"></span>
                </label>
                <span style="font-size:14px; font-weight:500; color:var(--text);">Can Access Admin Panel</span>
            </div>
        </div>

        {{-- Admin Permissions (shown when can_access_admin is true) --}}
        <div class="form-group" id="admin-permissions-group" style="margin-bottom:20px; display:{{ old('can_access_admin', $record->can_access_admin ?? false) ? 'block' : 'none' }};">
            <label class="form-label">Admin Permissions</label>
            <p style="font-size:12px; color:var(--muted); margin-bottom:12px;">Select which sections this role can access in the admin panel.</p>

            <div style="margin-bottom:12px;">
                <button type="button" class="btn-select-all" onclick="selectAllPermissions()">Select All</button>
                <button type="button" class="btn-select-none" onclick="deselectAllPermissions()">Deselect All</button>
            </div>

            @php
                $currentPermissions = old('admin_permissions', $record->admin_permissions ?? []);
            @endphp

            <div class="permissions-container">
                @foreach($groupedPermissions as $groupName => $permissions)
                <div class="permission-group">
                    <div class="permission-group-header">
                        <span class="group-title">{{ $groupName }}</span>
                        <button type="button" class="btn-group-toggle" onclick="toggleGroup(this, '{{ Str::slug($groupName) }}')">Select All</button>
                    </div>
                    <div class="permission-group-items" data-group="{{ Str::slug($groupName) }}">
                        @foreach($permissions as $key => $label)
                        <div class="permission-item">
                            <input type="checkbox" name="admin_permissions[]" value="{{ $key }}" id="perm_{{ Str::slug($key) }}"
                                   {{ in_array($key, $currentPermissions) ? 'checked' : '' }}/>
                            <label for="perm_{{ Str::slug($key) }}">{{ $label }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; padding-top:20px; border-top:1px solid var(--border);">
            <button type="submit" class="btn-save">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
// Global functions for admin permissions
window.toggleAdminPermissions = function() {
    var checkbox = document.getElementById('can_access_admin');
    var permissionsGroup = document.getElementById('admin-permissions-group');
    if (permissionsGroup) {
        permissionsGroup.style.display = checkbox.checked ? 'block' : 'none';
    }
};

window.selectAllPermissions = function() {
    var container = document.getElementById('admin-permissions-group');
    if (!container) return;
    var checkboxes = container.querySelectorAll('.permission-item input[type="checkbox"]');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = true;
    }
};

window.deselectAllPermissions = function() {
    var container = document.getElementById('admin-permissions-group');
    if (!container) return;
    var checkboxes = container.querySelectorAll('.permission-item input[type="checkbox"]');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = false;
    }
};

window.toggleGroup = function(btn, groupSlug) {
    var container = document.querySelector('[data-group="' + groupSlug + '"]');
    if (!container) return;
    var checkboxes = container.querySelectorAll('input[type="checkbox"]');
    var allChecked = true;
    for (var i = 0; i < checkboxes.length; i++) {
        if (!checkboxes[i].checked) {
            allChecked = false;
            break;
        }
    }
    for (var j = 0; j < checkboxes.length; j++) {
        checkboxes[j].checked = !allChecked;
    }
    btn.textContent = allChecked ? 'Select All' : 'Deselect All';
};

// Slug auto-generation
document.addEventListener('DOMContentLoaded', function () {
    var roleInput = document.getElementById('edit_role_name');
    var slugInput = document.getElementById('edit_slug');
    var userEdited = slugInput && slugInput.value !== '';

    if (roleInput) {
        roleInput.addEventListener('input', function () {
            if (!userEdited && slugInput) {
                slugInput.value = this.value.toLowerCase().trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-');
            }
        });
    }

    if (slugInput) {
        slugInput.addEventListener('input', function () {
            userEdited = true;
        });
    }
});
</script>
@endsection


@else


{{-- ═══ INDEX MODE ═══ --}}
@section('title', 'User Roles')

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
    .entries-select { padding:7px 26px 7px 10px; border:1.5px solid var(--border); border-radius:7px; font-family:inherit; font-size:13px; font-weight:600; color:var(--text); background:white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 7px center; -webkit-appearance:none; appearance:none; outline:none; cursor:pointer; }
    .entries-select:hover,.entries-select:focus { border-color:var(--primary); }
    .data-table { width:100%; border-collapse:collapse; }
    .data-table thead { background:#F0F9FF; }
    .data-table th { padding:11px 16px; text-align:left; font-size:12px; font-weight:700; color:var(--primary-d); text-transform:uppercase; letter-spacing:.5px; border-bottom:2px solid #BAE6FD; white-space:nowrap; }
    .data-table th.center,.data-table td.center { text-align:center; }
    .data-table td { padding:16px; font-size:13.5px; color:var(--text); border-bottom:1px solid #F1F5F9; vertical-align:middle; }
    .data-table tr:last-child td { border-bottom:none; }
    .data-table tbody tr:nth-child(odd) td { background:white; }
    .data-table tbody tr:nth-child(even) td { background:#FAFBFD; }
    .data-table tbody tr:hover td { background:#E0F2FE !important; }
    .role-name { font-weight:700; font-size:15px; color:var(--text); }
    .action-btns { display:flex; align-items:center; justify-content:center; gap:8px; }
    .action-icon { width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; border:1.5px solid transparent; background:none; border-radius:7px; transition:all .15s; text-decoration:none; }
    .action-icon svg { width:15px; height:15px; }
    .action-icon.edit   { color:#D97706; border-color:#FDE68A; background:#FFFBEB; }
    .action-icon.toggle { color:#059669; border-color:#A7F3D0; background:#ECFDF5; }
    .action-icon.delete { color:#DC2626; border-color:#FECACA; background:#FEF2F2; }
    .action-icon:hover { transform:translateY(-2px) scale(1.08); box-shadow:0 3px 8px rgba(0,0,0,.12); }
    .action-icon.edit:hover   { background:#FEF3C7; border-color:#F59E0B; }
    .action-icon.toggle:hover { background:#D1FAE5; border-color:#10B981; }
    .action-icon.delete:hover { background:#FEE2E2; border-color:#EF4444; }
    .table-footer { padding:12px 20px; font-size:13px; color:var(--muted); border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; background:#FAFBFD; }
    .empty-state { text-align:center; padding:52px 20px; color:var(--muted); }
    .empty-state svg { width:42px; height:42px; margin:0 auto 12px; opacity:.2; display:block; }
    .empty-state p { font-size:14px; font-weight:500; }
    .alert-success { padding:12px 16px; background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; border-radius:8px; font-size:13.5px; font-weight:500; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800; color:var(--text);">User Roles</h1>
    <a href="{{ route('admin.setup.roles.create') }}"
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
        <form method="GET" action="{{ route('admin.setup.roles.index') }}" class="search-group">
            <label>Search:</label>
            <div class="search-input-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search By Role Name..." class="search-input"/>
            </div>
        </form>

        <form method="GET" action="{{ route('admin.setup.roles.index') }}" class="entries-group">
            Show
            <select name="entries" class="entries-select" onchange="this.form.submit()">
                @foreach([10, 25, 50, 100] as $n)
                    <option value="{{ $n }}" {{ request('entries', 10) == $n ? 'selected' : '' }}>{{ $n }}</option>
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
                <th class="center" style="width:70px;">S.No</th>
                <th>Role</th>
                <th>Slug</th>
                <th>Guard Name</th>
                <th>Access Types</th>
                <th>Admin Access</th>
                <th class="center" style="width:130px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $role)
            <tr>
                <td class="center" style="font-weight:700; color:var(--muted); font-size:13px;">
                    {{ $records->firstItem() + $index }}
                </td>
                <td>
                    <span class="role-name">{{ $role->role }}</span>
                </td>
                <td>
                    <span style="
                        display:inline-flex; align-items:center;
                        padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;
                        letter-spacing:.4px; font-family:monospace;
                        background:#F1F5F9; color:#475569; border:1px solid #E2E8F0;
                    ">{{ $role->slug }}</span>
                </td>
                <td>
                    @php
                        $guardColors = [
                            'web'   => ['bg'=>'#EFF6FF','color'=>'#1D4ED8','border'=>'#BFDBFE'],
                            'api'   => ['bg'=>'#F0FDF4','color'=>'#15803D','border'=>'#BBF7D0'],
                            'admin' => ['bg'=>'#FDF4FF','color'=>'#7E22CE','border'=>'#E9D5FF'],
                        ];
                        $gc = $guardColors[$role->guard_name] ?? ['bg'=>'#F8FAFC','color'=>'#64748B','border'=>'#E2E8F0'];
                    @endphp
                    <span style="
                        display:inline-flex; align-items:center;
                        padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;
                        letter-spacing:.4px; text-transform:uppercase;
                        background:{{ $gc['bg'] }}; color:{{ $gc['color'] }}; border:1px solid {{ $gc['border'] }};
                    ">{{ $role->guard_name }}</span>
                </td>
                <td>
                    @php
                        $accessTypes = $role->access_types ?? [];
                        $accessColors = [
                            'buyer'    => ['bg'=>'#FEF3C7','color'=>'#92400E','border'=>'#FDE68A'],
                            'seller'   => ['bg'=>'#DBEAFE','color'=>'#1E40AF','border'=>'#BFDBFE'],
                            'projects' => ['bg'=>'#FCE7F3','color'=>'#9D174D','border'=>'#FBCFE8'],
                        ];
                    @endphp
                    @if(count($accessTypes) > 0)
                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                            @foreach($accessTypes as $type)
                                @php $ac = $accessColors[$type] ?? ['bg'=>'#F8FAFC','color'=>'#64748B','border'=>'#E2E8F0']; @endphp
                                <span style="
                                    display:inline-flex; align-items:center;
                                    padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;
                                    letter-spacing:.4px; text-transform:capitalize;
                                    background:{{ $ac['bg'] }}; color:{{ $ac['color'] }}; border:1px solid {{ $ac['border'] }};
                                ">{{ $type }}</span>
                            @endforeach
                        </div>
                    @else
                        <span style="color:#94A3B8; font-size:12px;">—</span>
                    @endif
                </td>
                <td>
                    @if($role->can_access_admin)
                        <span style="
                            display:inline-flex; align-items:center; gap:4px;
                            padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700;
                            background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0;
                        ">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Yes
                        </span>
                        @php $permCount = count($role->admin_permissions ?? []); @endphp
                        @if($permCount > 0)
                            <span style="display:block; font-size:10px; color:#64748B; margin-top:4px;">{{ $permCount }} permission(s)</span>
                        @endif
                    @else
                        <span style="
                            display:inline-flex; align-items:center;
                            padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700;
                            background:#FEE2E2; color:#991B1B; border:1px solid #FECACA;
                        ">No</span>
                    @endif
                </td>
                <td>
                    <div class="action-btns">
                        <a href="{{ route('admin.setup.roles.edit', $role->id) }}"
                           class="action-icon edit" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </a>
                        <button class="action-icon delete" title="Delete"
                            onclick="if(confirm('Delete {{ addslashes($role->role) }}?')) document.getElementById('del-{{ $role->id }}').submit();">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </button>
                        <form id="del-{{ $role->id }}" method="POST"
                              action="{{ route('admin.setup.roles.destroy', $role->id) }}"
                              style="display:none;">
                            @csrf @method('DELETE')
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                        </svg>
                        <p>No roles yet. Click <strong>Add +</strong> to create one.</p>
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
                    <a href="{{ $url }}" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:6px;border:1.5px solid var(--border);background:white;color:var(--text);text-decoration:none;font-size:13px;font-weight:500;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';this.style.background='var(--primary-l)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)';this.style.background='white';">{{ $url }}</a>
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