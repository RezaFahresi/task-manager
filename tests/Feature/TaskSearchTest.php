<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_search_tasks_by_title(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Belajar Laravel Framework',
            'description' => 'Mempelajari routing dan controller',
            'status' => 'pending',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Belajar Golang Dasar',
            'description' => 'Mempelajari goroutine',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/tasks?search=Laravel');

        $response->assertOk();
        $response->assertSee('Belajar Laravel Framework');
        $response->assertDontSee('Belajar Golang Dasar');
    }

    public function test_user_can_search_tasks_by_description(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Pertama',
            'description' => 'Membuat database PostgreSQL',
            'status' => 'pending',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Tugas Kedua',
            'description' => 'Membuat styling CSS',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/tasks?search=PostgreSQL');

        $response->assertOk();
        $response->assertSee('Tugas Pertama');
        $response->assertDontSee('Tugas Kedua');
    }

    public function test_search_is_case_insensitive(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'BELAJAR DOCKER CONTAINER',
            'description' => 'DESKRIPSI HURUF BESAR',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/tasks?search=docker');

        $response->assertOk();
        $response->assertSee('BELAJAR DOCKER CONTAINER');

        $descResponse = $this
            ->actingAs($user)
            ->get('/tasks?search=deskripsi');

        $descResponse->assertOk();
        $descResponse->assertSee('BELAJAR DOCKER CONTAINER');
    }

    public function test_search_only_returns_tasks_belonging_to_authenticated_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Task::create([
            'user_id' => $userA->id,
            'title' => 'Task Rahasia User A',
            'description' => 'Catatan penting user A',
            'status' => 'pending',
        ]);

        Task::create([
            'user_id' => $userB->id,
            'title' => 'Task Rahasia User B',
            'description' => 'Catatan penting user B',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($userA)
            ->get('/tasks?search=Rahasia');

        $response->assertOk();
        $response->assertSee('Task Rahasia User A');
        $response->assertDontSee('Task Rahasia User B');
    }

    public function test_search_works_together_with_status_filter(): void
    {
        $user = User::factory()->create();

        Task::create([
            'user_id' => $user->id,
            'title' => 'Proyek Laravel Pending',
            'description' => 'Masih dalam proses',
            'status' => 'pending',
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Proyek Laravel Selesai',
            'description' => 'Sudah selesai dikerjakan',
            'status' => 'completed',
        ]);

        $responsePending = $this
            ->actingAs($user)
            ->get('/tasks?search=Laravel&status=pending');

        $responsePending->assertOk();
        $responsePending->assertSee('Proyek Laravel Pending');
        $responsePending->assertDontSee('Proyek Laravel Selesai');

        $responseCompleted = $this
            ->actingAs($user)
            ->get('/tasks?search=Laravel&status=completed');

        $responseCompleted->assertOk();
        $responseCompleted->assertSee('Proyek Laravel Selesai');
        $responseCompleted->assertDontSee('Proyek Laravel Pending');
    }

    public function test_search_preserves_query_string_on_pagination(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 7; $i++) {
            Task::create([
                'user_id' => $user->id,
                'title' => "Tugas Khusus {$i}",
                'description' => "Deskripsi {$i}",
                'status' => 'pending',
            ]);
        }

        $response = $this
            ->actingAs($user)
            ->get('/tasks?search=Khusus');

        $response->assertOk();
        $response->assertSee('search=Khusus');
    }

    public function test_search_input_is_rendered_in_view(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/tasks?search=KataKunci');

        $response->assertOk();
        $response->assertSee('name="search"', false);
        $response->assertSee('value="KataKunci"', false);
    }
}
