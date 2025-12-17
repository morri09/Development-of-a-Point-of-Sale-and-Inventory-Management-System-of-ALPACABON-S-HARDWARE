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
 * Feature: pos-hardwarezone, Property 3: User Permission Persistence
 * Validates: Requirements 2.3, 3.2
 * 
 * For any user whose role or menu permissions are updated by an admin, 
 * the changes must be persisted to the database and reflected in the 
 * user's menu_permissions JSON field.
 */
class UserPermissionPropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    /**
     * Get all available menu keys from config.
     */
    private function getAllMenuKeys(): array
    {
        return config('menu.all_keys', []);
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
     * Property 3: User Permission Persistence
     * 
     * For any user and any set of menu permissions, when the permissions are
     * updated, the changes must be persisted to the database and retrievable.
     */
    #[Test]
    public function user_permission_persistence(): void
    {
        $allMenuKeys = $this->getAllMenuKeys();
        
        $this->forAll(
            Generators::subset($allMenuKeys)
        )
        ->withMaxSize(count($allMenuKeys))
        ->__invoke(function (array $permissions): void {
            // Create a user with initial empty permissions
            $user = User::factory()->create([
                'role' => UserRole::USER,
                'menu_permissions' => [],
            ]);

            // Update the user's permissions using the model method
            $user->setMenuPermissions($permissions);

            // Refresh the user from the database to ensure we're reading persisted data
            $user->refresh();

            // Get the persisted permissions
            $persistedPermissions = $user->menu_permissions ?? [];

            // Sort both arrays for comparison (order doesn't matter)
            sort($permissions);
            sort($persistedPermissions);

            // Assert that the persisted permissions match the set permissions
            $this->assertEquals(
                $permissions,
                $persistedPermissions,
                sprintf(
                    "Permission persistence failed.\nSet: %s\nPersisted: %s",
                    json_encode($permissions),
                    json_encode($persistedPermissions)
                )
            );

            // Clean up
            $user->delete();
        });
    }

    /**
     * Property 3 (Extended): Permission update overwrites previous permissions
     * 
     * For any user with existing permissions, updating to new permissions
     * should completely replace the old permissions.
     */
    #[Test]
    public function permission_update_overwrites_previous(): void
    {
        $allMenuKeys = $this->getAllMenuKeys();
        
        $this->forAll(
            Generators::subset($allMenuKeys),  // Initial permissions
            Generators::subset($allMenuKeys)   // Updated permissions
        )
        ->withMaxSize(count($allMenuKeys))
        ->__invoke(function (array $initialPermissions, array $updatedPermissions): void {
            // Create a user with initial permissions
            $user = User::factory()->create([
                'role' => UserRole::USER,
                'menu_permissions' => $initialPermissions,
            ]);

            // Update to new permissions
            $user->setMenuPermissions($updatedPermissions);

            // Refresh from database
            $user->refresh();

            // Get persisted permissions
            $persistedPermissions = $user->menu_permissions ?? [];

            // Sort for comparison
            sort($updatedPermissions);
            sort($persistedPermissions);

            // Should have the updated permissions, not the initial ones
            $this->assertEquals(
                $updatedPermissions,
                $persistedPermissions,
                sprintf(
                    "Permission update should overwrite previous.\nInitial: %s\nUpdated: %s\nPersisted: %s",
                    json_encode($initialPermissions),
                    json_encode($updatedPermissions),
                    json_encode($persistedPermissions)
                )
            );

            // Clean up
            $user->delete();
        });
    }

    /**
     * Property 3 (Role Update): Role changes are persisted correctly
     * 
     * For any user, when their role is updated, the change must be
     * persisted to the database.
     */
    #[Test]
    public function role_update_persistence(): void
    {
        $this->forAll(
            Generators::elements(UserRole::ADMIN, UserRole::USER),  // Initial role
            Generators::elements(UserRole::ADMIN, UserRole::USER)   // Updated role
        )
        ->__invoke(function (UserRole $initialRole, UserRole $updatedRole): void {
            // Create a user with initial role
            $user = User::factory()->create([
                'role' => $initialRole,
                'menu_permissions' => [],
            ]);

            // Update the role
            $user->role = $updatedRole;
            $user->save();

            // Refresh from database
            $user->refresh();

            // Assert the role was persisted
            $this->assertEquals(
                $updatedRole,
                $user->role,
                sprintf(
                    "Role update persistence failed.\nInitial: %s\nUpdated: %s\nPersisted: %s",
                    $initialRole->value,
                    $updatedRole->value,
                    $user->role->value
                )
            );

            // Clean up
            $user->delete();
        });
    }

    /**
     * Property 3 (Direct Update): Direct model update persists permissions
     * 
     * For any user, updating menu_permissions via direct model update
     * should persist correctly.
     */
    #[Test]
    public function direct_model_update_persists_permissions(): void
    {
        $allMenuKeys = $this->getAllMenuKeys();
        
        $this->forAll(
            Generators::subset($allMenuKeys)
        )
        ->withMaxSize(count($allMenuKeys))
        ->__invoke(function (array $permissions): void {
            // Create a user
            $user = User::factory()->create([
                'role' => UserRole::USER,
                'menu_permissions' => [],
            ]);

            // Update via direct assignment and save (simulating Livewire component behavior)
            $user->menu_permissions = $permissions;
            $user->save();

            // Fetch fresh from database using a new query
            $freshUser = User::find($user->id);

            // Get persisted permissions
            $persistedPermissions = $freshUser->menu_permissions ?? [];

            // Sort for comparison
            sort($permissions);
            sort($persistedPermissions);

            $this->assertEquals(
                $permissions,
                $persistedPermissions,
                sprintf(
                    "Direct model update should persist permissions.\nSet: %s\nPersisted: %s",
                    json_encode($permissions),
                    json_encode($persistedPermissions)
                )
            );

            // Clean up
            $user->delete();
        });
    }

    /**
     * Property 3 (Empty Permissions): Empty permissions array persists correctly
     * 
     * For any user, setting empty permissions should persist as an empty array.
     */
    #[Test]
    public function empty_permissions_persist_correctly(): void
    {
        $allMenuKeys = $this->getAllMenuKeys();
        
        $this->forAll(
            Generators::subset($allMenuKeys)  // Initial non-empty permissions
        )
        ->withMaxSize(count($allMenuKeys))
        ->__invoke(function (array $initialPermissions): void {
            // Skip if initial permissions are already empty
            if (empty($initialPermissions)) {
                return;
            }

            // Create a user with initial permissions
            $user = User::factory()->create([
                'role' => UserRole::USER,
                'menu_permissions' => $initialPermissions,
            ]);

            // Update to empty permissions
            $user->setMenuPermissions([]);

            // Refresh from database
            $user->refresh();

            // Should be empty array
            $this->assertEquals(
                [],
                $user->menu_permissions ?? [],
                "Empty permissions should persist as empty array"
            );

            // Clean up
            $user->delete();
        });
    }
}
