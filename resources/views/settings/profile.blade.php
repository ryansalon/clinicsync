@extends('layouts.app')

@section('title', 'Profile settings')

@section('content')
    @php
        $photoPath = $user->patient?->photo_path ?? $user->physician?->photo_path;
        $roleLabel = $user->getRoleNames()
            ->map(fn ($role) => \App\Enums\Role::tryFrom($role)?->label() ?? $role)
            ->first() ?? 'No role assigned';
    @endphp

    <x-page-header title="Profile settings"
                   description="Manage your personal details and account security." />

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Profile details --}}
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="card self-start">
            @csrf
            @method('PATCH')

            <div class="card-header">
                <h2 class="text-sm font-semibold text-slate-900">Profile</h2>
                <p class="mt-0.5 text-xs text-slate-500">Your name, contact number and photo.</p>
            </div>

            <div class="card-body space-y-5">
                <div class="flex items-start gap-4">
                    @if ($photoPath)
                        <img src="{{ route('profile.photo') }}" alt="{{ $user->name }}"
                             width="64" height="64" loading="lazy"
                             class="h-16 w-16 shrink-0 rounded-full object-cover ring-2 ring-slate-200">
                    @else
                        <x-avatar :name="$user->name" size="lg" />
                    @endif

                    @if ($user->patient || $user->physician)
                        <div class="min-w-0 flex-1">
                            <label for="photo" class="label">Profile photo</label>
                            <input id="photo" type="file" name="photo" accept="image/png,image/jpeg,image/webp"
                                   class="block w-full cursor-pointer text-sm text-slate-600 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 transition hover:file:bg-slate-200">
                            <p class="helper">JPG, PNG or WebP · up to 2 MB.</p>
                            @error('photo')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>

                <div>
                    <label for="name" class="label">Full name</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                           autocomplete="name" class="input @error('name') input-invalid @enderror">
                    @error('name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="label">Mobile number</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" inputmode="numeric" autocomplete="tel"
                           class="input @error('phone') input-invalid @enderror" placeholder="09XXXXXXXXX">
                    @error('phone')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-lg border border-slate-200 bg-slate-50/70 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                        <span class="text-slate-500">Login email</span>
                        <span class="font-medium text-slate-900">{{ $user->email }}</span>
                    </div>
                    <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-sm">
                        <span class="text-slate-500">Role</span>
                        <span class="badge bg-teal-100 text-teal-800">{{ $roleLabel }}</span>
                    </div>
                    <p class="helper">Contact the clinic administrator to change your login email or role.</p>
                </div>
            </div>

            <div class="border-t border-slate-200 px-5 py-4">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="check" class="h-4 w-4" />
                    Save changes
                </button>
            </div>
        </form>

        {{-- Password --}}
        <form method="POST" action="{{ route('password.change') }}" class="card self-start">
            @csrf
            @method('PATCH')

            <div class="card-header">
                <h3 class="text-sm font-semibold text-slate-900">Change password</h3>
                <p class="mt-0.5 text-xs text-slate-500">Use at least 8 characters with a mix of letters and numbers.</p>
            </div>

            <div class="card-body space-y-5">
                <div>
                    <label for="current_password" class="label">Current password</label>
                    <div class="relative">
                        <input id="current_password" type="password" name="current_password" required
                               autocomplete="current-password" class="input pr-16 @error('current_password') input-invalid @enderror"
                               placeholder="••••••••">
                        <x-password-toggle for="#current_password" />
                    </div>
                    @error('current_password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="label">New password</label>
                    <div class="relative">
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                               class="input pr-16 @error('password') input-invalid @enderror" placeholder="At least 8 characters">
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

                <div class="alert alert-info">
                    <x-icon name="information-circle" class="mt-0.5 h-5 w-5 shrink-0" />
                    <p>You'll stay signed in on this device after changing your password.</p>
                </div>
            </div>

            <div class="border-t border-slate-200 px-5 py-4">
                <button type="submit" class="btn btn-secondary">
                    <x-icon name="lock" class="h-4 w-4" />
                    Update password
                </button>
            </div>
        </form>
    </div>
@endsection
