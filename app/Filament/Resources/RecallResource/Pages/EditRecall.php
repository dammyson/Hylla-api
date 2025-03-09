<?php

namespace App\Filament\Resources\RecallResource\Pages;

use App\Filament\Resources\RecallResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRecall extends EditRecord
{
    protected static string $resource = RecallResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
