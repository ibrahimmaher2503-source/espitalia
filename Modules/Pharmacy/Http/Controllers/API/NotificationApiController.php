<?php

namespace Modules\Pharmacy\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Models\PharmacyNotification;
use Modules\Pharmacy\Transformers\NotificationResource;

class NotificationApiController extends Controller
{
    /**
     * List notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $query = PharmacyNotification::where('user_id', auth()->id());

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('unread_only') && $request->boolean('unread_only')) {
            $query->unread();
        }

        $perPage = min((int) $request->get('per_page', 15), 50);
        $notifications = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => NotificationResource::collection($notifications),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'last_page' => $notifications->lastPage(),
            ],
        ]);
    }

    /**
     * Mark a single notification as read
     */
    public function markAsRead($id)
    {
        $notification = PharmacyNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'status' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        PharmacyNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'status' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount()
    {
        $count = PharmacyNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'status' => true,
            'data' => ['unread_count' => $count],
        ]);
    }
}
