<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class QuizAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'learner_id' => $this->learner_id,
            'quiz_session_id' => $this->quiz_session_id,
            'started_at' => $this->started_at,
            'submitted_at' => $this->submitted_at,
            'score' => $this->score,
            'max_score' => $this->max_score,
            'percentage' => $this->percentage,
            'passed' => $this->passed,
            'time_taken_seconds' => $this->time_taken,
            'status' => $this->submitted_at ? 'submitted' : 'in_progress',
            'learner' => $this->whenLoaded('learner', fn () => new UserResource($this->learner)),
            'quiz' => $this->whenLoaded('quiz', fn () => new QuizResource($this->quiz)),
            'answers' => $this->whenLoaded('answers', fn () => AnswerResource::collection($this->answers)),
        ];
    }
}
