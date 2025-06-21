<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-session', function () {
    session(['test' => 'working']);
    return 'Session set';
});

Route::get('/check-session', function () {
    return session('test', 'not working');
});

// Rotte per export PDF fatture
Route::middleware(['auth'])->group(function () {
    Route::get('/fatture/{fattura}/pdf', [App\Http\Controllers\FatturaController::class, 'viewPdf'])
        ->name('fatture.pdf');
    Route::get('/fatture/{fattura}/download', [App\Http\Controllers\FatturaController::class, 'downloadPdf'])
        ->name('fatture.download');
    Route::post('/fatture/{fattura}/invia-email', [App\Http\Controllers\FatturaController::class, 'inviaEmail'])
        ->name('fatture.invia-email');
});
