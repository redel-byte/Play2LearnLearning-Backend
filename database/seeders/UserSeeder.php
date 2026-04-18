<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'email' => 'admin@play2learn.com',
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'password' => Hash::make('password'),
                'is_active' => true,
                'roles' => ['admin'],
            ],
            [
                'email' => 'teacher1@play2learn.com',
                'first_name' => 'John',
                'last_name' => 'Teacher',
                'password' => Hash::make('password'),
                'is_active' => true,
                'roles' => ['teacher'],
            ],
            [
                'email' => 'teacher2@play2learn.com',
                'first_name' => 'Jane',
                'last_name' => 'Instructor',
                'password' => Hash::make('password'),
                'is_active' => true,
                'roles' => ['teacher'],
            ],
            [
                'email' => 'learner1@play2learn.com',
                'first_name' => 'Alice',
                'last_name' => 'Student',
                'password' => Hash::make('password'),
                'is_active' => true,
                'roles' => ['learner'],
            ],
            [
                'email' => 'learner2@play2learn.com',
                'first_name' => 'Bob',
                'last_name' => 'Learner',
                'password' => Hash::make('password'),
                'is_active' => true,
                'roles' => ['learner'],
            ],
        ];

        foreach ($users as $payload) {
            $roles = $payload['roles'];
            unset($payload['roles']);

            $user = User::updateOrCreate(['email' => $payload['email']], $payload);
            $user->syncRoles($roles);
        }
    }
}
