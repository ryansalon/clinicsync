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
<body class="min-h-screen bg-[#f3f6f9] text-slate-800 antialiased">

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
       class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-teal-600 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
        Skip to content
    </a>

    {{-- Mobile backdrop --}}
    <div class="fixed inset-0 z-40 hidden bg-black/60 backdrop-blur-sm md:hidden"
         data-sidebar-backdrop data-sidebar-close></div>

    <div id="app-shell" class="flex h-dvh overflow-hidden">
        {{-- Sidebar --}}
        <aside id="app-sidebar"
               class="fixed inset-y-0 left-0 z-50 hidden w-72 flex-col border-r border-slate-200/85 bg-white md:static md:flex md:w-64 md:shrink-0">
            {{-- Brand --}}
            <div class="flex h-16 shrink-0 items-center gap-3 border-b border-slate-200/80 px-5">
                <img src="{{ asset('images/cpsc-logo2.png') }}"
                     alt="CPSC"
                     width="474" height="474"
                     class="h-9 w-9 shrink-0 object-contain"
                     decoding="async">
                <div class="min-w-0 leading-tight">
                    <p class="truncate text-sm font-bold tracking-tight text-slate-900">ClinicSync</p>
                    <p class="truncate text-[11px] font-medium text-slate-500">CPSC Outpatient Clinic</p>
                </div>
                <button type="button" class="ml-auto rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 md:hidden"
                        data-sidebar-close aria-label="Close menu">
                    <x-icon name="x" />
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4 text-sm">
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

            {{-- Signed-in user --}}
            <div class="shrink-0 border-t border-slate-200/80 p-3">
                <div class="flex items-center gap-1 rounded-xl border border-slate-200/80 bg-slate-50/85 p-1">
                    <a href="{{ route('profile.edit') }}" title="Profile settings"
                       class="group flex min-w-0 flex-1 items-center gap-2.5 rounded-lg p-1.5 transition hover:bg-white">
                        <x-avatar :name="$currentUser->name" :photo="$currentUserPhoto" />
                        <div class="min-w-0 flex-1 leading-tight">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $currentUser->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $currentRoleLabel }}</p>
                        </div>
                        <x-icon name="cog" class="h-4 w-4 shrink-0 text-slate-500 transition group-hover:text-teal-500" />
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Sign out" aria-label="Sign out"
                                class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-rose-600">
                            <x-icon name="logout" class="h-[18px] w-[18px]" />
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            {{-- Top bar --}}
            <header class="z-30 flex h-16 shrink-0 items-center gap-3 border-b border-slate-200 bg-white/85 px-4 backdrop-blur sm:px-6">
                <button type="button"
                        class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 md:hidden"
                        data-sidebar-open aria-controls="app-sidebar" aria-expanded="false" aria-label="Open menu">
                    <x-icon name="bars-3" />
                </button>

                <p class="min-w-0 flex-1 truncate text-base font-semibold tracking-tight text-slate-900">
                    @yield('title', 'ClinicSync')
                </p>

                {{-- Notifications --}}
                <div class="relative" data-dropdown>
                    <button type="button" data-dropdown-trigger aria-label="Notifications"
                            aria-haspopup="true" aria-expanded="false" aria-controls="notification-menu"
                            class="relative rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500/60">
                        <x-icon name="bell" />
                        @if ($unreadNotificationCount > 0)
                            <span aria-hidden="true"
                                  class="absolute -top-0.5 -right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] leading-none font-bold text-white ring-2 ring-white">
                                {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                            </span>
                            <span class="sr-only">{{ $unreadNotificationCount }} unread</span>
                        @endif
                    </button>

                    <div id="notification-menu" data-dropdown-menu
                         class="absolute right-0 z-50 mt-2 hidden w-80 origin-top-right rounded-xl border border-slate-200 bg-white shadow-lg ring-1 ring-slate-950/5">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900">Notifications</p>
                            @if ($unreadNotificationCount > 0)
                                <form method="POST" action="{{ route('notifications.read-all') }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-teal-600 transition hover:text-teal-700">
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
                                <div class="flex items-start gap-3 rounded-lg px-3 py-2.5 transition hover:bg-slate-50 {{ $isUnread ? 'bg-teal-50/70 hover:bg-teal-50' : '' }}">
                                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $isUnread ? 'bg-teal-100 text-teal-600' : 'bg-slate-100 text-slate-500' }}">
                                        <x-icon :name="$notificationIcon" class="h-4 w-4" />
                                    </span>
                                    @if ($notificationUrl)
                                        <a href="{{ $notificationUrl }}" class="group min-w-0 flex-1">
                                            <p class="text-[10px] font-semibold tracking-wider text-slate-500 uppercase transition group-hover:text-teal-600">{{ $notificationTitle }}</p>
                                            <p class="mt-0.5 text-sm break-words text-slate-700 transition group-hover:text-slate-900">
                                                {{ $notification->data['message'] ?? 'Notification' }}
                                            </p>
                                            <p class="mt-1 text-[11px] text-slate-500">{{ $notification->created_at->diffForHumans() }}</p>
                                        </a>
                                    @else
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[10px] font-semibold tracking-wider text-slate-500 uppercase">{{ $notificationTitle }}</p>
                                            <p class="mt-0.5 text-sm break-words text-slate-700">
                                                {{ $notification->data['message'] ?? 'Notification' }}
                                            </p>
                                            <p class="mt-1 text-[11px] text-slate-500">{{ $notification->created_at->diffForHumans() }}</p>
                                        </div>
                                    @endif
                                    @if ($isUnread)
                                        <form method="POST" action="{{ route('notifications.read', $notification) }}" class="shrink-0">
                                            @csrf
                                            <button type="submit" title="Mark as read" aria-label="Mark as read"
                                                    class="rounded-md p-1 text-slate-500 transition hover:bg-slate-100 hover:text-teal-600">
                                                <x-icon name="check" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <div class="px-4 py-8 text-center">
                                    <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <x-icon name="bell" class="h-5 w-5" />
                                    </span>
                                    <p class="mt-3 text-sm font-medium text-slate-900">You're all caught up</p>
                                    <p class="mt-0.5 text-xs text-slate-500">Appointment updates and reminders appear here.</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-100 px-4 py-2.5 text-xs text-slate-500">
                            <span>
                                @if ($recentNotifications->count() >= 6)
                                    Showing 6 most recent
                                @elseif ($recentNotifications->count() > 0)
                                    {{ $recentNotifications->count() }} notification{{ $recentNotifications->count() === 1 ? '' : 's' }}
                                @endif
                            </span>
                            @role('patient')
                                <a href="{{ route('patient.appointments.index') }}" class="font-semibold text-teal-600 transition hover:text-teal-700">
                                    View appointments &rarr;
                                </a>
                            @endrole
                            @role('physician')
                                <a href="{{ route('physician.dashboard') }}" class="font-semibold text-teal-600 transition hover:text-teal-700">
                                    View queue &rarr;
                                </a>
                            @endrole
                            @hasanyrole('administrator|receptionist')
                                <a href="{{ route('admin.queue.index') }}" class="font-semibold text-teal-600 transition hover:text-teal-700">
                                    Daily queue &rarr;
                                </a>
                            @endhasanyrole
                        </div>
                    </div>
                </div>

                <span class="chip hidden sm:inline-flex border-teal-200/70 bg-teal-50/70 text-teal-800">
                    {{ $currentRoleLabel }}
                </span>

                <span class="chip hidden lg:inline-flex">
                    <x-icon name="calendar" class="h-4 w-4 text-slate-400" />
                    {{ now()->isoFormat('dddd, MMM D, YYYY') }}
                </span>
            </header>

            {{-- Page content --}}
            <main id="main-content" class="flex-1 overflow-y-auto overscroll-contain p-4 sm:p-6 lg:p-8">
                <div class="mx-auto w-full max-w-7xl">
                    @include('partials.flash')
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    {{-- Confirmation modal for destructive actions --}}
    <div id="confirm-modal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4"
         role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title" aria-describedby="confirm-modal-message">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" data-confirm-dismiss></div>
        <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-rose-50 text-rose-600">
                <x-icon name="exclamation-triangle" />
            </span>
            <h3 id="confirm-modal-title" class="mt-4 text-base font-semibold text-slate-900">Are you sure?</h3>
            <p id="confirm-modal-message" class="mt-1 text-sm text-slate-500"></p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" class="btn btn-secondary" data-confirm-dismiss>Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-modal-go">Confirm</button>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
