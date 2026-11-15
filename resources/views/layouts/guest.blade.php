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
<body data-shell="guest" class="guest-light min-h-screen bg-ground font-guest text-read antialiased">

@php
    $logoUrl = asset('images/logocsasd.png');
    $sealUrl = asset('images/cpsc-logo2.png');
@endphp
{{-- Ribbon Field WebGL background (ThreeUI `ribbon-field` port). --}}
<div class="shader-frame" aria-hidden="true">
    <div data-ribbon-field></div>
</div>
<div class="relative z-10 flex min-h-screen">
    {{-- Brand panel (transparent: the fixed Ribbon Field layer shows through).
         Header, main copy and footer share one left axis (the panel padding). --}}
    <div class="relative hidden w-1/2 flex-col overflow-hidden border-r border-hairline p-8 [@media(min-height:760px)]:p-10 lg:sticky lg:top-0 lg:flex lg:h-screen [@media(min-height:900px)]:p-12">
        {{-- Static pools of white light over a veil that calms the Ribbon
             Field behind the brand copy (keeps small text at 4.5:1). --}}
        <div class="brand-scrim pointer-events-none absolute inset-0"></div>

        {{-- Main copy + transparent logo lockup aligned to one left axis. --}}
        <div class="relative flex min-h-0 flex-1 items-center py-8 [@media(min-height:900px)]:py-10">
            <div class="w-full">
                <div class="mb-6 flex items-center gap-4 [@media(min-height:900px)]:mb-7 [@media(min-height:900px)]:gap-5">
                    <img src="{{ $logoUrl }}"
                         alt="ClinicSync"
                         width="597" height="497"
                         class="h-14 w-auto object-contain [@media(min-height:900px)]:h-16"
                         fetchpriority="high" decoding="async">
                    <span class="h-11 w-px shrink-0 bg-hairline-2 [@media(min-height:900px)]:h-12" aria-hidden="true"></span>
                    <img src="{{ $sealUrl }}"
                         alt="Camiguin Polytechnic State College seal"
                         width="474" height="474"
                         class="h-14 w-14 shrink-0 object-contain [@media(min-height:900px)]:h-16 [@media(min-height:900px)]:w-16"
                         decoding="async">
                </div>

                <p class="font-guest-mono text-[11px] uppercase tracking-[0.18em] text-live">
                    Outpatient Clinic Management
                </p>

                <p class="mt-5 max-w-[19ch] text-[2.75rem] leading-[1.04] tracking-[-0.03em] [@media(min-height:760px)]:text-[3.25rem] [@media(min-height:900px)]:text-[3.75rem]">
                    <span class="font-semibold text-bright">Campus healthcare,</span>
                    <span class="text-live">thoughtfully organized.</span>
                </p>

                <p class="mt-5 max-w-lg text-sm leading-relaxed text-read [@media(min-height:900px)]:text-[15px]">
                    Book consultations, follow your queue in real time, and keep every medical record
                    in one secure place — built for the Camiguin Polytechnic State College clinic.
                </p>

                <ul class="mt-7 max-w-lg space-y-3 text-sm [@media(min-height:900px)]:mt-8">
                    <li class="flex items-start gap-3.5 rounded-xl border border-teal-200/70 bg-white/85 px-4 py-3.5 shadow-[0_2px_10px_-4px_rgba(15,23,42,0.06)] backdrop-blur-sm">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-teal-200 bg-teal-50 text-live">
                            <x-icon name="check" class="h-3.5 w-3.5" />
                        </span>
                        <span class="text-read">Book appointments online and receive an instant queue number.</span>
                    </li>
                    <li class="flex items-start gap-3.5 rounded-xl border border-teal-200/70 bg-white/85 px-4 py-3.5 shadow-[0_2px_10px_-4px_rgba(15,23,42,0.06)] backdrop-blur-sm">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-teal-200 bg-teal-50 text-live">
                            <x-icon name="check" class="h-3.5 w-3.5" />
                        </span>
                        <span class="text-read">Physicians record encounters, vitals and prescriptions as they happen.</span>
                    </li>
                    <li class="flex items-start gap-3.5 rounded-xl border border-teal-200/70 bg-white/85 px-4 py-3.5 shadow-[0_2px_10px_-4px_rgba(15,23,42,0.06)] backdrop-blur-sm">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-teal-200 bg-teal-50 text-live">
                            <x-icon name="check" class="h-3.5 w-3.5" />
                        </span>
                        <span class="text-read">Patients follow their care journey with a complete medical history.</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="relative flex flex-wrap items-center justify-between gap-3 border-t border-teal-200/60 pt-4 [@media(min-height:900px)]:pt-5">
            <p class="text-xs leading-relaxed">
                <span class="block font-medium text-dim">Camiguin Polytechnic State College</span>
                <span class="text-meta">Outpatient Clinic · © {{ now()->year }}</span>
            </p>
            <span class="inline-flex items-center gap-1.5 rounded-full border border-teal-200/80 bg-white/90 px-3 py-1 text-[11px] font-medium text-live shadow-xs">
                <span class="h-1.5 w-1.5 rounded-full bg-teal-500" aria-hidden="true"></span>
                Online Booking Open
            </span>
        </div>
    </div>

    {{-- Form panel --}}
    <div class="relative flex flex-1 items-center justify-center p-4 sm:p-8 lg:p-12">
        {{-- Mobile only: soften the texture behind the logo block so both marks
             and caption stay crisp above the card. --}}
        <div class="pointer-events-none absolute inset-x-0 top-0 h-64 bg-[linear-gradient(to_bottom,var(--dp-color-ground)_0%,var(--dp-color-ground)_70%,transparent_100%)] lg:hidden"></div>

        <div class="relative z-10 w-full max-w-md">
            <div class="mb-6 flex flex-col items-center text-center lg:hidden">
                <div class="flex items-center justify-center gap-4">
                    <img src="{{ $logoUrl }}"
                         alt="ClinicSync"
                         width="597" height="497"
                         class="h-14 w-auto object-contain"
                         fetchpriority="high" decoding="async">
                    <span class="h-11 w-px shrink-0 bg-hairline-2" aria-hidden="true"></span>
                    <img src="{{ $sealUrl }}"
                         alt="Camiguin Polytechnic State College seal"
                         width="474" height="474"
                         class="h-14 w-14 shrink-0 object-contain"
                         decoding="async">
                </div>
                <p class="mt-3 font-guest-mono text-[11px] uppercase tracking-[0.14em] text-live">
                    Camiguin Polytechnic State College · Outpatient Clinic
                </p>
            </div>

            {{-- Clean solid card lifted softly off the background for crisp legibility. --}}
            <div class="rounded-panel border border-hairline border-t-2 border-t-teal-600 bg-panel p-6 shadow-[0_24px_60px_-20px_rgba(15,23,42,0.12)] sm:p-8">
                @include('partials.flash')
                @yield('content')
            </div>
        </div>
    </div>
</div>
</body>
</html>
