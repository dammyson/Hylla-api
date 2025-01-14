<?php

namespace App\Channels;

use App\Services\Utilities\FCMService;
use GuzzleHttp\Client as HttpClient;

class FirebaseChannel
{
    protected $messaging;

    private $fcmUrl = "https://fcm.googleapis.com/v1/projects/hyllamobile/messages:send"; // Replace with your Firebase project ID.
   
    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return void
     */
    public function send($notifiable, $notification)
    {
        $title= "FDA Recall ".  $notification->itemName;
        $message = "The item '{$notification->itemName}' has been recalled. Reason:".  $notification->recallReason;
        if (method_exists($notification, 'toFirebase')) {
            $this->sendNotification($notifiable,$title,$message);
        }
    }

    public function sendNotification($notifiable, string $title, string $body, array $data = [])
    {
        $fcmService = new FCMService();
        $accessToken = $fcmService->getValidAccessToken();
     
        try {
            $httpClient = new HttpClient();
            $response = $httpClient->post($this->fcmUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'message' => [
                        'token' => $notifiable->firebase_token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => [
                             "feature"=> "New Release",
                             "url"=> "https://hylla.com/updates"
                        ],
                    ],
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            throw new \Exception('Failed to send notification: ' . $e->getMessage());
        }
    }
}
