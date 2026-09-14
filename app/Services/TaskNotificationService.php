<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDeadlineOneHourNotification;
use App\Notifications\TaskDeadlineTenMinutesNotification;
use App\Notifications\TaskDueSoonNotification;
use App\Notifications\TaskDueTodayNotification;
use App\Notifications\TaskOverdueNotification;
use Carbon\Carbon;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TaskNotificationService
{
    /**
     * Check tasks for the given user and dispatch deadline notifications.
     */
    public function checkAndNotifyUser(User $user): int
    {
        $today = today()->toDateString();
        $threeDaysLater = today()->addDays(3)->toDateString();

        $userTasks = $user->tasks()
            ->where('status', 'pending')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', $threeDaysLater)
            ->get();

        if ($userTasks->isEmpty()) {
            return 0;
        }

        $userNotifications = $user->notifications()->get();

        return $this->processTasksForUser(
            $user,
            $userTasks,
            $userNotifications,
            $today,
            $threeDaysLater
        );
    }

    /**
     * Check tasks for all users and dispatch notifications using bulk queries (O(1) queries instead of O(N)).
     */
    public function checkAndNotifyAll(): int
    {
        $totalSent = 0;
        $today = today()->toDateString();
        $threeDaysLater = today()->addDays(3)->toDateString();

        // 1. Bulk query all candidate pending tasks with their users in a single query
        $tasks = Task::with('user')
            ->where('status', 'pending')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', $threeDaysLater)
            ->get();

        if ($tasks->isEmpty()) {
            return 0;
        }

        // Group tasks by user_id
        $tasksByUser = $tasks->groupBy('user_id');
        $userIds = $tasksByUser->keys()->all();

        // 2. Bulk query all relevant notifications for all these users in a single query
        $notificationsByUser = DatabaseNotification::whereIn('notifiable_id', $userIds)
            ->where('notifiable_type', User::class)
            ->get()
            ->groupBy('notifiable_id');

        // 3. Process tasks for each user in memory without per-user database queries
        foreach ($tasksByUser as $userId => $userTasks) {
            $user = $userTasks->first()->user;
            if (! $user) {
                continue;
            }

            $userNotifications = $notificationsByUser->get($userId, collect());
            $totalSent += $this->processTasksForUser(
                $user,
                $userTasks,
                $userNotifications,
                $today,
                $threeDaysLater
            );
        }

        return $totalSent;
    }

    /**
     * Process deadline checks for a specific user using pre-fetched collections.
     *
     * @param  Collection<int, Task>  $userTasks
     * @param  Collection<int, DatabaseNotification>  $userNotifications
     */
    public function processTasksForUser(
        User $user,
        Collection $userTasks,
        Collection $userNotifications,
        string $today,
        string $threeDaysLater
    ): int {
        $sentCount = 0;
        $now = now();

        // Pre-filter today's notifications for due_today and due_soon deduplication
        $todayNotifications = $userNotifications->filter(function ($notification) use ($today) {
            return $notification->created_at && $notification->created_at->toDateString() === $today;
        });

        // 1. Task jatuh tempo hari ini (pending) - Daily morning alert
        $dueTodayTasks = $userTasks->filter(function ($task) use ($today, $now) {
            if (! $task->due_date || $task->due_date->toDateString() !== $today) {
                return false;
            }

            // If task has explicit time and is within the smart reminder window (<= 60m) or overdue, let smart reminders handle it
            if ($task->due_date->format('H:i:s') !== '00:00:00') {
                $diff = $now->diffInMinutes($task->due_date, false);
                if ($diff <= 60) {
                    return false;
                }
            }

            return true;
        });

        foreach ($dueTodayTasks as $task) {
            $dueDate = $task->due_date?->toDateString();
            if (! $this->isAlreadyNotifiedToday($user, $task->id, 'due_today', $dueDate, $todayNotifications)) {
                if ($this->notifySafely($user, new TaskDueTodayNotification($task), $task, 'due_today')) {
                    $sentCount++;
                }
            }
        }

        // 2. Task akan jatuh tempo dalam waktu dekat (pending, 1-3 hari ke depan)
        $dueSoonTasks = $userTasks->filter(function ($task) use ($today, $threeDaysLater) {
            $taskDate = $task->due_date?->toDateString();

            return $taskDate && $taskDate > $today && $taskDate <= $threeDaysLater;
        });

        foreach ($dueSoonTasks as $task) {
            $dueDate = $task->due_date?->toDateString();
            if (! $this->isAlreadyNotifiedToday($user, $task->id, 'due_soon', $dueDate, $todayNotifications)) {
                if ($this->notifySafely($user, new TaskDueSoonNotification($task), $task, 'due_soon')) {
                    $sentCount++;
                }
            }
        }

        // 3. SMART DEADLINE REMINDERS: Overdue, 10 Menit, 1 Jam
        foreach ($userTasks as $task) {
            if ($task->status !== 'pending' || ! $task->due_date) {
                continue;
            }

            $deadline = $this->getTaskDeadlineCarbon($task);
            if (! $deadline) {
                continue;
            }

            $deadlineKey = $task->due_date->format('Y-m-d H:i:s');
            $dateOnlyKey = $task->due_date->toDateString();

            // A. Overdue Check: when current time has passed the deadline
            if ($now->greaterThan($deadline)) {
                if (! $this->isOverdueAlreadyNotified($user, $task->id, $dateOnlyKey, $userNotifications)
                    && ! $this->isOverdueAlreadyNotified($user, $task->id, $deadlineKey, $userNotifications)) {
                    if ($this->notifySafely($user, new TaskOverdueNotification($task), $task, 'overdue')) {
                        $sentCount++;
                    }
                }

                continue; // Overdue tasks do not receive 1h or 10m reminders
            }

            // Remaining time to deadline in minutes
            $diffInMinutes = $now->diffInMinutes($deadline, false);

            // B. 10 Menit Sebelum Deadline (0 <= diff <= 10 minutes)
            if ($diffInMinutes <= 10 && $diffInMinutes >= 0) {
                if (! $this->isReminderAlreadyNotified($user, $task->id, 'reminder_10m', $deadlineKey, $userNotifications)
                    && ! $this->isReminderAlreadyNotified($user, $task->id, 'reminder_10m', $dateOnlyKey, $userNotifications)) {
                    if ($this->notifySafely($user, new TaskDeadlineTenMinutesNotification($task), $task, 'reminder_10m')) {
                        $sentCount++;
                    }
                }

                continue; // In the 10m window, do not dispatch 1h reminder
            }

            // C. 1 Jam Sebelum Deadline (10 < diff <= 60 minutes)
            if ($diffInMinutes <= 60 && $diffInMinutes > 10) {
                if (! $this->isReminderAlreadyNotified($user, $task->id, 'reminder_1h', $deadlineKey, $userNotifications)
                    && ! $this->isReminderAlreadyNotified($user, $task->id, 'reminder_1h', $dateOnlyKey, $userNotifications)) {
                    if ($this->notifySafely($user, new TaskDeadlineOneHourNotification($task), $task, 'reminder_1h')) {
                        $sentCount++;
                    }
                }
            }
        }

        return $sentCount;
    }

    /**
     * Get exact target Carbon deadline for a task.
     */
    public function getTaskDeadlineCarbon(Task $task): ?Carbon
    {
        if (! $task->due_date) {
            return null;
        }

        $dueDate = $task->due_date;
        if (! $dueDate instanceof Carbon) {
            $dueDate = Carbon::parse($dueDate);
        }

        // If time was specified (e.g. not 00:00:00), use that exact datetime
        if ($dueDate->format('H:i:s') !== '00:00:00') {
            return $dueDate->copy();
        }

        // If only a date was entered (00:00:00), deadline is end of day (23:59:59)
        return $dueDate->copy()->endOfDay();
    }

    /**
     * Check if user was already notified today for a given task and type (for due_today and due_soon).
     *
     * @param  Collection<int, DatabaseNotification>  $todayNotifications
     */
    protected function isAlreadyNotifiedToday(
        User $user,
        int $taskId,
        string $type,
        ?string $dueDate,
        Collection $todayNotifications
    ): bool {
        if ($this->isDismissed($user->id, $taskId, $type, $dueDate)) {
            return true;
        }

        return $todayNotifications->contains(function ($notification) use ($taskId, $type) {
            $data = $notification->data;

            return is_array($data)
                && isset($data['task_id'], $data['type'])
                && (int) $data['task_id'] === $taskId
                && $data['type'] === $type;
        });
    }

    /**
     * Check if user already has an active reminder notification for this task and deadline (1h or 10m).
     *
     * @param  Collection<int, DatabaseNotification>  $allUserNotifications
     */
    public function isReminderAlreadyNotified(
        User $user,
        int $taskId,
        string $type,
        string $deadlineKey,
        Collection $allUserNotifications
    ): bool {
        if ($this->isDismissed($user->id, $taskId, $type, $deadlineKey)) {
            return true;
        }

        return $allUserNotifications->contains(function ($notification) use ($taskId, $type, $deadlineKey) {
            $data = $notification->data;

            return is_array($data)
                && isset($data['task_id'], $data['type'])
                && (int) $data['task_id'] === $taskId
                && $data['type'] === $type
                && ($data['due_date'] ?? null) === $deadlineKey;
        });
    }

    /**
     * Check if user already has an active overdue notification for this task and due date (BUG #7).
     *
     * @param  Collection<int, DatabaseNotification>  $allUserNotifications
     */
    protected function isOverdueAlreadyNotified(
        User $user,
        int $taskId,
        ?string $dueDate,
        Collection $allUserNotifications
    ): bool {
        if ($this->isDismissed($user->id, $taskId, 'overdue', $dueDate)) {
            return true;
        }

        return $allUserNotifications->contains(function ($notification) use ($taskId, $dueDate) {
            $data = $notification->data;

            return is_array($data)
                && isset($data['task_id'], $data['type'])
                && (int) $data['task_id'] === $taskId
                && $data['type'] === 'overdue'
                && ($data['due_date'] ?? null) === $dueDate;
        });
    }

    /**
     * Mark a task deadline notification as dismissed by the user.
     */
    public function markAsDismissed(int $userId, int $taskId, string $type, ?string $dueDate = null): void
    {
        $key = $this->getDismissedCacheKey($userId, $taskId, $type, $dueDate);
        Cache::put($key, true, now()->addDays(30));
    }

    /**
     * Check if a task deadline notification was dismissed by the user.
     */
    public function isDismissed(int $userId, int $taskId, string $type, ?string $dueDate = null): bool
    {
        $key = $this->getDismissedCacheKey($userId, $taskId, $type, $dueDate);

        return Cache::has($key);
    }

    /**
     * Generate a cache key for dismissed notifications.
     */
    protected function getDismissedCacheKey(int $userId, int $taskId, string $type, ?string $dueDate = null): string
    {
        $date = $dueDate ?? 'none';

        return "dismissed_notification:{$userId}:{$taskId}:{$type}:{$date}";
    }

    /**
     * Safely dispatch notification to user, ensuring broadcasting or network errors do not crash web requests.
     */
    protected function notifySafely(User $user, mixed $notification, Task $task, string $type): bool
    {
        try {
            $user->notify($notification);

            return true;
        } catch (\Throwable $e) {
            Log::warning("Notification dispatch encountered an error for User #{$user->id}, Task #{$task->id} ({$type}): {$e->getMessage()}");

            return true;
        }
    }
}
