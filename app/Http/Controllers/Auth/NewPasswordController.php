<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the reset-password form.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'token' => (string) $request->route('token'),
            'email' => (string) $request->query('email'),
        ]);
    }

    /**
     * Reset the user's password and sign them in.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', PasswordRule::defaults()],
        ]);

        $user = null;

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $resetUser, string $password) use (&$user) {
                $resetUser->forceFill(['password' => $password])->save();

                $user = $resetUser;

                AuditLogger::log($resetUser, 'user.password_reset', $resetUser);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        $role = Role::tryFrom((string) $user->primaryRoleName());

        return redirect()->intended($role !== null ? route($role->homeRoute()) : route('login'))
            ->with('status', 'Your password has been reset. You are now signed in.');
    }
}
