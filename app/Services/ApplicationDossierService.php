<?php

namespace App\Services;

use App\Models\Application;

class ApplicationDossierService
{
    /**
     * Load all dossier relationships for a given application.
     */
    public function load(Application $application): Application
    {
        return $application->load([
            'user',
            'programme.faculty',
            'programme.department',
            'fieldValues.formField',
            'documents.documentType',
            'payment',
            'decision.officer',
            'statusHistories.officer',
        ]);
    }

    /**
     * Flatten dossier into an array for export or API.
     */
    public function toArray(Application $application): array
    {
        $this->load($application);

        return [
            'application' => [
                'number'        => $application->application_number,
                'status'        => $application->application_status,
                'payment_status'=> $application->payment_status,
                'fee'           => $application->application_fee,
                'submitted_at'  => $application->submitted_at?->toDateTimeString(),
            ],

            'applicant' => [
                'name'  => $application->user?->name,
                'email' => $application->user?->email,
                'phone' => $application->user?->phone,
            ],

            'programme' => [
                'name'       => $application->programme?->name,
                'degree_type'=> $application->programme?->degree_type,
                'faculty'    => $application->programme?->faculty?->name,
                'department' => $application->programme?->department?->name,
            ],

            'fields' => $application->fieldValues
                ->mapWithKeys(function ($value) {
                    return [
                        $value->formField?->label
                            ?? $value->formField?->key
                            ?? 'Field' => $value->value,
                    ];
                })
                ->toArray(),

            'documents' => $application->documents
                ->map(function ($document) {
                    return [
                        'name'            => $document->documentType?->name,
                        'status'          => $document->status,
                        'uploaded_at'     => $document->uploaded_at?->toDateTimeString(),
                        'path'            => $document->path,
                        'original_name'   => $document->original_name,
                        'mime_type'       => $document->mime_type,
                        'size'            => $document->size,
                        'rejection_reason'=> $document->rejection_reason,
                    ];
                })
                ->toArray(),

            'payment' => $application->payment ? [
                'reference' => $application->payment->reference,
                'amount'    => $application->payment->amountInNaira(),
                'gateway'   => $application->payment->gateway,
                'status'    => $application->payment->status,
                'paid_at'   => $application->payment->paid_at?->toDateTimeString(),
            ] : null,

            'decision' => $application->decision ? [
                'status'     => $application->decision->status,
                'remarks'    => $application->decision->remarks,
                'officer'    => $application->decision->officer?->name,
                'decided_at' => $application->decision->decided_at?->toDateTimeString(),
            ] : null,

            'history' => $application->statusHistories
                ->map(function ($history) {
                    return [
                        'old_status' => $history->old_status,
                        'new_status' => $history->new_status,
                        'changed_by' => $history->officer?->name ?? 'System',
                        'changed_at' => $history->changed_at?->toDateTimeString(),
                    ];
                })
                ->toArray(),
        ];
    }
}
