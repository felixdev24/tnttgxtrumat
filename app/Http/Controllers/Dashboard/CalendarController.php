<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CalendarEvent;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class CalendarController extends Controller
{
    public function index(Request $request): Response
    {
        $events = CalendarEvent::query()
            ->orderBy('event_date', 'asc')
            ->get();

        return Inertia::render('dashboard/calendar/Index', [
            'events' => $events,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event_date' => 'required|date',
            'title' => 'required|string|max:255',
            'type' => 'required|in:solemnity,memorial,feast,note',
            'description' => 'nullable|string',
        ]);

        CalendarEvent::create($validated);

        return redirect()->route('dashboard.calendar.index')
            ->with('success', 'Thêm sự kiện lịch thành công!');
    }

    public function update(Request $request, CalendarEvent $calendarEvent): RedirectResponse
    {
        $validated = $request->validate([
            'event_date' => 'required|date',
            'title' => 'required|string|max:255',
            'type' => 'required|in:solemnity,memorial,feast,note',
            'description' => 'nullable|string',
        ]);

        $calendarEvent->update($validated);

        return redirect()->route('dashboard.calendar.index')
            ->with('success', 'Cập nhật sự kiện lịch thành công!');
    }

    public function destroy(CalendarEvent $calendarEvent): RedirectResponse
    {
        $calendarEvent->delete();

        return redirect()->route('dashboard.calendar.index')
            ->with('success', 'Xóa sự kiện lịch thành công!');
    }
}
