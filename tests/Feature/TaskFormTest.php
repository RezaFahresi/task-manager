<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_task_screen_can_be_rendered_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/tasks/create');

        $response->assertOk();
        $response->assertSee('Tambah Task');
        $response->assertSee('name="title"', false);
        $response->assertSee('name="description"', false);
        $response->assertSee('name="status"', false);
        $response->assertSee('Simpan');
        $response->assertSee('Kembali');
    }

    public function test_user_can_create_task_with_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/tasks', [
                'title' => 'Tugas Baru Penting',
                'description' => 'Deskripsi pengerjaan tugas baru',
                'status' => 'pending',
            ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Tugas Baru Penting',
            'description' => 'Deskripsi pengerjaan tugas baru',
            'status' => 'pending',
        ]);
    }

    public function test_create_task_validation_fails_and_preserves_old_input(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/tasks', [
                'title' => '',
                'description' => 'Deskripsi yang dipertahankan',
                'status' => 'invalid_status',
            ]);

        $response->assertSessionHasErrors(['title', 'status']);
        $this->assertDatabaseMissing('tasks', [
            'description' => 'Deskripsi yang dipertahankan',
        ]);
    }

    public function test_edit_task_screen_can_be_rendered_for_owner(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Siap Diedit',
            'description' => 'Deskripsi awal task',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('tasks.edit', $task));

        $response->assertOk();
        $response->assertSee('Edit Task');
        $response->assertSee('Task Siap Diedit');
        $response->assertSee('Deskripsi awal task');
        $response->assertSee('Kembali');
        $response->assertSee('Simpan');
    }

    public function test_user_cannot_access_edit_screen_for_another_users_task(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $task = Task::create([
            'user_id' => $userA->id,
            'title' => 'Task Milik User A',
            'description' => 'Deskripsi user A',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($userB)
            ->get(route('tasks.edit', $task));

        $response->assertForbidden();
    }

    public function test_user_can_update_task_with_valid_data(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Sebelum Update',
            'description' => 'Deskripsi sebelum update',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('tasks.update', $task), [
                'title' => 'Task Sesudah Update',
                'description' => 'Deskripsi sesudah update',
                'status' => 'completed',
            ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Task Sesudah Update',
            'description' => 'Deskripsi sesudah update',
            'status' => 'completed',
        ]);
    }

    public function test_edit_task_validation_fails_on_empty_title(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Awal',
            'description' => 'Deskripsi awal',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('tasks.update', $task), [
                'title' => '',
                'description' => 'Deskripsi baru',
                'status' => 'pending',
            ]);

        $response->assertSessionHasErrors(['title']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Task Awal',
        ]);
    }

    public function test_user_cannot_update_another_users_task(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $task = Task::create([
            'user_id' => $userA->id,
            'title' => 'Task User A Asli',
            'description' => 'Deskripsi Asli',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($userB)
            ->put(route('tasks.update', $task), [
                'title' => 'Task Dibajak User B',
                'description' => 'Deskripsi Bajakan',
                'status' => 'completed',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Task User A Asli',
            'status' => 'pending',
        ]);
    }

    public function test_user_can_view_own_task_detail(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Detail Task Milik Sendiri',
            'description' => 'Deskripsi detail lengkap',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertSee('Detail Task Milik Sendiri');
        $response->assertSee('Deskripsi detail lengkap');
    }

    public function test_user_cannot_view_another_users_task_detail(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $task = Task::create([
            'user_id' => $userA->id,
            'title' => 'Task Rahasia User A',
            'description' => 'Deskripsi rahasia',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($userB)
            ->get(route('tasks.show', $task));

        $response->assertForbidden();
    }

    public function test_user_can_delete_own_task(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Yang Akan Dihapus',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('tasks.destroy', $task));

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_task(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $task = Task::create([
            'user_id' => $userA->id,
            'title' => 'Task Milik User A Tidak Boleh Dihapus User B',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($userB)
            ->delete(route('tasks.destroy', $task));

        $response->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_tasks_or_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/tasks')->assertRedirect('/login');
        $this->get('/tasks/create')->assertRedirect('/login');
    }
}
