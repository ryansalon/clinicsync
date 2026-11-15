<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mark one of the signed-in user's notifications as read.
     */
    public function read(Request $request, string $notification): RedirectResponse
    {
        $record = $request->user()
            ->notifications()
            ->findOrFail($notification);

        $record->markAsRead();

        return back();
    }

    /**
     * Mark every unread notification as read.
     */
    public function readAll(Request $request): RedirectResponse
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return back()->with('status', 'All notifications marked as read.');
    }
}
