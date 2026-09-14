<x-app-layout :title="'Dashboard - Task Manager'">
    @php
        $totalTasks = $totalTasks ?? 0;
        $pendingTasks = $pendingTasks ?? 0;
        $completedTasks = $completedTasks ?? 0;
        $overdueTasks = $overdueTasks ?? ($overdue ?? 0);
        $dueTodayTasks = $dueTodayTasks ?? ($dueToday ?? 0);
        $completionRate = $completionRate ?? 0;
        $recentTasks = $recentTasks ?? collect();
        $upcomingTasks = $upcomingTasks ?? collect();
        $prioritySummary = $prioritySummary ?? ['high' => 0, 'medium' => 0, 'low' => 0];
        $categorySummary = $categorySummary ?? collect();
        $uncategorizedCount = $uncategorizedCount ?? 0;
    @endphp

    <div class="container-fluid px-3 px-md-4 px-lg-5 py-4">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 pb-3 mb-4 border-bottom" style="border-color: #DFE5EC !important;">
            <div>
                <span class="text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.08em; color: #8D99AE;">Workspace Overview</span>
                <h1 class="h3 fw-bold mb-0 mt-1" style="color: #2B2D42;">
                    {{ Auth::user()->name }}
                </h1>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary px-3 py-1.5 fw-medium d-inline-flex align-items-center gap-1.5" style="border-color: #DFE5EC; color: #2B2D42; background-color: #FFFFFF;">
                    Lihat Semua Task
                </a>
                <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-primary px-3 py-1.5 fw-medium d-inline-flex align-items-center gap-1.5" style="background-color: #4361EE; border-color: #4361EE;">
                    <x-iconly name="plus" size="16" />
                    <span>Tambah Task</span>
                </a>
            </div>
        </div>

        <!-- 6 Metrics Ledger Cards -->
        <div class="row g-3 mb-4">
            <!-- 1. Total Task -->
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('tasks.index') }}" class="card text-decoration-none h-100 p-3 transition-all" style="border: 1px solid #DFE5EC; background-color: #FFFFFF;" onmouseover="this.style.borderColor='#4361EE'" onmouseout="this.style.borderColor='#DFE5EC'">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-semibold" style="font-size: 11px; color: #8D99AE;">Total Task</span>
                        <div class="rounded-2 p-1 d-flex align-items-center justify-content-center" style="background-color: #EEF2FF; color: #4361EE;">
                            <x-iconly name="activity" size="15" />
                        </div>
                    </div>
                    <span class="h3 fw-bold mb-0" style="color: #2B2D42;">
                        {{ $totalTasks }}
                    </span>
                </a>
            </div>

            <!-- 2. Completed -->
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('tasks.index', ['status' => 'completed']) }}" class="card text-decoration-none h-100 p-3 transition-all" style="border: 1px solid #DFE5EC; background-color: #FFFFFF;" onmouseover="this.style.borderColor='#10B981'" onmouseout="this.style.borderColor='#DFE5EC'">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-semibold" style="font-size: 11px; color: #8D99AE;">Completed</span>
                        <div class="rounded-2 p-1 d-flex align-items-center justify-content-center" style="background-color: #ECFDF5; color: #10B981;">
                            <x-iconly name="check" size="15" />
                        </div>
                    </div>
                    <span class="h3 fw-bold mb-0" style="color: #10B981;">
                        {{ $completedTasks }}
                    </span>
                </a>
            </div>

            <!-- 3. Pending -->
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('tasks.index', ['status' => 'pending']) }}" class="card text-decoration-none h-100 p-3 transition-all" style="border: 1px solid #DFE5EC; background-color: #FFFFFF;" onmouseover="this.style.borderColor='#F59E0B'" onmouseout="this.style.borderColor='#DFE5EC'">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-semibold" style="font-size: 11px; color: #8D99AE;">Pending</span>
                        <div class="rounded-2 p-1 d-flex align-items-center justify-content-center" style="background-color: #FFFBEB; color: #F59E0B;">
                            <x-iconly name="clock" size="15" />
                        </div>
                    </div>
                    <span class="h3 fw-bold mb-0" style="color: #F59E0B;">
                        {{ $pendingTasks }}
                    </span>
                </a>
            </div>

            <!-- 4. Overdue -->
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('tasks.index', ['deadline' => 'overdue']) }}" class="card text-decoration-none h-100 p-3 transition-all" style="border: 1px solid #DFE5EC; background-color: #FFFFFF;" onmouseover="this.style.borderColor='#EF233C'" onmouseout="this.style.borderColor='#DFE5EC'">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-semibold" style="font-size: 11px; color: #8D99AE;">Overdue</span>
                        <div class="rounded-2 p-1 d-flex align-items-center justify-content-center" style="background-color: #FEF2F2; color: #EF233C;">
                            <x-iconly name="calendar" size="15" />
                        </div>
                    </div>
                    <span class="h3 fw-bold mb-0" style="color: {{ $overdueTasks > 0 ? '#EF233C' : '#2B2D42' }};">
                        {{ $overdueTasks }}
                    </span>
                </a>
            </div>

            <!-- 5. Due Today -->
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('tasks.index', ['deadline' => 'today']) }}" class="card text-decoration-none h-100 p-3 transition-all" style="border: 1px solid #DFE5EC; background-color: #FFFFFF;" onmouseover="this.style.borderColor='#F59E0B'" onmouseout="this.style.borderColor='#DFE5EC'">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-semibold" style="font-size: 11px; color: #8D99AE;">Due Today</span>
                        <div class="rounded-2 p-1 d-flex align-items-center justify-content-center" style="background-color: #FFFBEB; color: #F59E0B;">
                            <x-iconly name="time" size="15" />
                        </div>
                    </div>
                    <span class="h3 fw-bold mb-0" style="color: {{ $dueTodayTasks > 0 ? '#F59E0B' : '#2B2D42' }};">
                        {{ $dueTodayTasks }}
                    </span>
                </a>
            </div>

            <!-- 6. Completion Rate -->
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card h-100 p-3" style="border: 1px solid #DFE5EC; background-color: #FFFFFF;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-semibold" style="font-size: 11px; color: #8D99AE;">Completion Rate</span>
                        <div class="rounded-2 p-1 d-flex align-items-center justify-content-center" style="background-color: #EEF2FF; color: #4361EE;">
                            <x-iconly name="activity" size="15" />
                        </div>
                    </div>
                    <span class="h3 fw-bold mb-2" style="color: #2B2D42;">
                        {{ $completionRate }}%
                    </span>
                    <div class="progress" style="height: 5px; background-color: #DFE5EC;">
                        <div class="progress-bar" role="progressbar" style="width: {{ min(100, max(0, $completionRate)) }}%; background-color: #4361EE;" aria-valuenow="{{ $completionRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Ringkasan Priority & Ringkasan Category -->
        <div class="row g-4 mb-4">
            <!-- Ringkasan Priority -->
            <div class="col-12 col-lg-6">
                <div class="card h-100" style="border: 1px solid #DFE5EC;">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4" style="border-bottom: 1px solid #DFE5EC;">
                        <div class="d-flex align-items-center gap-2">
                            <x-iconly name="flag" size="16" style="color: #4361EE;" />
                            <h2 class="h6 fw-bold mb-0 text-uppercase" style="font-size: 12px; letter-spacing: 0.05em; color: #2B2D42;">Ringkasan Priority</h2>
                        </div>
                        <span style="font-size: 12px; color: #8D99AE;">Berdasarkan prioritas</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex flex-column gap-2">
                            <!-- High -->
                            <a href="{{ route('tasks.index', ['priority' => 'high']) }}" class="d-flex align-items-center justify-content-between p-2.5 rounded-2 text-decoration-none transition-all" style="background-color: #F8FAFC;" onmouseover="this.style.backgroundColor='#FEF2F2'" onmouseout="this.style.backgroundColor='#F8FAFC'">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle d-inline-block" style="width: 8px; height: 8px; background-color: #EF233C;"></span>
                                    <span class="fw-semibold small" style="color: #2B2D42;">High</span>
                                </div>
                                <span class="badge rounded-pill fw-bold" style="background-color: #FEF2F2; color: #EF233C; border: 1px solid rgba(239, 35, 60, 0.2);">
                                    {{ $prioritySummary['high'] ?? 0 }} task
                                </span>
                            </a>

                            <!-- Medium -->
                            <a href="{{ route('tasks.index', ['priority' => 'medium']) }}" class="d-flex align-items-center justify-content-between p-2.5 rounded-2 text-decoration-none transition-all" style="background-color: #F8FAFC;" onmouseover="this.style.backgroundColor='#FFFBEB'" onmouseout="this.style.backgroundColor='#F8FAFC'">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle d-inline-block" style="width: 8px; height: 8px; background-color: #F59E0B;"></span>
                                    <span class="fw-semibold small" style="color: #2B2D42;">Medium</span>
                                </div>
                                <span class="badge rounded-pill fw-bold" style="background-color: #FFFBEB; color: #D97706; border: 1px solid rgba(245, 158, 11, 0.2);">
                                    {{ $prioritySummary['medium'] ?? 0 }} task
                                </span>
                            </a>

                            <!-- Low -->
                            <a href="{{ route('tasks.index', ['priority' => 'low']) }}" class="d-flex align-items-center justify-content-between p-2.5 rounded-2 text-decoration-none transition-all" style="background-color: #F8FAFC;" onmouseover="this.style.backgroundColor='#EEF2FF'" onmouseout="this.style.backgroundColor='#F8FAFC'">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle d-inline-block" style="width: 8px; height: 8px; background-color: #8D99AE;"></span>
                                    <span class="fw-semibold small" style="color: #2B2D42;">Low</span>
                                </div>
                                <span class="badge rounded-pill fw-bold" style="background-color: #F1F5F9; color: #64748B; border: 1px solid #DFE5EC;">
                                    {{ $prioritySummary['low'] ?? 0 }} task
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Category -->
            <div class="col-12 col-lg-6">
                <div class="card h-100" style="border: 1px solid #DFE5EC;">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4" style="border-bottom: 1px solid #DFE5EC;">
                        <div class="d-flex align-items-center gap-2">
                            <x-iconly name="folder" size="16" style="color: #4361EE;" />
                            <h2 class="h6 fw-bold mb-0 text-uppercase" style="font-size: 12px; letter-spacing: 0.05em; color: #2B2D42;">Ringkasan Category</h2>
                        </div>
                        <a href="{{ route('categories.index') }}" class="text-decoration-none fw-semibold small d-inline-flex align-items-center gap-1" style="color: #4361EE;">
                            <span>Kelola</span>
                            <x-iconly name="arrow-right" size="12" />
                        </a>
                    </div>
                    <div class="card-body p-3 overflow-y-auto" style="max-height: 220px;">
                        @if ($categorySummary->isNotEmpty())
                            <div class="d-flex flex-column gap-2">
                                @foreach ($categorySummary as $cat)
                                    <a href="{{ route('tasks.index', ['category_id' => $cat->id]) }}" class="d-flex align-items-center justify-content-between p-2.5 rounded-2 text-decoration-none transition-all" style="background-color: #F8FAFC;" onmouseover="this.style.backgroundColor='#EEF2FF'" onmouseout="this.style.backgroundColor='#F8FAFC'">
                                        <div class="d-flex align-items-center gap-2">
                                            <x-iconly name="folder" size="14" style="color: #4361EE;" />
                                            <span class="fw-semibold small" style="color: #2B2D42;">{{ $cat->name }}</span>
                                        </div>
                                        <span class="badge rounded-pill fw-bold" style="background-color: #EEF2FF; color: #4361EE; border: 1px solid rgba(67, 97, 238, 0.2);">
                                            {{ $cat->tasks_count }} task
                                        </span>
                                    </a>
                                @endforeach
                                @if ($uncategorizedCount > 0)
                                    <div class="d-flex align-items-center justify-content-between p-2.5 rounded-2" style="background-color: #F8FAFC;">
                                        <span class="small text-muted">Tanpa Kategori</span>
                                        <span class="small fw-medium text-muted">{{ $uncategorizedCount }} task</span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="py-4 text-center">
                                <p class="small text-muted mb-2">Belum ada kategori yang dibuat.</p>
                                <a href="{{ route('categories.create') }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                    <x-iconly name="plus" size="14" />
                                    <span>Buat Kategori Baru</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Tasks Section -->
        <div class="card mb-4" style="border: 1px solid #DFE5EC;">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4" style="border-bottom: 1px solid #DFE5EC;">
                <div>
                    <h2 class="h6 fw-bold mb-0 text-uppercase" style="font-size: 12px; letter-spacing: 0.05em; color: #2B2D42;">Upcoming Tasks</h2>
                    <span style="font-size: 11px; color: #8D99AE;">Tugas Mendatang</span>
                </div>
                @if ($upcomingTasks->isNotEmpty())
                    <a href="{{ route('tasks.index', ['deadline' => 'upcoming']) }}" class="text-decoration-none fw-semibold small d-inline-flex align-items-center gap-1" style="color: #4361EE;">
                        <span>Lihat Semua</span>
                        <x-iconly name="arrow-right" size="12" />
                    </a>
                @endif
            </div>

            @if ($upcomingTasks->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead style="background-color: #F8FAFC; color: #8D99AE; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">
                            <tr>
                                <th scope="col" class="ps-4 py-3" style="width: 120px;">Status</th>
                                <th scope="col" class="py-3" style="width: 110px;">Prioritas</th>
                                <th scope="col" class="py-3">Judul & Deskripsi</th>
                                <th scope="col" class="py-3" style="width: 140px;">Deadline</th>
                                <th scope="col" class="pe-4 py-3 text-end" style="width: 110px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($upcomingTasks as $task)
                                <tr>
                                    <td class="ps-4">
                                        <span class="d-inline-flex align-items-center gap-1.5 fw-medium small" style="color: #F59E0B;">
                                            <span class="rounded-circle d-inline-block" style="width: 6px; height: 6px; background-color: #F59E0B;"></span>
                                            <span>Pending</span>
                                        </span>
                                    </td>
                                    <td>
                                        @if ($task->priority === 'high')
                                            <span class="badge rounded-pill fw-semibold" style="background-color: #FEF2F2; color: #EF233C; border: 1px solid rgba(239, 35, 60, 0.2); font-size: 10px;">
                                                High
                                            </span>
                                        @elseif ($task->priority === 'low')
                                            <span class="badge rounded-pill fw-semibold" style="background-color: #F1F5F9; color: #64748B; border: 1px solid #DFE5EC; font-size: 10px;">
                                                Low
                                            </span>
                                        @else
                                            <span class="badge rounded-pill fw-semibold" style="background-color: #FFFBEB; color: #D97706; border: 1px solid rgba(245, 158, 11, 0.2); font-size: 10px;">
                                                Medium
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <a href="{{ route('tasks.show', $task) }}" class="fw-semibold text-decoration-none" style="color: #2B2D42;">
                                                {{ $task->title }}
                                            </a>
                                            @if ($task->category)
                                                <span class="badge rounded-pill" style="background-color: #EEF2FF; color: #4361EE; border: 1px solid rgba(67, 97, 238, 0.2); font-size: 10px;">
                                                    {{ $task->category->name }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($task->description)
                                            <p class="text-muted small mb-0 text-truncate" style="max-width: 480px;">
                                                {{ $task->description }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="text-nowrap" style="color: #2B2D42;">
                                        <div class="d-flex align-items-center gap-1.5">
                                            <x-iconly name="calendar" size="13" style="color: #8D99AE;" />
                                            <span>{{ $task->due_date ? $task->due_date->format('d M Y') : '—' }}</span>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end text-nowrap">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none small fw-medium" style="color: #8D99AE;">
                                                Detail
                                            </a>
                                            <span style="color: #DFE5EC;">|</span>
                                            <a href="{{ route('tasks.edit', $task) }}" class="text-decoration-none small fw-medium" style="color: #4361EE;">
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 text-center">
                    <p class="small text-muted mb-0">Tidak ada tugas mendatang.</p>
                </div>
            @endif
        </div>

        <!-- Recent Tasks Section -->
        <div class="card" style="border: 1px solid #DFE5EC;">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4" style="border-bottom: 1px solid #DFE5EC;">
                <div>
                    <h2 class="h6 fw-bold mb-0 text-uppercase" style="font-size: 12px; letter-spacing: 0.05em; color: #2B2D42;">Recent Tasks</h2>
                    <span style="font-size: 11px; color: #8D99AE;">Task Terbaru</span>
                </div>
                @if ($recentTasks->isNotEmpty())
                    <a href="{{ route('tasks.index') }}" class="text-decoration-none fw-semibold small d-inline-flex align-items-center gap-1" style="color: #4361EE;">
                        <span>Lihat Semua</span>
                        <x-iconly name="arrow-right" size="12" />
                    </a>
                @endif
            </div>

            @if ($recentTasks->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead style="background-color: #F8FAFC; color: #8D99AE; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">
                            <tr>
                                <th scope="col" class="ps-4 py-3" style="width: 120px;">Status</th>
                                <th scope="col" class="py-3" style="width: 110px;">Prioritas</th>
                                <th scope="col" class="py-3">Judul & Deskripsi</th>
                                <th scope="col" class="py-3" style="width: 150px;">Deadline</th>
                                <th scope="col" class="py-3 d-none d-md-table-cell" style="width: 150px;">Dibuat</th>
                                <th scope="col" class="pe-4 py-3 text-end" style="width: 110px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentTasks as $task)
                                <tr>
                                    <td class="ps-4">
                                        @if ($task->status === 'completed')
                                            <span class="d-inline-flex align-items-center gap-1.5 fw-medium small" style="color: #10B981;">
                                                <span class="rounded-circle d-inline-block" style="width: 6px; height: 6px; background-color: #10B981;"></span>
                                                <span>Completed</span>
                                            </span>
                                        @else
                                            <span class="d-inline-flex align-items-center gap-1.5 fw-medium small" style="color: #F59E0B;">
                                                <span class="rounded-circle d-inline-block" style="width: 6px; height: 6px; background-color: #F59E0B;"></span>
                                                <span>Pending</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($task->priority === 'high')
                                            <span class="badge rounded-pill fw-semibold" style="background-color: #FEF2F2; color: #EF233C; border: 1px solid rgba(239, 35, 60, 0.2); font-size: 10px;">
                                                High
                                            </span>
                                        @elseif ($task->priority === 'low')
                                            <span class="badge rounded-pill fw-semibold" style="background-color: #F1F5F9; color: #64748B; border: 1px solid #DFE5EC; font-size: 10px;">
                                                Low
                                            </span>
                                        @else
                                            <span class="badge rounded-pill fw-semibold" style="background-color: #FFFBEB; color: #D97706; border: 1px solid rgba(245, 158, 11, 0.2); font-size: 10px;">
                                                Medium
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <a href="{{ route('tasks.show', $task) }}" class="fw-semibold text-decoration-none" style="color: #2B2D42;">
                                                {{ $task->title }}
                                            </a>
                                            @if ($task->category)
                                                <span class="badge rounded-pill" style="background-color: #EEF2FF; color: #4361EE; border: 1px solid rgba(67, 97, 238, 0.2); font-size: 10px;">
                                                    {{ $task->category->name }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($task->description)
                                            <p class="text-muted small mb-0 text-truncate" style="max-width: 480px;">
                                                {{ $task->description }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        @if ($task->due_date)
                                            @php
                                                $isOverdue = $task->due_date->isPast() && !$task->due_date->isToday() && $task->status !== 'completed';
                                                $isToday = $task->due_date->isToday();
                                            @endphp
                                            <div class="d-flex align-items-center gap-1.5">
                                                <x-iconly name="calendar" size="13" style="color: {{ $isOverdue ? '#EF233C' : ($isToday ? '#F59E0B' : '#8D99AE') }};" />
                                                <span class="small {{ $isOverdue ? 'text-danger fw-semibold' : ($isToday ? 'text-warning fw-semibold' : '') }}" style="color: {{ $isOverdue ? '#EF233C' : ($isToday ? '#D97706' : '#2B2D42') }};">
                                                    {{ $task->due_date->format('d M Y') }}
                                                </span>
                                                @if ($isOverdue)
                                                    <span class="badge rounded-pill" style="background-color: #FEF2F2; color: #EF233C; font-size: 10px;">Terlambat</span>
                                                @elseif ($isToday)
                                                    <span class="badge rounded-pill" style="background-color: #FFFBEB; color: #D97706; font-size: 10px;">Hari ini</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="d-none d-md-table-cell text-muted small text-nowrap">
                                        {{ $task->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="pe-4 text-end text-nowrap">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none small fw-medium" style="color: #8D99AE;">
                                                Detail
                                            </a>
                                            <span style="color: #DFE5EC;">|</span>
                                            <a href="{{ route('tasks.edit', $task) }}" class="text-decoration-none small fw-medium" style="color: #4361EE;">
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <!-- Empty State -->
                <div class="p-5 text-center">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3" style="background-color: #EEF2FF; color: #4361EE;">
                        <x-iconly name="tick-square" size="28" />
                    </div>
                    <p class="h6 fw-bold mb-1" style="color: #2B2D42;">Belum Ada Task</p>
                    <p class="small text-muted mb-3">Anda belum memiliki task apapun.</p>
                    <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-primary px-3 py-1.5 fw-medium d-inline-flex align-items-center gap-1.5">
                        <x-iconly name="plus" size="16" />
                        <span>Tambah Task Pertama</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>