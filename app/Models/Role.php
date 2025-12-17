<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system' => 'boolean',
        ];
    }

    /**
     * Get users with this role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if role has a specific permission.
     */
    public function hasPermission(string $menuKey): bool
    {
        // Administrator role has all permissions
        if ($this->name === 'administrator') {
            return true;
        }

        return in_array($menuKey, $this->permissions ?? [], true);
    }

    /**
     * Check if this is the administrator role.
     */
    public function isAdministrator(): bool
    {
        return $this->name === 'administrator';
    }
}
