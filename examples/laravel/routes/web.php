<?php

use App\Http\Controllers\PesapalPaymentController;
use Illuminate\Support\Facades\Route;

Route::post('/payments/pesapal/checkout', [PesapalPaymentController::class, 'checkout'])
    ->name('pesapal.checkout');

Route::match(['GET', 'POST'], '/payments/pesapal/callback', [PesapalPaymentController::class, 'callback'])
    ->name('pesapal.callback');
