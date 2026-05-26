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

        return view('screen.show', [
            'logoPath' => $settings->get('main_logo_path'),
            'providerLogos' => ProviderLogo::query()
                ->where('is_active', true)
                ->orderByRaw('sort_order is null')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
            'qrSvg' => $qrCode->generate($qrTargetUrl, 360),
        ]);
    }
}
