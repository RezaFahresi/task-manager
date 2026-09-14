<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueSoonNotification;
use App\Notifications\TaskDueTodayNotification;
use App\Notifications\TaskOverdueNotification;
use App\Services\TaskNotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Events\BroadcastNotificationCreated;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RealtimeNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_authorize_their_own_private_notification_channel(): void
    {
        $user = User::factory()->create();

        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-reverb-key',
            'broadcasting.connections.reverb.secret' => 'test-reverb-secret',
            'broadcasting.connections.reverb.app_id' => '999999',
        ]);
        require base_path('routes/channels.php');

        $response = $this->actingAs($user)->post('/broadcasting/auth', [
            'channel_name' => 'private-App.Models.User.'.$user->id,
            'socket_id' => '1234.5678',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['auth']);
    }

    public function test_user_cannot_authorize_another_users_private_notification_channel(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-reverb-key',
            'broadcasting.connections.reverb.secret' => 'test-reverb-secret',
            'broadcasting.connections.reverb.app_id' => '999999',
        ]);
        require base_path('routes/channels.php');

        $response = $this->actingAs($userA)->post('/broadcasting/auth', [
            'channel_name' => 'private-App.Models.User.'.$userB->id,
            'socket_id' => '1234.5678',
        ]);

        $response->assertStatus(403);
    }

    public function test_guest_cannot_authorize_private_notification_channel(): void
    {
        $user = User::factory()->create();

        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-reverb-key',
            'broadcasting.connections.reverb.secret' => 'test-reverb-secret',
            'broadcasting.connections.reverb.app_id' => '999999',
        ]);
        require base_path('routes/channels.php');

        $response = $this->post('/broadcasting/auth', [
            'channel_name' => 'private-App.Models.User.'.$user->id,
            'socket_id' => '1234.5678',
        ]);

        $response->assertStatus(403);
    }

    public function test_deadline_notifications_broadcast_on_user_private_channel(): void
    {
        Event::fake([BroadcastNotificationCreated::class]);

        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Realtime Task Today',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        $user->notify(new TaskDueTodayNotification($task));

        Event::assertDispatched(BroadcastNotificationCreated::class, function ($event) use ($user, $task) {
            $this->assertSame($user->id, $event->notifiable->id);

            $channels = $event->broadcastOn();
            $channelNames = collect($channels)->map(fn ($ch) => is_string($ch) ? $ch : $ch->name)->all();

            $this->assertContains('private-App.Models.User.'.$user->id, $channelNames);

            $payload = $event->broadcastWith();
            $this->assertSame($task->id, $payload['task_id']);
            $this->assertSame('Realtime Task Today', $payload['task_title']);
            $this->assertSame('Task Jatuh Tempo Hari Ini', $payload['title']);
            $this->assertSame(route('tasks.show', $task), $payload['url']);

            return true;
        });
    }

    public function test_overdue_and_due_soon_notifications_broadcast_correctly(): void
    {
        Event::fake([BroadcastNotificationCreated::class]);

        $user = User::factory()->create();

        $taskOverdue = Task::create([
            'user_id' => $user->id,
            'title' => 'Realtime Task Overdue',
            'status' => 'pending',
            'due_date' => Carbon::yesterday()->toDateString(),
        ]);

        $taskDueSoon = Task::create([
            'user_id' => $user->id,
            'title' => 'Realtime Task Soon',
            'status' => 'pending',
            'due_date' => Carbon::tomorrow()->toDateString(),
        ]);

        $user->notify(new TaskOverdueNotification($taskOverdue));
        $user->notify(new TaskDueSoonNotification($taskDueSoon));

        Event::assertDispatched(BroadcastNotificationCreated::class, function ($event) use ($taskOverdue) {
            return ($event->data['task_id'] ?? null) === $taskOverdue->id
                && ($event->data['type'] ?? null) === 'overdue';
        });

        Event::assertDispatched(BroadcastNotificationCreated::class, function ($event) use ($taskDueSoon) {
            return ($event->data['task_id'] ?? null) === $taskDueSoon->id
                && ($event->data['type'] ?? null) === 'due_soon';
        });
    }

    public function test_user_isolation_user_b_does_not_receive_user_a_broadcast(): void
    {
        Event::fake([BroadcastNotificationCreated::class]);

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $taskA = Task::create([
            'user_id' => $userA->id,
            'title' => 'Task Rahasia User A',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        $userA->notify(new TaskDueTodayNotification($taskA));

        Event::assertDispatched(BroadcastNotificationCreated::class, function ($event) use ($userA) {
            return $event->notifiable->id === $userA->id;
        });

        Event::assertNotDispatched(BroadcastNotificationCreated::class, function ($event) use ($userB) {
            return $event->notifiable->id === $userB->id;
        });
    }

    public function test_check_and_notify_is_idempotent_and_does_not_double_broadcast(): void
    {
        Event::fake([BroadcastNotificationCreated::class]);

        $user = User::factory()->create();
        Task::create([
            'user_id' => $user->id,
            'title' => 'Idempotent Broadcast Task',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        $service = app(TaskNotificationService::class);

        // Run first time
        $sentFirst = $service->checkAndNotifyUser($user);
        $this->assertSame(1, $sentFirst);
        Event::assertDispatchedTimes(BroadcastNotificationCreated::class, 1);

        // Run second time on same day
        $sentSecond = $service->checkAndNotifyUser($user);
        $this->assertSame(0, $sentSecond);
        Event::assertDispatchedTimes(BroadcastNotificationCreated::class, 1);
    }

    public function test_mark_as_read_supports_ajax_json_response(): void
    {
        $user = User::factory()->create();
        $task = Task::create(['user_id' => $user->id, 'title' => 'Task Ajax Test', 'status' => 'pending']);

        $user->notify(new TaskDueTodayNotification($task));
        $notification = $user->unreadNotifications()->first();

        $this->assertNotNull($notification);

        $response = $this->actingAs($user)->postJson("/notifications/{$notification->id}/read");
        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'unread_count' => 0,
        ]);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_all_as_read_supports_ajax_json_response(): void
    {
        $user = User::factory()->create();
        $task1 = Task::create(['user_id' => $user->id, 'title' => 'Task 1', 'status' => 'pending']);
        $task2 = Task::create(['user_id' => $user->id, 'title' => 'Task 2', 'status' => 'pending']);

        $user->notify(new TaskDueTodayNotification($task1));
        $user->notify(new TaskOverdueNotification($task2));

        $this->assertSame(2, $user->unreadNotifications()->count());

        $response = $this->actingAs($user)->postJson('/notifications/read-all');
        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'unread_count' => 0,
        ]);

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_ajax_mark_as_read_denies_unauthorized_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $taskB = Task::create(['user_id' => $userB->id, 'title' => 'Task User B', 'status' => 'pending']);
        $userB->notify(new TaskDueTodayNotification($taskB));
        $notificationB = $userB->notifications()->first();

        $response = $this->actingAs($userA)->postJson("/notifications/{$notificationB->id}/read");
        $response->assertStatus(403);
    }
}
