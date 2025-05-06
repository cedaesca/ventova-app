<?php

namespace App\Filament\Resources;

use App\Enums\ResourceStatusesEnum;
use App\Filament\Resources\WhatsAppTemplateResource\Pages;
use App\Filament\Resources\WhatsAppTemplateResource\RelationManagers;
use App\Interfaces\Services\WhatsAppCloudServiceInterface;
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
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Unique;

class WhatsAppTemplateResource extends VentovaResource
{
    protected static ?string $model = WhatsAppTemplate::class;

    protected static ?string $navigationIcon = 'icomoon-insert-template';
    protected static ?string $navigationLabel = 'Plantillas';
    protected static ?string $navigationGroup = 'Envíos masivos';
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
                Action::make('Testear')
                    ->modal()
                    ->modalHeading('Testear plantilla')
                    ->form(function (Form $form, WhatsAppTemplate $record) {
                        $wsCloudService = app()->get(WhatsAppCloudServiceInterface::class);

                        /** @var array  */
                        $metaTemplate = Cache::remember($record->meta_template_id, 3600, function () use ($wsCloudService, $record) {
                            return $wsCloudService->getTemplate($record->name, $record->meta_template_id)['data'][0];
                        });

                        $templateComponents = $metaTemplate['components'];

                        $header = array_find($templateComponents, fn($c) => $c['type'] === 'HEADER');
                        $body = array_find($templateComponents, fn($c) => $c['type'] === 'BODY');

                        $headerVariableInput = null;
                        $bodyVariableInputs = [];

                        if (isset($header['example'])) {
                            $headerVariableInput = TextInput::make('header_variable')
                                ->label('Variable de encabezado')
                                ->maxLength(60)
                                ->required()
                                ->placeholder($header['example']['header_text'][0]);
                        }

                        foreach ($body['example']['body_text'][0] as $key => $value) {
                            $bodyVariableInputs[] = TextInput::make('body_variables.' . $key + 1)
                                ->label('Cuerpo - Valor de variable ' . $key + 1)
                                ->maxLength(60)
                                ->required()
                                ->placeholder($value);
                        }

                        return $form->schema([
                            Select::make('sender_phone_number')
                                ->label('Número de teléfono del remitente')
                                ->options(['+15551613873' => '+15551613873 (Teléfono de prueba)'])
                                ->required(),
                            Select::make('recipient_phone_number')
                                ->label('Número de teléfono del destinatario')
                                ->hint('Solo se muestran números autorizados')
                                ->options(['584143573254' => '+584143573254'])
                                ->required(),
                            TextInput::make('header')
                                ->label('Encabezado')
                                ->readOnly()
                                ->columnSpanFull()
                                ->default($header['text']),
                            $headerVariableInput,
                            Textarea::make('body')
                                ->label('Cuerpo')
                                ->readOnly()
                                ->columnSpanFull()
                                ->default($body['text']),
                            ...$bodyVariableInputs,
                        ]);
                    })
                    ->action(function (array $data, WhatsAppTemplate $record) {
                        $wsCloudService = app()->get(WhatsAppCloudServiceInterface::class);

                        $response = $wsCloudService->sendTemplateMessage(
                            $data['recipient_phone_number'],
                            $record->name,
                            $record->language_code,
                            $data['body_variables'] ?? [],
                            $data['header_variable'] ?? null,
                        );

                        Log::debug('WhatsAppTemplateResource:Test', [
                            'response' => $response
                        ]);
                    })
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
