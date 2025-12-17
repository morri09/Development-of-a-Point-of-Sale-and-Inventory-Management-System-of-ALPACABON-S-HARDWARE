<?php

namespace Tests\Property;

use App\Enums\UserRole;
use App\Models\User;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature: pos-hardwarezone, Property 4: Authenticated User Session Contains Permissions
 * Validates: Requirements 2.4
 * 
 * For any authenticated user, their session/auth context must contain their role 
 * and menu_permissions, loaded from the database without additional queries 
 * after initial authentication.
 */
class AuthenticatedUserSessionPropertyTest extends TestCase
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
     * Property 4: Authenticated User Session Contains Permissions
     * 
     * For any user with any role and any set of menu permissions, when they
     * authenticate, the authenticated user object must contain their role
     * and menu_permissions attributes.
     */
    #[Test]
    public function authenticated_user_session_contains_role_and_permissions(): void
    {
        $allMenuKeys = $this->getAllMenuKeys();
        
        $this->forAll(
            Generators::elements(UserRole::ADMIN, UserRole::USER),
            Generators::subset($allMenuKeys)
        )
        ->withMaxSize(count($allMenuKeys))
        ->__invoke(function (UserRole $role, array $permissions): void {
            // Create a user with specific role and permissions
            $user = User::factory()->create([
                'role' => $role,
                'menu_permissions' => $permissions,
            ]);

            // Authenticate the user
            Auth::login($user);

            // Get the authenticated user from the auth context
            $authenticatedUser = Auth::user();

            // Assert the authenticated user is not null
            $this->assertNotNull(
                $authenticatedUser,
                "Authenticated user should not be null after login"
            );

            // Assert the role is present and matches
            $this->assertEquals(
                $role,
                $authenticatedUser->role,
                sprintf(
                    "Authenticated user role mismatch.\nExpected: %s\nActual: %s",
                    $role->value,
                    $authenticatedUser->role?->value ?? 'null'
                )
            );

            // Assert menu_permissions is present and matches
            $authenticatedPermissions = $authenticatedUser->menu_permissions ?? [];
            sort($permissions);
            sort($authenticatedPermissions);

            $this->assertEquals(
                $permissions,
                $authenticatedPermissions,
                sprintf(
                    "Authenticated user permissions mismatch.\nExpected: %s\nActual: %s",
                    json_encode($permissions),
                    json_encode($authenticatedPermissions)
                )
            );

            // Logout and clean up
            Auth::logout();
            $user->delete();
        });
    }

    /**
     * Property 4 (Extended): Auth user can check permissions without additional queries
     * 
     * For any authenticated user, checking menu permissions should use the
     * already-loaded data without requiring additional database queries.
     */
    #[Test]
    public function authenticated_user_can_check_permissions_from_loaded_data(): void
    {
        $allMenuKeys = $this->getAllMenuKeys();
        
        $this->forAll(
            Generators::subset($allMenuKeys)
        )
        ->withMaxSize(count($allMenuKeys))
        ->__invoke(function (array $permissions) use ($allMenuKeys): void {
            // Create a user with specific permissions
            $user = User::factory()->create([
                'role' => UserRole::USER,
                'menu_permissions' => $permissions,
            ]);

            // Authenticate the user
            Auth::login($user);

            // Get the authenticated user
            $authenticatedUser = Auth::user();

            // Check each menu key - the hasMenuPermission method should work
            // using the already-loaded menu_permissions attribute
            foreach ($allMenuKeys as $menuKey) {
                $expectedHasPermission = in_array($menuKey, $permissions, true);
                $actualHasPermission = $authenticatedUser->hasMenuPermission($menuKey);

                $this->assertEquals(
                    $expectedHasPermission,
                    $actualHasPermission,
                    sprintf(
                        "Permission check mismatch for '%s'.\nUser permissions: %s\nExpected: %s\nActual: %s",
                        $menuKey,
                        json_encode($permissions),
                        $expectedHasPermission ? 'true' : 'false',
                        $actualHasPermission ? 'true' : 'false'
                    )
                );
            }

            // Logout and clean up
            Auth::logout();
            $user->delete();
        });
    }

    /**
     * Property 4 (Admin Override): Admin users have all permissions in session
     * 
     * For any authenticated admin user, hasMenuPermission should return true
     * for all menu items regardless of their menu_permissions array.
     */
    #[Test]
    public function admin_user_has_all_permissions_in_session(): void
    {
        $allMenuKeys = $this->getAllMenuKeys();
        
        $this->forAll(
            Generators::subset($allMenuKeys)  // Even with limited permissions array
        )
        ->withMaxSize(count($allMenuKeys))
        ->__invoke(function (array $permissions) use ($allMenuKeys): void {
            // Create an admin user (even with limited permissions in the array)
            $user = User::factory()->create([
                'role' => UserRole::ADMIN,
                'menu_permissions' => $permissions,  // This should be ignored for admins
            ]);

            // Authenticate the user
            Auth::login($user);

            // Get the authenticated user
            $authenticatedUser = Auth::user();

            // Admin should have access to ALL menu items
            foreach ($allMenuKeys as $menuKey) {
                $this->assertTrue(
                    $authenticatedUser->hasMenuPermission($menuKey),
                    sprintf(
                        "Admin should have permission for '%s' regardless of menu_permissions array.\nAdmin's menu_permissions: %s",
                        $menuKey,
                        json_encode($permissions)
                    )
                );
            }

            // Verify isAdmin() returns true
            $this->assertTrue(
                $authenticatedUser->isAdmin(),
                "Admin user's isAdmin() should return true"
            );

            // Logout and clean up
            Auth::logout();
            $user->delete();
        });
    }

    /**
     * Property 4 (Session Persistence): Auth user data persists across requests
     * 
     * For any authenticated user, their role and permissions should be
     * accessible via the auth helper after authentication.
     */
    #[Test]
    public function auth_helper_provides_user_with_permissions(): void
    {
        $allMenuKeys = $this->getAllMenuKeys();
        
        $this->forAll(
            Generators::elements(UserRole::ADMIN, UserRole::USER),
            Generators::subset($allMenuKeys)
        )
        ->withMaxSize(count($allMenuKeys))
        ->__invoke(function (UserRole $role, array $permissions): void {
            // Create a user
            $user = User::factory()->create([
                'role' => $role,
                'menu_permissions' => $permissions,
            ]);

            // Use actingAs to simulate authenticated request
            $this->actingAs($user);

            // Access via auth() helper
            $authUser = auth()->user();

            // Assert role is accessible
            $this->assertEquals(
                $role,
                $authUser->role,
                "Role should be accessible via auth()->user()->role"
            );

            // Assert permissions are accessible
            $authPermissions = $authUser->menu_permissions ?? [];
            sort($permissions);
            sort($authPermissions);

            $this->assertEquals(
                $permissions,
                $authPermissions,
                "Permissions should be accessible via auth()->user()->menu_permissions"
            );

            // Assert getPermittedMenuItems() works
            $permittedItems = $authUser->getPermittedMenuItems();
            sort($permittedItems);

            $this->assertEquals(
                $permissions,
                $permittedItems,
                "getPermittedMenuItems() should return the user's permissions"
            );

            // Clean up
            $user->delete();
        });
    }
}
