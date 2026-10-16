<?php

namespace App\Filament\Resources\ContactGroups\Pages;

use App\Filament\Resources\ContactGroups\ContactGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageContactGroups extends ManageRecords
{
    protected static string $resource = ContactGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
