<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Category;
use App\Models\Task;
use App\Services\TaskNotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function dashboard(): View
    {
        $userId = Auth::id();
        $user = Auth::user();

        if ($user) {
            app(TaskNotificationService::class)->checkAndNotifyUser($user);
        }

        $totalTasks = Task::where('user_id', $userId)->count();

        $pendingTasks = Task::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();

        $completedTasks = Task::where('user_id', $userId)
            ->where('status', 'completed')
            ->count();

        $overdueTasks = Task::where('user_id', $userId)
            ->where('status', 'pending')
            ->whereDate('due_date', '<', today())
            ->count();

        $dueTodayTasks = Task::where('user_id', $userId)
            ->where('status', 'pending')
            ->whereDate('due_date', today())
            ->count();

        $completionRate = $totalTasks > 0
            ? (int) round(($completedTasks / $totalTasks) * 100)
            : 0;

        $recentTasks = Task::with('category')
            ->where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        $upcomingTasks = Task::with('category')
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->whereDate('due_date', '>', today())
            ->orderBy('due_date', 'asc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        $prioritySummary = [
            'high' => Task::where('user_id', $userId)->where('priority', 'high')->count(),
            'medium' => Task::where('user_id', $userId)->where('priority', 'medium')->count(),
            'low' => Task::where('user_id', $userId)->where('priority', 'low')->count(),
        ];

        $categorySummary = Category::where('user_id', $userId)
            ->withCount(['tasks' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->orderBy('name')
            ->get();

        $uncategorizedCount = Task::where('user_id', $userId)
            ->whereNull('category_id')
            ->count();

        return view('dashboard', [
            'totalTasks' => $totalTasks,
            'pendingTasks' => $pendingTasks,
            'completedTasks' => $completedTasks,
            'overdueTasks' => $overdueTasks,
            'overdue' => $overdueTasks,
            'dueTodayTasks' => $dueTodayTasks,
            'dueToday' => $dueTodayTasks,
            'completionRate' => $completionRate,
            'recentTasks' => $recentTasks,
            'upcomingTasks' => $upcomingTasks,
            'prioritySummary' => $prioritySummary,
            'categorySummary' => $categorySummary,
            'uncategorizedCount' => $uncategorizedCount,
        ]);
    }

    public function index(Request $request): View
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(['pending', 'completed', ''])],
            'priority' => ['nullable', 'string', Rule::in(['low', 'medium', 'high', ''])],
            'deadline' => ['nullable', 'string', Rule::in(['today', 'upcoming', 'overdue', 'all', 'hari_ini', 'mendatang', 'terlambat', 'semua', ''])],
            'due_date' => ['nullable', 'string', Rule::in(['today', 'upcoming', 'overdue', 'all', 'hari_ini', 'mendatang', 'terlambat', 'semua', ''])],
            'category' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer'],
            'sort' => [
                'nullable',
                'string',
                Rule::in([
                    'latest',
                    'oldest',
                    'title_asc',
                    'title_desc',
                    'terbaru',
                    'terlama',
                    'judul_asc',
                    'judul_desc',
                    'title_a_z',
                    'title_z_a',
                    'judul_a_z',
                    'judul_z_a',
                    'priority_desc',
                    'priority_asc',
                    'prioritas_desc',
                    'prioritas_asc',
                    'prioritas_tertinggi',
                    'prioritas_terendah',
                    'due_date_asc',
                    'due_date_desc',
                    'deadline_asc',
                    'deadline_desc',
                    'deadline_terdekat',
                    'deadline_terjauh',
                ]),
            ],
        ]);

        $query = Task::with(['user', 'category'])
            ->where('user_id', Auth::id());

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $escapedSearch = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('title', 'ilike', "%{$escapedSearch}%")
                    ->orWhere('description', 'ilike', "%{$escapedSearch}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        } elseif ($request->filled('category')) {
            $categoryParam = (string) $request->input('category');

            // Prioritize matching category by name for this user (supports numeric names like "2026")
            $matchingCategoryByName = Category::where('user_id', Auth::id())
                ->where(function ($q) use ($categoryParam) {
                    $q->where('name', $categoryParam)
                        ->orWhere('name', 'ilike', $categoryParam);
                })
                ->first();

            if ($matchingCategoryByName) {
                $query->where('category_id', $matchingCategoryByName->id);
            } elseif (is_numeric($categoryParam)) {
                $query->where('category_id', (int) $categoryParam);
            } else {
                $query->whereHas('category', function ($q) use ($categoryParam) {
                    $q->where('name', 'ilike', $categoryParam);
                });
            }
        }

        $deadlineFilter = $request->input('deadline') ?: $request->input('due_date');
        if ($deadlineFilter) {
            match ($deadlineFilter) {
                'today', 'hari_ini' => $query->whereDate('due_date', today()),
                'upcoming', 'mendatang' => $query->whereDate('due_date', '>', today()),
                'overdue', 'terlambat' => $query->where('status', 'pending')->whereDate('due_date', '<', today()),
                default => null,
            };
        }

        $sort = $request->input('sort', 'latest') ?: 'latest';

        match ($sort) {
            'oldest', 'terlama' => $query->oldest(),
            'title_asc', 'judul_asc', 'title_a_z', 'judul_a_z' => $query->orderBy('title', 'asc'),
            'title_desc', 'judul_desc', 'title_z_a', 'judul_z_a' => $query->orderBy('title', 'desc'),
            'priority_desc', 'prioritas_desc', 'prioritas_tertinggi' => $query->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END ASC")->latest(),
            'priority_asc', 'prioritas_asc', 'prioritas_terendah' => $query->orderByRaw("CASE priority WHEN 'low' THEN 1 WHEN 'medium' THEN 2 WHEN 'high' THEN 3 ELSE 4 END ASC")->latest(),
            'due_date_asc', 'deadline_asc', 'deadline_terdekat' => $query->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC')->latest(),
            'due_date_desc', 'deadline_desc', 'deadline_terjauh' => $query->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date DESC')->latest(),
            default => $query->latest(),
        };

        $tasks = $query
            ->paginate(5)
            ->withQueryString();

        $categories = Category::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return view('tasks.index', compact('tasks', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return view('tasks.create', compact('categories'));
    }

    public function store(TaskRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (! empty($validated['due_date']) && ! empty($request->input('due_time'))) {
            $validated['due_date'] = Carbon::parse($validated['due_date'].' '.$request->input('due_time'))->format('Y-m-d H:i:s');
        }
        unset($validated['due_time']);

        Task::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'] ?? 'medium',
            'due_date' => $validated['due_date'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
        ]);

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task Berhasil dibuat.');
    }

    public function show(Task $task): View
    {
        Gate::authorize('view', $task);

        $task->load(['user', 'category']);

        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task): View
    {
        Gate::authorize('update', $task);

        $categories = Category::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return view('tasks.edit', compact('task', 'categories'));
    }

    public function update(TaskRequest $request, Task $task): RedirectResponse
    {
        Gate::authorize('update', $task);

        $validated = $request->validated();
        if (array_key_exists('priority', $validated) && $validated['priority'] === null) {
            $validated['priority'] = 'medium';
        }

        if (! empty($validated['due_date']) && ! empty($request->input('due_time'))) {
            $validated['due_date'] = Carbon::parse($validated['due_date'].' '.$request->input('due_time'))->format('Y-m-d H:i:s');
        } elseif (array_key_exists('due_date', $validated) && empty($validated['due_date'])) {
            $validated['due_date'] = null;
        }
        unset($validated['due_time']);

        $task->update($validated);

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task berhasil diperbarui.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        Gate::authorize('delete', $task);

        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task berhasil dihapus.');
    }
}
