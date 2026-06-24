<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get notifications for the authenticated admin user.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $limit = $request->input('limit', 20);

        $notifications = Notification::forUser($userId)
            ->notDeleted()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => (string) $notification->_id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'metadata' => $notification->metadata,
                    'is_read' => $notification->is_read,
                    'link' => $notification->getLink(),
                    'time_ago' => $notification->getTimeAgo(),
                    'created_at' => $notification->created_at?->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Get the count of unread notifications.
     */
    public function unreadCount()
    {
        $userId = Auth::id();

        $count = Notification::forUser($userId)
            ->notDeleted()
            ->unread()
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, string $id)
    {
        $userId = Auth::id();

        $notification = Notification::where('_id', new \MongoDB\BSON\ObjectId($id))
            ->forUser($userId)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        $notification->is_read = true;
        $notification->read_at = now();
        $notification->save();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        $userId = Auth::id();

        $count = Notification::forUser($userId)
            ->notDeleted()
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'count' => $count,
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(string $id)
    {
        $userId = Auth::id();

        $notification = Notification::where('_id', new \MongoDB\BSON\ObjectId($id))
            ->forUser($userId)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        // Soft delete
        $notification->deleted_at = now();
        $notification->save();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }
}
