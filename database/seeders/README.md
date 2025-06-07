# Database Seeders Documentation

This directory contains comprehensive seeders for the Online Registration Management System. The seeders create a complete dataset with realistic data for development and testing purposes.

## Seeder Overview

### 1. RoleSeeder
**Purpose**: Creates roles, permissions, and basic system users.

**What it creates**:
- **Roles**: admin, organizer, candidate
- **Permissions**: Complete set of permissions for event management, registration management, user management, and system access
- **Basic Users**:
  - Admin User (admin@example.com)
  - Event Organizer (organizer@example.com)
  - John Candidate (candidate@example.com)

**Default Password**: `password` for all users

### 2. UserSeeder
**Purpose**: Creates additional diverse users with detailed profiles.

**What it creates**:
- **3 additional organizers** with professional backgrounds
- **15 candidates** with diverse professional profiles and bios
- **1 additional admin** (System Administrator)
- **10 random candidates** generated via factory
- **3 random organizers** generated via factory

**Total Additional Users**: ~32 users

### 3. EventSeeder
**Purpose**: Creates initial sample events with detailed information.

**What it creates**:
- **Laravel Workshop**: Technical workshop with approval required
- **Digital Marketing Masterclass**: Business-focused event
- **Free Community Networking Event**: Open networking event

**Features**:
- Custom form fields for each event type
- Realistic pricing and capacity limits
- Proper date scheduling
- Rich descriptions with HTML content

### 4. AdditionalEventSeeder
**Purpose**: Creates diverse events using the EventFactory.

**What it creates**:
- **Workshop Events**: 10 events (5 published, 2 draft, 3 free)
- **Conference Events**: 5 events (3 published, 2 draft)
- **Networking Events**: 6 events (4 regular, 2 premium)
- **Mixed Events**: 8 events with various characteristics
- **Historical Events**: 3 completed/cancelled events
- **Unlimited Events**: 2 events with no registration limits

**Total Additional Events**: ~32 events

### 5. RegistrationSeeder
**Purpose**: Creates realistic registrations for all published events.

**What it creates**:
- Registrations for each published event (up to 80% capacity)
- Realistic status distribution:
  - **Approval Required Events**: 50% approved, 25% pending, 15% rejected, 10% cancelled
  - **No Approval Events**: 80% approved, 10% pending, 5% rejected, 5% cancelled
- **20 additional random registrations** via factory

**Features**:
- Form data matching event requirements
- File uploads simulation
- Reviewer assignments for processed registrations
- Realistic rejection reasons and organizer notes

## Factories

### UserFactory
**Enhanced Features**:
- Phone numbers (70% of users)
- Professional bios (60% of users)
- Avatar URLs (40% of users)
- Realistic fake data generation

### EventFactory
**Features**:
- Event type-based generation (workshop, conference, networking)
- Realistic pricing based on event type
- Dynamic form field generation
- Proper date scheduling
- Status and approval requirement distribution

### RegistrationFactory
**Features**:
- Form data generation based on event fields
- File upload simulation
- Status-specific data (approval dates, rejection reasons)
- Reviewer assignment for processed registrations

## Running the Seeders

### Run All Seeders
```bash
php artisan db:seed
```

### Run Specific Seeder
```bash
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=EventSeeder
php artisan db:seed --class=RegistrationSeeder
```

### Fresh Migration with Seeding
```bash
php artisan migrate:fresh --seed
```

## Expected Data Volume

After running all seeders, you should have approximately:

- **Users**: ~35-40 users
  - 2 admins
  - 6-7 organizers  
  - 25-30 candidates

- **Events**: ~35-40 events
  - Various statuses (draft, published, completed, cancelled)
  - Different types (workshops, conferences, networking)
  - Range of pricing (free to $1000)

- **Registrations**: ~100-150 registrations
  - Distributed across all published events
  - Various statuses with realistic distribution
  - Complete form data and file uploads

## Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.com | password |
| Admin | sysadmin@example.com | password |
| Organizer | organizer@example.com | password |
| Organizer | sarah.organizer@example.com | password |
| Organizer | michael.organizer@example.com | password |
| Organizer | emily.organizer@example.com | password |
| Candidate | candidate@example.com | password |
| Candidate | alex.candidate@example.com | password |
| Candidate | maria.candidate@example.com | password |
| ... | (and many more) | password |

## Seeder Dependencies

The seeders must be run in the following order due to foreign key dependencies:

1. **RoleSeeder** - Creates roles and basic users
2. **UserSeeder** - Creates additional users (requires roles)
3. **EventSeeder** - Creates initial events (requires organizers)
4. **AdditionalEventSeeder** - Creates more events (requires organizers)
5. **RegistrationSeeder** - Creates registrations (requires users and events)

## Customization

### Adding More Data
To increase the amount of seeded data, modify the factory calls in each seeder:

```php
// In UserSeeder.php
User::factory(20)->create(); // Increase from 10

// In AdditionalEventSeeder.php
Event::factory(15)->published()->create(); // Increase from 5

// In RegistrationSeeder.php
Registration::factory(50)->create(); // Increase from 20
```

### Modifying Event Types
Edit the `EventFactory.php` to add new event types or modify existing ones:

```php
$eventTypes = [
    'workshop' => [...],
    'conference' => [...],
    'networking' => [...],
    'webinar' => [...], // Add new type
];
```

## Testing Data Quality

After seeding, verify the data quality:

```bash
# Check user distribution
php artisan tinker
>>> User::with('roles')->get()->groupBy(fn($u) => $u->roles->first()?->name)->map->count()

# Check event status distribution  
>>> Event::groupBy('status')->selectRaw('status, count(*) as count')->get()

# Check registration status distribution
>>> Registration::groupBy('status')->selectRaw('status, count(*) as count')->get()
```

This comprehensive seeding setup provides a realistic dataset for development, testing, and demonstration of the Online Registration Management System.
