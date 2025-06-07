<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Register for') }} {{ $event->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Event Summary -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $event->title }}</h3>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p><strong>Date:</strong> {{ $event->start_date->format('l, F j, Y g:i A') }}</p>
                            @if($event->location)
                                <p><strong>Location:</strong> {{ $event->location }}</p>
                            @endif
                            @if($event->price > 0)
                                <p><strong>Price:</strong> ${{ number_format($event->price, 2) }}</p>
                            @else
                                <p><strong>Price:</strong> Free</p>
                            @endif
                        </div>
                    </div>

                    <!-- Registration Form -->
                    <form method="POST" action="{{ route('registrations.store', $event) }}" enctype="multipart/form-data">
                        @csrf

                        <div class="space-y-6">
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Registration Information</h4>
                                <p class="text-sm text-gray-600 mb-4">Please fill out the following information to complete your registration.</p>
                            </div>

                            @if($event->form_fields && count($event->form_fields) > 0)
                                @foreach($event->form_fields as $field)
                                    @php
                                        // Generate safe field name (same logic as controller)
                                        $fieldName = 'field_' . preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower(trim($field['label'])));
                                        $fieldName = preg_replace('/_+/', '_', $fieldName);
                                        $fieldName = rtrim($fieldName, '_');
                                    @endphp
                                    <div>
                                        <label for="{{ $fieldName }}" class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ $field['label'] }}
                                            @if($field['required'])
                                                <span class="text-red-500">*</span>
                                            @endif
                                        </label>

                                        @if($field['type'] === 'text')
                                            <input type="text"
                                                   id="{{ $fieldName }}"
                                                   name="{{ $fieldName }}"
                                                   value="{{ old($fieldName) }}"
                                                   placeholder="{{ $field['placeholder'] ?? '' }}"
                                                   @if($field['required']) required @endif
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error($fieldName) border-red-500 @enderror">

                                        @elseif($field['type'] === 'email')
                                            <input type="email"
                                                   id="{{ $fieldName }}"
                                                   name="{{ $fieldName }}"
                                                   value="{{ old($fieldName) }}"
                                                   placeholder="{{ $field['placeholder'] ?? '' }}"
                                                   @if($field['required']) required @endif
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error($fieldName) border-red-500 @enderror">

                                        @elseif($field['type'] === 'number')
                                            <input type="number"
                                                   id="{{ $fieldName }}"
                                                   name="{{ $fieldName }}"
                                                   value="{{ old($fieldName) }}"
                                                   placeholder="{{ $field['placeholder'] ?? '' }}"
                                                   @if($field['required']) required @endif
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error($fieldName) border-red-500 @enderror">

                                        @elseif($field['type'] === 'textarea')
                                            <textarea id="{{ $fieldName }}"
                                                      name="{{ $fieldName }}"
                                                      rows="4"
                                                      placeholder="{{ $field['placeholder'] ?? '' }}"
                                                      @if($field['required']) required @endif
                                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error($fieldName) border-red-500 @enderror">{{ old($fieldName) }}</textarea>
                                        
                                        @elseif($field['type'] === 'select')
                                            <select id="{{ $fieldName }}"
                                                    name="{{ $fieldName }}"
                                                    @if($field['required']) required @endif
                                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error($fieldName) border-red-500 @enderror">
                                                <option value="">{{ $field['required'] ? 'Please select an option' : 'Select an option (optional)' }}</option>
                                                @if(isset($field['options']))
                                                    @foreach(explode("\n", $field['options']) as $option)
                                                        @php $optionValue = trim($option); @endphp
                                                        <option value="{{ $optionValue }}" {{ old($fieldName) == $optionValue ? 'selected' : '' }}>
                                                            {{ $optionValue }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        
                                        @elseif($field['type'] === 'checkbox')
                                            <div class="space-y-2">
                                                @if(isset($field['options']))
                                                    @foreach(explode("\n", $field['options']) as $option)
                                                        @php $optionValue = trim($option); @endphp
                                                        <label class="flex items-center">
                                                            <input type="checkbox"
                                                                   name="{{ $fieldName }}[]"
                                                                   value="{{ $optionValue }}"
                                                                   {{ in_array($optionValue, old($fieldName, [])) ? 'checked' : '' }}
                                                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                            <span class="ml-2 text-sm text-gray-700">{{ $optionValue }}</span>
                                                        </label>
                                                    @endforeach
                                                @endif
                                            </div>

                                        @elseif($field['type'] === 'file')
                                            <input type="file"
                                                   id="{{ $fieldName }}"
                                                   name="{{ $fieldName }}"
                                                   @if($field['required']) required @endif
                                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                            <p class="mt-1 text-xs text-gray-500">Maximum file size: 10MB</p>
                                        @endif

                                        @error($fieldName)
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            @else
                                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                    <p class="text-sm text-blue-800">No additional information required. Click "Submit Registration" to complete your registration.</p>
                                </div>
                            @endif

                            <!-- Terms and Conditions -->
                            <div class="border-t border-gray-200 pt-6">
                                <div class="flex items-start">
                                    <input type="checkbox" 
                                           id="terms" 
                                           name="terms" 
                                           required
                                           class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <label for="terms" class="ml-2 text-sm text-gray-700">
                                        I agree to the terms and conditions and understand that 
                                        @if($event->requires_approval)
                                            my registration is subject to approval by the event organizer.
                                        @else
                                            my registration will be confirmed immediately.
                                        @endif
                                        <span class="text-red-500">*</span>
                                    </label>
                                </div>
                                @error('terms')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                                <a href="{{ route('events.show', $event) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Cancel
                                </a>
                                
                                <button type="submit" class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Submit Registration
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
