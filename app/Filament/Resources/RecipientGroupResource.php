<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipientGroupResource\Pages;
use App\Filament\Resources\RecipientGroupResource\RelationManagers;
use App\Models\RecipientGroup;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class RecipientGroupResource extends VentovaResource
{
    protected static ?string $model = RecipientGroup::class;

    protected static ?string $navigationIcon = 'elusive-group';
    protected static ?string $navigationLabel = 'Grupos de destinatarios';
    protected static ?string $breadcrumb = 'Grupos de destinatarios';
    protected static ?string $label = 'grupo de destinatarios';
    protected static ?string $navigationGroup = 'Envíos masivos';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::user()->id);
    }

    public static function canEdit(Model $record): bool
    {
        return $record->user_id === Auth::user()->id || Auth::user()->isAdmin();
    }

    public static function canDelete(Model $record): bool
    {
        return $record->user_id === Auth::user()->id || Auth::user()->isAdmin();
    }

    public static function canView(Model $record): bool
    {
        return $record->user_id === Auth::user()->id || Auth::user()->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('description')
                    ->label('Descripción')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
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
            'index' => Pages\ListRecipientGroups::route('/'),
            'create' => Pages\CreateRecipientGroup::route('/create'),
            'edit' => Pages\EditRecipientGroup::route('/{record}/edit'),
        ];
    }
}
