<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class QuizAttempt extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'learner_id',
        'quiz_session_id',
        'quiz_id',
        'started_at',
        'submitted_at',
        'score',
        'max_score',
        'percentage',
        'passed',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'score' => 'integer',
        'max_score' => 'integer',
        'percentage' => 'decimal:2',
        'passed' => 'boolean',
    ];

    public function learner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'learner_id');
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'quiz_attempt_id');
    }

    public function getTimeTakenAttribute(): ?int
    {
        if (!$this->started_at) {
            return null;
        }

        $endTime = $this->submitted_at ?: now();
        
        return (int) $this->started_at->diffInSeconds($endTime);
    }

    public function calculateScore(): void
    {
        $totalScore = 0;
        $maxPossibleScore = 0;

        foreach ($this->quiz->questions as $question) {
            $answer = $this->answers()->where('question_id', $question->id)->first();
            
            $maxPossibleScore += $question->points;
            
            if ($answer) {
                $response = $answer->selectedChoices->pluck('id')->all();
                if ($response === []) {
                    $response = $answer->text_answer;
                } elseif (count($response) === 1 && ($question->isSingleChoice() || $question->isTrueFalse())) {
                    $response = $response[0];
                }

                $score = $question->calculateScore($response);
                $totalScore += $score;
                
                $answer->update([
                    'is_correct' => $score > 0,
                    'earned_points' => $score,
                ]);
            }
        }

        $this->update([
            'score' => $totalScore,
            'max_score' => $maxPossibleScore,
            'percentage' => $maxPossibleScore > 0 ? round(($totalScore / $maxPossibleScore) * 100, 2) : null,
            'passed' => $maxPossibleScore > 0 ? (($totalScore / $maxPossibleScore) * 100) >= $this->quiz->pass_percentage : null,
        ]);
    }

    public function submit(): self
    {
        if ($this->submitted_at) {
            throw new \Exception('Attempt is already completed');
        }

        $this->calculateScore();
        
        $this->update([
            'submitted_at' => now(),
        ]);

        return $this->refresh();
    }

    protected static function booted(): void
    {
        static::creating(function ($attempt) {
            if (!$attempt->started_at) {
                $attempt->started_at = now();
            }
        });
    }
}
