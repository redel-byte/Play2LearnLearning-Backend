<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class BadgeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'icon_url' => $this->icon_url,
            'criteria' => $this->criteria,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            'learners' => $this->whenLoaded('learners', fn() => UserResource::collection($this->learners)),
            
            'earner_count' => $this->when(
                $request->has('include_stats'),
                $this->earner_count ?? $this->learners()->count()
            ),
            'is_rare' => $this->when(
                $request->has('include_metadata'),
                fn() => $this->earner_count < 10
            ),
            'popularity_tier' => $this->when(
                $request->has('include_metadata'),
                fn() => $this->getPopularityTier()
            ),
            
            'user_earned' => $this->when(
                $request->user(),
                fn() => $this->learners()->where('learner_id', $request->user()->id)->exists()
            ),
            'earned_at' => $this->when(
                $request->user() && $this->relationLoaded('learners'),
                fn() => $this->learners->where('learner_id', $request->user()->id)->first()?->pivot->earned_at
            ),
        ];
    }

    private function getPopularityTier(): string
    {
        $count = $this->earner_count ?? $this->learners()->count();
        
        return match (true) {
            $count >= 100 => 'common',
            $count >= 50 => 'uncommon',
            $count >= 20 => 'rare',
            $count >= 10 => 'epic',
            default => 'legendary',
        };
    }
}

