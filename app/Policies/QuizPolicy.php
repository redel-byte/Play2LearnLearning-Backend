<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

final class QuizPolicy
{
    public function view(User $user, Quiz $quiz): bool
    {
        return $user->isAdmin()
            || $quiz->is_public
            || $quiz->creator_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->canManageQuizzes();
    }

    public function update(User $user, Quiz $quiz): bool
    {
        return $user->isAdmin() || $quiz->creator_id === $user->id;
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $this->update($user, $quiz);
    }

    public function share(User $user, Quiz $quiz): bool
    {
        return $this->update($user, $quiz);
    }
}
