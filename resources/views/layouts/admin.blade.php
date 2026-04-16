<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Powernsun Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:     #0EA5E9;
            --primary-d:   #0284C7;
            --primary-l:   #F0F9FF;
            --primary-m:   #BAE6FD;
            --accent:      #F97316;
            --text:        #1E293B;
            --muted:       #64748B;
            --light:       #F8FAFC;
            --border:      #E2E8F0;
            --white:       #FFFFFF;
            --success:     #10B981;
            --danger:      #EF4444;
            --sidebar-w:   280px;
            --topbar-h:    70px;
        }

        [data-theme="orange"] {
            --primary:   #F97316;
            --primary-d: #EA580C;
            --primary-m: #FDBA74;
            --primary-l: #FFF7ED;
        }

        html, body {
            height: 100%;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--light);
            color: var(--text);
        }

        .admin-layout { display: flex; min-height: 100vh; }

        /* ══════════════════════
           SIDEBAR
        ══════════════════════ */
        .sidebar {
            width: var(--sidebar-w);
            height: 100vh;
            background: white;
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
            box-shadow: 2px 0 12px rgba(14,165,233,.06);
            overflow: hidden;
            transition: transform .25s ease;
        }

        .sidebar.sidebar-hidden {
    transform: translateX(-100%);
}

