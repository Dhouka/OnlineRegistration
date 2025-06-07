<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    /**
     * Export event registrations to CSV.
     */
    public function exportRegistrationsCsv(Event $event)
    {
        // Check if user can access this event
        if (! Auth::user()->hasRole('admin') && $event->created_by !== Auth::id()) {
            abort(403);
        }

        $registrations = $event->registrations()->with('user')->get();

        $csvData = [];
        $csvData[] = [
            'Registration ID',
            'User Name',
            'User Email',
            'Status',
            'Registration Date',
            'Approved Date',
            'Rejected Date',
            'Reviewer',
            'Organizer Notes',
            'Rejection Reason',
        ];

        // Add form field headers if they exist
        $formFields = [];
        if ($event->form_fields) {
            foreach ($event->form_fields as $field) {
                $formFields[] = $field['label'];
                $csvData[0][] = $field['label'];
            }
        }

        foreach ($registrations as $registration) {
            $row = [
                $registration->id,
                $registration->user->name,
                $registration->user->email,
                ucfirst($registration->status),
                $registration->created_at->format('Y-m-d H:i:s'),
                $registration->approved_at ? $registration->approved_at->format('Y-m-d H:i:s') : '',
                $registration->rejected_at ? $registration->rejected_at->format('Y-m-d H:i:s') : '',
                $registration->reviewer ? $registration->reviewer->name : '',
                $registration->organizer_notes ?? '',
                $registration->rejection_reason ?? '',
            ];

            // Add form data
            foreach ($formFields as $fieldLabel) {
                $value = $registration->form_data[$fieldLabel] ?? '';
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $row[] = $value;
            }

            $csvData[] = $row;
        }

        $filename = 'registrations_'.$event->id.'_'.now()->format('Y-m-d_H-i-s').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export event registrations to PDF.
     */
    public function exportRegistrationsPdf(Event $event)
    {
        // Check if user can access this event
        if (! Auth::user()->hasRole('admin') && $event->created_by !== Auth::id()) {
            abort(403);
        }

        $registrations = $event->registrations()->with('user', 'reviewer')->get();

        $pdf = Pdf::loadView('exports.registrations-pdf', compact('event', 'registrations'));

        $filename = 'registrations_'.$event->id.'_'.now()->format('Y-m-d_H-i-s').'.pdf';

        return $pdf->download($filename);
    }
}
