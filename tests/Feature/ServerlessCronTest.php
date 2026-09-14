<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerlessCronTest extends TestCase
{
    use RefreshDatabase;

    public function test_cron_smart_reminders_rejects_unauthorized_access(): void
    {
        config(['app.cron_secret' => 'test-secret-12345']);

        // Without secret
        $resNoSecret = $this->get('/api/cron/smart-reminders');
        $resNoSecret->assertStatus(403);

        // With wrong secret
        $resWrongSecret = $this->get('/api/cron/smart-reminders?secret=wrong-secret');
        $resWrongSecret->assertStatus(403);
    }

    public function test_cron_smart_reminders_executes_successfully_with_valid_secret(): void
    {
        config(['app.cron_secret' => 'test-secret-12345']);

        $user = User::factory()->create();
        Task::create([
            'user_id' => $user->id,
            'title' => 'Serverless Cron Task',
            'status' => 'pending',
            'due_date' => Carbon::now()->addMinutes(45)->format('Y-m-d H:i:s'),
        ]);

        $res = $this->get('/api/cron/smart-reminders?secret=test-secret-12345');
        $res->assertOk();
        $res->assertJsonStructure(['status', 'message', 'dispatched_count', 'timestamp']);
        $res->assertJson(['status' => 'success']);

        $this->assertSame(1, $user->notifications()->count());
    }

    public function test_cron_smart_reminders_accepts_bearer_token(): void
    {
        config(['app.cron_secret' => 'test-secret-12345']);

        $res = $this->withHeader('Authorization', 'Bearer test-secret-12345')
            ->get('/api/cron/smart-reminders');
        $res->assertOk();
        $res->assertJson(['status' => 'success']);
    }

    public function test_cron_schedule_run_rejects_unauthorized_and_executes_with_valid_secret(): void
    {
        config(['app.cron_secret' => 'test-secret-12345']);

        // Unauthorized
        $this->get('/api/cron/schedule-run')->assertStatus(403);

        // Authorized
        $res = $this->get('/api/cron/schedule-run?secret=test-secret-12345');
        $res->assertOk();
        $res->assertJsonStructure(['status', 'message', 'output', 'timestamp']);
        $res->assertJson(['status' => 'success']);
    }
}
