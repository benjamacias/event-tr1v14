<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderLogo extends Model
{
    protected $fillable = ['name', 'image_path', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];
}
