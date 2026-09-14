<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Task Manager') }} - Modern Productivity</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased d-flex flex-column min-vh-100" style="background-color: var(--color-canvas, #EDF2F4); color: var(--color-dark, #2B2D42);">
        <!-- Top Navigation -->
        <header class="w-100 border-bottom bg-white sticky-top shadow-none">
            <div class="container py-3 d-flex align-items-center justify-content-between">
                <a href="/" class="text-decoration-none d-inline-flex align-items-center gap-2.5">
                    <div class="w-9 h-9 rounded-3 bg-primary text-white d-flex align-items-center justify-center shadow-sm">
                        <x-iconly name="tick-square" class="w-5 h-5" />
                    </div>
                    <span class="fs-5 fw-bold text-dark tracking-tight">Task Manager</span>
                </a>

                @if (Route::has('login'))
                    <nav class="d-flex align-items-center gap-2">
                        @auth
                            <a
                                href="{{ url('/dashboard') }}"
                                class="btn btn-primary rounded-3 px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm"
                            >
                                <x-iconly name="category" class="w-4 h-4" />
                                <span>Buka Dashboard</span>
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="btn btn-light border bg-white text-secondary fw-semibold rounded-3 px-3.5 py-2 hover-lift"
                            >
                                Log in
                            </a>

                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="btn btn-primary rounded-3 px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm"
                                >
                                    <span>Daftar</span>
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </div>
        </header>

        <!-- Main Content -->
        <main class="my-auto py-5">
            <div class="container py-4">
                <!-- Hero Section -->
                <div class="row justify-content-center text-center mb-5">
                    <div class="col-lg-8 col-xl-7">
                        <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase tracking-wider px-3 py-1.5 rounded-pill small mb-3 d-inline-block">
                            Sistem Manajemen Tugas Modern
                        </span>
                        <h1 class="display-5 fw-bold text-dark mb-3 tracking-tight lh-sm">
                            Fokus pada hal penting, selesaikan tepat waktu.
                        </h1>
                        <p class="lead text-secondary mb-4 mx-auto" style="max-width: 580px; font-size: 1.05rem;">
                            Pengalaman produktivitas yang bersih, terstruktur, dan bertenaga. Dirancang dengan kejelasan visual untuk membantu Anda mengelola target harian tanpa distraksi.
                        </p>

                        <div class="d-flex flex-wrap align-items-center justify-content-center gap-3">
                            @auth
                                <a
                                    href="{{ route('dashboard') }}"
                                    class="btn btn-primary btn-lg rounded-3 px-4 py-2.5 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm"
                                >
                                    <x-iconly name="category" class="w-5 h-5" />
                                    <span>Masuk ke Dashboard</span>
                                </a>
                            @else
                                @if (Route::has('register'))
                                    <a
                                        href="{{ route('register') }}"
                                        class="btn btn-primary btn-lg rounded-3 px-4 py-2.5 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm"
                                    >
                                        <x-iconly name="plus" class="w-5 h-5" />
                                        <span>Mulai Sekarang</span>
                                    </a>
                                @endif
                                <a
                                    href="{{ route('login') }}"
                                    class="btn btn-light btn-lg border bg-white text-secondary fw-semibold rounded-3 px-4 py-2.5 hover-lift"
                                >
                                    Masuk Akun
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>

                <!-- Feature Pillars -->
                <div class="row g-4 mt-2">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 p-4 bg-white hover-lift">
                            <div class="w-10 h-10 rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-center mb-3">
                                <x-iconly name="flag" class="w-5 h-5" />
                            </div>
                            <h3 class="h6 fw-bold text-dark mb-1">Prioritas & Tenggat Waktu</h3>
                            <p class="text-secondary small mb-0 leading-relaxed">
                                Kelola urgensi tugas dengan tingkatan prioritas dan deadline yang terintegrasi secara cerdas.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 p-4 bg-white hover-lift">
                            <div class="w-10 h-10 rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-center mb-3">
                                <x-iconly name="folder" class="w-5 h-5" />
                            </div>
                            <h3 class="h6 fw-bold text-dark mb-1">Pengelompokan Kategori</h3>
                            <p class="text-secondary small mb-0 leading-relaxed">
                                Pisahkan pekerjaan kuliah, proyek tim, atau target personal dalam workspace kategori terisolasi.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 p-4 bg-white hover-lift">
                            <div class="w-10 h-10 rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-center mb-3">
                                <x-iconly name="notification" class="w-5 h-5" />
                            </div>
                            <h3 class="h6 fw-bold text-dark mb-1">Notification Center</h3>
                            <p class="text-secondary small mb-0 leading-relaxed">
                                Peringatan proaktif untuk tugas yang jatuh tempo hari ini atau terlewatkan agar target tetap tercapai.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-100 border-top bg-white mt-auto py-3">
            <div class="container d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2 text-secondary small">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold text-dark">Task Manager</span>
                    <span>• Modern Productivity Architecture</span>
                </div>
                <div>
                    Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
                </div>
            </div>
        </footer>
    </body>
</html>

