<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'date',
        'city',
        'location',
        'cover_image',
        'attendee_count',
        'public',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'attendee_count' => 'integer',
        ];
    }

    /**
     * User who owns the event.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Users attending the event.
     */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'attendee_users');
    }

    public function invites(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'invite_users');
    }
}