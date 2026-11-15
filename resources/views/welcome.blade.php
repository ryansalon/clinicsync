<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>ClinicSync</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 p-4 text-slate-800 antialiased">
    <div class="w-full max-w-md text-center">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-500 to-teal-700 text-white shadow-sm">
            <x-icon name="heart-pulse" class="h-7 w-7" />
        </span>

        <h1 class="mt-5 text-2xl font-semibold tracking-tight text-slate-900">ClinicSync</h1>
        <p class="mt-2 text-sm text-slate-500">
            Your account does not have an assigned role yet. Please contact the clinic administrator
            to gain access to the system.
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
    </div>
</body>
</html>
