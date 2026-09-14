<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueSoonNotification;
use App\Notifications\TaskDueTodayNotification;
use App\Notifications\TaskOverdueNotification;

class TaskNotificationService
{
    /**
     * Check tasks for the given user and dispatch deadline notifications.
     */
    public function checkAndNotifyUser(User $user): int
    {
        $sentCount = 0;

        // Fetch today's notifications for fast, dialect-independent deduplication
        $todayNotifications = $user->notifications()
            ->whereDate('created_at', today())
            ->get();

        $alreadyNotified = function (int $taskId, string $type) use ($todayNotifications): bool {
            return $todayNotifications->contains(function ($notification) use ($taskId, $type) {
                $data = $notification->data;

                return is_array($data)
                    && isset($data['task_id'], $data['type'])
                    && (int) $data['task_id'] === $taskId
                    && $data['type'] === $type;
            });
        };

        // 1. Task jatuh tempo hari ini (pending)
        $dueTodayTasks = $user->tasks()
            ->where('status', 'pending')
            ->whereDate('due_date', today())
            ->get();

        foreach ($dueTodayTasks as $task) {
            if (! $alreadyNotified($task->id, 'due_today')) {
                $user->notify(new TaskDueTodayNotification($task));
                $sentCount++;
            }
        }

        // 2. Task menjadi overdue (pending)
        $overdueTasks = $user->tasks()
            ->where('status', 'pending')
            ->whereDate('due_date', '<', today())
            ->get();

        foreach ($overdueTasks as $task) {
            if (! $alreadyNotified($task->id, 'overdue')) {
                $user->notify(new TaskOverdueNotification($task));
                $sentCount++;
            }
        }

        // 3. Task akan jatuh tempo dalam waktu dekat (pending, 1-3 hari ke depan)
        $dueSoonTasks = $user->tasks()
            ->where('status', 'pending')
            ->whereDate('due_date', '>', today())
            ->whereDate('due_date', '<=', today()->addDays(3))
            ->get();

        foreach ($dueSoonTasks as $task) {
            if (! $alreadyNotified($task->id, 'due_soon')) {
                $user->notify(new TaskDueSoonNotification($task));
                $sentCount++;
            }
        }

        return $sentCount;
    }

    /**
     * Check tasks for all users and dispatch notifications.
     */
    public function checkAndNotifyAll(): int
    {
        $totalSent = 0;

        User::chunk(100, function ($users) use (&$totalSent) {
            foreach ($users as $user) {
                $totalSent += $this->checkAndNotifyUser($user);
            }
        });

        return $totalSent;
    }
}
