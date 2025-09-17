<x-customer-portal.layouts.guest>
    <div class="mb-4 text-center">
        <h2 class="text-xl font-semibold text-gray-900">Forgot Password</h2>
        <p class="text-sm text-gray-600 mt-1">Enter your email to receive a password reset link</p>
    </div>

    <div class="mb-4 text-sm text-gray-600">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <form method="POST" action="{{ route('customer-portal.password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="text-sm text-primary-600 hover:text-primary-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" href="{{ route('customer-portal.login') }}">
                {{ __('Back to login') }}
            </a>

            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-customer-portal.layouts.guest>