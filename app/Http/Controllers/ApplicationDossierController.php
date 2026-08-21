<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ApplicationDossierController extends Controller
{
    /**
     * Show the full application dossier.
     *
     * Route name: application.show
     */
    public function show(Application $application)
    {
        Gate::authorize('view', $application);

        $application->load([
            'user',
            'programme.faculty',
            'programme.department',
            'fieldValues.formField',
            'documents.documentType',
            'payments',
            'decision.officer',
            'statusHistories.officer',
        ]);

        return view('applications.show', compact('application'));
    }

    /**
     * Print-friendly dossier view.
     *
     * Route name: application.print
     */
    public function print(Application $application)
    {
        Gate::authorize('view', $application);

        $application->loadMissing([
            'user','programme.faculty','programme.department',
            'fieldValues.formField','documents.documentType',
            'payments','decision.officer','statusHistories.officer',
        ]);

        return view('applications.print', compact('application'));
    }

    /**
     * Download dossier as PDF.
     *
     * Route name: application.pdf
     */
    public function pdf(Application $application)
    {
        Gate::authorize('view', $application);

        $application->loadMissing([
            'user','programme.faculty','programme.department',
            'fieldValues.formField','documents.documentType',
            'payments','decision.officer','statusHistories.officer',
        ]);

        $pdf = Pdf::loadView('applications.print', compact('application'));
        return $pdf->download("Application-{$application->application_number}.pdf");
    }

    /**
     * Export dossier to Excel.
     *
     * Route name: application.export.excel
     */
    public function exportExcel(Application $application)
    {
        Gate::authorize('view', $application);

        $application->loadMissing([
            'user','programme.faculty','programme.department',
            'fieldValues.formField','documents.documentType',
            'payments','decision.officer','statusHistories.officer',
        ]);

        return Excel::download(
            new \App\Filament\Exports\ApplicationExport($application),
            "Application-{$application->application_number}.xlsx"
        );
    }

    /**
     * Export dossier to CSV.
     *
     * Route name: application.export.csv
     */
    public function exportCsv(Application $application)
    {
        Gate::authorize('view', $application);

        $application->loadMissing([
            'user',
            'programme.faculty',
            'programme.department',
            'fieldValues.formField',
            'documents.documentType',
            'payments',
            'decision.officer',
            'statusHistories.officer',
        ]);

        Log::info("Exporting application {$application->id} dossier to CSV");

        return Excel::download(
            new \App\Filament\Exports\ApplicationExport($application),
            "Application-{$application->application_number}.csv",
            \Maatwebsite\Excel\Excel::CSV
        );
    }
}
