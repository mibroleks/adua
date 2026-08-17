<?php

/*
Component: Application Export
File Path: app/Exports/ApplicationExport.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the export class for full Application dossiers.
Uses ApplicationDossierService to flatten data into arrays
for Excel/CSV export via Maatwebsite\Excel or Filament Excel.

Status: ✅ Production Ready
Version: 1.0
*/

namespace App\Exports;

use App\Models\Application;
use App\Services\ApplicationDossierService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class ApplicationExport implements FromArray, WithTitle
{
    protected Application $application;
    protected ApplicationDossierService $service;

    public function __construct(Application $application)
    {
        $this->application = $application;
        $this->service = app(ApplicationDossierService::class);
    }

    /**
     * Provide the flattened dossier as an array for export.
     */
    public function array(): array
    {
        $dossier = $this->service->toArray($this->application);

        // Flatten into rows for Excel/CSV
        $rows = [];

        // Application overview
        $rows[] = [
            'Section' => 'Application',
            'Field'   => 'Number',
            'Value'   => $dossier['application']['number'],
        ];
        $rows[] = [
            'Section' => 'Application',
            'Field'   => 'Status',
            'Value'   => $dossier['application']['status'],
        ];
        $rows[] = [
            'Section' => 'Application',
            'Field'   => 'Payment Status',
            'Value'   => $dossier['application']['payment_status'],
        ];
        $rows[] = [
            'Section' => 'Application',
            'Field'   => 'Submitted At',
            'Value'   => $dossier['application']['submitted_at'],
        ];

        // Applicant info
        foreach ($dossier['applicant'] as $field => $value) {
            $rows[] = [
                'Section' => 'Applicant',
                'Field'   => ucfirst($field),
                'Value'   => $value,
            ];
        }

        // Programme info
        foreach ($dossier['programme'] as $field => $value) {
            $rows[] = [
                'Section' => 'Programme',
                'Field'   => ucfirst($field),
                'Value'   => $value,
            ];
        }

        // Dynamic fields
        foreach ($dossier['fields'] as $label => $value) {
            $rows[] = [
                'Section' => 'Fields',
                'Field'   => $label,
                'Value'   => $value,
            ];
        }

        // Documents
        foreach ($dossier['documents'] as $doc) {
            $rows[] = [
                'Section' => 'Documents',
                'Field'   => $doc['name'],
                'Value'   => "Status: {$doc['status']} | Uploaded: {$doc['uploaded_at']}",
            ];
        }

        // Payment
        if ($dossier['payment']) {
            foreach ($dossier['payment'] as $field => $value) {
                $rows[] = [
                    'Section' => 'Payment',
                    'Field'   => ucfirst(str_replace('_', ' ', $field)),
                    'Value'   => $value,
                ];
            }
        }

        // Decision
        if ($dossier['decision']) {
            foreach ($dossier['decision'] as $field => $value) {
                $rows[] = [
                    'Section' => 'Decision',
                    'Field'   => ucfirst(str_replace('_', ' ', $field)),
                    'Value'   => $value,
                ];
            }
        }

        // Status history
        foreach ($dossier['history'] as $h) {
            $rows[] = [
                'Section' => 'History',
                'Field'   => "{$h['changed_at']} ({$h['changed_by']})",
                'Value'   => "From {$h['old_status']} → {$h['new_status']}",
            ];
        }

        return $rows;
    }

    /**
     * Title for the worksheet.
     */
    public function title(): string
    {
        return "Application {$this->application->application_number}";
    }
}
