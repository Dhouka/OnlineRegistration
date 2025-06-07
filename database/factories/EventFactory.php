<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+1 week', '+3 months');
        $endDate = fake()->dateTimeBetween($startDate, $startDate->format('Y-m-d H:i:s').' +8 hours');
        $registrationStart = fake()->dateTimeBetween('-1 week', $startDate->format('Y-m-d H:i:s').' -1 day');
        $registrationEnd = fake()->dateTimeBetween($registrationStart, $startDate->format('Y-m-d H:i:s').' -1 hour');

        $eventTypes = [
            'workshop' => [
                'titles' => [
                    'Laravel Development Workshop',
                    'React.js Masterclass',
                    'Python for Data Science',
                    'Digital Marketing Bootcamp',
                    'UI/UX Design Workshop',
                    'Cybersecurity Fundamentals',
                    'Cloud Computing with AWS',
                    'Mobile App Development',
                ],
                'price_range' => [50, 200],
                'max_spots_range' => [20, 50],
            ],
            'conference' => [
                'titles' => [
                    'Tech Innovation Summit',
                    'Digital Transformation Conference',
                    'AI & Machine Learning Expo',
                    'Startup Pitch Competition',
                    'Women in Tech Conference',
                    'DevOps & Cloud Summit',
                    'Blockchain Technology Forum',
                    'Future of Work Conference',
                ],
                'price_range' => [100, 500],
                'max_spots_range' => [100, 300],
            ],
            'networking' => [
                'titles' => [
                    'Professional Networking Mixer',
                    'Industry Leaders Meetup',
                    'Startup Founders Gathering',
                    'Tech Community Social',
                    'Business Development Forum',
                    'Entrepreneur Coffee Chat',
                    'Innovation Hub Networking',
                    'Career Growth Meetup',
                ],
                'price_range' => [0, 50],
                'max_spots_range' => [50, 150],
            ],
        ];

        $eventType = fake()->randomElement(array_keys($eventTypes));
        $typeData = $eventTypes[$eventType];
        $title = fake()->randomElement($typeData['titles']);
        $price = fake()->randomFloat(2, $typeData['price_range'][0], $typeData['price_range'][1]);
        $maxSpots = fake()->numberBetween($typeData['max_spots_range'][0], $typeData['max_spots_range'][1]);

        // Get a random organizer or admin user
        $organizer = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'organizer']);
        })->inRandomOrder()->first();

        return [
            'title' => $title,
            'description' => $this->generateDescription($eventType),
            'short_description' => fake()->sentence(12),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'registration_start' => $registrationStart,
            'registration_end' => $registrationEnd,
            'max_spots' => $maxSpots,
            'current_registrations' => 0,
            'form_fields' => $this->generateFormFields($eventType),
            'location' => fake()->randomElement([
                'Tech Hub Conference Center',
                'Downtown Convention Center',
                'Business Innovation Center',
                'University Auditorium',
                'Community Center Hall',
                'Hotel Conference Room',
                'Online (Virtual Event)',
                'Startup Incubator Space',
            ]),
            'price' => $price,
            'image_url' => fake()->optional(0.6)->imageUrl(800, 400, 'business'),
            'status' => fake()->randomElement(['draft', 'published', 'published', 'published']), // More likely to be published
            'requires_approval' => fake()->boolean(70), // 70% chance of requiring approval
            'created_by' => $organizer?->id ?? 1,
        ];
    }

    /**
     * Generate event description based on type.
     */
    private function generateDescription(string $eventType): string
    {
        $descriptions = [
            'workshop' => [
                '<p>Join us for this hands-on workshop designed to enhance your skills and knowledge. Our expert instructors will guide you through practical exercises and real-world applications.</p>',
                '<p><strong>What you\'ll learn:</strong></p><ul><li>Fundamental concepts and best practices</li><li>Hands-on experience with industry tools</li><li>Real-world project implementation</li><li>Q&A session with experts</li></ul>',
                '<p>Perfect for beginners and intermediate practitioners looking to advance their careers.</p>',
            ],
            'conference' => [
                '<p>This premier conference brings together industry leaders, innovators, and professionals to share insights and explore the latest trends.</p>',
                '<p><strong>Conference highlights:</strong></p><ul><li>Keynote presentations from industry experts</li><li>Panel discussions on current trends</li><li>Networking opportunities</li><li>Exhibition showcase</li><li>Interactive workshops</li></ul>',
                '<p>Don\'t miss this opportunity to connect with peers and gain valuable insights.</p>',
            ],
            'networking' => [
                '<p>Connect with like-minded professionals in a relaxed and welcoming environment. This networking event is designed to foster meaningful connections and collaborations.</p>',
                '<p><strong>Event features:</strong></p><ul><li>Welcome reception with refreshments</li><li>Structured networking sessions</li><li>Industry insights and discussions</li><li>Business card exchange</li><li>Follow-up connection opportunities</li></ul>',
                '<p>Whether you\'re looking to expand your network or explore new opportunities, this event is for you.</p>',
            ],
        ];

        return implode('', $descriptions[$eventType]);
    }

    /**
     * Generate form fields based on event type.
     */
    private function generateFormFields(string $eventType): array
    {
        $commonFields = [
            [
                'label' => 'Phone Number',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Your contact number',
            ],
        ];

        $typeSpecificFields = [
            'workshop' => [
                [
                    'label' => 'Experience Level',
                    'type' => 'select',
                    'required' => true,
                    'options' => "Beginner\nIntermediate\nAdvanced",
                ],
                [
                    'label' => 'Previous Experience',
                    'type' => 'textarea',
                    'required' => false,
                    'placeholder' => 'Tell us about your relevant experience...',
                ],
                [
                    'label' => 'Resume/Portfolio',
                    'type' => 'file',
                    'required' => false,
                ],
            ],
            'conference' => [
                [
                    'label' => 'Company/Organization',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Your company name',
                ],
                [
                    'label' => 'Job Title',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Your current position',
                ],
                [
                    'label' => 'Dietary Restrictions',
                    'type' => 'textarea',
                    'required' => false,
                    'placeholder' => 'Any dietary restrictions or allergies?',
                ],
                [
                    'label' => 'Session Interests',
                    'type' => 'checkbox',
                    'required' => false,
                    'options' => "Keynote Presentations\nTechnical Workshops\nPanel Discussions\nNetworking Sessions\nExhibition Tours",
                ],
            ],
            'networking' => [
                [
                    'label' => 'Industry/Field',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'e.g., Technology, Marketing, Finance',
                ],
                [
                    'label' => 'Years of Experience',
                    'type' => 'select',
                    'required' => true,
                    'options' => "0-2 years\n3-5 years\n6-10 years\n10+ years",
                ],
                [
                    'label' => 'Networking Goals',
                    'type' => 'textarea',
                    'required' => false,
                    'placeholder' => 'What do you hope to achieve from this event?',
                ],
            ],
        ];

        return array_merge($commonFields, $typeSpecificFields[$eventType]);
    }

    /**
     * Indicate that the event should be published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    /**
     * Indicate that the event should be a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the event should require approval.
     */
    public function requiresApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_approval' => true,
        ]);
    }

    /**
     * Indicate that the event should not require approval.
     */
    public function noApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_approval' => false,
        ]);
    }

    /**
     * Indicate that the event should be free.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => 0.00,
        ]);
    }
}
