<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="h5 fw-bold text-dark mb-1">{{ __('Verifikasi Email') }}</h2>
        <p class="text-secondary small mb-0 leading-relaxed">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success border-0 bg-success-subtle text-success rounded-3 p-3 mb-4 small">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-4 pt-3 border-top d-flex align-items-center justify-content-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="text-xs text-secondary hover:text-dark text-decoration-none hover-underline btn btn-link p-0">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>

