<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use App\Traits\HasTranslations;

class Role extends Model
{
    use HasTranslations;
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'roles';

    protected $fillable = [
        'role',
        'slug',
        'guard_name',
        'access_types',
        'can_access_admin',
        'admin_permissions',
    ];

    protected $casts = [
        'access_types' => 'array',
        'can_access_admin' => 'boolean',
        'admin_permissions' => 'array',
    ];

    /**
     * Available admin permissions grouped by section
     */
    public static function getAdminPermissions(): array
    {
        return [
            // Main sections
            'dashboard' => 'Dashboard',

            // Settings submenus
            'settings.categories' => 'Settings → Categories',
            'settings.sub_categories' => 'Settings → Sub Categories',
            'settings.brands' => 'Settings → Brands',
            'settings.units' => 'Settings → Units',
            'settings.locations' => 'Settings → Locations',
            'settings.sliders' => 'Settings → Sliders',
            'settings.advertisements' => 'Settings → Advertisements',
            'settings.charges' => 'Settings → Charges Setup',
            'settings.commissions' => 'Settings → Commissions Setup',
            'settings.sub_admins' => 'Settings → Sub Admins',
            'settings.roles' => 'Settings → User Roles',
            'settings.countries' => 'Settings → Countries',
            'settings.coupons' => 'Settings → Coupons',
            'settings.incoterms' => 'Settings → Incoterms',
            'settings.languages' => 'Settings → Languages',
            'settings.markets' => 'Settings → Markets',

            // Static Pages
            'static_pages' => 'Static Pages',

            // Knowledge Hub submenus
            'knowledge_hub.news' => 'Knowledge Hub → News',
            'knowledge_hub.events' => 'Knowledge Hub → Events',
            'knowledge_hub.blogs' => 'Knowledge Hub → Blogs',
            'knowledge_hub.price_promotions' => 'Knowledge Hub → Price Promotions',
            'knowledge_hub.pv_spot_price' => 'Knowledge Hub → PV Spot Price',

            // Main menus
            'warehouses' => 'Warehouses',
            'users' => 'User Management',
            'schedules' => 'Schedules',
            'sales' => 'Sales',

            // Leads submenus
            'leads.index' => 'Leads → Leads',
            'leads.visits' => 'Leads → LoggedIn User Leads',

            // Other main menus
            'bids' => 'Bid/Fair Requests',
            'rfq_requests' => 'RFQ Requests',
            'listings' => 'Manage Listings',
            'inventory' => 'Inventory',

            // Products submenus
            'products.index' => 'Products → Products',
            'products.specifications' => 'Products → Specifications',
        ];
    }

    /**
     * Get permissions grouped by category for display
     */
    public static function getGroupedAdminPermissions(): array
    {
        return [
            'Main' => [
                'dashboard' => 'Dashboard',
            ],
            'Settings' => [
                'settings.categories' => 'Categories',
                'settings.sub_categories' => 'Sub Categories',
                'settings.brands' => 'Brands',
                'settings.units' => 'Units',
                'settings.locations' => 'Locations',
                'settings.sliders' => 'Sliders',
                'settings.advertisements' => 'Advertisements',
                'settings.charges' => 'Charges Setup',
                'settings.commissions' => 'Commissions Setup',
                'settings.sub_admins' => 'Sub Admins',
                'settings.roles' => 'User Roles',
                'settings.countries' => 'Countries',
                'settings.coupons' => 'Coupons',
                'settings.incoterms' => 'Incoterms',
                'settings.languages' => 'Languages',
                'settings.markets' => 'Markets',
            ],
            'Content' => [
                'static_pages' => 'Static Pages',
                'knowledge_hub.news' => 'News',
                'knowledge_hub.events' => 'Events',
                'knowledge_hub.blogs' => 'Blogs',
                'knowledge_hub.price_promotions' => 'Price Promotions',
                'knowledge_hub.pv_spot_price' => 'PV Spot Price',
            ],
            'Operations' => [
                'warehouses' => 'Warehouses',
                'users' => 'User Management',
                'schedules' => 'Schedules',
                'sales' => 'Sales',
            ],
            'Leads & Requests' => [
                'leads.index' => 'Leads',
                'leads.visits' => 'LoggedIn User Leads',
                'bids' => 'Bid/Fair Requests',
                'rfq_requests' => 'RFQ Requests',
            ],
            'Products & Inventory' => [
                'listings' => 'Manage Listings',
                'inventory' => 'Inventory',
                'products.index' => 'Products',
                'products.specifications' => 'Specifications',
            ],
        ];
    }

    /**
     * Check if role has a specific admin permission
     */
    public function hasAdminPermission(string $permission): bool
    {
        if (!$this->can_access_admin) {
            return false;
        }

        // Super admin has all permissions
        if ($this->slug === 'super-admin') {
            return true;
        }

        $permissions = $this->admin_permissions ?? [];
        return in_array($permission, $permissions);
    }
    public array $translatable = [
        'role',
    ];

}