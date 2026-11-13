<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\Patient;
use App\Models\Physician;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SequenceGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Account administration with role and status filters.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $role = $request->query('role');

        $users = User::query()
            ->with(['patient', 'physician', 'roles'])
            ->withCount('roles')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(fn ($sub) => $sub
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($role !== null && $role !== '', fn ($query) => $query->role($role))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'roles' => Role::cases(),
        ]);
    }

    /**
     * Create a staff or patient account, including its linked clinical profile.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $role = Role::from($data['role']);

        $user = DB::transaction(function () use ($data, $role) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'is_active' => true,
            ]);

            $user->assignRole($role->value);

            if ($role === Role::Patient) {
                $user->patient()->create([
                    'patient_number' => $this->nextPatientNumber(),
                    'date_of_birth' => now()->subYears(20)->toDateString(),
                    'sex' => 'other',
                ]);
            }

            if ($role === Role::Physician) {
                $user->physician()->create([
                    'physician_number' => $this->nextPhysicianNumber(),
                    'specialization' => $data['specialization'],
                    'license_number' => $data['license_number'],
                    'appointment_slot_minutes' => 15,
                ]);
            }

            return $user;
        });

        AuditLogger::log($request->user(), 'user.created', $user, null, ['role' => $role->value]);

        Physician::flushRosterCache();

        return redirect()
            ->route('admin.users.index')
            ->with('status', $user->name.' created as '.$role->label().'.');
    }

    /**
     * Suspend or reactivate an account.
     */
    public function status(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'You cannot change your own account status.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        AuditLogger::log($request->user(), $user->is_active ? 'user.activated' : 'user.suspended', $user);

        return redirect()
            ->route('admin.users.index', $request->only('q', 'role'))
            ->with('status', $user->name.($user->is_active ? ' reactivated.' : ' suspended.'));
    }

    private function nextPatientNumber(): string
    {
        $latest = Patient::query()->orderByDesc('id')->lockForUpdate()->value('patient_number');

        return SequenceGenerator::next($latest, 'PT-');
    }

    private function nextPhysicianNumber(): string
    {
        $latest = Physician::query()->orderByDesc('id')->lockForUpdate()->value('physician_number');

        return SequenceGenerator::next($latest, 'DR-');
    }
}
