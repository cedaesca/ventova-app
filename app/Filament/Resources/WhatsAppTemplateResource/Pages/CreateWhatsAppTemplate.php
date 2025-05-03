<?php

namespace App\Filament\Resources\WhatsAppTemplateResource\Pages;

use App\Filament\Resources\WhatsAppTemplateResource;
use App\Interfaces\Services\WhatsAppCloudServiceInterface;
use App\Jobs\CreateWhatsAppTemplateOnMeta;
use App\Models\WhatsAppTemplate;
use App\Models\WhatsAppTemplateCategory;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateWhatsAppTemplate extends CreateRecord
{
    protected static string $resource = WhatsAppTemplateResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $response = null;
        $template = new WhatsAppTemplate();

        DB::transaction(function () use (&$response, $data, &$template) {
            $creator = Auth::user();
            $whatsAppCloudService = app()->get(WhatsAppCloudServiceInterface::class);

            $template = $creator->whatsAppTemplates()->create([
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'previous_category_id' => $data['category_id'],
                'language_code' => $data['language_code'],
            ]);

            $categoryCode = $template->category->meta_code;
            $components = $this->formatTemplateComponents($data);

            $response = $whatsAppCloudService->createTemplate($data['name'], $categoryCode, $template->language_code, $components);

            $metaCategoryCode = $response['category'];

            $newCategory = app()
                ->get(WhatsAppTemplateCategory::class)
                ->where('meta_code', $metaCategoryCode)
                ->firstOrFail();

            Log::debug('Meta Response', $response);

            $template->category_id = $newCategory->id;
            $template->meta_template_id = $response['id'];
            $template->save();
        });

        //CreateWhatsAppTemplateOnMeta::dispatch($data['name'], $data['category_id'], $data['language_code']);

        return $template;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    private function formatTemplateComponents(array $data): array
    {
        $components = [];

        if ($data['header']) {
            $component = [];

            if (isset($data['header_variables'])) {
                $component['example'] = [
                    'header_text' => array_values($data['header_variables'])
                ];
            }

            $component['type'] = 'HEADER';
            $component['format'] = 'TEXT';
            $component['text'] = $data['header'];
            $components[] = $component;
        }

        if ($data['body']) {
            $component = [];

            if (isset($data['body_variables'])) {
                $component['example'] = [
                    'body_text' => [array_values($data['body_variables'])]
                ];
            }

            $component['type'] = 'BODY';
            $component['text'] = $data['body'];

            $components[] = $component;
        }

        return $components;
    }
}
