<x-app-layout :title="'Tambah Task - Task Manager'">
    <div class="container-fluid px-3 px-md-4 py-4 max-w-4xl mx-auto">
        <!-- Back Link & Breadcrumb Header -->
        <div class="mb-4">
            <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-light border bg-white text-secondary fw-semibold d-inline-flex align-items-center gap-2 rounded-3 px-3 py-1.5 shadow-none hover-lift">
                <x-iconly name="chevron-left" class="w-4 h-4" />
                <span>Kembali</span>
            </a>
        </div>

        <div class="d-flex flex-column flex-md-row md:align-items-center justify-content-between gap-3 pb-4 mb-4 border-bottom">
            <div>
                <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase tracking-wider px-2.5 py-1 rounded-pill small mb-2 d-inline-block">Formulir</span>
                <h1 class="h3 fw-bold text-dark mb-1 tracking-tight">Tambah Task</h1>
                <p class="text-secondary small mb-0">Buat tugas baru untuk melacak target atau rencana pekerjaan Anda.</p>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="card-body p-4 p-md-5">
                <!-- Validation Errors Alert -->
                @if ($errors->any())
                    <div class="alert alert-danger rounded-3 border-0 bg-danger-subtle text-danger p-3 mb-4 d-flex align-items-start gap-2">
                        <x-iconly name="info" class="w-5 h-5 mt-0.5 shrink-0" />
                        <div>
                            <p class="fw-semibold small mb-1">Terjadi kesalahan pada input data:</p>
                            <ul class="mb-0 ps-3 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('tasks.store') }}" method="POST" class="d-flex flex-column gap-4">
                    @csrf

                    <!-- Title Input -->
                    <div>
                        <label for="title" class="form-label text-dark fw-semibold small mb-1.5">
                            Judul Task <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title') }}"
                            placeholder="Masukkan judul task..."
                            class="form-control form-control-md rounded-3 @error('title') is-invalid @enderror"
                            required
                        >
                        @error('title')
                            <div class="invalid-feedback d-block small mt-1.5 text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    @php
                        $categories = $categories ?? collect();
                    @endphp

                    <!-- Category Select -->
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <label for="category_id" class="form-label text-dark fw-semibold small mb-0">
                                Kategori <span class="text-secondary fw-normal">(Opsional)</span>
                            </label>
                            <a href="{{ route('categories.create') }}" class="text-primary small fw-semibold text-decoration-none hover-underline d-inline-flex align-items-center gap-1">
                                <x-iconly name="plus" class="w-3.5 h-3.5" />
                                <span>Buat Kategori Baru</span>
                            </a>
                        </div>
                        <select
                            id="category_id"
                            name="category_id"
                            class="form-select form-select-md rounded-3 @error('category_id') is-invalid @enderror"
                        >
                            <option value="">Tanpa Kategori</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback d-block small mt-1.5 text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description Textarea -->
                    <div>
                        <label for="description" class="form-label text-dark fw-semibold small mb-1.5">
                            Deskripsi <span class="text-secondary fw-normal">(Opsional)</span>
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            placeholder="Tambahkan detail catatan atau instruksi pengerjaan..."
                            class="form-control rounded-3 @error('description') is-invalid @enderror"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback d-block small mt-1.5 text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status, Priority & Deadline Grid -->
                    <div class="row g-3">
                        <!-- Status Select -->
                        <div class="col-12 col-md-4">
                            <label for="status" class="form-label text-dark fw-semibold small mb-1.5">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select
                                id="status"
                                name="status"
                                class="form-select form-select-md rounded-3 @error('status') is-invalid @enderror"
                                required
                            >
                                <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>
                                    Pending
                                </option>
                                <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>
                                    Completed
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback d-block small mt-1.5 text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Priority Select -->
                        <div class="col-12 col-md-4">
                            <label for="priority" class="form-label text-dark fw-semibold small mb-1.5">
                                Prioritas <span class="text-danger">*</span>
                            </label>
                            <select
                                id="priority"
                                name="priority"
                                class="form-select form-select-md rounded-3 @error('priority') is-invalid @enderror"
                                required
                            >
                                <option value="low" {{ old('priority', 'medium') === 'low' ? 'selected' : '' }}>
                                    Low
                                </option>
                                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>
                                    Medium
                                </option>
                                <option value="high" {{ old('priority', 'medium') === 'high' ? 'selected' : '' }}>
                                    High
                                </option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback d-block small mt-1.5 text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Deadline Input -->
                        <div class="col-12 col-md-4">
                            <label for="due_date" class="form-label text-dark fw-semibold small mb-1.5">
                                Deadline <span class="text-secondary fw-normal">(Opsional)</span>
                            </label>
                            <input
                                type="date"
                                id="due_date"
                                name="due_date"
                                value="{{ old('due_date') }}"
                                class="form-control form-control-md rounded-3 @error('due_date') is-invalid @enderror"
                            >
                            @error('due_date')
                                <div class="invalid-feedback d-block small mt-1.5 text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex align-items-center justify-content-end gap-2.5 pt-4 mt-2 border-top">
                        <a
                            href="{{ route('tasks.index') }}"
                            class="btn btn-light border bg-white text-secondary fw-semibold rounded-3 px-4 py-2"
                        >
                            Kembali
                        </a>
                        <button
                            type="submit"
                            class="btn btn-primary fw-semibold rounded-3 px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm"
                        >
                            <x-iconly name="tick-square" class="w-4 h-4" />
                            <span>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>