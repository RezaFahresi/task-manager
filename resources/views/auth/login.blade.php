<x-guest-layout>
    <x-slot name="visual">
        <div class="auth-anim-stagger-1 mb-4">
            <span class="badge px-3 py-1.5 rounded-pill mb-3 fw-semibold" style="background-color: #EEF2FF; color: #4361EE; font-size: 0.8125rem;">
                Selamat Datang Kembali
            </span>
            <h2 class="display-6 fw-bold text-dark tracking-tight mb-2" style="letter-spacing: -0.02em; font-size: 1.85rem; line-height: 1.25;">
                Lanjutkan target dan tugas Anda hari ini.
            </h2>
            <p class="text-secondary small mb-0" style="color: #64748B; font-size: 0.95rem; line-height: 1.55; max-width: 480px;">
                Pantau deadline harian, urutkan prioritas pekerjaan, dan terima notifikasi proaktif sebelum tenggat waktu terlewat.
            </p>
        </div>

        <!-- Task Preview Box -->
        <div class="auth-anim-stagger-2 position-relative" style="max-width: 480px;">
            <div class="card rounded-3 border bg-white shadow-sm p-3.5" style="border-color: #E2E8F0 !important;">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom" style="border-color: #F1F5F9 !important;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-2 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; background-color: #EEF2FF; color: #4361EE;">
                            <x-iconly name="task" size="15" />
                        </div>
                        <span class="fw-bold small text-dark">Daftar Tugas Hari Ini</span>
                    </div>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-0.5 rounded small" style="font-size: 10px;">
                        1 Jatuh Tempo
                    </span>
                </div>

                <div class="d-flex flex-column gap-2">
                    <div class="p-2.5 rounded-2 border d-flex align-items-center justify-content-between" style="border-color: #E2E8F0 !important; background-color: #FFFFFF;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle border" style="width: 16px; height: 16px; border-color: #CBD5E1 !important;"></div>
                            <div>
                                <div class="fw-semibold text-dark small" style="font-size: 0.825rem;">Finalisasi Laporan Proyek</div>
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
                                <div class="fw-semibold text-dark small" style="font-size: 0.825rem;">Rapat Sinkronisasi Tim</div>
                                <div class="d-flex align-items-center gap-1.5" style="font-size: 10px; color: #64748B;">
                                    <span class="badge bg-warning-subtle text-warning-emphasis px-1 py-0 rounded">Sedang</span>
                                    <span>•</span>
                                    <span>Besok</span>
                                </div>
                            </div>
                        </div>
                        <span class="badge rounded-pill text-secondary border small px-1.5 py-0.5" style="font-size: 9px; border-color: #E2E8F0 !important;">Kantor</span>
                    </div>

                    <div class="p-2.5 rounded-2 border d-flex align-items-center justify-content-between" style="border-color: #E2E8F0 !important; background-color: #F8FAFC; opacity: 0.85;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 16px; height: 16px; background-color: #10B981;">
                                <x-iconly name="check" size="10" />
                            </div>
                            <div>
                                <div class="text-decoration-line-through text-muted small" style="font-size: 0.825rem;">Review Checklist Kategori</div>
                                <div class="d-flex align-items-center gap-1" style="font-size: 10px; color: #10B981;">
                                    <span>Selesai</span>
                                </div>
                            </div>
                        </div>
                        <span class="badge rounded-pill text-secondary border small px-1.5 py-0.5" style="font-size: 9px; border-color: #E2E8F0 !important;">Sistem</span>
                    </div>
                </div>
            </div>

            <!-- Floating Pill 1: Notification -->
            <div class="auth-float-1 position-absolute d-flex align-items-center gap-2 p-2 rounded-3 bg-white border shadow-sm" style="top: -14px; right: -14px; border-color: #E2E8F0 !important; z-index: 2; max-width: 250px;">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; background-color: #FFFBEB; color: #D97706;">
                    <x-iconly name="notification" size="14" />
                </div>
                <div style="line-height: 1.2;">
                    <div class="fw-bold text-dark" style="font-size: 10.5px;">Pengingat Realtime</div>
                    <div class="text-secondary" style="font-size: 10px; color: #64748B;">1 task perlu diselesaikan hari ini</div>
                </div>
            </div>

            <!-- Floating Pill 2: Productivity -->
            <div class="auth-float-2 position-absolute d-flex align-items-center gap-2 p-2 rounded-3 bg-white border shadow-sm" style="bottom: -14px; left: -14px; border-color: #E2E8F0 !important; z-index: 2;">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; background-color: #ECFDF5; color: #10B981;">
                    <x-iconly name="check" size="14" />
                </div>
                <div style="line-height: 1.2;">
                    <div class="fw-bold text-dark" style="font-size: 10.5px;">Tingkat Penyelesaian 80%</div>
                    <div class="text-secondary" style="font-size: 10px; color: #64748B;">8 tugas berhasil dituntaskan</div>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- FORM LOGIN -->
    <div class="auth-anim-stagger-1 mb-4">
        <h1 class="h4 fw-bold text-dark tracking-tight mb-1" style="letter-spacing: -0.02em;">Masuk ke Akun</h1>
        <p class="text-secondary small mb-0" style="color: #64748B;">Masukkan email dan kata sandi Anda untuk melanjutkan.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="auth-anim-stagger-2 d-flex flex-column gap-3">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="fw-semibold text-dark small mb-1" />
            <x-text-input id="email" class="block w-full py-2.5 px-3 rounded-3 auth-input" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <div class="d-flex align-items-center justify-content-between mb-1">
                <x-input-label for="password" :value="__('Password')" class="fw-semibold text-dark small mb-0" />
                @if (Route::has('password.request'))
                    <a class="small text-decoration-none fw-semibold" style="font-size: 0.8125rem; color: #4361EE;" href="{{ route('password.request') }}">
                        Lupa kata sandi?
                    </a>
                @endif
            </div>

            <x-text-input id="password" class="block w-full py-2.5 px-3 rounded-3 auth-input"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="••••••••" />

            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Remember Me -->
        <div class="d-flex align-items-center justify-content-between pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer user-select-none">
                <input id="remember_me" type="checkbox" class="rounded border-secondary-subtle text-primary focus:ring-primary/20" name="remember">
                <span class="ms-2 small text-secondary" style="font-size: 0.8125rem; color: #64748B;">Ingat saya di perangkat ini</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="btn auth-btn-primary w-100 py-2.5 rounded-3 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                <span>Masuk ke Akun</span>
                <x-iconly name="chevron-right" size="15" />
            </button>
        </div>

        <!-- Switch to Register -->
        @if (Route::has('register'))
            <div class="pt-2 text-center">
                <span class="small text-secondary" style="color: #64748B;">Belum punya akun?</span>
                <a href="{{ route('register') }}" class="small fw-semibold text-decoration-none ms-1" style="color: #4361EE;">
                    Daftar Akun Baru
                </a>
            </div>
        @endif
    </form>
</x-guest-layout>
