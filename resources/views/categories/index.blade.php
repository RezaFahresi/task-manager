<x-app-layout :title="'Manajemen Kategori - Task Manager'">
    <div class="container-fluid px-3 px-md-4 py-4 max-w-6xl mx-auto">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success border-0 bg-success-subtle text-success rounded-3 p-3 mb-4 d-flex align-items-center gap-2 shadow-sm">
                <x-iconly name="check" class="w-4 h-4 shrink-0" />
                <span class="small fw-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0 bg-danger-subtle text-danger rounded-3 p-3 mb-4 shadow-sm">
                <ul class="mb-0 ps-3 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Page Heading + Single Primary Action -->
        <div class="d-flex flex-column flex-md-row md:align-items-center justify-content-between gap-3 pb-4 mb-4 border-bottom">
            <div>
                <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase tracking-wider px-2.5 py-1 rounded-pill small mb-2 d-inline-block">Manajemen</span>
                <h1 class="h3 fw-bold text-dark mb-1 tracking-tight">Kategori</h1>
                <p class="text-secondary small mb-0">Kelola kategori untuk mengelompokkan tugas-tugas Anda.</p>
            </div>
            <div>
                <a
                    href="{{ route('categories.create') }}"
                    class="btn btn-primary fw-semibold rounded-3 px-3.5 py-2 d-inline-flex align-items-center gap-2 shadow-sm"
                >
                    <x-iconly name="plus" class="w-4 h-4" />
                    <span>Tambah Kategori</span>
                </a>
            </div>
        </div>

        <!-- Categories Table Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-secondary text-uppercase small" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                            <th class="ps-4 py-3 border-bottom text-secondary fw-semibold">Nama Kategori</th>
                            <th class="py-3 border-bottom text-secondary fw-semibold text-center" style="width: 160px;">Jumlah Task</th>
                            <th class="py-3 border-bottom text-secondary fw-semibold d-none d-sm-table-cell" style="width: 180px;">Dibuat</th>
                            <th class="pe-4 py-3 border-bottom text-secondary fw-semibold text-end" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($categories as $category)
                            <tr>
                                <td class="ps-4 py-3.5">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-center shrink-0">
                                            <x-iconly name="folder" class="w-4 h-4" />
                                        </div>
                                        <span class="fw-semibold text-dark">{{ $category->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 text-center">
                                    <a
                                        href="{{ route('tasks.index', ['category_id' => $category->id]) }}"
                                        class="badge bg-light text-secondary border text-decoration-none px-2.5 py-1.5 rounded-pill fw-medium hover-lift"
                                    >
                                        {{ $category->tasks_count }} task
                                    </a>
                                </td>
                                <td class="py-3.5 text-secondary small d-none d-sm-table-cell">
                                    {{ $category->created_at ? $category->created_at->format('d M Y') : '-' }}
                                </td>
                                <td class="pe-4 py-3.5 text-end">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <a
                                            href="{{ route('categories.edit', $category) }}"
                                            class="btn btn-sm btn-light border bg-white text-secondary p-1.5 rounded-2 hover-lift"
                                            title="Edit Kategori"
                                        >
                                            <x-iconly name="edit" class="w-3.5 h-3.5" />
                                            <span class="d-none d-md-inline ms-1 small">Edit</span>
                                        </a>
                                        <form
                                            action="{{ route('categories.destroy', $category) }}"
                                            method="POST"
                                            class="d-inline"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')"
                                                class="btn btn-sm btn-light border bg-white text-danger p-1.5 rounded-2 hover-lift"
                                                title="Hapus Kategori"
                                            >
                                                <x-iconly name="delete" class="w-3.5 h-3.5" />
                                                <span class="d-none d-md-inline ms-1 small">Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-5 text-center">
                                    <div class="py-4">
                                        <div class="w-12 h-12 rounded-circle bg-light d-flex align-items-center justify-center mx-auto mb-3 text-secondary">
                                            <x-iconly name="folder" class="w-6 h-6" />
                                        </div>
                                        <p class="text-secondary small mb-2">Belum ada kategori yang dibuat.</p>
                                        <a href="{{ route('categories.create') }}" class="btn btn-sm btn-primary rounded-3 px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm">
                                            <x-iconly name="plus" class="w-3.5 h-3.5" />
                                            <span>Tambah Kategori Pertama</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $categories->links() }}
        </div>
    </div>
</x-app-layout>
