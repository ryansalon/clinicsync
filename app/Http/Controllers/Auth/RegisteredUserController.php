<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Patient;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SequenceGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->string('name'),
                'email' => $request->string('email'),
                'phone' => $request->input('phone'),
                'password' => $request->string('password'),
                'is_active' => true,
            ]);

            $user->assignRole(Role::Patient->value);

            $user->patient()->create([
                'patient_number' => $this->generatePatientNumber(),
                'date_of_birth' => $request->input('date_of_birth'),
                'sex' => $request->input('sex'),
                'civil_status' => $request->input('civil_status'),
                'address' => $request->input('address'),
                'blood_type' => $request->input('blood_type'),
                'emergency_contact_name' => $request->input('emergency_contact_name'),
                'emergency_contact_phone' => $request->input('emergency_contact_phone'),
                'emergency_contact_relationship' => $request->input('emergency_contact_relationship'),
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        AuditLogger::log($user, 'user.registered', $user);

        return redirect()->route(Role::Patient->homeRoute());
    }

    private function generatePatientNumber(): string
    {
        $latest = Patient::query()
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('patient_number');

        return SequenceGenerator::next($latest, 'PT-');
    }
}
