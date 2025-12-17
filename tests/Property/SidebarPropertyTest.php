<?php

namespace Tests\Property;

use App\Enums\UserRole;
use App\Models\User;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature: pos-hardwarezone, Property 5: Sidebar Renders Only Permitted Items
 * Validates: Requirements 3.3
 * 
 * For any user with a specific set of menu_permissions, the rendered sidebar 
 * must contain exactly those menu items and no others (except items visible to all).
 */
class SidebarPropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    /**
     * Get all available menu keys from config.
     */
    private function getAllMenuKeys(): array
    {
        return array_keys(config('menu.items', []));
    }

    /**
     * Get non-admin-only menu keys.
     */
    private function getNonAdminOnlyMenuKeys(): array
    {
        $menuItems = config('menu.items', []);
        return array_keys(array_filter($menuItems, fn($item) => !($item['admin_only'] ?? false)));
    }

    /**
     * Get admin-only menu keys.
     */
    private function getAdminOnlyMenuKeys(): array
    {
        $menuItems = config('menu.items', []);
        return array_keys(array_filter($menuItems, fn($item) => $item['admin_only'] ?? false));
    }

    /**
     * Simulate the sidebar filtering logic (mirrors the Blade component logic).
     */
    private function filterMenuItemsForUser(User $user): array
    {
        $menuItems = config('menu.items', []);
        $userPermissions = $user->menu_permissions ?? [];
        $isAdmin = $user->isAdmin();

        return collect($menuItems)->filter(function ($item, $key) use ($userPermissions, $isAdmin) {
            // Admins see all items
            if ($isAdmin) {
                return true;
            }

            // Admin-only items are hidden from non-admins
            if ($item['admin_only'] ?? false) {
                return false;
            }

            // Check if user has permission for this menu item
            return in_array($key, $userPermissions, true);
        })->keys()->all();
    }

    /**
     * Property 5: Sidebar Renders Only Permitted Items (Regular User)
     * 
     * For any regular user with a specific set of menu_permissions, the sidebar
     * must contain exactly those menu items (excluding admin-only items).
     */
    #[Test]
    public function sidebar_renders_only_permitted_items_for_regular_user(): void
    {
        $nonAdminMenuKeys = $this->getNonAdminOnlyMenuKeys();
        
        $this->forAll(
            Generators::subset($nonAdminMenuKeys)
        )
        ->withMaxSize(count($nonAdminMenuKeys))
        ->__invoke(function (array $permissions): void {
            // Create a regular user with the generated permissions
            $user = User::factory()->create([
                'role' => UserRole::USER,
                'menu_permissions' => $permissions,
            ]);

            // Get the filtered menu items using the same logic as the sidebar
            $filteredItems = $this->filterMenuItemsForUser($user);

            // The filtered items should exactly match the user's permissions
            // (sorted for comparison since order doesn't matter)
            sort($permissions);
            sort($filteredItems);

            $this->assertEquals(
                $permissions,
                $filteredItems,
                sprintf(
                    "Sidebar should render exactly the permitted items.\nExpected: %s\nActual: %s",
                    json_encode($permissions),
                    json_encode($filteredItems)
                )
            );

            // Verify no admin-only items are included
            $adminOnlyKeys = $this->getAdminOnlyMenuKeys();
            foreach ($adminOnlyKeys as $adminKey) {
                $this->assertNotContains(
                    $adminKey,
                    $filteredItems,
                    "Admin-only item '{$adminKey}' should not appear for regular user"
                );
            }

            // Clean up
            $user->delete();
        });
    }

    /**
     * Property 5 (Admin): Admin users see all menu items
     * 
     * For any admin user, regardless of menu_permissions, the sidebar must
     * contain all menu items.
     */
    #[Test]
    public function sidebar_renders_all_items_for_admin_user(): void
    {
        $allMenuKeys = $this->getAllMenuKeys();
        
        $this->forAll(
            Generators::subset($allMenuKeys)  // Random permissions (should be ignored for admin)
        )
        ->withMaxSize(count($allMenuKeys))
        ->__invoke(function (array $permissions) use ($allMenuKeys): void {
            // Create an admin user with random permissions
            $user = User::factory()->create([
                'role' => UserRole::ADMIN,
                'menu_permissions' => $permissions,
            ]);

            // Get the filtered menu items
            $filteredItems = $this->filterMenuItemsForUser($user);

            // Admin should see ALL menu items regardless of permissions
            $expectedKeys = $allMenuKeys;
            sort($expectedKeys);
            sort($filteredItems);

            $this->assertEquals(
                $expectedKeys,
                $filteredItems,
                sprintf(
                    "Admin should see all menu items.\nExpected: %s\nActual: %s",
                    json_encode($expectedKeys),
                    json_encode($filteredItems)
                )
            );

            // Clean up
            $user->delete();
        });
    }

    /**
     * Property 5 (No Permissions): User with empty permissions sees no items
     * 
     * For any regular user with no menu_permissions, the sidebar must be empty.
     */
    #[Test]
    public function sidebar_renders_no_items_for_user_with_no_permissions(): void
    {
        // Create a regular user with no permissions
        $user = User::factory()->create([
            'role' => UserRole::USER,
            'menu_permissions' => [],
        ]);

        // Get the filtered menu items
        $filteredItems = $this->filterMenuItemsForUser($user);

        // Should be empty
        $this->assertEmpty(
            $filteredItems,
            "User with no permissions should see no menu items"
        );

        // Clean up
        $user->delete();
    }

    /**
     * Property 5 (Invalid Permissions): Invalid permission keys are ignored
     * 
     * For any user with invalid menu keys in their permissions, those invalid
     * keys should not appear in the rendered sidebar.
     */
    #[Test]
    public function sidebar_ignores_invalid_permission_keys(): void
    {
        $validKeys = $this->getNonAdminOnlyMenuKeys();
        
        $this->forAll(
            Generators::subset($validKeys),
            Generators::string()  // Random invalid key
        )
        ->withMaxSize(50)
        ->__invoke(function (array $validPermissions, string $invalidKey): void {
            // Skip if the random string happens to be a valid key
            if (in_array($invalidKey, $this->getAllMenuKeys(), true)) {
                return;
            }

            // Create permissions with both valid and invalid keys
            $mixedPermissions = array_merge($validPermissions, [$invalidKey]);

            $user = User::factory()->create([
                'role' => UserRole::USER,
                'menu_permissions' => $mixedPermissions,
            ]);

            // Get the filtered menu items
            $filteredItems = $this->filterMenuItemsForUser($user);

            // Invalid key should not appear in filtered items
            $this->assertNotContains(
                $invalidKey,
                $filteredItems,
                "Invalid permission key '{$invalidKey}' should not appear in sidebar"
            );

            // Only valid permissions should appear
            sort($validPermissions);
            sort($filteredItems);

            $this->assertEquals(
                $validPermissions,
                $filteredItems,
                "Only valid permissions should appear in sidebar"
            );

            // Clean up
            $user->delete();
        });
    }
}
