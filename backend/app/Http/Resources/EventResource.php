<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'description'    => $this->description,
            'type'           => $this->type,
            'date'           => $this->date,
            'city'           => $this->city,
            'location'       => $this->location,
            'public'         => (int)$this->public,
            'cover_image'    => $this->cover_image
                ? Storage::disk('public')->url($this->cover_image)
                : null,
            'attendee_count' => $this->attendee_count,
            'invited_emails' => $this->whenLoaded('invite_users', function () {
                return $this->attendees->pluck('email');
            }),

            'is_attending'   => $user && $this->relationLoaded('attendees') 
                ? $this->attendees->contains('id', $user->id) 
                : false,
            'owner'=> $this->whenLoaded('owner', function () {
                return [
                    'id'=>$this->owner->id,
                    'name'  => $this->owner->name,
                    'email' => $this->owner->email,
                ];
            }),        ];
    }
}