<x-guest-layout>
    <x-slot name="visual">
        <div class="auth-anim-stagger-1 mb-4">
            <span class="badge px-3 py-1.5 rounded-pill mb-3 fw-semibold" style="background-color: #EEF2FF; color: #4361EE; font-size: 0.8125rem;">
                Keamanan Akun
            </span>
            <h2 class="display-6 fw-bold text-dark tracking-tight mb-2" style="letter-spacing: -0.02em; font-size: 1.85rem; line-height: 1.25;">
                Amankan kembali akses ke tugas Anda.
            </h2>
            <p class="text-secondary small mb-0" style="color: #64748B; font-size: 0.95rem; line-height: 1.55; max-width: 480px;">
                Buat kata sandi baru yang unik dan kuat untuk melindungi seluruh catatan tugas, tenggat waktu, dan kategori Anda.
            </p>
        </div>

        <div class="auth-anim-stagger-2 position-relative" style="max-width: 480px;">
            <div class="card rounded-3 border bg-white shadow-sm p-3.5" style="border-color: #E2E8F0 !important;">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom" style="border-color: #F1F5F9 !important;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-2 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; background-color: #EEF2FF; color: #4361EE;">
                            <x-iconly name="lock" size="15" />
                        </div>
                        <span class="fw-bold small text-dark">Proteksi Sandi Baru</span>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded small" style="font-size: 10px;">
                        Terkunci Aman
                    </span>
                </div>

                <div class="d-flex flex-column gap-2.5">
                    <div class="p-2.5 rounded-2 border d-flex align-items-center gap-2.5" style="border-color: #E2E8F0 !important; background-color: #FFFFFF;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 20px; height: 20px; background-color: #ECFDF5; color: #10B981;">
                            <x-iconly name="check" size="12" />
                        </div>
                        <span class="small text-secondary" style="font-size: 0.825rem; color: #475569;">Gunakan minimal 8 karakter dengan variasi angka</span>
                    </div>
                    <div class="p-2.5 rounded-2 border d-flex align-items-center gap-2.5" style="border-color: #E2E8F0 !important; background-color: #FFFFFF;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 20px; height: 20px; background-color: #ECFDF5; color: #10B981;">
                            <x-iconly name="check" size="12" />
                        </div>
                        <span class="small text-secondary" style="font-size: 0.825rem; color: #475569;">Hindari menggunakan tanggal lahir atau kata umum</span>
                    </div>
                </div>
            </div>

            <!-- Floating Pill -->
            <div class="auth-float-1 position-absolute d-flex align-items-center gap-2 p-2 rounded-3 bg-white border shadow-sm" style="top: -14px; right: -14px; border-color: #E2E8F0 !important; z-index: 2;">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; background-color: #ECFDF5; color: #10B981;">
                    <x-iconly name="shield-done" size="14" />
                </div>
                <div style="line-height: 1.2;">
                    <div class="fw-bold text-dark" style="font-size: 10.5px;">Enkripsi Aktif</div>
                    <div class="text-secondary" style="font-size: 10px; color: #64748B;">Sandi terproteksi bcrypt</div>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- FORM RESET PASSWORD -->
    <div class="auth-anim-stagger-1 mb-3">
        <h1 class="h4 fw-bold text-dark tracking-tight mb-1" style="letter-spacing: -0.02em;">Atur Ulang Kata Sandi</h1>
        <p class="text-secondary small mb-0" style="color: #64748B;">Masukkan kata sandi baru untuk akun Anda.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="auth-anim-stagger-2 d-flex flex-column gap-2.5">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="fw-semibold text-dark small mb-1" />
            <x-text-input id="email" class="block w-full py-2 px-3 rounded-3 auth-input" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Kata Sandi Baru')" class="fw-semibold text-dark small mb-1" />
            <x-text-input id="password" class="block w-full py-2 px-3 rounded-3 auth-input" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi Baru')" class="fw-semibold text-dark small mb-1" />
            <x-text-input id="password_confirmation" class="block w-full py-2 px-3 rounded-3 auth-input"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password"
                                placeholder="Ulangi kata sandi baru" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="pt-2">
            <button type="submit" class="btn auth-btn-primary w-100 py-2.5 rounded-3 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                <span>Atur Ulang Kata Sandi</span>
                <x-iconly name="chevron-right" size="15" />
            </button>
        </div>
    </form>
</x-guest-layout>
