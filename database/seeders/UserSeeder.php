<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create additional organizers
        $organizers = [
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.organizer@example.com',
                'phone' => '+1-555-0123',
                'bio' => 'Experienced event organizer specializing in tech conferences and workshops. Over 10 years of experience in the industry.',
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'michael.organizer@example.com',
                'phone' => '+1-555-0124',
                'bio' => 'Digital marketing expert and event coordinator. Passionate about bringing professionals together for networking and learning.',
            ],
            [
                'name' => 'Emily Rodriguez',
                'email' => 'emily.organizer@example.com',
                'phone' => '+1-555-0125',
                'bio' => 'Community builder and workshop facilitator with expertise in professional development and skill-building events.',
            ],
        ];

        foreach ($organizers as $organizerData) {
            $organizer = User::create([
                'name' => $organizerData['name'],
                'email' => $organizerData['email'],
                'password' => Hash::make('password'),
                'phone' => $organizerData['phone'],
                'bio' => $organizerData['bio'],
                'email_verified_at' => now(),
            ]);
            $organizer->assignRole('organizer');
        }

        // Create additional candidates with diverse backgrounds
        $candidates = [
            [
                'name' => 'Alex Thompson',
                'email' => 'alex.candidate@example.com',
                'phone' => '+1-555-0201',
                'bio' => 'Software developer with 5 years of experience in web development. Always eager to learn new technologies.',
            ],
            [
                'name' => 'Maria Garcia',
                'email' => 'maria.candidate@example.com',
                'phone' => '+1-555-0202',
                'bio' => 'Marketing professional looking to expand skills in digital marketing and data analytics.',
            ],
            [
                'name' => 'David Kim',
                'email' => 'david.candidate@example.com',
                'phone' => '+1-555-0203',
                'bio' => 'Recent computer science graduate interested in machine learning and artificial intelligence.',
            ],
            [
                'name' => 'Jennifer Wilson',
                'email' => 'jennifer.candidate@example.com',
                'phone' => '+1-555-0204',
                'bio' => 'Project manager with experience in agile methodologies. Interested in leadership and team management workshops.',
            ],
            [
                'name' => 'Robert Brown',
                'email' => 'robert.candidate@example.com',
                'phone' => '+1-555-0205',
                'bio' => 'Entrepreneur and startup founder. Always looking for networking opportunities and business development insights.',
            ],
            [
                'name' => 'Lisa Anderson',
                'email' => 'lisa.candidate@example.com',
                'phone' => '+1-555-0206',
                'bio' => 'UX/UI designer passionate about creating user-centered designs. Interested in design thinking workshops.',
            ],
            [
                'name' => 'James Taylor',
                'email' => 'james.candidate@example.com',
                'phone' => '+1-555-0207',
                'bio' => 'Data scientist with expertise in Python and machine learning. Looking to expand knowledge in cloud computing.',
            ],
            [
                'name' => 'Amanda Davis',
                'email' => 'amanda.candidate@example.com',
                'phone' => '+1-555-0208',
                'bio' => 'Digital marketing specialist with focus on social media and content marketing. Seeking advanced analytics training.',
            ],
            [
                'name' => 'Kevin Martinez',
                'email' => 'kevin.candidate@example.com',
                'phone' => '+1-555-0209',
                'bio' => 'Full-stack developer interested in DevOps and cloud infrastructure. Active in the local tech community.',
            ],
            [
                'name' => 'Rachel Green',
                'email' => 'rachel.candidate@example.com',
                'phone' => '+1-555-0210',
                'bio' => 'Business analyst with experience in process improvement. Looking to learn more about data visualization.',
            ],
            [
                'name' => 'Christopher Lee',
                'email' => 'christopher.candidate@example.com',
                'phone' => '+1-555-0211',
                'bio' => 'Cybersecurity professional with 8 years of experience. Interested in staying updated with latest security trends.',
            ],
            [
                'name' => 'Nicole White',
                'email' => 'nicole.candidate@example.com',
                'phone' => '+1-555-0212',
                'bio' => 'Product manager with background in tech startups. Passionate about user research and product strategy.',
            ],
            [
                'name' => 'Daniel Johnson',
                'email' => 'daniel.candidate@example.com',
                'phone' => '+1-555-0213',
                'bio' => 'Mobile app developer specializing in React Native and Flutter. Always exploring new mobile technologies.',
            ],
            [
                'name' => 'Stephanie Clark',
                'email' => 'stephanie.candidate@example.com',
                'phone' => '+1-555-0214',
                'bio' => 'HR professional interested in organizational development and employee engagement strategies.',
            ],
            [
                'name' => 'Ryan Miller',
                'email' => 'ryan.candidate@example.com',
                'phone' => '+1-555-0215',
                'bio' => 'Sales professional looking to enhance skills in CRM systems and sales automation tools.',
            ],
        ];

        foreach ($candidates as $candidateData) {
            $candidate = User::create([
                'name' => $candidateData['name'],
                'email' => $candidateData['email'],
                'password' => Hash::make('password'),
                'phone' => $candidateData['phone'],
                'bio' => $candidateData['bio'],
                'email_verified_at' => now(),
            ]);
            $candidate->assignRole('candidate');
        }

        // Create additional admin user
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'sysadmin@example.com',
            'password' => Hash::make('password'),
            'phone' => '+1-555-0001',
            'bio' => 'System administrator responsible for platform maintenance and user management.',
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Create some users using the factory for variety
        User::factory(10)->create()->each(function ($user) {
            $user->assignRole('candidate');
        });

        // Create a few more organizers using factory
        User::factory(3)->create()->each(function ($user) {
            $user->assignRole('organizer');
        });

        $this->command->info('Created additional users:');
        $this->command->info('- 3 additional organizers');
        $this->command->info('- 15 candidates with detailed profiles');
        $this->command->info('- 1 additional admin');
        $this->command->info('- 10 random candidates via factory');
        $this->command->info('- 3 random organizers via factory');
    }
}
