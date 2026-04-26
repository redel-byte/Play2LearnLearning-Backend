<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ChoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $attempt = $request->route('attempt');
        $canReviewSubmittedAttempt = $attempt instanceof QuizAttempt
            && $attempt->submitted_at !== null
            && $attempt->learner_id === optional($user)->id;

        return [
            'id' => $this->id,
            'label' => $this->label,
            'position' => $this->position,
            'is_correct' => $this->when(
                $request->boolean('show_answers')
                    || optional($user)->isTeacher()
                    || optional($user)->isAdmin()
                    || $canReviewSubmittedAttempt,
                $this->is_correct
            ),
        ];
    }
}
