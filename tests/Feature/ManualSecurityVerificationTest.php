<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class ManualSecurityVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Guest mengakses /dashboard dan seluruh /tasks/*
     */
    public function test_01_guest_accessing_dashboard_and_tasks_routes_redirects_to_login(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Sample Task',
            'status' => 'pending',
        ]);

        // GET /dashboard -> 302 Redirect to /login
        $this->get('/dashboard')->assertStatus(302)->assertRedirect('/login');

        // GET /tasks -> 302 Redirect to /login
        $this->get('/tasks')->assertStatus(302)->assertRedirect('/login');

        // GET /tasks/create -> 302 Redirect to /login
        $this->get('/tasks/create')->assertStatus(302)->assertRedirect('/login');

        // GET /tasks/{id} -> 302 Redirect to /login
        $this->get(route('tasks.show', $task))->assertStatus(302)->assertRedirect('/login');

        // GET /tasks/{id}/edit -> 302 Redirect to /login
        $this->get(route('tasks.edit', $task))->assertStatus(302)->assertRedirect('/login');

        // POST /tasks -> 302 Redirect to /login
        $this->post('/tasks', [
            'title' => 'New Task',
            'status' => 'pending',
        ])->assertStatus(302)->assertRedirect('/login');

        // PUT /tasks/{id} -> 302 Redirect to /login
        $this->put(route('tasks.update', $task), [
            'title' => 'Updated Task',
            'status' => 'completed',
        ])->assertStatus(302)->assertRedirect('/login');

        // DELETE /tasks/{id} -> 302 Redirect to /login
        $this->delete(route('tasks.destroy', $task))->assertStatus(302)->assertRedirect('/login');
    }

    /**
     * 2. User A mencoba membuka detail, edit, update, dan delete Task milik User B
     */
    public function test_02_user_a_cannot_view_edit_update_or_delete_user_b_task(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $taskB = Task::create([
            'user_id' => $userB->id,
            'title' => 'Task Rahasia Milik User B',
            'description' => 'Deskripsi sensitif B',
            'status' => 'pending',
        ]);

        // User A mencoba melihat detail task B -> 403 Forbidden
        $this->actingAs($userA)
            ->get(route('tasks.show', $taskB))
            ->assertStatus(403);

        // User A mencoba membuka form edit task B -> 403 Forbidden
        $this->actingAs($userA)
            ->get(route('tasks.edit', $taskB))
            ->assertStatus(403);

        // User A mencoba mengupdate task B -> 403 Forbidden
        $this->actingAs($userA)
            ->put(route('tasks.update', $taskB), [
                'title' => 'Judul Dibajak',
                'description' => 'Deskripsi Dibajak',
                'status' => 'completed',
            ])
            ->assertStatus(403);

        // Pastikan isi task B tidak berubah di database
        $this->assertDatabaseHas('tasks', [
            'id' => $taskB->id,
            'title' => 'Task Rahasia Milik User B',
            'status' => 'pending',
        ]);

        // User A mencoba menghapus task B -> 403 Forbidden
        $this->actingAs($userA)
            ->delete(route('tasks.destroy', $taskB))
            ->assertStatus(403);

        // Pastikan task B masih ada di database
        $this->assertDatabaseHas('tasks', [
            'id' => $taskB->id,
        ]);
    }

    /**
     * 3. Manipulasi ID pada URL /tasks/{id}
     */
    public function test_03_manipulating_task_id_in_url_returns_404(): void
    {
        $user = User::factory()->create();

        // Non-existent numeric ID -> 404 Not Found
        $this->actingAs($user)->get('/tasks/999999')->assertStatus(404);
        $this->actingAs($user)->get('/tasks/999999/edit')->assertStatus(404);
        $this->actingAs($user)->put('/tasks/999999', ['title' => 'X', 'status' => 'pending'])->assertStatus(404);
        $this->actingAs($user)->delete('/tasks/999999')->assertStatus(404);

        // Non-numeric / invalid string ID -> 404 Not Found
        $this->actingAs($user)->get('/tasks/non-existent-task-slug')->assertStatus(404);
    }

    /**
     * 4. Parameter search/status/sort dengan tipe array dan nilai tidak valid
     */
    public function test_04_query_parameters_validation_against_arrays_and_invalid_values(): void
    {
        $user = User::factory()->create();

        // Parameter bertipe array (array injection) harus ditolak oleh validator -> 302
        $responseArray = $this->actingAs($user)->get('/tasks?search[]=hack&status[]=pending&sort[]=latest');
        $responseArray->assertStatus(302);
        $responseArray->assertSessionHasErrors(['search', 'status', 'sort']);

        // Nilai status tidak valid -> 302
        $responseInvalidStatus = $this->actingAs($user)->get('/tasks?status=malicious_status');
        $responseInvalidStatus->assertStatus(302);
        $responseInvalidStatus->assertSessionHasErrors('status');

        // Nilai sort tidak valid (SQL injection attempt) -> 302
        $responseInvalidSort = $this->actingAs($user)->get('/tasks?sort=DROP_TABLE');
        $responseInvalidSort->assertStatus(302);
        $responseInvalidSort->assertSessionHasErrors('sort');

        // Parameter valid harus berhasil diterima -> 200 OK
        $responseValid = $this->actingAs($user)->get('/tasks?search=normal&status=pending&sort=latest');
        $responseValid->assertStatus(200);
    }

    /**
     * 5. CSRF pada POST/PUT/DELETE
     */
    public function test_05_csrf_protection_on_state_changing_requests(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Original Task',
            'status' => 'pending',
        ]);

        // Verifikasi keberadaan token CSRF pada seluruh form Blade
        $createForm = $this->actingAs($user)->get('/tasks/create');
        $createForm->assertSee('name="_token"', false);

        $editForm = $this->actingAs($user)->get(route('tasks.edit', $task));
        $editForm->assertSee('name="_token"', false);

        $indexView = $this->actingAs($user)->get('/tasks');
        $indexView->assertSee('name="_token"', false); // Delete form token

        // Verifikasi eksekusi CSRF Middleware (PreventRequestForgery)
        $csrfMiddleware = new class(app(), app('encrypter')) extends PreventRequestForgery
        {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        };

        // POST request tanpa token -> Melempar TokenMismatchException (HTTP 419)
        $requestWithoutToken = Request::create('/tasks', 'POST', [
            'title' => 'Task Tanpa Token',
            'status' => 'pending',
        ]);
        $session = app('session.store');
        $session->start();
        $requestWithoutToken->setLaravelSession($session);

        $caughtException = false;
        try {
            $csrfMiddleware->handle($requestWithoutToken, fn () => response('OK'));
        } catch (TokenMismatchException $e) {
            $caughtException = true;
        }
        $this->assertTrue($caughtException, 'CSRF Middleware gagal mendeteksi token mismatch pada POST!');

        // POST request dengan token valid -> Berhasil lolos middleware (HTTP 200)
        $requestWithToken = Request::create('/tasks', 'POST', [
            'title' => 'Task Dengan Token Valid',
            'status' => 'pending',
            '_token' => $session->token(),
        ]);
        $requestWithToken->setLaravelSession($session);

        $response = $csrfMiddleware->handle($requestWithToken, fn () => response('OK', 200));
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * 6. Input HTML/JavaScript pada title dan description untuk memastikan XSS ter-escape
     */
    public function test_06_html_and_javascript_in_title_and_description_are_properly_escaped(): void
    {
        $user = User::factory()->create();

        $xssTitle = '<script>alert("XSS-TITLE")</script>';
        $xssDesc = '<img src="x" onerror="alert(\'XSS-DESC\')"><b>Bold</b>';

        $task = Task::create([
            'user_id' => $user->id,
            'title' => $xssTitle,
            'description' => $xssDesc,
            'status' => 'pending',
        ]);

        // 1. Pada halaman Index
        $indexResponse = $this->actingAs($user)->get('/tasks');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('&lt;script&gt;alert(&quot;XSS-TITLE&quot;)&lt;/script&gt;', false);
        $indexResponse->assertDontSee('<script>alert("XSS-TITLE")</script>', false);

        // 2. Pada halaman Detail
        $detailResponse = $this->actingAs($user)->get(route('tasks.show', $task));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('&lt;script&gt;alert(&quot;XSS-TITLE&quot;)&lt;/script&gt;', false);
        $detailResponse->assertDontSee('<script>alert("XSS-TITLE")</script>', false);

        // 3. Pada halaman Dashboard (recent tasks)
        $dashboardResponse = $this->actingAs($user)->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('&lt;script&gt;alert(&quot;XSS-TITLE&quot;)&lt;/script&gt;', false);
        $dashboardResponse->assertDontSee('<script>alert("XSS-TITLE")</script>', false);
    }

    /**
     * 7. Mass assignment user_id/status
     */
    public function test_07_mass_assignment_protection_on_user_id_and_status(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Percobaan POST dengan user_id milik userB -> Ownership tetap User A
        $responsePost = $this->actingAs($userA)->post('/tasks', [
            'user_id' => $userB->id,
            'title' => 'Tugas Milik Siapa',
            'description' => 'Deskripsi tugas',
            'status' => 'pending',
        ]);

        $responsePost->assertStatus(302)->assertRedirect(route('tasks.index'));

        // Pastikan task yang tersimpan tetap dimiliki oleh userA
        $createdTask = Task::where('title', 'Tugas Milik Siapa')->first();
        $this->assertNotNull($createdTask);
        $this->assertSame($userA->id, $createdTask->user_id);

        // Percobaan PUT dengan user_id milik userB -> Ownership tidak bisa diubah
        $responsePut = $this->actingAs($userA)->put(route('tasks.update', $createdTask), [
            'user_id' => $userB->id,
            'title' => 'Tugas Sesudah Edit',
            'status' => 'completed',
        ]);

        $responsePut->assertStatus(302)->assertRedirect(route('tasks.index'));
        $createdTask->refresh();
        $this->assertSame($userA->id, $createdTask->user_id);

        // Percobaan POST dengan status tidak valid -> 302 Validation Error
        $responseInvalidStatus = $this->actingAs($userA)->post('/tasks', [
            'title' => 'Task Status Invalid',
            'status' => 'admin_super_privilege',
        ]);
        $responseInvalidStatus->assertStatus(302);
        $responseInvalidStatus->assertSessionHasErrors('status');
    }

    /**
     * 8. Logout dan akses kembali halaman protected
     */
    public function test_08_logout_destroys_session_and_protected_pages_become_inaccessible(): void
    {
        $user = User::factory()->create();

        // Login dan akses dashboard -> 200 OK
        $this->actingAs($user)->get('/dashboard')->assertStatus(200);

        // Melakukan logout -> 302 Redirect to /
        $logoutResponse = $this->actingAs($user)->post('/logout');
        $logoutResponse->assertStatus(302)->assertRedirect('/');

        // Verifikasi bahwa pengguna sudah menjadi guest
        $this->assertGuest();

        // Mencoba mengakses halaman protected setelah logout -> 302 Redirect to /login
        $this->get('/dashboard')->assertStatus(302)->assertRedirect('/login');
        $this->get('/tasks')->assertStatus(302)->assertRedirect('/login');
        $this->get('/tasks/create')->assertStatus(302)->assertRedirect('/login');
        $this->get('/profile')->assertStatus(302)->assertRedirect('/login');
    }

    /**
     * 9. Pagination + search + filter + sorting secara bersamaan
     */
    public function test_09_pagination_search_filter_and_sorting_work_together(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Buat 12 task untuk User A
        for ($i = 1; $i <= 8; $i++) {
            Task::create([
                'user_id' => $userA->id,
                'title' => "Proyek {$i} Modul Alpha",
                'description' => "Deskripsi pengerjaan modul {$i}",
                'status' => 'pending',
            ]);
        }

        for ($i = 9; $i <= 12; $i++) {
            Task::create([
                'user_id' => $userA->id,
                'title' => "Proyek {$i} Modul Selesai",
                'description' => "Deskripsi pengerjaan modul {$i}",
                'status' => 'completed',
            ]);
        }

        // 5 task untuk User B dengan kata kunci "Proyek"
        for ($i = 1; $i <= 5; $i++) {
            Task::create([
                'user_id' => $userB->id,
                'title' => "Proyek B {$i} Milik User B",
                'description' => "Deskripsi B {$i}",
                'status' => 'pending',
            ]);
        }

        // Akses Halaman 1 -> 200 OK
        $page1 = $this->actingAs($userA)->get('/tasks?search=Proyek&status=pending&sort=title_asc&page=1');
        $page1->assertStatus(200);

        // Verifikasi data
        $page1->assertSee('Proyek 1 Modul Alpha');
        $page1->assertDontSee('Proyek 9 Modul Selesai');
        $page1->assertDontSee('Milik User B');

        // Verifikasi query string pada tautan pagination
        $page1->assertSee('search=Proyek');
        $page1->assertSee('status=pending');
        $page1->assertSee('sort=title_asc');

        // Akses Halaman 2 -> 200 OK
        $page2 = $this->actingAs($userA)->get('/tasks?search=Proyek&status=pending&sort=title_asc&page=2');
        $page2->assertStatus(200);
        $page2->assertSee('Proyek 6 Modul Alpha');
        $page2->assertDontSee('Milik User B');
    }

    /**
     * 10. Pastikan tidak ada query database dari Blade
     */
    public function test_10_no_database_queries_are_executed_from_blade_views(): void
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Uji Efisiensi',
            'description' => 'Deskripsi task uji',
            'status' => 'pending',
        ]);
        $task->load('user');

        $recentTasks = Task::with('user')->where('user_id', $user->id)->latest()->take(5)->get();
        $paginatedTasks = Task::with('user')->where('user_id', $user->id)->paginate(5);

        $errors = new ViewErrorBag;

        // 1. Rendering dashboard view langsung
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAs($user)->view('dashboard', [
            'totalTasks' => 1,
            'pendingTasks' => 1,
            'completedTasks' => 0,
            'recentTasks' => $recentTasks,
            'errors' => $errors,
        ]);

        $dashboardQueries = DB::getQueryLog();
        $this->assertCount(0, $dashboardQueries, 'dashboard.blade.php mengeksekusi query database!');

        // 2. Rendering tasks.show view langsung
        DB::flushQueryLog();

        $this->actingAs($user)->view('tasks.show', [
            'task' => $task,
            'errors' => $errors,
        ]);

        $showQueries = DB::getQueryLog();
        $this->assertCount(0, $showQueries, 'tasks/show.blade.php mengeksekusi query database!');

        // 3. Rendering tasks.index view langsung
        DB::flushQueryLog();

        $this->actingAs($user)->view('tasks.index', [
            'tasks' => $paginatedTasks,
            'errors' => $errors,
        ]);

        $indexQueries = DB::getQueryLog();
        $this->assertCount(0, $indexQueries, 'tasks/index.blade.php mengeksekusi query database!');

        DB::disableQueryLog();
    }
}
