<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;

final class QuizService
{
    public function create(array $data, string $creatorId): Quiz
    {
        return DB::transaction(function () use ($data, $creatorId): Quiz {
            $quiz = Quiz::create([
                'creator_id' => $creatorId,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'is_public' => (bool) ($data['is_public'] ?? false),
                'is_featured' => (bool) ($data['is_featured'] ?? false),
                'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
                'max_attempts' => $data['max_attempts'],
                'pass_percentage' => $data['pass_percentage'],
                'published_at' => !empty($data['is_public']) ? now() : null,
            ]);

            $this->syncQuestions($quiz, $data['questions'] ?? []);

            return $quiz;
        });
    }

    public function update(Quiz $quiz, array $data): Quiz
    {
        return DB::transaction(function () use ($quiz, $data): Quiz {
            $quiz->update([
                'title' => $data['title'] ?? $quiz->title,
                'description' => $data['description'] ?? $quiz->description,
                'is_public' => $data['is_public'] ?? $quiz->is_public,
                'is_featured' => $data['is_featured'] ?? $quiz->is_featured,
                'time_limit_minutes' => $data['time_limit_minutes'] ?? $quiz->time_limit_minutes,
                'max_attempts' => $data['max_attempts'] ?? $quiz->max_attempts,
                'pass_percentage' => $data['pass_percentage'] ?? $quiz->pass_percentage,
                'published_at' => array_key_exists('is_public', $data)
                    ? (($data['is_public'] ?? false) ? ($quiz->published_at ?? now()) : null)
                    : $quiz->published_at,
            ]);

            if (array_key_exists('questions', $data)) {
                $this->syncQuestions($quiz, $data['questions']);
            }

            return $quiz;
        });
    }

    public function delete(Quiz $quiz): void
    {
        DB::transaction(fn () => $quiz->delete());
    }

    public function duplicate(Quiz $quiz, string $creatorId): Quiz
    {
        return DB::transaction(function () use ($quiz, $creatorId): Quiz {
            $quiz->loadMissing('questions.choices');

            $newQuiz = Quiz::create([
                'creator_id' => $creatorId,
                'title' => "Copy of {$quiz->title}",
                'description' => $quiz->description,
                'is_public' => false,
                'is_featured' => false,
                'time_limit_minutes' => $quiz->time_limit_minutes,
                'max_attempts' => $quiz->max_attempts,
                'pass_percentage' => $quiz->pass_percentage,
            ]);

            $questions = $quiz->questions
                ->sortBy('position')
                ->values()
                ->map(fn (Question $question): array => [
                    'type' => $question->type,
                    'prompt' => $question->prompt,
                    'explanation' => $question->explanation,
                    'points' => $question->points,
                    'position' => $question->position,
                    'choices' => $question->choices
                        ->sortBy('position')
                        ->values()
                        ->map(fn ($choice): array => [
                            'label' => $choice->label,
                            'is_correct' => $choice->is_correct,
                            'position' => $choice->position,
                        ])
                        ->all(),
                ])
                ->all();

            $this->syncQuestions($newQuiz, $questions);

            return $newQuiz;
        });
    }

    public function share(Quiz $quiz, bool $featured = false): Quiz
    {
        $quiz->update([
            'is_public' => true,
            'is_featured' => $featured,
            'published_at' => $quiz->published_at ?? now(),
        ]);

        return $quiz;
    }

    public function setPublicationStatus(Quiz $quiz, bool $isPublic): Quiz
    {
        $quiz->update([
            'is_public' => $isPublic,
            'published_at' => $isPublic ? ($quiz->published_at ?? now()) : null,
        ]);

        return $quiz;
    }

    private function syncQuestions(Quiz $quiz, array $questionsData): void
    {
        $existingQuestions = $quiz->questions()->get(['id', 'position']);
        $existingQuestionIds = $existingQuestions->pluck('id')->all();
        $submittedQuestionIds = collect($questionsData)->pluck('id')->filter()->all();

        $questionIdsToDelete = array_diff($existingQuestionIds, $submittedQuestionIds);
        if ($questionIdsToDelete !== []) {
            $quiz->questions()->whereIn('id', $questionIdsToDelete)->delete();
        }

        $this->temporarilyMoveQuestionPositions($quiz, $questionsData, $submittedQuestionIds, $existingQuestions->pluck('position')->all());

        foreach ($questionsData as $questionData) {
            $question = isset($questionData['id'])
                ? $quiz->questions()->findOrFail($questionData['id'])
                : $quiz->questions()->make();

            $question->fill([
                'type' => $questionData['type'],
                'prompt' => $questionData['prompt'],
                'explanation' => $questionData['explanation'] ?? null,
                'points' => $questionData['points'],
                'position' => $questionData['position'],
            ]);
            $question->save();

            $this->syncChoices($question, $questionData['choices'] ?? []);
        }
    }

    private function syncChoices(Question $question, array $choicesData): void
    {
        $existingChoices = $question->choices()->get(['id', 'position']);
        $existingChoiceIds = $existingChoices->pluck('id')->all();
        $submittedChoiceIds = collect($choicesData)->pluck('id')->filter()->all();

        $choiceIdsToDelete = array_diff($existingChoiceIds, $submittedChoiceIds);
        if ($choiceIdsToDelete !== []) {
            $question->choices()->whereIn('id', $choiceIdsToDelete)->delete();
        }

        $this->temporarilyMoveChoicePositions($question, $choicesData, $submittedChoiceIds, $existingChoices->pluck('position')->all());

        foreach ($choicesData as $choiceData) {
            $choice = isset($choiceData['id'])
                ? $question->choices()->findOrFail($choiceData['id'])
                : $question->choices()->make();

            $choice->fill([
                'label' => $choiceData['label'],
                'is_correct' => $choiceData['is_correct'],
                'position' => $choiceData['position'],
            ]);
            $choice->save();
        }
    }

    private function temporarilyMoveQuestionPositions(Quiz $quiz, array $questionsData, array $submittedQuestionIds, array $existingPositions): void
    {
        if ($submittedQuestionIds === []) {
            return;
        }

        $maxPosition = max([
            0,
            ...$existingPositions,
            ...collect($questionsData)->pluck('position')->filter()->all(),
        ]);

        foreach (array_values($submittedQuestionIds) as $index => $questionId) {
            $quiz->questions()
                ->whereKey($questionId)
                ->update(['position' => $maxPosition + $index + 1]);
        }
    }

    private function temporarilyMoveChoicePositions(Question $question, array $choicesData, array $submittedChoiceIds, array $existingPositions): void
    {
        if ($submittedChoiceIds === []) {
            return;
        }

        $maxPosition = max([
            0,
            ...$existingPositions,
            ...collect($choicesData)->pluck('position')->filter()->all(),
        ]);

        foreach (array_values($submittedChoiceIds) as $index => $choiceId) {
            $question->choices()
                ->whereKey($choiceId)
                ->update(['position' => $maxPosition + $index + 1]);
        }
    }
}
