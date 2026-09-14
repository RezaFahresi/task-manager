<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Task Manager') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/task-manager-logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            @keyframes authFadeIn {
                from {
                    opacity: 0;
                    transform: translateY(14px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes authFloat1 {
                0%, 100% {
                    transform: translateY(0);
                }
                50% {
                    transform: translateY(-5px);
                }
            }

            @keyframes authFloat2 {
                0%, 100% {
                    transform: translateY(0);
                }
                50% {
                    transform: translateY(5px);
                }
            }

            .auth-layout-wrapper {
                min-height: 100vh;
                background-color: #F8FAFC;
                background-image: url('{{ asset('images/bg-taskmanager.png') }}');
                background-repeat: no-repeat;
                background-size: cover;
                background-position: center center;
            }

            /* Sisi Kiri (Visual Column): Frosted glass over background */
            .auth-visual-col {
                background: linear-gradient(135deg, rgba(248, 250, 252, 0.86) 0%, rgba(241, 245, 249, 0.82) 100%);
                backdrop-filter: blur(14px);
                -webkit-backdrop-filter: blur(14px);
                border-color: rgba(226, 232, 240, 0.85) !important;
            }

            /* Sisi Kanan (Form Column): Clean, subtle white glass */
            .auth-form-col {
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.93) 100%);
                backdrop-filter: blur(18px);
                -webkit-backdrop-filter: blur(18px);
            }

            .auth-anim-fade {
                animation: authFadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }

            .auth-anim-stagger-1 {
                animation: authFadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.08s forwards;
                opacity: 0;
            }

            .auth-anim-stagger-2 {
                animation: authFadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.16s forwards;
                opacity: 0;
            }

            .auth-anim-stagger-3 {
                animation: authFadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.24s forwards;
                opacity: 0;
            }

            .auth-float-1 {
                animation: authFloat1 4.5s ease-in-out infinite;
            }

            .auth-float-2 {
                animation: authFloat2 5s ease-in-out infinite;
            }

            .auth-input:focus {
                border-color: #4361EE !important;
                box-shadow: 0 0 0 3.5px rgba(67, 97, 238, 0.12) !important;
            }

            .auth-btn-primary {
                background-color: #4361EE;
                border-color: #4361EE;
                color: #FFFFFF;
                box-shadow: 0 4px 14px rgba(67, 97, 238, 0.25);
                transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
            }

            .auth-btn-primary:hover {
                background-color: #3751D4;
                border-color: #3751D4;
                color: #FFFFFF;
                transform: translateY(-1.5px);
                box-shadow: 0 6px 18px rgba(67, 97, 238, 0.32);
            }

            .auth-btn-primary:active {
                transform: translateY(0);
            }

            /* Responsive: Mobile & Tablet */
            @media (max-width: 991.98px) {
                .auth-layout-wrapper {
                    background-position: center top;
                    background-attachment: scroll;
                    padding: 1.5rem 1rem;
                }
                .auth-form-col {
                    background: transparent !important;
                    backdrop-filter: none !important;
                    -webkit-backdrop-filter: none !important;
                    min-height: auto !important;
                    padding: 0 !important;
                }
                .auth-mobile-card {
                    background: rgba(255, 255, 255, 0.96) !important;
                    backdrop-filter: blur(16px) !important;
                    -webkit-backdrop-filter: blur(16px) !important;
                    border: 1px solid rgba(226, 232, 240, 0.9) !important;
                    border-radius: 16px !important;
                    box-shadow: 0 12px 36px -8px rgba(15, 23, 42, 0.08) !important;
                    padding: 2rem 1.5rem !important;
                    width: 100% !important;
                    max-width: 440px !important;
                    margin: 0.5rem auto !important;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                *, ::before, ::after {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                }
                .auth-float-1, .auth-float-2 {
                    animation: none !important;
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased text-dark min-vh-100" style="color: #2B2D42;">
        <div class="auth-layout-wrapper d-flex flex-column flex-lg-row min-vh-100 w-100 overflow-x-hidden">
            
            <!-- SISI KIRI: Visual & Branding Showcase (Desktop / Tablet besar) -->
            <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-between p-5 p-xl-6 border-end position-relative auth-visual-col">
                
                <!-- Top Brand Header -->
                <div class="auth-anim-fade d-flex align-items-center justify-content-between">
                    <a href="/" class="text-decoration-none d-inline-flex align-items-center gap-2.5">
                        <img
                            src="{{ asset('images/task-manager-logo.png') }}"
                            alt="{{ config('app.name', 'Task Manager') }}"
                            class="rounded-2 shadow-xs"
                            style="width: 38px; height: 38px; object-fit: contain;"
                        />
                        <span class="fs-5 fw-bold text-dark lh-1" style="letter-spacing: -0.02em;">Task Manager</span>
                    </a>

                    <a href="/" class="text-decoration-none small text-secondary d-inline-flex align-items-center gap-1" style="color: #64748B;" onmouseover="this.style.color='#4361EE'" onmouseout="this.style.color='#64748B'">
                        <span>Beranda</span>
                        <x-iconly name="chevron-right" size="14" />
                    </a>
                </div>

                <!-- Center: Visual Content Showcase -->
                <div class="my-auto py-4">
                    @if (isset($visual))
                        {{ $visual }}
                    @else
                        {{-- Default Visual for Guest Pages --}}
                        <div class="auth-anim-stagger-1 mb-4">
                            <span class="badge px-3 py-1.5 rounded-pill mb-3 fw-semibold" style="background-color: #EEF2FF; color: #4361EE; font-size: 0.8125rem;">
                                Sistem Manajemen Tugas
                            </span>
                            <h2 class="display-6 fw-bold text-dark tracking-tight mb-2" style="letter-spacing: -0.02em; font-size: 1.85rem; line-height: 1.25;">
                                Selesaikan target penting dengan lebih teratur.
                            </h2>
                            <p class="text-secondary small mb-0" style="color: #64748B; font-size: 0.95rem; line-height: 1.55; max-width: 480px;">
                                Pantau deadline harian, urutkan prioritas pekerjaan, dan terima notifikasi proaktif sebelum tenggat waktu terlewat.
                            </p>
                        </div>

                        <!-- Card Preview -->
                        <div class="auth-anim-stagger-2 position-relative" style="max-width: 480px;">
                            <div class="card rounded-3 border bg-white shadow-sm p-3.5" style="border-color: #E2E8F0 !important;">
                                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom" style="border-color: #F1F5F9 !important;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-2 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; background-color: #EEF2FF; color: #4361EE;">
                                            <x-iconly name="task" size="15" />
                                        </div>
                                        <span class="fw-bold small text-dark">Daftar Tugas Aktif</span>
                                    </div>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-0.5 rounded small" style="font-size: 10px;">
                                        1 Hari Ini
                                    </span>
                                </div>

                                <div class="d-flex flex-column gap-2">
                                    <div class="p-2.5 rounded-2 border d-flex align-items-center justify-content-between" style="border-color: #E2E8F0 !important; background-color: #FFFFFF;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle border" style="width: 16px; height: 16px; border-color: #CBD5E1 !important;"></div>
                                            <div>
                                                <div class="fw-semibold text-dark small" style="font-size: 0.825rem;">Finalisasi Presentasi Proyek</div>
                                                <div class="d-flex align-items-center gap-1.5" style="font-size: 10px; color: #64748B;">
                                                    <span class="badge bg-danger-subtle text-danger px-1 py-0 rounded">Tinggi</span>
                                                    <span>•</span>
                                                    <span class="text-warning fw-semibold">Jatuh Tempo Hari Ini</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="badge rounded-pill text-secondary border small px-1.5 py-0.5" style="font-size: 9px; border-color: #E2E8F0 !important;">Bisnis</span>
                                    </div>

                                    <div class="p-2.5 rounded-2 border d-flex align-items-center justify-content-between" style="border-color: #E2E8F0 !important; background-color: #FFFFFF;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle border" style="width: 16px; height: 16px; border-color: #CBD5E1 !important;"></div>
                                            <div>
                                                <div class="fw-semibold text-dark small" style="font-size: 0.825rem;">Review Dokumen Spesifikasi</div>
                                                <div class="d-flex align-items-center gap-1.5" style="font-size: 10px; color: #64748B;">
                                                    <span class="badge bg-warning-subtle text-warning-emphasis px-1 py-0 rounded">Sedang</span>
                                                    <span>•</span>
                                                    <span>Besok</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="badge rounded-pill text-secondary border small px-1.5 py-0.5" style="font-size: 9px; border-color: #E2E8F0 !important;">Kantor</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Floating Card 1: Notification -->
                            <div class="auth-float-1 position-absolute d-flex align-items-center gap-2 p-2 rounded-3 bg-white border shadow-sm" style="top: -14px; right: -14px; border-color: #E2E8F0 !important; z-index: 2; max-width: 250px;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; background-color: #FFFBEB; color: #D97706;">
                                    <x-iconly name="notification" size="14" />
                                </div>
                                <div style="line-height: 1.2;">
                                    <div class="fw-bold text-dark" style="font-size: 10.5px;">Pengingat Realtime</div>
                                    <div class="text-secondary" style="font-size: 10px; color: #64748B;">1 task perlu diselesaikan hari ini</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Bottom Trust Note -->
                <div class="auth-anim-stagger-3 d-flex align-items-center gap-3 small text-secondary" style="font-size: 0.8rem; color: #8D99AE;">
                    <div class="d-flex align-items-center gap-1">
                        <x-iconly name="check" size="13" class="text-success" />
                        <span>Workspace Terisolasi</span>
                    </div>
                    <span>•</span>
                    <div class="d-flex align-items-center gap-1">
                        <x-iconly name="check" size="13" class="text-success" />
                        <span>Notifikasi Realtime</span>
                    </div>
                </div>
            </div>

            <!-- SISI KANAN: Form Authentication (Login / Register / etc.) -->
            <div class="col-12 col-lg-6 d-flex flex-column justify-content-between p-4 p-sm-5 p-xl-6 auth-form-col min-vh-100">
                
                <!-- Mobile Brand Header & Desktop Back Link -->
                <div class="d-flex align-items-center justify-content-between mb-3 mb-lg-4 w-100 mx-auto" style="max-width: 420px;">
                    <!-- Mobile Logo -->
                    <a href="/" class="d-flex d-lg-none align-items-center gap-2 text-decoration-none">
                        <img
                            src="{{ asset('images/task-manager-logo.png') }}"
                            alt="{{ config('app.name', 'Task Manager') }}"
                            class="rounded-2 shadow-xs"
                            style="width: 32px; height: 32px; object-fit: contain;"
                        />
                        <span class="fs-5 fw-bold text-dark lh-1">Task Manager</span>
                    </a>

                    <!-- Back to Home -->
                    <a href="/" class="text-decoration-none small text-secondary d-inline-flex align-items-center gap-1.5 ms-auto" style="color: #64748B;" onmouseover="this.style.color='#4361EE'" onmouseout="this.style.color='#64748B'">
                        <x-iconly name="chevron-left" size="14" />
                        <span>Kembali ke Beranda</span>
                    </a>
                </div>

                <!-- Center: Auth Form Container -->
                <div class="my-auto py-3 w-100 mx-auto auth-mobile-card" style="max-width: 420px;">
                    {{ $slot }}
                </div>

                <!-- Bottom Copyright -->
                <div class="pt-3 text-center text-secondary small w-100 mx-auto" style="max-width: 420px; font-size: 0.775rem; color: #94A3B8;">
                    &copy; {{ date('Y') }} Task Manager. Seluruh hak cipta dilindungi.
                </div>
            </div>

        </div>
    </body>
</html>
