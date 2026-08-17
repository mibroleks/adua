<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application {{ $application->application_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 18mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #222;
            line-height: 1.5;
        }
        .header {
            border-bottom: 2px solid #222;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .section {
            margin-top: 22px;
        }
        .section-title {
            font-size: 15px;
            font-weight: 700;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 7px;
            text-align: left;
            vertical-align: top;
        }
        th {
            width: 30%;
            background: #f9f9f9;
        }
        .print-actions {
            margin-bottom: 20px;
        }
        @media print {
            .print-actions { display: none; }
        }
    </style>
</head>
<body>

<div class="print-actions">
    <button onclick="window.print()">Print</button>
</div>

<div class="header">
    @if(setting('institution.logo'))
        <img src="{{ asset('storage/' . setting('institution.logo')) }}" style="height:70px;" alt="Institution Logo">
    @endif
    <h1>{{ setting('institution.name') ?? 'Institution Name' }}</h1>
    <div>{{ setting('institution.address') ?? '' }}</div>
</div>

<h2>Application Dossier</h2>
<p><strong>Application Number:</strong> {{ $application->application_number }}</p>

{{-- Application Overview --}}
<div class="section">
    <div class="section-title">Application Overview</div>
    <table>
        <tr><th>Programme</th><td>{{ $application->programme?->name }}</td></tr>
        <tr><th>Faculty</th><td>{{ $application->programme?->faculty?->name ?? '—' }}</td></tr>
        <tr><th>Department</th><td>{{ $application->programme?->department?->name ?? '—' }}</td></tr>
        <tr><th>Application Fee</th><td>{{ $application->formatted_application_fee }}</td></tr>
        <tr><th>Application Status</th><td>{{ $application->application_status }}</td></tr>
        <tr><th>Payment Status</th><td>{{ $application->payment_status }}</td></tr>
        <tr><th>Submitted At</th><td>{{ $application->formatted_submitted_at }}</td></tr>
    </table>
</div>

{{-- Applicant Information --}}
<div class="section">
    <div class="section-title">Applicant Information</div>
    <table>
        <tr><th>Name</th><td>{{ $application->user?->name }}</td></tr>
        <tr><th>Email</th><td>{{ $application->user?->email }}</td></tr>
        <tr><th>Phone</th><td>{{ $application->user?->phone ?? '—' }}</td></tr>
    </table>
</div>

{{-- Dynamic Fields --}}
@if($application->fieldValues->count())
<div class="section">
    <div class="section-title">Application Fields</div>
    <table>
        @foreach($application->fieldValues as $fv)
            <tr>
                <th>{{ $fv->formField?->label ?? $fv->formField?->key }}</th>
                <td>{{ $fv->value }}</td>
            </tr>
        @endforeach
    </table>
</div>
@endif

{{-- Documents --}}
<div class="section">
    <div class="section-title">Documents</div>
    <table>
        <thead>
            <tr><th>Document</th><th>Status</th><th>Uploaded At</th></tr>
        </thead>
        <tbody>
            @forelse($application->documents as $doc)
                <tr>
                    <td>{{ $doc->documentType?->name }}</td>
                    <td>{{ $doc->status }}</td>
                    <td>{{ $doc->uploaded_at?->format('d M Y, H:i') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="3">No documents uploaded.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Payment --}}
@if($application->payment)
<div class="section">
    <div class="section-title">Payment</div>
    <table>
        <tr><th>Reference</th><td>{{ $application->payment->reference }}</td></tr>
        <tr><th>Amount</th><td>₦{{ number_format($application->payment->amountInNaira(), 2) }}</td></tr>
        <tr><th>Gateway</th><td>{{ $application->payment->gateway }}</td></tr>
        <tr><th>Status</th><td>{{ $application->payment->status }}</td></tr>
        <tr><th>Paid At</th><td>{{ $application->payment->paid_at?->format('d M Y, H:i') ?? '—' }}</td></tr>
    </table>
</div>
@endif

{{-- Admission Decision --}}
@if($application->decision)
<div class="section">
    <div class="section-title">Admission Decision</div>
    <table>
        <tr><th>Decision</th><td>{{ $application->decision->status }}</td></tr>
        <tr><th>Remarks</th><td>{{ $application->decision->remarks ?? '—' }}</td></tr>
        <tr><th>Officer</th><td>{{ $application->decision->officer?->name ?? '—' }}</td></tr>
        <tr><th>Decided At</th><td>{{ $application->decision->decided_at?->format('d M Y, H:i') ?? '—' }}</td></tr>
    </table>
</div>
@endif

{{-- Status History --}}
<div class="section">
    <div class="section-title">Status History</div>
    <table>
        <thead>
            <tr><th>Date</th><th>Old Status</th><th>New Status</th><th>Changed By</th></tr>
        </thead>
        <tbody>
            @forelse($application->statusHistories as $history)
                <tr>
                    <td>{{ $history->changed_at?->format('d M Y, H:i') }}</td>
                    <td>{{ $history->old_status ?? '—' }}</td>
                    <td>{{ $history->new_status }}</td>
                    <td>{{ $history->officer?->name ?? 'System' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No status history recorded.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

</body>
</html>
