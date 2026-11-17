<style>
    .content-panel { background:white; border:1px solid var(--border); border-radius:12px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .section-title { font-size:16px; font-weight:700; color:var(--text); margin-bottom:16px; padding-bottom:10px; border-bottom:2px solid var(--border); }
    .form-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:20px 28px; margin-bottom:24px; }
    .form-grid-3 { display:grid; grid-template-columns:repeat(3, 1fr); gap:20px 28px; margin-bottom:24px; }
    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-group.full { grid-column:1 / -1; }
    .form-label { font-size:13px; font-weight:600; color:var(--text); }
    .form-label span { color:var(--danger); margin-left:2px; }
    .form-input { width:100%; padding:10px 14px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; color:var(--text); outline:none; transition:border-color .2s,box-shadow .2s; background:white; box-sizing:border-box; }
    .form-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.1); }
    .form-input::placeholder { color:#CBD5E1; }
    .form-hint { font-size:11px; color:var(--muted); margin-top:2px; }
    .btn-save { display:inline-flex; align-items:center; gap:8px; padding:10px 28px; background:#10B981; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; transition:background .15s; }
    .btn-save:hover { background:#059669; }
    .btn-back { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--text); color:white; border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none; transition:background .15s; white-space:nowrap; border:1.5px solid var(--text); }
    .alert-error { padding:12px 16px; background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13.5px; margin-bottom:20px; }
    .toggle-wrap { display:flex; align-items:center; gap:10px; }
    .toggle-input { display:none; }
    .toggle-slider { width:44px; height:24px; background:#CBD5E1; border-radius:12px; position:relative; cursor:pointer; transition:background .2s; }
    .toggle-slider::after { content:''; position:absolute; width:18px; height:18px; background:white; border-radius:50%; top:3px; left:3px; transition:transform .2s; box-shadow:0 1px 3px rgba(0,0,0,.15); }
    .toggle-input:checked + .toggle-slider { background:#10B981; }
    .toggle-input:checked + .toggle-slider::after { transform:translateX(20px); }
    .toggle-label { font-size:13px; font-weight:500; color:var(--text); }
</style>
