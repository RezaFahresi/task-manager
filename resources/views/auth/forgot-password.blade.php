<x-guest-layout>
    <x-slot name="visual">
        <div class="auth-anim-stagger-1 mb-4">
            <span class="badge px-3 py-1.5 rounded-pill mb-3 fw-semibold" style="background-color: #EEF2FF; color: #4361EE; font-size: 0.8125rem;">
                Pemulihan Akun
            </span>
            <h2 class="display-6 fw-bold text-dark tracking-tight mb-2" style="letter-spacing: -0.02em; font-size: 1.85rem; line-height: 1.25;">
                Akses kembali tugas dan alur kerja Anda.
            </h2>
            <p class="text-secondary small mb-0" style="color: #64748B; font-size: 0.95rem; line-height: 1.55; max-width: 480px;">
                Lupa kata sandi? Masukkan email terdaftar Anda dan kami akan mengirimkan tautan untuk membuat kata sandi baru dengan aman.
            </p>
        </div>

        <!-- Security / Recovery Showcase Box -->
        <div class="auth-anim-stagger-2 position-relative" style="max-width: 480px;">
            <div class="card rounded-3 border bg-white shadow-sm p-3.5" style="border-color: #E2E8F0 !important;">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom" style="border-color: #F1F5F9 !important;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-2 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; background-color: #EEF2FF; color: #4361EE;">
                            <x-iconly name="shield-done" size="15" />
                        </div>
                        <span class="fw-bold small text-dark">Langkah Pemulihan Aman</span>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded small" style="font-size: 10px;">
                        Terkonfirmasi
                    </span>
                </div>

                <div class="d-flex flex-column gap-2.5">
                    <div class="p-2.5 rounded-2 border d-flex align-items-start gap-2.5" style="border-color: #E2E8F0 !important; background-color: #FFFFFF;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0" style="width: 22px; height: 22px; background-color: #4361EE; font-size: 11px;">
                            1
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold text-dark small" style="font-size: 0.825rem;">Permintaan Tautan Reset</div>
                            <div class="text-secondary small mt-0.5" style="font-size: 10.5px; color: #64748B;">
                                Tautan rahasia dikirim langsung ke alamat email terdaftar Anda.
                            </div>
                        </div>
                    </div>

                    <div class="p-2.5 rounded-2 border d-flex align-items-start gap-2.5" style="border-color: #E2E8F0 !important; background-color: #FFFFFF;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0" style="width: 22px; height: 22px; background-color: #3F37C9; font-size: 11px;">
                            2
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold text-dark small" style="font-size: 0.825rem;">Buat Kata Sandi Baru</div>
                            <div class="text-secondary small mt-0.5" style="font-size: 10.5px; color: #64748B;">
                                Masukkan kata sandi baru yang kuat untuk melindungi akun.
                            </div>
                        </div>
                    </div>

                    <div class="p-2.5 rounded-2 border d-flex align-items-start gap-2.5" style="border-color: #E2E8F0 !important; background-color: #FFFFFF;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0" style="width: 22px; height: 22px; background-color: #10B981; font-size: 11px;">
                            3
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold text-dark small" style="font-size: 0.825rem;">Akses Kembali Tugas Anda</div>
                            <div class="text-secondary small mt-0.5" style="font-size: 10.5px; color: #64748B;">
                                Masuk kembali dan lanjutkan pencapaian target harian Anda.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floating Pill 1: Security -->
            <div class="auth-float-1 position-absolute d-flex align-items-center gap-2 p-2 rounded-3 bg-white border shadow-sm" style="top: -14px; right: -14px; border-color: #E2E8F0 !important; z-index: 2; max-width: 250px;">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; background-color: #ECFDF5; color: #10B981;">
                    <x-iconly name="lock" size="14" />
                </div>
                <div style="line-height: 1.2;">
                    <div class="fw-bold text-dark" style="font-size: 10.5px;">Enkripsi Standar Tinggi</div>
                    <div class="text-secondary" style="font-size: 10px; color: #64748B;">Data akun terlindungi penuh</div>
                </div>
            </div>

            <!-- Floating Pill 2: Fast support -->
            <div class="auth-float-2 position-absolute d-flex align-items-center gap-2 p-2 rounded-3 bg-white border shadow-sm" style="bottom: -14px; left: -14px; border-color: #E2E8F0 !important; z-index: 2;">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; background-color: #EEF2FF; color: #4361EE;">
                    <x-iconly name="message" size="14" />
                </div>
                <div style="line-height: 1.2;">
                    <div class="fw-bold text-dark" style="font-size: 10.5px;">Email Instan</div>
                    <div class="text-secondary" style="font-size: 10px; color: #64748B;">Tautan terkirim dalam detik</div>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- FORM FORGOT PASSWORD -->
    <div class="auth-anim-stagger-1 mb-4">
        <h1 class="h4 fw-bold text-dark tracking-tight mb-1" style="letter-spacing: -0.02em;">Lupa Kata Sandi?</h1>
        <p class="text-secondary small mb-0" style="color: #64748B;">
            Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="auth-anim-stagger-2 d-flex flex-column gap-3">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="fw-semibold text-dark small mb-1" />
            <x-text-input id="email" class="block w-full py-2.5 px-3 rounded-3 auth-input" type="email" name="email" :value="old('email')" required autofocus placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="btn auth-btn-primary w-100 py-2.5 rounded-3 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                <span>Kirim Tautan Atur Ulang</span>
                <x-iconly name="chevron-right" size="15" />
            </button>
        </div>

        <!-- Back to Login -->
        <div class="pt-2 text-center">
            <a href="{{ route('login') }}" class="small fw-semibold text-decoration-none d-inline-flex align-items-center gap-1.5" style="color: #4361EE;">
                <x-iconly name="arrow-left" size="13" />
                <span>Kembali ke Halaman Masuk</span>
            </a>
        </div>
    </form>
</x-guest-layout>
