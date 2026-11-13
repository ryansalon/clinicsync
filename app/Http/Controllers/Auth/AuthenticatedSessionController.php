<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Suspended accounts must not authenticate at all; EnsureUserIsActive
        // only catches this after a session already exists.
        if (! Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        AuditLogger::log($user, 'user.login', $user);

        return redirect()->intended($this->homeRouteFor($user));
    }

    /**
     * Invalidate the user's session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if ($request->user() !== null) {
            AuditLogger::log($request->user(), 'user.logout', $request->user());
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function homeRouteFor(object $user): string
    {
        $role = Role::tryFrom((string) $user->getRoleNames()->first());

        return $role !== null ? route($role->homeRoute()) : route('login');
    }
}
