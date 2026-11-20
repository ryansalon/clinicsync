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
<body class="min-h-screen overflow-x-hidden bg-canvas text-read antialiased">

    @php
        /** Shell data ($currentUser, $currentUserPhoto, $currentRoleLabel, $recentNotifications,
            $unreadNotificationCount) is supplied by the layouts.app view composer. */

        /** Highlight a sidebar link when the current route matches any of its patterns. */
        $navActive = fn (string ...$patterns): string => collect($patterns)
            ->contains(fn (string $pattern) => request()->routeIs($pattern))
            ? 'nav-link-active'
            : '';
    @endphp

    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-md focus:bg-brand focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
        Skip to content
    </a>

    {{-- Mobile backdrop --}}
    <div class="fixed inset-0 z-40 hidden bg-bright/45 backdrop-blur-xs md:hidden"
         data-sidebar-backdrop data-sidebar-close></div>

    <div id="app-shell" class="flex h-dvh overflow-hidden">
        {{-- Warm Travertine Navigation Rail (#ECE8DF) --}}
        <aside id="app-sidebar"
               class="fixed inset-y-0 left-0 z-50 hidden w-72 flex-col border-r border-hairline bg-rail text-bright md:static md:flex md:w-[248px] md:shrink-0">
            {{-- Co-branded clinic header mark --}}
            <div class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-hairline px-4">
                <div class="flex min-w-0 items-center gap-2.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center">
                        <img src="{{ asset('images/cpsc-logo2.png') }}"
                             alt="CPSC"
                             width="474" height="474"
                             class="h-full w-full object-contain"
                             decoding="async">
                    </span>
                    <div class="min-w-0 leading-tight">
                        <p class="truncate text-sm font-bold tracking-tight text-bright">ClinicSync</p>
                        <p class="truncate font-mono text-[10px] tracking-wider text-meta uppercase">CPSC Clinic</p>
                    </div>
                </div>
                <button type="button"
                        class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md text-read transition hover:bg-rail-hover hover:text-bright focus:outline-none focus-visible:ring-2 focus-visible:ring-brand md:hidden"
                        data-sidebar-close aria-label="Close navigation menu">
                    <x-icon name="x" />
                </button>
            </div>

            {{-- Primary Navigation --}}
            <nav aria-label="Primary navigation" class="flex-1 space-y-1 overflow-y-auto px-3 py-4 text-sm">
                @role('patient')
                    <p class="nav-group-label">Patient portal</p>
                    <a href="{{ route('patient.dashboard') }}"
                       class="nav-link {{ $navActive('patient.dashboard') }}">
                        <x-icon name="home" /> <span>Dashboard</span>
                    </a>
                    <a href="{{ route('patient.appointments.create') }}"
                       class="nav-link {{ $navActive('patient.appointments.create') }}">
                        <x-icon name="calendar" /> <span>Book an appointment</span>
                    </a>
                    <a href="{{ route('patient.appointments.index') }}"
                       class="nav-link {{ $navActive('patient.appointments.index', 'patient.appointments.show', 'patient.appointments.reschedule') }}">
                        <x-icon name="clock" /> <span>My appointments</span>
                    </a>
                    <a href="{{ route('patient.records') }}"
                       class="nav-link {{ $navActive('patient.records') }}">
                        <x-icon name="document" /> <span>My medical records</span>
                    </a>
                @endrole

                @role('physician')
                    <p class="nav-group-label">Consultation</p>
                    <a href="{{ route('physician.dashboard') }}"
                       class="nav-link {{ $navActive('physician.dashboard', 'physician.consultation') }}">
                        <x-icon name="list" /> <span>Today's queue</span>
                    </a>
                    <a href="{{ route('physician.encounters.index') }}"
                       class="nav-link {{ $navActive('physician.encounters.*', 'physician.patients.*') }}">
                        <x-icon name="clipboard" /> <span>Encounter log</span>
                    </a>
                    <a href="{{ route('physician.availability.index') }}"
                       class="nav-link {{ $navActive('physician.availability.*') }}">
                        <x-icon name="calendar" /> <span>My availability</span>
                    </a>
                @endrole

                @hasanyrole('administrator|receptionist')
                    <p class="nav-group-label">Front desk</p>
                    <a href="{{ route('admin.queue.index') }}"
                       class="nav-link {{ $navActive('admin.queue.index', 'admin.queue.store', 'admin.queue.update', 'admin.queue.call', 'admin.queue.destroy', 'admin.queue.patient-search') }}">
                        <x-icon name="list" /> <span>Daily queue</span>
                    </a>
                    <a href="{{ route('admin.schedules.index') }}"
                       class="nav-link {{ $navActive('admin.schedules.*') }}">
                        <x-icon name="calendar" /> <span>Physician schedules</span>
                    </a>
                    <a href="{{ route('admin.reports.index') }}"
                       class="nav-link {{ $navActive('admin.reports.*') }}">
                        <x-icon name="chart-bar" /> <span>Daily census</span>
                    </a>
                @endhasanyrole

                @role('administrator')
                    <p class="nav-group-label">Administration</p>
                    <a href="{{ route('admin.dashboard') }}"
                       class="nav-link {{ $navActive('admin.dashboard') }}">
                        <x-icon name="home" /> <span>System overview</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}"
                       class="nav-link {{ $navActive('admin.users.*') }}">
                        <x-icon name="users" /> <span>User accounts</span>
                    </a>
                    <a href="{{ route('admin.audit-logs.index') }}"
                       class="nav-link {{ $navActive('admin.audit-logs.*') }}">
                        <x-icon name="shield-check" /> <span>Audit log</span>
                    </a>
                @endrole
            </nav>

            {{-- Compact clinic operating hours inside rail footer --}}
            <div class="mx-3 mb-2 rounded-md border border-hairline bg-canvas/85 px-3 py-2 text-[11px] text-meta">
                <p class="font-mono text-[10px] font-semibold tracking-wider text-brand uppercase">Clinic Desk</p>
                <p class="mt-0.5 cs-num font-mono text-[11px] text-read">Mon–Sat · 08:00–17:00</p>
            </div>

            {{-- Signed-in user --}}
            <div class="shrink-0 border-t border-hairline p-3">
                <div class="flex items-center gap-1 rounded-lg border border-hairline bg-surface p-1">
                    <a href="{{ route('profile.edit') }}" title="Profile settings"
                       class="group flex min-h-11 min-w-0 flex-1 items-center gap-2.5 rounded-md p-1.5 transition hover:bg-surface-subtle focus:outline-none focus-visible:ring-2 focus-visible:ring-brand">
                        <x-avatar :name="$currentUser->name" :photo="$currentUserPhoto" />
                        <div class="min-w-0 flex-1 leading-tight">
                            <p class="truncate text-sm font-semibold text-bright">{{ $currentUser->name }}</p>
                            <p class="truncate text-xs text-meta">{{ $currentRoleLabel }}</p>
                        </div>
                        <x-icon name="cog" class="h-4 w-4 shrink-0 text-meta transition group-hover:text-brand" />
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Sign out" aria-label="Sign out"
                                class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md text-meta transition hover:bg-danger-tint hover:text-danger focus:outline-none focus-visible:ring-2 focus-visible:ring-brand md:min-h-9 md:min-w-9">
                            <x-icon name="logout" class="h-[18px] w-[18px]" />
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            {{-- Crisp daylight utility top bar --}}
            <header class="z-30 flex h-14 shrink-0 items-center gap-3 border-b border-hairline bg-surface px-4 sm:px-6">
                <button type="button"
                        class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md text-read transition hover:bg-surface-subtle focus:outline-none focus-visible:ring-2 focus-visible:ring-brand md:hidden"
                        data-sidebar-open aria-controls="app-sidebar" aria-expanded="false" aria-label="Open navigation menu">
                    <x-icon name="bars-3" />
                </button>

                <div class="flex min-w-0 flex-1 items-center gap-2 text-xs">
                    <span class="truncate font-semibold text-bright md:hidden">
                        @yield('title', 'ClinicSync')
                    </span>
                    <div class="hidden min-w-0 items-center gap-2 md:inline-flex">
                        <span class="text-meta">CPSC Outpatient Clinic</span>
                        <span class="text-hairline-2" aria-hidden="true">/</span>
                        <span class="truncate font-semibold text-bright">@yield('title', 'Workspace')</span>
                    </div>
                </div>

                {{-- Notifications --}}
                <div class="relative" data-dropdown>
                    <button type="button" data-dropdown-trigger aria-label="Notifications"
                            aria-haspopup="true" aria-expanded="false" aria-controls="notification-menu"
                            class="relative inline-flex min-h-11 min-w-11 items-center justify-center rounded-md text-read transition hover:bg-surface-subtle hover:text-bright focus:outline-none focus-visible:ring-2 focus-visible:ring-brand md:min-h-9 md:min-w-9">
                        <x-icon name="bell" />
                        @if ($unreadNotificationCount > 0)
                            <span aria-hidden="true"
                                  class="cs-num absolute top-0.5 right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-danger-solid px-1 font-mono text-[10px] leading-none font-bold text-white ring-2 ring-surface md:-top-0.5 md:-right-0.5">
                                {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                            </span>
                            <span class="sr-only">{{ $unreadNotificationCount }} unread</span>
                        @endif
                    </button>

                    <div id="notification-menu" data-dropdown-menu
                         class="dropdown-panel right-0 hidden w-[min(22rem,calc(100vw-2rem))] p-0">
                        <div class="flex items-center justify-between gap-2 border-b border-hairline px-4 py-3">
                            <p class="text-sm font-semibold text-bright">Notifications</p>
                            @if ($unreadNotificationCount > 0)
                                <form method="POST" action="{{ route('notifications.read-all') }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex min-h-8 items-center rounded-md px-2 text-xs font-semibold text-brand transition hover:bg-brand-tint focus:outline-none focus-visible:ring-2 focus-visible:ring-brand">
                                        Mark all read
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="max-h-96 overflow-y-auto p-1.5">
                            @forelse ($recentNotifications as $notification)
                                @php
                                    $isUnread = $notification->read_at === null;
                                    $notificationType = $notification->data['type'] ?? '';
                                    $notificationIcon = match ($notificationType) {
                                        'appointment_status' => 'calendar',
                                        'appointment_reminder' => 'clock',
                                        default => 'bell',
                                    };
                                    $notificationTitle = match ($notificationType) {
                                        'appointment_status' => 'Appointment update',
                                        'appointment_reminder' => 'Appointment reminder',
                                        default => 'Notification',
                                    };
                                    $appointmentId = $notification->data['appointment_id'] ?? null;
                                    $notificationUrl = null;
                                    if ($appointmentId && auth()->user()->hasRole('patient')) {
                                        $notificationUrl = route('patient.appointments.show', $appointmentId);
                                    } elseif (auth()->user()->hasAnyRole('administrator|receptionist')) {
                                        $notificationUrl = route('admin.queue.index');
                                    } elseif (auth()->user()->hasRole('physician')) {
                                        $notificationUrl = route('physician.dashboard');
                                    }
                                @endphp
                                <div class="flex items-start gap-3 rounded-md px-3 py-2.5 transition hover:bg-canvas {{ $isUnread ? 'bg-brand-tint/60' : '' }}">
                                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $isUnread ? 'bg-brand text-white' : 'bg-surface-subtle text-meta' }}">
                                        <x-icon :name="$notificationIcon" class="h-4 w-4" />
                                    </span>
                                    @if ($notificationUrl)
                                        <a href="{{ $notificationUrl }}" class="group min-w-0 flex-1 focus:outline-none focus-visible:underline">
                                            <p class="font-mono text-[10px] font-semibold tracking-wider text-meta uppercase transition group-hover:text-brand">{{ $notificationTitle }}</p>
                                            <p class="mt-0.5 text-sm break-words text-read transition group-hover:text-bright">
                                                {{ $notification->data['message'] ?? 'Notification' }}
                                            </p>
                                            <p class="mt-1 text-[11px] text-meta">{{ $notification->created_at->diffForHumans() }}</p>
                                        </a>
                                    @else
                                        <div class="min-w-0 flex-1">
                                            <p class="font-mono text-[10px] font-semibold tracking-wider text-meta uppercase">{{ $notificationTitle }}</p>
                                            <p class="mt-0.5 text-sm break-words text-read">
                                                {{ $notification->data['message'] ?? 'Notification' }}
                                            </p>
                                            <p class="mt-1 text-[11px] text-meta">{{ $notification->created_at->diffForHumans() }}</p>
                                        </div>
                                    @endif
                                    @if ($isUnread)
                                        <form method="POST" action="{{ route('notifications.read', $notification) }}" class="shrink-0">
                                            @csrf
                                            <button type="submit" title="Mark as read" aria-label="Mark as read"
                                                    class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md text-meta transition hover:bg-surface-subtle hover:text-brand focus:outline-none focus-visible:ring-2 focus-visible:ring-brand md:min-h-8 md:min-w-8">
                                                <x-icon name="check" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <div class="px-4 py-8 text-center">
                                    <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-surface-subtle text-meta">
                                        <x-icon name="bell" class="h-5 w-5" />
                                    </span>
                                    <p class="mt-3 text-sm font-medium text-bright">You're all caught up</p>
                                    <p class="mt-0.5 text-xs text-meta">Appointment updates and reminders appear here.</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="flex items-center justify-between border-t border-hairline px-4 py-2.5 text-xs text-meta">
                            <span>
                                @if ($recentNotifications->count() >= 6)
                                    Showing 6 most recent
                                @elseif ($recentNotifications->count() > 0)
                                    {{ $recentNotifications->count() }} notification{{ $recentNotifications->count() === 1 ? '' : 's' }}
                                @endif
                            </span>
                            @role('patient')
                                <a href="{{ route('patient.appointments.index') }}" class="font-semibold text-brand transition hover:underline">
                                    View appointments &rarr;
                                </a>
                            @endrole
                            @role('physician')
                                <a href="{{ route('physician.dashboard') }}" class="font-semibold text-brand transition hover:underline">
                                    View queue &rarr;
                                </a>
                            @endrole
                            @hasanyrole('administrator|receptionist')
                                <a href="{{ route('admin.queue.index') }}" class="font-semibold text-brand transition hover:underline">
                                    Daily queue &rarr;
                                </a>
                            @endhasanyrole
                        </div>
                    </div>
                </div>

                <span class="chip hidden lg:inline-flex cs-num font-mono text-[11px]">
                    <x-icon name="calendar" class="h-3.5 w-3.5 text-meta" />
                    {{ now()->isoFormat('ddd, MMM D, YYYY') }}
                </span>
            </header>

            {{-- Page content --}}
            <main id="main-content" class="min-w-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-contain p-4 sm:p-6 lg:p-7">
                <div class="mx-auto w-full min-w-0 max-w-7xl">
                    @include('partials.flash')
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    {{-- Confirmation modal for destructive actions --}}
    <div id="confirm-modal" class="fixed inset-0 z-[70] hidden items-center justify-center p-4"
         role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title" aria-describedby="confirm-modal-message">
        <div class="absolute inset-0 bg-bright/45 backdrop-blur-xs" data-confirm-dismiss></div>
        <div class="relative w-full max-w-md rounded-lg border border-hairline bg-surface p-6 shadow-[0_16px_40px_-10px_rgba(24,32,29,0.22)]">
            <span class="flex h-11 w-11 items-center justify-center rounded-full border border-danger-border bg-danger-tint text-danger">
                <x-icon name="exclamation-triangle" />
            </span>
            <h2 id="confirm-modal-title" class="mt-4 text-base font-semibold text-bright">Are you sure?</h2>
            <p id="confirm-modal-message" class="mt-1 text-sm text-meta"></p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" class="btn btn-secondary" data-confirm-dismiss>Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-modal-go">Confirm</button>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
