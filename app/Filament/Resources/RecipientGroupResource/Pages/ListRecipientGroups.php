<?php

namespace App\Filament\Resources\RecipientGroupResource\Pages;

use App\Filament\Resources\RecipientGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRecipientGroups extends ListRecords
{
    protected static string $resource = RecipientGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
