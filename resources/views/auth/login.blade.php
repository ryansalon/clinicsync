@extends('layouts.guest')

@section('title', 'Sign in')

@section('content')
    <h1 class="text-2xl font-bold tracking-tight text-bright sm:text-3xl">Welcome back</h1>
    <p class="mt-1.5 text-sm leading-relaxed text-meta">Sign in with the email address registered with the clinic.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5">
        @csrf

        <div>
            <label for="email" class="label">Email address</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-meta">
                    <x-icon name="envelope" class="h-[18px] w-[18px]" />
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       autocomplete="email" class="input pl-10 @error('email') input-invalid @enderror"
                       placeholder="you@example.com">
            </div>
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="mb-1.5 flex items-center justify-between gap-2">
                <label for="password" class="label mb-0">Password</label>
                <a href="{{ route('password.request') }}"
                   class="text-xs font-semibold text-brand transition hover:underline">
                    Forgot password?
                </a>
            </div>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-meta">
                    <x-icon name="lock" class="h-[18px] w-[18px]" />
                </span>
                <input id="password" type="password" name="password" required
                       autocomplete="current-password" class="input pr-16 pl-10 @error('password') input-invalid @enderror"
                       placeholder="••••••••">
                <x-password-toggle for="#password" />
            </div>
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center">
            <input id="remember" type="checkbox" name="remember" value="1"
                   class="h-4 w-4 rounded border-hairline-2 accent-brand">
            <label for="remember" class="ml-2 cursor-pointer text-sm text-read">
                Keep me signed in on this device
            </label>
        </div>

        <button type="submit" class="btn btn-primary w-full py-2.5">
            Sign in
            <x-icon name="arrow-right" class="h-4 w-4" />
        </button>
    </form>

    <div class="mt-7 space-y-3 border-t border-hairline pt-6">
        <a href="{{ route('register') }}"
           class="btn btn-secondary w-full justify-between px-4 text-sm font-medium">
            <span>New patient? Register an account</span>
            <x-icon name="arrow-right" class="h-4 w-4 text-meta" />
        </a>
        <p class="text-center text-xs text-meta">
            Physicians and clinic staff sign in with clinic-issued credentials.
        </p>
    </div>
@endsection
