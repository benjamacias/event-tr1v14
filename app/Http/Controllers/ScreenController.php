<?php

namespace App\Http\Controllers;

use App\Models\ProviderLogo;
use App\Services\QrCodeSvg;
use App\Services\Settings;
use Illuminate\View\View;

class ScreenController extends Controller
{
    public function __invoke(Settings $settings, QrCodeSvg $qrCode): View
    {
        $qrTargetUrl = $settings->get('qr_target_url') ?: route('participants.create');
        $providerLogos = ProviderLogo::query()
            ->where('is_active', true)
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('screen.show', [
            'logoPath' => $settings->get('main_logo_path'),
            'providerLogos' => $providerLogos,
            'providerAds' => $providerLogos
                ->map(fn (ProviderLogo $logo): array => [
                    'name' => $logo->name,
                    'url' => asset('storage/'.$logo->image_path),
                ])
                ->values(),
            'qrSvg' => $qrCode->generate($qrTargetUrl, 360),
        ]);
    }
}
