<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdditionalEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating additional events using factory...');

        // Ensure we have organizers to create events
        $organizers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'organizer']);
        })->get();

        if ($organizers->isEmpty()) {
            $this->command->warn('No organizers found. Skipping additional event creation.');
            return;
        }

        // Create various types of events
        $this->createWorkshopEvents($organizers);
        $this->createConferenceEvents($organizers);
        $this->createNetworkingEvents($organizers);
        $this->createMixedEvents($organizers);

        $this->command->info('Additional events created successfully!');
        $this->command->info('Total events: ' . Event::count());
    }

    /**
     * Create workshop-style events.
     */
    private function createWorkshopEvents($organizers): void
    {
        // Published workshops
        Event::factory(5)
            ->published()
            ->requiresApproval()
            ->state(function () use ($organizers) {
                return [
                    'created_by' => $organizers->random()->id,
                    'price' => fake()->randomFloat(2, 50, 200),
                    'max_spots' => fake()->numberBetween(15, 40),
                ];
            })
            ->create();

        // Draft workshops
        Event::factory(2)
            ->draft()
            ->state(function () use ($organizers) {
                return [
                    'created_by' => $organizers->random()->id,
                    'price' => fake()->randomFloat(2, 75, 250),
                    'max_spots' => fake()->numberBetween(20, 35),
                ];
            })
            ->create();

        // Free workshops
        Event::factory(3)
            ->published()
            ->free()
            ->noApproval()
            ->state(function () use ($organizers) {
                return [
                    'created_by' => $organizers->random()->id,
                    'max_spots' => fake()->numberBetween(25, 60),
                ];
            })
            ->create();
    }

    /**
     * Create conference-style events.
     */
    private function createConferenceEvents($organizers): void
    {
        // Large published conferences
        Event::factory(3)
            ->published()
            ->requiresApproval()
            ->state(function () use ($organizers) {
                return [
                    'created_by' => $organizers->random()->id,
                    'price' => fake()->randomFloat(2, 200, 800),
                    'max_spots' => fake()->numberBetween(100, 500),
                    'start_date' => fake()->dateTimeBetween('+2 weeks', '+6 months'),
                    'end_date' => fake()->dateTimeBetween('+2 weeks', '+6 months'),
                ];
            })
            ->create();

        // Draft conferences (being planned)
        Event::factory(2)
            ->draft()
            ->state(function () use ($organizers) {
                return [
                    'created_by' => $organizers->random()->id,
                    'price' => fake()->randomFloat(2, 300, 1000),
                    'max_spots' => fake()->numberBetween(150, 400),
                ];
            })
            ->create();
    }

    /**
     * Create networking events.
     */
    private function createNetworkingEvents($organizers): void
    {
        // Regular networking events
        Event::factory(4)
            ->published()
            ->noApproval()
            ->state(function () use ($organizers) {
                return [
                    'created_by' => $organizers->random()->id,
                    'price' => fake()->randomFloat(2, 0, 50),
                    'max_spots' => fake()->numberBetween(50, 150),
                ];
            })
            ->create();

        // Premium networking events
        Event::factory(2)
            ->published()
            ->requiresApproval()
            ->state(function () use ($organizers) {
                return [
                    'created_by' => $organizers->random()->id,
                    'price' => fake()->randomFloat(2, 100, 300),
                    'max_spots' => fake()->numberBetween(30, 80),
                ];
            })
            ->create();
    }

    /**
     * Create mixed events with various characteristics.
     */
    private function createMixedEvents($organizers): void
    {
        // Events with different statuses and characteristics
        Event::factory(8)
            ->state(function () use ($organizers) {
                $status = fake()->randomElement(['draft', 'published', 'published', 'published']); // More likely to be published
                $requiresApproval = fake()->boolean(60); // 60% chance of requiring approval
                $price = fake()->randomFloat(2, 0, 500);
                $maxSpots = fake()->numberBetween(20, 200);

                return [
                    'created_by' => $organizers->random()->id,
                    'status' => $status,
                    'requires_approval' => $requiresApproval,
                    'price' => $price,
                    'max_spots' => $maxSpots,
                ];
            })
            ->create();

        // Some events that are completed or cancelled
        Event::factory(3)
            ->state(function () use ($organizers) {
                $status = fake()->randomElement(['completed', 'cancelled']);
                
                return [
                    'created_by' => $organizers->random()->id,
                    'status' => $status,
                    'start_date' => fake()->dateTimeBetween('-6 months', '-1 week'),
                    'end_date' => fake()->dateTimeBetween('-6 months', '-1 week'),
                    'registration_start' => fake()->dateTimeBetween('-8 months', '-2 months'),
                    'registration_end' => fake()->dateTimeBetween('-2 months', '-2 weeks'),
                    'price' => fake()->randomFloat(2, 0, 300),
                    'max_spots' => fake()->numberBetween(25, 100),
                    'current_registrations' => fake()->numberBetween(10, 80),
                ];
            })
            ->create();

        // Events with no registration limits
        Event::factory(2)
            ->published()
            ->state(function () use ($organizers) {
                return [
                    'created_by' => $organizers->random()->id,
                    'max_spots' => null, // No limit
                    'price' => fake()->randomFloat(2, 0, 100),
                    'requires_approval' => false,
                ];
            })
            ->create();
    }
}
