<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskDueDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_can_be_created_with_nullable_due_date(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Tanpa Deadline',
            'status' => 'pending',
            'due_date' => null,
        ]);

        $this->assertNull($task->due_date);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'due_date' => null,
        ]);
    }

    public function test_task_due_date_is_cast_to_carbon_instance(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Dengan Deadline',
            'status' => 'pending',
            'due_date' => '2026-10-25',
        ]);

        $this->assertInstanceOf(Carbon::class, $task->fresh()->due_date);
        $this->assertSame('2026-10-25', $task->fresh()->due_date->format('Y-m-d'));
    }

    public function test_user_can_create_task_with_valid_due_date(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/tasks', [
                'title' => 'Task Baru Berdeadline',
                'description' => 'Selesaikan sebelum tanggal target',
                'status' => 'pending',
                'priority' => 'high',
                'due_date' => '2026-11-01',
            ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Task Baru Berdeadline',
            'due_date' => '2026-11-01',
        ]);
    }

    public function test_user_can_create_task_without_due_date(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/tasks', [
                'title' => 'Task Tanpa Due Date',
                'status' => 'pending',
                'priority' => 'medium',
                'due_date' => null,
            ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Task Tanpa Due Date',
            'due_date' => null,
        ]);
    }

    public function test_create_task_validation_fails_with_invalid_due_date_format(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/tasks', [
                'title' => 'Task Format Tanggal Salah',
                'status' => 'pending',
                'due_date' => 'bukan-format-tanggal',
            ]);

        $response->assertSessionHasErrors('due_date');
        $this->assertDatabaseMissing('tasks', [
            'title' => 'Task Format Tanggal Salah',
        ]);
    }

    public function test_create_task_validation_fails_with_array_due_date(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/tasks', [
                'title' => 'Task Array Due Date',
                'status' => 'pending',
                'due_date' => ['2026-10-10'],
            ]);

        $response->assertSessionHasErrors('due_date');
        $this->assertDatabaseMissing('tasks', [
            'title' => 'Task Array Due Date',
        ]);
    }

    public function test_user_can_update_task_due_date(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Update Deadline',
            'status' => 'pending',
            'due_date' => '2026-10-01',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('tasks.update', $task), [
                'title' => 'Task Update Deadline',
                'status' => 'pending',
                'due_date' => '2026-12-15',
            ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'due_date' => '2026-12-15',
        ]);
    }

    public function test_user_can_clear_task_due_date(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Hapus Deadline',
            'status' => 'pending',
            'due_date' => '2026-10-01',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('tasks.update', $task), [
                'title' => 'Task Hapus Deadline',
                'status' => 'pending',
                'due_date' => null,
            ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'due_date' => null,
        ]);
    }

    public function test_update_task_validation_fails_with_invalid_due_date(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Valid Awal',
            'status' => 'pending',
            'due_date' => '2026-10-01',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('tasks.update', $task), [
                'title' => 'Task Valid Awal',
                'status' => 'pending',
                'due_date' => 'invalid-date-string',
            ]);

        $response->assertSessionHasErrors('due_date');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'due_date' => '2026-10-01',
        ]);
    }

    public function test_user_cannot_update_due_date_of_another_users_task(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $taskA = Task::create([
            'user_id' => $userA->id,
            'title' => 'Task A',
            'status' => 'pending',
            'due_date' => '2026-10-01',
        ]);

        $response = $this
            ->actingAs($userB)
            ->put(route('tasks.update', $taskA), [
                'title' => 'Task A Diserang',
                'status' => 'pending',
                'due_date' => '2026-12-31',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $taskA->id,
            'due_date' => '2026-10-01',
        ]);
    }

    public function test_user_can_filter_tasks_by_deadline_today(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Hari Ini',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Mendatang Besok',
            'status' => 'pending',
            'due_date' => Carbon::tomorrow()->toDateString(),
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Terlambat Kemarin',
            'status' => 'pending',
            'due_date' => Carbon::yesterday()->toDateString(),
        ]);

        // Test with "today"
        $response = $this->actingAs($user)->get('/tasks?deadline=today');
        $response->assertOk();
        $response->assertSee('Tugas Hari Ini');
        $response->assertDontSee('Tugas Mendatang Besok');
        $response->assertDontSee('Tugas Terlambat Kemarin');

        // Test with Indonesian alias "hari_ini"
        $responseAlias = $this->actingAs($user)->get('/tasks?deadline=hari_ini');
        $responseAlias->assertOk();
        $responseAlias->assertSee('Tugas Hari Ini');
        $responseAlias->assertDontSee('Tugas Mendatang Besok');
        $responseAlias->assertDontSee('Tugas Terlambat Kemarin');
    }

    public function test_user_can_filter_tasks_by_deadline_upcoming(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Hari Ini',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Mendatang Lusa',
            'status' => 'pending',
            'due_date' => Carbon::today()->addDays(2)->toDateString(),
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Terlambat Kemarin',
            'status' => 'pending',
            'due_date' => Carbon::yesterday()->toDateString(),
        ]);

        // Test with "upcoming"
        $response = $this->actingAs($user)->get('/tasks?deadline=upcoming');
        $response->assertOk();
        $response->assertSee('Tugas Mendatang Lusa');
        $response->assertDontSee('Tugas Hari Ini');
        $response->assertDontSee('Tugas Terlambat Kemarin');

        // Test with Indonesian alias "mendatang"
        $responseAlias = $this->actingAs($user)->get('/tasks?deadline=mendatang');
        $responseAlias->assertOk();
        $responseAlias->assertSee('Tugas Mendatang Lusa');
        $responseAlias->assertDontSee('Tugas Hari Ini');
        $responseAlias->assertDontSee('Tugas Terlambat Kemarin');
    }

    public function test_user_can_filter_tasks_by_deadline_overdue(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Hari Ini',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Mendatang Besok',
            'status' => 'pending',
            'due_date' => Carbon::tomorrow()->toDateString(),
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Terlambat Lampau',
            'status' => 'pending',
            'due_date' => Carbon::today()->subDays(3)->toDateString(),
        ]);

        // Test with "overdue"
        $response = $this->actingAs($user)->get('/tasks?deadline=overdue');
        $response->assertOk();
        $response->assertSee('Tugas Terlambat Lampau');
        $response->assertDontSee('Tugas Hari Ini');
        $response->assertDontSee('Tugas Mendatang Besok');

        // Test with Indonesian alias "terlambat"
        $responseAlias = $this->actingAs($user)->get('/tasks?deadline=terlambat');
        $responseAlias->assertOk();
        $responseAlias->assertSee('Tugas Terlambat Lampau');
        $responseAlias->assertDontSee('Tugas Hari Ini');
        $responseAlias->assertDontSee('Tugas Mendatang Besok');
    }

    public function test_deadline_filter_respects_user_isolation(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Task::create([
            'user_id' => $userA->id,
            'title' => 'Task Hari Ini User A',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        Task::create([
            'user_id' => $userB->id,
            'title' => 'Task Hari Ini User B',
            'status' => 'pending',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        $response = $this->actingAs($userA)->get('/tasks?deadline=today');
        $response->assertOk();
        $response->assertSee('Task Hari Ini User A');
        $response->assertDontSee('Task Hari Ini User B');
    }

    public function test_deadline_filter_works_together_with_search_status_and_priority(): void
    {
        $user = User::factory()->create();

        // Matching: title contains 'Sprint', status 'pending', priority 'high', deadline 'today'
        Task::create([
            'user_id' => $user->id,
            'title' => 'Sprint Planning Final',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        // Mismatch deadline (tomorrow)
        Task::create([
            'user_id' => $user->id,
            'title' => 'Sprint Review Prep',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => Carbon::tomorrow()->toDateString(),
        ]);

        // Mismatch priority ('low')
        Task::create([
            'user_id' => $user->id,
            'title' => 'Sprint Documentation',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => Carbon::today()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/tasks?search=Sprint&status=pending&priority=high&deadline=today');
        $response->assertOk();
        $response->assertSee('Sprint Planning Final');
        $response->assertDontSee('Sprint Review Prep');
        $response->assertDontSee('Sprint Documentation');
    }

    public function test_user_can_sort_tasks_by_deadline_asc_and_desc(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Deadline Lusa',
            'status' => 'pending',
            'due_date' => Carbon::today()->addDays(2)->toDateString(),
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Deadline Besok',
            'status' => 'pending',
            'due_date' => Carbon::today()->addDays(1)->toDateString(),
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Deadline Minggu Depan',
            'status' => 'pending',
            'due_date' => Carbon::today()->addDays(7)->toDateString(),
        ]);

        // Nearest / ASC
        $responseAsc = $this->actingAs($user)->get('/tasks?sort=due_date_asc');
        $responseAsc->assertOk();
        $responseAsc->assertSeeInOrder([
            'Task Deadline Besok',
            'Task Deadline Lusa',
            'Task Deadline Minggu Depan',
        ]);

        // Indonesian alias
        $responseAscAlias = $this->actingAs($user)->get('/tasks?sort=deadline_terdekat');
        $responseAscAlias->assertOk();
        $responseAscAlias->assertSeeInOrder([
            'Task Deadline Besok',
            'Task Deadline Lusa',
            'Task Deadline Minggu Depan',
        ]);

        // Furthest / DESC
        $responseDesc = $this->actingAs($user)->get('/tasks?sort=due_date_desc');
        $responseDesc->assertOk();
        $responseDesc->assertSeeInOrder([
            'Task Deadline Minggu Depan',
            'Task Deadline Lusa',
            'Task Deadline Besok',
        ]);

        // Indonesian alias
        $responseDescAlias = $this->actingAs($user)->get('/tasks?sort=deadline_terjauh');
        $responseDescAlias->assertOk();
        $responseDescAlias->assertSeeInOrder([
            'Task Deadline Minggu Depan',
            'Task Deadline Lusa',
            'Task Deadline Besok',
        ]);
    }

    public function test_deadline_query_validation_fails_on_invalid_value_or_array(): void
    {
        $user = User::factory()->create();

        $responseInvalid = $this->actingAs($user)->get('/tasks?deadline=random_string');
        $responseInvalid->assertStatus(302);
        $responseInvalid->assertSessionHasErrors('deadline');

        $responseArray = $this->actingAs($user)->get('/tasks?deadline[]=today');
        $responseArray->assertStatus(302);
        $responseArray->assertSessionHasErrors('deadline');
    }

    public function test_deadline_is_rendered_in_index_detail_and_dashboard(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Target Khusus',
            'description' => 'Target Akhir Bulan',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => Carbon::today()->addDays(5)->toDateString(),
        ]);

        $formattedDate = $task->fresh()->due_date->format('d M Y');

        // Index page
        $indexRes = $this->actingAs($user)->get('/tasks');
        $indexRes->assertOk();
        $indexRes->assertSee('Task Target Khusus');
        $indexRes->assertSee($formattedDate);

        // Show page
        $showRes = $this->actingAs($user)->get(route('tasks.show', $task));
        $showRes->assertOk();
        $showRes->assertSee('Task Target Khusus');
        $showRes->assertSee('Deadline');
        $showRes->assertSee($formattedDate);

        // Dashboard
        $dashRes = $this->actingAs($user)->get('/dashboard');
        $dashRes->assertOk();
        $dashRes->assertSee('Task Target Khusus');
        $dashRes->assertSee($formattedDate);
    }

    public function test_create_and_edit_screens_render_due_date_field(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Edit Deadline',
            'status' => 'pending',
            'due_date' => '2026-11-20',
        ]);

        $createRes = $this->actingAs($user)->get('/tasks/create');
        $createRes->assertOk();
        $createRes->assertSee('name="due_date"', false);
        $createRes->assertSee('type="date"', false);

        $editRes = $this->actingAs($user)->get(route('tasks.edit', $task));
        $editRes->assertOk();
        $editRes->assertSee('name="due_date"', false);
        $editRes->assertSee('value="2026-11-20"', false);
    }
}
