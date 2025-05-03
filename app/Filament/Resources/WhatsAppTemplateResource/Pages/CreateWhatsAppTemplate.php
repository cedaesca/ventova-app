<?php

namespace App\Filament\Resources\WhatsAppTemplateResource\Pages;

use App\Filament\Resources\WhatsAppTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateWhatsAppTemplate extends CreateRecord
{
    protected static string $resource = WhatsAppTemplateResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $creator = Auth::user();

        $template = $creator->whatsAppTemplates()->create([
            'name' => $data['name'],
            'category_id' => $data['category_id'],
            'previous_category_id' => $data['category_id'],
            'language_code' => $data['language_code'],
        ]);

        return $template;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
