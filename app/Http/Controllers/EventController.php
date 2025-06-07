<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * Display a listing of published events.
     */
    public function index(): View
    {
        $events = Event::where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('registration_end')
                    ->orWhere('registration_end', '>', now());
            })
            ->orderBy('start_date', 'asc')
            ->paginate(12);

        return view('events.index', compact('events'));
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event): View
    {
        // Only show published events
        if ($event->status !== 'published') {
            abort(404);
        }

        $isRegistrationOpen = $event->isRegistrationOpen();
        $hasAvailableSpots = $event->hasAvailableSpots();

        // Check if user is already registered
        $userRegistration = null;
        if (auth()->check()) {
            $userRegistration = $event->registrations()
                ->where('user_id', auth()->id())
                ->first();
        }

        return view('events.show', compact('event', 'isRegistrationOpen', 'hasAvailableSpots', 'userRegistration'));
    }
}
