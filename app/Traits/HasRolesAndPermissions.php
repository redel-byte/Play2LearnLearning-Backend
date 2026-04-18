<?php
declare(strict_types=1);

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;

trait HasRolesAndPermissions
{
    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user', 'user_id', 'permission_id')
            ->withPivot('granted_at');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot('assigned_at');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')
            ->join('permission_role', 'permission_role.role_id', '=', 'roles.id')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->select('permissions.*');
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function hasPermission(string $permissionName): bool
    {
        $hasDirectPermission = Schema::hasTable('permission_user')
            && $this->directPermissions()->where('permissions.name', $permissionName)->exists();

        return $hasDirectPermission
            || $this->permissions()->where('permissions.name', $permissionName)->exists();
    }

    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    public function hasAnyPermission(array $permissionNames): bool
    {
        $hasAnyDirectPermission = Schema::hasTable('permission_user')
            && $this->directPermissions()->whereIn('permissions.name', $permissionNames)->exists();

        return $hasAnyDirectPermission
            || $this->permissions()->whereIn('permissions.name', $permissionNames)->exists();
    }

    public function hasAllRoles(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->count() === count($roleNames);
    }

    public function hasAllPermissions(array $permissionNames): bool
    {
        return count(array_intersect($permissionNames, $this->getPermissionNames())) === count($permissionNames);
    }

    public function assignRole(string $roleName): self
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $this->roles()->syncWithoutDetaching([
            $role->id => ['assigned_at' => now()],
        ]);
        
        return $this;
    }

    public function assignRoles(array $roleNames): self
    {
        $roles = Role::whereIn('name', $roleNames)->get();
        $assignedAt = now();
        $roleIds = $roles
            ->pluck('id')
            ->mapWithKeys(fn (int $roleId) => [$roleId => ['assigned_at' => $assignedAt]])
            ->all();
        
        $this->roles()->syncWithoutDetaching($roleIds);
        
        return $this;
    }

    public function removeRole(string $roleName): self
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $this->roles()->detach($role->id);
        
        return $this;
    }

    public function syncRoles(array $roleNames): self
    {
        $roles = Role::whereIn('name', $roleNames)->get();
        $assignedAt = now();
        $roleIds = $roles
            ->pluck('id')
            ->mapWithKeys(fn (int $roleId) => [$roleId => ['assigned_at' => $assignedAt]])
            ->all();
        
        $this->roles()->sync($roleIds);
        
        return $this;
    }

    public function getRoleNames(): array
    {
        return $this->roles()->pluck('name')->toArray();
    }

    public function getPermissionNames(): array
    {
        $directPermissions = Schema::hasTable('permission_user')
            ? $this->directPermissions()->pluck('permissions.name')->toArray()
            : [];

        return array_values(array_unique(array_merge(
            $this->permissions()->pluck('permissions.name')->toArray(),
            $directPermissions
        )));
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isTeacher(): bool
    {
        return $this->hasRole('teacher') || $this->isAdmin();
    }

    public function isLearner(): bool
    {
        return $this->hasRole('learner');
    }

    public function canManageQuizzes(): bool
    {
        return $this->hasPermission('create_quiz') || $this->isAdmin();
    }

    public function canManageUsers(): bool
    {
        return $this->hasPermission('create_user') || $this->isAdmin();
    }

    public function canViewAnalytics(): bool
    {
        return $this->hasPermission('view_analytics') || $this->isAdmin();
    }
}
