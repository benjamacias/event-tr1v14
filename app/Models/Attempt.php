<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attempt extends Model
{
    public const STATUS_STARTED = 'started';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'participant_id',
        'question_set_id',
        'status',
        'started_at',
        'completed_at',
        'correct_answers_count',
        'total_time_seconds',
        'duplicate_flag',
        'user_agent',
        'ip_address',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duplicate_flag' => 'boolean',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function questionSet(): BelongsTo
    {
        return $this->belongsTo(QuestionSet::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    public function orderedAnswers(): HasMany
    {
        return $this->answers()->with(['question', 'answerOption'])->orderBy('answered_at');
    }

    public function nextQuestion(): ?Question
    {
        $answeredIds = $this->answers()->pluck('question_id');

        return $this->questionSet
            ->questions()
            ->where('is_active', true)
            ->whereNotIn('id', $answeredIds)
            ->orderBy('sort_order')
            ->with('answerOptions')
            ->first();
    }

    public function formattedTime(): ?string
    {
        if ($this->total_time_seconds === null) {
            return null;
        }

        return sprintf('%02d:%02d', intdiv($this->total_time_seconds, 60), $this->total_time_seconds % 60);
    }
}
