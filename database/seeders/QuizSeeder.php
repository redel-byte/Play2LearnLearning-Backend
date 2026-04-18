<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Choice;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Seeder;

final class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::where('email', 'teacher1@play2learn.com')->first();
        if (!$teacher) {
            return;
        }

        $quizzes = [
            [
                'title' => 'Basic Mathematics Quiz',
                'description' => 'Practice arithmetic and basic reasoning.',
                'is_public' => true,
                'time_limit_minutes' => 30,
                'max_attempts' => 3,
                'pass_percentage' => 70,
                'questions' => [
                    [
                        'type' => 'single_choice',
                        'prompt' => 'What is 15 + 27?',
                        'points' => 5,
                        'choices' => [
                            ['label' => '40', 'is_correct' => false],
                            ['label' => '42', 'is_correct' => true],
                            ['label' => '44', 'is_correct' => false],
                            ['label' => '46', 'is_correct' => false],
                        ],
                    ],
                    [
                        'type' => 'multiple_choice',
                        'prompt' => 'Which of the following are prime numbers?',
                        'points' => 10,
                        'choices' => [
                            ['label' => '2', 'is_correct' => true],
                            ['label' => '4', 'is_correct' => false],
                            ['label' => '7', 'is_correct' => true],
                            ['label' => '9', 'is_correct' => false],
                        ],
                    ],
                    [
                        'type' => 'short_answer',
                        'prompt' => 'Explain what makes a number prime.',
                        'points' => 10,
                        'choices' => [],
                    ],
                ],
            ],
            [
                'title' => 'Science Fundamentals',
                'description' => 'Introductory science quiz.',
                'is_public' => true,
                'time_limit_minutes' => 20,
                'max_attempts' => 2,
                'pass_percentage' => 75,
                'questions' => [
                    [
                        'type' => 'single_choice',
                        'prompt' => 'What is the chemical symbol for water?',
                        'points' => 5,
                        'choices' => [
                            ['label' => 'H2O', 'is_correct' => true],
                            ['label' => 'CO2', 'is_correct' => false],
                            ['label' => 'NaCl', 'is_correct' => false],
                        ],
                    ],
                    [
                        'type' => 'true_false',
                        'prompt' => 'Light travels faster than sound.',
                        'points' => 5,
                        'choices' => [
                            ['label' => 'True', 'is_correct' => true],
                            ['label' => 'False', 'is_correct' => false],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($quizzes as $quizIndex => $payload) {
            $questions = $payload['questions'];
            unset($payload['questions']);

            $quiz = Quiz::updateOrCreate(
                ['title' => $payload['title'], 'creator_id' => $teacher->id],
                array_merge($payload, [
                    'creator_id' => $teacher->id,
                    'published_at' => $payload['is_public'] ? now() : null,
                ])
            );

            foreach ($questions as $questionIndex => $questionData) {
                $choices = $questionData['choices'];
                unset($questionData['choices']);

                $question = Question::updateOrCreate(
                    ['quiz_id' => $quiz->id, 'position' => $questionIndex + 1],
                    array_merge($questionData, [
                        'quiz_id' => $quiz->id,
                        'position' => $questionIndex + 1,
                    ])
                );

                foreach ($choices as $choiceIndex => $choiceData) {
                    Choice::updateOrCreate(
                        ['question_id' => $question->id, 'position' => $choiceIndex + 1],
                        array_merge($choiceData, [
                            'question_id' => $question->id,
                            'position' => $choiceIndex + 1,
                        ])
                    );
                }
            }
        }
    }
}
