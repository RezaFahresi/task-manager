<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_category_index_and_see_own_categories_only(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $catA = Category::create([
            'user_id' => $userA->id,
            'name' => 'Kategori Milik A',
        ]);

        $catB = Category::create([
            'user_id' => $userB->id,
            'name' => 'Kategori Milik B',
        ]);

        $response = $this->actingAs($userA)->get(route('categories.index'));

        $response->assertOk();
        $response->assertSee('Kategori Milik A');
        $response->assertDontSee('Kategori Milik B');
    }

    public function test_user_can_view_category_create_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('categories.create'));

        $response->assertOk();
        $response->assertSee('Tambah Kategori');
        $response->assertSee('name="name"', false);
    }

    public function test_user_can_create_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Kuliah',
        ]);

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Kuliah',
        ]);
    }

    public function test_user_can_view_category_edit_screen(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Project',
        ]);

        $response = $this->actingAs($user)->get(route('categories.edit', $category));

        $response->assertOk();
        $response->assertSee('Edit Kategori');
        $response->assertSee('value="Project"', false);
    }

    public function test_user_can_update_own_category(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Nama Lama',
        ]);

        $response = $this->actingAs($user)->put(route('categories.update', $category), [
            'name' => 'Nama Baru',
        ]);

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Nama Baru',
        ]);
    }

    public function test_user_can_delete_own_category(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Kategori Dihapus',
        ]);

        $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_category_name_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_category_name_cannot_exceed_100_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => str_repeat('A', 101),
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_category_name_must_be_unique_for_same_user(): void
    {
        $user = User::factory()->create();

        Category::create([
            'user_id' => $user->id,
            'name' => 'Desain',
        ]);

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Desain',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_different_users_can_have_category_with_same_name(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Category::create([
            'user_id' => $userA->id,
            'name' => 'Umum',
        ]);

        $response = $this->actingAs($userB)->post(route('categories.store'), [
            'name' => 'Umum',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'user_id' => $userB->id,
            'name' => 'Umum',
        ]);
    }

    public function test_updating_category_allows_same_name_for_current_category(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Tetap Sama',
        ]);

        $response = $this->actingAs($user)->put(route('categories.update', $category), [
            'name' => 'Tetap Sama',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Tetap Sama',
        ]);
    }

    public function test_user_cannot_access_another_users_category_edit_screen(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $catB = Category::create([
            'user_id' => $userB->id,
            'name' => 'Kategori B',
        ]);

        $response = $this->actingAs($userA)->get(route('categories.edit', $catB));

        $response->assertForbidden();
    }

    public function test_user_cannot_update_another_users_category(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $catB = Category::create([
            'user_id' => $userB->id,
            'name' => 'Kategori B Asli',
        ]);

        $response = $this->actingAs($userA)->put(route('categories.update', $catB), [
            'name' => 'Dibajak A',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('categories', [
            'id' => $catB->id,
            'name' => 'Kategori B Asli',
        ]);
    }

    public function test_user_cannot_delete_another_users_category(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $catB = Category::create([
            'user_id' => $userB->id,
            'name' => 'Kategori B Aman',
        ]);

        $response = $this->actingAs($userA)->delete(route('categories.destroy', $catB));

        $response->assertForbidden();

        $this->assertDatabaseHas('categories', [
            'id' => $catB->id,
        ]);
    }

    public function test_user_cannot_assign_another_users_category_to_own_task(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $catB = Category::create([
            'user_id' => $userB->id,
            'name' => 'Kategori Milik B',
        ]);

        // Attempt Create Task with User B's category
        $responseCreate = $this->actingAs($userA)->post('/tasks', [
            'title' => 'Task Bajakan Category',
            'status' => 'pending',
            'category_id' => $catB->id,
        ]);

        $responseCreate->assertSessionHasErrors('category_id');
        $this->assertDatabaseMissing('tasks', [
            'title' => 'Task Bajakan Category',
        ]);

        // Attempt Update Task with User B's category
        $taskA = Task::create([
            'user_id' => $userA->id,
            'title' => 'Task Asli A',
            'status' => 'pending',
        ]);

        $responseUpdate = $this->actingAs($userA)->put(route('tasks.update', $taskA), [
            'title' => 'Task Asli A',
            'status' => 'pending',
            'category_id' => $catB->id,
        ]);

        $responseUpdate->assertSessionHasErrors('category_id');
        $this->assertNull($taskA->fresh()->category_id);
    }

    public function test_user_can_create_and_update_task_with_own_category(): void
    {
        $user = User::factory()->create();

        $cat1 = Category::create([
            'user_id' => $user->id,
            'name' => 'Laravel',
        ]);

        $cat2 = Category::create([
            'user_id' => $user->id,
            'name' => 'Belajar',
        ]);

        // Create with category
        $responseCreate = $this->actingAs($user)->post('/tasks', [
            'title' => 'Belajar Laravel',
            'status' => 'pending',
            'category_id' => $cat1->id,
        ]);

        $responseCreate->assertRedirect(route('tasks.index'));
        $task = Task::where('title', 'Belajar Laravel')->first();
        $this->assertNotNull($task);
        $this->assertSame($cat1->id, $task->category_id);

        // Update category
        $responseUpdate = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Belajar Laravel',
            'status' => 'pending',
            'category_id' => $cat2->id,
        ]);

        $responseUpdate->assertRedirect(route('tasks.index'));
        $this->assertSame($cat2->id, $task->fresh()->category_id);

        // Remove category (set to null)
        $responseRemove = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Belajar Laravel',
            'status' => 'pending',
            'category_id' => null,
        ]);

        $responseRemove->assertRedirect(route('tasks.index'));
        $this->assertNull($task->fresh()->category_id);
    }

    public function test_task_list_and_detail_display_category_name(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Kuliah',
        ]);

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Mengerjakan TA',
            'status' => 'pending',
            'category_id' => $category->id,
        ]);

        // Task index
        $indexResponse = $this->actingAs($user)->get('/tasks');
        $indexResponse->assertOk();
        $indexResponse->assertSee('Mengerjakan TA');
        $indexResponse->assertSee('Kuliah');

        // Task show
        $showResponse = $this->actingAs($user)->get(route('tasks.show', $task));
        $showResponse->assertOk();
        $showResponse->assertSee('Mengerjakan TA');
        $showResponse->assertSee('Kuliah');

        // Dashboard
        $dashResponse = $this->actingAs($user)->get('/dashboard');
        $dashResponse->assertOk();
        $dashResponse->assertSee('Mengerjakan TA');
        $dashResponse->assertSee('Kuliah');
    }

    public function test_user_can_filter_tasks_by_category(): void
    {
        $user = User::factory()->create();

        $catProject = Category::create([
            'user_id' => $user->id,
            'name' => 'Project',
        ]);

        $catDesign = Category::create([
            'user_id' => $user->id,
            'name' => 'Design',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Mengerjakan TA',
            'status' => 'pending',
            'category_id' => $catProject->id,
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Membuat UI',
            'status' => 'pending',
            'category_id' => $catDesign->id,
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Task Tanpa Kategori',
            'status' => 'pending',
            'category_id' => null,
        ]);

        // Filter by Category ID
        $responseId = $this->actingAs($user)->get('/tasks?category='.$catProject->id);
        $responseId->assertOk();
        $responseId->assertSee('Mengerjakan TA');
        $responseId->assertDontSee('Membuat UI');
        $responseId->assertDontSee('Task Tanpa Kategori');

        // Filter by Category Name
        $responseName = $this->actingAs($user)->get('/tasks?category=Design');
        $responseName->assertOk();
        $responseName->assertSee('Membuat UI');
        $responseName->assertDontSee('Mengerjakan TA');
        $responseName->assertDontSee('Task Tanpa Kategori');
    }

    public function test_category_filter_respects_user_isolation(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $catA = Category::create([
            'user_id' => $userA->id,
            'name' => 'SharedName',
        ]);

        $catB = Category::create([
            'user_id' => $userB->id,
            'name' => 'SharedName',
        ]);

        Task::create([
            'user_id' => $userA->id,
            'title' => 'Task Milik User A',
            'status' => 'pending',
            'category_id' => $catA->id,
        ]);

        Task::create([
            'user_id' => $userB->id,
            'title' => 'Task Milik User B',
            'status' => 'pending',
            'category_id' => $catB->id,
        ]);

        $response = $this->actingAs($userA)->get('/tasks?category='.$catA->id);
        $response->assertOk();
        $response->assertSee('Task Milik User A');
        $response->assertDontSee('Task Milik User B');
    }

    public function test_category_filter_works_together_with_search_status_priority_and_due_date(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Project',
        ]);

        $otherCategory = Category::create([
            'user_id' => $user->id,
            'name' => 'Kuliah',
        ]);

        // Match: search "TA", status "pending", priority "high", deadline "today", category "Project"
        Task::create([
            'user_id' => $user->id,
            'title' => 'Mengerjakan TA Bab 1',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => Carbon::today()->toDateString(),
            'category_id' => $category->id,
        ]);

        // Mismatch category
        Task::create([
            'user_id' => $user->id,
            'title' => 'Mengerjakan TA Bab 2',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => Carbon::today()->toDateString(),
            'category_id' => $otherCategory->id,
        ]);

        // Mismatch priority
        Task::create([
            'user_id' => $user->id,
            'title' => 'Mengerjakan TA Bab 3',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => Carbon::today()->toDateString(),
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get(
            '/tasks?search=TA&status=pending&priority=high&deadline=today&category='.$category->id
        );

        $response->assertOk();
        $response->assertSee('Mengerjakan TA Bab 1');
        $response->assertDontSee('Mengerjakan TA Bab 2');
        $response->assertDontSee('Mengerjakan TA Bab 3');
    }

    public function test_deleting_category_sets_task_category_id_to_null(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Kategori Sementara',
        ]);

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Bertahan',
            'status' => 'pending',
            'category_id' => $category->id,
        ]);

        $this->actingAs($user)->delete(route('categories.destroy', $category));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'category_id' => null,
        ]);
    }
}
