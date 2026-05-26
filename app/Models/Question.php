<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = ['question_set_id', 'text', 'explanation', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function questionSet(): BelongsTo
    {
        return $this->belongsTo(QuestionSet::class);
    }

    public function answerOptions(): HasMany
    {
        return $this->hasMany(AnswerOption::class)->orderBy('sort_order');
    }

    public function attemptAnswers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    public function correctOption(): ?AnswerOption
    {
        return $this->answerOptions()->where('is_correct', true)->first();
    }
}
