<?php

/**
 * Menu Configuration
 * 
 * Defines all available menu items for the POS system sidebar.
 * Each item has a key, label, icon (Heroicon name), route, and optional admin_only flag.
 * 
 * Menu permissions are stored as JSON array of keys on the user record.
 * Admins have access to all items regardless of permissions.
 */

return [
    'groups' => [
        'main' => [
            'label' => null, // No label for main group
            'items' => ['dashboard', 'pos'],
        ],
        'inventory' => [
            'label' => 'Inventory',
            'items' => ['products', 'inventory'],
        ],
        'sales' => [
            'label' => 'Sales',
            'items' => ['transactions', 'reports'],
        ],
        'admin' => [
            'label' => 'Administration',
            'items' => ['users', 'roles', 'settings'],
        ],
    ],

    'items' => [
        'dashboard' => [
            'label' => 'Dashboard',
            'icon' => 'home',
            'route' => 'dashboard',
            'admin_only' => false,
        ],
        'pos' => [
            'label' => 'POS Terminal',
            'icon' => 'shopping-cart',
            'route' => 'pos',
            'admin_only' => false,
        ],
        'products' => [
            'label' => 'Products',
            'icon' => 'cube',
            'route' => 'products.index',
            'admin_only' => false,
        ],
        'inventory' => [
            'label' => 'Inventory',
            'icon' => 'clipboard-document-list',
            'route' => 'inventory.index',
            'admin_only' => false,
        ],
        'transactions' => [
            'label' => 'Transactions',
            'icon' => 'receipt-percent',
            'route' => 'transactions.index',
            'admin_only' => false,
        ],
        'reports' => [
            'label' => 'Reports',
            'icon' => 'chart-bar',
            'route' => 'reports.index',
            'admin_only' => false,
        ],
        'users' => [
            'label' => 'Users',
            'icon' => 'users',
            'route' => 'users.index',
            'admin_only' => true,
        ],
        'roles' => [
            'label' => 'Permissions',
            'icon' => 'shield-check',
            'route' => 'roles.index',
            'admin_only' => true,
        ],
        'settings' => [
            'label' => 'Settings',
            'icon' => 'cog-6-tooth',
            'route' => 'settings.index',
            'admin_only' => true,
        ],
    ],

    /**
     * Default permissions for new users (non-admin).
     * These are the menu items a new user will have access to by default.
     */
    'default_permissions' => [
        'dashboard',
        'pos',
    ],

    /**
     * All available menu keys for reference.
     * Used when displaying permission checkboxes in admin panel.
     */
    'all_keys' => [
        'dashboard',
        'pos',
        'products',
        'inventory',
        'transactions',
        'reports',
        'users',
        'roles',
        'settings',
    ],
];
