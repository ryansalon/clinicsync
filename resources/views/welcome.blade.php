<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>ClinicSync</title>
    {{ Vite::fonts(['geist', 'geist-mono']) }}
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-[#F4F7F6] p-4 text-[#243330] antialiased">
    <main class="w-full max-w-md rounded-xl border border-[#DCE3E1] bg-white p-8 text-center shadow-xs">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-[#0F766E] text-white shadow-xs">
            <x-icon name="heart-pulse" class="h-7 w-7" />
        </span>

        <h1 class="mt-5 text-2xl font-semibold tracking-tight text-[#14201E]">ClinicSync</h1>
        <p class="mt-2 text-sm text-[#465854]">
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
