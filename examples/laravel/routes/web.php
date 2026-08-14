<?php

use App\Http\Controllers\PesapalPaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Checkout page
|--------------------------------------------------------------------------
|
| No variables are needed on the initial GET.
| checkout.blade.php detects that $pesapalUrl is absent
| and displays the payment form.
|
*/

Route::view('/', 'payments.checkout')
    ->name('payments.demo');


/*
|--------------------------------------------------------------------------
| Create Pesapal order
|--------------------------------------------------------------------------
*/

Route::post(
    '/payments/pesapal/checkout',
    [PesapalPaymentController::class, 'checkout']
)->name('pesapal.checkout');


/*
|--------------------------------------------------------------------------
| Pesapal callback
|--------------------------------------------------------------------------
*/

Route::match(
    ['GET', 'POST'],
    '/payments/pesapal/callback',
    [PesapalPaymentController::class, 'callback']
)->name('pesapal.callback');


/*
|--------------------------------------------------------------------------
| Pesapal IPN
|--------------------------------------------------------------------------
*/

Route::match(
    ['GET', 'POST'],
    '/payments/pesapal/ipn',
    [PesapalPaymentController::class, 'ipn']
)->name('pesapal.ipn');