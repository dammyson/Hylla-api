<?php

namespace App\Filament\Resources\PushNotificationResource\Pages;

use App\Filament\Resources\PushNotificationResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePushNotification extends CreateRecord
{
    protected static string $resource = PushNotificationResource::class;


    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }


    protected function handleRecordCreation(array $data): Model
    {
        try {
            $notification = parent::handleRecordCreation($data);

            if ($data['target'] === 'all') {
                $notification->firebase_tokens = User::pluck('firebase_token')->toArray();
            }
    
            if ($data['target'] === 'specific') {
                $notification->firebase_tokens = User::whereIn('id', $data['user_ids'])->pluck('firebase_token')->toArray();
            }

            if ($data['target'] === 'everyone') {
                $notification->firebase_tokens = [];
            }
    
            $notification->save();
           
        } catch (\Exception $e) {
            throw $e;
        }
        return $notification;
    }
}
