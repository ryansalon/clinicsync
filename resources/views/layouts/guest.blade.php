<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ClinicSync') · ClinicSync</title>
    {{ Vite::fonts(['geist', 'geist-mono']) }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-shell="guest" class="guest-dark min-h-screen bg-ground font-guest text-read antialiased">
    {{-- Apply the stored theme before first paint so light-mode users
         never see a dark flash (default stays dark). --}}
    <script>
        try {
            if (localStorage.getItem('clinicsync-theme') === 'light') {
                document.body.classList.remove('guest-dark');
                document.body.classList.add('guest-light');
            }
        } catch (error) {
            /* Storage unavailable — keep the default dark theme. */
        }
    </script>

@php($logoUrl = \Illuminate\Support\Facades\Storage::url('photos/logocsasd.png'))
@php($sealUrl = \Illuminate\Support\Facades\Storage::url('photos/cpsc logo2.png'))
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

        {{-- Brand header: top-left, slightly reduced so the type leads. --}}
        <div class="relative flex items-center gap-4 [@media(min-height:900px)]:gap-5">
            <img src="{{ $logoUrl }}"
                 alt="ClinicSync"
                 width="1376" height="768"
                 class="h-16 w-auto translate-y-[2.3%] object-contain [@media(min-height:760px)]:h-[4.5rem] [@media(min-height:900px)]:h-20"
                 fetchpriority="high" decoding="async">
            <img src="{{ $sealUrl }}"
                 alt="Camiguin Polytechnic State College seal"
                 width="474" height="474"
                 class="h-10 w-auto object-contain [@media(min-height:760px)]:h-11 [@media(min-height:900px)]:h-12"
                 decoding="async">
        </div>

        {{-- Main copy: optically centered between header and footer. --}}
        <div class="relative flex min-h-0 flex-1 items-center py-10">
            <div class="w-full">
                <p class="font-guest-mono text-[11px] uppercase tracking-[0.18em] text-live">
                    Outpatient Clinic Management
                </p>

                <p class="mt-5 max-w-[19ch] text-[2.75rem] leading-[1.04] tracking-[-0.03em] [@media(min-height:760px)]:text-[3.25rem] [@media(min-height:900px)]:text-[3.75rem]">
                    <span class="text-bright">Campus healthcare,</span>
                    <span class="text-read">thoughtfully organized.</span>
                </p>

                <p class="mt-5 max-w-lg text-sm leading-relaxed text-bright/80 [@media(min-height:900px)]:text-[15px]">
                    Book consultations, follow your queue in real time, and keep every medical record
                    in one secure place — built for the Camiguin Polytechnic State College clinic.
                </p>

                <ul class="mt-7 max-w-lg text-sm [@media(min-height:900px)]:mt-8">
                    <li class="flex items-start gap-4 border-t border-hairline py-4">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-hairline-2 bg-panel text-live">
                            <x-icon name="check" class="h-3 w-3" />
                        </span>
                        <span class="text-bright/80">Book appointments online and receive an instant queue number.</span>
                    </li>
                    <li class="flex items-start gap-4 border-t border-hairline py-4">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-hairline-2 bg-panel text-live">
                            <x-icon name="check" class="h-3 w-3" />
                        </span>
                        <span class="text-bright/80">Physicians record encounters, vitals and prescriptions as they happen.</span>
                    </li>
                    <li class="flex items-start gap-4 border-t border-hairline py-4">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-hairline-2 bg-panel text-live">
                            <x-icon name="check" class="h-3 w-3" />
                        </span>
                        <span class="text-bright/80">Patients follow their care journey with a complete medical history.</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="relative border-t border-hairline pt-4 [@media(min-height:900px)]:pt-5">
            <p class="text-xs leading-relaxed">
                <span class="block font-medium text-dim">Camiguin Polytechnic State College</span>
                <span class="text-meta">Outpatient Clinic · © {{ now()->year }}</span>
            </p>
        </div>
    </div>

    {{-- Form panel --}}
    <div class="relative flex flex-1 items-center justify-center p-4 sm:p-8 lg:p-12">
        {{-- Theme: light / dark (choice stored in localStorage) --}}
        <button type="button" data-theme-toggle
                aria-label="Switch to light mode" title="Switch to light mode"
                class="absolute top-4 right-4 z-10 rounded-lg p-2 text-meta transition hover:text-bright focus:outline-none focus-visible:ring-2 focus-visible:ring-live sm:top-6 sm:right-6">
            <x-icon name="sun" class="theme-icon theme-icon-sun" />
            <x-icon name="moon" class="theme-icon theme-icon-moon" />
        </button>

        {{-- Mobile only: the brand panel (and its veil) is hidden here, so
             ground out the texture behind the logo block — the gold kicker
             needs a solid ground through its band, fading out as the card
             begins so the dots still read below it. --}}
        <div class="pointer-events-none absolute inset-x-0 top-0 h-80 bg-[linear-gradient(to_bottom,var(--dp-color-ground)_0%,var(--dp-color-ground)_70%,transparent_100%)] lg:hidden"></div>

        <div class="relative z-10 w-full max-w-md">
            <div class="mb-8 flex flex-col items-center text-center lg:hidden">
                <img src="{{ $logoUrl }}"
                     alt="ClinicSync"
                     width="1376" height="768"
                     class="h-24 w-auto object-contain"
                     fetchpriority="high" decoding="async">
                <p class="mt-4 font-guest-mono text-[11px] uppercase tracking-[0.14em] text-live">
                    Camiguin Polytechnic State College · Outpatient Clinic
                </p>
            </div>

            {{-- Glass card: hairline border, translucent panel fill with
                 backdrop blur, and a soft large shadow so it lifts off the
                 Ribbon Field texture without a hard edge. --}}
            <div class="rounded-panel border border-hairline bg-panel/70 p-6 shadow-[0_32px_80px_-24px_rgba(0,0,0,0.55)] backdrop-blur-xl sm:p-8">
                @include('partials.flash')
                @yield('content')
            </div>
        </div>
    </div>
</div>
</body>
</html>
