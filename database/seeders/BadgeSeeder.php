<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

final class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'name' => 'First Quiz',
                'description' => 'Complete your first quiz attempt.',
                'icon_path' => 'badges/first-quiz.svg',
                'criteria' => ['completed_attempts' => 1],
            ],
            [
                'name' => 'Perfect Score',
                'description' => 'Score 100% on a quiz.',
                'icon_path' => 'badges/perfect-score.svg',
                'criteria' => ['perfect_score' => true],
            ],
            [
                'name' => 'Consistent Learner',
                'description' => 'Complete 5 quiz attempts.',
                'icon_path' => 'badges/consistent-learner.svg',
                'criteria' => ['completed_attempts' => 5],
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(['name' => $badge['name']], $badge);
        }
    }
}
