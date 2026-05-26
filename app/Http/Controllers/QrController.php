<?php

namespace App\Http\Controllers;

use App\Services\QrCodeSvg;
use App\Services\Settings;
use Illuminate\View\View;

class QrController extends Controller
{
    public function __invoke(Settings $settings, QrCodeSvg $qrCode): View
    {
        $qrTargetUrl = $settings->get('qr_target_url') ?: route('participants.create');

        return view('qr.print', [
            'logoPath' => $settings->get('main_logo_path'),
            'printText' => $settings->get('qr_print_text', 'Escanea el codigo QR para participar de la trivia de Ianus SA.'),
            'qrSvg' => $qrCode->generate($qrTargetUrl, 520),
        ]);
    }
}
