<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Event Registrations - {{ $event->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .event-info {
            margin-bottom: 30px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .event-info h2 {
            margin: 0 0 10px 0;
            color: #2563eb;
        }
        .event-info p {
            margin: 5px 0;
        }
        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            background-color: #e5e7eb;
            padding: 15px;
            border-radius: 5px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        .stat-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #374151;
        }
        .status-approved {
            color: #059669;
            font-weight: bold;
        }
        .status-pending {
            color: #d97706;
            font-weight: bold;
        }
        .status-rejected {
            color: #dc2626;
            font-weight: bold;
        }
        .status-cancelled {
            color: #6b7280;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Event Registration Report</h1>
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <div class="event-info">
        <h2>{{ $event->title }}</h2>
        <p><strong>Event Date:</strong> {{ $event->start_date->format('l, F j, Y g:i A') }} - {{ $event->end_date->format('l, F j, Y g:i A') }}</p>
        @if($event->location)
            <p><strong>Location:</strong> {{ $event->location }}</p>
        @endif
        <p><strong>Price:</strong> @if($event->price > 0) ${{ number_format($event->price, 2) }} @else Free @endif</p>
        @if($event->max_spots)
            <p><strong>Maximum Spots:</strong> {{ $event->max_spots }}</p>
        @endif
        <p><strong>Requires Approval:</strong> {{ $event->requires_approval ? 'Yes' : 'No' }}</p>
    </div>

    <div class="stats">
        <div class="stat-item">
            <div class="stat-number">{{ $registrations->count() }}</div>
            <div class="stat-label">Total Registrations</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $registrations->where('status', 'approved')->count() }}</div>
            <div class="stat-label">Approved</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $registrations->where('status', 'pending')->count() }}</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $registrations->where('status', 'rejected')->count() }}</div>
            <div class="stat-label">Rejected</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $registrations->where('status', 'cancelled')->count() }}</div>
            <div class="stat-label">Cancelled</div>
        </div>
    </div>

    @if($registrations->count() > 0)
        <h3>Registration Details</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Registration Date</th>
                    <th>Reviewed By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registrations as $registration)
                    <tr>
                        <td>{{ $registration->id }}</td>
                        <td>{{ $registration->user->name }}</td>
                        <td>{{ $registration->user->email }}</td>
                        <td class="status-{{ $registration->status }}">{{ ucfirst($registration->status) }}</td>
                        <td>{{ $registration->created_at->format('M j, Y g:i A') }}</td>
                        <td>{{ $registration->reviewer ? $registration->reviewer->name : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($registrations->where('status', 'approved')->count() > 0)
            <div class="page-break">
                <h3>Approved Registrations</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Approved Date</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registrations->where('status', 'approved') as $registration)
                            <tr>
                                <td>{{ $registration->user->name }}</td>
                                <td>{{ $registration->user->email }}</td>
                                <td>{{ $registration->user->phone ?? '-' }}</td>
                                <td>{{ $registration->approved_at ? $registration->approved_at->format('M j, Y g:i A') : '-' }}</td>
                                <td>{{ $registration->organizer_notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($registrations->where('status', 'rejected')->count() > 0)
            <div class="page-break">
                <h3>Rejected Registrations</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Rejected Date</th>
                            <th>Rejection Reason</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registrations->where('status', 'rejected') as $registration)
                            <tr>
                                <td>{{ $registration->user->name }}</td>
                                <td>{{ $registration->user->email }}</td>
                                <td>{{ $registration->rejected_at ? $registration->rejected_at->format('M j, Y g:i A') : '-' }}</td>
                                <td>{{ $registration->rejection_reason ?? '-' }}</td>
                                <td>{{ $registration->organizer_notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @else
        <p style="text-align: center; color: #6b7280; font-style: italic; margin: 40px 0;">
            No registrations found for this event.
        </p>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the Online Registration Management System.</p>
        <p>Event: {{ $event->title }} | Report Date: {{ now()->format('F j, Y') }}</p>
    </div>
</body>
</html>
