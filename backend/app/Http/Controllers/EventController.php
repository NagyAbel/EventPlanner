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
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SearchEventRequest;
class EventController extends Controller
{
    public function list(SearchEventRequest $request)
    {
        $perPage = min((int) $request->input('per_page', 10), 100);
        $page = (int) $request->input('page', 1);
        $events = Event::with('owner','attendees')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('city'), function ($query) use ($request) {
                $query->where('city', $request->input('city'));
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereDate('date', $request->input('date'));
            })
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(
                perPage: $perPage,
                pageName: 'page',
                page: $page
            );
            return response()->json([
                'data'         => EventResource::collection($events->items()),
                'current_page' => $events->currentPage(),
                'last_page'    => $events->lastPage(),
                'per_page'     => $events->perPage(),
                'total'        => $events->total(),
            ]);    
    }

    public function own(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 10), 100);

        $events = Event::with(['owner', 'attendees'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'data'         => EventResource::collection($events->items()),
            'current_page' => $events->currentPage(),
            'last_page'    => $events->lastPage(),
            'per_page'     => $events->perPage(),
            'total'        => $events->total(),
        ]);
    }

    public function joined(Request $request){
        $perPage = min((int) $request->input('per_page', 20), 100);

        $events = Event::with(['owner', 'attendees'])
        ->whereHas('attendees', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })
        ->orderByDesc('date')
        ->orderByDesc('id')
        ->paginate($perPage);

        return response()->json([
            'data'         => EventResource::collection($events->items()),
            'current_page' => $events->currentPage(),
            'last_page'    => $events->lastPage(),
            'per_page'     => $events->perPage(),
            'total'        => $events->total(),
        ]);
    }

    public function show(Event $event){
        $event->load('owner','attendees');

        return new EventResource($event);
    }

    public function create(CreateEventRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            $oldImage = $request->cover_image;

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
                Storage::disk('public')->delete($oldImage);
            }
            $data['cover_image'] = $path;        
        }

        $event = $request->user()
            ->events()
            ->create($data);

        return new EventResource($event->load('owner','invites'));
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        abort_unless((int) $event->user_id === (int) $request->user()->id,403);

        $data = $request->validated();

        return DB::transaction(function () use ($request, $event, $data) {
            if ($request->hasFile('cover_image')) {
            $oldImage = $event->cover_image;

            $manager = ImageManager::usingDriver(Driver::class);
            $image = $manager->decodePath($request->file('cover_image')->getRealPath());
            $image->scaleDown(width: 1920, height: 1080);
            $imageData = $image->encodeUsingFormat(Format::WEBP, quality: 65);
            
            $filename = Str::uuid() . '.webp';
            $path = 'events/covers/' . $filename;

            Storage::disk('public')->put($path, (string) $imageData);
            
            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
            
            $data['cover_image'] = $path;
            }

            $event->update($data);

            // Sync event invitations if present in request payload
            if ($request->has('invited_emails')) {
                $userIds = User::whereIn('email', $request->input('invited_emails', []))
                    ->pluck('id');

                $event->attendees()->sync($userIds);
            }

            $event->load(['owner', 'attendees']);

            return new EventResource($event);
        });
    }

    public function delete(Request $request, Event $event)
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

    public function attend(Request $request, Event $event){
        $user = $request->user();

        if ((int) $event->user_id === (int) $user->id) {
            return response()->json(['message' => 'You are the creator of this event.',], 422);
        }

        if (!$event->public) {
            $event->loadMissing('invites');

            $isInvited = $event->invites->contains(function ($attendee) use ($user) {
                return $attendee->id === $user->id;
            });

            if (!$isInvited) {
                return response()->json([
                    'message' => 'You are not invited to this event.',
                ], 403);
            }
        }

        $changes = $event->attendees()->toggle($user->id);
    
        $isAttending = count($changes['attached']) > 0;

        return response()->json([
            'message' => $isAttending ? 'Successfully joined the event.' : 'Successfully left the event.',
            'attending' => $isAttending,
            'event' => new EventResource($event->load(['owner', 'attendees'])),
        ]);
    }
}