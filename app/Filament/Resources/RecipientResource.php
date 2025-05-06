<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipientResource\Pages;
use App\Filament\Resources\RecipientResource\RelationManagers;
use App\Models\Recipient;
use App\Models\RecipientGroup;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RecipientResource extends VentovaResource
{
    protected static ?string $model = Recipient::class;

    protected static ?string $navigationIcon = 'phosphor-phone';
    protected static ?string $navigationLabel = 'Destinatarios';
    protected static ?string $breadcrumb = 'Destinatarios';
    protected static ?string $label = 'destinatarios';
    protected static ?string $navigationGroup = 'Envíos masivos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('recipient_group_id')
                    ->label('Grupo de destinatarios')
                    ->searchable()
                    ->required()
                    ->options(
                        RecipientGroup::query()
                            ->where('user_id', Auth::user()->id)
                            ->whereHas(relation: 'recipients', operator: '<', count: 1)
                            ->pluck('name', 'id')
                    )
                    ->columnSpanFull(),
                FileUpload::make('file')
                    ->disk('local')
                    ->label('Archivo Excel')
                    ->required()
                    ->directory('recipients')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                    ])
                    ->maxSize(1024 * 5)
                    ->hint('El archivo debe tener obligatoriamente una columna con el nombre "phone_number".')
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        $activeTab = $table->getLivewire()->activeTab;

        $group = Auth::user()->recipientGroups()
            ->where('name', $activeTab)
            ->first();

        $dynamicTableColumns = self::getDynamicTableColumns($group);

        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone_number')
                    ->label('Número de teléfono')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                ...$dynamicTableColumns,
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecipients::route('/'),
            //'create' => Pages\CreateRecipient::route('/create'),
            'edit' => Pages\EditRecipient::route('/{record}/edit'),
            'import' => Pages\ImportRecipients::route('/import'),
        ];
    }

    private static function getDynamicTableColumns(?RecipientGroup $group): array
    {
        if (!$group) {
            return [];
        }

        $variableLabels = $group->recipients()
            ->join('recipient_variables', 'recipients.id', '=', 'recipient_variables.recipient_id')
            ->distinct()
            ->pluck('recipient_variables.label');

        return $variableLabels->map(function ($label) use ($group) {
            return TextColumn::make('variables.' . $label)
                ->label($label)
                ->sortable()
                ->searchable()
                ->toggleable()
                ->getStateUsing(function ($record) use ($label) {
                    return optional($record->variables->firstWhere('label', $label))->value;
                });
        })->toArray();
    }
}
