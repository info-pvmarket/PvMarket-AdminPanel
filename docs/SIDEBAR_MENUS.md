# Sidebar Menu Documentation

This document provides a comprehensive overview of all sidebar menu components and configurations in the PvMarket Admin Panel.

## Overview

The admin panel uses a dynamic sidebar navigation system built with Laravel Blade components. The sidebar supports nested menus, active state detection, and is fully manageable through the admin panel.

---

## Main Sidebar Component

**File:** `resources/views/components/admin/sidebar.blade.php`

---

## Menu Structure

### 1. Setting (Expandable)

Contains all system configuration options:

| Menu Item | Route Pattern | Description |
|-----------|---------------|-------------|
| Categories | `admin.setup.main-menus.*` | Main category management |
| Sub Categories | `admin.setup.sub-menus.*` | Sub-category management |
| Brands | `admin.setup.brands.*` | Brand management |
| Units | `admin.setup.units.*` | Unit management |
| Locations | `admin.setup.locations.*` | Location management |
| Sliders | `admin.setup.sliders.*` | Homepage slider management |
| Advertisements | `admin.setup.advertisements.*` | Advertisement management |
| Charges Setup | `admin.setup.charges.*` | Charges configuration |
| Commissions Setup | `admin.setup.commissions.*` | Commission configuration |
| Sub Admins | `admin.setup.sub-admins.*` | Sub-admin user management |
| User Roles | `admin.setup.roles.*` | Role & permission management |
| Countries | `admin.setup.countries.*` | Country management |
| Coupons Management | `admin.setup.coupons.*` | Coupon/discount management |
| Incoterms | `admin.setup.incoterms.*` | International trade terms |
| Languages | `admin.setup.languages.*` | Language/localization settings |
| Markets | `admin.setup.markets.*` | Market configuration |

---

### 2. Static Pages

| Menu Item | Route Pattern | Description |
|-----------|---------------|-------------|
| Static Pages | `admin.page-sections.*` | CMS/Static page management |

---

### 3. Knowledge Hub (Expandable)

Content management for informational resources:

| Menu Item | Route Pattern | Description |
|-----------|---------------|-------------|
| News | `admin.knowledge-hub.news.*` | News article management |
| Events | `admin.knowledge-hub.events.*` | Event management |
| Blogs | `admin.knowledge-hub.blogs.*` | Blog post management |
| Price Promotions | `admin.knowledge-hub.price-promotions.*` | Price promotion content |
| PV Spot Price | `admin.knowledge-hub.pv-spot-price.*` | PV spot price data |

---

### 4. Business Operations

| Menu Item | Route Pattern | Description |
|-----------|---------------|-------------|
| Offers | `admin.offers.*` | Offer management |
| Warehouses | `admin.warehouses.*` | Warehouse management |
| User Management | `admin.users.*` | User account management |
| Schedules | `admin.schedules.*` | Schedule management |
| Sales | `admin.sales.*` | Sales management |

---

### 5. Lead Generation (Expandable)

| Menu Item | Route Pattern | Description |
|-----------|---------------|-------------|
| Leads | `admin.leads.*` | Lead management |
| LoggedIn User Leads | `admin.leads.visits.*` | Logged-in user lead tracking |

---

### 6. Requests & Listings

| Menu Item | Route Pattern | Description |
|-----------|---------------|-------------|
| Bid/Fair Requests | `admin.bids.*` | Bid and fair price requests |
| RFQ Requests | `admin.rfq-requests.*` | Request for quotation management |
| Manage Listings | `product_listing.*` | Product listing management |
| Inventory | `admin.inventory.*` | Inventory management |

---

### 7. Products (Expandable)

| Menu Item | Route Pattern | Description |
|-----------|---------------|-------------|
| Products | `admin.products.*` | Product management |
| Specifications | `admin.products.detail-options.*` | Product specification options |

---

### 8. System

| Menu Item | Route Pattern | Description |
|-----------|---------------|-------------|
| Dashboard | `admin.dashboard` | Main dashboard (root) |
| Logout | `admin.logout` | User logout |

---

## Route Definitions

**File:** `routes/web.php`

All admin routes are prefixed with `admin/` and grouped under appropriate middleware.

### Route Namespaces

```
admin.setup.*           - All setup/configuration routes
admin.knowledge-hub.*   - Knowledge hub content routes
admin.page-sections.*   - Static page routes
admin.offers.*          - Offer routes
admin.warehouses.*      - Warehouse routes
admin.users.*           - User management routes
admin.schedules.*       - Schedule routes
admin.sales.*           - Sales routes
admin.leads.*           - Lead generation routes
admin.bids.*            - Bid request routes
admin.rfq-requests.*    - RFQ request routes
product_listing.*       - Product listing routes
admin.inventory.*       - Inventory routes
admin.products.*        - Product routes
```

---

## Reusable Components

### Product Navigation Item

**File:** `resources/views/components/admin/product-nav-item.blade.php`

A reusable Blade component for consistent navigation items:

```php
@props([
    'label',        // Menu item label
    'routePattern', // Route pattern for active state
    'icon'          // SVG icon HTML
])
```

---

## JavaScript Files

| File | Purpose |
|------|---------|
| `public/assets/js/sidebar.js` | Main sidebar functionality |
| `public/assets/js/sidebar-mini.js` | Mini/collapsed sidebar variant |

### Key Functions

- `toggleNav()` - Expand/collapse menu items with children

---

## Features

- **Active State Detection**: Uses `request()->routeIs()` for highlighting active menu items
- **Nested Navigation**: Collapsible sections for grouped menu items
- **SVG Icons**: Consistent iconography across all menu items
- **Drag & Drop**: Reordering capability for main menus
- **Multi-language Support**: Via `lang()` helper function
- **Stock Value Toggle**: Per menu item stock visibility control
- **Soft Delete**: Active/inactive status management

---

## Database-Driven Navigation

The sidebar navigation is database-driven through:

- **Main Menu Controller**: Manages primary categories
- **Sub Menu Controller**: Manages sub-categories

This makes the navigation fully dynamic and manageable through the admin panel interface.

---

## Related Admin Pages

### Main Menu Management
**File:** `resources/views/admin/setup/main-menu/main-menu.blade.php`

Features:
- Category Name
- Category Icon
- Alt Tag
- Meta Fields (Title, Description, Image)
- Rich text content editor (Quill.js)
- Drag-and-drop reordering

### Sub Menu Management
**File:** `resources/views/admin/setup/sub-menu/sub-menu.blade.php`

Features:
- Sub Category Name
- Parent Category Selection
- Pallet/Container Applicable flags
- Meta Fields
- Rich text content editor

---

## Adding New Menu Items

To add a new menu item to the sidebar:

1. Create the route in `routes/web.php`
2. Add the menu item HTML in `resources/views/components/admin/sidebar.blade.php`
3. Use the appropriate route pattern for active state detection
4. Include an SVG icon for visual consistency

Example:
```html
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.new-feature.*') ? 'active' : '' }}"
       href="{{ route('admin.new-feature.index') }}">
        <i class="nav-icon">
            <!-- SVG icon here -->
        </i>
        <p>{{ lang('New Feature') }}</p>
    </a>
</li>
```
