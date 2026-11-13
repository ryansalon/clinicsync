<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\Channels\SmsChannel;
use App\Policies\AppointmentPolicy;
use App\Policies\EncounterPolicy;
use App\Policies\PatientPolicy;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // One password policy for registration, admin-created accounts,
        // password changes and reset links alike.
        Password::defaults(fn (): Password => Password::min(8));

        Notification::extend('sms', fn ($app) => new SmsChannel);

        RedirectIfAuthenticated::redirectUsing(function (Request $request): string {
            $role = $request->user() !== null
                ? Role::tryFrom((string) $request->user()->primaryRoleName())
                : null;

            return $role !== null ? route($role->homeRoute()) : '/';
        });

        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(Patient::class, PatientPolicy::class);
        Gate::policy(Encounter::class, EncounterPolicy::class);

        Paginator::defaultView('vendor.pagination.tailwind');

        ViewFactory::composer('layouts.app', function (View $view): void {
            $user = auth()->user();

            if ($user === null) {
                return;
            }

            $user->loadMissing(['patient', 'physician']);

            $photoPath = $user->patient?->photo_path ?? $user->physician?->photo_path;

            $view->with([
                'currentUser' => $user,
                'currentUserPhoto' => $photoPath !== null ? route('profile.photo') : null,
                'currentRoleLabel' => $this->roleLabelFor($user),
                'recentNotifications' => $user->notifications()->latest()->limit(6)->get(),
                'unreadNotificationCount' => $user->unreadNotifications()->count(),
            ]);
        });
    }

    /**
     * The signed-in user's display label for the sidebar.
     *
     * Roles are assigned once at account creation and never edited, so the
     * label is cached instead of querying the role tables on every page.
     */
    private function roleLabelFor(User $user): string
    {
        return Cache::remember(
            "user:{$user->id}:role-label",
            now()->addHour(),
            fn () => $user->getRoleNames()
                ->map(fn (string $role) => Role::tryFrom($role)?->label() ?? $role)
                ->first() ?? 'No role assigned',
        );
    }
}