.main-wrap.main-expanded {
    margin-left: 0;
}

        .sidebar-logo {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            min-height: var(--topbar-h);
            flex-shrink: 0;
            transition: background .15s;
        }

        .sidebar-logo:hover { background: var(--primary-l); }

        .sidebar-logo-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-d) 100%);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            transition: transform .2s, box-shadow .2s;
        }

        .sidebar-logo:hover .sidebar-logo-icon {
            transform: rotate(-5deg) scale(1.05);
            box-shadow: 0 4px 12px rgba(14,165,233,.3);
        }

        .sidebar-logo-name {
            font-size: 18px;
            font-weight: 800;
            color: var(--primary-d);
            letter-spacing: -0.5px;
            line-height: 1.1;
        }

        .sidebar-logo-name span { color: var(--accent); }
        .sidebar-logo-tagline { font-size: 10px; color: var(--muted); font-weight: 500; }

        .sidebar-nav {
            flex: 1;
            padding: 10px 10px;
            overflow-y: auto;
            min-height: 0;
        }

        .sidebar-nav::-webkit-scrollbar { width: 3px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: var(--primary-m); border-radius: 3px; }
        .sidebar-nav::-webkit-scrollbar-thumb:hover { background: var(--primary); }

        .nav-section-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--muted);
            background: var(--light);
            border-radius: 6px;
            margin: 10px 0 2px 0;
            border: 1px solid var(--border);
        }

        .nav-section-header svg { width: 12px; height: 12px; opacity: .5; flex-shrink: 0; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--text);
            font-size: 14px;
            font-weight: 500;
            transition: all .15s;
            margin-bottom: 1px;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            font-family: inherit;
            position: relative;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%) scaleY(0);
            width: 3px;
            height: 60%;
            background: var(--primary);
            border-radius: 0 3px 3px 0;
            transition: transform .15s;
        }

        .nav-item:hover::before { transform: translateY(-50%) scaleY(1); }

        .nav-item:hover {
            background: var(--primary-l);
            color: var(--primary-d);
            padding-left: 16px;
        }

        .nav-item.active {
            background: linear-gradient(90deg, var(--primary-l) 0%, #E0F2FE 100%);
            color: var(--primary-d);
            font-weight: 700;
            border-left: 3px solid var(--primary);
            padding-left: 9px;
        }

        .nav-item.active::before { display: none; }

        .nav-item svg {
            width: 18px; height: 18px;
            flex-shrink: 0;
            color: var(--muted);
            transition: color .15s, transform .15s;
        }

        .nav-item:hover svg { color: var(--primary); transform: scale(1.1); }
        .nav-item.active svg { color: var(--primary-d); }

        .nav-arrow {
            margin-left: auto;
            width: 14px; height: 14px;
            transition: transform .2s;
            color: var(--muted) !important;
            transform: unset !important;
        }

        .nav-item:hover .nav-arrow { transform: translateX(2px) !important; }
        .nav-item.open .nav-arrow { transform: rotate(90deg) !important; }

        .nav-sub { display: none; padding-left: 38px; }
        .nav-sub.open { display: block; }

        .nav-sub-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 6px;
            text-decoration: none;
            color: var(--muted);
            font-size: 13px;
            font-weight: 500;
            transition: all .15s;
            margin-bottom: 1px;
        }

        .nav-sub-item::before {
            content: '';
            width: 5px; height: 5px;
            border-radius: 50%;
            background: var(--border);
            flex-shrink: 0;
            transition: background .15s;
        }

        .nav-sub-item:hover { color: var(--primary-d); background: var(--primary-l); }
        .nav-sub-item:hover::before { background: var(--primary); }
        .nav-sub-item.active { color: var(--primary-d); font-weight: 600; }
        .nav-sub-item.active::before { background: var(--primary); }

        .sidebar-footer {
            padding: 10px 10px;
            border-top: 1px solid var(--border);
            flex-shrink: 0;
        }

        .logout-nav-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: var(--danger);
            font-size: 14px;
            font-weight: 600;
            width: 100%;
            border: none;
            background: none;
            cursor: pointer;
            font-family: inherit;
            transition: all .15s;
        }

        .logout-nav-btn svg { width: 18px; height: 18px; color: var(--danger); transition: transform .15s; }
        .logout-nav-btn:hover { background: #FEF2F2; padding-left: 16px; }
        .logout-nav-btn:hover svg { transform: translateX(2px); }

        /* ══════════════════════
           MAIN WRAP
        ══════════════════════ */
        .main-wrap {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left .25s ease;
        }

        /* ══════════════════════
           TOPBAR
        ══════════════════════ */
        .topbar {
    height: var(--topbar-h);
    background: white;
    border: 1px solid var(--border);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    position: sticky;
    top: 12px;
    z-index: 50;
    margin: 12px 16px 0 16px;
    box-shadow: 0 4px 24px rgba(14,165,233,.10), 0 1px 4px rgba(0,0,0,.06);
}
        .topbar-left { display: flex; align-items: center; gap: 12px; }

        .topbar-hamburger {
            color: var(--muted);
            border: 1px solid transparent;
            background: none;
            cursor: pointer;
            padding: 7px;
            display: flex; align-items: center;
            border-radius: 8px;
            transition: all .15s;
        }

        .topbar-hamburger:hover {
            background: var(--primary-l);
            border-color: var(--primary-m);
            color: var(--primary-d);
        }

        .topbar-welcome { font-size: 14px; color: var(--muted); font-weight: 500; }
        .topbar-welcome strong { color: var(--text); font-weight: 700; }

        .topbar-right { display: flex; align-items: center; gap: 10px; }

        /* ── Bell ── */
        .topbar-bell {
            position: relative;
            width: 40px; height: 40px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            background: white;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: var(--muted);
            transition: all .2s;
        }

        .topbar-bell:hover {
            background: var(--primary-l);
            border-color: var(--primary-m);
            color: var(--primary-d);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(14,165,233,.18);
        }

        .topbar-bell:hover svg {
    animation: bellRing .45s ease;
    display: block;
}

        @keyframes bellRing {
            0%   { transform: rotate(0deg); }
            25%  { transform: rotate(18deg); }
            50%  { transform: rotate(-14deg); }
            75%  { transform: rotate(10deg); }
            100% { transform: rotate(0deg); }
        }

        .bell-dot {
            position: absolute;
            top: 8px; right: 8px;
            width: 7px; height: 7px;
            background: var(--accent);
            border-radius: 50%;
            border: 2px solid white;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: .7; }
        }

        /* ── Avatar wrap ── */
        .topbar-avatar-wrap {
            position: relative;
        }

        /* ── Avatar button ── */
        .topbar-avatar {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-d) 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            font-weight: 700;
            color: white;
            cursor: pointer;
            border: 2px solid var(--primary-m);
            transition: all .2s;
            user-select: none;
            box-shadow: 0 2px 8px rgba(14,165,233,.25);
            overflow: hidden;
            padding: 0;
            position: relative;
        }

        /* Glowing ring on hover */
        .topbar-avatar::after {
            content: '';
            position: absolute;
            inset: -5px;
            border-radius: 50%;
            border: 2.5px solid var(--primary);
            opacity: 0;
            transition: opacity .25s, transform .25s;
            transform: scale(.8);
            pointer-events: none;
        }

        .topbar-avatar:hover::after {
            opacity: 1;
            transform: scale(1);
        }

        .topbar-avatar:hover {
            transform: scale(1.08);
            box-shadow: 0 4px 16px rgba(14,165,233,.4);
            border-color: var(--primary);
        }

        /* ── Tooltip ── */
        .avatar-tooltip {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            background: var(--text);
            color: white;
            padding: 8px 13px;
            border-radius: 9px;
            font-size: 12px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transform: translateY(-6px);
            transition: opacity .2s, transform .2s;
            z-index: 300;
            box-shadow: 0 6px 18px rgba(0,0,0,.22);
        }

        /* Arrow pointing up */
        .avatar-tooltip::before {
            content: '';
            position: absolute;
            bottom: 100%;
            right: 14px;
            border: 6px solid transparent;
            border-bottom-color: var(--text);
        }

        .avatar-tooltip-name {
            font-weight: 700;
            font-size: 12.5px;
        }

        .avatar-tooltip-role {
            font-size: 11px;
            opacity: .65;
            margin-top: 2px;
            text-transform: capitalize;
        }

        .topbar-avatar-wrap:hover .avatar-tooltip {
            opacity: 1;
            transform: translateY(0);
        }

        /* Hide tooltip when dropdown open */
        .topbar-avatar-wrap:has(#avatarDropdown.open) .avatar-tooltip {
            display: none;
        }

        /* ── Dropdown ── */
        .topbar-dropdown {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            width: 240px;
            background: white;
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(14,165,233,.14), 0 2px 10px rgba(0,0,0,.07);
            display: none;
            z-index: 200;
            overflow: hidden;
            animation: dropIn .18s ease;
        }

        @keyframes dropIn {
            from { opacity: 0; transform: translateY(-8px) scale(.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .topbar-dropdown.open { display: block; }

        /* Dropdown header with avatar */
        .dropdown-header {
            padding: 16px;
            background: linear-gradient(135deg, var(--primary-l) 0%, #E0F2FE 100%);
            border-bottom: 1px solid var(--primary-m);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .dropdown-avatar {
            width: 44px; height: 44px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            border: 2.5px solid var(--primary-m);
            background: var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-size: 17px; font-weight: 700; color: white;
        }

        .dropdown-avatar img {
            width: 100%; height: 100%;
            object-fit: cover;
        }

        .dropdown-header-info { min-width: 0; }
        .dropdown-header-name { font-size: 14px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dropdown-header-email { font-size: 11px; color: var(--muted); margin-top: 1px; word-break: break-all; }

        .dropdown-header-role {
            display: inline-block;
            margin-top: 5px;
            padding: 2px 10px;
            background: var(--primary-d);
            color: white;
            border-radius: 20px;
            font-size: 10.5px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            font-size: 13px;
            color: var(--text);
            text-decoration: none;
            transition: all .12s;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            font-family: inherit;
            font-weight: 500;
            text-align: left;
        }

        .dropdown-item svg { width: 15px; height: 15px; color: var(--muted); transition: color .12s; flex-shrink: 0; }

        .dropdown-item:hover {
            background: var(--primary-l);
            color: var(--primary-d);
            padding-left: 20px;
        }

        .dropdown-item:hover svg { color: var(--primary); }
        .dropdown-item.danger { color: var(--danger); }
        .dropdown-item.danger svg { color: var(--danger); }
        .dropdown-item.danger:hover { background: #FEF2F2; color: #B91C1C; padding-left: 20px; }

        .dropdown-divider { height: 1px; background: var(--border); margin: 4px 0; }

        /* ══════════════════════
           PAGE CONTENT
        ══════════════════════ */
        .page-content { flex: 1; padding: 24px; }

        .card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
        }

        /* ══════════════════════
           SETTINGS MODAL
        ══════════════════════ */
        .settings-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 9998;
            display: none;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
        }

        .settings-overlay.open { display: flex; }

        .settings-modal {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,.2);
            overflow: hidden;
            animation: modalIn .2s ease;
        }

        @keyframes modalIn {
            from { transform: translateY(-16px) scale(.97); opacity: 0; }
            to   { transform: translateY(0) scale(1); opacity: 1; }
        }

        .settings-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            background: #FAFBFD;
        }

        .settings-modal-header h3 {
            font-size: 15px;
            font-weight: 800;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .settings-close-btn {
            width: 30px; height: 30px;
            display: flex; align-items: center; justify-content: center;
            background: none; border: 1.5px solid var(--border);
            border-radius: 7px; cursor: pointer; color: var(--muted);
            transition: all .15s; font-size: 16px; line-height: 1;
        }

        .settings-close-btn:hover { background: var(--light); color: var(--text); }

        .settings-modal-body { padding: 22px; }

        .settings-section-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 14px;
        }

        .theme-options { display: flex; gap: 12px; }

        .theme-btn {
            flex: 1;
            padding: 16px 12px;
            border: 2.5px solid var(--border);
            border-radius: 12px;
            background: white;
            cursor: pointer;
            font-family: inherit;
            transition: all .2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            position: relative;
        }

        .theme-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.1); }

        .theme-btn[data-theme="blue"].active  { border-color: #0284C7; background: #EFF9FF; }
        .theme-btn[data-theme="orange"].active { border-color: #EA580C; background: #FFF7ED; }

        .theme-preview {
            width: 52px; height: 52px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }

        .theme-preview.blue   { background: linear-gradient(135deg, #0EA5E9, #0284C7); }
        .theme-preview.orange { background: linear-gradient(135deg, #F97316, #EA580C); }

        .theme-check {
            position: absolute;
            top: 8px; right: 8px;
            width: 20px; height: 20px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700;
            color: white;
            opacity: 0;
            transition: opacity .2s;
        }

        .theme-btn[data-theme="blue"]   .theme-check { background: #0284C7; }
        .theme-btn[data-theme="orange"] .theme-check { background: #EA580C; }
        .theme-btn.active .theme-check { opacity: 1; }

        .theme-name { font-size: 13px; font-weight: 700; color: var(--text); }
        .theme-desc { font-size: 11px; color: var(--muted); font-weight: 400; margin-top: -4px; }

        @yield('styles')
    </style>
</head>
<body>

{{-- Apply theme before paint to avoid flash --}}
<script>
    (function () {
        const t = localStorage.getItem('pvmarket_theme') || 'blue';
        if (t === 'orange') document.documentElement.setAttribute('data-theme', 'orange');
    })();
</script>

<div class="admin-layout">

    @include('components.admin.sidebar')

    <div class="main-wrap">

        <header class="topbar">
            <div class="topbar-left">
                <button class="topbar-hamburger">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                <span class="topbar-welcome">Welcome <strong>{{ Auth::user()->name }}</strong></span>
            </div>

            <div class="topbar-right">

                {{-- Bell --}}
                <button class="topbar-bell" type="button" title="Notifications">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span class="bell-dot"></span>
                </button>

                {{-- Avatar + Tooltip + Dropdown --}}
                <div class="topbar-avatar-wrap">

                    {{-- Avatar circle --}}
                    <div class="topbar-avatar" id="avatarToggle">
                        @if(Auth::user()->avatar)
                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}"
                                 alt="{{ Auth::user()->name }}"
                                 style="width:100%; height:100%; object-fit:cover; border-radius:50%;"/>
                        @else
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        @endif
                    </div>

                    {{-- Hover tooltip --}}
                    <div class="avatar-tooltip">
                        <div class="avatar-tooltip-name">{{ Auth::user()->name }}</div>
                        <div class="avatar-tooltip-role">{{ ucfirst(str_replace('_', ' ', Auth::user()->role ?? 'Admin')) }}</div>
                    </div>

                    {{-- Dropdown menu --}}
                    <div class="topbar-dropdown" id="avatarDropdown">

                        {{-- Header with avatar --}}
                        <div class="dropdown-header">
                            <div class="dropdown-avatar">
                                @if(Auth::user()->avatar)
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}"
                                         alt="{{ Auth::user()->name }}"/>
                                @else
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                @endif
                            </div>
                            <div class="dropdown-header-info">
                                <div class="dropdown-header-name">{{ Auth::user()->name }}</div>
                                <div class="dropdown-header-email">{{ Auth::user()->email }}</div>
                                <span class="dropdown-header-role">{{ Auth::user()->role ?? 'Admin' }}</span>
                            </div>
                        </div>

                        <a href="{{ route('admin.profile') }}" class="dropdown-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            My Profile
                        </a>

                        <button type="button" class="dropdown-item"
                                onclick="openSettingsModal(); document.getElementById('avatarDropdown').classList.remove('open');">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                            </svg>
                            Settings
                        </button>

                        <div class="dropdown-divider"></div>

                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item danger">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </header>

        <div class="page-content">
            @yield('content')
        </div>

    </div>
</div>

{{-- SETTINGS MODAL --}}
<div class="settings-overlay" id="settingsOverlay" onclick="closeOnBackdrop(event)">
    <div class="settings-modal">

        <div class="settings-modal-header">
            <h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                Settings
            </h3>
            <button class="settings-close-btn" onclick="closeSettingsModal()">✕</button>
        </div>

        <div class="settings-modal-body">
            <div class="settings-section-title">Theme Color</div>
            <div class="theme-options">

                <button class="theme-btn" data-theme="blue" onclick="setTheme('blue')">
                    <span class="theme-check">✓</span>
                    <div class="theme-preview blue">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="white" stroke="none">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                        </svg>
                    </div>
                    <span class="theme-name">Ocean Blue</span>
                    <span class="theme-desc">Default theme</span>
                </button>

                <button class="theme-btn" data-theme="orange" onclick="setTheme('orange')">
                    <span class="theme-check">✓</span>
                    <div class="theme-preview orange">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="white" stroke="none">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                        </svg>
                    </div>
                    <span class="theme-name">Sunset Orange</span>
                    <span class="theme-desc">Warm & energetic</span>
                </button>

            </div>
        </div>
    </div>
</div>

<script>
    // ── Sidebar submenus ──
    document.querySelectorAll('.nav-item.has-children').forEach(item => {
        item.addEventListener('click', () => {
            item.classList.toggle('open');
            const sub = item.nextElementSibling;
            if (sub && sub.classList.contains('nav-sub')) sub.classList.toggle('open');
        });
    });

    // ── Avatar dropdown ──
    const avatarToggle   = document.getElementById('avatarToggle');
    const avatarDropdown = document.getElementById('avatarDropdown');

    avatarToggle.addEventListener('click', e => {
        e.stopPropagation();
        avatarDropdown.classList.toggle('open');
    });

    document.addEventListener('click', () => avatarDropdown.classList.remove('open'));

    // ── Sidebar toggle ──
document.querySelector('.topbar-hamburger').addEventListener('click', () => {
    document.querySelector('.sidebar').classList.toggle('sidebar-hidden');
    document.querySelector('.main-wrap').classList.toggle('main-expanded');
});

    // ── Theme functions ──
    function setTheme(theme) {
        if (theme === 'orange') {
            document.documentElement.setAttribute('data-theme', 'orange');
        } else {
            document.documentElement.removeAttribute('data-theme');
        }
        localStorage.setItem('pvmarket_theme', theme);
        document.querySelectorAll('.theme-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.theme === theme);
        });
    }

    // ── Settings modal ──
    function openSettingsModal() {
        document.getElementById('settingsOverlay').classList.add('open');
        const current = localStorage.getItem('pvmarket_theme') || 'blue';
        document.querySelectorAll('.theme-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.theme === current);
        });
    }

    function closeSettingsModal() {
        document.getElementById('settingsOverlay').classList.remove('open');
    }

    function closeOnBackdrop(e) {
        if (e.target === document.getElementById('settingsOverlay')) closeSettingsModal();
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeSettingsModal();
    });
</script>

@yield('scripts')

</body>
</html>