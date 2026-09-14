<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueTodayNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAuditRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present_on_http_responses(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_categories_show_route_is_excluded_and_returns_method_not_allowed_instead_of_500(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Design',
        ]);

        $response = $this->actingAs($user)->get('/categories/'.$category->id);

        $response->assertStatus(405);
    }

    public function test_registration_endpoint_is_rate_limited(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/register', [
                'name' => 'User '.$i,
                'email' => "user{$i}@example.com",
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);
            auth()->logout();
            $this->flushSession();
        }

        $response = $this->post('/register', [
            'name' => 'User Blocked',
            'email' => 'blocked@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(429);
    }

    public function test_forgot_password_endpoint_is_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 6; $i++) {
            $this->post('/forgot-password', [
                'email' => $user->email,
            ]);
        }

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(429);
    }

    public function test_task_creation_prevents_user_id_tampering_via_mass_assignment(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->actingAs($userA)->post('/tasks', [
            'title' => 'Security Audit Task',
            'status' => 'pending',
            'priority' => 'high',
            'user_id' => $userB->id,
        ]);

        $createdTask = Task::where('title', 'Security Audit Task')->first();
        $this->assertNotNull($createdTask);
        $this->assertSame($userA->id, $createdTask->user_id);
    }

    public function test_user_cannot_assign_another_users_category_to_their_task(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $categoryB = Category::create([
            'user_id' => $userB->id,
            'name' => 'Secret User B Category',
        ]);

        $response = $this->actingAs($userA)->post('/tasks', [
            'title' => 'Tampered Task',
            'status' => 'pending',
            'category_id' => $categoryB->id,
        ]);

        $response->assertSessionHasErrors('category_id');
        $this->assertDatabaseMissing('tasks', ['title' => 'Tampered Task']);
    }

    public function test_user_cannot_access_or_modify_another_users_task(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $taskB = Task::create([
            'user_id' => $userB->id,
            'title' => 'Private Task B',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $this->actingAs($userA)->get("/tasks/{$taskB->id}")->assertForbidden();
        $this->actingAs($userA)->get("/tasks/{$taskB->id}/edit")->assertForbidden();
        $this->actingAs($userA)->put("/tasks/{$taskB->id}", [
            'title' => 'Hacked Task',
            'status' => 'completed',
        ])->assertForbidden();
        $this->actingAs($userA)->delete("/tasks/{$taskB->id}")->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $taskB->id, 'title' => 'Private Task B']);
    }

    public function test_user_cannot_access_or_modify_another_users_category(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $categoryB = Category::create([
            'user_id' => $userB->id,
            'name' => 'User B Category',
        ]);

        $this->actingAs($userA)->get("/categories/{$categoryB->id}/edit")->assertForbidden();
        $this->actingAs($userA)->put("/categories/{$categoryB->id}", [
            'name' => 'Hacked Category',
        ])->assertForbidden();
        $this->actingAs($userA)->delete("/categories/{$categoryB->id}")->assertForbidden();

        $this->assertDatabaseHas('categories', ['id' => $categoryB->id, 'name' => 'User B Category']);
    }

    public function test_user_cannot_mark_as_read_or_delete_another_users_notification(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $taskB = Task::create([
            'user_id' => $userB->id,
            'title' => 'Task for User B',
            'status' => 'pending',
        ]);

        $userB->notify(new TaskDueTodayNotification($taskB));
        $notificationB = $userB->notifications()->first();
        $this->assertNotNull($notificationB);

        $this->actingAs($userA)->post("/notifications/{$notificationB->id}/read")->assertForbidden();
        $this->actingAs($userA)->patch("/notifications/{$notificationB->id}/read")->assertForbidden();
        $this->actingAs($userA)->delete("/notifications/{$notificationB->id}")->assertForbidden();

        $this->assertDatabaseHas('notifications', [
            'id' => $notificationB->id,
            'read_at' => null,
        ]);
    }
}
