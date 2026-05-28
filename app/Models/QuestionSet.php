<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class QuestionSet extends Model
{
    protected $fillable = ['name', 'slug', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (QuestionSet $set): void {
            if (! $set->slug) {
                $set->slug = Str::slug($set->name);
            }
        });
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    public function activeQuestions(): HasMany
    {
        return $this->questions()->where('is_active', true)->orderBy('sort_order');
    }

    public function isPlayable(): bool
    {
        $questions = $this->questions()
            ->where('is_active', true)
            ->with(['answerOptions' => fn ($query) => $query->orderBy('sort_order')])
            ->get();

        return $questions->count() === 5
            && $questions->every(fn (Question $question) => $question->answerOptions->count() === 3
                && $question->answerOptions->where('is_correct', true)->count() === 1);
    }
}
