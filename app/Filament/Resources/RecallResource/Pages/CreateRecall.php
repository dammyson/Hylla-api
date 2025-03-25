<?php

namespace App\Filament\Resources\RecallResource\Pages;

use App\Filament\Resources\RecallResource;
use Filament\Actions;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Notifications\RecallNotification;
use Illuminate\Support\Facades\Notification;
use App\Channels\FirebaseChannel;

class CreateRecall extends CreateRecord
{
    protected static string $resource = RecallResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            // Create the recall record
            $recall = parent::handleRecordCreation($data);
            
            $users = User::all();
            
            Notification::send($users, new RecallNotification($recall->product_name, $recall->recall_description));
            $firebaseChannel = new FirebaseChannel();
            $res = $firebaseChannel->sendNotificationTopic('everyone', $recall->product_name, $recall->recall_description);
            
            return $recall;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
