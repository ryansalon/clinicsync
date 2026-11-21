<?php

use App\Enums\Role;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\QueueController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\AvailabilityCalendarController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Patient\AppointmentController as PatientAppointmentController;
use App\Http\Controllers\Patient\DashboardController as PatientDashboardController;
use App\Http\Controllers\Physician\AvailabilityController as PhysicianAvailabilityController;
use App\Http\Controllers\Physician\DashboardController as PhysicianDashboardController;
use App\Http\Controllers\Physician\EncounterController;
use App\Http\Controllers\Physician\PatientController as PhysicianPatientController;
use App\Http\Controllers\Physician\PrescriptionController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth()->user();

    if ($user === null) {
        return redirect()->route('login');
    }

    $role = Role::tryFrom((string) $user->primaryRoleName());

    return $role !== null ? redirect()->route($role->homeRoute()) : view('welcome');
});

/*
|--------------------------------------------------------------------------
| Guest routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:10,1');

    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:5,1');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    /*
    | Account settings
    */
    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('settings/profile/photo', [ProfileController::class, 'photo'])->name('profile.photo');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('settings/password', [ProfileController::class, 'updatePassword'])->name('password.change');

    /*
    | Notifications
    */
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    /*
    | Patient portal
    */
    Route::middleware('role:patient')->prefix('patient')->name('patient.')->group(function () {
        Route::get('dashboard', [PatientDashboardController::class, 'index'])->name('dashboard');
        Route::get('records', [PatientDashboardController::class, 'records'])->name('records');

        Route::get('appointments', [PatientAppointmentController::class, 'index'])->name('appointments.index');
        Route::get('appointments/create', [PatientAppointmentController::class, 'create'])->name('appointments.create');
        Route::get('appointments/availability', [PatientAppointmentController::class, 'availability'])
            ->middleware('throttle:60,1')
            ->name('appointments.availability');
        Route::get('availability-calendar', [AvailabilityCalendarController::class, 'show'])
            ->middleware('throttle:30,1')
            ->name('availability.calendar');
        Route::post('appointments', [PatientAppointmentController::class, 'store'])->name('appointments.store');
        Route::get('appointments/{appointment}', [PatientAppointmentController::class, 'show'])->name('appointments.show');
        Route::get('appointments/{appointment}/reschedule', [PatientAppointmentController::class, 'reschedule'])->name('appointments.reschedule');
        Route::patch('appointments/{appointment}/reschedule', [PatientAppointmentController::class, 'rescheduleUpdate'])->name('appointments.reschedule.update');
        Route::delete('appointments/{appointment}', [PatientAppointmentController::class, 'destroy'])->name('appointments.destroy');
    });

    /*
    | Physician dashboard
    */
    Route::middleware('role:physician')->prefix('physician')->name('physician.')->group(function () {
        Route::get('dashboard', [PhysicianDashboardController::class, 'index'])->name('dashboard');

        Route::get('availability', [PhysicianAvailabilityController::class, 'index'])->name('availability.index');
        Route::post('availability', [PhysicianAvailabilityController::class, 'store'])->name('availability.store');
        Route::patch('availability/limit', [PhysicianAvailabilityController::class, 'updateLimit'])->name('availability.limit');
        Route::put('availability/{schedule}', [PhysicianAvailabilityController::class, 'update'])->name('availability.update');
        Route::delete('availability/{schedule}', [PhysicianAvailabilityController::class, 'destroy'])->name('availability.destroy');

        Route::get('encounters', [EncounterController::class, 'index'])->name('encounters.index');
        Route::get('encounters/{encounter}', [EncounterController::class, 'show'])->name('encounters.show');
        Route::put('encounters/{encounter}', [EncounterController::class, 'update'])->name('encounters.update');
        Route::post('encounters/{encounter}/prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');

        Route::get('appointments/{appointment}/consultation', [EncounterController::class, 'create'])->name('consultation');
        Route::post('appointments/{appointment}/encounters', [EncounterController::class, 'store'])->name('encounters.store');

        Route::get('patients/{patient}', [PhysicianPatientController::class, 'show'])->name('patients.show');
        Route::post('patients/{patient}/allergies', [PhysicianPatientController::class, 'storeAllergy'])->name('patients.allergies.store');
        Route::patch('patients/{patient}/allergies/{allergy}/toggle', [PhysicianPatientController::class, 'toggleAllergy'])->name('patients.allergies.toggle');
        Route::post('patients/{patient}/histories', [PhysicianPatientController::class, 'storeHistory'])->name('patients.histories.store');
    });

    /*
    | Receptionist / administration
    */
    Route::middleware('role:administrator|receptionist')->prefix('admin')->name('admin.')->group(function () {
        Route::get('queue', [QueueController::class, 'index'])->name('queue.index');
        Route::post('queue/walk-ins', [QueueController::class, 'store'])->name('queue.store');
        Route::patch('queue/{appointment}', [QueueController::class, 'update'])->name('queue.update');
        Route::patch('queue/{appointment}/call', [QueueController::class, 'call'])->name('queue.call');
        Route::delete('queue/{appointment}', [QueueController::class, 'destroy'])->name('queue.destroy');

        Route::get('availability-calendar', [AvailabilityCalendarController::class, 'show'])
            ->middleware('throttle:30,1')
            ->name('availability.calendar');

        Route::get('schedules', [ScheduleController::class, 'index'])->name('schedules.index');
        Route::post('schedules', [ScheduleController::class, 'store'])->name('schedules.store');
        Route::put('schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
        Route::delete('schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
    });

    /*
    | System administrator only
    */
    Route::middleware('role:administrator')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::patch('users/{user}/status', [UserController::class, 'status'])->name('users.status');

        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});
