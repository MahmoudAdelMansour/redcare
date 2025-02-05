<?php

namespace App\Filament\Resources\PoliciesResource\Pages;

use App\Filament\Resources\PoliciesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPolicies extends EditRecord
{
    protected static string $resource = PoliciesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (auth()->user()->role == 'manager') {
            $data['user_id'] = auth()->user()->id;
        }
        return $data;
    }

    protected function afterSave(): void
    {
        if (auth()->user()->role == 'manager') {
            $record = $this->record;
            $record->departments()->sync(auth()->user()->department_id);
        }
    }

}
