@extends('app')

@section('title', __('auth.reset_password'))

@section('content')
    <section class="flex items-center justify-center py-12 h-screen w-screen bg-slate-300">
        <div class="max-w-md p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
            <form method="POST" action="{{ route('password.update') }}" class="mt-2 sm:mx-auto sm:w-full sm:max-w-md">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Email Address -->
                @if($request->query('email'))
                    <input type="hidden" name="email" value="{{ $request->query('email') }}">
                @else
                    <div class="mt-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">{{ __('auth.email_address') }}</label>
                        <input id="email" type="email" name="email" required value="{{ old('email', $request->query('email')) }}"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    </div>
                    @error('email')
                        <div class="text-sm text-red-700">{{ $message }}</div>
                    @enderror
                @endif

                <!-- Password -->
                <div class="mt-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">{{ __('auth.password') }}</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                </div>
                @error('password')
                    <div class="text-sm text-red-700">{{ $message }}</div>
                @enderror

                <!-- Confirm Password -->
                <div class="mt-4">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('auth.confirm_password') }}</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                </div>
                @error('password_confirmation')
                    <div class="text-sm text-red-700">{{ $message }}</div>
                @enderror

                <div class="mt-4">
                    <button type="submit" class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-sm hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        {{ __('auth.reset_password') }}
                        <svg class="rtl:rotate-180 w-3.5 h-3.5 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection
