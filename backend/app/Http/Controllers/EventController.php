<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Http\Resources\EventResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Illuminate\Support\Str;
use Intervention\Image\Format;
class EventController extends Controller
{
    public function own(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 20),100);

        $events = Event::with('owner')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($perPage);

        return EventResource::collection($events);  
    }

    public function show(Event $event){
        $event->load('owner');

        return new EventResource($event);
    }

    public function store(CreateEventRequest $request)
    {
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

        return response()->json([
            'event' => $event->load('owner'),
        ], 201);
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        abort_unless(
            (int) $event->user_id === (int) $request->user()->id,
            403
        );

        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            $oldImage = $event->cover_image;

            $manager = ImageManager::usingDriver(Driver::class);
            //throw new \Exception('Debug error triggered in EventController::update');
            $image = $manager->decodePath($request->file('cover_image')->getRealPath());
            $image->scaleDown(
                width: 1920,
                height: 1080,
            );
            $imageData = $image->encodeUsingFormat(Format::WEBP, quality: 65);;            
            $filename = Str::uuid() . '.webp';
            $path = 'events/covers/' . $filename;

            Storage::disk('public')->put($path, (string) $imageData);
            if ($oldImage) {
                Storage::disk('public')->delete($event->cover_image);
            }
            $data['cover_image'] = $path;        
        }

        $event->update($data);

        $event->load('owner');

        return new EventResource($event);
    }

    public function destroy(Request $request, Event $event)
    {
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