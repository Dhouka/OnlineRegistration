<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class EventsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');

        // Base queries
        $eventsQuery = Event::query();
        $registrationsQuery = Registration::query();
        $usersQuery = User::query();

        // Filter for non-admin users
        if (! $isAdmin) {
            $eventsQuery->where('created_by', $user->id);
            $registrationsQuery->whereHas('event', function ($q) use ($user) {
                $q->where('created_by', $user->id);
            });
        }

        return [
            Stat::make('Total Events', $eventsQuery->count())
                ->description($isAdmin ? 'All events in system' : 'Your events')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Published Events', $eventsQuery->where('status', 'published')->count())
                ->description('Currently active')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),

            Stat::make('Total Registrations', $registrationsQuery->count())
                ->description($isAdmin ? 'All registrations' : 'For your events')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Pending Approvals', $registrationsQuery->where('status', 'pending')->count())
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            $isAdmin ?
                Stat::make('Total Users', $usersQuery->count())
                    ->description('Registered users')
                    ->descriptionIcon('heroicon-m-users')
                    ->color('gray') :
                Stat::make('Approved Registrations', $registrationsQuery->where('status', 'approved')->count())
                    ->description('Successfully approved')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),
        ];
    }
}
