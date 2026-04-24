<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class Quiz extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'creator_id',
        'title',
        'description',
        'access_code',
        'is_public',
        'is_featured',
        'time_limit_minutes',
        'max_attempts',
        'pass_percentage',
        'published_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
        'time_limit_minutes' => 'integer',
        'max_attempts' => 'integer',
        'pass_percentage' => 'integer',
        'published_at' => 'datetime',
    ];

    protected $attributes = [
        'is_public' => false,
        'is_featured' => false,
        'max_attempts' => 1,
        'pass_percentage' => 70,
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }

    public function quizSessions(): HasMany
    {
        return $this->hasMany(QuizSession::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function getTotalQuestionsAttribute(): int
    {
        return $this->questions()->count();
    }

    public function getTotalPointsAttribute(): int
    {
        return (int) $this->questions()->sum('points');
    }

    public function getAverageScoreAttribute(): float
    {
        $results = $this->attempts()
            ->whereNotNull('submitted_at')
            ->get()
            ->pluck('percentage')
            ->filter();

        return $results->isNotEmpty() ? $results->avg() : 0;
    }

    public function getCompletionRateAttribute(): float
    {
        $totalAttempts = $this->attempts()->count();
        $completedAttempts = $this->attempts()->whereNotNull('submitted_at')->count();

        return $totalAttempts > 0 ? ($completedAttempts / $totalAttempts) * 100 : 0;
    }

    public function isAvailableToUser(User $user): bool
    {
        if ($this->is_public) {
            return true;
        }

        if ($this->creator_id === $user->id) {
            return true;
        }

        return $user->quizAttempts()->where('quiz_id', $this->id)->exists();
    }

    public function canUserAttempt(User $user): bool
    {
        if ($this->creator_id === $user->id) {
            return false;
        }

        $attemptCount = $user->quizAttempts()
            ->where('quiz_id', $this->id)
            ->count();

        return $attemptCount < $this->max_attempts;
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeByCreator(Builder $query, User $user): Builder
    {
        return $query->where('creator_id', $user->id);
    }

    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('is_public', true)
              ->orWhere('creator_id', $user->id);
        });
    }

    protected static function booted(): void
    {
        static::creating(function ($quiz) {
            if (!$quiz->access_code) {
                $quiz->access_code = self::generateUniqueAccessCode();
            }
        });

        static::updating(function ($quiz) {
            if ($quiz->isDirty('access_code') && !$quiz->access_code) {
                $quiz->access_code = self::generateUniqueAccessCode();
            }
        });
    }

    private static function generateUniqueAccessCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('access_code', $code)->exists());

        return $code;
    }
}
