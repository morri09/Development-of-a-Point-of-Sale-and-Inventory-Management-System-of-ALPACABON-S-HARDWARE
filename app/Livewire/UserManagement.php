<?php

namespace App\Livewire;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app-with-sidebar')]
class UserManagement extends Component
{
    use WithPagination;

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;
    public bool $showRoleModal = false;
    public bool $showDeleteRoleModal = false;

    public ?int $editingUserId = null;
    public ?int $deletingUserId = null;
    public ?int $deletingRoleId = null;

    // User form fields
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?int $roleId = null;
    
    // Role form fields
    public string $roleName = '';
    public array $rolePermissions = [];

    // Search
    public string $search = '';

    protected $queryString = ['search'];

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'roleId' => 'required|exists:roles,id',
        ];

        if ($this->showCreateModal) {
            $rules['email'] .= '|unique:users,email';
            $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            $rules['email'] .= '|unique:users,email,' . $this->editingUserId;
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        return $rules;
    }

    protected array $messages = [
        'name.required' => 'Name is required.',
        'email.required' => 'Email is required.',
        'email.email' => 'Please enter a valid email address.',
        'email.unique' => 'This email is already in use.',
        'password.required' => 'Password is required.',
        'password.min' => 'Password must be at least 8 characters.',
        'password.confirmed' => 'Passwords do not match.',
        'roleId.required' => 'Role is required.',
        'roleId.exists' => 'Invalid role selected.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal(int $userId): void
    {
        $this->resetForm();
        $user = User::findOrFail($userId);
        
        $this->editingUserId = $userId;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->roleId = $user->role_id;
        $this->showEditModal = true;
    }

    public function openDeleteModal(int $userId): void
    {
        $this->deletingUserId = $userId;
        $this->showDeleteModal = true;
    }

    public function openRoleModal(): void
    {
        $this->roleName = '';
        $this->rolePermissions = [];
        $this->showRoleModal = true;
    }

    public function openDeleteRoleModal(int $roleId): void
    {
        $this->deletingRoleId = $roleId;
        $this->showDeleteRoleModal = true;
    }

    public function closeModals(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showRoleModal = false;
        $this->showDeleteRoleModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->editingUserId = null;
        $this->deletingUserId = null;
        $this->deletingRoleId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->roleId = null;
        $this->roleName = '';
        $this->rolePermissions = [];
        $this->resetValidation();
    }
    
    public function createRole(): void
    {
        $this->validate([
            'roleName' => 'required|string|max:255',
            'rolePermissions' => 'array',
        ]);

        $slug = strtolower(str_replace(' ', '_', $this->roleName));
        
        // Check if role name already exists
        if (Role::where('name', $slug)->exists()) {
            session()->flash('error', 'A role with this name already exists.');
            return;
        }

        Role::create([
            'name' => $slug,
            'display_name' => $this->roleName,
            'permissions' => $this->rolePermissions,
            'is_system' => false,
        ]);

        $this->closeModals();
        session()->flash('message', 'Role created successfully.');
    }

    public function createUser(): void
    {
        $this->validate();

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role_id' => $this->roleId,
        ]);

        $this->closeModals();
        session()->flash('message', 'User created successfully.');
    }

    public function updateUser(): void
    {
        $this->validate();

        $user = User::findOrFail($this->editingUserId);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role_id' => $this->roleId,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        $user->update($data);

        $this->closeModals();
        session()->flash('message', 'User updated successfully.');
    }

    public function deleteUser(): void
    {
        $user = User::findOrFail($this->deletingUserId);

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            $this->closeModals();
            return;
        }

        $user->delete();

        $this->closeModals();
        session()->flash('message', 'User deleted successfully.');
    }

    public function deleteRole(): void
    {
        $role = Role::findOrFail($this->deletingRoleId);

        // Prevent deleting system roles
        if ($role->is_system) {
            session()->flash('error', 'Cannot delete system roles.');
            $this->closeModals();
            return;
        }

        // Check if any users are using this role
        $usersWithRole = User::where('role_id', $role->id)->count();
        if ($usersWithRole > 0) {
            session()->flash('error', 'Cannot delete role. ' . $usersWithRole . ' user(s) are assigned to this role.');
            $this->closeModals();
            return;
        }

        $role->delete();

        $this->closeModals();
        session()->flash('message', 'Role deleted successfully.');
    }

    public function render()
    {
        $users = User::query()
            ->with('userRole')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'usersPage');

        // Filter out custom user-specific roles (those starting with custom_user_)
        $roles = Role::where('name', 'not like', 'custom_user_%')
            ->orderBy('display_name')
            ->get();

        // All manageable roles with pagination (5 per page)
        $manageableRoles = Role::where('name', 'not like', 'custom_user_%')
            ->orderBy('display_name')
            ->paginate(5, ['*'], 'rolesPage');

        return view('livewire.user-management', [
            'users' => $users,
            'roles' => $roles,
            'manageableRoles' => $manageableRoles,
        ]);
    }
}
