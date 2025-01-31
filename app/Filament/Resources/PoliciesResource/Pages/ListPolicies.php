<?php

namespace App\Filament\Resources\PoliciesResource\Pages;

use App\Filament\Resources\PoliciesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPolicies extends ListRecords
{
    protected static string $resource = PoliciesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
