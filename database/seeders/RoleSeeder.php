<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

final class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'System administrator with full access to all features',
            ],
            [
                'name' => 'teacher',
                'description' => 'Teacher who can create, manage, and share quizzes',
            ],
            [
                'name' => 'learner',
                'description' => 'Learner who can take quizzes and view their progress',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }

        $this->command->info('Roles seeded successfully.');
    }
}
