<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');

        // Seed in specific order due to dependencies
        $this->call([
            RoleSeeder::class,            // Creates roles, permissions, and basic users (admin, organizer, candidate)
            UserSeeder::class,            // Creates additional diverse users
            EventSeeder::class,           // Creates sample events (existing events)
            AdditionalEventSeeder::class, // Creates more events using factory
            RegistrationSeeder::class,    // Creates registrations for events
        ]);

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('Users: '.\App\Models\User::count());
        $this->command->info('Events: '.\App\Models\Event::count());
        $this->command->info('Registrations: '.\App\Models\Registration::count());
        $this->command->info('');
        $this->command->info('🔑 Default login credentials:');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Organizer: organizer@example.com / password');
        $this->command->info('Candidate: candidate@example.com / password');
    }
}
