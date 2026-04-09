<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Question extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'quiz_id',
        'type',
        'prompt',
        'points',
        'position',
    ];

    protected $casts = [
        'points' => 'integer',
        'position' => 'integer',
    ];

    protected $attributes = [
        'points' => 1,
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function choices(): HasMany
    {
        return $this->hasMany(Choice::class)->orderBy('position');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function correctChoices(): HasMany
    {
        return $this->hasMany(Choice::class)->where('is_correct', true);
    }

    public function isMultipleChoice(): bool
    {
        return $this->type === 'multiple_choice';
    }

    public function isSingleChoice(): bool
    {
        return $this->type === 'single_choice';
    }

    public function isTrueFalse(): bool
    {
        return $this->type === 'true_false';
    }

    public function isShortAnswer(): bool
    {
        return $this->type === 'short_answer';
    }

    public function hasChoices(): bool
    {
        return $this->isMultipleChoice() || $this->isSingleChoice() || $this->isTrueFalse();
    }

    public function validateAnswer($answer): bool
    {
        if ($this->isShortAnswer()) {
            return $this->validateShortAnswer($answer);
        }

        if ($this->hasChoices()) {
            return $this->validateChoiceAnswer($answer);
        }

        return false;
    }

    private function validateShortAnswer(string $answer): bool
    {
        return !empty(trim($answer));
    }

    private function validateChoiceAnswer($answer): bool
    {
        if ($this->isSingleChoice() || $this->isTrueFalse()) {
            return $this->choices()->where('id', $answer)->exists();
        }

        if ($this->isMultipleChoice()) {
            if (!is_array($answer)) {
                return false;
            }

            return $this->choices()->whereIn('id', $answer)->count() === count($answer);
        }

        return false;
    }

    public function calculateScore($answer): int
    {
        if (!$this->validateAnswer($answer)) {
            return 0;
        }

        if ($this->isShortAnswer()) {
            return $this->points;
        }

        if ($this->isSingleChoice() || $this->isTrueFalse()) {
            return $this->choices()->where('id', $answer)->where('is_correct', true)->exists() 
                ? $this->points 
                : 0;
        }

        if ($this->isMultipleChoice()) {
            $correctChoices = $this->correctChoices()->pluck('id')->toArray();
            $selectedChoices = is_array($answer) ? $answer : [];

            if (count($selectedChoices) !== count($correctChoices)) {
                return 0;
            }

            $correctSelections = array_intersect($selectedChoices, $correctChoices);
            
            return count($correctSelections) === count($correctChoices) ? $this->points : 0;
        }

        return 0;
    }
}
