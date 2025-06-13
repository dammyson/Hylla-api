<?php

namespace App\Http\Controllers\Api;


use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\PushNotification;
use App\Http\Controllers\Controller;
use App\Models\UserPushNotification;
use Illuminate\Support\Facades\Auth;
use App\Services\Utilities\FCMService;
use App\Notifications\ItemRecalledNotification;

class NotificationController extends Controller
{
    public function listUserNotifications(Request $request)
    {
        $notifications = $request->user()->notifications; 

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
        ]);
    }


    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function test(){

        $user = auth()->user();
         $itemName = 'Widget 3000';
         $recallReason = 'Safety concerns related to overheating.';

         $user->notify(new ItemRecalledNotification($itemName, $recallReason));
    }

    public function listPushNotification() {
        $user = Auth::user(); // get logged in user


        $userNotifications = UserPushNotification::with('pushNotification')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $unreadCount = $userNotifications->where('is_read', false)->count();

        $everyoneNotifications = PushNotification::where('target', 'everyone')
            ->get()
            ->map(function ($push) {
                return [
                    'id' => $push->id,
                    'title' => $push->title,
                    'message' => $push->message,
                    'is_read' => true, // Assume read because it's a broadcast
                    'sent_at' => $push->created_at,
                    'time_ago' => Carbon::parse($push->created_at)->diffForHumans(),
                ];
            });

        $userNotifications = collect($userNotifications);
        $everyoneNotifications = collect($everyoneNotifications);

    // Merge both collections
    $allNotifications = $userNotifications->merge($everyoneNotifications)
        ->sortByDesc('sent_at')
        ->values(); // Reset indexes


        // $data = $userNotifications->map(function ($userNotification) {
        //     $push = $userNotification->pushNotification;
            
        //     return [
        //         'id' => $push->id,
        //         'title' => $push->title,
        //         'message' => $push->message,
        //         'is_read' => $userNotification->is_read,
        //         'sent_at' => $push->created_at->format('Y-m-d H:i:s'),
        //         'time_ago' => Carbon::parse($push->created_at)->diffForHumans(),
        //     ];
        // });

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            // 'notifications' => $data,
             'notifications' => $allNotifications,
        ]);

    }

    public function markNotificationAsRead($id)
    {
        $user = Auth::user();

        $userNotification = UserPushNotification::where('user_id', $user->id)
            ->where('push_notification_id', $id)
            ->first();

        if (!$userNotification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $userNotification->update(['is_read' => true]);

        return response()->json(['success' => true, 'message' => 'Notification marked as read']);
    }

}
