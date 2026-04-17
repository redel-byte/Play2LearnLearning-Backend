<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'prompt' => $this->prompt,
            'points' => $this->points,
            'position' => $this->position,
            'choices' => $this->whenLoaded('choices', fn () => ChoiceResource::collection($this->choices)),
        ];
    }
}
