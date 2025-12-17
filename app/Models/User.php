<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'menu_permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'menu_permissions' => 'array',
        ];
    }

    /**
     * Get the role that the user belongs to.
     */
    public function userRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        // Check new role system first
        if ($this->userRole && $this->userRole->isAdministrator()) {
            return true;
        }
        // Fallback to old enum system
        return $this->role === UserRole::ADMIN;
    }

    /**
     * Check if the user has permission to access a specific menu item.
     *
     * @param string $menuKey
     * @return bool
     */
    public function hasMenuPermission(string $menuKey): bool
    {
        // Admins have access to all menu items
        if ($this->isAdmin()) {
            return true;
        }

        // Check role-based permissions first
        if ($this->userRole) {
            return $this->userRole->hasPermission($menuKey);
        }

        // Fallback to user-specific permissions
        $permissions = $this->menu_permissions ?? [];
        return in_array($menuKey, $permissions, true);
    }

    /**
     * Get the user's permitted menu items.
     *
     * @return array<string>
     */
    public function getPermittedMenuItems(): array
    {
        // If user has a role, use role permissions
        if ($this->userRole) {
            if ($this->userRole->isAdministrator()) {
                return config('menu.all_keys', []);
            }
            return $this->userRole->permissions ?? [];
        }
        
        return $this->menu_permissions ?? [];
    }

    /**
     * Set the user's menu permissions.
     *
     * @param array<string> $permissions
     * @return void
     */
    public function setMenuPermissions(array $permissions): void
    {
        $this->menu_permissions = $permissions;
        $this->save();
    }

    /**
     * Get the stock adjustments made by this user.
     */
    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }
}
