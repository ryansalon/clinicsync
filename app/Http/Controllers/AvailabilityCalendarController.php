<?php

namespace App\Http\Controllers;

use App\Services\PhysicianAvailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityCalendarController extends Controller
{
    /**
     * Month-by-month physician availability consumed by the booking calendars
     * on the patient dashboard and the receptionist walk-in form.
     */
    public function show(Request $request, PhysicianAvailability $availability): JsonResponse
    {
        $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        return response()->json($availability->calendarMonth((string) $request->query('month')));
    }
}
