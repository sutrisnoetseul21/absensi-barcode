<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationInboxController extends Controller
{
    /**
     * Mengambil daftar riwayat notifikasi in-app untuk pengguna yang sedang login.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min($perPage, 100));

        $unreadCount = $user->unreadNotifications()->count();

        $paginator = $user->notifications()
            ->latest()
            ->paginate($perPage);

        $items = collect($paginator->items())->map(function ($notif) {
            $data = is_array($notif->data) ? $notif->data : (json_decode($notif->data, true) ?: []);

            return [
                'id'         => (string) $notif->id,
                'title'      => $data['title'] ?? 'Pemberitahuan',
                'body'       => $data['body'] ?? '',
                'type'         => $data['type'] ?? 'general',
                'target_id'    => (string) ($data['id'] ?? ''),
                'related_id'   => (string) ($data['id'] ?? ''),
                'event_status' => $data['event_status'] ?? null,
                'read_at'    => $notif->read_at ? $notif->read_at->toIso8601String() : null,
                'created_at' => $notif->created_at ? $notif->created_at->toIso8601String() : null,
                'meta'       => $data['meta'] ?? null,
            ];
        });

        return response()->json([
            'success'      => true,
            'unread_count' => $unreadCount,
            'data'         => $items,
            'pagination'   => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    /**
     * Menandai satu notifikasi tertentu sebagai telah dibaca.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan.',
            ], 404);
        }

        if ($notification->unread()) {
            $notification->markAsRead();
        }

        return response()->json([
            'success'      => true,
            'message'      => 'Notifikasi berhasil ditandai telah dibaca.',
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Menandai seluruh notifikasi yang belum dibaca sebagai telah dibaca.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->unreadNotifications->markAsRead();

        return response()->json([
            'success'      => true,
            'message'      => 'Semua notifikasi berhasil ditandai telah dibaca.',
            'unread_count' => 0,
        ]);
    }
}
