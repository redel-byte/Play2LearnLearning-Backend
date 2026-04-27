<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class QuizSession extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'quiz_id',
        'host_id',
        'title',
        'join_code',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->ends_at && now() > $this->ends_at) {
            return false;
        }

        return true;
    }

    public function getParticipantCountAttribute(): int
    {
        return $this->attempts()->distinct('learner_id')->count();
    }

    public function getCompletedAttemptsCountAttribute(): int
    {
        return $this->attempts()->whereNotNull('submitted_at')->count();
    }

    public function canUserJoin(User $user): bool
    {
        if (!$user->isLearner() || !$user->is_active) {
            return false;
        }

        if (!$this->isActive()) {
            return false;
        }

        return $this->host_id !== $user->id;
    }

    protected static function booted(): void
    {
        static::creating(function ($session) {
            if (!$session->join_code) {
                $session->join_code = self::generateUniqueAccessCode();
            }
        });
    }

    private static function generateUniqueAccessCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 6));
        } while (self::where('join_code', $code)->exists());

        return $code;
    }
}
