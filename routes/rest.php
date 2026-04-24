<?php

use App\Http\Controllers\Rest\PublicApi\ContactController;
use App\Http\Controllers\Rest\PublicApi\TrackerController;
use App\Http\Controllers\Rest\Webhooks\EasyPostWebhookController;
use App\Http\Controllers\Rest\Webhooks\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// 3rd-party inbound endpoints: EasyPost + Stripe webhooks,
// plus tenant-facing public tenant-API (P1). All signature-verified per route.

Route::get('/health', fn () => ['ok' => true])->name('rest.health');

Route::prefix('public')->name('public.')->group(function () {
    Route::get('trackers/{code}', TrackerController::class)
        ->name('trackers.show')
        ->middleware('throttle:60,10');

    Route::post('contact', ContactController::class)
        ->name('contact')
        ->middleware('throttle:5,10');
});

Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('easypost', EasyPostWebhookController::class)->name('easypost');
    Route::post('stripe', StripeWebhookController::class)->name('stripe');
});
