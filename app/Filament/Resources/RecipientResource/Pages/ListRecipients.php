<?php

namespace App\Filament\Resources\RecipientResource\Pages;

use App\Filament\Resources\RecipientResource;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListRecipients extends ListRecords
{
    protected static string $resource = RecipientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
            Actions\Action::make('Importar')
                ->label('Importar destinatarios')
                ->color('primary')
                ->url(self::getResource()::getUrl('import'))
        ];
    }

    public function getTabs(): array
    {
        $recipientGroups = Auth::user()->recipientGroups;

        $tabs = [];

        foreach ($recipientGroups as $group) {
            $tabs[$group->name] = Tab::make($group->name)
                ->modifyQueryUsing(function (Builder $query) use ($group) {
                    $query->where('recipient_group_id', $group->id);
                });
        }

        return $tabs;
    }
}
