<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskSortTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_sort_tasks_by_latest(): void
    {
        $user = User::factory()->create();

        $olderTask = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Lama',
            'description' => 'Dibuat lebih dulu',
            'status' => 'pending',
        ]);
        $olderTask->created_at = now()->subDays(2);
        $olderTask->save();

        $newerTask = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Baru',
            'description' => 'Dibuat belakangan',
            'status' => 'pending',
        ]);
        $newerTask->created_at = now()->subDay();
        $newerTask->save();

        $response = $this
            ->actingAs($user)
            ->get('/tasks?sort=latest');

        $response->assertOk();
        $response->assertSeeInOrder(['Task Baru', 'Task Lama']);
    }

    public function test_user_can_sort_tasks_by_oldest(): void
    {
        $user = User::factory()->create();

        $olderTask = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Pertama Kali',
            'description' => 'Dibuat paling awal',
            'status' => 'pending',
        ]);
        $olderTask->created_at = now()->subDays(3);
        $olderTask->save();

        $newerTask = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Kedua Kali',
            'description' => 'Dibuat setelahnya',
            'status' => 'pending',
        ]);
        $newerTask->created_at = now()->subDay();
        $newerTask->save();

        $response = $this
            ->actingAs($user)
            ->get('/tasks?sort=oldest');

        $response->assertOk();
        $response->assertSeeInOrder(['Task Pertama Kali', 'Task Kedua Kali']);
    }

    public function test_user_can_sort_tasks_by_title_a_z(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Zeta Task',
            'description' => 'Awalan Z',
            'status' => 'pending',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Alpha Task',
            'description' => 'Awalan A',
            'status' => 'pending',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Beta Task',
            'description' => 'Awalan B',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/tasks?sort=title_asc');

        $response->assertOk();
        $response->assertSeeInOrder(['Alpha Task', 'Beta Task', 'Zeta Task']);
    }

    public function test_user_can_sort_tasks_by_title_z_a(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Alpha Task',
            'description' => 'Awalan A',
            'status' => 'pending',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Zeta Task',
            'description' => 'Awalan Z',
            'status' => 'pending',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Beta Task',
            'description' => 'Awalan B',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/tasks?sort=title_desc');

        $response->assertOk();
        $response->assertSeeInOrder(['Zeta Task', 'Beta Task', 'Alpha Task']);
    }

    public function test_user_can_sort_tasks_using_indonesian_alias_values(): void
    {
        $user = User::factory()->create();

        $zebraTask = Task::create([
            'user_id' => $user->id,
            'title' => 'Zebra Task',
            'description' => 'Deskripsi',
            'status' => 'pending',
        ]);
        $zebraTask->created_at = now()->subDays(2);
        $zebraTask->save();

        $anjingTask = Task::create([
            'user_id' => $user->id,
            'title' => 'Anjing Task',
            'description' => 'Deskripsi',
            'status' => 'pending',
        ]);
        $anjingTask->created_at = now()->subDay();
        $anjingTask->save();

        $responseTerbaru = $this
            ->actingAs($user)
            ->get('/tasks?sort=terbaru');
        $responseTerbaru->assertOk();
        $responseTerbaru->assertSeeInOrder(['Anjing Task', 'Zebra Task']);

        $responseTerlama = $this
            ->actingAs($user)
            ->get('/tasks?sort=terlama');
        $responseTerlama->assertOk();
        $responseTerlama->assertSeeInOrder(['Zebra Task', 'Anjing Task']);

        $responseJudulAsc = $this
            ->actingAs($user)
            ->get('/tasks?sort=judul_asc');
        $responseJudulAsc->assertOk();
        $responseJudulAsc->assertSeeInOrder(['Anjing Task', 'Zebra Task']);

        $responseJudulDesc = $this
            ->actingAs($user)
            ->get('/tasks?sort=judul_desc');
        $responseJudulDesc->assertOk();
        $responseJudulDesc->assertSeeInOrder(['Zebra Task', 'Anjing Task']);
    }

    public function test_combination_of_search_filter_status_and_sorting(): void
    {
        $user = User::factory()->create();

        // Matching: title contains 'Framework', status is 'pending'
        Task::create([
            'user_id' => $user->id,
            'title' => 'Zend Framework Task',
            'description' => 'Backend PHP',
            'status' => 'pending',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Laravel Framework Task',
            'description' => 'Backend PHP Modern',
            'status' => 'pending',
        ]);

        // Status mismatch ('completed')
        Task::create([
            'user_id' => $user->id,
            'title' => 'Django Framework Task',
            'description' => 'Python web',
            'status' => 'completed',
        ]);

        // Search mismatch ('Library', not 'Framework')
        Task::create([
            'user_id' => $user->id,
            'title' => 'React Library Task',
            'description' => 'Frontend JS',
            'status' => 'pending',
        ]);

        // Query with search=Framework, status=pending, sort=title_asc
        $response = $this
            ->actingAs($user)
            ->get('/tasks?search=Framework&status=pending&sort=title_asc');

        $response->assertOk();
        // Should only have Laravel and Zend in title ascending order
        $response->assertSeeInOrder(['Laravel Framework Task', 'Zend Framework Task']);
        $response->assertDontSee('Django Framework Task');
        $response->assertDontSee('React Library Task');

        // Query with search=Framework, status=pending, sort=title_desc
        $responseDesc = $this
            ->actingAs($user)
            ->get('/tasks?search=Framework&status=pending&sort=title_desc');

        $responseDesc->assertOk();
        $responseDesc->assertSeeInOrder(['Zend Framework Task', 'Laravel Framework Task']);
        $responseDesc->assertDontSee('Django Framework Task');
        $responseDesc->assertDontSee('React Library Task');
    }

    public function test_sorting_maintains_user_isolation(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Task::create([
            'user_id' => $userA->id,
            'title' => 'Alpha Milik User A',
            'description' => 'Deskripsi A',
            'status' => 'pending',
        ]);

        Task::create([
            'user_id' => $userB->id,
            'title' => 'Alpha Milik User B',
            'description' => 'Deskripsi B',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($userA)
            ->get('/tasks?sort=title_asc');

        $response->assertOk();
        $response->assertSee('Alpha Milik User A');
        $response->assertDontSee('Alpha Milik User B');
    }

    public function test_sorting_preserves_query_string_on_pagination(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 7; $i++) {
            Task::create([
                'user_id' => $user->id,
                'title' => "Tugas Sort Pagination {$i}",
                'description' => "Deskripsi {$i}",
                'status' => 'pending',
            ]);
        }

        $response = $this
            ->actingAs($user)
            ->get('/tasks?search=Pagination&status=pending&sort=title_asc');

        $response->assertOk();
        $response->assertSee('sort=title_asc');
        $response->assertSee('search=Pagination');
        $response->assertSee('status=pending');
    }

    public function test_invalid_sort_parameter_fails_validation(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/tasks?sort=invalid_column');

        $response->assertSessionHasErrors('sort');
    }

    public function test_sort_select_is_rendered_in_view(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/tasks?sort=title_asc');

        $response->assertOk();
        $response->assertSee('name="sort"', false);
        $response->assertSee('value="title_asc"', false);
        $response->assertSee('Terbaru');
        $response->assertSee('Terlama');
        $response->assertSee('Judul A-Z');
        $response->assertSee('Judul Z-A');
    }
}
