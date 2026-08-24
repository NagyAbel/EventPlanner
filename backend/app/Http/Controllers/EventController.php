<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Http\Resources\EventResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SearchEventRequest;
use App\Services\ImageService;
class EventController extends Controller
{
    public function index(SearchEventRequest $request)
    {
        $validated = $request->validated();
        $user = auth('sanctum')->user();

        $scope = $validated['scope'] ?? 'public';
        if (!$user && $scope !== 'public') {
            $scope = 'public';
        }

        $perPage = $validated['per_page'] ?? 10;

        $events = Event::query()
            ->with(['owner', 'eventType'])
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
            // Events the user is invited to
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
            // General Filters (Escaped against wildcard DoS attacks)
            ->when(!empty($validated['search']), function ($query) use ($validated) {
                $search = addcslashes($validated['search'], '%_');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when(!empty($validated['city']), function ($query) use ($validated) {
                $city = addcslashes($validated['city'], '%_');
                $query->where(function ($q) use ($city) {
                    $q->where('city', 'like', "%{$city}%")
                      ->orWhere('location', 'like', "%{$city}%");
                });
            })
            ->when(!empty($validated['date']), fn($q) => $q->whereDate('date', $validated['date']))
            ->when(!empty($validated['event_type_id']), fn($q) => $q->where('event_type_id', $validated['event_type_id']))
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

    public function create(CreateEventRequest $request, ImageService $imageService)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($request, $data, $imageService) {
            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $imageService->storeCoverImage($request->file('cover_image'));
            }

            $event = $request->user()->events()->create($data);

            if ($request->has('invited_emails')) {
                $userIds = User::whereIn('email', $request->input('invited_emails', []))->pluck('id');
                $event->invites()->sync($userIds);
            }

            return new EventResource($event->load(['owner', 'invites', 'eventType']));
        });
    }

    public function update(UpdateEventRequest $request, Event $event,ImageService $imageService)
    {
        $data = $request->validated();
        return DB::transaction(function () use ($request, $event, $data,$imageService) {
            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $imageService->storeCoverImage($request->file('cover_image'));
            }
            $event->update($data);

            // Sync event invitations if present in request payload
            if ($request->has('invited_emails')) {
                $userIds = User::whereIn('email', $request->input('invited_emails', []))->pluck('id');
                $event->invites()->sync($userIds);
            }

            return new EventResource($event->load(['owner', 'invites', 'eventType']));
        });
    }

    public function delete(Request $request, Event $event)
    {
        abort_unless((int) $event->user_id === (int) $request->user()?->id, 403);

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

            $event = Event::whereKey($event->id)->lockForUpdate()->firstOrFail();

            if ((int) $event->user_id === (int) $user->id) {
                abort(422, 'You are the creator of this event.');
            }

            if (!$event->public) {
                $isInvited = $event->invites()
                    ->where('users.id', $user->id)
                    ->exists();

                if (!$isInvited) abort(403, 'You are not invited to this event.');
            }

            $changes = $event->attendees()->toggle($user->id);
            $isAttending = count($changes['attached']) > 0;

            $event->update(['attendee_count' => $event->attendees()->count(),]);

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