<?php

namespace App\Filament\Resources\RecallResource\Pages;

use App\Filament\Resources\RecallResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRecalls extends ListRecords
{
    protected static string $resource = RecallResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
