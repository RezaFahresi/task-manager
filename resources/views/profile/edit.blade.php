<x-app-layout :title="'Profil - Task Manager'">
    <div class="container-fluid px-3 px-md-4 py-4 max-w-4xl mx-auto">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row md:align-items-center justify-content-between gap-3 pb-4 mb-4 border-bottom">
            <div>
                <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase tracking-wider px-2.5 py-1 rounded-pill small mb-2 d-inline-block">Pengaturan</span>
                <h1 class="h3 fw-bold text-dark mb-1 tracking-tight">Profil Pengguna</h1>
                <p class="text-secondary small mb-0">Kelola informasi akun dan kredensial keamanan Anda.</p>
            </div>
        </div>

        <!-- Information Form Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="card-body p-4 p-md-5">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        <!-- Password Form Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="card-body p-4 p-md-5">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <!-- Delete Account Form Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="card-body p-4 p-md-5">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
