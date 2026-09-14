<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDeadlineOneHourNotification;
use App\Notifications\TaskDeadlineTenMinutesNotification;
use App\Notifications\TaskOverdueNotification;
use App\Services\TaskNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Event as ScheduleEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Events\BroadcastNotificationCreated;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SmartDeadlineReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_smart_reminders_command_is_registered_in_schedule_to_run_every_minute(): void
    {
        $schedule = app(Schedule::class);

        $event = collect($schedule->events())->first(function (ScheduleEvent $event) {
            return str_contains($event->command ?? '', 'tasks:smart-reminders');
        });

        $this->assertNotNull($event, 'Command tasks:smart-reminders harus terdaftar di scheduler.');
        $this->assertSame('* * * * *', $event->expression, 'tasks:smart-reminders harus dijadwalkan setiap menit (* * * * *).');
    }

    public function test_sends_1_hour_reminder_when_task_deadline_is_within_60_minutes(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Presentasi Q3',
            'status' => 'pending',
            'due_date' => Carbon::now()->addMinutes(45)->format('Y-m-d H:i:s'),
        ]);

        $service = app(TaskNotificationService::class);
        $count = $service->checkAndNotifyUser($user);

        $this->assertSame(1, $count);
        $this->assertSame(1, $user->notifications()->count());

        $notification = $user->notifications->first();
        $this->assertSame(TaskDeadlineOneHourNotification::class, $notification->type);
        $this->assertSame('reminder_1h', $notification->data['type']);
        $this->assertSame($task->id, $notification->data['task_id']);
        $this->assertStringContainsString('1 jam', strtolower($notification->data['message']));
    }

    public function test_sends_10_minutes_reminder_when_task_deadline_is_within_10_minutes(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Submit Laporan Finansial',
            'status' => 'pending',
            'due_date' => Carbon::now()->addMinutes(8)->format('Y-m-d H:i:s'),
        ]);

        $service = app(TaskNotificationService::class);
        $count = $service->checkAndNotifyUser($user);

        $this->assertSame(1, $count);
        $this->assertSame(1, $user->notifications()->count());

        $notification = $user->notifications->first();
        $this->assertSame(TaskDeadlineTenMinutesNotification::class, $notification->type);
        $this->assertSame('reminder_10m', $notification->data['type']);
        $this->assertSame($task->id, $notification->data['task_id']);
        $this->assertStringContainsString('10 menit', strtolower($notification->data['message']));
    }

    public function test_sends_overdue_reminder_when_task_deadline_has_passed(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Review Kontrak Vendor',
            'status' => 'pending',
            'due_date' => Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s'),
        ]);

        $service = app(TaskNotificationService::class);
        $count = $service->checkAndNotifyUser($user);

        $this->assertSame(1, $count);
        $this->assertSame(1, $user->notifications()->count());

        $notification = $user->notifications->first();
        $this->assertSame(TaskOverdueNotification::class, $notification->type);
        $this->assertSame('overdue', $notification->data['type']);
        $this->assertSame($task->id, $notification->data['task_id']);
    }

    public function test_does_not_send_reminders_for_completed_tasks(): void
    {
        $user = User::factory()->create();

        // Completed task within 8 minutes
        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Sudah Selesai 1',
            'status' => 'completed',
            'due_date' => Carbon::now()->addMinutes(8)->format('Y-m-d H:i:s'),
        ]);

        // Completed task overdue
        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Sudah Selesai 2',
            'status' => 'completed',
            'due_date' => Carbon::now()->subMinutes(10)->format('Y-m-d H:i:s'),
        ]);

        $service = app(TaskNotificationService::class);
        $count = $service->checkAndNotifyUser($user);

        $this->assertSame(0, $count);
        $this->assertSame(0, $user->notifications()->count());
    }

    public function test_does_not_send_duplicate_reminders_for_the_same_deadline(): void
    {
        $user = User::factory()->create();
        Task::create([
            'user_id' => $user->id,
            'title' => 'Rapat Koordinasi Tim',
            'status' => 'pending',
            'due_date' => Carbon::now()->addMinutes(40)->format('Y-m-d H:i:s'),
        ]);

        $service = app(TaskNotificationService::class);

        // First run
        $firstCount = $service->checkAndNotifyUser($user);
        $this->assertSame(1, $firstCount);
        $this->assertSame(1, $user->notifications()->count());

        // Second run immediately
        $secondCount = $service->checkAndNotifyUser($user);
        $this->assertSame(0, $secondCount);
        $this->assertSame(1, $user->notifications()->count());
    }

    public function test_rescheduling_task_clears_old_reminders_and_triggers_new_reminder_for_new_deadline(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Deploy Aplikasi v2',
            'status' => 'pending',
            'due_date' => Carbon::now()->addMinutes(45)->format('Y-m-d H:i:s'),
        ]);

        $service = app(TaskNotificationService::class);
        $service->checkAndNotifyUser($user);
        $this->assertSame(1, $user->notifications()->count());

        // Reschedule task to 8 minutes from now
        $task->update([
            'due_date' => Carbon::now()->addMinutes(8)->format('Y-m-d H:i:s'),
        ]);

        // Old reminders must have been cleared
        $this->assertSame(0, $user->notifications()->count());

        // Now run notification service again
        $service->checkAndNotifyUser($user);

        // New reminder (10m) should be generated for the new deadline
        $this->assertSame(1, $user->notifications()->count());
        $notification = $user->notifications->first();
        $this->assertSame(TaskDeadlineTenMinutesNotification::class, $notification->type);
        $this->assertSame('reminder_10m', $notification->data['type']);
    }

    public function test_completing_task_dismisses_active_deadline_notifications(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Kirim Proposal Klien',
            'status' => 'pending',
            'due_date' => Carbon::now()->addMinutes(8)->format('Y-m-d H:i:s'),
        ]);

        $service = app(TaskNotificationService::class);
        $service->checkAndNotifyUser($user);
        $this->assertSame(1, $user->notifications()->count());

        // Complete the task
        $task->update(['status' => 'completed']);

        // Notification for this task must be cleared
        $this->assertSame(0, $user->notifications()->count());
    }

    public function test_smart_reminders_broadcast_on_user_private_channel(): void
    {
        Event::fake([BroadcastNotificationCreated::class]);

        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Broadcast Realtime Test',
            'status' => 'pending',
            'due_date' => Carbon::now()->addMinutes(8)->format('Y-m-d H:i:s'),
        ]);

        $user->notify(new TaskDeadlineTenMinutesNotification($task));

        Event::assertDispatched(BroadcastNotificationCreated::class, function ($event) use ($user, $task) {
            $this->assertSame($user->id, $event->notifiable->id);
            $this->assertSame('private-App.Models.User.'.$user->id, $event->broadcastOn()[0]->name);
            $this->assertSame($task->id, $event->data['task_id']);
            $this->assertSame('reminder_10m', $event->data['type']);

            return true;
        });
    }

    public function test_task_creation_and_update_supports_due_time(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post('/tasks', [
            'title' => 'Task dengan Waktu Spesifik',
            'status' => 'pending',
            'due_date' => '2026-11-20',
            'due_time' => '15:45',
            'priority' => 'high',
            'category_id' => $category->id,
        ]);

        $response->assertRedirect('/tasks');
        $task = Task::where('title', 'Task dengan Waktu Spesifik')->first();
        $this->assertNotNull($task);
        $this->assertSame('2026-11-20 15:45:00', $task->due_date->format('Y-m-d H:i:s'));

        // Update with new time
        $updateResponse = $this->actingAs($user)->put("/tasks/{$task->id}", [
            'title' => 'Task dengan Waktu Diperbarui',
            'status' => 'pending',
            'due_date' => '2026-11-21',
            'due_time' => '09:15',
            'priority' => 'medium',
            'category_id' => $category->id,
        ]);

        $updateResponse->assertRedirect('/tasks');
        $task->refresh();
        $this->assertSame('Task dengan Waktu Diperbarui', $task->title);
        $this->assertSame('2026-11-21 09:15:00', $task->due_date->format('Y-m-d H:i:s'));
    }

    public function test_smart_reminders_command_executes_successfully(): void
    {
        $user = User::factory()->create();
        Task::create([
            'user_id' => $user->id,
            'title' => 'Artisan Command Test Task',
            'status' => 'pending',
            'due_date' => Carbon::now()->addMinutes(45)->format('Y-m-d H:i:s'),
        ]);

        $this->artisan('tasks:smart-reminders')
            ->expectsOutputToContain('Checking smart task deadline reminders...')
            ->expectsOutputToContain('Successfully dispatched')
            ->assertSuccessful();

        $this->assertSame(1, $user->notifications()->count());
    }

    public function test_smart_reminder_classes_implement_should_queue_and_channels(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Queue Test',
            'status' => 'pending',
            'due_date' => Carbon::now()->addMinutes(45)->format('Y-m-d H:i:s'),
        ]);

        $notif1h = new TaskDeadlineOneHourNotification($task);
        $notif10m = new TaskDeadlineTenMinutesNotification($task);

        $this->assertInstanceOf(ShouldQueue::class, $notif1h);
        $this->assertInstanceOf(ShouldQueue::class, $notif10m);

        $expectedChannels = ['database', 'mail', 'broadcast'];
        $this->assertSame($expectedChannels, $notif1h->via($user));
        $this->assertSame($expectedChannels, $notif10m->via($user));

        $expectedConnections = [
            'database' => 'sync',
            'broadcast' => 'sync',
        ];
        $this->assertSame($expectedConnections, $notif1h->viaConnections());
        $this->assertSame($expectedConnections, $notif10m->viaConnections());
    }

    public function test_smart_reminder_emails_render_correct_details_and_badges(): void
    {
        $user = User::factory()->create(['name' => 'Fahreza']);
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Kirim Laporan Akhir Bulan',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => Carbon::now()->addMinutes(10)->format('Y-m-d H:i:s'),
        ]);

        // 1h reminder email
        $notif1h = new TaskDeadlineOneHourNotification($task);
        $mail1h = $notif1h->toMail($user);
        $html1h = view($mail1h->view, $mail1h->viewData)->render();
        $this->assertStringContainsString('1 Jam', $mail1h->subject);
        $this->assertStringContainsString('1 Jam Menuju Deadline', $html1h);

        // 10m reminder email
        $notif10m = new TaskDeadlineTenMinutesNotification($task);
        $mail10m = $notif10m->toMail($user);
        $html10m = view($mail10m->view, $mail10m->viewData)->render();
        $this->assertStringContainsString('10 Menit', $mail10m->subject);
        $this->assertStringContainsString('Mendesak (10 Menit Menuju Deadline)', $html10m);
    }

    public function test_notification_center_renders_smart_badges(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Desain Landing Page',
            'status' => 'pending',
            'priority' => 'medium',
            'due_date' => Carbon::now()->addMinutes(8)->format('Y-m-d H:i:s'),
        ]);

        $user->notify(new TaskDeadlineOneHourNotification($task));
        $user->notify(new TaskDeadlineTenMinutesNotification($task));

        $response = $this->actingAs($user)->get('/notifications');
        $response->assertOk();
        $response->assertSee('1 Jam');
        $response->assertSee('10 Menit');
        $response->assertSee('Desain Landing Page');
    }
}
