<x-app-layout :title="'Edit Kategori - Task Manager'">
    <div class="container-fluid px-3 px-md-4 py-4 max-w-2xl mx-auto">
        <!-- Back Link & Breadcrumb Header -->
        <div class="mb-4">
            <a href="{{ route('categories.index') }}" class="btn btn-sm btn-light border bg-white text-secondary fw-semibold d-inline-flex align-items-center gap-2 rounded-3 px-3 py-1.5 shadow-none hover-lift">
                <x-iconly name="chevron-left" class="w-4 h-4" />
                <span>Kembali ke Kategori</span>
            </a>
        </div>

        <div class="d-flex flex-column flex-md-row md:align-items-center justify-content-between gap-3 pb-4 mb-4 border-bottom">
            <div>
                <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase tracking-wider px-2.5 py-1 rounded-pill small mb-2 d-inline-block">Formulir</span>
                <h1 class="h3 fw-bold text-dark mb-1 tracking-tight">Edit Kategori</h1>
                <p class="text-secondary small mb-0">Ubah nama kategori pengelompokan task ini.</p>
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

                <form action="{{ route('categories.update', $category) }}" method="POST" class="d-flex flex-column gap-4">
                    @csrf
                    @method('PUT')

                    <!-- Name Input -->
                    <div>
                        <label for="name" class="form-label text-dark fw-semibold small mb-1.5">
                            Nama Kategori <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $category->name) }}"
                            placeholder="Contoh: Kuliah, Project, Belajar..."
                            class="form-control form-control-md rounded-3 @error('name') is-invalid @enderror"
                            required
                            maxlength="100"
                        >
                        @error('name')
                            <div class="invalid-feedback d-block small mt-1.5 text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex align-items-center justify-content-end gap-2.5 pt-4 mt-2 border-top">
                        <a
                            href="{{ route('categories.index') }}"
                            class="btn btn-light border bg-white text-secondary fw-semibold rounded-3 px-4 py-2"
                        >
                            Kembali
                        </a>
                        <button
                            type="submit"
                            class="btn btn-primary fw-semibold rounded-3 px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm"
                        >
                            <x-iconly name="tick-square" class="w-4 h-4" />
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
