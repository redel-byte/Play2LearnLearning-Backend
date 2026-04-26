<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Answer extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'quiz_attempt_id',
        'question_id',
        'text_answer',
        'is_correct',
        'earned_points',
        'answered_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'earned_points' => 'integer',
        'answered_at' => 'datetime',
    ];

    protected $attributes = [
        'earned_points' => 0,
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedChoices(): BelongsToMany
    {
        return $this->belongsToMany(Choice::class, 'answer_choice');
    }
}
