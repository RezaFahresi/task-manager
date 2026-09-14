<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPriorityTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_has_default_priority_medium_when_not_specified(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Default Priority',
            'status' => 'pending',
        ]);

        $this->assertSame('medium', $task->priority);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'priority' => 'medium',
        ]);
    }

    public function test_user_can_create_task_with_priority_low_medium_high(): void
    {
        $user = User::factory()->create();

        foreach (['low', 'medium', 'high'] as $priority) {
            $response = $this
                ->actingAs($user)
                ->post('/tasks', [
                    'title' => "Task {$priority}",
                    'description' => "Deskripsi {$priority}",
                    'status' => 'pending',
                    'priority' => $priority,
                ]);

            $response->assertRedirect(route('tasks.index'));
            $response->assertSessionHas('success');

            $this->assertDatabaseHas('tasks', [
                'user_id' => $user->id,
                'title' => "Task {$priority}",
                'priority' => $priority,
            ]);
        }
    }

    public function test_create_task_validation_fails_with_invalid_priority(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/tasks', [
                'title' => 'Task Priority Invalid',
                'status' => 'pending',
                'priority' => 'critical_urgent',
            ]);

        $response->assertSessionHasErrors('priority');
        $this->assertDatabaseMissing('tasks', [
            'title' => 'Task Priority Invalid',
        ]);
    }

    public function test_user_can_update_task_priority(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Awal',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('tasks.update', $task), [
                'title' => 'Task Awal Updated',
                'status' => 'pending',
                'priority' => 'high',
            ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'priority' => 'high',
        ]);
    }

    public function test_update_task_validation_fails_with_invalid_priority(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Valid',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('tasks.update', $task), [
                'title' => 'Task Valid',
                'status' => 'pending',
                'priority' => 'super_high',
            ]);

        $response->assertSessionHasErrors('priority');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'priority' => 'medium',
        ]);
    }

    public function test_user_cannot_update_priority_of_another_users_task(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $taskA = Task::create([
            'user_id' => $userA->id,
            'title' => 'Task Milik A',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        $response = $this
            ->actingAs($userB)
            ->put(route('tasks.update', $taskA), [
                'title' => 'Task Dibajak B',
                'status' => 'pending',
                'priority' => 'high',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $taskA->id,
            'priority' => 'low',
        ]);
    }

    public function test_user_can_filter_tasks_by_priority(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Penting Sekali',
            'status' => 'pending',
            'priority' => 'high',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Biasa Saja',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Nanti Saja',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        // Filter High
        $responseHigh = $this->actingAs($user)->get('/tasks?priority=high');
        $responseHigh->assertOk();
        $responseHigh->assertSee('Task Penting Sekali');
        $responseHigh->assertDontSee('Task Biasa Saja');
        $responseHigh->assertDontSee('Task Nanti Saja');

        // Filter Medium
        $responseMed = $this->actingAs($user)->get('/tasks?priority=medium');
        $responseMed->assertOk();
        $responseMed->assertSee('Task Biasa Saja');
        $responseMed->assertDontSee('Task Penting Sekali');
        $responseMed->assertDontSee('Task Nanti Saja');

        // Filter Low
        $responseLow = $this->actingAs($user)->get('/tasks?priority=low');
        $responseLow->assertOk();
        $responseLow->assertSee('Task Nanti Saja');
        $responseLow->assertDontSee('Task Penting Sekali');
        $responseLow->assertDontSee('Task Biasa Saja');
    }

    public function test_priority_filter_respects_user_isolation(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Task::create([
            'user_id' => $userA->id,
            'title' => 'Task High Milik User A',
            'status' => 'pending',
            'priority' => 'high',
        ]);

        Task::create([
            'user_id' => $userB->id,
            'title' => 'Task High Milik User B',
            'status' => 'pending',
            'priority' => 'high',
        ]);

        $response = $this->actingAs($userA)->get('/tasks?priority=high');
        $response->assertOk();
        $response->assertSee('Task High Milik User A');
        $response->assertDontSee('Task High Milik User B');
    }

    public function test_priority_filter_works_together_with_search_and_status(): void
    {
        $user = User::factory()->create();

        // Matching: title contains 'Backend', status is 'pending', priority is 'high'
        Task::create([
            'user_id' => $user->id,
            'title' => 'Backend API Security',
            'status' => 'pending',
            'priority' => 'high',
        ]);

        // Priority mismatch ('low')
        Task::create([
            'user_id' => $user->id,
            'title' => 'Backend Database Indexing',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        // Status mismatch ('completed')
        Task::create([
            'user_id' => $user->id,
            'title' => 'Backend Logging System',
            'status' => 'completed',
            'priority' => 'high',
        ]);

        $response = $this->actingAs($user)->get('/tasks?search=Backend&status=pending&priority=high');
        $response->assertOk();
        $response->assertSee('Backend API Security');
        $response->assertDontSee('Backend Database Indexing');
        $response->assertDontSee('Backend Logging System');
    }

    public function test_user_can_sort_tasks_by_priority_desc(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Prioritas Rendah',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Prioritas Tinggi',
            'status' => 'pending',
            'priority' => 'high',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Prioritas Sedang',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $response = $this->actingAs($user)->get('/tasks?sort=priority_desc');
        $response->assertOk();
        $response->assertSeeInOrder(['Tugas Prioritas Tinggi', 'Tugas Prioritas Sedang', 'Tugas Prioritas Rendah']);
    }

    public function test_user_can_sort_tasks_by_priority_asc(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Prioritas Rendah',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Prioritas Tinggi',
            'status' => 'pending',
            'priority' => 'high',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Prioritas Sedang',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $response = $this->actingAs($user)->get('/tasks?sort=priority_asc');
        $response->assertOk();
        $response->assertSeeInOrder(['Tugas Prioritas Rendah', 'Tugas Prioritas Sedang', 'Tugas Prioritas Tinggi']);
    }

    public function test_priority_sorting_works_with_indonesian_aliases(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Rendah',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Tinggi',
            'status' => 'pending',
            'priority' => 'high',
        ]);

        $responseDesc = $this->actingAs($user)->get('/tasks?sort=prioritas_tertinggi');
        $responseDesc->assertOk();
        $responseDesc->assertSeeInOrder(['Tugas Tinggi', 'Tugas Rendah']);

        $responseAsc = $this->actingAs($user)->get('/tasks?sort=prioritas_terendah');
        $responseAsc->assertOk();
        $responseAsc->assertSeeInOrder(['Tugas Rendah', 'Tugas Tinggi']);
    }

    public function test_priority_query_validation_fails_on_invalid_value_or_array(): void
    {
        $user = User::factory()->create();

        $responseInvalid = $this->actingAs($user)->get('/tasks?priority=dangerous');
        $responseInvalid->assertStatus(302);
        $responseInvalid->assertSessionHasErrors('priority');

        $responseArray = $this->actingAs($user)->get('/tasks?priority[]=low');
        $responseArray->assertStatus(302);
        $responseArray->assertSessionHasErrors('priority');
    }

    public function test_priority_is_rendered_in_index_detail_and_dashboard(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Prioritas Khusus',
            'description' => 'Deskripsi Khusus',
            'status' => 'pending',
            'priority' => 'high',
        ]);

        // Index page
        $indexRes = $this->actingAs($user)->get('/tasks');
        $indexRes->assertOk();
        $indexRes->assertSee('Task Prioritas Khusus');
        $indexRes->assertSee('High');

        // Show page
        $showRes = $this->actingAs($user)->get(route('tasks.show', $task));
        $showRes->assertOk();
        $showRes->assertSee('Task Prioritas Khusus');
        $showRes->assertSee('High');

        // Dashboard
        $dashRes = $this->actingAs($user)->get('/dashboard');
        $dashRes->assertOk();
        $dashRes->assertSee('Task Prioritas Khusus');
        $dashRes->assertSee('High');
    }

    public function test_create_and_edit_screens_render_priority_field_with_options(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Edit Priority',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        $createRes = $this->actingAs($user)->get('/tasks/create');
        $createRes->assertOk();
        $createRes->assertSee('name="priority"', false);
        $createRes->assertSee('value="low"', false);
        $createRes->assertSee('value="medium"', false);
        $createRes->assertSee('value="high"', false);

        $editRes = $this->actingAs($user)->get(route('tasks.edit', $task));
        $editRes->assertOk();
        $editRes->assertSee('name="priority"', false);
        $editRes->assertSee('value="low"', false);
        $editRes->assertSee('value="medium"', false);
        $editRes->assertSee('value="high"', false);
    }
}
