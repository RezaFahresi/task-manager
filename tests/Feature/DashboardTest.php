<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_can_be_rendered_for_authenticated_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Budi Santoso',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee('Total Task');
        $response->assertSee('Pending');
        $response->assertSee('Completed');
        $response->assertSee('Overdue');
        $response->assertSee('Due Today');
        $response->assertSee('Completion Rate');
        $response->assertSee('Upcoming Tasks');
        $response->assertSee('Recent Tasks');
        $response->assertSee('Ringkasan Priority');
        $response->assertSee('Ringkasan Category');
        $response->assertSee('Lihat Semua Task');
        $response->assertSee('Tambah Task');
    }

    public function test_dashboard_displays_correct_task_counts(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Pending 1',
            'status' => 'pending',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Pending 2',
            'status' => 'pending',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Selesai 1',
            'status' => 'completed',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertOk();
        // Total: 3, Pending: 2, Completed: 1
        $response->assertSee('3');
        $response->assertSee('2');
        $response->assertSee('1');
        $response->assertViewHas('totalTasks', 3);
        $response->assertViewHas('pendingTasks', 2);
        $response->assertViewHas('completedTasks', 1);
    }

    public function test_dashboard_displays_recent_tasks_and_respects_user_isolation(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Task::create([
            'user_id' => $userA->id,
            'title' => 'Task Milik User A',
            'description' => 'Deskripsi task A',
            'status' => 'pending',
        ]);

        Task::create([
            'user_id' => $userB->id,
            'title' => 'Task Milik User B',
            'description' => 'Deskripsi task B',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($userA)
            ->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Task Milik User A');
        $response->assertDontSee('Task Milik User B');
    }

    public function test_dashboard_displays_empty_state_when_no_tasks(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Belum Ada Task');
        $response->assertSee('Tambah Task Pertama');
        $response->assertViewHas('totalTasks', 0);
        $response->assertViewHas('completionRate', 0);
    }

    public function test_dashboard_calculates_overdue_tasks_correctly_and_respects_user_isolation(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Overdue pending task for User A
        Task::create([
            'user_id' => $userA->id,
            'title' => 'Task Terlambat User A',
            'status' => 'pending',
            'due_date' => Carbon::yesterday()->toDateString(),
        ]);

        // Completed task past due date should NOT be counted as overdue
        Task::create([
            'user_id' => $userA->id,
            'title' => 'Task Lampau Selesai User A',
            'status' => 'completed',
            'due_date' => Carbon::yesterday()->toDateString(),
        ]);

        // Future pending task should NOT be counted as overdue
        Task::create([
            'user_id' => $userA->id,
            'title' => 'Task Masa Depan User A',
            'status' => 'pending',
            'due_date' => Carbon::tomorrow()->toDateString(),
        ]);

        // Overdue pending task for User B (must be isolated)
        Task::create([
            'user_id' => $userB->id,
            'title' => 'Task Terlambat User B',
            'status' => 'pending',
            'due_date' => Carbon::yesterday()->toDateString(),
        ]);

        $response = $this->actingAs($userA)->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('overdueTasks', 1);
        $response->assertViewHas('overdue', 1);
    }

    public function test_dashboard_calculates_due_today_tasks_correctly_and_respects_user_isolation(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Tasks due today for User A
        Task::create([
            'user_id' => $userA->id,
            'title' => 'Task Hari Ini 1 User A',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        Task::create([
            'user_id' => $userA->id,
            'title' => 'Task Hari Ini 2 User A',
            'status' => 'completed',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        // Task due tomorrow for User A
        Task::create([
            'user_id' => $userA->id,
            'title' => 'Task Besok User A',
            'status' => 'pending',
            'due_date' => Carbon::tomorrow()->toDateString(),
        ]);

        // Task due today for User B (isolated)
        Task::create([
            'user_id' => $userB->id,
            'title' => 'Task Hari Ini User B',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        $response = $this->actingAs($userA)->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('dueTodayTasks', 2);
        $response->assertViewHas('dueToday', 2);
    }

    public function test_dashboard_calculates_completion_rate_accurately(): void
    {
        $user = User::factory()->create();

        // 3 completed, 1 pending => 75%
        Task::create(['user_id' => $user->id, 'title' => 'Task 1', 'status' => 'completed']);
        Task::create(['user_id' => $user->id, 'title' => 'Task 2', 'status' => 'completed']);
        Task::create(['user_id' => $user->id, 'title' => 'Task 3', 'status' => 'completed']);
        Task::create(['user_id' => $user->id, 'title' => 'Task 4', 'status' => 'pending']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('completionRate', 75);
        $response->assertSee('75%');
    }

    public function test_dashboard_displays_upcoming_tasks_ordered_by_deadline_and_isolated(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $category = Category::create(['user_id' => $userA->id, 'name' => 'Backend']);

        // Soonest upcoming task
        Task::create([
            'user_id' => $userA->id,
            'title' => 'Upcoming Besok',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => Carbon::today()->addDay()->toDateString(),
            'category_id' => $category->id,
        ]);

        // Later upcoming task
        Task::create([
            'user_id' => $userA->id,
            'title' => 'Upcoming Minggu Depan',
            'status' => 'pending',
            'priority' => 'medium',
            'due_date' => Carbon::today()->addDays(7)->toDateString(),
        ]);

        // Completed task should not appear in upcoming
        Task::create([
            'user_id' => $userA->id,
            'title' => 'Upcoming Tapi Selesai',
            'status' => 'completed',
            'due_date' => Carbon::today()->addDays(3)->toDateString(),
        ]);

        // Past task should not appear in upcoming
        Task::create([
            'user_id' => $userA->id,
            'title' => 'Task Kemarin',
            'status' => 'pending',
            'due_date' => Carbon::yesterday()->toDateString(),
        ]);

        // User B's upcoming task
        Task::create([
            'user_id' => $userB->id,
            'title' => 'Upcoming Milik User B',
            'status' => 'pending',
            'due_date' => Carbon::today()->addDays(2)->toDateString(),
        ]);

        $response = $this->actingAs($userA)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Upcoming Besok');
        $response->assertSee('Upcoming Minggu Depan');
        $response->assertDontSee('Upcoming Milik User B');

        $upcomingTasks = $response->viewData('upcomingTasks');
        $this->assertCount(2, $upcomingTasks);
        $this->assertSame('Upcoming Besok', $upcomingTasks->first()->title);
        $this->assertSame('Upcoming Minggu Depan', $upcomingTasks->last()->title);
        $this->assertFalse($upcomingTasks->contains('title', 'Upcoming Tapi Selesai'));
        $this->assertFalse($upcomingTasks->contains('title', 'Task Kemarin'));
        $this->assertFalse($upcomingTasks->contains('title', 'Upcoming Milik User B'));
    }

    public function test_dashboard_displays_priority_summary_and_respects_user_isolation(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Task::create(['user_id' => $userA->id, 'title' => 'High 1', 'priority' => 'high']);
        Task::create(['user_id' => $userA->id, 'title' => 'High 2', 'priority' => 'high']);
        Task::create(['user_id' => $userA->id, 'title' => 'Medium 1', 'priority' => 'medium']);
        Task::create(['user_id' => $userA->id, 'title' => 'Low 1', 'priority' => 'low']);

        // User B's high priority task (must not be added to User A's counts)
        Task::create(['user_id' => $userB->id, 'title' => 'High User B', 'priority' => 'high']);

        $response = $this->actingAs($userA)->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('prioritySummary', [
            'high' => 2,
            'medium' => 1,
            'low' => 1,
        ]);
        $response->assertSee('2 task');
        $response->assertSee('1 task');
    }

    public function test_dashboard_displays_category_summary_and_respects_user_isolation(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $catWork = Category::create(['user_id' => $userA->id, 'name' => 'Work']);
        $catPersonal = Category::create(['user_id' => $userA->id, 'name' => 'Personal']);
        $catSecretB = Category::create(['user_id' => $userB->id, 'name' => 'Secret B']);

        Task::create(['user_id' => $userA->id, 'title' => 'Work 1', 'category_id' => $catWork->id]);
        Task::create(['user_id' => $userA->id, 'title' => 'Work 2', 'category_id' => $catWork->id]);
        Task::create(['user_id' => $userA->id, 'title' => 'Personal 1', 'category_id' => $catPersonal->id]);
        Task::create(['user_id' => $userB->id, 'title' => 'Secret Task', 'category_id' => $catSecretB->id]);

        $response = $this->actingAs($userA)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Work');
        $response->assertSee('Personal');
        $response->assertDontSee('Secret B');

        $categorySummary = $response->viewData('categorySummary');
        $this->assertCount(2, $categorySummary);
        $this->assertSame(2, $categorySummary->firstWhere('name', 'Work')->tasks_count);
        $this->assertSame(1, $categorySummary->firstWhere('name', 'Personal')->tasks_count);
    }
}
