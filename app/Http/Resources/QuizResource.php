<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class QuizResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'access_code' => $this->access_code,
            'is_public' => $this->is_public,
            'is_featured' => $this->is_featured,
            'time_limit_minutes' => $this->time_limit_minutes,
            'max_attempts' => $this->max_attempts,
            'pass_percentage' => $this->pass_percentage,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'creator' => $this->whenLoaded('creator', fn () => new UserResource($this->creator)),
            'questions' => $this->whenLoaded('questions', fn () => QuestionResource::collection($this->questions)),
            'quiz_sessions' => $this->whenLoaded('quizSessions', fn () => QuizSessionResource::collection($this->quizSessions)),
            'attempts' => $this->whenLoaded('attempts', fn () => QuizAttemptResource::collection($this->attempts)),
            'total_questions' => $this->total_questions,
            'total_points' => $this->total_points,
            'average_score' => round((float) $this->average_score, 2),
            'completion_rate' => round((float) $this->completion_rate, 2),
            'user_can_attempt' => $this->when(
                $request->user(),
                fn () => $this->canUserAttempt($request->user())
            ),
        ];
    }
}
