@props(['name', 'stroke' => '1.75'])

{{-- Hand-drawn24x24 outline icon set (heroicon-style). Sizing is set by the `.icon`
     class (h-5 w-5) and can be overridden with utilities, e.g. <x-icon class="h-4 w-4" /> --}}
<svg {{ $attributes->merge(['class' => 'icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor"
     stroke-width="{{ $stroke }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('home')
            <path d="M3 10.75 12 3.25l9 7.5" />
            <path d="M5.25 9.5v10.25c0 .69.56 1.25 1.25 1.25h3v-6h5v6h3c.69 0 1.25-.69 1.25-1.25V9.5" />
            @break

        @case('calendar')
            <rect x="3.25" y="5.25" width="17.5" height="15.5" rx="2.25" />
            <path d="M8 3v4M16 3v4M3.5 10h17" />
            @break

        @case('clock')
            <path d="M12 6v6h4.5m3.75 0a8.25 8.25 0 1 1-16.5 0 8.25 8.25 0 0 1 16.5 0z" />
            @break

        @case('users')
            <circle cx="9" cy="7.5" r="3.5" />
            <circle cx="16" cy="8" r="3.5" />
            <path d="M2.75 20.25A6.75 6.75 0 0 1 9.5 13.5h.5a6.75 6.75 0 0 1 6.25 6.5M18.25 13.9a6.25 6.25 0 0 1 3.5 5.1" />
            @break

        @case('user')
            <circle cx="12" cy="8" r="3.75" />
            <path d="M4.75 20.25A7.5 7.5 0 0 1 12 12.75a7.5 7.5 0 0 1 7.25 7.5" />
            @break

        @case('user-plus')
            <circle cx="10" cy="8" r="3.5" />
            <path d="M3.5 20.25A6.75 6.75 0 0 1 10.25 13.5h.5a6.75 6.75 0 0 1 6.25 6.75M18.5 8.5v6M21.5 11.5h-6" />
            @break

        @case('clipboard')
            <path d="M9 4.75V4.5A1.75 1.75 0 0 1 10.75 2.75h2.5A1.75 1.75 0 0 1 15 4.5v.25" />
            <path d="M8.25 5.25h-1.5A1.75 1.75 0 0 0 5 7v13.25A1.75 1.75 0 0 0 6.75 22h10.5a1.75 1.75 0 0 0 1.75-1.75V7a1.75 1.75 0 0 0-1.75-1.75h-1.5" />
            <path d="M8.5 11.5h7M8.5 15.5h7" />
            @break

        @case('clipboard-check')
            <path d="M9 4.75V4.5A1.75 1.75 0 0 1 10.75 2.75h2.5A1.75 1.75 0 0 1 15 4.5v.25" />
            <path d="M8.25 5.25h-1.5A1.75 1.75 0 0 0 5 7v13.25A1.75 1.75 0 0 0 6.75 22h10.5a1.75 1.75 0 0 0 1.75-1.75V7a1.75 1.75 0 0 0-1.75-1.75h-1.5" />
            <path d="M9 13.75l2.25 2.25 4.25-4.75" />
            @break

        @case('chart-bar')
            <path d="M3.75 20.25V11M9.25 20.25V4.5M14.75 20.25v-6.5M20.25 20.25V8.5M2.5 20.5h19" />
            @break

        @case('shield-check')
            <path d="M12 3 4.5 5.5v6.25c0 4.32.9 8.3 7.25 9.42 4.35-1.12 7.25-5.12 7.25-9.42V5.5L12 2.75z" />
            <path d="M9.25 11.75l2 2 3.5-3.75" />
            @break

        @case('list')
            <path d="M8.5 6h12M8.5 12h12M8.5 18h12M4.25 6h.005M4.25 12h.005M4.25 18h.005" />
            @break

        @case('document')
            <path d="M14 2.75H7.25A2.25 2.25 0 0 0 5 5v14a2.25 2.25 0 0 0 2.25 2.25h9.5A2.25 2.25 0 0 0 19 19V7.75L14 2.75z" />
            <path d="M14 2.75V7.5a.25.25 0 0 0 .25 .25h4.5M8.5 12.5h7M8.5 16.5h7" />
            @break

        @case('heart-pulse')
            <path d="M12 20.5S3.5 15.3 3.5 9.36A4.86 4.86 0 0 1 12 6.5a4.86 4.86 0 0 1 8.5 2.86c0 5.94-8.5 11.14-8.5 11.14z" />
            <path d="M3.75 11.25h3.5l1.25-2 1.5 4.5 1.5-3 1.25 1h3.5" />
            @break

        @case('magnifying-glass')
            <circle cx="10.75" cy="10.75" r="6.75" />
            <path d="M15.75 15.75 20.5 20.5" />
            @break

        @case('plus')
            <path d="M12 5v14M5 12h14" />
            @break

        @case('x')
            <path d="M6 6l12 12M18 6 6 18" />
            @break

        @case('check')
            <path d="M4.75 12.75l4.5 4.5 10-10.5" />
            @break

        @case('check-circle')
            <circle cx="12" cy="12" r="9" />
            <path d="M8.25 12.25l2.5 2.5 5-5.5" />
            @break

        @case('exclamation-triangle')
            <path d="M10.94 3.75h2.12l8.65 15.37a1.9 1.9 0 0 1-1.66 2.88H4.05a1.9 1.9 0 0 1-1.66-2.88L10.94 3.75z" />
            <path d="M12 9.25v4.5M12 16.75h.005" />
            @break

        @case('information-circle')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 11.25v5M12 7.75h.005" />
            @break

        @case('chevron-down')
            <path d="M6 9.5l6 6 6-6" />
            @break

        @case('chevron-right')
            <path d="M9.5 6l6 6-6 6" />
            @break

        @case('chevron-left')
            <path d="M14.5 6l-6 6 6 6" />
            @break

        @case('bars-3')
            <path d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
            @break

        @case('ellipsis-horizontal')
            <circle cx="5" cy="12" r="1.35" fill="currentColor" stroke="none" />
            <circle cx="12" cy="12" r="1.35" fill="currentColor" stroke="none" />
            <circle cx="19" cy="12" r="1.35" fill="currentColor" stroke="none" />
            @break

        @case('logout')
            <path d="M9.75 3.75h6.75a1.75 1.75 0 0 1 1.75 1.75v13a1.75 1.75 0 0 1-1.75 1.75H9.75M14.25 12H3.75m0 0 3-3m-3 3 3 3" />
            @break

        @case('arrow-right')
            <path d="M4.5 12h15m0 0-5.25-5.25M19.5 12l-5.25 5.25" />
            @break

        @case('arrow-left')
            <path d="M19.5 12h-15m0 0 5.25-5.25M4.5 12l5.25 5.25" />
            @break

        @case('pencil')
            <path d="M4.5 19.5l.9-3.6L16.15.2a1.87 1.87 0 0 1 2.64 0l.06.06a1.87 1.87 0 0 1 0 2.64L8.1 18.6l-3.6.9z" />
            @break

        @case('eye')
            <path d="M2.5 12S6 5.75 12 5.75 21.5 12 21.5 12 18 18.25 12 18.25 2.5 12 2.5 12z" />
            <circle cx="12" cy="12" r="3" />
            @break

        @case('phone')
            <path d="M3.5 5.75A1.75 1.75 0 0 1 5.25 4h2.1c.72 0 1.35.5 1.5 1.2l.66 3.1a1.5 1.5 0 0 1-.7 1.6l-1.3.8a11.5 11.5 0 0 0 5.4 5.4l.8-1.3a1.5 1.5 0 0 1 1.6-.7l3.1.66c.7.15 1.2.78 1.2 1.5v2.1a1.75 1.75 0 0 1-1.75 1.75A14.25 14.25 0 0 1 3.5 5.75z" />
            @break

        @case('envelope')
            <rect x="2.75" y="5" width="18.5" height="14" rx="2.25" />
            <path d="M3.5 7.5l7.42 5.19a1.75 1.75 0 0 0 2.16 0L20.5 7.5" />
            @break

        @case('bell')
            <path d="M9 9.75a3 3 0 0 1 6 0c0 4.5 1.75 6 2.25 6.75H6.75C7.25 15.75 9 14.25 9 9.75z" />
            <path d="M10.25 19.25a2.14 2.14 0 0 0 3.5 0" />
            @break

        @case('trash')
            <path d="M4.5 6.75h15M9.75 6.75V5.5A1.75 1.75 0 0 1 11.5 3.75h1a1.75 1.75 0 0 1 1.75 1.75v1.25" />
            <path d="M6.5 6.75l.75 13.13A1.75 1.75 0 0 0 9 21.5h6a1.75 1.75 0 0 0 1.75-1.62l.75-13.13M10.25 10.5v6M13.75 10.5v6" />
            @break

        @case('refresh')
            <path d="M20.25 12a8.25 8.25 0 1 1-2.4-5.83M20.25 3.75v4.5h-4.5" />
            @break

        @case('funnel')
            <path d="M3.75 4.5h16.5l-6.5 7.75v6.5l-3.5 2v-8.5L3.75 4.5z" />
            @break

        @case('sparkles')
            <path d="M12 3.5l1.9 4.85 4.85 1.9-4.85 1.9L12 17l-1.9-4.85L5.25 10.25l4.85-1.9L12 3.5z" />
            <path d="M19 15.5l.85 2.15L22 18.5l-2.15.85L19 21.5l-.85-2.15L16 18.5l2.15-.85L19 15.5z" />
            @break

        @case('building')
            <rect x="4.75" y="3.25" width="14.5" height="17.5" rx="1.75" />
            <path d="M9.5 7.25h.005M14.5 7.25h.005M9.5 11.25h.005M14.5 11.25h.005M10.5 20.75v-4.5h3v4.5" />
            @break

        @case('lock')
            <rect x="4.75" y="10.25" width="14.5" height="10" rx="2" />
            <path d="M8 10.25V7a4 4 0 1 1 8 0v3.25" />
            @break

        @case('cog')
            <circle cx="12" cy="12" r="3" />
            <path d="M10.5 3.75h3l.5 2.1a6.4 6.4 0 0 1 1.65.95l2.05-.6 1.5 2.6-1.55 1.45a6.6 6.6 0 0 1 0 1.9l1.55 1.45-1.5 2.6-2.05-.6a6.4 6.4 0 0 1-1.65.95l-.5 2.1h-3l-.5-2.1a6.4 6.4 0 0 1-1.65-.95l-2.05.6-1.5-2.6 1.55-1.45a6.6 6.6 0 0 1 0-1.9L4.8 8.8l1.5-2.6 2.05.6a6.4 6.4 0 0 1 1.65-.95l.5-2.1z" />
            @break

        @case('map-pin')
            <path d="M12 21.25s6-5.636.6-11.09A6.56 6.56 0 0 0 5.4 9.91c0 5.454 6.6 11.34 6.6 11.34z" />
            <circle cx="12" cy="10" r="2.5" />
            @break

        @default
            <circle cx="12" cy="12" r="9" />
    @endswitch
</svg>
