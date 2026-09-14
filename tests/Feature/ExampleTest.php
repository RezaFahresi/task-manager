<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Task Manager');
        $response->assertSee('Mulai Sekarang');
        $response->assertSee('Log in');

        // Feature section verification
        $response->assertSee('Task Management Terpadu');
        $response->assertSee('Pelacakan Deadline Otomatis');
        $response->assertSee('Tingkat Prioritas');
        $response->assertSee('Pengelompokan Kategori Fleksibel');
        $response->assertSee('Notifikasi Realtime & Email Otomatis', false);

        // Workflow section verification
        $response->assertSee('Buat Task');
        $response->assertSee('Atur Deadline & Kategori', false);
        $response->assertSee('Dapatkan Pengingat');
        $response->assertSee('Selesaikan & Pantau', false);

        // Visual preview mockup
        $response->assertSee('app.taskmanager/dashboard');
    }

    /**
     * Test landing page reflects authenticated user state.
     */
    public function test_landing_page_renders_dashboard_cta_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Buka Dashboard');
    }
}
