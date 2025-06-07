<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizer = User::where('email', 'organizer@example.com')->first();

        if (! $organizer) {
            return;
        }

        $events = [
            [
                'title' => 'Laravel Workshop: Building Modern Web Applications',
                'description' => '<p>Join us for an intensive Laravel workshop where you\'ll learn to build modern web applications from scratch. This hands-on session covers everything from basic routing to advanced features like queues and real-time notifications.</p><p><strong>What you\'ll learn:</strong></p><ul><li>Laravel fundamentals and MVC architecture</li><li>Database migrations and Eloquent ORM</li><li>Authentication and authorization</li><li>API development with Laravel</li><li>Testing your applications</li></ul>',
                'short_description' => 'Learn to build modern web applications with Laravel in this comprehensive hands-on workshop.',
                'start_date' => now()->addDays(14)->setTime(9, 0),
                'end_date' => now()->addDays(14)->setTime(17, 0),
                'registration_start' => now(),
                'registration_end' => now()->addDays(10),
                'max_spots' => 30,
                'location' => 'Tech Hub Conference Center, Room A',
                'price' => 99.00,
                'status' => 'published',
                'requires_approval' => true,
                'created_by' => $organizer->id,
                'form_fields' => [
                    [
                        'label' => 'Programming Experience',
                        'type' => 'select',
                        'required' => true,
                        'options' => "Beginner\nIntermediate\nAdvanced",
                    ],
                    [
                        'label' => 'Previous Laravel Experience',
                        'type' => 'textarea',
                        'required' => false,
                        'placeholder' => 'Tell us about your previous experience with Laravel...',
                    ],
                    [
                        'label' => 'Resume/CV',
                        'type' => 'file',
                        'required' => false,
                    ],
                ],
            ],
            [
                'title' => 'Digital Marketing Masterclass',
                'description' => '<p>Discover the latest digital marketing strategies and tools to grow your business online. This comprehensive masterclass covers SEO, social media marketing, content strategy, and analytics.</p><p><strong>Topics covered:</strong></p><ul><li>Search Engine Optimization (SEO)</li><li>Social Media Marketing</li><li>Content Marketing Strategy</li><li>Google Analytics and Data Analysis</li><li>Email Marketing Campaigns</li><li>Paid Advertising (Google Ads, Facebook Ads)</li></ul>',
                'short_description' => 'Master digital marketing strategies to grow your business online with expert-led sessions.',
                'start_date' => now()->addDays(21)->setTime(10, 0),
                'end_date' => now()->addDays(21)->setTime(16, 0),
                'registration_start' => now(),
                'registration_end' => now()->addDays(18),
                'max_spots' => 50,
                'location' => 'Business Center, Main Auditorium',
                'price' => 149.00,
                'status' => 'published',
                'requires_approval' => false,
                'created_by' => $organizer->id,
                'form_fields' => [
                    [
                        'label' => 'Company/Organization',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Your company name',
                    ],
                    [
                        'label' => 'Current Marketing Challenges',
                        'type' => 'textarea',
                        'required' => true,
                        'placeholder' => 'What marketing challenges are you currently facing?',
                    ],
                    [
                        'label' => 'Preferred Session Topics',
                        'type' => 'checkbox',
                        'required' => false,
                        'options' => "SEO Optimization\nSocial Media Strategy\nContent Marketing\nPaid Advertising\nEmail Marketing\nAnalytics & Reporting",
                    ],
                ],
            ],
            [
                'title' => 'Free Community Networking Event',
                'description' => '<p>Join fellow professionals for an evening of networking, knowledge sharing, and community building. This free event is perfect for making new connections and learning from industry experts.</p><p><strong>Event highlights:</strong></p><ul><li>Welcome reception with refreshments</li><li>Lightning talks from industry leaders</li><li>Structured networking sessions</li><li>Panel discussion on industry trends</li><li>Closing mixer</li></ul>',
                'short_description' => 'Connect with fellow professionals in this free networking event with industry insights.',
                'start_date' => now()->addDays(7)->setTime(18, 0),
                'end_date' => now()->addDays(7)->setTime(21, 0),
                'registration_start' => now(),
                'registration_end' => now()->addDays(6),
                'max_spots' => 100,
                'location' => 'Downtown Convention Center',
                'price' => 0.00,
                'status' => 'published',
                'requires_approval' => false,
                'created_by' => $organizer->id,
                'form_fields' => [
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
                        'placeholder' => 'What do you hope to achieve from this networking event?',
                    ],
                ],
            ],
        ];

        foreach ($events as $eventData) {
            Event::create($eventData);
        }
    }
}
