<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $event->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Event Image -->
            @if($event->image_url)
                <div class="mb-8">
                    <img src="{{ Storage::url($event->image_url) }}" alt="{{ $event->title }}" class="w-full h-64 object-cover rounded-lg shadow-lg">
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Event Details -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-2">
                            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $event->title }}</h1>
                            
                            @if($event->short_description)
                                <p class="text-lg text-gray-600 mb-6">{{ $event->short_description }}</p>
                            @endif
                            
                            <div class="prose max-w-none mb-8">
                                {!! $event->description !!}
                            </div>
                        </div>
                        
                        <div class="lg:col-span-1">
                            <div class="bg-gray-50 rounded-lg p-6 space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Event Information</h3>
                                
                                <div class="space-y-3">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Start Date</p>
                                            <p class="text-sm text-gray-600">{{ $event->start_date->format('l, F j, Y') }}</p>
                                            <p class="text-sm text-gray-600">{{ $event->start_date->format('g:i A') }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">End Date</p>
                                            <p class="text-sm text-gray-600">{{ $event->end_date->format('l, F j, Y') }}</p>
                                            <p class="text-sm text-gray-600">{{ $event->end_date->format('g:i A') }}</p>
                                        </div>
                                    </div>
                                    
                                    @if($event->location)
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Location</p>
                                                <p class="text-sm text-gray-600">{{ $event->location }}</p>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Price</p>
                                            <p class="text-sm text-gray-600">
                                                @if($event->price > 0)
                                                    ${{ number_format($event->price, 2) }}
                                                @else
                                                    Free
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    
                                    @if($event->max_spots)
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Available Spots</p>
                                                <p class="text-sm text-gray-600">{{ $event->current_registrations }}/{{ $event->max_spots }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Registration Status and Actions -->
                                <div class="pt-4 border-t border-gray-200">
                                    @auth
                                        @if($userRegistration)
                                            <div class="space-y-3">
                                                <div class="flex items-center justify-center p-3 rounded-lg
                                                    @if($userRegistration->status === 'approved') bg-green-100 text-green-800
                                                    @elseif($userRegistration->status === 'pending') bg-yellow-100 text-yellow-800
                                                    @elseif($userRegistration->status === 'rejected') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    <span class="font-medium">
                                                        @if($userRegistration->status === 'approved')
                                                            ✓ Registration Approved
                                                        @elseif($userRegistration->status === 'pending')
                                                            ⏳ Registration Pending
                                                        @elseif($userRegistration->status === 'rejected')
                                                            ✗ Registration Rejected
                                                        @else
                                                            Registration {{ ucfirst($userRegistration->status) }}
                                                        @endif
                                                    </span>
                                                </div>
                                                
                                                @if($userRegistration->status === 'pending' || $userRegistration->status === 'approved')
                                                    <form action="{{ route('registrations.destroy', $userRegistration) }}" method="POST" class="w-full">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                                                onclick="return confirm('Are you sure you want to cancel your registration?')">
                                                            Cancel Registration
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @elseif($isRegistrationOpen && $hasAvailableSpots)
                                            <a href="{{ route('registrations.create', $event) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                Register Now
                                            </a>
                                        @elseif(!$hasAvailableSpots)
                                            <div class="w-full text-center p-3 bg-red-100 text-red-800 rounded-lg font-medium">
                                                Event is Full
                                            </div>
                                        @else
                                            <div class="w-full text-center p-3 bg-gray-100 text-gray-800 rounded-lg font-medium">
                                                Registration Closed
                                            </div>
                                        @endif
                                    @else
                                        <div class="space-y-3">
                                            <p class="text-sm text-gray-600 text-center">Please log in to register for this event.</p>
                                            <div class="flex space-x-2">
                                                <a href="{{ route('login') }}" class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                    Login
                                                </a>
                                                <a href="{{ route('register') }}" class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                    Sign Up
                                                </a>
                                            </div>
                                        </div>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
