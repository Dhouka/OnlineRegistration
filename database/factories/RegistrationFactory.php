<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Registration>
 */
class RegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'approved', 'rejected', 'cancelled']);

        // Get random user with candidate role or any user
        $user = User::whereHas('roles', function ($query) {
            $query->where('name', 'candidate');
        })->inRandomOrder()->first() ?? User::inRandomOrder()->first();

        // Get random published event
        $event = Event::where('status', 'published')->inRandomOrder()->first();

        // Ensure we don't create duplicate registrations
        $maxAttempts = 10;
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $existingRegistration = Registration::where('user_id', $user?->id)
                ->where('event_id', $event?->id)
                ->exists();

            if (! $existingRegistration) {
                break;
            }

            // Try different user/event combination
            $user = User::whereHas('roles', function ($query) {
                $query->where('name', 'candidate');
            })->inRandomOrder()->first() ?? User::inRandomOrder()->first();

            $event = Event::where('status', 'published')->inRandomOrder()->first();
            $attempts++;
        }

        $formData = $this->generateFormData($event);
        $uploadedFiles = $this->generateUploadedFiles();

        $registration = [
            'user_id' => $user?->id ?? 1,
            'event_id' => $event?->id ?? 1,
            'status' => $status,
            'form_data' => $formData,
            'uploaded_files' => $uploadedFiles,
            'organizer_notes' => null,
            'rejection_reason' => null,
            'approved_at' => null,
            'rejected_at' => null,
            'reviewed_by' => null,
        ];

        // Set additional fields based on status
        if ($status === 'approved') {
            $registration['approved_at'] = fake()->dateTimeBetween('-1 month', 'now');
            $registration['reviewed_by'] = $this->getReviewer();
            $registration['organizer_notes'] = fake()->optional(0.3)->sentence();
        } elseif ($status === 'rejected') {
            $registration['rejected_at'] = fake()->dateTimeBetween('-1 month', 'now');
            $registration['reviewed_by'] = $this->getReviewer();
            $registration['rejection_reason'] = fake()->randomElement([
                'Application does not meet minimum requirements',
                'Event capacity has been reached',
                'Incomplete application form',
                'Does not match target audience',
                'Technical requirements not met',
                'Previous attendance conflicts',
            ]);
            $registration['organizer_notes'] = fake()->optional(0.5)->sentence();
        }

        return $registration;
    }

    /**
     * Generate realistic form data based on event form fields.
     */
    private function generateFormData(?Event $event): array
    {
        if (! $event || ! $event->form_fields) {
            return $this->getDefaultFormData();
        }

        $formData = [];

        foreach ($event->form_fields as $field) {
            $label = $field['label'] ?? 'Unknown Field';
            $type = $field['type'] ?? 'text';
            $required = $field['required'] ?? false;

            // Skip if not required and randomly decide to not fill
            if (! $required && fake()->boolean(30)) {
                continue;
            }

            switch ($type) {
                case 'text':
                    $formData[$label] = $this->generateTextResponse($label);
                    break;
                case 'textarea':
                    $formData[$label] = fake()->paragraph(2);
                    break;
                case 'select':
                    if (isset($field['options'])) {
                        $options = explode("\n", $field['options']);
                        $formData[$label] = fake()->randomElement($options);
                    }
                    break;
                case 'checkbox':
                    if (isset($field['options'])) {
                        $options = explode("\n", $field['options']);
                        $selected = fake()->randomElements($options, fake()->numberBetween(1, min(3, count($options))));
                        $formData[$label] = $selected;
                    }
                    break;
                case 'file':
                    // Don't generate actual files in factory, just indicate file was uploaded
                    $formData[$label] = 'File uploaded';
                    break;
                default:
                    $formData[$label] = fake()->sentence();
            }
        }

        return $formData;
    }

    /**
     * Generate text response based on field label.
     */
    private function generateTextResponse(string $label): string
    {
        $label = strtolower($label);

        if (str_contains($label, 'phone')) {
            return fake()->phoneNumber();
        } elseif (str_contains($label, 'company') || str_contains($label, 'organization')) {
            return fake()->company();
        } elseif (str_contains($label, 'job') || str_contains($label, 'title') || str_contains($label, 'position')) {
            return fake()->jobTitle();
        } elseif (str_contains($label, 'industry') || str_contains($label, 'field')) {
            return fake()->randomElement([
                'Technology', 'Marketing', 'Finance', 'Healthcare', 'Education',
                'Manufacturing', 'Retail', 'Consulting', 'Media', 'Non-profit',
            ]);
        } elseif (str_contains($label, 'website') || str_contains($label, 'url')) {
            return fake()->url();
        } else {
            return fake()->sentence(3);
        }
    }

    /**
     * Generate uploaded files data.
     */
    private function generateUploadedFiles(): ?array
    {
        if (fake()->boolean(40)) { // 40% chance of having uploaded files
            return [
                [
                    'filename' => 'resume_'.fake()->uuid().'.pdf',
                    'original_name' => 'John_Doe_Resume.pdf',
                    'path' => 'uploads/registrations/'.fake()->uuid().'.pdf',
                    'size' => fake()->numberBetween(100000, 2000000), // 100KB to 2MB
                    'mime_type' => 'application/pdf',
                    'uploaded_at' => fake()->dateTimeBetween('-1 week', 'now')->format('Y-m-d H:i:s'),
                ],
            ];
        }

        return null;
    }

    /**
     * Get default form data for events without specific form fields.
     */
    private function getDefaultFormData(): array
    {
        return [
            'Phone Number' => fake()->phoneNumber(),
            'Additional Comments' => fake()->optional(0.6)->paragraph(),
        ];
    }

    /**
     * Get a random reviewer (admin or organizer).
     */
    private function getReviewer(): ?int
    {
        $reviewer = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'organizer']);
        })->inRandomOrder()->first();

        return $reviewer?->id;
    }

    /**
     * Create a pending registration.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'approved_at' => null,
            'rejected_at' => null,
            'reviewed_by' => null,
            'organizer_notes' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Create an approved registration.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'reviewed_by' => $this->getReviewer(),
            'rejected_at' => null,
            'rejection_reason' => null,
            'organizer_notes' => fake()->optional(0.3)->sentence(),
        ]);
    }

    /**
     * Create a rejected registration.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejected_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'reviewed_by' => $this->getReviewer(),
            'approved_at' => null,
            'rejection_reason' => fake()->randomElement([
                'Application does not meet minimum requirements',
                'Event capacity has been reached',
                'Incomplete application form',
                'Does not match target audience',
            ]),
            'organizer_notes' => fake()->optional(0.5)->sentence(),
        ]);
    }

    /**
     * Create a cancelled registration.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'approved_at' => null,
            'rejected_at' => null,
            'reviewed_by' => null,
            'organizer_notes' => 'Registration cancelled by user',
            'rejection_reason' => null,
        ]);
    }

    /**
     * Create registration for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create registration for a specific event.
     */
    public function forEvent(Event $event): static
    {
        return $this->state(fn (array $attributes) => [
            'event_id' => $event->id,
            'form_data' => $this->generateFormData($event),
        ]);
    }
}
