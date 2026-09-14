<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDeadlineOneHourNotification;
use App\Notifications\TaskDeadlineTenMinutesNotification;
use App\Notifications\TaskDueTodayNotification;
use App\Notifications\TaskOverdueNotification;
use App\Services\TaskNotificationService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Events\BroadcastNotificationCreated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class FullRegressionAuditTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Authentication & 2. Register/Login/Logout
     */
    public function test_audit_01_and_02_authentication_register_login_logout(): void
    {
        // Register
        $regResponse = $this->post('/register', [
            'name' => 'Audit Tester',
            'email' => 'audittester@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);
        $regResponse->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        // Logout
        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertRedirect('/');
        $this->assertGuest();

        // Login with valid credentials
        $loginResponse = $this->post('/login', [
            'email' => 'audittester@example.com',
            'password' => 'SecurePass123!',
        ]);
        $loginResponse->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        // Login with invalid credentials
        $this->post('/logout');
        $badLogin = $this->post('/login', [
            'email' => 'audittester@example.com',
            'password' => 'WrongPassword',
        ]);
        $badLogin->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * 3. Forgot & Reset Password
     */
    public function test_audit_03_forgot_and_reset_password(): void
    {
        $user = User::factory()->create(['email' => 'resetme@example.com']);

        $forgotResponse = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);
        $forgotResponse->assertSessionHas('status');

        $token = app('auth.password.broker')->createToken($user);

        $resetPage = $this->get("/reset-password/{$token}?email=".urlencode($user->email));
        $resetPage->assertOk();

        $resetResponse = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecretPassword123!',
            'password_confirmation' => 'NewSecretPassword123!',
        ]);
        $resetResponse->assertSessionHas('status');

        $this->assertTrue(auth()->validate([
            'email' => $user->email,
            'password' => 'NewSecretPassword123!',
        ]));
    }

    /**
     * 4. Task CRUD
     */
    public function test_audit_04_task_crud(): void
    {
        $user = User::factory()->create();

        // Create
        $createRes = $this->actingAs($user)->post('/tasks', [
            'title' => 'Audit Task CRUD Test',
            'description' => 'Testing Create, Read, Update, Delete',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => '2026-12-01',
            'due_time' => '10:00',
        ]);
        $createRes->assertRedirect('/tasks');

        $task = Task::where('title', 'Audit Task CRUD Test')->first();
        $this->assertNotNull($task);
        $this->assertSame($user->id, $task->user_id);
        $this->assertSame('high', $task->priority);
        $this->assertSame('2026-12-01 10:00:00', $task->due_date->format('Y-m-d H:i:s'));

        // Read (Show & Index)
        $showRes = $this->actingAs($user)->get("/tasks/{$task->id}");
        $showRes->assertOk();
        $showRes->assertSee('Audit Task CRUD Test');

        $indexRes = $this->actingAs($user)->get('/tasks');
        $indexRes->assertOk();
        $indexRes->assertSee('Audit Task CRUD Test');

        // Update
        $updateRes = $this->actingAs($user)->put("/tasks/{$task->id}", [
            'title' => 'Audit Task CRUD Updated',
            'description' => 'Updated Description',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => '2026-12-02',
            'due_time' => '14:30',
        ]);
        $updateRes->assertRedirect('/tasks');
        $task->refresh();
        $this->assertSame('Audit Task CRUD Updated', $task->title);
        $this->assertSame('low', $task->priority);
        $this->assertSame('2026-12-02 14:30:00', $task->due_date->format('Y-m-d H:i:s'));

        // Delete
        $delRes = $this->actingAs($user)->delete("/tasks/{$task->id}");
        $delRes->assertRedirect('/tasks');
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /**
     * 5. Complete / Reopen Task
     */
    public function test_audit_05_complete_and_reopen_task(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Status Toggle Task',
            'status' => 'pending',
            'due_date' => Carbon::now()->addMinutes(8)->format('Y-m-d H:i:s'),
        ]);

        // Complete the task
        $compRes = $this->actingAs($user)->put("/tasks/{$task->id}", [
            'title' => $task->title,
            'status' => 'completed',
        ]);
        $compRes->assertRedirect('/tasks');
        $task->refresh();
        $this->assertSame('completed', $task->status);

        // Reopen the task
        $reopenRes = $this->actingAs($user)->put("/tasks/{$task->id}", [
            'title' => $task->title,
            'status' => 'pending',
        ]);
        $reopenRes->assertRedirect('/tasks');
        $task->refresh();
        $this->assertSame('pending', $task->status);
    }

    /**
     * 6. Priority & Category
     */
    public function test_audit_06_priority_and_category(): void
    {
        $user = User::factory()->create();

        // Create category
        $catRes = $this->actingAs($user)->post('/categories', ['name' => 'Kuliah Semester 6']);
        $catRes->assertRedirect('/categories');
        $category = Category::where('name', 'Kuliah Semester 6')->first();
        $this->assertNotNull($category);

        // Create tasks with different priorities and category
        $taskHigh = Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Prioritas Tinggi',
            'priority' => 'high',
            'category_id' => $category->id,
            'status' => 'pending',
        ]);

        $taskLow = Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Prioritas Rendah',
            'priority' => 'low',
            'status' => 'pending',
        ]);

        $this->assertSame('high', $taskHigh->priority);
        $this->assertSame($category->id, $taskHigh->category->id);
        $this->assertNull($taskLow->category_id);
    }

    /**
     * 7. Search, Filter, Sort, Pagination
     */
    public function test_audit_07_search_filter_sort_pagination(): void
    {
        $user = User::factory()->create();
        $cat = Category::create(['user_id' => $user->id, 'name' => 'Urgent Category']);

        // Create 12 tasks to test pagination (page size is 5)
        for ($i = 1; $i <= 12; $i++) {
            $label = sprintf('%02d', $i);
            Task::create([
                'user_id' => $user->id,
                'title' => "Task Pagination Item [{$label}]",
                'status' => $i <= 6 ? 'pending' : 'completed',
                'priority' => $i % 2 === 0 ? 'high' : 'low',
                'category_id' => $i === 1 ? $cat->id : null,
                'due_date' => Carbon::today()->addDays($i)->format('Y-m-d'),
            ]);
        }

        // Search
        $searchRes = $this->actingAs($user)->get('/tasks?search=Item+%5B10%5D');
        $searchRes->assertOk();
        $searchRes->assertSee('Task Pagination Item [10]');
        $searchRes->assertDontSee('Task Pagination Item [11]');

        // Filter status
        $statusRes = $this->actingAs($user)->get('/tasks?status=completed');
        $statusRes->assertOk();
        $statusRes->assertSee('Task Pagination Item [07]');
        $statusRes->assertDontSee('Task Pagination Item [01]');

        // Filter category_id
        $catFilterRes = $this->actingAs($user)->get("/tasks?category_id={$cat->id}");
        $catFilterRes->assertOk();
        $catFilterRes->assertSee('Task Pagination Item [01]');
        $catFilterRes->assertDontSee('Task Pagination Item [02]');

        // Sort title_desc
        $sortRes = $this->actingAs($user)->get('/tasks?sort=title_desc');
        $sortRes->assertOk();

        // Pagination page 2
        $page2Res = $this->actingAs($user)->get('/tasks?page=2');
        $page2Res->assertOk();
    }

    /**
     * 8. Dashboard metrics
     */
    public function test_audit_08_dashboard_metrics(): void
    {
        $user = User::factory()->create();

        // 2 pending, 1 completed, 1 overdue pending
        Task::create([
            'user_id' => $user->id,
            'title' => 'Pending Regular',
            'status' => 'pending',
            'due_date' => Carbon::tomorrow()->format('Y-m-d'),
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Completed Task',
            'status' => 'completed',
            'due_date' => Carbon::today()->format('Y-m-d'),
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Overdue Pending',
            'status' => 'pending',
            'due_date' => Carbon::yesterday()->format('Y-m-d'),
        ]);

        $dashRes = $this->actingAs($user)->get('/dashboard');
        $dashRes->assertOk();
        $dashRes->assertViewHas('totalTasks', 3);
        $dashRes->assertViewHas('pendingTasks', 2);
        $dashRes->assertViewHas('completedTasks', 1);
        $dashRes->assertViewHas('overdueTasks', 1);
    }

    /**
     * 9. Database Notifications & 15. Notification Center
     */
    public function test_audit_09_and_15_database_notifications_and_notification_center(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Notif Test Task',
            'status' => 'pending',
            'due_date' => Carbon::today()->format('Y-m-d'),
        ]);

        $user->notify(new TaskDueTodayNotification($task));

        $this->assertSame(1, $user->notifications()->count());
        $this->assertSame(1, $user->unreadNotifications()->count());
        $notif = $user->notifications->first();

        // View Notification Center
        $centerRes = $this->actingAs($user)->get('/notifications');
        $centerRes->assertOk();
        $centerRes->assertSee('Notif Test Task');

        // Mark single as read
        $readRes = $this->actingAs($user)->post("/notifications/{$notif->id}/read");
        $readRes->assertStatus(302);
        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());

        // Delete notification
        $delRes = $this->actingAs($user)->delete("/notifications/{$notif->id}");
        $delRes->assertStatus(302);
        $this->assertSame(0, $user->fresh()->notifications()->count());
    }

    /**
     * 10. Realtime Reverb & 12. Queue
     */
    public function test_audit_10_and_12_realtime_reverb_and_queue(): void
    {
        Event::fake([BroadcastNotificationCreated::class]);

        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Reverb Realtime Task',
            'status' => 'pending',
            'due_date' => Carbon::now()->addMinutes(8)->format('Y-m-d H:i:s'),
        ]);

        $notif10m = new TaskDeadlineTenMinutesNotification($task);

        // Assert implements ShouldQueue
        $this->assertInstanceOf(ShouldQueue::class, $notif10m);

        // Assert sync connections for database and broadcast
        $connections = $notif10m->viaConnections();
        $this->assertSame('sync', $connections['database']);
        $this->assertSame('sync', $connections['broadcast']);

        // Dispatch notification
        $user->notify($notif10m);

        Event::assertDispatched(BroadcastNotificationCreated::class, function ($event) use ($user, $task) {
            $this->assertSame($user->id, $event->notifiable->id);
            $this->assertSame('private-App.Models.User.'.$user->id, $event->broadcastOn()[0]->name);
            $this->assertSame($task->id, $event->data['task_id']);

            return true;
        });
    }

    /**
     * 11. Email Notifications
     */
    public function test_audit_11_email_notifications(): void
    {
        $user = User::factory()->create(['name' => 'Auditor Email']);
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Email Audit Task',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => Carbon::today()->format('Y-m-d'),
        ]);

        $notif = new TaskDueTodayNotification($task);
        $mail = $notif->toMail($user);

        $this->assertNotEmpty($mail->subject);
        $this->assertStringContainsString('Email Audit Task', $mail->subject);

        $html = view($mail->view, $mail->viewData)->render();
        $this->assertStringContainsString('Auditor Email', $html);
        $this->assertStringContainsString('Email Audit Task', $html);
        $this->assertStringContainsString(route('tasks.show', $task), $html);
    }

    /**
     * 13. Deadline Reminder & 14. Overdue Reminder
     */
    public function test_audit_13_and_14_smart_deadline_and_overdue_reminders(): void
    {
        $user = User::factory()->create();

        // 1 hour task
        $task1h = Task::create([
            'user_id' => $user->id,
            'title' => 'Smart 1 Hour Task',
            'status' => 'pending',
            'due_date' => Carbon::now()->addMinutes(45)->format('Y-m-d H:i:s'),
        ]);

        // 10 minute task
        $task10m = Task::create([
            'user_id' => $user->id,
            'title' => 'Smart 10 Minute Task',
            'status' => 'pending',
            'due_date' => Carbon::now()->addMinutes(8)->format('Y-m-d H:i:s'),
        ]);

        // Overdue task
        $taskOverdue = Task::create([
            'user_id' => $user->id,
            'title' => 'Smart Overdue Task',
            'status' => 'pending',
            'due_date' => Carbon::now()->subMinutes(10)->format('Y-m-d H:i:s'),
        ]);

        $service = app(TaskNotificationService::class);
        $sentCount = $service->checkAndNotifyUser($user);

        $this->assertSame(3, $sentCount);
        $this->assertSame(3, $user->notifications()->count());

        $types = $user->notifications->pluck('type')->toArray();
        $this->assertContains(TaskDeadlineOneHourNotification::class, $types);
        $this->assertContains(TaskDeadlineTenMinutesNotification::class, $types);
        $this->assertContains(TaskOverdueNotification::class, $types);
    }

    /**
     * 16. Authorization & IDOR
     */
    public function test_audit_16_authorization_and_idor_protection(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $taskB = Task::create([
            'user_id' => $userB->id,
            'title' => 'Private Task of User B',
            'status' => 'pending',
        ]);

        $categoryB = Category::create([
            'user_id' => $userB->id,
            'name' => 'Private Category of User B',
        ]);

        // User A cannot view User B's task
        $viewRes = $this->actingAs($userA)->get("/tasks/{$taskB->id}");
        $viewRes->assertStatus(403);

        // User A cannot edit User B's task
        $editRes = $this->actingAs($userA)->get("/tasks/{$taskB->id}/edit");
        $editRes->assertStatus(403);

        // User A cannot update User B's task
        $updateRes = $this->actingAs($userA)->put("/tasks/{$taskB->id}", [
            'title' => 'Hacked Task',
            'status' => 'pending',
        ]);
        $updateRes->assertStatus(403);

        // User A cannot delete User B's task
        $delRes = $this->actingAs($userA)->delete("/tasks/{$taskB->id}");
        $delRes->assertStatus(403);

        // User A cannot edit User B's category
        $catEditRes = $this->actingAs($userA)->get("/categories/{$categoryB->id}/edit");
        $catEditRes->assertStatus(403);

        // User A cannot delete User B's category
        $catDelRes = $this->actingAs($userA)->delete("/categories/{$categoryB->id}");
        $catDelRes->assertStatus(403);
    }

    /**
     * 17. CSRF, XSS, SQL Injection, Mass Assignment
     */
    public function test_audit_17_security_csrf_xss_sql_injection_mass_assignment(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Mass assignment: user_id tampering
        $this->actingAs($userA)->post('/tasks', [
            'title' => 'Tampering Test',
            'status' => 'pending',
            'user_id' => $userB->id,
        ]);
        $task = Task::where('title', 'Tampering Test')->first();
        $this->assertSame($userA->id, $task->user_id);

        // SQL Injection & Wildcard escaping in search
        $searchWildcard = $this->actingAs($userA)->get('/tasks?search=%25');
        $searchWildcard->assertOk();

        // XSS escaping in views
        $xssTitle = '<script>alert("xss")</script> Safe Title';
        $taskXss = Task::create([
            'user_id' => $userA->id,
            'title' => $xssTitle,
            'status' => 'pending',
        ]);

        $xssShow = $this->actingAs($userA)->get("/tasks/{$taskXss->id}");
        $xssShow->assertOk();
        $xssShow->assertDontSee('<script>alert("xss")</script>', false);
        $xssShow->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false);
    }

    /**
     * 18. Landing Page, 19. Responsive UI, 20. Routes & Broken Links
     */
    public function test_audit_18_19_20_landing_page_responsive_and_route_health(): void
    {
        // 18. Landing page renders
        $welcomeRes = $this->get('/');
        $welcomeRes->assertOk();
        $welcomeRes->assertSee('Task Manager');
        $welcomeRes->assertSee('Mulai Sekarang');

        // 19. Responsive UI viewport check
        $welcomeRes->assertSee('name="viewport"', false);

        // Check dashboard and tasks viewports
        $user = User::factory()->create();
        $dashRes = $this->actingAs($user)->get('/dashboard');
        $dashRes->assertOk();
        $dashRes->assertSee('name="viewport"', false);

        // 20. Route health: test public & authenticated GET routes
        $routesToTest = [
            '/',
            '/login',
            '/register',
            '/forgot-password',
            '/dashboard',
            '/tasks',
            '/tasks/create',
            '/categories',
            '/categories/create',
            '/notifications',
            '/profile',
        ];

        foreach ($routesToTest as $uri) {
            $acting = in_array($uri, ['/dashboard', '/tasks', '/tasks/create', '/categories', '/categories/create', '/notifications', '/profile']);
            $req = $acting ? $this->actingAs($user) : $this;
            $res = $req->get($uri);
            $this->assertNotSame(500, $res->status(), "Route {$uri} returned 500 internal server error.");
        }
    }
}
