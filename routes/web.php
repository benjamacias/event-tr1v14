<?php

use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\ScreenController;
use App\Http\Controllers\TriviaController;
use Illuminate\Support\Facades\Route;

Route::get('/login', fn () => redirect()->route('filament.auth.login'))->name('login');

Route::get('/screen', ScreenController::class)->name('screen.show');
Route::get('/qr/print', QrController::class)->name('qr.print');

Route::middleware(['event.active'])->group(function () {
    Route::get('/', [ParticipantController::class, 'create'])->name('participants.create');

    Route::post('/participants', [ParticipantController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('participants.store');

    Route::get('/play/{attempt}', [TriviaController::class, 'show'])->name('play.show');
    Route::post('/play/{attempt}/answer', [TriviaController::class, 'answer'])
        ->middleware('throttle:30,1')
        ->name('play.answer');
    Route::get('/play/{attempt}/result', [TriviaController::class, 'result'])->name('play.result');
});
