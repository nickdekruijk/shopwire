<?php

use NickDeKruijk\Shopwire\Controllers\PaymentController;

Route::group(['middleware' => ['web']], function () {
    Route::post(config('shopwire.routes_prefix') . '/payment/webhook', [PaymentController::class, 'webhook'])->name('shopwire-payment-webhook');
    Route::get(config('shopwire.routes_prefix') . '/payment/verify', [PaymentController::class, 'verify'])->name('shopwire-payment-verify');
});
