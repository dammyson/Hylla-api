<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendPushNotification extends Notification
{
    use Queueable;

    public $title;
    public $message;

    public function __construct($title, $message)
    {
        $this->title = $title;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return [\App\Channels\FirebaseChannel::class];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
        ];
    }

       /**
     * Get the Firebase representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toFirebase($notifiable)
    {
        return [
            'title' => 'Item Recall Notification',
            'body' => "The item ",
            'data' => [
                'item_name' => "Data",
                'recall_reason' => "Data",
                'action_url' => url('/recalls'),
            ],
        ];
    }
}
