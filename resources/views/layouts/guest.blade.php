<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>@yield('title', 'ClinicSync') · ClinicSync</title>
    {{ Vite::fonts(['public-sans', 'jetbrains-mono']) }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-shell="guest" class="guest-light min-h-screen bg-canvas font-guest text-read antialiased">

@php
    $logoUrl = asset('images/logocsasd.png');
    $sealUrl = asset('images/cpsc-logo2.png');
@endphp

<a href="#guest-main"
   class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-md focus:bg-brand focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
    Skip to sign in form
</a>

<div class="flex min-h-screen flex-col lg:flex-row">
    {{-- Warm Travertine institutional anchor panel — full-color CPSC & ClinicSync marks sit cleanly on daylight paper --}}
    <aside class="relative hidden w-1/2 flex-col justify-between overflow-hidden border-r border-hairline bg-rail p-10 text-bright lg:sticky lg:top-0 lg:flex lg:h-screen xl:p-14">
        <div class="pointer-events-none absolute inset-0 dossier-paper-wash" aria-hidden="true"></div>

        <div class="relative flex flex-1 flex-col justify-center">
            {{-- Co-branded CPSC + ClinicSync lockup at equal height above Outpatient Clinic Management --}}
            <div class="w-fit">
                <div class="inline-flex items-center gap-4">
                    <img src="{{ $logoUrl }}"
                         alt="ClinicSync"
                         width="597" height="497"
                         class="h-14 w-auto object-contain xl:h-16"
                         fetchpriority="high" decoding="async">
                    <span class="h-11 w-px shrink-0 bg-hairline-2" aria-hidden="true"></span>
                    <img src="{{ $sealUrl }}"
                         alt="Camiguin Polytechnic State College seal"
                         width="474" height="474"
                         class="h-14 w-14 shrink-0 object-contain xl:h-16 xl:w-16"
                         decoding="async">
                </div>
                <p class="mt-3 font-mono text-[11px] font-semibold tracking-[0.12em] text-brand uppercase">
                    Outpatient Clinic Management
                </p>
            </div>

            <p class="mt-9 max-w-[18ch] text-[2.5rem] font-bold leading-[1.08] tracking-[-0.03em] text-bright xl:text-[3.1rem]">
                Campus care, <span class="text-brand">charted cleanly.</span>
            </p>

            <p class="mt-4 max-w-lg text-[15px] leading-relaxed text-read">
                Coordinating scheduled appointments, walk-in triage, physician consultations,
                and longitudinal medical records for the Camiguin Polytechnic State College outpatient clinic.
            </p>

            <dl class="mt-8 max-w-lg divide-y divide-hairline-2 border-y border-hairline-2">
                <div class="flex items-start gap-3.5 py-3.5">
                    <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-brand-border bg-brand-tint text-brand">
                        <x-icon name="list" class="h-4 w-4" />
                    </span>
                    <div>
                        <dt class="text-sm font-semibold text-bright">Live triage queue coordination</dt>
                        <dd class="mt-0.5 text-xs leading-relaxed text-meta">Patients and reception follow queue tickets and room calls in real time.</dd>
                    </div>
                </div>
                <div class="flex items-start gap-3.5 py-3.5">
                    <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-brand-border bg-brand-tint text-brand">
                        <x-icon name="clipboard" class="h-4 w-4" />
                    </span>
                    <div>
                        <dt class="text-sm font-semibold text-bright">Structured consultations &amp; e-prescriptions</dt>
                        <dd class="mt-0.5 text-xs leading-relaxed text-meta">Allergies, vitals, diagnoses, and prescriptions stay connected to each visit.</dd>
                    </div>
                </div>
                <div class="flex items-start gap-3.5 py-3.5">
                    <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-brand-border bg-brand-tint text-brand">
                        <x-icon name="clock" class="h-4 w-4" />
                    </span>
                    <div>
                        <dt class="text-sm font-semibold text-bright">Clinic hours: Mon–Sat, 08:00–17:00</dt>
                        <dd class="mt-0.5 text-xs leading-relaxed text-meta">Online booking opens per physician schedule; walk-ins accommodated daily.</dd>
                    </div>
                </div>
            </dl>
        </div>

        <div class="relative flex flex-wrap items-center justify-between gap-3 border-t border-hairline-2 pt-4 text-xs">
            <p class="text-read">
                <span class="font-semibold text-bright">Camiguin Polytechnic State College</span>
                <span class="text-meta"> · Outpatient Clinic · © {{ now()->year }}</span>
            </p>
            <span class="cs-num inline-flex items-center rounded-md border border-hairline-2 bg-surface px-2.5 py-1 font-mono text-[11px] font-medium text-brand">
                Mon–Sat · 08:00–17:00
            </span>
        </div>
    </aside>

    {{-- Archival Ivory form stage framing the crisp folio paper auth card --}}
    <main id="guest-main" class="relative flex min-w-0 w-full flex-1 items-center justify-center overflow-x-hidden bg-canvas p-4 sm:p-8 lg:p-12">
        <div class="w-full max-w-md min-w-0">
            <div class="mb-6 flex flex-col items-center text-center lg:hidden">
                <div class="inline-flex items-center justify-center gap-4">
                    <img src="{{ $logoUrl }}"
                         alt="ClinicSync"
                         width="597" height="497"
                         class="h-12 w-auto object-contain"
                         fetchpriority="high" decoding="async">
                    <span class="h-9 w-px shrink-0 bg-hairline-2" aria-hidden="true"></span>
                    <img src="{{ $sealUrl }}"
                         alt="Camiguin Polytechnic State College seal"
                         width="474" height="474"
                         class="h-12 w-12 shrink-0 object-contain"
                         decoding="async">
                </div>
                <p class="mt-2.5 font-mono text-[11px] font-semibold tracking-[0.1em] text-brand uppercase">
                    Outpatient Clinic Management
                </p>
                <span class="cs-num mt-1.5 inline-flex items-center rounded-full border border-hairline bg-surface px-3 py-0.5 font-mono text-[11px] font-medium text-meta">
                    Mon–Sat · 08:00–17:00
                </span>
            </div>

            <div class="rounded-lg border border-hairline bg-surface p-6 sm:p-8">
                @include('partials.flash')
                @yield('content')
            </div>
        </div>
    </main>
</div>
</body>
</html>
