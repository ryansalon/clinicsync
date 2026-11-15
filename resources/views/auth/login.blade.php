@extends('layouts.guest')

@section('title', 'Sign in')

@section('content')
    <p class="inline-flex items-center gap-2 font-guest-mono text-[11px] uppercase tracking-[0.16em] text-live">
        <span class="h-1.5 w-1.5 rounded-full bg-live" aria-hidden="true"></span>
        Patient &amp; Staff Portal
    </p>
    <h1 class="mt-2 text-3xl font-normal tracking-tight text-bright">Welcome back</h1>
    <p class="mt-2 text-sm leading-relaxed text-read">Sign in with the email address registered with the clinic.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
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
            <label for="password" class="label">Password</label>
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

        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center">
                <input id="remember" type="checkbox" name="remember" value="1"
                       class="h-4 w-4 rounded accent-live">
                <label for="remember" class="ml-2 cursor-pointer text-sm text-read">
                    Remember me
                </label>
            </div>
            <a href="{{ route('password.request') }}"
               class="text-sm font-medium text-read hover:text-brand-strong">
                Forgot password?
            </a>
        </div>

        <button type="submit" class="btn btn-primary w-full py-2.5 flex items-center justify-center gap-2">
            Sign in
            <x-icon name="arrow-right" class="h-4 w-4" />
        </button>
    </form>

    <hr class="my-6 border-hairline">

    <p class="text-center text-sm text-meta">
        No account yet?
        <a href="{{ route('register') }}" class="font-medium text-bright hover:text-brand-strong">
            Register as a patient
        </a>
    </p>
    <p class="mt-2 text-center text-xs text-meta">
        Physicians and clinic staff sign in with clinic-issued credentials.
    </p>
@endsection
