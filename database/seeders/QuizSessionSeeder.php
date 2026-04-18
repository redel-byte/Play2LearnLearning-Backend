<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuizSession;
use App\Models\User;
use Illuminate\Database\Seeder;

final class QuizSessionSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::where('email', 'teacher1@play2learn.com')->first();
        $quiz = Quiz::where('title', 'Basic Mathematics Quiz')->first();

        if (!$teacher || !$quiz) {
            return;
        }

        $sessions = [
            [
                'title' => 'Math Class Live Session',
                'starts_at' => now()->subMinutes(15),
                'ends_at' => now()->addHour(),
                'status' => 'active',
            ],
            [
                'title' => 'Math Review Session',
                'starts_at' => now()->addDay(),
                'ends_at' => now()->addDay()->addHour(),
                'status' => 'scheduled',
            ],
        ];

        foreach ($sessions as $payload) {
            QuizSession::updateOrCreate(
                ['quiz_id' => $quiz->id, 'title' => $payload['title']],
                array_merge($payload, [
                    'quiz_id' => $quiz->id,
                    'host_id' => $teacher->id,
                ])
            );
        }
    }
}
