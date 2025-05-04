<?php

namespace App\Filament\Resources\RecipientResource\Pages;

use App\Filament\Resources\RecipientResource;
use App\Imports\RecipientsImport;
use App\Jobs\ProcessRecipientExcel;
use App\Models\RecipientGroup;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ImportRecipients extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected static string $resource = RecipientResource::class;

    protected static string $view = 'filament.resources.recipient-resource.pages.import-recipients';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
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
                    ->extraAttributes(['class' => 'mb-4'])
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $this->form->getState();
        $recipientGroupId = (int)$this->data['recipient_group_id'];
        $filePath = array_values($this->data['file'])[0];

        ProcessRecipientExcel::dispatch($filePath, $recipientGroupId);

        Notification::make()
            ->title('El archivo se está procesando. Recibirás una notificación cuando termine.')
            ->success()
            ->send();

        $this->redirect(RecipientResource::getUrl('index'));
    }
}
