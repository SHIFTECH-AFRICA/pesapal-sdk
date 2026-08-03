<?php

use App\Http\Controllers\PesapalPaymentController;
use Illuminate\Support\Facades\Route;

Route::match(['GET', 'POST'], '/payments/pesapal/ipn', [PesapalPaymentController::class, 'ipn'])
    ->name('pesapal.ipn');
