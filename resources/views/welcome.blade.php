<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>ClinicSync</title>
    {{ Vite::fonts(['public-sans', 'jetbrains-mono']) }}
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-canvas p-4 text-read antialiased">
    <main class="w-full max-w-md rounded-lg border border-hairline bg-surface p-8 text-center">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg border border-brand-border bg-brand-tint text-brand">
            <x-icon name="heart-pulse" class="h-7 w-7" />
        </span>

        <h1 class="mt-5 text-2xl font-bold tracking-tight text-bright">ClinicSync</h1>
        <p class="mt-2 text-sm text-meta">
            Your account does not have an assigned role yet. Please contact the clinic administrator
            at CPSC Outpatient Clinic to gain access to the system.
        </p>

        <div class="mt-8 flex justify-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    <x-icon name="logout" class="h-4 w-4" />
                    Sign out
                </button>
            </form>
        </div>
    </main>
</body>
</html>
