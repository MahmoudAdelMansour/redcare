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
}
