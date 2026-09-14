<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="h5 fw-bold text-dark mb-1">{{ __('Lupa Kata Sandi?') }}</h2>
        <p class="text-secondary small mb-0 leading-relaxed">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="d-flex align-items-center justify-content-between mt-4 pt-3 border-top">
            <a href="{{ route('login') }}" class="text-xs text-secondary text-decoration-none hover-underline">
                Kembali ke Login
            </a>
            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

