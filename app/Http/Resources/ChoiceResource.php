<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ChoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'position' => $this->position,
            'is_correct' => $this->when(
                $request->boolean('show_answers')
                    || optional($request->user())->isTeacher()
                    || optional($request->user())->isAdmin(),
                $this->is_correct
            ),
        ];
    }
}
