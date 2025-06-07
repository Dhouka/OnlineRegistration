<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;

class RegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = Event::where('status', 'published')->get();
        $candidates = User::whereHas('roles', function ($query) {
            $query->where('name', 'candidate');
        })->get();

        if ($events->isEmpty() || $candidates->isEmpty()) {
            $this->command->warn('No published events or candidates found. Skipping registration seeding.');

            return;
        }

        $this->command->info('Creating registrations for events...');

        foreach ($events as $event) {
            $this->createRegistrationsForEvent($event, $candidates);
        }

        // Create some additional random registrations using factory
        // Use a try-catch to handle potential unique constraint violations
        $additionalRegistrations = 0;
        $maxAttempts = 30;

        for ($i = 0; $i < $maxAttempts; $i++) {
            try {
                Registration::factory()->create();
                $additionalRegistrations++;
                if ($additionalRegistrations >= 15) {
                    break;
                } // Target 15 additional registrations
            } catch (\Exception $e) {
                // Skip if duplicate registration
                continue;
            }
        }

        $this->command->info('Registration seeding completed!');
        $this->command->info('Total registrations created: '.Registration::count());
        $this->command->info('- Pending: '.Registration::where('status', 'pending')->count());
        $this->command->info('- Approved: '.Registration::where('status', 'approved')->count());
        $this->command->info('- Rejected: '.Registration::where('status', 'rejected')->count());
        $this->command->info('- Cancelled: '.Registration::where('status', 'cancelled')->count());
    }

    /**
     * Create registrations for a specific event.
     */
    private function createRegistrationsForEvent(Event $event, $candidates): void
    {
        // Determine how many registrations to create for this event
        $maxRegistrations = min(
            $event->max_spots ? (int) ($event->max_spots * 0.8) : 20, // 80% of max spots or 20
            $candidates->count(), // Don't exceed available candidates
            25 // Maximum 25 registrations per event
        );

        $numRegistrations = fake()->numberBetween(
            max(1, (int) ($maxRegistrations * 0.3)), // At least 30% of max
            $maxRegistrations
        );

        // Randomly select candidates for this event
        $selectedCandidates = $candidates->random($numRegistrations);

        $statusDistribution = $this->getStatusDistribution($event);

        foreach ($selectedCandidates as $index => $candidate) {
            $status = $this->selectStatus($statusDistribution, $index, $numRegistrations);

            $registration = $this->createRegistrationForEventAndUser($event, $candidate, $status);

            if ($registration && $status === 'approved') {
                // Update event's current_registrations count
                $event->increment('current_registrations');
            }
        }
    }

    /**
     * Get status distribution based on event characteristics.
     */
    private function getStatusDistribution(Event $event): array
    {
        if ($event->requires_approval) {
            // Events requiring approval have more varied statuses
            return [
                'approved' => 0.5,   // 50%
                'pending' => 0.25,   // 25%
                'rejected' => 0.15,  // 15%
                'cancelled' => 0.1,  // 10%
            ];
        } else {
            // Events not requiring approval are mostly approved
            return [
                'approved' => 0.8,   // 80%
                'pending' => 0.1,    // 10%
                'rejected' => 0.05,  // 5%
                'cancelled' => 0.05, // 5%
            ];
        }
    }

    /**
     * Select status based on distribution and position.
     */
    private function selectStatus(array $distribution, int $index, int $total): string
    {
        // Ensure we have some of each status type
        if ($index === 0) {
            return 'approved';
        }
        if ($index === 1 && $total > 3) {
            return 'pending';
        }
        if ($index === 2 && $total > 5) {
            return 'rejected';
        }
        if ($index === 3 && $total > 7) {
            return 'cancelled';
        }

        // Use weighted random selection for the rest
        $random = fake()->randomFloat(2, 0, 1);
        $cumulative = 0;

        foreach ($distribution as $status => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $status;
            }
        }

        return 'approved'; // Fallback
    }

    /**
     * Create a registration for a specific event and user.
     */
    private function createRegistrationForEventAndUser(Event $event, User $user, string $status): ?Registration
    {
        try {
            $formData = $this->generateFormDataForEvent($event, $user);
            $uploadedFiles = $this->generateUploadedFiles();

            $registrationData = [
                'user_id' => $user->id,
                'event_id' => $event->id,
                'status' => $status,
                'form_data' => $formData,
                'uploaded_files' => $uploadedFiles,
            ];

            // Add status-specific data
            $registrationData = $this->addStatusSpecificData($registrationData, $status);

            return Registration::create($registrationData);
        } catch (\Exception $e) {
            // Skip if registration already exists (unique constraint)
            return null;
        }
    }

    /**
     * Generate form data specific to the event and user.
     */
    private function generateFormDataForEvent(Event $event, User $user): array
    {
        $formData = [];

        if ($event->form_fields) {
            foreach ($event->form_fields as $field) {
                $label = $field['label'] ?? 'Unknown Field';
                $type = $field['type'] ?? 'text';
                $required = $field['required'] ?? false;

                // Skip optional fields sometimes
                if (! $required && fake()->boolean(20)) {
                    continue;
                }

                $formData[$label] = $this->generateFieldValue($field, $user);
            }
        }

        // Always include phone if user has it
        if ($user->phone) {
            $formData['Phone Number'] = $user->phone;
        }

        return $formData;
    }

    /**
     * Generate field value based on field type and user data.
     */
    private function generateFieldValue(array $field, User $user): mixed
    {
        $type = $field['type'] ?? 'text';
        $label = strtolower($field['label'] ?? '');

        switch ($type) {
            case 'text':
                if (str_contains($label, 'phone')) {
                    return $user->phone ?? fake()->phoneNumber();
                } elseif (str_contains($label, 'company')) {
                    return fake()->company();
                } elseif (str_contains($label, 'job') || str_contains($label, 'title')) {
                    return fake()->jobTitle();
                }

                return fake()->sentence(3);

            case 'textarea':
                return fake()->paragraph(2);

            case 'select':
                if (isset($field['options'])) {
                    $options = explode("\n", $field['options']);

                    return fake()->randomElement($options);
                }

                return fake()->word();

            case 'checkbox':
                if (isset($field['options'])) {
                    $options = explode("\n", $field['options']);
                    $numSelected = fake()->numberBetween(1, min(3, count($options)));

                    return fake()->randomElements($options, $numSelected);
                }

                return [fake()->word()];

            case 'file':
                return 'resume_'.$user->id.'_'.fake()->uuid().'.pdf';

            default:
                return fake()->sentence();
        }
    }

    /**
     * Generate uploaded files data.
     */
    private function generateUploadedFiles(): ?array
    {
        if (fake()->boolean(30)) { // 30% chance of having files
            return [
                [
                    'filename' => 'document_'.fake()->uuid().'.pdf',
                    'original_name' => fake()->word().'_document.pdf',
                    'path' => 'uploads/registrations/'.fake()->uuid().'.pdf',
                    'size' => fake()->numberBetween(50000, 1500000),
                    'mime_type' => 'application/pdf',
                    'uploaded_at' => fake()->dateTimeBetween('-1 week', 'now')->format('Y-m-d H:i:s'),
                ],
            ];
        }

        return null;
    }

    /**
     * Add status-specific data to registration.
     */
    private function addStatusSpecificData(array $data, string $status): array
    {
        $reviewer = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'organizer']);
        })->inRandomOrder()->first();

        switch ($status) {
            case 'approved':
                $data['approved_at'] = fake()->dateTimeBetween('-2 weeks', 'now');
                $data['reviewed_by'] = $reviewer?->id;
                $data['organizer_notes'] = fake()->optional(0.3)->sentence();
                break;

            case 'rejected':
                $data['rejected_at'] = fake()->dateTimeBetween('-2 weeks', 'now');
                $data['reviewed_by'] = $reviewer?->id;
                $data['rejection_reason'] = fake()->randomElement([
                    'Application does not meet minimum requirements',
                    'Event capacity has been reached',
                    'Incomplete application form',
                    'Does not match target audience',
                    'Technical requirements not met',
                ]);
                $data['organizer_notes'] = fake()->optional(0.4)->sentence();
                break;

            case 'cancelled':
                $data['organizer_notes'] = 'Registration cancelled by user';
                break;

            case 'pending':
            default:
                // No additional data needed for pending status
                break;
        }

        return $data;
    }
}
