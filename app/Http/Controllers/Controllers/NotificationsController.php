<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationsController extends Controller
{
    /**
     * GET /api/notifications
     */
    public function index()
    {
        $userId = Auth::id();

        $notifications = DB::table('Notifications')
            ->select([
                'IdNotification',
                'IdUser',
                'Type',
                'Title',
                'Message',
                'Link',
                'IsRead',
                'CreatedAt',
                'IdReference'
            ])
            ->where('IdUser', $userId)
            ->orderByDesc('CreatedAt')
            ->limit(30)
            ->get();

        $items = $notifications->map(function ($n) {
            return [
                'id'         => $n->IdNotification,
                'type'       => $n->Type,
                'title'      => $n->Title,
                'message'    => $n->Message,
                'link'       => $n->Link,
                'is_read'    => (bool) $n->IsRead,
                'created_at' => $n->CreatedAt,
                'icon'       => self::getIcon($n->Type),
            ];
        });

        return response()->json([
            'notifications' => $items,
            'unread_count'  => $items->where('is_read', false)->count(),
        ]);
    }

    /**
     * PATCH /api/notifications/{id}/read
     */
    public function markRead($id)
    {
        $userId = Auth::id();

        DB::table('Notifications')
            ->where('IdNotification', $id)
            ->where('IdUser', $userId)
            ->update([
                'IsRead' => 1
            ]);

        return response()->json([
            'message' => 'Notification lue.'
        ]);
    }

    /**
     * PATCH /api/notifications/read-all
     */
    public function markAllRead()
    {
        $userId = Auth::id();

        DB::table('Notifications')
            ->where('IdUser', $userId)
            ->update([
                'IsRead' => 1
            ]);

        return response()->json([
            'message' => 'Toutes les notifications lues.'
        ]);
    }

    /**
     * Notification icon helper
     */
    private static function getIcon(?string $type): string
    {
        return match ($type) {
            'new_product'  => 'bx-store',
            'order_update' => 'bx-package',
            'follow'       => 'bx-user-plus',
            default        => 'bx-bell',
        };
    }

    /**
     * Static helper for other controllers
     */
    public static function createNotification(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?string $link = null,
        ?int $idRef = null
    ): void {
        try {

            DB::table('Notifications')->insert([
                'IdUser'      => $userId,
                'Type'        => $type,
                'Title'       => $title,
                'Message'     => $message,
                'Link'        => $link,
                'IsRead'      => 0,
                'CreatedAt'   => now(),
                'IdReference' => $idRef,
            ]);
        } catch (\Exception $e) {
            // Never fail parent operation
            Log::error('Notification creation failed: ' . $e->getMessage());
        }
    }
}
