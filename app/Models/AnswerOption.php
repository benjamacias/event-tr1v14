<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnswerOption extends Model
{
    protected $fillable = ['question_id', 'label', 'text', 'is_correct', 'explanation', 'sort_order'];

    protected $casts = ['is_correct' => 'boolean'];

    protected static function booted(): void
    {
        static::saved(function (AnswerOption $option): void {
            if ($option->is_correct) {
                static::query()
                    ->where('question_id', $option->question_id)
                    ->whereKeyNot($option->id)
                    ->update(['is_correct' => false]);
            }
        });
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function attemptAnswers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }
}
