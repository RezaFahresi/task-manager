@php
    $task = (isset($task) && $task instanceof \App\Models\Task) ? $task : new \App\Models\Task();
@endphp
<x-app-layout :title="'Detail Task - Task Manager'">
    <div class="container py-4" style="max-width: 800px;">
        <!-- Back Link -->
        <div class="mb-3">
            <a href="{{ route('tasks.index') }}" class="text-decoration-none small fw-medium d-inline-flex align-items-center gap-1" style="color: #8D99AE;">
                <x-iconly name="arrow-left" size="14" />
                <span>Kembali ke Tasks</span>
            </a>
        </div>

        <!-- Heading + Action -->
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 pb-3 mb-4 border-bottom" style="border-color: #DFE5EC !important;">
            <div>
                <span class="text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.08em; color: #8D99AE;">Detail</span>
                <h1 class="h3 fw-bold mb-0 mt-1" style="color: #2B2D42;">Detail Task</h1>
                <p class="small text-muted mb-0 mt-1">Informasi lengkap mengenai task ini.</p>
            </div>
            <div>
                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-primary px-3 py-1.5 fw-medium d-inline-flex align-items-center gap-1.5" style="background-color: #4361EE; border-color: #4361EE;">
                    <x-iconly name="edit" size="15" />
                    <span>Edit Task</span>
                </a>
            </div>
        </div>

        <!-- Detail Card -->
        <div class="card" style="border: 1px solid #DFE5EC; background-color: #FFFFFF;">
            <!-- Header: Judul, Status & Prioritas -->
            <div class="p-4 border-bottom" style="border-color: #DFE5EC !important;">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-2 flex-wrap">
                    <span class="text-uppercase fw-semibold" style="font-size: 11px; color: #8D99AE;">Judul</span>
                    <div class="d-flex align-items-center gap-2">
                        @if ($task->status === 'completed')
                            <span class="badge rounded-pill fw-semibold" style="background-color: #ECFDF5; color: #10B981; border: 1px solid rgba(16, 185, 129, 0.2);">
                                Completed
                            </span>
                        @else
                            <span class="badge rounded-pill fw-semibold" style="background-color: #FFFBEB; color: #D97706; border: 1px solid rgba(245, 158, 11, 0.2);">
                                Pending
                            </span>
                        @endif

                        <span style="color: #DFE5EC;">•</span>

                        @if ($task->priority === 'high')
                            <span class="badge rounded-pill fw-semibold" style="background-color: #FEF2F2; color: #EF233C; border: 1px solid rgba(239, 35, 60, 0.2);">
                                High
                            </span>
                        @elseif ($task->priority === 'low')
                            <span class="badge rounded-pill fw-semibold" style="background-color: #F1F5F9; color: #64748B; border: 1px solid #DFE5EC;">
                                Low
                            </span>
                        @else
                            <span class="badge rounded-pill fw-semibold" style="background-color: #FFFBEB; color: #D97706; border: 1px solid rgba(245, 158, 11, 0.2);">
                                Medium
                            </span>
                        @endif
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h2 class="h4 fw-bold mb-0" style="color: #2B2D42;">
                        {{ $task->title }}
                    </h2>
                    @if ($task->category)
                        <span class="badge rounded-pill" style="background-color: #EEF2FF; color: #4361EE; border: 1px solid rgba(67, 97, 238, 0.2); font-size: 11px;">
                            {{ $task->category->name }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="p-4 border-bottom" style="border-color: #DFE5EC !important;">
                <span class="text-uppercase fw-semibold d-block mb-2" style="font-size: 11px; color: #8D99AE;">Deskripsi</span>
                @if ($task->description)
                    <p class="mb-0" style="color: #2B2D42; font-size: 14px; line-height: 1.6; white-space: pre-line;">
                        {{ $task->description }}
                    </p>
                @else
                    <p class="small text-muted fst-italic mb-0">
                        Tidak ada deskripsi untuk task ini.
                    </p>
                @endif
            </div>

            <!-- Metadata Info Grid -->
            <div class="p-4 border-bottom" style="border-color: #DFE5EC !important;">
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <span class="text-uppercase fw-semibold d-block mb-1" style="font-size: 11px; color: #8D99AE;">Kategori</span>
                        <p class="small fw-semibold mb-0" style="color: #2B2D42;">
                            {{ $task->category?->name ?? '—' }}
                        </p>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-uppercase fw-semibold d-block mb-1" style="font-size: 11px; color: #8D99AE;">Prioritas</span>
                        <p class="small fw-semibold mb-0 text-capitalize" style="color: #2B2D42;">
                            {{ $task->priority ?? 'Medium' }}
                        </p>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-uppercase fw-semibold d-block mb-1" style="font-size: 11px; color: #8D99AE;">Deadline</span>
                        @if ($task->due_date)
                            @php
                                $isOverdue = $task->due_date->isPast() && !$task->due_date->isToday() && $task->status !== 'completed';
                                $isToday = $task->due_date->isToday();
                            @endphp
                            <div class="d-flex align-items-center gap-1.5">
                                <x-iconly name="calendar" size="13" style="color: {{ $isOverdue ? '#EF233C' : ($isToday ? '#F59E0B' : '#8D99AE') }};" />
                                <span class="small fw-semibold" style="color: {{ $isOverdue ? '#EF233C' : ($isToday ? '#D97706' : '#2B2D42') }};">
                                    {{ $task->due_date->format('d M Y') }}
                                </span>
                                @if ($isOverdue)
                                    <span class="badge rounded-pill" style="background-color: #FEF2F2; color: #EF233C; font-size: 10px;">Terlambat</span>
                                @elseif ($isToday)
                                    <span class="badge rounded-pill" style="background-color: #FFFBEB; color: #D97706; font-size: 10px;">Hari Ini</span>
                                @endif
                            </div>
                        @else
                            <span class="small text-muted">—</span>
                        @endif
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-uppercase fw-semibold d-block mb-1" style="font-size: 11px; color: #8D99AE;">Pemilik Task</span>
                        <p class="small fw-semibold mb-0" style="color: #2B2D42;">
                            {{ $task->user->name ?? Auth::user()->name ?? '-' }}
                        </p>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-uppercase fw-semibold d-block mb-1" style="font-size: 11px; color: #8D99AE;">Dibuat Pada</span>
                        <p class="small fw-semibold mb-0" style="color: #2B2D42;">
                            {{ $task->created_at ? $task->created_at->format('d M Y, H:i') : '-' }}
                        </p>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-uppercase fw-semibold d-block mb-1" style="font-size: 11px; color: #8D99AE;">Terakhir Diperbarui</span>
                        <p class="small fw-semibold mb-0" style="color: #2B2D42;">
                            {{ $task->updated_at ? $task->updated_at->format('d M Y, H:i') : '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="p-3 d-flex align-items-center justify-content-between" style="background-color: #F8FAFC;">
                <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary px-3" style="border-color: #DFE5EC; color: #2B2D42; background-color: #FFFFFF;">
                    Kembali ke Tasks
                </a>
                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-primary px-3 fw-medium d-inline-flex align-items-center gap-1.5" style="background-color: #4361EE; border-color: #4361EE;">
                    <x-iconly name="edit" size="15" />
                    <span>Edit Task</span>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>