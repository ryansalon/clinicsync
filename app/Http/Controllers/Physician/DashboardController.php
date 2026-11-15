<?php

namespace App\Http\Controllers\Physician;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\Physician;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Physician home: today's consultation queue and caseload counters.
     */
    public function index(Request $request): View|JsonResponse
    {
        $physician = $request->user()->physician;

        abort_if($physician === null, 404);

        $today = now()->toDateString();

        $queue = Appointment::query()
            ->forPhysician($physician->id)
            ->forDate($today)
            ->with(['patient.user', 'patient.activeAllergies', 'encounter'])
            ->orderBy('queue_number')
            ->get();

        $boardData = $this->boardData($physician, $queue);

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('physician.partials.board', $boardData)->render(),
            ]);
        }

        return view('physician.dashboard', $boardData + [
            'physician' => $physician,
            'upcoming' => Appointment::query()
                ->forPhysician($physician->id)
                ->active()
                ->where('appointment_date', '>', $today)
                ->with('patient.user')
                ->orderBy('appointment_date')
                ->orderBy('start_time')
                ->limit(10)
                ->get(),
        ]);
    }

    /**
     * @param  Collection<int, Appointment>  $queue
     * @return array<string, mixed>
     */
    private function boardData(Physician $physician, $queue): array
    {
        $waiting = $queue->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED]);

        return [
            'queue' => $queue,
            'waiting' => $waiting,
            'nextPatient' => $waiting->first(),
            'completedToday' => $queue->where('status', Appointment::STATUS_COMPLETED)->count(),
            'encounterCount' => Encounter::query()->where('physician_id', $physician->id)->count(),
        ];
    }
}
