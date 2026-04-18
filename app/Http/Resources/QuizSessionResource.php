<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class QuizSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'host_id' => $this->host_id,
            'title' => $this->title,
            'join_code' => $this->join_code,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'status' => $this->status,
            'participant_count' => $this->participant_count,
            'completed_attempts_count' => $this->completed_attempts_count,
            'quiz' => $this->whenLoaded('quiz', fn () => new QuizResource($this->quiz)),
            'host' => $this->whenLoaded('host', fn () => new UserResource($this->host)),
        ];
    }
}
