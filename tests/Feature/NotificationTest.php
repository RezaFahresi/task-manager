<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueSoonNotification;
use App\Notifications\TaskDueTodayNotification;
use App\Notifications\TaskOverdueNotification;
use App\Services\TaskNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_notifications_and_is_redirected_to_login(): void
    {
        $response = $this->get('/notifications');
        $response->assertRedirect('/login');

        $responsePost = $this->post('/notifications/read-all');
        $responsePost->assertRedirect('/login');
    }

    public function test_notifications_are_created_for_due_today_overdue_and_upcoming_tasks(): void
    {
        $user = User::factory()->create();

        // 1. Task jatuh tempo hari ini
        $taskToday = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Deadline Hari Ini',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        // 2. Task terlambat (overdue)
        $taskOverdue = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Deadline Terlambat',
            'status' => 'pending',
            'due_date' => Carbon::yesterday()->toDateString(),
        ]);

        // 3. Task akan jatuh tempo dalam waktu dekat (besok)
        $taskDueSoon = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Deadline Besok',
            'status' => 'pending',
            'due_date' => Carbon::tomorrow()->toDateString(),
        ]);

        // 4. Task yang sudah selesai tidak boleh dibuatkan notifikasi
        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Selesai Hari Ini',
            'status' => 'completed',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        $service = app(TaskNotificationService::class);
        $sentCount = $service->checkAndNotifyUser($user);

        $this->assertSame(3, $sentCount);
        $this->assertSame(3, $user->notifications()->count());
        $this->assertSame(3, $user->unreadNotifications()->count());

        // Verifikasi tipe notifikasi di database
        $types = $user->notifications->pluck('type')->toArray();
        $this->assertContains(TaskDueTodayNotification::class, $types);
        $this->assertContains(TaskOverdueNotification::class, $types);
        $this->assertContains(TaskDueSoonNotification::class, $types);

        // Verifikasi payload data
        $todayNotification = $user->notifications->firstWhere('type', TaskDueTodayNotification::class);
        $this->assertSame($taskToday->id, $todayNotification->data['task_id']);
        $this->assertSame('due_today', $todayNotification->data['type']);

        $overdueNotification = $user->notifications->firstWhere('type', TaskOverdueNotification::class);
        $this->assertSame($taskOverdue->id, $overdueNotification->data['task_id']);
        $this->assertSame('overdue', $overdueNotification->data['type']);

        $dueSoonNotification = $user->notifications->firstWhere('type', TaskDueSoonNotification::class);
        $this->assertSame($taskDueSoon->id, $dueSoonNotification->data['task_id']);
        $this->assertSame('due_soon', $dueSoonNotification->data['type']);
    }

    public function test_notification_creation_is_idempotent_and_does_not_duplicate_on_same_day(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Hari Ini',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        $service = app(TaskNotificationService::class);

        $sentFirst = $service->checkAndNotifyUser($user);
        $this->assertSame(1, $sentFirst);
        $this->assertSame(1, $user->notifications()->count());

        // Jalankan kedua kali di hari yang sama
        $sentSecond = $service->checkAndNotifyUser($user);
        $this->assertSame(0, $sentSecond);
        $this->assertSame(1, $user->fresh()->notifications()->count());
    }

    public function test_notification_center_renders_list_with_unread_count_and_task_links(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Penting',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        $user->notify(new TaskDueTodayNotification($task));

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertOk();
        $response->assertSee('Notification Center');
        $response->assertSee('Task Penting');
        $response->assertSee('Task Jatuh Tempo Hari Ini');
        $response->assertSee(route('tasks.show', $task));
        $response->assertSee('Tandai dibaca');
        $response->assertSee('Tandai Semua Dibaca');
    }

    public function test_user_can_mark_single_notification_as_read(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Uji Read',
            'status' => 'pending',
        ]);

        $user->notify(new TaskDueTodayNotification($task));
        $notification = $user->unreadNotifications()->first();

        $this->assertNotNull($notification);
        $this->assertNull($notification->read_at);

        $response = $this->actingAs($user)->post("/notifications/{$notification->id}/read");
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();

        $task1 = Task::create(['user_id' => $user->id, 'title' => 'Task 1', 'status' => 'pending']);
        $task2 = Task::create(['user_id' => $user->id, 'title' => 'Task 2', 'status' => 'pending']);

        $user->notify(new TaskDueTodayNotification($task1));
        $user->notify(new TaskOverdueNotification($task2));

        $this->assertSame(2, $user->unreadNotifications()->count());

        $response = $this->actingAs($user)->post('/notifications/read-all');
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
        $this->assertSame(2, $user->fresh()->notifications()->count());
    }

    public function test_user_isolation_user_a_cannot_see_user_b_notifications(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $taskA = Task::create(['user_id' => $userA->id, 'title' => 'Task Rahasia User A', 'status' => 'pending']);
        $taskB = Task::create(['user_id' => $userB->id, 'title' => 'Task Rahasia User B', 'status' => 'pending']);

        $userA->notify(new TaskDueTodayNotification($taskA));
        $userB->notify(new TaskDueTodayNotification($taskB));

        $responseA = $this->actingAs($userA)->get('/notifications');
        $responseA->assertOk();
        $responseA->assertSee('Task Rahasia User A');
        $responseA->assertDontSee('Task Rahasia User B');

        $responseB = $this->actingAs($userB)->get('/notifications');
        $responseB->assertOk();
        $responseB->assertSee('Task Rahasia User B');
        $responseB->assertDontSee('Task Rahasia User A');
    }

    public function test_task_authorization_user_a_cannot_mark_user_b_notification_as_read(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $taskB = Task::create(['user_id' => $userB->id, 'title' => 'Task User B', 'status' => 'pending']);
        $userB->notify(new TaskDueTodayNotification($taskB));

        $notificationB = $userB->notifications()->first();

        // User A mencoba menandai notifikasi milik User B
        $response = $this->actingAs($userA)->post("/notifications/{$notificationB->id}/read");
        $response->assertStatus(403);

        $this->assertNull($notificationB->fresh()->read_at);
    }

    public function test_task_authorization_user_a_cannot_delete_user_b_notification(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $taskB = Task::create(['user_id' => $userB->id, 'title' => 'Task User B', 'status' => 'pending']);
        $userB->notify(new TaskDueTodayNotification($taskB));

        $notificationB = $userB->notifications()->first();

        // User A mencoba menghapus notifikasi milik User B
        $response = $this->actingAs($userA)->delete("/notifications/{$notificationB->id}");
        $response->assertStatus(403);

        $this->assertDatabaseHas('notifications', [
            'id' => $notificationB->id,
        ]);
    }

    public function test_filter_unread_only_displays_unread_notifications(): void
    {
        $user = User::factory()->create();

        $task1 = Task::create(['user_id' => $user->id, 'title' => 'Task Unread Test', 'status' => 'pending']);
        $task2 = Task::create(['user_id' => $user->id, 'title' => 'Task Read Test', 'status' => 'pending']);

        $user->notify(new TaskDueTodayNotification($task1));
        $user->notify(new TaskDueTodayNotification($task2));

        // Mark task2 notification as read specifically
        $notif2 = $user->notifications->first(fn ($n) => ($n->data['task_id'] ?? null) == $task2->id);
        $notif2->markAsRead();

        $response = $this->actingAs($user)->get('/notifications?filter=unread');
        $response->assertOk();
        $response->assertSee('Task Unread Test');
        $response->assertDontSee('Task Read Test');
    }

    public function test_artisan_command_checks_deadlines_and_dispatches_notifications(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Artisan Hari Ini',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        $this->artisan('tasks:check-deadlines')
            ->expectsOutput('Checking task deadlines...')
            ->assertSuccessful();

        $this->assertSame(1, $user->notifications()->count());
    }

    public function test_tasks_check_deadlines_command_is_registered_in_schedule_to_run_daily(): void
    {
        $schedule = app(Schedule::class);
        $events = collect($schedule->events());

        $deadlineEvent = $events->first(function ($event) {
            return str_contains($event->command, 'tasks:check-deadlines');
        });

        $this->assertNotNull($deadlineEvent, 'Command tasks:check-deadlines tidak terdaftar di scheduler.');
        $this->assertSame('0 0 * * *', $deadlineEvent->expression, 'Command tidak dijadwalkan secara daily (0 0 * * *).');
    }

    public function test_completed_tasks_are_never_notified_even_if_due_dates_match(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Selesai Hari Ini',
            'status' => 'completed',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Selesai Overdue',
            'status' => 'completed',
            'due_date' => Carbon::yesterday()->toDateString(),
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Selesai Besok',
            'status' => 'completed',
            'due_date' => Carbon::tomorrow()->toDateString(),
        ]);

        $this->artisan('tasks:check-deadlines')->assertSuccessful();

        $this->assertSame(0, $user->notifications()->count());
    }

    public function test_command_is_idempotent_across_multiple_invocations(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Idempotent Test',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        // Run 3 times consecutively
        $this->artisan('tasks:check-deadlines')->assertSuccessful();
        $this->artisan('tasks:check-deadlines')->assertSuccessful();
        $this->artisan('tasks:check-deadlines')->assertSuccessful();

        $this->assertSame(1, $user->notifications()->count());
    }
}
