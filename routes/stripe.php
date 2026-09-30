<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\StripePaymentController;

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

Route::post('/stripe/checkout', [StripePaymentController::class, 'checkout']);