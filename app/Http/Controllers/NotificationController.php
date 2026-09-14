<?php

namespace App\Http\Controllers;

use App\Services\TaskNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(protected TaskNotificationService $notificationService) {}

    public function index(Request $request): View
    {
        $user = Auth::user();

        // Check for any newly triggered deadline notifications for this user
        $this->notificationService->checkAndNotifyUser($user);

        $filter = $request->query('filter', 'all');

        $notificationsQuery = $user->notifications();

        if ($filter === 'unread' || $filter === 'belum_dibaca') {
            $notificationsQuery->whereNull('read_at');
        }

        $notifications = $notificationsQuery->paginate(10)->withQueryString();
        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount', 'filter'));
    }

    public function markAsRead(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            $existsOther = DatabaseNotification::where('id', $id)->exists();
            if ($existsOther) {
                abort(403, 'Aksi tidak diizinkan.');
            }

            abort(404, 'Notifikasi tidak ditemukan.');
        }

        $notification->markAsRead();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => $user->unreadNotifications()->count(),
            ]);
        }

        return back()->with('success', 'Notifikasi telah ditandai sebagai dibaca.');
    }

    public function markAllAsRead(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            $existsOther = DatabaseNotification::where('id', $id)->exists();
            if ($existsOther) {
                abort(403, 'Aksi tidak diizinkan.');
            }

            abort(404, 'Notifikasi tidak ditemukan.');
        }

        $data = $notification->data;
        if (is_array($data) && isset($data['task_id'], $data['type'])) {
            $this->notificationService->markAsDismissed(
                userId: $user->id,
                taskId: (int) $data['task_id'],
                type: (string) $data['type'],
                dueDate: isset($data['due_date']) ? (string) $data['due_date'] : null
            );
        }

        $notification->delete();

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }
}
