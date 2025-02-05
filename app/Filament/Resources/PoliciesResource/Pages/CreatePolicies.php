<?php

namespace App\Filament\Resources\PoliciesResource\Pages;

use App\Filament\Resources\PoliciesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePolicies extends CreateRecord
{
    protected static string $resource = PoliciesResource::class;
 protected function mutateFormDataBeforeCreate(array $data): array
 {
     if (auth()->user()->role == 'manager') {
            $data['user_id'] = auth()->user()->id;
        }
        return $data;
 }

    protected function afterCreate(): void
    {
        if (auth()->user()->role == 'manager') {
            $record = $this->record;
            $record->departments()->sync(auth()->user()->department_id);
        }
    }

}
