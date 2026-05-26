<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSetting extends CreateRecord
{
    protected static string $resource = SettingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['value'] = $data['type'] === 'image'
            ? $this->imageValue($data['value_file'] ?? null)
            : ($data['value_text'] ?? null);

        unset($data['value_file'], $data['value_text']);

        return $data;
    }

    private function imageValue(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        return filled($value) ? (string) $value : null;
    }
}
