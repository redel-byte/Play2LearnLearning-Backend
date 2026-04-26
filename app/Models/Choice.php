<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Choice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'question_id',
        'label',
        'is_correct',
        'position',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'position' => 'integer',
    ];

    protected $attributes = [
        'is_correct' => false,
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
