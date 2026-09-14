<x-app-layout :title="'Daftar Task - Task Manager'">
    <div class="container-fluid px-3 px-md-4 px-lg-5 py-4">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 py-2.5 px-3 mb-4 rounded-2 border" style="background-color: #ECFDF5; border-color: rgba(16, 185, 129, 0.3); color: #065F46; font-size: 13px;">
                <x-iconly name="check" size="16" style="color: #10B981;" />
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger py-2.5 px-3 mb-4 rounded-2 border" style="background-color: #FEF2F2; border-color: rgba(239, 35, 60, 0.3); color: #991B1B; font-size: 13px;">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Page Heading + Primary Action -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 pb-3 mb-4 border-bottom" style="border-color: #DFE5EC !important;">
            <div>
                <span class="text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.08em; color: #8D99AE;">Manajemen</span>
                <h1 class="h3 fw-bold mb-0 mt-1" style="color: #2B2D42;">Daftar Task</h1>
            </div>
            <div>
                <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-primary px-3 py-1.5 fw-medium d-inline-flex align-items-center gap-1.5" style="background-color: #4361EE; border-color: #4361EE;">
                    <x-iconly name="plus" size="16" />
                    <span>Tambah Task</span>
                </a>
            </div>
        </div>

        <!-- Filter, Search & Sort Card -->
        <div class="card p-3 mb-4" style="border: 1px solid #DFE5EC; background-color: #FFFFFF;">
            <form action="{{ route('tasks.index') }}" method="GET" class="row g-2 align-items-center">
                <!-- Search Input -->
                <div class="col-12 col-md-3 col-xl-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0" style="border-color: #DFE5EC; color: #8D99AE;">
                            <x-iconly name="search" size="14" />
                        </span>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari task..."
                            class="form-control form-control-sm border-start-0"
                            style="border-color: #DFE5EC;"
                        >
                    </div>
                </div>

                <!-- Status Select -->
                <div class="col-6 col-md-2 col-xl-2">
                    <select name="status" class="form-select form-select-sm" style="border-color: #DFE5EC; color: #2B2D42;">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>
                            Pending
                        </option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>
                            Completed
                        </option>
                    </select>
                </div>

                <!-- Priority Select -->
                <div class="col-6 col-md-2 col-xl-2">
                    <select name="priority" class="form-select form-select-sm" style="border-color: #DFE5EC; color: #2B2D42;">
                        <option value="">Semua Prioritas</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>
                            High
                        </option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>
                            Medium
                        </option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>
                            Low
                        </option>
                    </select>
                </div>

                @php
                    $categories = $categories ?? collect();
                @endphp

                <!-- Category Select -->
                <div class="col-6 col-md-2 col-xl-2">
                    <select name="category_id" class="form-select form-select-sm" style="border-color: #DFE5EC; color: #2B2D42;">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id || (string) request('category') === (string) $cat->id || request('category') === $cat->name ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Deadline Select -->
                <div class="col-6 col-md-2 col-xl-1">
                    <select name="deadline" class="form-select form-select-sm" style="border-color: #DFE5EC; color: #2B2D42;">
                        <option value="">Semua Deadline</option>
                        <option value="today" {{ request('deadline') === 'today' || request('deadline') === 'hari_ini' ? 'selected' : '' }}>
                            Hari Ini
                        </option>
                        <option value="upcoming" {{ request('deadline') === 'upcoming' || request('deadline') === 'mendatang' ? 'selected' : '' }}>
                            Mendatang
                        </option>
                        <option value="overdue" {{ request('deadline') === 'overdue' || request('deadline') === 'terlambat' ? 'selected' : '' }}>
                            Terlambat
                        </option>
                    </select>
                </div>

                <!-- Sort Select -->
                <div class="col-8 col-md-2 col-xl-1">
                    <select name="sort" class="form-select form-select-sm" style="border-color: #DFE5EC; color: #2B2D42;">
                        <option value="latest" {{ request('sort', 'latest') === 'latest' || request('sort') === 'terbaru' ? 'selected' : '' }}>
                            Terbaru
                        </option>
                        <option value="oldest" {{ in_array(request('sort'), ['oldest', 'terlama']) ? 'selected' : '' }}>
                            Terlama
                        </option>
                        <option value="title_asc" {{ in_array(request('sort'), ['title_asc', 'judul_asc', 'title_a_z', 'judul_a_z']) ? 'selected' : '' }}>
                            Judul A-Z
                        </option>
                        <option value="title_desc" {{ in_array(request('sort'), ['title_desc', 'judul_desc', 'title_z_a', 'judul_z_a']) ? 'selected' : '' }}>
                            Judul Z-A
                        </option>
                        <option value="priority_desc" {{ in_array(request('sort'), ['priority_desc', 'prioritas_desc', 'prioritas_tertinggi']) ? 'selected' : '' }}>
                            Prioritas Tertinggi
                        </option>
                        <option value="priority_asc" {{ in_array(request('sort'), ['priority_asc', 'prioritas_asc', 'prioritas_terendah']) ? 'selected' : '' }}>
                            Prioritas Terendah
                        </option>
                        <option value="due_date_asc" {{ in_array(request('sort'), ['due_date_asc', 'deadline_asc', 'deadline_terdekat']) ? 'selected' : '' }}>
                            Deadline Terdekat
                        </option>
                        <option value="due_date_desc" {{ in_array(request('sort'), ['due_date_desc', 'deadline_desc', 'deadline_terjauh']) ? 'selected' : '' }}>
                            Deadline Terjauh
                        </option>
                    </select>
                </div>

                <!-- Submit & Reset Buttons -->
                <div class="col-4 col-md-1 col-xl-1 d-flex align-items-center gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-medium" style="background-color: #4361EE; border-color: #4361EE;">
                        Filter
                    </button>
                    @if (request()->hasAny(['search', 'status', 'priority', 'deadline', 'due_date', 'category', 'category_id', 'sort']))
                        <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary px-2 border-0" style="color: #8D99AE;" title="Reset Filter">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Task List Table Card -->
        <div class="card" style="border: 1px solid #DFE5EC;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead style="background-color: #F8FAFC; color: #8D99AE; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">
                        <tr>
                            <th scope="col" class="ps-4 py-3" style="width: 120px;">Status</th>
                            <th scope="col" class="py-3" style="width: 110px;">Prioritas</th>
                            <th scope="col" class="py-3">Judul & Deskripsi</th>
                            <th scope="col" class="py-3" style="width: 160px;">Deadline</th>
                            <th scope="col" class="py-3 d-none d-md-table-cell" style="width: 140px;">Dibuat</th>
                            <th scope="col" class="pe-4 py-3 text-end" style="width: 130px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tasks as $task)
                            <tr>
                                <!-- Status: dot + label -->
                                <td class="ps-4 whitespace-nowrap">
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

                                <!-- Prioritas: badge subtle -->
                                <td class="whitespace-nowrap">
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

                                <!-- Judul & Deskripsi -->
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

                                <!-- Deadline -->
                                <td class="whitespace-nowrap">
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

                                <!-- Dibuat -->
                                <td class="d-none d-md-table-cell text-muted small whitespace-nowrap">
                                    {{ $task->created_at->format('d M Y') }}
                                </td>

                                <!-- Aksi -->
                                <td class="pe-4 text-end whitespace-nowrap">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none small fw-medium" style="color: #8D99AE;">
                                            Detail
                                        </a>
                                        <span style="color: #DFE5EC;">|</span>
                                        <a href="{{ route('tasks.edit', $task) }}" class="text-decoration-none small fw-medium" style="color: #4361EE;">
                                            Edit
                                        </a>
                                        <span style="color: #DFE5EC;">|</span>
                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus task ini?')"
                                                class="btn btn-link p-0 text-decoration-none small fw-medium border-0"
                                                style="color: #EF233C;"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-5 text-center">
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-2" style="background-color: #F8FAFC; color: #8D99AE;">
                                        <x-iconly name="tick-square" size="24" />
                                    </div>
                                    <p class="small text-muted mb-2">
                                        @if (request()->hasAny(['search', 'status', 'priority', 'deadline', 'due_date', 'category', 'category_id', 'sort']))
                                            Tidak ada task yang cocok dengan kriteria pencarian.
                                        @else
                                            Belum ada task yang terdaftar.
                                        @endif
                                    </p>
                                    @if (request()->hasAny(['search', 'status', 'priority', 'deadline', 'due_date', 'category', 'category_id', 'sort']))
                                        <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary">
                                            Reset filter
                                        </a>
                                    @else
                                        <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-primary">
                                            Tambah Task
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4 d-flex justify-content-center">
            {{ $tasks->links() }}
        </div>
    </div>
</x-app-layout>