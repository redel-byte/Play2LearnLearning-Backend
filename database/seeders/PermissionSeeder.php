<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

final class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'create_quiz', 'description' => 'Create quizzes'],
            ['name' => 'edit_quiz', 'description' => 'Edit quizzes'],
            ['name' => 'delete_quiz', 'description' => 'Delete quizzes'],
            ['name' => 'view_quiz', 'description' => 'View quizzes'],
            ['name' => 'share_quiz', 'description' => 'Share quizzes publicly'],
            ['name' => 'publish_quiz', 'description' => 'Publish or unpublish quizzes'],
            ['name' => 'attempt_quiz', 'description' => 'Start quiz attempts'],
            ['name' => 'submit_attempt', 'description' => 'Submit quiz answers'],
            ['name' => 'view_results', 'description' => 'View quiz results'],
            ['name' => 'view_user', 'description' => 'View users'],
            ['name' => 'edit_user', 'description' => 'Edit users'],
            ['name' => 'manage_roles', 'description' => 'Manage user roles'],
            ['name' => 'view_badges', 'description' => 'View badges'],
            ['name' => 'manage_badges', 'description' => 'Manage badges'],
            ['name' => 'view_analytics', 'description' => 'View analytics'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission,
            );
        }
    }
}
