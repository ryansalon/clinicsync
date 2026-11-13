<?php

namespace App\Http\Controllers\Physician;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePhysicianScheduleRequest;
use App\Models\Appointment;
use App\Models\Physician;
use App\Models\PhysicianSchedule;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    /**
     * The physician's own availability windows and daily patient limit.
     */
    public function index(Request $request): View
    {
        $physician = $this->physicianFor($request);

        return view('physician.availability', [
            'physician' => $physician,
            'schedules' => $physician->schedules()
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get(),
            'bookedToday' => Appointment::query()
                ->forPhysician($physician->id)
                ->forDate(now()->toDateString())
                ->whereNotIn('status', [Appointment::STATUS_CANCELLED])
                ->count(),
        ]);
    }

    /**
     * Publish a new recurring availability window.
     */
    public function store(StorePhysicianScheduleRequest $request): RedirectResponse
    {
        $physician = $this->physicianFor($request);

        $schedule = $physician->schedules()->create($request->validated() + [
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLogger::log($request->user(), 'schedule.created', $schedule, null, $schedule->toArray());

        return redirect()
            ->route('physician.availability.index')
            ->with('status', 'Availability added.');
    }

    /**
     * Update one of the physician's own availability windows.
     */
    public function update(StorePhysicianScheduleRequest $request, PhysicianSchedule $schedule): RedirectResponse
    {
        $physician = $this->physicianFor($request);

        abort_unless($schedule->physician_id === $physician->id, 403);

        $before = $schedule->toArray();

        $schedule->update($request->validated() + [
            'is_active' => $request->boolean('is_active', $schedule->is_active),
        ]);

        AuditLogger::log($request->user(), 'schedule.updated', $schedule, $before, $schedule->fresh()->toArray());

        return redirect()
            ->route('physician.availability.index')
            ->with('status', 'Availability updated.');
    }

    /**
     * Retire one of the physician's own availability windows.
     */
    public function destroy(Request $request, PhysicianSchedule $schedule): RedirectResponse
    {
        $physician = $this->physicianFor($request);

        abort_unless($schedule->physician_id === $physician->id, 403);

        $schedule->delete();

        AuditLogger::log($request->user(), 'schedule.deleted', $schedule);

        return redirect()
            ->route('physician.availability.index')
            ->with('status', 'Availability removed.');
    }

    /**
     * Set how many patients the physician can cater in a single day.
     */
    public function updateLimit(Request $request): RedirectResponse
    {
        $physician = $this->physicianFor($request);

        $validated = $request->validate([
            'daily_patient_limit' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $before = ['daily_patient_limit' => $physician->daily_patient_limit];

        $physician->update($validated);

        AuditLogger::log($request->user(), 'physician.limit_updated', $physician, $before, $validated);

        return redirect()
            ->route('physician.availability.index')
            ->with('status', 'Daily patient limit updated to '.$validated['daily_patient_limit'].'.');
    }

    private function physicianFor(Request $request): Physician
    {
        $physician = $request->user()->physician;

        abort_if($physician === null, 404);

        return $physician;
    }
}
