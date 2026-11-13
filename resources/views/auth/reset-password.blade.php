@extends('layouts.guest')

@section('title', 'Reset password')

@section('content')
    <h1 class="text-3xl font-normal tracking-tight text-bright">Choose a new password</h1>
    <p class="mt-2 text-sm leading-relaxed text-read">Pick a strong password you haven't used before.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="label">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required
                   autocomplete="email" class="input @error('email') input-invalid @enderror">
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="label">New password</label>
            <div class="relative">
                <input id="password" type="password" name="password" required autofocus
                       autocomplete="new-password" class="input pr-16 @error('password') input-invalid @enderror"
                       placeholder="At least 8 characters">
                <x-password-toggle for="#password" />
            </div>
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="label">Confirm new password</label>
            <div class="relative">
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       autocomplete="new-password" class="input pr-16" placeholder="Repeat password">
                <x-password-toggle for="#password_confirmation" />
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-full py-2.5">
            Reset password
            <x-icon name="check" class="h-4 w-4" />
        </button>
    </form>

    <div class="divider my-6"></div>

    <p class="text-center text-sm text-meta">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 font-medium text-bright hover:text-brand-strong">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Back to sign in
        </a>
    </p>
@endsection
