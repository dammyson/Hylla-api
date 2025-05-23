<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecallNotification extends Notification
{
    use Queueable;

    public $itemName;
    public $recallReason;

    /**
     * Create a new notification instance.
     *
     * @param string $itemName
     * @param string $recallReason
     */
    public function __construct($itemName, $recallReason)
    {
        $this->itemName = $itemName;
        $this->recallReason = $recallReason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable  ['database', 'mail', \App\Channels\FirebaseChannel::class];
     * @return array
     */
    public function via($notifiable)
    {
        return [ \App\Channels\FirebaseChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('item recalled notification')
                    ->line("The item '{$this->itemName}' has been recalled.")
                    ->line("Reason for recall: {$this->recallReason}")
                    ->action('View Details', url('/recalls'))
                    ->line('Please take necessary actions regarding this item.');
    }

     /**
     * Get the database representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'item_name' => $this->itemName,
            'recall_reason' => $this->recallReason,
            'action_url' => url('/recalls'),
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
            'body' => "The item '{$this->itemName}' has been recalled. Reason: {$this->recallReason}.",
            'data' => [
                'item_name' => $this->itemName,
                'recall_reason' => $this->recallReason,
                'action_url' => url('/recalls'),
            ],
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
