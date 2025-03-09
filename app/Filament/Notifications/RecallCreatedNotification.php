<?php

namespace App\Filament\Notifications;

use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class RecallCreatedNotification extends Notification
{
    public static function fromRecall($recall): Notification
    {
        return Notification::make()
            ->title('New Recall Created')
            ->body("A new recall has been created for: **{$recall->product_name}**.")
            ->actions([
                Action::make('View')
                    ->url(route('filament.admin.resources.recalls.view', $recall)),
            ]);
    }
}
