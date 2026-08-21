@extends('layouts.app')

@section('title', 'Application Dossier')

@section('content')
<div class="application-dossier">

    <header class="dossier-header">
        <h1>Application Dossier</h1>
        <p>Application Number: {{ $application->application_number }}</p>
        <div class="dossier-actions">
            <a href="{{ route('admin.applications.print', $application) }}" target="_blank" class="btn btn-secondary">Print</a>
            <a href="{{ route('admin.applications.pdf', $application) }}" target="_blank" class="btn btn-secondary">Download PDF</a>
            <a href="{{ route('admin.applications.export.excel', $application) }}" class="btn btn-secondary">Export Excel</a>
            <a href="{{ route('admin.applications.export.csv', $application) }}" class="btn btn-secondary">Export CSV</a>
        </div>
    </header>

    {{-- Application Overview --}}
    <section class="dossier-section">
        <h2>Application Overview</h2>
        <table class="table">
            <tr><th>Programme</th><td>{{ $application->programme?->name }}</td></tr>
            <tr><th>Faculty</th><td>{{ $application->programme?->faculty?->name ?? '—' }}</td></tr>
            <tr><th>Department</th><td>{{ $application->programme?->department?->name ?? '—' }}</td></tr>
            <tr><th>Application Fee</th><td>{{ $application->formatted_application_fee }}</td></tr>
            <tr><th>Application Status</th><td>{{ $application->application_status }}</td></tr>
            <tr><th>Payment Status</th><td>{{ $application->payment_status }}</td></tr>
            <tr><th>Submitted At</th><td>{{ $application->formatted_submitted_at }}</td></tr>
        </table>
    </section>

    {{-- Applicant Information --}}
    <section class="dossier-section">
        <h2>Applicant Information</h2>
        <table class="table">
            <tr><th>Name</th><td>{{ $application->user?->name }}</td></tr>
            <tr><th>Email</th><td>{{ $application->user?->email }}</td></tr>
            <tr><th>Phone</th><td>{{ $application->user?->phone ?? '—' }}</td></tr>
        </table>
    </section>

    {{-- Dynamic Fields --}}
    @if($application->fieldValues->count())
    <section class="dossier-section">
        <h2>Application Fields</h2>
        <table class="table">
            @foreach($application->fieldValues as $fv)
                <tr>
                    <th>{{ $fv->formField?->label ?? $fv->formField?->key }}</th>
                    <td>{{ $fv->value }}</td>
                </tr>
            @endforeach
        </table>
    </section>
    @endif

    {{-- Documents --}}
    <section class="dossier-section">
        <h2>Documents</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Status</th>
                    <th>Uploaded At</th>
                    <th>View</th>
                </tr>
            </thead>
            <tbody>
                @forelse($application->documents as $doc)
                    <tr>
                        <td>{{ $doc->documentType?->name }}</td>
                        <td>{{ $doc->status }}</td>
                        <td>{{ $doc->uploaded_at?->format('d M Y, H:i') ?? '—' }}</td>
                        <td>
                            <a href="{{ Storage::url($doc->path) }}" target="_blank">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">No documents uploaded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    {{-- Payment --}}
    @if($application->payment)
    <section class="dossier-section">
        <h2>Payment</h2>
        <table class="table">
            <tr><th>Reference</th><td>{{ $application->payment->reference }}</td></tr>
            <tr><th>Amount</th><td>₦{{ number_format($application->payment->amountInNaira(), 2) }}</td></tr>
            <tr><th>Gateway</th><td>{{ $application->payment->gateway }}</td></tr>
            <tr><th>Status</th><td>{{ $application->payment->status }}</td></tr>
            <tr><th>Paid At</th><td>{{ $application->payment->paid_at?->format('d M Y, H:i') ?? '—' }}</td></tr>
        </table>
    </section>
    @endif

    {{-- Admission Decision --}}
    @if($application->decision)
    <section class="dossier-section">
        <h2>Admission Decision</h2>
        <table class="table">
            <tr><th>Decision</th><td>{{ $application->decision->decision }}</td></tr>
            <tr><th>Remarks</th><td>{{ $application->decision->remarks ?? '—' }}</td></tr>
            <tr><th>Officer</th><td>{{ $application->decision->officer?->name ?? '—' }}</td></tr>
            <tr><th>Decided At</th><td>{{ $application->decision->decided_at?->format('d M Y, H:i') ?? '—' }}</td></tr>
        </table>
    </section>
    @endif

    {{-- Status History --}}
    <section class="dossier-section">
        <h2>Status History</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Old Status</th>
                    <th>New Status</th>
                    <th>Changed By</th>
                </tr>
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
    </section>

</div>
@endsection
