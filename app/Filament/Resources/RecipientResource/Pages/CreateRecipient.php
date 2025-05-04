<?php

namespace App\Filament\Resources\RecipientResource\Pages;

use App\Filament\Resources\RecipientResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CreateRecipient extends CreateRecord
{
    protected static string $resource = RecipientResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $filePath = $data['file'];
        $fileInstance = Storage::disk('local')->get($filePath);

        return parent::handleRecordCreation($data);
    }
}
