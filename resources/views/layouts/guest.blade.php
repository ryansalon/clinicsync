<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>@yield('title', 'ClinicSync') · ClinicSync</title>
    {{ Vite::fonts(['geist', 'geist-mono']) }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-shell="guest" class="guest-light min-h-screen bg-[#D5ECE7] font-guest text-[#243330] antialiased">

@php
    $logoUrl = asset('images/logocsasd.png');
    $sealUrl = asset('images/cpsc-logo2.png');
@endphp

<a href="#guest-main"
   class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-[#0F766E] focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
    Skip to sign in form
</a>

<div class="flex min-h-screen flex-col lg:flex-row">
    {{-- Deep Clinical Teal architectural clinic anchor (#093E3A -> #0F766E) --}}
    <aside class="relative hidden w-1/2 flex-col justify-between overflow-hidden border-r border-[#093E3A] bg-gradient-to-br from-[#093E3A] via-[#0D5D57] to-[#0F766E] p-10 text-white lg:sticky lg:top-0 lg:flex lg:h-screen xl:p-12">
        <div class="pointer-events-none absolute inset-0 civic-grid-pattern opacity-100" aria-hidden="true"></div>

        <div class="relative flex flex-1 flex-col justify-center">
            {{-- Transparent ClinicSync + CPSC brand lockup with alpha-following light contour --}}
            <div class="inline-flex w-fit items-center gap-4">
                <img src="{{ $logoUrl }}"
                     alt="ClinicSync"
                     width="597" height="497"
                     class="h-14 w-auto object-contain [filter:drop-shadow(0_0_1.5px_rgba(255,255,255,0.95))_drop-shadow(0_0_4px_rgba(255,255,255,0.55))] xl:h-16"
                     fetchpriority="high" decoding="async">
                <span class="h-10 w-px shrink-0 bg-[#B5DCD4]/50" aria-hidden="true"></span>
                <img src="{{ $sealUrl }}"
                     alt="Camiguin Polytechnic State College seal"
                     width="474" height="474"
                     class="h-14 w-14 shrink-0 object-contain [filter:drop-shadow(0_0_1.5px_rgba(255,255,255,0.92))_drop-shadow(0_2px_6px_rgba(9,62,58,0.35))] xl:h-16 xl:w-16"
                     decoding="async">
            </div>

            <p class="mt-8 max-w-[18ch] text-[2.75rem] font-semibold leading-[1.06] tracking-[-0.03em] text-white xl:text-[3.35rem]">
                Campus care, <span class="text-[#B5DCD4]">charted cleanly.</span>
            </p>

            <p class="mt-4 max-w-lg text-[15px] leading-relaxed text-[#E6F4F1]">
                Coordinating scheduled appointments, walk-in triage, physician consultations,
                and longitudinal medical records for the Camiguin Polytechnic State College outpatient clinic.
            </p>

            <dl class="mt-8 max-w-lg divide-y divide-[#B5DCD4]/25 border-y border-[#B5DCD4]/25">
                <div class="flex items-start gap-3.5 py-3.5">
                    <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-[#B5DCD4]/35 bg-[#093E3A]/70 text-[#B5DCD4]">
                        <x-icon name="list" class="h-4 w-4" />
                    </span>
                    <div>
                        <dt class="text-sm font-medium text-white">Live triage queue coordination</dt>
                        <dd class="mt-0.5 text-xs text-[#D5ECE7]">Patients and reception follow queue tickets and room calls in real time.</dd>
                    </div>
                </div>
                <div class="flex items-start gap-3.5 py-3.5">
                    <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-[#B5DCD4]/35 bg-[#093E3A]/70 text-[#B5DCD4]">
                        <x-icon name="clipboard" class="h-4 w-4" />
                    </span>
                    <div>
                        <dt class="text-sm font-medium text-white">Structured consultations &amp; e-prescriptions</dt>
                        <dd class="mt-0.5 text-xs text-[#D5ECE7]">Allergies, vitals, diagnoses, and prescriptions stay connected to each visit.</dd>
                    </div>
                </div>
                <div class="flex items-start gap-3.5 py-3.5">
                    <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-[#B5DCD4]/35 bg-[#093E3A]/70 text-[#B5DCD4]">
                        <x-icon name="clock" class="h-4 w-4" />
                    </span>
                    <div>
                        <dt class="text-sm font-medium text-white">Clinic hours: Mon–Sat, 08:00–17:00</dt>
                        <dd class="mt-0.5 text-xs text-[#D5ECE7]">Online booking opens per physician schedule; walk-ins accommodated daily.</dd>
                    </div>
                </div>
            </dl>
        </div>

        <div class="relative flex flex-wrap items-center justify-between gap-3 border-t border-[#B5DCD4]/25 pt-4 text-xs">
            <p>
                <span class="font-medium text-white">Camiguin Polytechnic State College</span>
                <span class="text-[#D5ECE7]"> · Outpatient Clinic · © {{ now()->year }}</span>
            </p>
            <span class="cs-num inline-flex items-center rounded-md border border-[#B5DCD4]/35 bg-[#093E3A]/70 px-2.5 py-1 font-mono text-[11px] font-medium text-[#E6F4F1]">
                Mon–Sat · 08:00–17:00
            </span>
        </div>
    </aside>

    {{-- Coastal Mint form stage (#D5ECE7 / #E6F4F1) framing the crisp white auth card --}}
    <main id="guest-main" class="relative flex min-w-0 w-full flex-1 items-center justify-center overflow-x-hidden bg-gradient-to-b from-[#E6F4F1] to-[#D5ECE7] p-4 sm:p-8 lg:p-12">
        <div class="w-full max-w-md min-w-0">
            <div class="mb-6 flex flex-col items-center text-center lg:hidden">
                <div class="inline-flex items-center justify-center gap-4">
                    <img src="{{ $logoUrl }}"
                         alt="ClinicSync"
                         width="597" height="497"
                         class="h-12 w-auto object-contain"
                         fetchpriority="high" decoding="async">
                    <span class="h-9 w-px shrink-0 bg-[#B5DCD4]" aria-hidden="true"></span>
                    <img src="{{ $sealUrl }}"
                         alt="Camiguin Polytechnic State College seal"
                         width="474" height="474"
                         class="h-12 w-12 shrink-0 object-contain"
                         decoding="async">
                </div>
                <span class="mt-3 inline-flex items-center rounded-full bg-[#0F766E] px-3 py-1 font-mono text-[11px] font-medium uppercase tracking-[0.1em] text-white">
                    Outpatient Clinic · Mon–Sat 08:00–17:00
                </span>
            </div>

            <div class="rounded-xl border border-[#B5DCD4] bg-white p-6 shadow-[0_4px_12px_rgba(9,62,58,0.06),0_20px_40px_-14px_rgba(15,118,110,0.16)] sm:p-8">
                @include('partials.flash')
                @yield('content')
            </div>
        </div>
    </main>
</div>
</body>
</html>
