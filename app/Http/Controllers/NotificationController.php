<?php

namespace App\Http\Controllers;

use App\Services\TaskNotificationService;
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

    public function markAsRead(string $id): RedirectResponse
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

        return back()->with('success', 'Notifikasi telah ditandai sebagai dibaca.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

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

        $notification->delete();

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }
}
