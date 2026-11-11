<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PublicEventController extends Controller
{
    public function index(): View
    {
        return view('events.public');
    }

    public function feed(): JsonResponse
    {
        $events = Event::query()
            ->orderBy('start_at')
            ->get()
            ->map(fn (Event $event): array => [
                'id' => $event->id,
                'title' => $event->title,
                'color' => $event->color,
                'start' => $event->start_at->toIso8601String(),
                'end' => $event->end_at?->toIso8601String(),
            ]);

        return response()->json($events);
    }
}
