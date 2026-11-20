@extends('layouts.guest')

@section('title', 'Forgot password')

@section('content')
    <h1 class="text-2xl font-bold tracking-tight text-bright sm:text-3xl">Forgot your password?</h1>
    <p class="mt-2 text-sm leading-relaxed text-meta">
        Enter the email address registered with the clinic and we'll send you a link to reset it.
    </p>

    <form method="POST" action="{{ route('password.email') }}" class="mt-7 space-y-5">
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

        <button type="submit" class="btn btn-primary w-full py-2.5">
            Send reset link
            <x-icon name="arrow-right" class="h-4 w-4" />
        </button>
    </form>

    <div class="divider my-6"></div>

    <p class="text-center text-sm text-meta">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 font-semibold text-brand hover:underline">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Back to sign in
        </a>
    </p>
@endsection
