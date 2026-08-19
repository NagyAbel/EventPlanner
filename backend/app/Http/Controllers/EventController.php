<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class EventController extends Controller
{
    public function index()
    {
        return response()->json([
            'events' => Event::with('owner')
                ->latest('date')
                ->get(),
        ]);
    }

    public function show(Event $event)
    {
        return response()->json([
            'event' => $event->load('owner'),
        ]);
    }

    public function store(CreateEventRequest $request){
        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store(
                'events/covers',
                'public'
            );
        }

        $event = $request->user()
            ->events()
            ->create($data);

        return response()->json(['event' => $event->load('owner'),], 201);
    }

    public function update(UpdateEventRequest $request,Event $event) {
        abort_unless(
            $event->user_id === $request->user()->id,
            403
        );

        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            if ($event->cover_image) {
                Storage::disk('public')->delete($event->cover_image);
            }

            $data['cover_image'] = $request->file('cover_image')->store(
                'events/covers',
                'public'
            );
        }

        $event->update($data);

        return response()->json([
            'event' => $event->fresh()->load('owner'),
        ]);
    }

    public function destroy(Request $request, Event $event){
        abort_unless(
            $event->user_id === $request->user()->id,
            403
        );

        if ($event->cover_image) {
            Storage::disk('public')->delete($event->cover_image);
        }

        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully.',
        ]);
    }
}