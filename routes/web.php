<?php

use Cai\Identity\Http\Controllers\MfaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

// MFA: rutas públicas (todavía no hay sesión autenticada durante el challenge).
Route::get('/mfa/challenge', [MfaController::class, 'challenge'])->name('mfa.challenge');
Route::post('/mfa/challenge', [MfaController::class, 'verify'])->name('mfa.verify');
Route::get('/mfa/setup', [MfaController::class, 'setup'])->name('mfa.setup');
Route::post('/mfa/setup', [MfaController::class, 'confirmSetup'])->name('mfa.setup.confirm');
