<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatusCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification as Notification;

class NotificationController extends Controller
{
    /**
     * Index all user notifications.
     */
    public function userIndex()
    {
        return $this->sendResponse(Auth::user()->notifications()->get());
    }

    /**
     * User unread notifications.
     */
    public function userIndexUnread()
    {
        return $this->sendResponse(Auth::user()->unreadNotifications()->get());
    }

    /**
     * User read notifications.
     */
    public function userIndexRead()
    {
        return $this->sendResponse(Auth::user()->readNotifications()->get());
    }

    /**
     * Mark notification as read.
     */
    public function mark(Notification $notification)
    {
        $notification->markAsRead();
        return $this->sendResponse(null, ResponseStatusCode::OK, 'Notification marked as read');
    }

    /**
     * Mark all user notifications as read.
     */
    public function userMarkAll()
    {
        Auth::user()->unreadNotifications()->markAsRead();
        return $this->sendResponse(null, ResponseStatusCode::OK, 'All notifications marked as read');
    }

    /**
     * Delete notification.
     */
    public function destroy(Notification $notification)
    {
        $notification->delete();
        return $this->sendResponse(null, ResponseStatusCode::OK, 'Notification deleted successfully');
    }
}
