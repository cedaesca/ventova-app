<?php

namespace App\Filament\Resources\RecipientGroupResource\Pages;

use App\Filament\Resources\RecipientGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateRecipientGroup extends CreateRecord
{
    protected static string $resource = RecipientGroupResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;

        return $data;
    }
}
