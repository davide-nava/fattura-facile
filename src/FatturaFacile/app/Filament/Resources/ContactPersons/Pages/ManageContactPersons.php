<?php

namespace App\Filament\Resources\ContactPersons\Pages;

use App\Filament\Resources\ContactPersons\ContactPersonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageContactPersons extends ManageRecords
{
    protected static string $resource = ContactPersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
