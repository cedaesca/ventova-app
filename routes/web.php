<?php

use App\Enums\ResourceStatusesEnum;
use App\Models\WhatsAppTemplate;
use App\Models\WhatsAppTemplateCategory;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/meta/whatsapp', function (Request $request) {
    Log::info('Meta WhatsApp Webhook', [
        'request' => request()->all(),
    ]);

    $payload = $request->input('entry.0.changes.0');

    if (is_null($payload)) {
        Log::debug('No hay Request', ['request' => $request->all()]);

        return response(status: 200);
    }

    $eventType = $payload['field'] ?? null;

    if ($eventType === 'message_template_status_update') {
        $templateId = $payload['value']['message_template_id'];

        $template = WhatsAppTemplate::where('meta_template_id', $templateId)->first();
        $template->status = $payload['value']['event'];
        $template->save();
    }

    return response(status: 200);
})->withoutMiddleware(VerifyCsrfToken::class);

Route::get('/webhooks/meta/whatsapp', function (Request $request) {
    return response($request->hub_challenge, 200)
        ->header('Content-Type', 'text/plain');
});
