<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app-with-sidebar')]
class RoleManagement extends Component
{
    use WithPagination;
    /**
     * Toggle a permission for a user's role.
     * Note: This creates a custom role for the user if they don't have one.
     */
    public function togglePermission(int $userId, string $permissionKey): void
    {
        $user = User::with('userRole')->find($userId);
        
        if (!$user || $user->isAdmin()) {
            return;
        }

        $role = $user->userRole;
        
        if (!$role) {
            // Create a custom role for this user
            $role = Role::create([
                'name' => 'custom_user_' . $userId,
                'display_name' => 'Custom (' . $user->name . ')',
                'permissions' => [],
                'is_system' => false,
            ]);
            $user->update(['role_id' => $role->id]);
        } elseif ($role->is_system) {
            // Clone the system role for this user
            $newRole = Role::create([
                'name' => 'custom_user_' . $userId,
                'display_name' => 'Custom (' . $user->name . ')',
                'permissions' => $role->permissions ?? [],
                'is_system' => false,
            ]);
            $user->update(['role_id' => $newRole->id]);
            $role = $newRole;
        }

        // Toggle the permission
        $permissions = $role->permissions ?? [];
        
        if (in_array($permissionKey, $permissions)) {
            $permissions = array_values(array_diff($permissions, [$permissionKey]));
        } else {
            $permissions[] = $permissionKey;
        }

        $role->update(['permissions' => $permissions]);
        
        session()->flash('message', 'Permission updated.');
    }

    public function render()
    {
        $users = User::with('userRole')
            ->orderBy('name')
            ->paginate(10);

        // Get menu items but exclude admin-only pages (users, roles, settings)
        $adminOnlyKeys = ['users', 'roles', 'settings'];
        $menuItems = collect(config('menu.items', []))
            ->filter(fn($item, $key) => !in_array($key, $adminOnlyKeys))
            ->toArray();

        return view('livewire.role-management', [
            'users' => $users,
            'menuItems' => $menuItems,
        ]);
    }
}
