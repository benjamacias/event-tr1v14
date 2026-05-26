<?php

use App\Http\Controllers\Admin\ParticipantExportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/participants/export.csv', [ParticipantExportController::class, 'csv'])->name('participants.export.csv');
    Route::get('/participants/export.xlsx', [ParticipantExportController::class, 'xlsx'])->name('participants.export.xlsx');
});
