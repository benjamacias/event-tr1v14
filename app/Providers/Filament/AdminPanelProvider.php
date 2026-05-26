<?php

namespace App\Providers\Filament;

use Filament\Facades\Filament;
use Filament\Navigation\UserMenuItem;
use Illuminate\Support\ServiceProvider;

class AdminPanelProvider extends ServiceProvider
{
    public function boot(): void
    {
        Filament::serving(function (): void {
            Filament::registerUserMenuItems([
                'home' => UserMenuItem::make()
                    ->label('Ver sitio')
                    ->url(route('participants.create')),
            ]);
        });
    }
}
