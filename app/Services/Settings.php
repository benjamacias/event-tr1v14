<?php

namespace App\Services;

use App\Models\Setting;

class Settings
{
    public function get(string $key, mixed $default = null): mixed
    {
        return Setting::valueFor($key, $default);
    }

    public function eventIsActive(): bool
    {
        return (bool) $this->get('event_active', true);
    }
}
