<?php

use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\MemberPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingPageController::class, 'index'])
    ->name('home');

Route::get('/portal', [MemberPortalController::class, 'index'])
    ->name('portal.lookup');

Route::get('/status', [MemberPortalController::class, 'index'])
    ->name('portal.status');

Route::post('/portal/lookup', [MemberPortalController::class, 'lookup'])
    ->middleware('throttle:30,1')
    ->name('portal.find');

Route::get('/pass/{qr_code}', [MemberPortalController::class, 'showPass'])
    ->where('qr_code', '[A-Za-z0-9_\-]+')
    ->name('portal.pass');

Route::post('/pass/{qr_code}/pay', [MemberPortalController::class, 'pay'])
    ->where('qr_code', '[A-Za-z0-9_\-]+')
    ->middleware('throttle:10,1')
    ->name('portal.pay');

Route::view('/payments/success', 'payments.result', ['status' => 'success'])
    ->name('payments.success');

Route::view('/payments/cancelled', 'payments.result', ['status' => 'cancelled'])
    ->name('payments.cancelled');
