<?php
declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasScopes
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopePrivate(Builder $query): Builder
    {
        return $query->where('is_public', false);
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeOldest(Builder $query): Builder
    {
        return $query->orderBy('created_at');
    }

    public function scopeByName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', "%{$name}%");
    }

    public function scopeByTitle(Builder $query, string $title): Builder
    {
        return $query->where('title', 'like', "%{$title}%");
    }

    public function scopeByEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', 'like', "%{$email}%");
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('submitted_at');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('submitted_at');
    }

    public function scopePassed(Builder $query): Builder
    {
        return $query->where('passed', true);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('passed', false);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->orderByDesc('views_count')
                    ->orderByDesc('copies_count');
    }

    public function scopeTopPerformers(Builder $query, int $limit = 10): Builder
    {
        return $query->orderByDesc('total_points')
                    ->orderByDesc('average_score')
                    ->limit($limit);
    }
}
