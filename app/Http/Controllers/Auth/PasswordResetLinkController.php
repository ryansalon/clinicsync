<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the forgot-password form.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a password reset link to the given user.
     *
     * Always reports success so the form never reveals which addresses exist.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($request->only('email'));

        return back()->with(
            'status',
            'If that email address exists in our records, a password reset link has been sent.'
        );
    }
}
