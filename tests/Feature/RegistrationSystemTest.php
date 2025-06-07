<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'organizer']);
        Role::create(['name' => 'candidate']);
    }

    public function test_public_can_view_events_page()
    {
        $response = $this->get('/events');
        $response->assertStatus(200);
    }

    public function test_user_can_register_for_event()
    {
        // Create users
        $organizer = User::factory()->create();
        $organizer->assignRole('organizer');

        $candidate = User::factory()->create();
        $candidate->assignRole('candidate');

        // Create event
        $event = Event::create([
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(7)->addHours(8),
            'status' => 'published',
            'requires_approval' => false,
            'created_by' => $organizer->id,
            'form_fields' => [
                [
                    'label' => 'Name',
                    'type' => 'text',
                    'required' => true,
                ],
            ],
        ]);

        // Ensure event is saved and has an ID
        $this->assertNotNull($event->id);
        $this->assertDatabaseHas('events', ['id' => $event->id]);

        // Test registration
        $response = $this->actingAs($candidate)
            ->post("/events/{$event->id}/register", [
                'field_Name' => 'John Doe',
                '_token' => csrf_token(),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('registrations', [
            'user_id' => $candidate->id,
            'event_id' => $event->id,
            'status' => 'approved', // Auto-approved since requires_approval is false
        ]);
    }

    public function test_organizer_can_approve_registration()
    {
        // Create users
        $organizer = User::factory()->create();
        $organizer->assignRole('organizer');

        $candidate = User::factory()->create();
        $candidate->assignRole('candidate');

        // Create event
        $event = Event::create([
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(7)->addHours(8),
            'status' => 'published',
            'requires_approval' => true,
            'created_by' => $organizer->id,
        ]);

        // Create registration
        $registration = Registration::create([
            'user_id' => $candidate->id,
            'event_id' => $event->id,
            'status' => 'pending',
            'form_data' => ['name' => 'John Doe'],
        ]);

        // Test approval
        $registration->approve($organizer, 'Approved!');

        $this->assertEquals('approved', $registration->fresh()->status);
        $this->assertNotNull($registration->fresh()->approved_at);
        $this->assertEquals(1, $event->fresh()->current_registrations);
    }

    public function test_organizer_can_reject_registration()
    {
        // Create users
        $organizer = User::factory()->create();
        $organizer->assignRole('organizer');

        $candidate = User::factory()->create();
        $candidate->assignRole('candidate');

        // Create event
        $event = Event::create([
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(7)->addHours(8),
            'status' => 'published',
            'requires_approval' => true,
            'created_by' => $organizer->id,
        ]);

        // Create registration
        $registration = Registration::create([
            'user_id' => $candidate->id,
            'event_id' => $event->id,
            'status' => 'pending',
            'form_data' => ['name' => 'John Doe'],
        ]);

        // Test rejection
        $registration->reject($organizer, 'Not qualified', 'Sorry');

        $this->assertEquals('rejected', $registration->fresh()->status);
        $this->assertNotNull($registration->fresh()->rejected_at);
        $this->assertEquals('Not qualified', $registration->fresh()->rejection_reason);
    }

    public function test_event_registration_validation()
    {
        // Create event with max spots
        $organizer = User::factory()->create();
        $organizer->assignRole('organizer');

        $event = Event::create([
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(7)->addHours(8),
            'status' => 'published',
            'max_spots' => 1,
            'current_registrations' => 1,
            'created_by' => $organizer->id,
        ]);

        $this->assertFalse($event->hasAvailableSpots());
        $this->assertFalse($event->isRegistrationOpen());
    }

    public function test_admin_can_access_all_events()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $organizer = User::factory()->create();
        $organizer->assignRole('organizer');

        // Create event by organizer
        $event = Event::create([
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(7)->addHours(8),
            'status' => 'published',
            'created_by' => $organizer->id,
        ]);

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->canAccessPanel(app('filament')->getDefaultPanel()));
    }

    public function test_candidate_cannot_access_admin_panel()
    {
        $candidate = User::factory()->create();
        $candidate->assignRole('candidate');

        $this->assertFalse($candidate->canAccessPanel(app('filament')->getDefaultPanel()));
    }

    public function test_dynamic_form_fields_work()
    {
        $organizer = User::factory()->create();
        $organizer->assignRole('organizer');

        $event = Event::create([
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(7)->addHours(8),
            'status' => 'published',
            'created_by' => $organizer->id,
            'form_fields' => [
                [
                    'label' => 'Experience Level',
                    'type' => 'select',
                    'required' => true,
                    'options' => "Beginner\nIntermediate\nAdvanced",
                ],
                [
                    'label' => 'Comments',
                    'type' => 'textarea',
                    'required' => false,
                ],
            ],
        ]);

        $this->assertIsArray($event->form_fields);
        $this->assertCount(2, $event->form_fields);
        $this->assertEquals('Experience Level', $event->form_fields[0]['label']);
        $this->assertEquals('select', $event->form_fields[0]['type']);
    }
}
