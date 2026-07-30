<?php

use App\Http\Controllers\DealController;
use App\Http\Controllers\WebhookController;
use App\Http\Middleware\VerifyWebhookToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/ping', function () {
    return [
        'status' => 'ok',
        'message' => 'Салам брат работаю четенько!!!!',
    ];
});

Route::post('/webhooks/mock-booking', [WebhookController::class, 'handleMockBooking'])
    ->middleware(VerifyWebhookToken::class)
    ->name('webhooks.mock-booking');

Route::get('/deals', [DealController::class, 'index'])->name('api.deals.index');
