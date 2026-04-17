<?php
declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\QuizSession;
use Illuminate\Foundation\Http\FormRequest;

final class AttemptQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'quiz_session_id' => ['sometimes', 'nullable', 'uuid', 'exists:quiz_sessions,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $quiz = $this->route('quiz');
            $user = $this->user();

            if (!$user->isLearner()) {
                $validator->errors()->add('user', 'Only learners can start quiz attempts.');
            }

            if (!$user->is_active) {
                $validator->errors()->add('user', 'Your account is not active.');
            }

            if (!$quiz->isAvailableToUser($user)) {
                $validator->errors()->add('quiz', 'This quiz is not available for you.');
            }

            if (!$quiz->canUserAttempt($user)) {
                $validator->errors()->add('quiz', 'You have reached the maximum number of attempts.');
            }

            if ($quiz->creator_id === $user->id) {
                $validator->errors()->add('quiz', 'You cannot attempt your own quiz.');
            }

            if ($this->filled('quiz_session_id')) {
                $session = QuizSession::find($this->input('quiz_session_id'));

                if (!$session || $session->quiz_id !== $quiz->id || !$session->canUserJoin($user)) {
                    $validator->errors()->add('quiz_session_id', 'The selected quiz session is not available.');
                }
            }
        });
    }
}
