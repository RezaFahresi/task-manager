<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_is_rendered_with_menu_items_and_logout_on_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertOk();
        $response->assertSee(route('dashboard'));
        $response->assertSee(route('tasks.index'));
        $response->assertSee(route('notifications.index'));
        $response->assertSee(route('profile.edit'));
        $response->assertSee(route('logout'));
        $response->assertSee('Keluar');
        $response->assertSee('John Doe');
    }

    public function test_sidebar_is_rendered_on_tasks_page(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/tasks');

        $response->assertOk();
        $response->assertSee(route('dashboard'));
        $response->assertSee(route('tasks.index'));
        $response->assertSee(route('profile.edit'));
        $response->assertSee(route('logout'));
    }

    public function test_sidebar_is_rendered_on_task_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/tasks/create');

        $response->assertOk();
        $response->assertSee(route('dashboard'));
        $response->assertSee(route('tasks.index'));
        $response->assertSee(route('profile.edit'));
        $response->assertSee(route('logout'));
    }

    public function test_sidebar_is_rendered_on_task_edit_page(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Test Edit Sidebar',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('tasks.edit', $task));

        $response->assertOk();
        $response->assertSee(route('dashboard'));
        $response->assertSee(route('tasks.index'));
        $response->assertSee(route('profile.edit'));
        $response->assertSee(route('logout'));
    }

    public function test_sidebar_is_rendered_on_task_detail_page(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Test Detail Sidebar',
            'description' => 'Detail Task Description',
            'status' => 'completed',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertSee('Test Detail Sidebar');
        $response->assertSee('Detail Task Description');
        $response->assertSee(route('dashboard'));
        $response->assertSee(route('tasks.index'));
        $response->assertSee(route('profile.edit'));
        $response->assertSee(route('logout'));
    }

    public function test_sidebar_is_rendered_on_profile_page(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        $response->assertSee(route('dashboard'));
        $response->assertSee(route('tasks.index'));
        $response->assertSee(route('profile.edit'));
        $response->assertSee(route('logout'));
    }
}
