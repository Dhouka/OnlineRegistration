<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'short_description',
        'start_date',
        'end_date',
        'registration_start',
        'registration_end',
        'max_spots',
        'current_registrations',
        'form_fields',
        'location',
        'price',
        'image_url',
        'status',
        'requires_approval',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'registration_start' => 'datetime',
        'registration_end' => 'datetime',
        'form_fields' => 'array',
        'price' => 'decimal:2',
        'requires_approval' => 'boolean',
    ];

    /**
     * Get the user who created this event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the registrations for this event.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Get the approved registrations for this event.
     */
    public function approvedRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class)->where('status', 'approved');
    }

    /**
     * Get the pending registrations for this event.
     */
    public function pendingRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class)->where('status', 'pending');
    }

    /**
     * Check if registration is open.
     */
    public function isRegistrationOpen(): bool
    {
        $now = now();

        if ($this->registration_start && $now->lt($this->registration_start)) {
            return false;
        }

        if ($this->registration_end && $now->gt($this->registration_end)) {
            return false;
        }

        if ($this->max_spots && $this->current_registrations >= $this->max_spots) {
            return false;
        }

        return $this->status === 'published';
    }

    /**
     * Check if the event has available spots.
     */
    public function hasAvailableSpots(): bool
    {
        if (! $this->max_spots) {
            return true;
        }

        return $this->current_registrations < $this->max_spots;
    }
}
