<?php

namespace App\Models;

use App\Notifications\RegistrationStatusUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'status',
        'form_data',
        'uploaded_files',
        'organizer_notes',
        'rejection_reason',
        'approved_at',
        'rejected_at',
        'reviewed_by',
    ];

    protected $casts = [
        'form_data' => 'array',
        'uploaded_files' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Get the user who made this registration.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event for this registration.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who reviewed this registration.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Approve the registration.
     */
    public function approve(User $reviewer, ?string $notes = null): void
    {
        $previousStatus = $this->status;

        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'rejected_at' => null,
            'reviewed_by' => $reviewer->id,
            'organizer_notes' => $notes,
            'rejection_reason' => null,
        ]);

        // Increment event registration count
        $this->event->increment('current_registrations');

        // Send notification
        $this->user->notify(new RegistrationStatusUpdated($this, $previousStatus));
    }

    /**
     * Reject the registration.
     */
    public function reject(User $reviewer, ?string $reason = null, ?string $notes = null): void
    {
        $previousStatus = $this->status;

        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'approved_at' => null,
            'reviewed_by' => $reviewer->id,
            'rejection_reason' => $reason,
            'organizer_notes' => $notes,
        ]);

        // Decrement event registration count if it was previously approved
        if ($this->getOriginal('status') === 'approved') {
            $this->event->decrement('current_registrations');
        }

        // Send notification
        $this->user->notify(new RegistrationStatusUpdated($this, $previousStatus));
    }

    /**
     * Cancel the registration.
     */
    public function cancel(): void
    {
        $wasApproved = $this->status === 'approved';

        $this->update([
            'status' => 'cancelled',
        ]);

        // Decrement event registration count if it was approved
        if ($wasApproved) {
            $this->event->decrement('current_registrations');
        }
    }
}
