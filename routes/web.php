<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/meta/whatsapp', function () {
    Log::info('Meta WhatsApp Webhook', [
        'request' => request()->all(),
    ]);

    return response(status: 200);
})->withoutMiddleware(VerifyCsrfToken::class);

Route::get('/webhooks/meta/whatsapp', function (Request $request) {
    return response($request->hub_challenge, 200)
        ->header('Content-Type', 'text/plain');
});
