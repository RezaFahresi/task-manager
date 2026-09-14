<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use App\Services\TaskNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [TaskController::class, 'dashboard'])->name('dashboard');
    Route::resource('tasks', TaskController::class);
    Route::resource('categories', CategoryController::class)->except(['show']);

    // Notification Center Routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.patch-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Serverless Cron Webhook Endpoints (Protected by CRON_SECRET)
Route::match(['get', 'post'], '/api/cron/smart-reminders', function (Request $request, TaskNotificationService $notificationService) {
    $expectedSecret = config('app.cron_secret', env('CRON_SECRET'));
    $providedSecret = $request->bearerToken() ?? $request->query('secret') ?? $request->header('x-cron-secret');

    if (empty($expectedSecret) || ! hash_equals((string) $expectedSecret, (string) $providedSecret)) {
        abort(403, 'Unauthorized cron execution.');
    }

    $sentCount = $notificationService->checkAndNotifyAll();

    return response()->json([
        'status' => 'success',
        'message' => 'Smart reminders processed successfully.',
        'dispatched_count' => $sentCount,
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('cron.smart-reminders');

Route::match(['get', 'post'], '/api/cron/schedule-run', function (Request $request) {
    $expectedSecret = config('app.cron_secret', env('CRON_SECRET'));
    $providedSecret = $request->bearerToken() ?? $request->query('secret') ?? $request->header('x-cron-secret');

    if (empty($expectedSecret) || ! hash_equals((string) $expectedSecret, (string) $providedSecret)) {
        abort(403, 'Unauthorized cron execution.');
    }

    Artisan::call('schedule:run');
    $output = Artisan::output();

    return response()->json([
        'status' => 'success',
        'message' => 'Scheduler executed successfully.',
        'output' => trim($output),
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('cron.schedule-run');
