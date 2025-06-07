<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $organizerRole = Role::create(['name' => 'organizer']);
        $candidateRole = Role::create(['name' => 'candidate']);

        // Create permissions
        $permissions = [
            // Event permissions
            'create events',
            'edit events',
            'delete events',
            'view events',
            'publish events',

            // Registration permissions
            'view registrations',
            'approve registrations',
            'reject registrations',
            'export registrations',

            // User permissions
            'manage users',
            'view users',

            // System permissions
            'access admin panel',
            'view statistics',
            'send notifications',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());

        $organizerRole->givePermissionTo([
            'create events',
            'edit events',
            'delete events',
            'view events',
            'publish events',
            'view registrations',
            'approve registrations',
            'reject registrations',
            'export registrations',
            'access admin panel',
            'view statistics',
            'send notifications',
        ]);

        $candidateRole->givePermissionTo([
            'view events',
        ]);

        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Create organizer user
        $organizer = User::create([
            'name' => 'Event Organizer',
            'email' => 'organizer@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $organizer->assignRole('organizer');

        // Create candidate user
        $candidate = User::create([
            'name' => 'John Candidate',
            'email' => 'candidate@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $candidate->assignRole('candidate');
    }
}
