<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPushNotification extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'push_notification_id',
        'is_read',
    ];

    public function pushNotification()
    {
        return $this->belongsTo(PushNotification::class);
    }
}
