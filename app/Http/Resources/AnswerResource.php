<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AnswerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question_id' => $this->question_id,
            'text_answer' => $this->text_answer,
            'selected_choices' => $this->whenLoaded('selectedChoices', fn () => ChoiceResource::collection($this->selectedChoices)),
            'is_correct' => $this->is_correct,
            'earned_points' => $this->earned_points,
            'answered_at' => $this->answered_at,
        ];
    }
}
