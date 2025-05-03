<?php

namespace App\Filament\Resources;

use App\Enums\ResourceStatusesEnum;
use App\Filament\Resources\WhatsAppTemplateResource\Pages;
use App\Filament\Resources\WhatsAppTemplateResource\RelationManagers;
use App\Models\WhatsAppTemplate;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class WhatsAppTemplateResource extends Resource
{
    protected static ?string $model = WhatsAppTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Plantillas';
    protected static ?string $navigationGroup = 'WhatsApp';
    protected static ?string $breadcrumb = 'Plantillas';
    protected static ?string $label = 'Plantilla';
    protected static ?string $slug = 'whatsapp/templates';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información básica')
                    ->schema([
                        TextInput::make('name')
                            ->unique(modifyRuleUsing: function (Unique $rule) {
                                return $rule->where('user_id', Auth::user()->id);
                            }, ignoreRecord: true)
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->minLength(3)
                            ->regex('/^(?!.*__)(?!.*_$)[a-zA-Z0-9]+(?:_[a-zA-Z0-9]+)*$/', 'El nombre solo puede contener letras y números, sin espacios.')
                            ->placeholder('nombre_de_la_plantilla'),
                        Select::make('category_id')
                            ->label('Categoría')
                            ->options(fn() => \App\Models\WhatsAppTemplateCategory::all()->pluck('name', 'id'))
                            ->required(),
                        Select::make('language_code')
                            ->label('Idioma')
                            ->options([
                                'es' => 'Español',
                            ])
                            ->required()
                    ])->columns(3),
                Section::make('Configuración de encabezado')
                    ->schema([
                        TextInput::make('header')
                            ->label('Texto')
                            ->maxLength(60)
                            ->dehydrated(fn(?string $state): bool => !is_null($state))
                            ->live(onBlur: true)
                            ->columnSpanFull(),
                        Group::make(function (Get $get) {
                            $header = $get('header');
                            preg_match_all('/\{\{(\d+)\}\}/', $header, $matches);
                            $variables = $matches[1];

                            $inputs = [];

                            foreach ($variables as $variable) {
                                $inputs[] = TextInput::make('header_variables.' . $variable)
                                    ->label('Ejemplo de variable  ' . $variable)
                                    ->maxLength(60)
                                    ->required();
                            }

                            return $inputs;
                        })->columns(2)
                    ]),
                Section::make('Configuración del contenido')
                    ->schema([
                        Textarea::make('body')
                            ->label('Texto')
                            ->maxLength(1024)
                            ->dehydrated(fn(?string $state): bool => !is_null($state))
                            ->live(onBlur: true)
                            ->columnSpanFull()
                            ->rows(5),
                        Group::make(function (Get $get) {
                            $body = $get('body');
                            preg_match_all('/\{\{(\d+)\}\}/', $body, $matches);
                            $variables = $matches[1];

                            $inputs = [];

                            foreach ($variables as $variable) {
                                $inputs[] = TextInput::make('body_variables.' . $variable)
                                    ->label('Ejemplo de variable ' . $variable)
                                    ->maxLength(60)
                                    ->required();
                            }

                            return $inputs;
                        })->columns(2)
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        $table->modifyQueryUsing(function (Builder $query) {
            $query->with('category');
        });

        return $table
            ->columns([
                TextColumn::make('meta_template_id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('language_code')
                    ->label('Código de idioma')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->color(fn(ResourceStatusesEnum $state): string => match ($state->value) {
                        ResourceStatusesEnum::PENDING->value => 'warning',
                        ResourceStatusesEnum::APPROVED->value => 'success',
                        ResourceStatusesEnum::REJECTED->value => 'danger',
                    }),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->sortable()
                    ->dateTime()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->sortable()
                    ->dateTime()
                    ->searchable()
                    ->toggleable()
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
            'index' => Pages\ListWhatsAppTemplates::route('/'),
            'create' => Pages\CreateWhatsAppTemplate::route('/create'),
            'edit' => Pages\EditWhatsAppTemplate::route('/{record}/edit'),
        ];
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()->is_admin;
    }
}
