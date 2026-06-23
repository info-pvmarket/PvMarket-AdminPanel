<aside class="sidebar">

    <a href="{{ route('admin.dashboard') }}" class="sidebar-logo">
        <img
            src="{{ asset('assets/images/logos/Pv Market Logo-01.png') }}"
            alt="Powernsun"
            style="height: 40px; width: auto; object-fit: contain; max-width: 250px; transform: scale(1.4);"
        />
    </a>

    {{-- Navigation --}}
    <nav class="sidebar-nav">

        {{-- Setup --}}
        <div class="nav-item has-children {{ request()->routeIs('admin.setup.*') ? 'active open' : '' }}"
             onclick="toggleNav(this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
            Setting
            <svg class="nav-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="nav-sub {{ request()->routeIs('admin.setup.*') ? 'open' : '' }}">
            <a href="{{ route('admin.setup.main-menus.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.setup.main-menus.*') ? 'active' : '' }}">
               Categories
            </a>
            <a href="{{ route('admin.setup.sub-menus.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.setup.sub-menus.*') ? 'active' : '' }}">
               Sub Categories
            </a>
            <a href="{{ route('admin.setup.brands.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.setup.brands.*') ? 'active' : '' }}">
               Brands
            </a>
            <a href="{{ route('admin.setup.units.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.setup.units.*') ? 'active' : '' }}">
               Units
            </a>
            <a href="{{ route('admin.setup.locations.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.setup.locations.*') ? 'active' : '' }}">
               Locations
            </a>
            <a href="{{ route('admin.setup.sliders.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.setup.sliders.*') ? 'active' : '' }}">
               Sliders
            </a>
            <a href="{{ route('admin.setup.advertisements.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.setup.advertisements.*') ? 'active' : '' }}">
               Advertisements
            </a>
            <a href="{{ route('admin.setup.charges.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.setup.charges.*') ? 'active' : '' }}">
               Charges Setup
            </a>
                <a href="{{ route('admin.setup.commissions.index') }}"
                class="nav-sub-item {{ request()->routeIs('admin.setup.commissions.*') ? 'active' : '' }}">
                Commissions Setup
            </a>
            <a href="{{ route('admin.setup.sub-admins.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.setup.sub-admins.*') ? 'active' : '' }}">
               Sub Admins
            </a>
            <a href="{{ route('admin.setup.roles.index') }}"
   class="nav-sub-item {{ request()->routeIs('admin.setup.roles.*') ? 'active' : '' }}">
   User Roles
