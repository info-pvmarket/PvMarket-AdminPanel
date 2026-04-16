<header class="topbar">
<div style="background:red;color:white;padding:4px;">OLD TOPBAR</div>
    <div class="topbar-left">
        <div>
            <div class="topbar-title">@yield('title', 'Dashboard')</div>
            <div class="topbar-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Home</a>
                @hasSection('breadcrumb')
                    &nbsp;/&nbsp; @yield('breadcrumb')
                @endif
            </div>
        </div>
    </div>
    <div class="topbar-right">
        <button class="topbar-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
        </button>
        <div class="topbar-avatar">
    @if(Auth::user()->avatar)
        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" 
             alt="{{ Auth::user()->name }}"
             style="width:100%; height:100%; object-fit:cover; border-radius:50%;"/>
    @else
        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
    @endif
</div>
    </div>
</header>

<style>
    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 30px;
        background: var(--white);
        border-bottom: 1px solid var(--border);
    }

    .topbar-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text);
    }

    .topbar-breadcrumb {
        font-size: 14px;
        color: var(--muted);
    }

    .topbar-breadcrumb a {
        color: var(--muted);
        text-decoration: none;
    }

    .topbar-right {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .topbar-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--muted);
        border-radius: 8px;
        padding: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .15s, color .15s, transform .15s;
    }

    .topbar-btn:hover {
        background: #E0F2FE;
        color: var(--primary);
        transform: translateY(-1px);
    }

    .topbar-avatar {
        width: 36px;
        height: 36px;
        background: var(--primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 600;
        margin-left: 6px;
        cursor: pointer;
        transition: box-shadow .15s, transform .15s;
    }

    .topbar-avatar:hover {
        box-shadow: 0 4px 14px rgba(14,165,233,.35);
        transform: translateY(-1px);
    }
</style>