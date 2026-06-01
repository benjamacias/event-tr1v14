<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    protected $fillable = ['full_name', 'document_number', 'email', 'phone', 'institution_role', 'consent_accepted'];

    protected $casts = ['consent_accepted' => 'boolean'];

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    public function latestAttempt(): HasOne
    {
        return $this->hasOne(Attempt::class)->latestOfMany();
    }
}
