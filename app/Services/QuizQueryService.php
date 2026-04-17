<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class QuizQueryService
{
    private const ALLOWED_SORT_COLUMNS = ['title', 'created_at', 'updated_at', 'is_public', 'is_featured'];

    public function buildIndexQuery(Request $request, User $user): Builder
    {
        $query = Quiz::query();

        if (!$user->isAdmin()) {
            $query->accessibleBy($user);
        }

        if ($request->boolean('public_only')) {
            $query->public();
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        if ($request->filled('search')) {
            $search = (string) $request->input('search');

            $query->where(fn (Builder $builder) => $builder
                ->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"));
        }

        $query->with($this->resolveIndexIncludes($request));
        $this->applySorting($query, $request);

        return $query;
    }

    public function loadForShow(Request $request, Quiz $quiz): Quiz
    {
        $quiz->load(['creator', 'questions.choices']);

        if ($request->boolean('include_attempts')) {
            $quiz->load(['attempts.learner', 'attempts.answers.selectedChoices']);
        }

        if ($request->boolean('include_sessions')) {
            $quiz->load('quizSessions.host');
        }

        return $quiz;
    }

    private function resolveIndexIncludes(Request $request): array
    {
        $relationships = ['creator'];

        if ($request->boolean('include_questions')) {
            $relationships[] = 'questions.choices';
        }

        if ($request->boolean('include_sessions')) {
            $relationships[] = 'quizSessions';
        }

        return $relationships;
    }

    private function applySorting(Builder $query, Request $request): void
    {
        $sortBy = (string) $request->input('sort_by', 'created_at');
        $sortOrder = strtolower((string) $request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (in_array($sortBy, self::ALLOWED_SORT_COLUMNS, true)) {
            $query->orderBy($sortBy, $sortOrder);
        }
    }
}
