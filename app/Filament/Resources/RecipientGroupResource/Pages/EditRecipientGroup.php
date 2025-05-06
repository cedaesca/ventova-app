<?php

namespace App\Filament\Resources\RecipientGroupResource\Pages;

use App\Filament\Resources\RecipientGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRecipientGroup extends EditRecord
{
    protected static string $resource = RecipientGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
