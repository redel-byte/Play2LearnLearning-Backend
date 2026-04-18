<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

final class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $matrix = [
            'admin' => [
                'create_quiz', 'edit_quiz', 'delete_quiz', 'view_quiz', 'share_quiz', 'publish_quiz',
                'attempt_quiz', 'submit_attempt', 'view_results',
                'view_user', 'edit_user', 'manage_roles',
                'view_badges', 'manage_badges', 'view_analytics',
            ],
            'teacher' => [
                'create_quiz', 'edit_quiz', 'delete_quiz', 'view_quiz', 'share_quiz', 'publish_quiz',
                'view_results', 'view_badges', 'view_analytics',
            ],
            'learner' => [
                'view_quiz', 'attempt_quiz', 'submit_attempt', 'view_results', 'view_badges',
            ],
        ];

        foreach ($matrix as $roleName => $permissionNames) {
            $role = Role::where('name', $roleName)->first();
            if (!$role) {
                continue;
            }

            $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id')->all();
            $role->permissions()->sync($permissionIds);
        }
    }
}
