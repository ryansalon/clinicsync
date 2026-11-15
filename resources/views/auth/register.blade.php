@extends('layouts.guest')

@section('title', 'Register')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight text-[#14201E] sm:text-3xl">Patient registration</h1>
    <p class="mt-2 text-sm leading-relaxed text-[#465854]">Create an account to book appointments and view your medical records.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="name" class="label">Full name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                   autocomplete="name" class="input @error('name') input-invalid @enderror" placeholder="Juan Dela Cruz">
            @error('name')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="email" class="label">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                       class="input @error('email') input-invalid @enderror" placeholder="you@example.com">
                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="phone" class="label">Mobile number</label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" inputmode="numeric" autocomplete="tel"
                       class="input @error('phone') input-invalid @enderror" placeholder="09XXXXXXXXX">
                @error('phone')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="date_of_birth" class="label">Date of birth</label>
                <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                       class="input @error('date_of_birth') input-invalid @enderror">
                @error('date_of_birth')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="sex" class="label">Sex</label>
                <select id="sex" name="sex" required class="input @error('sex') input-invalid @enderror">
                    <option value="">Select…</option>
                    @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('sex') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('sex')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @php
            $hasOptionalInput = $errors->hasAny(['civil_status', 'blood_type', 'address', 'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship'])
                || old('civil_status') || old('blood_type') || old('address')
                || old('emergency_contact_name') || old('emergency_contact_phone') || old('emergency_contact_relationship');
        @endphp

        <details class="rounded-lg border border-[#DCE3E1] bg-[#F4F7F6]/70 p-4" @if ($hasOptionalInput) open @endif>
            <summary class="flex min-h-8 cursor-pointer list-none items-center justify-between text-xs font-semibold uppercase tracking-[0.1em] text-[#465854] hover:text-[#14201E]">
                <span>Optional demographics &amp; emergency contact</span>
                <span class="text-[11px] font-normal normal-case text-[#465854]">Can be added later</span>
            </summary>

            <div class="mt-4 space-y-4 border-t border-[#DCE3E1] pt-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="civil_status" class="label">Civil status</label>
                        <select id="civil_status" name="civil_status" class="input @error('civil_status') input-invalid @enderror">
                            <option value="">Select…</option>
                            @foreach (['single' => 'Single', 'married' => 'Married', 'widowed' => 'Widowed', 'separated' => 'Separated'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('civil_status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('civil_status')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="blood_type" class="label">Blood type</label>
                        <input id="blood_type" type="text" name="blood_type" value="{{ old('blood_type') }}"
                               class="input @error('blood_type') input-invalid @enderror" placeholder="O+">
                        @error('blood_type')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="address" class="label">Address</label>
                    <input id="address" type="text" name="address" value="{{ old('address') }}"
                           class="input @error('address') input-invalid @enderror">
                    @error('address')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <fieldset class="border-t border-[#DCE3E1] pt-3">
                    <legend class="pr-2 font-mono text-[11px] font-semibold uppercase tracking-[0.12em] text-[#465854]">Emergency contact</legend>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="emergency_contact_name" class="label">Contact name</label>
                            <input id="emergency_contact_name" type="text" name="emergency_contact_name"
                                   value="{{ old('emergency_contact_name') }}" class="input">
                        </div>
                        <div>
                            <label for="emergency_contact_phone" class="label">Contact number</label>
                            <input id="emergency_contact_phone" type="tel" name="emergency_contact_phone"
                                   value="{{ old('emergency_contact_phone') }}" inputmode="numeric" autocomplete="tel"
                                   class="input" placeholder="09XXXXXXXXX">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="emergency_contact_relationship" class="label">Relationship</label>
                            <input id="emergency_contact_relationship" type="text" name="emergency_contact_relationship"
                                   value="{{ old('emergency_contact_relationship') }}" class="input" placeholder="Spouse, Parent…">
                        </div>
                    </div>
                </fieldset>
            </div>
        </details>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="label">Password</label>
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
                <label for="password_confirmation" class="label">Confirm password</label>
                <div class="relative">
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                           autocomplete="new-password" class="input pr-16" placeholder="Repeat password">
                    <x-password-toggle for="#password_confirmation" />
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-full py-2.5">
            Create account
            <x-icon name="arrow-right" class="h-4 w-4" />
        </button>
    </form>

    <div class="divider my-6"></div>

    <p class="text-center text-sm text-meta">
        Already registered?
        <a href="{{ route('login') }}" class="font-medium text-bright hover:text-brand-strong">Sign in</a>
    </p>
@endsection
