<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Notifications\RecallNotification;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Filament\Notifications\RecallCreatedNotification;

class Recall extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['product_name', 'description', 'image_url', 'recall_description'];

    protected static function booted()
    {
        // static::created(function ($recall) {
        //     new RecallNotification($recall->product_name, $recall->recall_description);
        // });
        static::created(function ($recall) {
            Notification::make()
                ->title('New Recall Created')
                ->body("A new recall has been created: {$recall->product_name}")
                ->sendToDatabase(auth()->user()); // Or send to a specific user
        });

        // static::created(function ($recall) {
        //     $notification = RecallCreatedNotification::fromRecall($recall);
        //     $notification->sendToDatabase(auth()->user());
        // });
    }
}
