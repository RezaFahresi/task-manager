<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueSoonNotification;
use App\Notifications\TaskDueTodayNotification;
use App\Notifications\TaskOverdueNotification;
use App\Services\TaskNotificationService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_classes_implement_should_queue_and_define_sync_for_db_and_broadcast(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Sample Task',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        $dueToday = new TaskDueTodayNotification($task);
        $overdue = new TaskOverdueNotification($task);
        $dueSoon = new TaskDueSoonNotification($task);

        $this->assertInstanceOf(ShouldQueue::class, $dueToday);
        $this->assertInstanceOf(ShouldQueue::class, $overdue);
        $this->assertInstanceOf(ShouldQueue::class, $dueSoon);

        $expectedConnections = [
            'database' => 'sync',
            'broadcast' => 'sync',
        ];

        $this->assertSame($expectedConnections, $dueToday->viaConnections());
        $this->assertSame($expectedConnections, $overdue->viaConnections());
        $this->assertSame($expectedConnections, $dueSoon->viaConnections());
    }

    public function test_notification_channels_include_database_broadcast_and_mail(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Channel Check Task',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        $dueToday = new TaskDueTodayNotification($task);
        $overdue = new TaskOverdueNotification($task);
        $dueSoon = new TaskDueSoonNotification($task);

        $expectedChannels = ['database', 'mail', 'broadcast'];

        $this->assertSame($expectedChannels, $dueToday->via($user));
        $this->assertSame($expectedChannels, $overdue->via($user));
        $this->assertSame($expectedChannels, $dueSoon->via($user));
    }

    public function test_task_due_today_email_template_contains_all_required_details(): void
    {
        $user = User::factory()->create(['name' => 'Fahreza']);
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Kuliah',
        ]);
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Mengerjakan Skripsi Bab 4',
            'description' => 'Selesaikan analisis hasil survei.',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => Carbon::today()->toDateString(),
            'category_id' => $category->id,
        ]);

        $notification = new TaskDueTodayNotification($task);
        $mail = $notification->toMail($user);

        $this->assertStringContainsString('Mengerjakan Skripsi Bab 4', $mail->subject);
        $this->assertStringContainsString('Jatuh Tempo Hari Ini', $mail->subject);

        $html = view($mail->view, $mail->viewData)->render();

        $this->assertStringContainsString('Fahreza', $html);
        $this->assertStringContainsString('Mengerjakan Skripsi Bab 4', $html);
        $this->assertStringContainsString('Selesaikan analisis hasil survei.', $html);
        $this->assertStringContainsString('Jatuh Tempo Hari Ini', $html);
        $this->assertStringContainsString('High', $html);
        $this->assertStringContainsString('Kuliah', $html);
        $this->assertStringContainsString(route('tasks.show', $task), $html);
    }

    public function test_task_overdue_email_template_contains_all_required_details(): void
    {
        $user = User::factory()->create(['name' => 'Fahreza']);
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Submit Laporan Pajak',
            'status' => 'pending',
            'priority' => 'medium',
            'due_date' => Carbon::yesterday()->toDateString(),
        ]);

        $notification = new TaskOverdueNotification($task);
        $mail = $notification->toMail($user);

        $this->assertStringContainsString('Submit Laporan Pajak', $mail->subject);
        $this->assertStringContainsString('Overdue', $mail->subject);

        $html = view($mail->view, $mail->viewData)->render();

        $this->assertStringContainsString('Fahreza', $html);
        $this->assertStringContainsString('Submit Laporan Pajak', $html);
        $this->assertStringContainsString('Terlambat (Overdue)', $html);
        $this->assertStringContainsString(route('tasks.show', $task), $html);
    }

    public function test_task_due_soon_email_template_contains_all_required_details(): void
    {
        $user = User::factory()->create(['name' => 'Fahreza']);
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Presentasi Final Project',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => Carbon::tomorrow()->toDateString(),
        ]);

        $notification = new TaskDueSoonNotification($task);
        $mail = $notification->toMail($user);

        $this->assertStringContainsString('Presentasi Final Project', $mail->subject);
        $this->assertStringContainsString('Segera Jatuh Tempo', $mail->subject);

        $html = view($mail->view, $mail->viewData)->render();

        $this->assertStringContainsString('Fahreza', $html);
        $this->assertStringContainsString('Presentasi Final Project', $html);
        $this->assertStringContainsString('Mendatang (Due Soon)', $html);
        $this->assertStringContainsString(route('tasks.show', $task), $html);
    }

    public function test_deadline_checker_dispatches_notifications_to_mail_channel(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $taskToday = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Deadline Hari Ini',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        $taskOverdue = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Terlambat',
            'status' => 'pending',
            'due_date' => Carbon::yesterday()->toDateString(),
        ]);

        $taskSoon = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Deadline Besok',
            'status' => 'pending',
            'due_date' => Carbon::tomorrow()->toDateString(),
        ]);

        $service = app(TaskNotificationService::class);
        $service->checkAndNotifyUser($user);

        Notification::assertSentTo(
            $user,
            TaskDueTodayNotification::class,
            function ($notification, $channels) {
                return in_array('mail', $channels) && in_array('database', $channels);
            }
        );

        Notification::assertSentTo(
            $user,
            TaskOverdueNotification::class,
            function ($notification, $channels) {
                return in_array('mail', $channels) && in_array('database', $channels);
            }
        );

        Notification::assertSentTo(
            $user,
            TaskDueSoonNotification::class,
            function ($notification, $channels) {
                return in_array('mail', $channels) && in_array('database', $channels);
            }
        );
    }

    public function test_email_notifications_are_not_duplicated_on_subsequent_runs(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Unique Daily Email Task',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        $service = app(TaskNotificationService::class);

        // First run dispatches 1 notification
        $firstRun = $service->checkAndNotifyUser($user);
        $this->assertSame(1, $firstRun);
        $this->assertSame(1, $user->notifications()->count());

        // Second run on same day must be idempotent (0 dispatched)
        $secondRun = $service->checkAndNotifyUser($user);
        $this->assertSame(0, $secondRun);
        $this->assertSame(1, $user->notifications()->count());
    }

    public function test_user_isolation_ensures_emails_are_only_sent_to_task_owner(): void
    {
        Notification::fake();

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Task::create([
            'user_id' => $userA->id,
            'title' => 'Task User A',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        $service = app(TaskNotificationService::class);
        $service->checkAndNotifyUser($userA);

        Notification::assertSentTo($userA, TaskDueTodayNotification::class);
        Notification::assertNotSentTo($userB, TaskDueTodayNotification::class);
    }
}
