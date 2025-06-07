<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Registrations') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($registrations->count() > 0)
                        <div class="space-y-6">
                            @foreach($registrations as $registration)
                                <div class="border border-gray-200 rounded-lg p-6">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                                <a href="{{ route('events.show', $registration->event) }}" class="hover:text-blue-600">
                                                    {{ $registration->event->title }}
                                                </a>
                                            </h3>
                                            
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 mb-4">
                                                <div>
                                                    <p><strong>Event Date:</strong> {{ $registration->event->start_date->format('M j, Y g:i A') }}</p>
                                                    @if($registration->event->location)
                                                        <p><strong>Location:</strong> {{ $registration->event->location }}</p>
                                                    @endif
                                                </div>
                                                <div>
                                                    <p><strong>Registered:</strong> {{ $registration->created_at->format('M j, Y g:i A') }}</p>
                                                    @if($registration->approved_at)
                                                        <p><strong>Approved:</strong> {{ $registration->approved_at->format('M j, Y g:i A') }}</p>
                                                    @elseif($registration->rejected_at)
                                                        <p><strong>Rejected:</strong> {{ $registration->rejected_at->format('M j, Y g:i A') }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <!-- Registration Status -->
                                            <div class="flex items-center space-x-4 mb-4">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                                    @if($registration->status === 'approved') bg-green-100 text-green-800
                                                    @elseif($registration->status === 'pending') bg-yellow-100 text-yellow-800
                                                    @elseif($registration->status === 'rejected') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    @if($registration->status === 'approved')
                                                        ✓ Approved
                                                    @elseif($registration->status === 'pending')
                                                        ⏳ Pending Review
                                                    @elseif($registration->status === 'rejected')
                                                        ✗ Rejected
                                                    @else
                                                        {{ ucfirst($registration->status) }}
                                                    @endif
                                                </span>
                                                
                                                @if($registration->event->price > 0)
                                                    <span class="text-sm text-gray-600">
                                                        Price: ${{ number_format($registration->event->price, 2) }}
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <!-- Rejection Reason -->
                                            @if($registration->status === 'rejected' && $registration->rejection_reason)
                                                <div class="bg-red-50 border border-red-200 rounded-md p-3 mb-4">
                                                    <p class="text-sm text-red-800">
                                                        <strong>Rejection Reason:</strong> {{ $registration->rejection_reason }}
                                                    </p>
                                                </div>
                                            @endif
                                            
                                            <!-- Organizer Notes -->
                                            @if($registration->organizer_notes)
                                                <div class="bg-blue-50 border border-blue-200 rounded-md p-3 mb-4">
                                                    <p class="text-sm text-blue-800">
                                                        <strong>Organizer Notes:</strong> {{ $registration->organizer_notes }}
                                                    </p>
                                                </div>
                                            @endif
                                            
                                            <!-- Form Data -->
                                            @if($registration->form_data && count($registration->form_data) > 0)
                                                <div class="bg-gray-50 rounded-md p-3 mb-4">
                                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Registration Details:</h4>
                                                    <div class="space-y-1">
                                                        @foreach($registration->form_data as $field => $value)
                                                            <p class="text-sm text-gray-600">
                                                                <strong>{{ $field }}:</strong> 
                                                                @if(is_array($value))
                                                                    {{ implode(', ', $value) }}
                                                                @else
                                                                    {{ $value }}
                                                                @endif
                                                            </p>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <!-- Uploaded Files -->
                                            @if($registration->uploaded_files && count($registration->uploaded_files) > 0)
                                                <div class="bg-gray-50 rounded-md p-3 mb-4">
                                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Uploaded Files:</h4>
                                                    <div class="space-y-1">
                                                        @foreach($registration->uploaded_files as $field => $file)
                                                            <p class="text-sm text-gray-600">
                                                                <strong>{{ $field }}:</strong> 
                                                                <a href="{{ Storage::url($file['path']) }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                                    {{ $file['original_name'] }}
                                                                </a>
                                                                <span class="text-gray-500">({{ number_format($file['size'] / 1024, 1) }} KB)</span>
                                                            </p>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="flex flex-col space-y-2 ml-4">
                                            <a href="{{ route('events.show', $registration->event) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                View Event
                                            </a>
                                            
                                            @if(in_array($registration->status, ['pending', 'approved']))
                                                <form action="{{ route('registrations.destroy', $registration) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                            onclick="return confirm('Are you sure you want to cancel this registration?')">
                                                        Cancel
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-8">
                            {{ $registrations->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No registrations yet</h3>
                            <p class="mt-1 text-sm text-gray-500">You haven't registered for any events yet.</p>
                            <div class="mt-6">
                                <a href="{{ route('events.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Browse Events
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
