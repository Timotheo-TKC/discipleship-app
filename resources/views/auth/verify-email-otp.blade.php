<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Thanks for signing up! We\'ve sent a 6-digit verification code to your email address. Please enter it below to verify your account.') }}
    </div>

    @if (session('status') == 'otp-sent')
        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            {{ __('A new verification code has been sent to your email address.') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4">
            <div class="font-medium text-red-600 dark:text-red-400">
                {{ __('Whoops! Something went wrong.') }}
            </div>
            <ul class="mt-3 list-disc list-inside text-sm text-red-600 dark:text-red-400">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('verification.otp.verify') }}">
        @csrf

        <!-- OTP Input -->
        <div class="mb-4">
            <label for="otp" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ __('Verification Code') }}
            </label>
            <input 
                type="text" 
                id="otp" 
                name="otp" 
                maxlength="6" 
                pattern="[0-9]{6}"
                autocomplete="one-time-code"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-center text-2xl font-mono tracking-widest"
                placeholder="000000"
                required 
                autofocus
            />
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                {{ __('Enter the 6-digit code sent to your email. The code expires in 10 minutes.') }}
            </p>
        </div>

        <div class="flex items-center justify-between">
            <button 
                type="submit"
                class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
            >
                {{ __('Verify Email') }}
            </button>

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button 
                    type="submit"
                    class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                >
                    {{ __('Resend Code') }}
                </button>
            </form>
        </div>
    </form>

    <div class="mt-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>

