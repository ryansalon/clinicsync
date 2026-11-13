<?php

namespace App\Http\Controllers\Physician;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Encounter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Physician home: today's consultation queue and caseload counters.
     */
    public function index(Request $request): View
    {
        $physician = $request->user()->physician;

        abort_if($physician === null, 404);

        $today = now()->toDateString();

        $queue = Appointment::query()
            ->forPhysician($physician->id)
            ->forDate($today)
            ->with('patient.user')
            ->orderBy('queue_number')
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('physician.partials.queue-table', [
                    'queue' => $queue,
                ])->render(),
            ]);
        }

        return view('physician.dashboard', [
            'physician' => $physician,
            'queue' => $queue,
            'waiting' => $queue->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED]),
            'completedToday' => $queue->where('status', Appointment::STATUS_COMPLETED)->count(),
            'upcoming' => Appointment::query()
                ->forPhysician($physician->id)
                ->active()
                ->where('appointment_date', '>', $today)
                ->with('patient.user')
                ->orderBy('appointment_date')
                ->orderBy('start_time')
                ->limit(10)
                ->get(),
            'encounterCount' => Encounter::query()->where('physician_id', $physician->id)->count(),
        ]);
    }
}
