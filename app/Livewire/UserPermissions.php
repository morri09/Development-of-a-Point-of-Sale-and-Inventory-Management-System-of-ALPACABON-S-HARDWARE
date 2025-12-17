<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class UserPermissions extends Component
{
    public ?int $userId = null;
    public ?User $user = null;
    public array $permissions = [];
    public bool $saved = false;

    public function mount(?int $userId = null): void
    {
        if ($userId) {
            $this->loadUser($userId);
        }
    }

    #[On('open-permissions-modal')]
    public function loadUser(int $userId): void
    {
        $this->userId = $userId;
        $this->user = User::findOrFail($userId);
        $this->permissions = $this->user->menu_permissions ?? [];
        $this->saved = false;
    }

    public function togglePermission(string $key): void
    {
        if (in_array($key, $this->permissions)) {
            $this->permissions = array_values(array_diff($this->permissions, [$key]));
        } else {
            $this->permissions[] = $key;
        }
    }

    public function selectAll(): void
    {
        $this->permissions = config('menu.all_keys', []);
    }

    public function deselectAll(): void
    {
        $this->permissions = [];
    }

    public function save(): void
    {
        if (!$this->user) {
            return;
        }

        $this->user->menu_permissions = $this->permissions;
        $this->user->save();

        $this->saved = true;
        $this->dispatch('permissions-saved');
    }

    public function close(): void
    {
        $this->dispatch('close-permissions-modal');
        $this->reset(['userId', 'user', 'permissions', 'saved']);
    }

    public function render()
    {
        $menuItems = config('menu.items', []);

        return view('livewire.user-permissions', [
            'menuItems' => $menuItems,
        ]);
    }
}
