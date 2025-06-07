<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    /**
     * Generate a safe field name from a label.
     */
    private function generateFieldName(string $label): string
    {
        // Convert to lowercase, replace spaces and special characters with underscores
        $fieldName = 'field_'.preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower(trim($label)));
        // Remove multiple consecutive underscores and trailing underscores
        $fieldName = preg_replace('/_+/', '_', $fieldName);
        $fieldName = rtrim($fieldName, '_');

        return $fieldName;
    }

    /**
     * Show the registration form for an event.
     */
    public function create(Event $event): View|RedirectResponse
    {
        // Check if event is published and registration is open
        if ($event->status !== 'published' || ! $event->isRegistrationOpen()) {
            return redirect()->to("/events/{$event->id}")
                ->with('error', 'Registration is not available for this event.');
        }

        // Check if user is already registered
        $existingRegistration = $event->registrations()
            ->where('user_id', Auth::id())
            ->first();

        if ($existingRegistration) {
            return redirect()->to("/events/{$event->id}")
                ->with('info', 'You are already registered for this event.');
        }

        // Check if event has available spots
        if (! $event->hasAvailableSpots()) {
            return redirect()->to("/events/{$event->id}")
                ->with('error', 'This event is full.');
        }

        return view('registrations.create', compact('event'));
    }

    /**
     * Store a new registration.
     */
    public function store(Request $request, Event $event): RedirectResponse
    {

        // Validate basic requirements
        if ($event->status !== 'published' || ! $event->isRegistrationOpen() || ! $event->hasAvailableSpots()) {
            return redirect()->to("/events/{$event->id}")
                ->with('error', 'Registration is not available for this event.');
        }

        // Check if user is already registered
        $existingRegistration = $event->registrations()
            ->where('user_id', Auth::id())
            ->first();

        if ($existingRegistration) {
            return redirect()->to("/events/{$event->id}")
                ->with('info', 'You are already registered for this event.');
        }

        // Validate form data based on event's form fields
        $rules = [];
        $formData = [];
        $uploadedFiles = [];

        if ($event->form_fields) {
            foreach ($event->form_fields as $field) {
                $fieldName = $this->generateFieldName($field['label']);

                // Build validation rules
                $fieldRules = [];

                if ($field['required']) {
                    $fieldRules[] = 'required';
                }

                if ($field['type'] === 'email') {
                    $fieldRules[] = 'email';
                } elseif ($field['type'] === 'file') {
                    $fieldRules[] = 'file';
                    $fieldRules[] = 'max:10240'; // 10MB max
                }

                if (! empty($fieldRules)) {
                    $rules[$fieldName] = implode('|', $fieldRules);
                }

                // Process form data regardless of whether field is present
                // This ensures proper validation for required fields
                if ($field['type'] === 'file') {
                    if ($request->hasFile($fieldName)) {
                        $file = $request->file($fieldName);
                        $path = $file->store('registrations/'.$event->id, 'public');
                        $uploadedFiles[$field['label']] = [
                            'original_name' => $file->getClientOriginalName(),
                            'path' => $path,
                            'size' => $file->getSize(),
                        ];
                    }
                } else {
                    // For non-file fields, always capture the value (even if empty)
                    if ($field['type'] === 'checkbox') {
                        // Handle checkbox arrays
                        $value = $request->input($fieldName, []);
                        if (! empty($value)) {
                            $formData[$field['label']] = $value;
                        }
                    } else {
                        // Handle other field types
                        $value = $request->input($fieldName);
                        if ($value !== null && $value !== '') {
                            $formData[$field['label']] = $value;
                        }
                    }
                }
            }
        }

        // Add validation for terms and conditions
        $rules['terms'] = 'required|accepted';

        // Create custom validation messages
        $messages = [];
        if ($event->form_fields) {
            foreach ($event->form_fields as $field) {
                $fieldName = $this->generateFieldName($field['label']);
                $messages[$fieldName.'.required'] = 'The '.$field['label'].' field is required.';
                if ($field['type'] === 'email') {
                    $messages[$fieldName.'.email'] = 'The '.$field['label'].' must be a valid email address.';
                } elseif ($field['type'] === 'file') {
                    $messages[$fieldName.'.file'] = 'The '.$field['label'].' must be a file.';
                    $messages[$fieldName.'.max'] = 'The '.$field['label'].' may not be greater than 10MB.';
                }
            }
        }
        $messages['terms.required'] = 'You must agree to the terms and conditions.';
        $messages['terms.accepted'] = 'You must agree to the terms and conditions.';

        $request->validate($rules, $messages);

        // Create registration
        $registration = Registration::create([
            'user_id' => Auth::id(),
            'event_id' => $event->id,
            'status' => 'pending',
            'form_data' => $formData,
            'uploaded_files' => $uploadedFiles,
        ]);

        // If event doesn't require approval, auto-approve
        if (! $event->requires_approval) {
            $registration->approve(Auth::user());
        }

        return redirect()->to("/events/{$event->id}")
            ->with('success', 'Your registration has been submitted successfully!');
    }

    /**
     * Show user's registrations.
     */
    public function index(): View
    {
        $registrations = Auth::user()->registrations()
            ->with('event')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('registrations.index', compact('registrations'));
    }

    /**
     * Cancel a registration.
     */
    public function destroy(Registration $registration): RedirectResponse
    {
        // Check if user owns this registration
        if ($registration->user_id !== Auth::id()) {
            abort(403);
        }

        $registration->cancel();

        return redirect()->back()
            ->with('success', 'Registration cancelled successfully.');
    }
}
