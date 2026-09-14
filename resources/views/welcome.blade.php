<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="scroll-behavior: smooth;">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Task Manager') }} — Kelola Tugas & Deadline Secara Modern</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/task-manager-logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /* Clean SaaS Base Variables & Utilities */
            :root {
                --tm-primary: #4361EE;
                --tm-primary-hover: #3751D4;
                --tm-primary-subtle: #EEF2FF;
                --tm-dark: #0F172A;
                --tm-slate: #1E293B;
                --tm-muted: #64748B;
                --tm-border: #E2E8F0;
                --tm-surface: #FFFFFF;
                --tm-canvas: #F8FAFC;
            }

            /* Hero Background with Productivity Workspace Theme */
            .hero-pattern {
                background-color: #F8FAFC;
                background-image: url('{{ asset('images/bg-taskmanager.png') }}');
                background-repeat: no-repeat;
                background-size: cover;
                background-position: center 25%;
                position: relative;
            }

            .hero-vignette {
                position: absolute;
                inset: 0;
                background: radial-gradient(circle at 50% 30%, rgba(255, 255, 255, 0.62) 0%, rgba(248, 250, 252, 0.88) 72%, #F8FAFC 100%);
                pointer-events: none;
            }

            @media (max-width: 767.98px) {
                .hero-pattern {
                    background-position: center top;
                    background-size: cover;
                }
                .hero-vignette {
                    background: linear-gradient(180deg, rgba(255, 255, 255, 0.82) 0%, rgba(248, 250, 252, 0.92) 100%);
                }
            }

            /* Subtle Abstract Task Framing Elements */
            .hero-blueprint-box {
                position: absolute;
                border: 1px dashed rgba(67, 97, 238, 0.16);
                border-radius: 20px;
                pointer-events: none;
            }

            .hero-blueprint-1 {
                top: 8%;
                right: 3%;
                width: 380px;
                height: 380px;
                transform: rotate(4deg);
            }

            .hero-blueprint-2 {
                bottom: 5%;
                left: -2%;
                width: 260px;
                height: 260px;
                transform: rotate(-6deg);
            }

            /* Smooth Entrance Keyframes */
            @keyframes heroFadeUp {
                from {
                    opacity: 0;
                    transform: translateY(22px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes heroScaleIn {
                from {
                    opacity: 0;
                    transform: translateY(26px) scale(0.97);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            @keyframes subtleFloat1 {
                0%, 100% {
                    transform: translateY(0px);
                }
                50% {
                    transform: translateY(-6px);
                }
            }

            @keyframes subtleFloat2 {
                0%, 100% {
                    transform: translateY(0px);
                }
                50% {
                    transform: translateY(6px);
                }
            }

            @keyframes pulseSubtle {
                0%, 100% {
                    opacity: 1;
                    transform: scale(1);
                }
                50% {
                    opacity: 0.55;
                    transform: scale(1.15);
                }
            }

            /* Animation Utility Classes */
            .anim-fade-up {
                animation: heroFadeUp 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }

            .anim-fade-up-d1 {
                animation: heroFadeUp 0.65s cubic-bezier(0.16, 1, 0.3, 1) 0.12s forwards;
                opacity: 0;
            }

            .anim-fade-up-d2 {
                animation: heroFadeUp 0.65s cubic-bezier(0.16, 1, 0.3, 1) 0.22s forwards;
                opacity: 0;
            }

            .anim-fade-up-d3 {
                animation: heroFadeUp 0.65s cubic-bezier(0.16, 1, 0.3, 1) 0.32s forwards;
                opacity: 0;
            }

            .anim-fade-up-d4 {
                animation: heroFadeUp 0.65s cubic-bezier(0.16, 1, 0.3, 1) 0.42s forwards;
                opacity: 0;
            }

            .anim-preview-in {
                animation: heroScaleIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.18s forwards;
                opacity: 0;
            }

            .float-card-1 {
                animation: subtleFloat1 4.8s ease-in-out infinite;
            }

            .float-card-2 {
                animation: subtleFloat2 5.4s ease-in-out infinite;
            }

            .dot-pulse {
                animation: pulseSubtle 2s ease-in-out infinite;
            }

            /* Scroll Reveal Transitions */
            .reveal-item {
                opacity: 0;
                transform: translateY(22px);
                transition: opacity 0.55s cubic-bezier(0.16, 1, 0.3, 1), transform 0.55s cubic-bezier(0.16, 1, 0.3, 1);
                will-change: opacity, transform;
            }

            .reveal-item.is-visible {
                opacity: 1;
                transform: translateY(0);
            }

            /* Clean SaaS Micro-Interactions */
            .btn-action-primary {
                background-color: #4361EE;
                color: #FFFFFF;
                border: 1px solid #4361EE;
                box-shadow: 0 2px 8px rgba(67, 97, 238, 0.22);
                transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease, border-color 0.18s ease;
            }

            .btn-action-primary:hover {
                background-color: #3751D4;
                border-color: #3751D4;
                color: #FFFFFF;
                transform: translateY(-2px);
                box-shadow: 0 6px 18px rgba(67, 97, 238, 0.3);
            }

            .btn-action-primary:active {
                transform: translateY(0);
            }

            .btn-action-secondary {
                background-color: #FFFFFF;
                color: #0F172A;
                border: 1px solid #CBD5E1;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
                transition: transform 0.18s ease, border-color 0.18s ease, background-color 0.18s ease, color 0.18s ease;
            }

            .btn-action-secondary:hover {
                background-color: #F8FAFC;
                border-color: #94A3B8;
                color: #0F172A;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
            }

            .interactive-card {
                transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
            }

            .interactive-card:hover {
                transform: translateY(-4px);
                border-color: #CBD5E1 !important;
                box-shadow: 0 12px 28px -6px rgba(15, 23, 42, 0.07) !important;
            }

            .nav-link-custom {
                color: #64748B;
                font-weight: 500;
                font-size: 0.875rem;
                position: relative;
                padding: 0.25rem 0;
                transition: color 0.2s ease;
            }

            .nav-link-custom:hover {
                color: #4361EE;
            }

            .nav-link-custom::after {
                content: '';
                position: absolute;
                bottom: -2px;
                left: 0;
                width: 0%;
                height: 2px;
                background-color: #4361EE;
                border-radius: 2px;
                transition: width 0.2s ease;
            }

            .nav-link-custom:hover::after {
                width: 100%;
            }

            /* Workflow Step Connectors */
            .workflow-card {
                position: relative;
                background: #FFFFFF;
                border: 1px solid #E2E8F0;
                border-radius: 12px;
                padding: 1.75rem 1.5rem;
                transition: transform 0.22s ease, border-color 0.22s ease, box-shadow 0.22s ease;
                height: 100%;
            }

            .workflow-card:hover {
                transform: translateY(-4px);
                border-color: #4361EE !important;
                box-shadow: 0 10px 24px -6px rgba(67, 97, 238, 0.08);
            }

            .workflow-step-num {
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                font-size: 0.75rem;
                font-weight: 700;
                letter-spacing: 0.05em;
                color: #4361EE;
                background: #EEF2FF;
                border: 1px solid rgba(67, 97, 238, 0.2);
                border-radius: 6px;
                padding: 2px 8px;
            }

            /* Responsive step arrow divider */
            .workflow-arrow {
                display: none;
            }

            @media (min-width: 992px) {
                .workflow-col:not(:last-child) .workflow-card::after {
                    content: '→';
                    position: absolute;
                    right: -17px;
                    top: 50%;
                    transform: translateY(-50%);
                    width: 24px;
                    height: 24px;
                    background: #FFFFFF;
                    border: 1px solid #E2E8F0;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 12px;
                    color: #94A3B8;
                    z-index: 2;
                }
            }

            /* Navbar blur */
            .navbar-clean {
                background: rgba(255, 255, 255, 0.92);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border-bottom: 1px solid #E2E8F0;
            }

            /* Accessibility: Reduced Motion */
            @media (prefers-reduced-motion: reduce) {
                *, ::before, ::after {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                    scroll-behavior: auto !important;
                }
                .float-card-1, .float-card-2, .dot-pulse {
                    animation: none !important;
                }
                .reveal-item {
                    opacity: 1 !important;
                    transform: none !important;
                }
                .anim-fade-up, .anim-fade-up-d1, .anim-fade-up-d2, .anim-fade-up-d3, .anim-fade-up-d4, .anim-preview-in {
                    animation: none !important;
                    opacity: 1 !important;
                    transform: none !important;
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased d-flex flex-column min-vh-100" style="background-color: #F8FAFC; color: #0F172A;">

        <!-- 1. NAVBAR -->
        <header class="w-100 sticky-top navbar-clean z-3">
            <div class="container py-2.5 d-flex align-items-center justify-content-between">
                <!-- Logo & Brand Name -->
                <a href="/" class="text-decoration-none d-inline-flex align-items-center gap-2.5">
                    <img
                        src="{{ asset('images/task-manager-logo.png') }}"
                        alt="{{ config('app.name', 'Task Manager') }}"
                        class="rounded-2"
                        style="width: 36px; height: 36px; object-fit: contain;"
                    />
                    <div class="d-flex flex-column">
                        <span class="fs-5 fw-bold lh-1 text-dark" style="letter-spacing: -0.025em; color: #0F172A;">Task Manager</span>
                    </div>
                </a>

                <!-- Desktop Nav Links -->
                <nav class="d-none d-md-flex align-items-center gap-4">
                    <a href="#fitur" class="text-decoration-none nav-link-custom">Fitur</a>
                    <a href="#workflow" class="text-decoration-none nav-link-custom">Alur Kerja</a>
                    <a href="#notifikasi" class="text-decoration-none nav-link-custom">Notifikasi</a>
                </nav>

                <!-- Auth Buttons -->
                <div class="d-flex align-items-center gap-2">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="btn btn-action-primary btn-sm rounded-3 px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-2 text-decoration-none"
                        >
                            <x-iconly name="category" size="16" />
                            <span>Buka Dashboard</span>
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="btn btn-action-secondary btn-sm rounded-3 px-3.5 py-2 fw-semibold text-decoration-none"
                        >
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="btn btn-action-primary btn-sm rounded-3 px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 text-decoration-none"
                            >
                                <span>Mulai Sekarang</span>
                                <x-iconly name="chevron-right" size="14" />
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </header>

        <!-- MAIN CONTENT -->
        <main class="flex-grow-1">

            <!-- 2. HERO SECTION -->
            <section class="py-5 py-lg-6 hero-pattern overflow-hidden">
                <!-- Soft radial gradient overlay -->
                <div class="hero-vignette"></div>

                <!-- Abstract Subtle Task Management Shapes -->
                <div class="hero-blueprint-box hero-blueprint-1 d-none d-xl-block"></div>
                <div class="hero-blueprint-box hero-blueprint-2 d-none d-xl-block"></div>

                <div class="container py-2 py-lg-4 position-relative z-1">
                    <div class="row align-items-center g-5">
                        <!-- Left Column: Copywriting & CTA -->
                        <div class="col-lg-6 text-start">
                            <div class="anim-fade-up">
                                <span class="badge d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill mb-3" style="background-color: #EEF2FF; color: #4361EE; border: 1px solid rgba(67, 97, 238, 0.2); font-size: 0.8125rem; font-weight: 600;">
                                    <span class="rounded-circle dot-pulse" style="width: 7px; height: 7px; background-color: #4361EE;"></span>
                                    Sistem Manajemen Tugas & Deadline
                                </span>
                            </div>

                            <h1 class="anim-fade-up-d1 display-5 fw-bold mb-3 tracking-tight" style="line-height: 1.18; letter-spacing: -0.03em; color: #0F172A;">
                                Kendalikan semua tugas & deadline dalam satu alur teratur.
                            </h1>

                            <p class="anim-fade-up-d2 lead mb-4" style="color: #475569; font-size: 1.075rem; line-height: 1.62; max-width: 530px;">
                                Prioritaskan pekerjaan penting, pantau tenggat waktu tanpa rasa cemas, dan dapatkan pengingat proaktif di browser dan email agar Anda selalu selesai tepat waktu.
                            </p>

                            <!-- CTA Buttons -->
                            <div class="anim-fade-up-d3 d-flex flex-wrap align-items-center gap-3 mb-4">
                                @auth
                                    <a
                                        href="{{ route('dashboard') }}"
                                        class="btn btn-action-primary btn-lg rounded-3 px-4 py-2.5 fw-semibold d-inline-flex align-items-center gap-2 text-decoration-none"
                                    >
                                        <x-iconly name="category" size="18" />
                                        <span>Buka Dashboard Anda</span>
                                    </a>
                                @else
                                    <a
                                        href="{{ route('register') }}"
                                        class="btn btn-action-primary btn-lg rounded-3 px-4 py-2.5 fw-semibold d-inline-flex align-items-center gap-2 text-decoration-none"
                                    >
                                        <span>Mulai Sekarang — Gratis</span>
                                        <x-iconly name="chevron-right" size="16" />
                                    </a>
                                    <a
                                        href="#fitur"
                                        class="btn btn-action-secondary btn-lg rounded-3 px-4 py-2.5 fw-semibold text-decoration-none"
                                    >
                                        Pelajari Fitur
                                    </a>
                                @endauth
                            </div>

                            <!-- Trust Markers -->
                            <div class="anim-fade-up-d4 d-flex flex-wrap align-items-center gap-3 small" style="font-size: 0.8125rem; color: #64748B;">
                                <div class="d-flex align-items-center gap-1.5">
                                    <x-iconly name="check" size="14" class="text-success" />
                                    <span class="fw-medium">Workspace Terisolasi</span>
                                </div>
                                <span class="text-muted">•</span>
                                <div class="d-flex align-items-center gap-1.5">
                                    <x-iconly name="check" size="14" class="text-success" />
                                    <span class="fw-medium">Notifikasi Realtime & Email</span>
                                </div>
                                <span class="text-muted">•</span>
                                <div class="d-flex align-items-center gap-1.5">
                                    <x-iconly name="check" size="14" class="text-success" />
                                    <span class="fw-medium">Gratis Digunakan</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Living Dashboard Mockup -->
                        <div class="col-lg-6">
                            <div class="position-relative anim-preview-in">
                                <!-- Main Window Mockup -->
                                <div class="card border rounded-4 bg-white overflow-hidden shadow-sm" style="border-color: #CBD5E1 !important; box-shadow: 0 20px 45px -15px rgba(15, 23, 42, 0.12) !important;">
                                    <!-- Window Header Bar -->
                                    <div class="d-flex align-items-center justify-content-between px-3.5 py-2.5 border-bottom" style="border-color: #E2E8F0 !important; background-color: #F8FAFC !important;">
                                        <div class="d-flex align-items-center gap-1.5">
                                            <span class="rounded-circle" style="width: 10px; height: 10px; background-color: #EF233C; opacity: 0.85;"></span>
                                            <span class="rounded-circle" style="width: 10px; height: 10px; background-color: #F59E0B; opacity: 0.85;"></span>
                                            <span class="rounded-circle" style="width: 10px; height: 10px; background-color: #10B981; opacity: 0.85;"></span>
                                        </div>
                                        <div class="badge bg-white border text-secondary fw-normal px-3 py-1 rounded-2 small d-inline-flex align-items-center gap-1.5" style="border-color: #E2E8F0 !important; font-size: 0.75rem; color: #64748B;">
                                            <span class="rounded-circle" style="width: 6px; height: 6px; background-color: #10B981;"></span>
                                            app.taskmanager/dashboard
                                        </div>
                                        <div class="d-flex align-items-center gap-2 text-secondary">
                                            <div class="position-relative">
                                                <x-iconly name="notification" size="15" style="color: #64748B;" />
                                                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mockup Category Filter Tab Bar -->
                                    <div class="d-flex align-items-center gap-2 px-3.5 pt-3 pb-2 border-bottom bg-white overflow-x-auto" style="border-color: #F1F5F9 !important; font-size: 0.775rem;">
                                        <span class="badge rounded-pill px-2.5 py-1 fw-semibold" style="background-color: #4361EE; color: #FFFFFF;">Semua (10)</span>
                                        <span class="badge rounded-pill px-2.5 py-1 fw-medium text-secondary border" style="border-color: #E2E8F0 !important; background-color: #F8FAFC;">Pekerjaan (5)</span>
                                        <span class="badge rounded-pill px-2.5 py-1 fw-medium text-secondary border" style="border-color: #E2E8F0 !important; background-color: #F8FAFC;">Proyek (3)</span>
                                        <span class="badge rounded-pill px-2.5 py-1 fw-medium text-secondary border" style="border-color: #E2E8F0 !important; background-color: #F8FAFC;">Pribadi (2)</span>
                                    </div>

                                    <!-- Mockup Body -->
                                    <div class="p-3.5 p-sm-4 bg-white">
                                        <!-- Mini Metric Cards Row -->
                                        <div class="row g-2 mb-3">
                                            <div class="col-4">
                                                <div class="p-2.5 rounded-3 border bg-light" style="border-color: #E2E8F0 !important; background-color: #F8FAFC !important;">
                                                    <span class="d-block text-secondary small fw-medium text-uppercase" style="font-size: 10px; letter-spacing: 0.04em;">Pending</span>
                                                    <span class="fs-5 fw-bold" style="color: #F59E0B;">3 Task</span>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="p-2.5 rounded-3 border" style="border-color: rgba(245, 158, 11, 0.3) !important; background-color: #FFFBEB !important;">
                                                    <span class="d-block text-secondary small fw-medium text-uppercase" style="font-size: 10px; letter-spacing: 0.04em; color: #B45309 !important;">Hari Ini</span>
                                                    <span class="fs-5 fw-bold" style="color: #D97706;">1 Task</span>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="p-2.5 rounded-3 border" style="border-color: rgba(16, 185, 129, 0.25) !important; background-color: #ECFDF5 !important;">
                                                    <span class="d-block text-secondary small fw-medium text-uppercase" style="font-size: 10px; letter-spacing: 0.04em; color: #047857 !important;">Selesai</span>
                                                    <span class="fs-5 fw-bold" style="color: #10B981;">6 Task</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Mockup Tasks List -->
                                        <div class="d-flex flex-column gap-2">
                                            <!-- Task 1: High Priority Due Today -->
                                            <div class="p-2.5 rounded-3 border d-flex align-items-center justify-content-between" style="border-color: #E2E8F0 !important; background-color: #FFFFFF;">
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="rounded-circle border d-flex align-items-center justify-content-center" style="width: 20px; height: 20px; border-color: #CBD5E1 !important;">
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold small" style="font-size: 0.85rem; color: #0F172A;">Finalisasi Presentasi Klien XYZ</div>
                                                        <div class="d-flex align-items-center gap-2 mt-0.5" style="font-size: 11px; color: #64748B;">
                                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1.5 py-0.5 rounded fw-semibold" style="font-size: 10px;">Tinggi</span>
                                                            <span>•</span>
                                                            <span class="fw-semibold text-danger">Jatuh Tempo Hari Ini, 17:00</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="badge rounded-pill text-secondary border small px-2 py-1" style="font-size: 10px; border-color: #E2E8F0 !important; background-color: #F8FAFC;">Proyek</span>
                                            </div>

                                            <!-- Task 2: Medium Priority Upcoming -->
                                            <div class="p-2.5 rounded-3 border d-flex align-items-center justify-content-between" style="border-color: #E2E8F0 !important; background-color: #FFFFFF;">
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="rounded-circle border d-flex align-items-center justify-content-center" style="width: 20px; height: 20px; border-color: #CBD5E1 !important;">
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold small" style="font-size: 0.85rem; color: #0F172A;">Review Dokumen Spesifikasi Sistem</div>
                                                        <div class="d-flex align-items-center gap-2 mt-0.5" style="font-size: 11px; color: #64748B;">
                                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-1.5 py-0.5 rounded fw-semibold" style="font-size: 10px;">Sedang</span>
                                                            <span>•</span>
                                                            <span>2 Hari Lagi</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="badge rounded-pill text-secondary border small px-2 py-1" style="font-size: 10px; border-color: #E2E8F0 !important; background-color: #F8FAFC;">Kantor</span>
                                            </div>

                                            <!-- Task 3: Completed Task -->
                                            <div class="p-2.5 rounded-3 border d-flex align-items-center justify-content-between" style="border-color: #E2E8F0 !important; background-color: #F8FAFC; opacity: 0.85;">
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 20px; height: 20px; background-color: #10B981;">
                                                        <x-iconly name="check" size="13" />
                                                    </div>
                                                    <div>
                                                        <div class="text-decoration-line-through text-muted small" style="font-size: 0.85rem;">Backup Database & Sinkronisasi</div>
                                                        <div class="d-flex align-items-center gap-2 mt-0.5" style="font-size: 11px; color: #10B981;">
                                                            <span>Selesai Tepat Waktu</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="badge rounded-pill text-secondary border small px-2 py-1" style="font-size: 10px; border-color: #E2E8F0 !important; background-color: #F8FAFC;">Sistem</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Floating Subtle Micro-Card 1 (Top-Right): Realtime Notification -->
                                <div class="float-card-1 position-absolute d-none d-md-flex align-items-center gap-2.5 p-2.5 rounded-3 bg-white border shadow-sm" style="top: -16px; right: -16px; border-color: #E2E8F0 !important; max-width: 280px; z-index: 2;">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; background-color: #FFFBEB; color: #D97706; border: 1px solid rgba(245, 158, 11, 0.3);">
                                        <x-iconly name="notification" size="16" />
                                    </div>
                                    <div style="line-height: 1.25;">
                                        <div class="fw-bold small" style="font-size: 11px; color: #0F172A;">Pengingat Realtime</div>
                                        <div class="text-secondary" style="font-size: 11px; color: #64748B;">Task "Finalisasi Presentasi" jatuh tempo hari ini!</div>
                                    </div>
                                </div>

                                <!-- Floating Subtle Micro-Card 2 (Bottom-Left): Productivity Metric -->
                                <div class="float-card-2 position-absolute d-none d-md-flex align-items-center gap-2.5 p-2.5 rounded-3 bg-white border shadow-sm" style="bottom: -18px; left: -16px; border-color: #E2E8F0 !important; z-index: 2;">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; background-color: #ECFDF5; color: #10B981; border: 1px solid rgba(16, 185, 129, 0.3);">
                                        <x-iconly name="check" size="16" />
                                    </div>
                                    <div style="line-height: 1.25; min-width: 170px;">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="fw-bold" style="font-size: 11px; color: #0F172A;">Target Selesai</span>
                                            <span class="fw-bold text-success" style="font-size: 11px;">75%</span>
                                        </div>
                                        <div class="progress" style="height: 4px; background-color: #E2E8F0;">
                                            <div class="progress-bar bg-success rounded" style="width: 75%;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 3. FEATURE SECTION (Bento-Inspired Visual Hierarchy) -->
            <section id="fitur" class="py-5 py-lg-6 bg-white border-top border-bottom" style="border-color: #E2E8F0 !important;">
                <div class="container py-2 py-lg-4">
                    <!-- Section Header -->
                    <div class="text-center mb-5 reveal-item">
                        <span class="badge px-3 py-1.5 rounded-pill mb-2 fw-semibold" style="background-color: #EEF2FF; color: #4361EE; font-size: 0.8125rem;">
                            Fitur Lengkap
                        </span>
                        <h2 class="display-6 fw-bold tracking-tight mb-2" style="letter-spacing: -0.025em; color: #0F172A;">
                            Dibangun untuk Kejelasan & Produktivitas Nyata
                        </h2>
                        <p class="text-secondary mx-auto" style="max-width: 580px; font-size: 1.05rem; color: #64748B;">
                            Semua fitur yang Anda butuhkan untuk mengelola pekerjaan tanpa kompleksitas yang membebani.
                        </p>
                    </div>

                    <!-- Bento-Structured Feature Grid -->
                    <div class="row g-4">
                        <!-- Feature 1 (Hero/Wide Card): Task Management Terpadu -->
                        <div class="col-lg-8 reveal-item">
                            <div class="card h-100 p-4 rounded-3 border bg-white interactive-card position-relative overflow-hidden" style="border-color: #E2E8F0 !important;">
                                <div class="d-flex flex-column flex-md-row align-items-start justify-content-between gap-3 mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background-color: #EEF2FF; color: #4361EE;">
                                            <x-iconly name="task" size="22" />
                                        </div>
                                        <div>
                                            <h3 class="h5 fw-bold mb-1" style="color: #0F172A; letter-spacing: -0.015em;">Task Management Terpadu</h3>
                                            <span class="badge text-uppercase fw-semibold" style="background-color: #F1F5F9; color: #475569; font-size: 10px; letter-spacing: 0.04em;">Pencarian & Status Realtime</span>
                                        </div>
                                    </div>
                                    <!-- Mini interactive UI widget preview -->
                                    <div class="d-none d-sm-flex align-items-center gap-1.5 p-1.5 rounded-2 border bg-light small" style="border-color: #E2E8F0 !important; font-size: 0.775rem;">
                                        <x-iconly name="search" size="14" class="text-muted" />
                                        <span class="text-secondary">Cari tugas cepat...</span>
                                    </div>
                                </div>
                                <p class="text-secondary mb-3" style="color: #64748B; line-height: 1.6; font-size: 0.95rem;">
                                    Buat, sunting, cari, dan kelola status tugas Anda (pending atau completed) dalam tabel terstruktur dengan navigasi instan dan filter fleksibel.
                                </p>
                                <!-- Visual Micro-Snippet -->
                                <div class="p-2.5 rounded-3 border bg-light d-flex flex-wrap align-items-center gap-2 mt-auto" style="border-color: #E2E8F0 !important; background-color: #F8FAFC !important; font-size: 0.8125rem;">
                                    <span class="fw-semibold text-secondary">Aksi Cepat:</span>
                                    <span class="badge bg-white border text-dark fw-normal px-2.5 py-1">Tandai Selesai</span>
                                    <span class="badge bg-white border text-dark fw-normal px-2.5 py-1">Ganti Status</span>
                                    <span class="badge bg-white border text-dark fw-normal px-2.5 py-1">Ubah Deadline</span>
                                </div>
                            </div>
                        </div>

                        <!-- Feature 2: Pelacakan Deadline Otomatis -->
                        <div class="col-lg-4 reveal-item">
                            <div class="card h-100 p-4 rounded-3 border bg-white interactive-card" style="border-color: #E2E8F0 !important;">
                                <div class="rounded-3 d-flex align-items-center justify-content-center mb-3" style="width: 44px; height: 44px; background-color: #FFFBEB; color: #D97706;">
                                    <x-iconly name="calendar" size="22" />
                                </div>
                                <h3 class="h6 fw-bold mb-2" style="font-size: 1.1rem; color: #0F172A;">Pelacakan Deadline Otomatis</h3>
                                <p class="text-secondary small mb-3" style="color: #64748B; line-height: 1.6;">
                                    Sistem memfilter dan mengelompokkan deadline secara otomatis agar Anda selalu siap menghadapi tanggal jatuh tempo.
                                </p>
                                <!-- Visual status tags -->
                                <div class="d-flex flex-wrap gap-1.5 mt-auto pt-2">
                                    <span class="badge" style="background-color: #FFFBEB; color: #B45309; border: 1px solid rgba(245, 158, 11, 0.3);">Hari Ini</span>
                                    <span class="badge" style="background-color: #EEF2FF; color: #4361EE; border: 1px solid rgba(67, 97, 238, 0.25);">Mendatang</span>
                                    <span class="badge" style="background-color: #FEF2F2; color: #EF233C; border: 1px solid rgba(239, 35, 60, 0.25);">Terlambat</span>
                                </div>
                            </div>
                        </div>

                        <!-- Feature 3: Tingkat Prioritas -->
                        <div class="col-md-6 col-lg-4 reveal-item">
                            <div class="card h-100 p-4 rounded-3 border bg-white interactive-card" style="border-color: #E2E8F0 !important;">
                                <div class="rounded-3 d-flex align-items-center justify-content-center mb-3" style="width: 44px; height: 44px; background-color: #FEF2F2; color: #EF233C;">
                                    <x-iconly name="flag" size="22" />
                                </div>
                                <h3 class="h6 fw-bold mb-2" style="font-size: 1.1rem; color: #0F172A;">Tingkat Prioritas (High, Med, Low)</h3>
                                <p class="text-secondary small mb-3" style="color: #64748B; line-height: 1.6;">
                                    Tentukan urgensi pekerjaan dengan visualisasi badge tegas untuk memandu fokus harian pada hal yang paling berdampak.
                                </p>
                                <!-- Visual priority indicators -->
                                <div class="d-flex align-items-center gap-1.5 mt-auto pt-2">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 11px;">Tinggi</span>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" style="font-size: 11px;">Sedang</span>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 11px;">Rendah</span>
                                </div>
                            </div>
                        </div>

                        <!-- Feature 4: Kategori Fleksibel -->
                        <div class="col-md-6 col-lg-4 reveal-item">
                            <div class="card h-100 p-4 rounded-3 border bg-white interactive-card" style="border-color: #E2E8F0 !important;">
                                <div class="rounded-3 d-flex align-items-center justify-content-center mb-3" style="width: 44px; height: 44px; background-color: #F3E8FF; color: #9333EA;">
                                    <x-iconly name="folder" size="22" />
                                </div>
                                <h3 class="h6 fw-bold mb-2" style="font-size: 1.1rem; color: #0F172A;">Pengelompokan Kategori Fleksibel</h3>
                                <p class="text-secondary small mb-3" style="color: #64748B; line-height: 1.6;">
                                    Organisir tugas berdasarkan proyek seperti Pekerjaan, Kuliah, Bisnis, atau Pribadi dengan hitungan tugas otomatis.
                                </p>
                                <!-- Visual category pills -->
                                <div class="d-flex flex-wrap gap-1.5 mt-auto pt-2">
                                    <span class="badge rounded-pill bg-light text-secondary border px-2.5 py-1" style="border-color: #E2E8F0 !important;">Pekerjaan (5)</span>
                                    <span class="badge rounded-pill bg-light text-secondary border px-2.5 py-1" style="border-color: #E2E8F0 !important;">Proyek (3)</span>
                                    <span class="badge rounded-pill bg-light text-secondary border px-2.5 py-1" style="border-color: #E2E8F0 !important;">Pribadi (2)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Feature 5: Realtime & Email Notification -->
                        <div class="col-lg-4 reveal-item" id="notifikasi">
                            <div class="card h-100 p-4 rounded-3 border bg-white interactive-card" style="border-color: #E2E8F0 !important;">
                                <div class="rounded-3 d-flex align-items-center justify-content-center mb-3" style="width: 44px; height: 44px; background-color: #ECFDF5; color: #10B981;">
                                    <x-iconly name="notification" size="22" />
                                </div>
                                <h3 class="h6 fw-bold mb-2" style="font-size: 1.1rem; color: #0F172A;">Notifikasi Realtime & Email Otomatis</h3>
                                <p class="text-secondary small mb-3" style="color: #64748B; line-height: 1.6;">
                                    Peringatan instan di Notification Center tanpa reload halaman, serta pengingat via email otomatis sebelum tenggat tiba.
                                </p>
                                <!-- Visual notification badge preview -->
                                <div class="d-flex align-items-center gap-2 p-2 rounded-2 border bg-light mt-auto" style="border-color: #E2E8F0 !important; font-size: 0.775rem;">
                                    <span class="rounded-circle bg-success" style="width: 6px; height: 6px;"></span>
                                    <span class="text-secondary">Pusat notifikasi selalu sinkron</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 4. WORKFLOW SECTION (Visual Alur 01 → 02 → 03 → 04) -->
            <section id="workflow" class="py-5 py-lg-6" style="background-color: #F8FAFC;">
                <div class="container py-2 py-lg-4">
                    <!-- Section Header -->
                    <div class="text-center mb-5 reveal-item">
                        <span class="badge px-3 py-1.5 rounded-pill mb-2 fw-semibold" style="background-color: #EEF2FF; color: #4361EE; font-size: 0.8125rem;">
                            Alur Kerja Sederhana
                        </span>
                        <h2 class="display-6 fw-bold tracking-tight mb-2" style="letter-spacing: -0.025em; color: #0F172A;">
                            Dari Rencana Hingga Tuntas dalam 4 Langkah
                        </h2>
                        <p class="text-secondary mx-auto" style="max-width: 540px; font-size: 1.05rem; color: #64748B;">
                            Dirancang agar Anda dapat langsung produktif tanpa perlu mempelajari antarmuka yang rumit.
                        </p>
                    </div>

                    <!-- 4 Steps Row with Visual Connectors -->
                    <div class="row g-4 position-relative">
                        <!-- Step 01 -->
                        <div class="col-12 col-sm-6 col-lg-3 workflow-col reveal-item">
                            <div class="workflow-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: #EEF2FF; color: #4361EE;">
                                        <x-iconly name="plus" size="18" />
                                    </div>
                                    <span class="workflow-step-num">01</span>
                                </div>
                                <h4 class="h6 fw-bold mb-2" style="color: #0F172A; font-size: 1.025rem;">Buat Task</h4>
                                <p class="text-secondary small mb-0" style="color: #64748B; line-height: 1.6;">
                                    Catat tugas atau ide pekerjaan baru lengkap dengan rincian deskripsi yang dibutuhkan.
                                </p>
                            </div>
                        </div>

                        <!-- Step 02 -->
                        <div class="col-12 col-sm-6 col-lg-3 workflow-col reveal-item">
                            <div class="workflow-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: #FFFBEB; color: #D97706;">
                                        <x-iconly name="calendar" size="18" />
                                    </div>
                                    <span class="workflow-step-num">02</span>
                                </div>
                                <h4 class="h6 fw-bold mb-2" style="color: #0F172A; font-size: 1.025rem;">Atur Deadline & Kategori</h4>
                                <p class="text-secondary small mb-0" style="color: #64748B; line-height: 1.6;">
                                    Pilih tanggal jatuh tempo, tetapkan kategori relevan, dan tentukan prioritas pengerjaan.
                                </p>
                            </div>
                        </div>

                        <!-- Step 03 -->
                        <div class="col-12 col-sm-6 col-lg-3 workflow-col reveal-item">
                            <div class="workflow-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: #FEF2F2; color: #EF233C;">
                                        <x-iconly name="notification" size="18" />
                                    </div>
                                    <span class="workflow-step-num">03</span>
                                </div>
                                <h4 class="h6 fw-bold mb-2" style="color: #0F172A; font-size: 1.025rem;">Dapatkan Pengingat</h4>
                                <p class="text-secondary small mb-0" style="color: #64748B; line-height: 1.6;">
                                    Sistem secara otomatis mengingatkan Anda saat deadline tiba baik di dashboard maupun email.
                                </p>
                            </div>
                        </div>

                        <!-- Step 04 -->
                        <div class="col-12 col-sm-6 col-lg-3 workflow-col reveal-item">
                            <div class="workflow-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: #ECFDF5; color: #10B981;">
                                        <x-iconly name="check" size="18" />
                                    </div>
                                    <span class="workflow-step-num">04</span>
                                </div>
                                <h4 class="h6 fw-bold mb-2" style="color: #0F172A; font-size: 1.025rem;">Selesaikan & Pantau</h4>
                                <p class="text-secondary small mb-0" style="color: #64748B; line-height: 1.6;">
                                    Tandai tugas selesai dengan satu klik dan pantau tingkat produktivitas di ringkasan metrik.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 5. FINAL CTA SECTION -->
            <section class="py-5 py-lg-6 bg-white border-top" style="border-color: #E2E8F0 !important;">
                <div class="container py-2 py-lg-3">
                    <div class="card border rounded-4 p-4 p-md-5 text-center position-relative overflow-hidden" style="border-color: #CBD5E1 !important; background: linear-gradient(180deg, rgba(255, 255, 255, 0.93) 0%, rgba(248, 250, 252, 0.92) 100%), url('{{ asset('images/bg-taskmanager.png') }}') center/cover no-repeat; box-shadow: 0 12px 32px -8px rgba(67, 97, 238, 0.09);">
                        <div class="position-relative z-1 mx-auto" style="max-width: 600px;">
                            <span class="badge px-3 py-1.5 rounded-pill mb-3 fw-semibold" style="background-color: #EEF2FF; color: #4361EE; font-size: 0.8125rem;">
                                Mulai Hari Ini
                            </span>
                            <h2 class="display-6 fw-bold tracking-tight mb-3" style="letter-spacing: -0.025em; color: #0F172A;">
                                Siap Menyelesaikan Tugas Lebih Terstruktur?
                            </h2>
                            <p class="text-secondary mb-4" style="color: #64748B; font-size: 1.05rem; line-height: 1.62;">
                                Bergabunglah sekarang dan rasakan kenyamanan mengelola tugas harian, kategori, dan tenggat waktu dalam satu aplikasi yang bersih, rapi, dan cepat.
                            </p>

                            <div class="d-flex flex-wrap align-items-center justify-content-center gap-3">
                                @auth
                                    <a
                                        href="{{ route('dashboard') }}"
                                        class="btn btn-action-primary btn-lg rounded-3 px-4 py-2.5 fw-semibold d-inline-flex align-items-center gap-2 text-decoration-none"
                                    >
                                        <x-iconly name="category" size="18" />
                                        <span>Masuk ke Dashboard</span>
                                    </a>
                                @else
                                    <a
                                        href="{{ route('register') }}"
                                        class="btn btn-action-primary btn-lg rounded-3 px-4 py-2.5 fw-semibold d-inline-flex align-items-center gap-2 text-decoration-none"
                                    >
                                        <span>Mulai Sekarang — Gratis</span>
                                        <x-iconly name="chevron-right" size="16" />
                                    </a>
                                    <a
                                        href="{{ route('login') }}"
                                        class="btn btn-action-secondary btn-lg rounded-3 px-4 py-2.5 fw-semibold text-decoration-none"
                                    >
                                        Masuk ke Akun
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </main>

        <!-- 6. MODERN & MINIMAL FOOTER -->
        <footer class="w-100 border-top bg-white py-4" style="border-color: #E2E8F0 !important;">
            <div class="container">
                <div class="row align-items-center g-3">
                    <!-- Brand Column -->
                    <div class="col-12 col-md-4 text-center text-md-start">
                        <div class="d-inline-flex align-items-center gap-2">
                            <img
                                src="{{ asset('images/task-manager-logo.png') }}"
                                alt="{{ config('app.name', 'Task Manager') }}"
                                class="rounded-2"
                                style="width: 28px; height: 28px; object-fit: contain;"
                            />
                            <span class="fw-bold text-dark fs-6" style="letter-spacing: -0.015em; color: #0F172A;">Task Manager</span>
                        </div>
                        <p class="text-secondary small mt-1 mb-0" style="color: #64748B; font-size: 0.8125rem;">
                            Kelola tugas dan tenggat waktu secara efisien tanpa distraksi.
                        </p>
                    </div>

                    <!-- Navigation Links -->
                    <div class="col-12 col-md-4 text-center">
                        <div class="d-inline-flex flex-wrap align-items-center justify-content-center gap-3 small">
                            <a href="#" class="text-decoration-none text-secondary" style="color: #64748B;" onmouseover="this.style.color='#4361EE'" onmouseout="this.style.color='#64748B'">Beranda</a>
                            <span class="text-muted">•</span>
                            <a href="#fitur" class="text-decoration-none text-secondary" style="color: #64748B;" onmouseover="this.style.color='#4361EE'" onmouseout="this.style.color='#64748B'">Fitur</a>
                            <span class="text-muted">•</span>
                            <a href="#workflow" class="text-decoration-none text-secondary" style="color: #64748B;" onmouseover="this.style.color='#4361EE'" onmouseout="this.style.color='#64748B'">Workflow</a>
                            <span class="text-muted">•</span>
                            <a href="#notifikasi" class="text-decoration-none text-secondary" style="color: #64748B;" onmouseover="this.style.color='#4361EE'" onmouseout="this.style.color='#64748B'">Notifikasi</a>
                        </div>
                    </div>

                    <!-- Legal & Contact Links -->
                    <div class="col-12 col-md-4 text-center text-md-end">
                        <div class="d-inline-flex flex-wrap align-items-center justify-content-center justify-content-md-end gap-3 small">
                            <button type="button" class="btn btn-link p-0 text-decoration-none small" style="color: #64748B; font-size: 0.8125rem;" data-bs-toggle="modal" data-bs-target="#privacyModal" onmouseover="this.style.color='#4361EE'" onmouseout="this.style.color='#64748B'">
                                Privacy Policy
                            </button>
                            <span class="text-muted">•</span>
                            <button type="button" class="btn btn-link p-0 text-decoration-none small" style="color: #64748B; font-size: 0.8125rem;" data-bs-toggle="modal" data-bs-target="#termsModal" onmouseover="this.style.color='#4361EE'" onmouseout="this.style.color='#64748B'">
                                Terms of Service
                            </button>
                            <span class="text-muted">•</span>
                            <button type="button" class="btn btn-link p-0 text-decoration-none small" style="color: #64748B; font-size: 0.8125rem;" data-bs-toggle="modal" data-bs-target="#contactModal" onmouseover="this.style.color='#4361EE'" onmouseout="this.style.color='#64748B'">
                                Contact
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Copyright Bar -->
                <div class="border-top mt-3 pt-3 text-center text-secondary small" style="border-color: #F1F5F9 !important; font-size: 0.775rem; color: #94A3B8;">
                    &copy; 2026 Task Manager. Seluruh hak cipta dilindungi.
                </div>
            </div>
        </footer>

        <!-- MODALS (Privacy Policy, Terms of Service, Contact) -->
        <!-- Privacy Policy Modal -->
        <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom px-4 py-3" style="border-color: #E2E8F0 !important;">
                        <h5 class="modal-title fw-bold" id="privacyModalLabel" style="color: #0F172A; font-size: 1.1rem;">Kebijakan Privasi (Privacy Policy)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 py-3 text-secondary" style="font-size: 0.9rem; line-height: 1.6; color: #475569;">
                        <h6 class="fw-bold text-dark mb-1">1. Pengumpulan Data</h6>
                        <p class="mb-3">Task Manager menghormati privasi Anda. Kami hanya mengumpulkan informasi yang diperlukan seperti nama, alamat email, serta data tugas dan kategori yang Anda buat untuk kebutuhan operasional aplikasi.</p>

                        <h6 class="fw-bold text-dark mb-1">2. Keamanan & Isolasi Data</h6>
                        <p class="mb-3">Setiap data tugas tersimpan secara aman dalam lingkungan workspace akun Anda sendiri dan tidak dibagikan kepada pihak ketiga untuk keperluan komersial atau periklanan.</p>

                        <h6 class="fw-bold text-dark mb-1">3. Notifikasi Email</h6>
                        <p class="mb-0">Alamat email Anda hanya digunakan untuk autentikasi keamanan dan pengiriman pengingat deadline tugas sesuai preferensi yang Anda tentukan.</p>
                    </div>
                    <div class="modal-footer border-top px-4 py-2.5" style="border-color: #E2E8F0 !important;">
                        <button type="button" class="btn btn-action-secondary btn-sm rounded-3 px-3 py-1.5" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terms of Service Modal -->
        <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom px-4 py-3" style="border-color: #E2E8F0 !important;">
                        <h5 class="modal-title fw-bold" id="termsModalLabel" style="color: #0F172A; font-size: 1.1rem;">Ketentuan Layanan (Terms of Service)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 py-3 text-secondary" style="font-size: 0.9rem; line-height: 1.6; color: #475569;">
                        <h6 class="fw-bold text-dark mb-1">1. Penggunaan Layanan</h6>
                        <p class="mb-3">Dengan mendaftar dan menggunakan Task Manager, Anda menyetujui untuk menggunakan layanan ini demi tujuan yang sah dalam pencatatan dan pengelolaan tugas personal maupun profesional.</p>

                        <h6 class="fw-bold text-dark mb-1">2. Tanggung Jawab Akun</h6>
                        <p class="mb-3">Anda bertanggung jawab penuh atas kerahasiaan kata sandi dan seluruh aktivitas yang terjadi di bawah akun Anda.</p>

                        <h6 class="fw-bold text-dark mb-1">3. Ketersediaan Layanan</h6>
                        <p class="mb-0">Kami terus berupaya menjaga keandalan dan ketersediaan layanan 24/7 demi memastikan kelancaran produktivitas Anda.</p>
                    </div>
                    <div class="modal-footer border-top px-4 py-2.5" style="border-color: #E2E8F0 !important;">
                        <button type="button" class="btn btn-action-secondary btn-sm rounded-3 px-3 py-1.5" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Modal -->
        <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom px-4 py-3" style="border-color: #E2E8F0 !important;">
                        <h5 class="modal-title fw-bold" id="contactModalLabel" style="color: #0F172A; font-size: 1.1rem;">Hubungi Kami (Contact)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 py-3 text-secondary" style="font-size: 0.9rem; line-height: 1.6; color: #475569;">
                        <p class="mb-3">Apakah Anda memiliki pertanyaan, masukan fitur, atau memerlukan bantuan teknis terkait Task Manager? Tim kami siap membantu Anda.</p>
                        <div class="p-3 rounded-3 border bg-light mb-3" style="border-color: #E2E8F0 !important;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="fw-semibold text-dark">Email Dukungan:</span>
                                <a href="mailto:support@taskmanager.app" class="text-decoration-none fw-medium" style="color: #4361EE;">support@taskmanager.app</a>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-semibold text-dark">Jam Operasional:</span>
                                <span class="text-secondary">Senin – Jumat, 09:00 – 17:00 WIB</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-2.5" style="border-color: #E2E8F0 !important;">
                        <button type="button" class="btn btn-action-primary btn-sm rounded-3 px-3.5 py-1.5" data-bs-dismiss="modal">Mengerti</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Reveal Script (Vanilla JS, Zero Dependency) -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const revealElements = document.querySelectorAll('.reveal-item');

                if ('IntersectionObserver' in window) {
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('is-visible');
                                observer.unobserve(entry.target);
                            }
                        });
                    }, {
                        threshold: 0.12,
                        rootMargin: '0px 0px -40px 0px'
                    });

                    revealElements.forEach(el => observer.observe(el));
                } else {
                    // Fallback for older browsers
                    revealElements.forEach(el => el.classList.add('is-visible'));
                }
            });
        </script>
    </body>
</html>
