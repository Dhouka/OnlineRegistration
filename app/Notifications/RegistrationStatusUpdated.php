<?php

namespace App\Notifications;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Registration $registration,
        public string $previousStatus
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->registration->event;
        $status = $this->registration->status;

        $message = (new MailMessage)
            ->subject("Registration {$status} for {$event->title}");

        if ($status === 'approved') {
            $message->greeting('Great news!')
                ->line("Your registration for \"{$event->title}\" has been approved!")
                ->line("Event Date: {$event->start_date->format('l, F j, Y g:i A')}")
                ->line("Location: {$event->location}")
                ->action('View Event Details', route('events.show', $event))
                ->line('We look forward to seeing you at the event!');
        } elseif ($status === 'rejected') {
            $message->greeting('Registration Update')
                ->line("We regret to inform you that your registration for \"{$event->title}\" has been declined.");

            if ($this->registration->rejection_reason) {
                $message->line("Reason: {$this->registration->rejection_reason}");
            }

            $message->action('Browse Other Events', route('events.index'))
                ->line('Thank you for your interest in our events.');
        }

        if ($this->registration->organizer_notes) {
            $message->line("Note from organizer: {$this->registration->organizer_notes}");
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'registration_id' => $this->registration->id,
            'event_id' => $this->registration->event_id,
            'event_title' => $this->registration->event->title,
            'status' => $this->registration->status,
            'previous_status' => $this->previousStatus,
            'message' => $this->getNotificationMessage(),
        ];
    }

    private function getNotificationMessage(): string
    {
        $eventTitle = $this->registration->event->title;

        return match ($this->registration->status) {
            'approved' => "Your registration for \"{$eventTitle}\" has been approved!",
            'rejected' => "Your registration for \"{$eventTitle}\" has been declined.",
            'cancelled' => "Your registration for \"{$eventTitle}\" has been cancelled.",
            default => "Your registration status for \"{$eventTitle}\" has been updated.",
        };
    }
}
