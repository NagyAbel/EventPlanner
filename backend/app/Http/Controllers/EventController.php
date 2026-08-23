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
    public function index(SearchEventRequest $request)
    {
        $perPage = min((int) $request->input('per_page', 10), 100);
        $user = auth('sanctum')->user();
        $scope = $request->input('scope', 'public');
    
        $events = Event::query()
            ->with(['owner', 'eventType']) // Eager load 'type' relationship
            ->when($user, function ($query) use ($user) {
                $query->withExists(['attendees as is_attending' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                }]);
            })
            // Events created by the user
            ->when($scope === 'own' && $user, function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            // Events the user is attending
            ->when($scope === 'joined' && $user, function ($query) use ($user) {
                $query->whereHas('attendees', fn($q) => $q->where('user_id', $user->id));
            })
            ->when($scope === 'invited' && $user, function ($query) use ($user) {
                $query->whereHas('invites', fn($q) => $q->where('user_id', $user->id));
            })            
            // Default public/invited discovery list
            ->when($scope === 'public', function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('public', true);
                    if ($user) {
                        $q->orWhere('user_id', $user->id)
                        ->orWhereHas('invites', fn($inv) => $inv->where('users.id', $user->id));
                    }
                });
            })
            // General Filters
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('city'), fn($q) => $q->where('city', $request->input('city')))
            ->when($request->filled('date'), fn($q) => $q->whereDate('date', $request->input('date')))
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

    public function show(Event $event)
    {
        $user = auth('sanctum')->user();

        if (!$event->public) {
            $isOwner = $user && (int) $event->user_id === (int) $user->id;
            $isInvited = $user && $event->invites()->where('users.id', $user->id)->exists();

            abort_unless($isOwner || $isInvited, 403, 'This event is private.');
        }

        return new EventResource($event->load(['owner', 'invites', 'eventType']));
    }

    public function create(CreateEventRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            $manager = ImageManager::usingDriver(Driver::class);
            $image = $manager->decodePath($request->file('cover_image')->getRealPath());
            $image->scaleDown(
                width: 1920,
                height: 1080,
            );
            $imageData = $image->encodeUsingFormat(Format::WEBP, quality: 65);            
            $filename = Str::uuid() . '.webp';
            $path = 'events/covers/' . $filename;

            Storage::disk('public')->put($path, (string) $imageData);
            $data['cover_image'] = $path;        
        }

        $event = $request->user()
            ->events()
            ->create($data);

        return new EventResource($event->load(['owner', 'invites', 'eventType']));
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        abort_unless((int) $event->user_id === (int) $request->user()->id, 403);

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

                $event->invites()->sync($userIds);
            }

            return new EventResource($event->load(['owner', 'invites', 'eventType']));
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

    public function attend(Request $request, Event $event)
    {
        $user = $request->user();

        $result = DB::transaction(function () use ($event, $user) {

            $event = Event::whereKey($event->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $event->user_id === (int) $user->id) {
                abort(422, 'You are the creator of this event.');
            }

            if (!$event->public) {
                $isInvited = $event->invites()
                    ->where('users.id', $user->id)
                    ->exists();

                if (!$isInvited) {
                    abort(403, 'You are not invited to this event.');
                }
            }

            $changes = $event->attendees()->toggle($user->id);

            $isAttending = count($changes['attached']) > 0;

            $event->update([
                'attendee_count' => $event->attendees()->count(),
            ]);

            return [
                'event' => $event,
                'is_attending' => $isAttending,
            ];
        });

        $event = $result['event'];
        $isAttending = $result['is_attending'];

        $event->load(['owner', 'eventType']);

        $event->loadExists([
            'attendees as is_attending' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            },
        ]);

        return response()->json([
            'message' => $isAttending
                ? 'Successfully joined the event.'
                : 'Successfully left the event.',

            'attending' => $isAttending,

            'event' => new EventResource($event),
        ]);
    }
}