</a>
            <a href="{{ route('admin.setup.countries.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.setup.countries.*') ? 'active' : '' }}">
               Countries
            </a>
            <a href="{{ route('admin.setup.coupons.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.setup.coupons.*') ? 'active' : '' }}">
               Coupons Management
            </a>
            <a href="{{ route('admin.setup.incoterms.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.setup.incoterms.*') ? 'active' : '' }}">
               Incoterms
            </a>
            <a href="{{ route('admin.setup.languages.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.setup.languages.*') ? 'active' : '' }}">
               Languages
            </a>
            <a href="{{ route('admin.setup.markets.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.setup.markets.*') ? 'active' : '' }}">
               Markets
            </a>
        </div>

        {{-- ═══ CMS / PAGE SECTIONS ═══ --}}
        <a href="{{ route('admin.page-sections.index') }}"
           class="nav-item {{ request()->routeIs('admin.page-sections.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 20h9"/>
                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
            </svg>
            Static Pages
        </a>

        {{-- Knowledge Hub --}}
        <div class="nav-item has-children {{ request()->routeIs('admin.knowledge-hub.*') ? 'active open' : '' }}"
             onclick="toggleNav(this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
            Knowledge Hub
            <svg class="nav-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="nav-sub {{ request()->routeIs('admin.knowledge-hub.*') ? 'open' : '' }}">
            <a href="{{ route('admin.knowledge-hub.news.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.knowledge-hub.news.*') ? 'active' : '' }}">
               News
            </a>
            <a href="{{ route('admin.knowledge-hub.events.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.knowledge-hub.events.*') ? 'active' : '' }}">
               Events
            </a>
            <a href="{{ route('admin.knowledge-hub.blogs.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.knowledge-hub.blogs.*') ? 'active' : '' }}">
               Blogs
            </a>
            <a href="{{ route('admin.knowledge-hub.price-promotions.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.knowledge-hub.price-promotions.*') ? 'active' : '' }}">
               Price Promotions
            </a>
            <a href="{{ route('admin.knowledge-hub.pv-spot-price.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.knowledge-hub.pv-spot-price.*') ? 'active' : '' }}">
               PV Spot Price
            </a>
        </div>

        {{-- Warehouses --}}
        <a href="{{ route('admin.warehouses.index') }}"
           class="nav-item {{ request()->routeIs('admin.warehouses.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Warehouses
        </a>

        {{-- User Management --}}
        <a href="{{ route('admin.users.index') }}"
           class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            User Management
        </a>

        {{-- Schedules --}}
        <a href="{{ route('admin.schedules.index') }}"
           class="nav-item {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Schedules
        </a>

        {{-- Sales --}}
        <a href="{{ route('admin.sales.index') }}"
           class="nav-item {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            Sales
        </a>

        {{-- Leads --}}
        <div class="nav-item has-children {{ request()->routeIs('admin.leads.*') ? 'active open' : '' }}"
             onclick="toggleNav(this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
            Lead Generation
            <svg class="nav-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="nav-sub {{ request()->routeIs('admin.leads.*') ? 'open' : '' }}">
            <a href="{{ route('admin.leads.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.leads.index') ? 'active' : '' }}">
                Leads
            </a>
            <a href="{{ route('admin.leads.visits.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.leads.visits.*') ? 'active' : '' }}">
                LoggedIn User Leads
            </a>
        </div>

        {{-- Bid/Fair Price Requests --}}
        <a href="{{ route('admin.bids.index') }}"
           class="nav-item {{ request()->routeIs('admin.bids.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
            Bid/Fair Requests
        </a>

        {{-- RFQ Requests --}}
        <a href="{{ route('admin.rfq-requests.index') }}"
           class="nav-item {{ request()->routeIs('admin.rfq-requests.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
            </svg>
            RFQ Requests
        </a>


    <a href="{{ route('product_listing.index') }}"
   class="nav-item {{ request()->routeIs('product_listing.*') ? 'active' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
        <rect x="9" y="3" width="6" height="4" rx="1"/>
        <path d="M9 12h6M9 16h4"/>
    </svg>
    Manage Listings
</a>

{{-- Inventory — paste this after the "Manage Listings" nav-item --}}
<a href="{{ route('admin.inventory.index') }}"
   class="nav-item {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
        <path d="M21 16V8a2 2 0 0 0-1-1.73L13 2.27a2 2 0 0 0-2 0L4 6.27A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
        <line x1="12" y1="22.08" x2="12" y2="12"/>
    </svg>
    Inventory
</a>


        {{-- Products --}}
        <div class="nav-item has-children {{ request()->routeIs('admin.products.*') ? 'active open' : '' }}"
             onclick="toggleNav(this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
            Products
            <svg class="nav-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="nav-sub {{ request()->routeIs('admin.products.*') ? 'open' : '' }}">
            <a href="{{ route('admin.products.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.products.index') || request()->routeIs('admin.products.create') || request()->routeIs('admin.products.edit') || request()->routeIs('admin.products.show') ? 'active' : '' }}">
               Products
            </a>
            <a href="{{ route('admin.products.detail-options.index') }}"
               class="nav-sub-item {{ request()->routeIs('admin.products.detail-options.*') ? 'active' : '' }}">
                Specifications
            </a>
        </div>

    </nav>

    {{-- Logout — fixed at bottom --}}
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="nav-item" style="color: var(--danger);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--danger);">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Logout
            </button>
        </form>
    </div>

</aside>

