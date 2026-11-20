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
                   description="Personal details, contact information, and password security." />

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Profile details --}}
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="card self-start">
            @csrf
            @method('PATCH')

            <div class="card-header">
                <x-card-title>Profile</x-card-title>
                <p class="mt-0.5 text-xs text-meta">Display name, mobile number, and portrait photo.</p>
            </div>

            <div class="card-body space-y-5">
                <div class="flex items-start gap-4">
                    @if ($photoPath)
                        <img src="{{ route('profile.photo') }}" alt="{{ $user->name }}"
                             width="64" height="64" loading="lazy"
                             class="h-16 w-16 shrink-0 rounded-full object-cover ring-1 ring-hairline">
                    @else
                        <x-avatar :name="$user->name" size="lg" />
                    @endif

                    @if ($user->patient || $user->physician)
                        <div class="min-w-0 flex-1">
                            <label for="photo" class="label">Profile photo</label>
                            <input id="photo" type="file" name="photo" accept="image/png,image/jpeg,image/webp"
                                   class="block w-full cursor-pointer text-sm text-meta file:mr-3 file:min-h-11 file:cursor-pointer file:rounded-md file:border file:border-hairline-2 file:bg-surface-subtle file:px-3.5 file:py-2 file:text-xs file:font-semibold file:text-bright transition hover:file:bg-brand-tint hover:file:text-brand">
                            <p class="helper">JPG, PNG, or WebP · max 2 MB.</p>
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
                           class="input cs-num @error('phone') input-invalid @enderror" placeholder="09XXXXXXXXX">
                    @error('phone')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <dl class="divide-y divide-hairline border-y border-hairline bg-canvas/70">
                    <div class="flex flex-wrap items-center justify-between gap-2 px-3 py-2.5 text-sm">
                        <dt class="text-meta">Login email</dt>
                        <dd class="font-medium text-bright">{{ $user->email }}</dd>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-2 px-3 py-2.5 text-sm">
                        <dt class="text-meta">Role</dt>
                        <dd>
                            <span class="badge badge-confirmed">{{ $roleLabel }}</span>
                        </dd>
                    </div>
                </dl>
                <p class="helper -mt-3">Contact the clinic administrator to update your login email or role.</p>
            </div>

            <div class="border-t border-hairline px-5 py-4">
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
                <x-card-title>Change password</x-card-title>
                <p class="mt-0.5 text-xs text-meta">Use at least 8 characters with letters and numbers.</p>
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

                <x-alert type="info">
                    You will remain signed in on this device after updating your password.
                </x-alert>
            </div>

            <div class="border-t border-hairline px-5 py-4">
                <button type="submit" class="btn btn-secondary">
                    <x-icon name="lock" class="h-4 w-4" />
                    Update password
                </button>
            </div>
        </form>
    </div>
@endsection
