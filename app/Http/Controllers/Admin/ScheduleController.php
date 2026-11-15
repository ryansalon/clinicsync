<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Models\Physician;
use App\Models\PhysicianSchedule;
use App\Services\AuditLogger;
use App\Services\PhysicianAvailability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Physician availability calendar for the current week.
     */
    public function index(Request $request): View
    {
        $physicianId = $request->query('physician_id');

        $schedules = PhysicianSchedule::query()
            ->with('physician.user')
            ->when($physicianId !== null && $physicianId !== '', fn ($query) => $query->where('physician_id', (int) $physicianId))
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return view('admin.schedules.index', [
            'schedules' => $schedules,
            'physicians' => Physician::cachedRoster(),
            'physicianId' => $physicianId === '' ? null : $physicianId,
        ]);
    }

    /**
     * Publish a new recurring availability window for a physician.
     */
    public function store(StoreScheduleRequest $request): RedirectResponse
    {
        $schedule = PhysicianSchedule::create($request->validated() + [
            'is_active' => $request->boolean('is_active'),
        ]);

        PhysicianAvailability::flushCalendarCache();
        AuditLogger::log($request->user(), 'schedule.created', $schedule, null, $schedule->toArray());

        return redirect()
            ->route('admin.schedules.index', ['physician_id' => $schedule->physician_id])
            ->with('status', 'Schedule added.');
    }

    /**
     * Update an availability window.
     */
    public function update(StoreScheduleRequest $request, PhysicianSchedule $schedule): RedirectResponse
    {
        $before = $schedule->toArray();

        $schedule->update($request->validated() + [
            'is_active' => $request->boolean('is_active', $schedule->is_active),
        ]);

        PhysicianAvailability::flushCalendarCache();
        AuditLogger::log($request->user(), 'schedule.updated', $schedule, $before, $schedule->fresh()->toArray());

        return redirect()
            ->route('admin.schedules.index', ['physician_id' => $schedule->physician_id])
            ->with('status', 'Schedule updated.');
    }

    /**
     * Retire an availability window.
     */
    public function destroy(Request $request, PhysicianSchedule $schedule): RedirectResponse
    {
        $physicianId = $schedule->physician_id;

        $schedule->delete();

        PhysicianAvailability::flushCalendarCache();
        AuditLogger::log($request->user(), 'schedule.deleted', $schedule);

        return redirect()
            ->route('admin.schedules.index', ['physician_id' => $physicianId])
            ->with('status', 'Schedule removed.');
    }
}
