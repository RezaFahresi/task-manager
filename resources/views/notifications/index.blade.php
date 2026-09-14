<x-app-layout :title="'Notification Center - Task Manager'">
    <div class="container-fluid px-3 px-md-4 py-4 max-w-6xl mx-auto">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row md:align-items-center justify-content-between gap-3 pb-4 mb-4 border-bottom">
            <div>
                <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase tracking-wider px-2.5 py-1 rounded-pill small mb-2 d-inline-block">Pemberitahuan</span>
                <h1 class="h3 fw-bold text-dark mb-1 tracking-tight">Notification Center</h1>
                <p class="text-secondary small mb-0">
                    Kelola notifikasi tenggat waktu dan status task Anda.
                    @if ($unreadCount > 0)
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5 ms-1 font-monospace">
                            {{ $unreadCount }} belum dibaca
                        </span>
                    @endif
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-light border bg-white text-secondary fw-semibold rounded-3 px-3 py-1.5 d-inline-flex align-items-center gap-2 hover-lift shadow-none"
                        >
                            <x-iconly name="tick-square" class="w-4 h-4 text-primary" />
                            <span class="small">Tandai Semua Dibaca</span>
                        </button>
                    </form>
                @endif
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary rounded-3 px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-2 hover-lift">
                    <x-iconly name="calendar" class="w-4 h-4" />
                    <span class="small">Lihat Tasks</span>
                </a>
            </div>
        </div>

        <!-- Flash Success Notification -->
        @if (session('success'))
            <div class="alert alert-success border-0 bg-success-subtle text-success rounded-3 p-3 mb-4 d-flex align-items-center gap-2 shadow-sm">
                <x-iconly name="check" class="w-4 h-4 shrink-0" />
                <span class="small fw-medium">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filter Bar -->
        <div class="d-flex align-items-center gap-2 py-2 mb-4">
            <span class="text-secondary small fw-semibold me-2">Filter:</span>
            <div class="btn-group rounded-3 shadow-none p-1 bg-white border" role="group">
                <a
                    href="{{ route('notifications.index', ['filter' => 'all']) }}"
                    class="btn btn-sm rounded-2 px-3 {{ ($filter ?? 'all') === 'all' ? 'btn-primary shadow-sm' : 'btn-light border-0 bg-transparent text-secondary' }} fw-medium"
                >
                    Semua
                </a>
                <a
                    href="{{ route('notifications.index', ['filter' => 'unread']) }}"
                    class="btn btn-sm rounded-2 px-3 d-inline-flex align-items-center gap-2 {{ ($filter ?? '') === 'unread' ? 'btn-primary shadow-sm' : 'btn-light border-0 bg-transparent text-secondary' }} fw-medium"
                >
                    <span>Belum Dibaca</span>
                    @if ($unreadCount > 0)
                        <span class="badge rounded-pill {{ ($filter ?? '') === 'unread' ? 'bg-white text-primary' : 'bg-primary-subtle text-primary' }} px-1.5 py-0.5" style="font-size: 0.68rem;">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </a>
            </div>
        </div>

        <!-- Notification List -->
        <div>
            @if ($notifications->isNotEmpty())
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                    <div class="list-group list-group-flush">
                        @foreach ($notifications as $notification)
                            @php
                                $isUnread = $notification->unread();
                                $data = $notification->data;
                                $type = $data['type'] ?? 'info';
                                $taskId = $data['task_id'] ?? null;
                                $taskUrl = $data['url'] ?? ($taskId ? route('tasks.show', $taskId) : null);
                            @endphp

                            <div class="list-group-item p-3 p-md-4 border-bottom d-flex flex-column flex-md-row md:align-items-center justify-content-between gap-3 {{ $isUnread ? 'bg-primary-subtle bg-opacity-25' : '' }}">
                                <div class="d-flex align-items-start gap-3 min-w-0">
                                    <!-- Indicator Dot -->
                                    <div class="mt-1 shrink-0">
                                        @if ($isUnread)
                                            <span class="d-inline-block rounded-circle bg-primary" style="width: 10px; height: 10px;" title="Belum dibaca"></span>
                                        @else
                                            <span class="d-inline-block rounded-circle bg-secondary bg-opacity-25" style="width: 10px; height: 10px;" title="Sudah dibaca"></span>
                                        @endif
                                    </div>

                                    <!-- Notification Content -->
                                    <div class="min-w-0">
                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                            @if ($type === 'overdue')
                                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-0.5 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                                    <span class="rounded-circle bg-danger d-inline-block" style="width: 5px; height: 5px;"></span>
                                                    <span>Overdue</span>
                                                </span>
                                            @elseif ($type === 'due_today')
                                                <span class="badge bg-warning-subtle text-warning rounded-pill px-2 py-0.5 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                                    <span class="rounded-circle bg-warning d-inline-block" style="width: 5px; height: 5px;"></span>
                                                    <span>Hari Ini</span>
                                                </span>
                                            @elseif ($type === 'due_soon')
                                                <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                                    <span class="rounded-circle bg-primary d-inline-block" style="width: 5px; height: 5px;"></span>
                                                    <span>Mendatang</span>
                                                </span>
                                            @endif

                                            <h4 class="h6 fw-bold text-dark mb-0 text-truncate">
                                                {{ $data['title'] ?? 'Pemberitahuan Task' }}
                                            </h4>
                                        </div>

                                        <p class="text-secondary small mb-1.5 leading-relaxed">
                                            {{ $data['message'] ?? 'Anda memiliki pembaruan pada task.' }}
                                        </p>

                                        <div class="d-flex align-items-center gap-3 text-secondary" style="font-size: 0.75rem;">
                                            <span class="d-inline-flex align-items-center gap-1">
                                                <x-iconly name="clock" class="w-3.5 h-3.5" />
                                                <span>{{ $notification->created_at->diffForHumans() }}</span>
                                            </span>
                                            @if (!empty($data['due_date']))
                                                <span>•</span>
                                                <span class="d-inline-flex align-items-center gap-1">
                                                    <x-iconly name="calendar" class="w-3.5 h-3.5" />
                                                    <span>Deadline: {{ \Carbon\Carbon::parse($data['due_date'])->format('d M Y') }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="d-flex align-items-center gap-2 shrink-0 justify-content-end">
                                    @if ($taskUrl)
                                        <a
                                            href="{{ $taskUrl }}"
                                            class="btn btn-sm btn-light border bg-white text-primary fw-semibold rounded-3 px-3 py-1.5 d-inline-flex align-items-center gap-1 hover-lift"
                                        >
                                            <span class="small">Buka Task</span>
                                            <x-iconly name="chevron-right" class="w-3.5 h-3.5" />
                                        </a>
                                    @endif

                                    @if ($isUnread)
                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-light border bg-white text-secondary fw-semibold rounded-3 px-3 py-1.5 hover-lift"
                                            >
                                                <span class="small">Tandai dibaca</span>
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" onsubmit="return confirm('Hapus notifikasi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-light border bg-white text-danger p-1.5 rounded-3 hover-lift"
                                            title="Hapus Notifikasi"
                                        >
                                            <x-iconly name="delete" class="w-3.5 h-3.5" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center">
                    <div class="py-4">
                        <div class="w-12 h-12 rounded-circle bg-light d-flex align-items-center justify-center mx-auto mb-3 text-secondary">
                            <x-iconly name="notification" class="w-6 h-6" />
                        </div>
                        <h4 class="h5 fw-bold text-dark mb-1">Belum Ada Notifikasi</h4>
                        <p class="text-secondary small mb-3">
                            @if (($filter ?? 'all') === 'unread')
                                Semua notifikasi telah dibaca.
                            @else
                                Anda tidak memiliki notifikasi saat ini.
                            @endif
                        </p>
                        <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-primary rounded-3 px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm">
                            <x-iconly name="calendar" class="w-4 h-4" />
                            <span>Lihat Semua Tasks</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
