<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'target',
        'user_ids',
        'firebase_tokens',
    ];

    protected $casts = [
        'user_ids' => 'array',
        'firebase_tokens' => 'array', 
    ];

    /**
     * Get all users that received this notification.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'id', 'user_ids');
    }
}
