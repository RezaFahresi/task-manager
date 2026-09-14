<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskNotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class BugHuntingProofTest extends TestCase
{
    use RefreshDatabase;

    /**
     * REGRESSION TEST BUG #1:
     * Deleting a notification for an overdue/due-today task does NOT get recreated
     * when reloading /notifications or /dashboard. Rescheduled tasks and new tasks still notify.
     */
    public function test_bug_1_notification_is_not_recreated_after_user_deletion(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Overdue Uji Hapus',
            'status' => 'pending',
            'due_date' => Carbon::yesterday()->toDateString(),
        ]);

        // First visit to /notifications triggers checkAndNotifyUser
        $this->actingAs($user)->get('/notifications');
        $this->assertSame(1, $user->notifications()->count());

        $notification = $user->notifications()->first();

        // User deletes the notification
        $deleteResponse = $this->actingAs($user)->delete("/notifications/{$notification->id}");
        $deleteResponse->assertRedirect();

        // Follow redirect back to /notifications (which runs checkAndNotifyUser again)
        $this->actingAs($user)->get('/notifications');

        // FIXED: Notification must NOT be recreated after being deleted!
        $this->assertSame(0, $user->fresh()->notifications()->count(), 'Notification must remain deleted and not be recreated.');

        // Visiting dashboard must also NOT recreate the deleted notification
        $this->actingAs($user)->get('/dashboard');
        $this->assertSame(0, $user->fresh()->notifications()->count(), 'Dashboard visit must not recreate deleted notification.');

        // Rescheduling the task deadline MUST trigger a new notification
        $task->update(['due_date' => Carbon::today()->toDateString()]);
        $this->actingAs($user)->get('/dashboard');
        $this->assertSame(1, $user->fresh()->notifications()->count(), 'Rescheduled task with new deadline must trigger new notification.');
    }

    /**
     * REGRESSION TEST BUG #2:
     * When Reverb server is offline (connection refused),
     * web requests (/dashboard, /notifications) and TaskNotificationService
     * must NOT crash with HTTP 500, and database notifications must still be delivered.
     */
    public function test_bug_2_web_request_and_notification_service_do_not_crash_when_reverb_offline(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Reverb Offline Test',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        // Point Reverb to an offline port
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'offline-key',
            'broadcasting.connections.reverb.secret' => 'offline-secret',
            'broadcasting.connections.reverb.app_id' => '999999',
            'broadcasting.connections.reverb.options.host' => '127.0.0.1',
            'broadcasting.connections.reverb.options.port' => 59998,
            'broadcasting.connections.reverb.options.scheme' => 'http',
        ]);

        // Accessing /dashboard must succeed (200 OK) instead of crashing with HTTP 500
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertOk();

        // Database notification must still be created
        $this->assertSame(1, $user->fresh()->notifications()->count());
        $notification = $user->fresh()->notifications()->first();
        $this->assertSame('due_today', $notification->data['type']);

        // Accessing /notifications must also succeed (200 OK)
        $notifResponse = $this->actingAs($user)->get('/notifications');
        $notifResponse->assertOk();
        $notifResponse->assertSee('Tugas Reverb Offline Test');
    }

    /**
     * REGRESSION TEST BUG #2:
     * CheckTaskDeadlinesCommand does not crash when Reverb is offline.
     */
    public function test_bug_2_check_deadlines_command_does_not_crash_when_reverb_offline(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Command Offline Reverb',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'offline-key',
            'broadcasting.connections.reverb.secret' => 'offline-secret',
            'broadcasting.connections.reverb.app_id' => '999999',
            'broadcasting.connections.reverb.options.host' => '127.0.0.1',
            'broadcasting.connections.reverb.options.port' => 59998,
            'broadcasting.connections.reverb.options.scheme' => 'http',
        ]);

        $this->artisan('tasks:check-deadlines')
            ->expectsOutput('Checking task deadlines...')
            ->assertSuccessful();

        $this->assertSame(1, $user->fresh()->notifications()->count());
    }

    /**
     * REGRESSION TEST BUG #3:
     * Filter deadline=overdue must ONLY display tasks with status 'pending'.
     * Completed tasks whose due date has passed must NOT appear as overdue.
     */
    public function test_bug_3_completed_task_does_not_appear_under_overdue_filter(): void
    {
        $user = User::factory()->create();

        // 1. Task pending yang sudah melewati deadline (overdue)
        $pendingOverdue = Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Pending Overdue Sebenarnya',
            'status' => 'pending',
            'due_date' => Carbon::yesterday()->toDateString(),
        ]);

        // 2. Task selesai yang deadline-nya kemarin (tidak boleh dianggap overdue)
        $completedTask = Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Selesai Tapi Deadline Kemarin',
            'status' => 'completed',
            'due_date' => Carbon::yesterday()->toDateString(),
        ]);

        // Dashboard harus konsisten: overdueTasks hanya menghitung pending task
        $dashRes = $this->actingAs($user)->get('/dashboard');
        $dashRes->assertOk();
        $this->assertSame(1, $dashRes->viewData('overdueTasks'));

        // Filter /tasks?deadline=overdue harus HANYA menampilkan task pending
        $indexRes = $this->actingAs($user)->get('/tasks?deadline=overdue');
        $indexRes->assertOk();
        $indexRes->assertSee('Tugas Pending Overdue Sebenarnya');
        $indexRes->assertDontSee('Tugas Selesai Tapi Deadline Kemarin');
    }

    /**
     * REGRESSION TEST BUG #4:
     * When Notification Center is initially empty, the DOM must contain both the empty-state
     * and the notification list-group container (#nc-notifications-list-group) so incoming
     * realtime Echo notifications can immediately appear without requiring a hard reload.
     */
    public function test_bug_4_empty_notification_center_renders_dom_list_group_for_realtime_updates(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/notifications');
        $response->assertOk();

        // Empty state is rendered
        $response->assertSee('nc-empty-card', false);
        $response->assertSee('Belum Ada Notifikasi');

        // Notification list group and card wrapper MUST exist in DOM for realtime Echo injection
        $response->assertSee('nc-notifications-list-group', false);
        $response->assertSee('nc-card', false);
        $response->assertSee('d-none', false);
    }

    /**
     * REGRESSION TEST BUG #5:
     * Category filter must work when category name is numeric like "2026".
     * Resolves category name properly without falsely treating it as a non-existent category_id.
     */
    public function test_bug_5_numeric_category_name_works_in_filter(): void
    {
        $user = User::factory()->create();

        $category2026 = Category::create([
            'user_id' => $user->id,
            'name' => '2026',
        ]);

        $categoryKuliah = Category::create([
            'user_id' => $user->id,
            'name' => 'Kuliah',
        ]);

        $task2026 = Task::create([
            'user_id' => $user->id,
            'category_id' => $category2026->id,
            'title' => 'Tugas Tahun 2026',
            'status' => 'pending',
        ]);

        $taskKuliah = Task::create([
            'user_id' => $user->id,
            'category_id' => $categoryKuliah->id,
            'title' => 'Tugas Kuliah Semester Baru',
            'status' => 'pending',
        ]);

        // Filter by category name with numeric value: ?category=2026
        $responseNumeric = $this->actingAs($user)->get('/tasks?category=2026');
        $responseNumeric->assertOk();
        $responseNumeric->assertSee('Tugas Tahun 2026');
        $responseNumeric->assertDontSee('Tugas Kuliah Semester Baru');

        // Filter by category name string: ?category=Kuliah
        $responseString = $this->actingAs($user)->get('/tasks?category=Kuliah');
        $responseString->assertOk();
        $responseString->assertSee('Tugas Kuliah Semester Baru');
        $responseString->assertDontSee('Tugas Tahun 2026');

        // Filter by category_id directly: ?category_id={$category2026->id}
        $responseId = $this->actingAs($user)->get("/tasks?category_id={$category2026->id}");
        $responseId->assertOk();
        $responseId->assertSee('Tugas Tahun 2026');
        $responseId->assertDontSee('Tugas Kuliah Semester Baru');
    }

    /**
     * REGRESSION TEST BUG #6:
     * When a task is deleted, any related notifications must be cleaned up safely.
     * Must not leave orphaned notifications with broken "Buka Task" links (404),
     * and must NOT delete notifications of other tasks or other users.
     */
    public function test_bug_6_deleting_task_cleans_up_related_notifications_safely(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // User A has Task 1 and Task 2
        $taskA1 = Task::create([
            'user_id' => $userA->id,
            'title' => 'Tugas User A Akan Dihapus',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        $taskA2 = Task::create([
            'user_id' => $userA->id,
            'title' => 'Tugas User A Tetap Ada',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        // User B has Task B
        $taskB = Task::create([
            'user_id' => $userB->id,
            'title' => 'Tugas User B Milik Orang Lain',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        // Trigger notifications for all users
        $service = app(TaskNotificationService::class);
        $service->checkAndNotifyUser($userA);
        $service->checkAndNotifyUser($userB);

        $this->assertSame(2, $userA->notifications()->count());
        $this->assertSame(1, $userB->notifications()->count());

        // User A deletes Task 1
        $deleteResponse = $this->actingAs($userA)->delete("/tasks/{$taskA1->id}");
        $deleteResponse->assertRedirect(route('tasks.index'));

        // Notification for Task 1 must be cleaned up
        $this->assertSame(1, $userA->fresh()->notifications()->count());
        $remainingNotifA = $userA->fresh()->notifications()->first();
        $this->assertSame($taskA2->id, $remainingNotifA->data['task_id']);

        // User B's notification must remain completely untouched
        $this->assertSame(1, $userB->fresh()->notifications()->count());
        $remainingNotifB = $userB->fresh()->notifications()->first();
        $this->assertSame($taskB->id, $remainingNotifB->data['task_id']);

        // Notification page of User A must not contain link to deleted Task 1
        $ncResponse = $this->actingAs($userA)->get('/notifications');
        $ncResponse->assertOk();
        $ncResponse->assertDontSee(route('tasks.show', $taskA1->id));
        $ncResponse->assertSee(route('tasks.show', $taskA2->id));
    }

    /**
     * REGRESSION TEST BUG #7:
     * Pending overdue tasks must NOT receive duplicate overdue notifications each day.
     * Only 1 active overdue notification exists for that task while status and due_date remain unchanged.
     * When completed and reopened, or when due_date is rescheduled, fresh notification is dispatched.
     */
    public function test_bug_7_overdue_task_does_not_duplicate_notification_on_subsequent_days(): void
    {
        Carbon::setTestNow('2026-09-10 09:00:00');
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Overdue Tanpa Duplikasi Harian',
            'status' => 'pending',
            'due_date' => '2026-09-08',
        ]);

        $service = app(TaskNotificationService::class);

        // Day 1 (2026-09-10): Task is overdue, dispatches 1 notification
        $sentDay1 = $service->checkAndNotifyUser($user);
        $this->assertSame(1, $sentDay1);
        $this->assertSame(1, $user->notifications()->count());

        // Day 2 (2026-09-11): Next day arrives
        Carbon::setTestNow('2026-09-11 09:00:00');
        $sentDay2 = $service->checkAndNotifyUser($user);
        // Must NOT create a duplicate notification!
        $this->assertSame(0, $sentDay2, 'Overdue task must not duplicate notification on next day.');
        $this->assertSame(1, $user->fresh()->notifications()->count());

        // Day 3 (2026-09-12): Another day passes
        Carbon::setTestNow('2026-09-12 09:00:00');
        $sentDay3 = $service->checkAndNotifyUser($user);
        $this->assertSame(0, $sentDay3, 'Overdue task must remain at single active notification.');
        $this->assertSame(1, $user->fresh()->notifications()->count());

        // Test BUG #1 dismissal preservation: User dismisses the notification
        $notification = $user->fresh()->notifications()->first();
        $this->actingAs($user)->delete("/notifications/{$notification->id}");
        $this->assertSame(0, $user->fresh()->notifications()->count());

        // Day 4 (2026-09-13): Dismissed overdue notification must NOT be recreated
        Carbon::setTestNow('2026-09-13 09:00:00');
        $sentDay4 = $service->checkAndNotifyUser($user);
        $this->assertSame(0, $sentDay4, 'Dismissed overdue notification must not be recreated.');
        $this->assertSame(0, $user->fresh()->notifications()->count());

        // Test Reschedule: User reschedules the due date
        $task->update(['due_date' => '2026-09-14']);
        // When 2026-09-15 arrives, the task is overdue for the new deadline
        Carbon::setTestNow('2026-09-15 09:00:00');
        $sentRescheduled = $service->checkAndNotifyUser($user);
        $this->assertSame(1, $sentRescheduled, 'Rescheduled task must receive notification for the new deadline.');
        $this->assertSame(1, $user->fresh()->notifications()->count());

        // Test Completed & Reopened:
        // When completed, overdue notification is cleaned up
        $task->update(['status' => 'completed']);
        $this->assertSame(0, $user->fresh()->notifications()->count());

        // When reopened to pending, it becomes overdue again and receives a fresh notification
        $task->update(['status' => 'pending']);
        $sentReopened = $service->checkAndNotifyUser($user);
        $this->assertSame(1, $sentReopened, 'Task returning to pending after completion must receive a fresh overdue notification.');
        $this->assertSame(1, $user->fresh()->notifications()->count());

        Carbon::setTestNow(); // Reset test time
    }

    /**
     * REGRESSION TEST BUG #8:
     * TaskNotificationService::checkAndNotifyAll() and tasks:check-deadlines must use
     * bulk queries and eager loading to avoid N+1 query loops.
     */
    public function test_bug_8_check_and_notify_all_eliminates_n_plus_one_queries(): void
    {
        // Create 10 distinct users, each with pending deadline tasks
        $users = User::factory()->count(10)->create();
        foreach ($users as $index => $user) {
            Task::create([
                'user_id' => $user->id,
                'title' => "Task User {$index}",
                'status' => 'pending',
                'due_date' => Carbon::today()->toDateString(),
            ]);
        }

        $service = app(TaskNotificationService::class);

        // 1. Verify checkAndNotifyAll() runs in bulk O(1) queries instead of O(N) per user
        Notification::fake();
        DB::flushQueryLog();
        DB::enableQueryLog();

        $totalSent = $service->checkAndNotifyAll();
        $checkQueryCount = count(DB::getQueryLog());

        $this->assertSame(10, $totalSent);
        // Before optimization: 1 (chunk) + 10 users * 4 queries = 41 queries
        // With bulk query optimization: exactly 3 queries (tasks, users eager load, notifications)
        $this->assertSame(3, $checkQueryCount, "Expected exactly 3 bulk queries, got {$checkQueryCount}.");

        // 2. Create existing notifications in database for these tasks
        foreach ($users as $user) {
            $task = $user->tasks()->first();
            $user->notifications()->create([
                'id' => (string) Str::uuid(),
                'type' => 'App\Notifications\TaskDueTodayNotification',
                'data' => [
                    'task_id' => $task->id,
                    'type' => 'due_today',
                    'due_date' => Carbon::today()->toDateString(),
                ],
                'read_at' => null,
            ]);
        }

        // 3. Verify scheduled artisan command runs in exactly 3 bulk queries without N+1
        DB::flushQueryLog();
        $this->artisan('tasks:check-deadlines')->assertSuccessful();
        $artisanQueryCount = count(DB::getQueryLog());

        // In the old code, even when no new notifications are needed, it ran 1 + 10*4 = 41 queries.
        // With bulk query optimization, it runs exactly 3 queries total.
        $this->assertSame(3, $artisanQueryCount, "Artisan command executed {$artisanQueryCount} queries, expected exactly 3 bulk queries.");
    }

    /**
     * REGRESSION TEST BUG #9:
     * TaskController::edit() must authorize using 'update' ability on TaskPolicy, not 'view'.
     */
    public function test_bug_9_task_edit_authorizes_using_update_ability(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $task = Task::create([
            'user_id' => $owner->id,
            'title' => 'Tugas Uji Authorize Edit',
            'status' => 'pending',
        ]);

        // Unauthorized user cannot access edit page
        $forbiddenResponse = $this->actingAs($otherUser)->get("/tasks/{$task->id}/edit");
        $forbiddenResponse->assertForbidden();

        // Authorized user can access edit page
        $okResponse = $this->actingAs($owner)->get("/tasks/{$task->id}/edit");
        $okResponse->assertOk();
    }

    /**
     * REGRESSION TEST BUG #10:
     * Search input must escape SQL %, _ and backslash wildcards so literal searches do not act as wildcards.
     */
    public function test_bug_10_search_escapes_sql_wildcards_and_uses_parameter_binding(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Proyek 100% Selesai',
            'status' => 'pending',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Proyek 1000 Pekerjaan',
            'status' => 'pending',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Fitur task_khusus',
            'status' => 'pending',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Fitur taskxkhusus',
            'status' => 'pending',
        ]);

        // 1. Searching literal "100%" must ONLY match "Proyek 100% Selesai", not "Proyek 1000 Pekerjaan"
        $resPercent = $this->actingAs($user)->get('/tasks?search='.urlencode('100%'));
        $resPercent->assertOk();
        $resPercent->assertSee('Proyek 100% Selesai');
        $resPercent->assertDontSee('Proyek 1000 Pekerjaan');

        // 2. Searching literal "task_khusus" must ONLY match with underscore, not arbitrary char "taskxkhusus"
        $resUnderscore = $this->actingAs($user)->get('/tasks?search=task_khusus');
        $resUnderscore->assertOk();
        $resUnderscore->assertSee('Fitur task_khusus');
        $resUnderscore->assertDontSee('Fitur taskxkhusus');

        // 3. Searching standalone "_" must only match tasks containing literal underscore
        $resOnlyUnderscore = $this->actingAs($user)->get('/tasks?search=_');
        $resOnlyUnderscore->assertOk();
        $resOnlyUnderscore->assertSee('Fitur task_khusus');
        $resOnlyUnderscore->assertDontSee('Proyek 100% Selesai');
        $resOnlyUnderscore->assertDontSee('Proyek 1000 Pekerjaan');

        // 4. Searching standalone "%" must only match tasks containing literal percent
        $resOnlyPercent = $this->actingAs($user)->get('/tasks?search='.urlencode('%'));
        $resOnlyPercent->assertOk();
        $resOnlyPercent->assertSee('Proyek 100% Selesai');
        $resOnlyPercent->assertDontSee('Proyek 1000 Pekerjaan');
    }

    /**
     * REGRESSION TEST BUG #11:
     * Category filter standardized to category_id across Dashboard, Categories, and Tasks view,
     * while maintaining backwards compatibility for legacy 'category' parameter.
     */
    public function test_bug_11_category_filter_standardized_to_category_id_with_backwards_compatibility(): void
    {
        $user = User::factory()->create();

        $catWork = Category::create(['user_id' => $user->id, 'name' => 'Work']);
        $catHome = Category::create(['user_id' => $user->id, 'name' => 'Home']);

        $taskWork = Task::create([
            'user_id' => $user->id,
            'category_id' => $catWork->id,
            'title' => 'Tugas Pekerjaan Kantor',
            'status' => 'pending',
        ]);

        $taskHome = Task::create([
            'user_id' => $user->id,
            'category_id' => $catHome->id,
            'title' => 'Tugas Rumah Tangga',
            'status' => 'pending',
        ]);

        // 1. Standard parameter: ?category_id=X
        $resId = $this->actingAs($user)->get("/tasks?category_id={$catWork->id}");
        $resId->assertOk();
        $resId->assertSee('Tugas Pekerjaan Kantor');
        $resId->assertDontSee('Tugas Rumah Tangga');

        // 2. Backwards compatibility: ?category=X (id)
        $resLegacyId = $this->actingAs($user)->get("/tasks?category={$catHome->id}");
        $resLegacyId->assertOk();
        $resLegacyId->assertSee('Tugas Rumah Tangga');
        $resLegacyId->assertDontSee('Tugas Pekerjaan Kantor');

        // 3. Backwards compatibility: ?category=Work (name)
        $resLegacyName = $this->actingAs($user)->get('/tasks?category=Work');
        $resLegacyName->assertOk();
        $resLegacyName->assertSee('Tugas Pekerjaan Kantor');
        $resLegacyName->assertDontSee('Tugas Rumah Tangga');

        // 4. Verify Tasks page HTML form uses select name="category_id"
        $resForm = $this->actingAs($user)->get('/tasks');
        $resForm->assertOk();
        $resForm->assertSee('name="category_id"', false);
    }

    /**
     * REGRESSION TEST BUG #12:
     * When user deletes account, all database notifications belonging to that user
     * are deleted before user deletion, without affecting other users' notifications.
     */
    public function test_bug_12_deleting_user_cleans_up_all_user_notifications(): void
    {
        $userA = User::factory()->create(['password' => bcrypt('password123')]);
        $userB = User::factory()->create(['password' => bcrypt('password123')]);

        // Create notifications for user A
        $userA->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\TaskDueTodayNotification',
            'data' => ['task_id' => 1, 'type' => 'due_today'],
            'read_at' => null,
        ]);
        $userA->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\TaskOverdueNotification',
            'data' => ['task_id' => 2, 'type' => 'overdue'],
            'read_at' => null,
        ]);

        // Create notification for user B
        $userB->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\TaskDueTodayNotification',
            'data' => ['task_id' => 3, 'type' => 'due_today'],
            'read_at' => null,
        ]);

        $this->assertSame(2, $userA->notifications()->count());
        $this->assertSame(1, $userB->notifications()->count());

        // User A deletes account via profile destroy
        $deleteResponse = $this->actingAs($userA)->delete('/profile', [
            'password' => 'password123',
        ]);
        $deleteResponse->assertRedirect('/');

        // User A's notifications must be completely wiped from database
        $this->assertSame(
            0,
            DB::table('notifications')->where('notifiable_type', User::class)->where('notifiable_id', $userA->id)->count(),
            "User A's notifications must be removed before/upon user deletion."
        );

        // User B's notifications must remain intact
        $this->assertSame(
            1,
            DB::table('notifications')->where('notifiable_type', User::class)->where('notifiable_id', $userB->id)->count(),
            "User B's notifications must not be affected."
        );
    }

    /**
     * REGRESSION TEST BUG #13:
     * MAIL_FROM_NAME in .env uses "${APP_NAME}" and is not malformed with spaces like "${Task Manager}".
     */
    public function test_bug_13_mail_from_name_is_not_malformed(): void
    {
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            $this->assertStringNotContainsString('MAIL_FROM_NAME="${Task Manager}"', $envContent, '.env must not contain malformed variable interpolation.');
            $this->assertStringContainsString('MAIL_FROM_NAME="${APP_NAME}"', $envContent, '.env should reference ${APP_NAME}.');
        }

        // Config correctly inherits APP_NAME without issues
        $this->assertNotEmpty(config('mail.from.name'));
    }

    /**
     * REGRESSION TEST BUG #14:
     * Dashboard metric "Due Today" must only count tasks with status 'pending'.
     * Completed tasks due today must not be counted as due today.
     */
    public function test_bug_14_dashboard_metric_due_today_only_counts_pending_tasks(): void
    {
        $user = User::factory()->create();

        // Pending task due today -> SHOULD be counted in Due Today
        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Pending Hari Ini',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        // Completed task due today -> MUST NOT be counted in Due Today
        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Selesai Hari Ini',
            'status' => 'completed',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        // Pending task overdue -> SHOULD be counted in Overdue, NOT Due Today
        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Pending Kemarin (Overdue)',
            'status' => 'pending',
            'due_date' => Carbon::yesterday()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertOk();

        // Due today metric must be 1 (only the pending task due today)
        $this->assertSame(1, $response->viewData('dueTodayTasks'), 'dueTodayTasks should only count pending tasks.');
        $this->assertSame(1, $response->viewData('dueToday'), 'dueToday should only count pending tasks.');

        // Overdue must be 1
        $this->assertSame(1, $response->viewData('overdueTasks'));

        // Pending must be 2 (1 due today pending + 1 overdue pending)
        $this->assertSame(2, $response->viewData('pendingTasks'));

        // Completed must be 1
        $this->assertSame(1, $response->viewData('completedTasks'));
    }
}
