<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationFieldValue;
use App\Models\FormField;
use App\Models\ApplicationStatusHistory;
use App\Models\ApplicationDocument;
use App\Models\ApplicationDocumentType;
use App\Services\AdmissionService;
use App\Services\PortalConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ApplicationController extends Controller
{
    protected AdmissionService $admissionService;
    protected PortalConfigService $portalConfig;

    public function __construct(AdmissionService $admissionService, PortalConfigService $portalConfig)
    {
        $this->admissionService = $admissionService;
        $this->portalConfig = $portalConfig;
    }

    /**
     * Show the application form view.
     */
    public function create(Request $request)
    {
        if (! $this->portalConfig->applicationsEnabled()) {
            abort(403, 'Applications are currently disabled.');
        }

        if ($this->portalConfig->applicationStatus() !== 'Open') {
            abort(403, 'Applications are not open.');
        }

        $programmeId = $request->query('programme_id');

        $globalFields = FormField::whereNull('programme_id')
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        $programmeFields = FormField::where('programme_id', $programmeId)
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        Log::info("ApplicationController::create - programmeId={$programmeId}, globalFields=".count($globalFields).", programmeFields=".count($programmeFields));

        return view('apply', compact('globalFields', 'programmeFields', 'programmeId'));
    }

    /**
     * Store a new draft application.
     */
    public function store(Request $request)
    {
        if (! $this->portalConfig->applicationsEnabled()) {
            abort(403, 'Applications are currently disabled.');
        }

        if ($this->portalConfig->applicationStatus() !== 'Open') {
            abort(403, 'Applications are not open.');
        }

        $request->validate([
            'programme_id' => 'required|exists:programmes,id',
        ]);

        $application = $this->admissionService->createDraft(auth()->user(), $request->programme_id);

        Log::info("Draft application created: {$application->id} for user ".auth()->id()." programme {$application->programme_id}");

        // Save dynamic field values
        $fields = FormField::where(function ($query) use ($application) {
                $query->whereNull('programme_id')
                      ->orWhere('programme_id', $application->programme_id);
            })
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($fields as $field) {
            if ($request->has($field->key)) {
                ApplicationFieldValue::create([
                    'application_id' => $application->id,
                    'form_field_id'  => $field->id,
                    'value'          => is_array($request->input($field->key))
                        ? json_encode($request->input($field->key))
                        : $request->input($field->key),
                ]);
                Log::info("Saved field value: {$field->key} for application {$application->id}");
            }
        }

        // Save uploaded documents
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $key => $file) {
                $docType = ApplicationDocumentType::where('key', $key)
                    ->where('active', true)
                    ->where(function ($q) use ($application) {
                        $q->whereNull('programme_id')
                          ->orWhere('programme_id', $application->programme_id);
                    })
                    ->first();

                if (! $docType) {
                    Log::warning("No ApplicationDocumentType found for key: {$key}");
                    continue;
                }

                if ($file && $file->isValid()) {
                    $path = $file->store('applications/' . $application->id, 'public');
                    ApplicationDocument::create([
                        'application_id'   => $application->id,
                        'document_type_id' => $docType->id,
                        'disk'             => 'public',
                        'path'             => $path,
                        'original_name'    => $file->getClientOriginalName(),
                        'mime_type'        => $file->getClientMimeType(),
                        'size'             => $file->getSize(),
                        'status'           => 'PENDING',
                        'uploaded_at'      => now(),
                    ]);
                    Log::info("Uploaded document {$docType->key} for application {$application->id}");
                }
            }
        }

        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'old_status'     => null,
            'new_status'     => Application::STATUS_DRAFT,
            'changed_by'     => auth()->id(),
            'changed_at'     => now(),
        ]);

        Log::info("Application {$application->id} status set to DRAFT");

        return redirect()
            ->route('application.status', $application)
            ->with('status', 'Your application has been saved successfully.');
    }

    /**
     * Submit an application.
     */
    public function submit(Application $application)
    {
        Gate::authorize('update', $application);

        if ($this->portalConfig->applicationStatus() !== 'Open') {
            abort(403, 'Applications are not open.');
        }

        try {
            $this->admissionService->submit($application);

            Log::info("Application {$application->id} submitted", [
                'application_number' => $application->application_number,
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->route('application.status', $application)
                ->with('status', 'Your application has been submitted successfully.');

        } catch (\InvalidArgumentException $e) {
            Log::warning("Application {$application->id} could not be submitted.", [
                'application_number' => $application->application_number,
                'user_id' => auth()->id(),
                'reason' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withErrors(['application' => $e->getMessage()]);
        }
    }

    /**
     * Edit an application when correction is requested.
     */
    public function edit(Application $application)
    {
        Gate::authorize('correct', $application);

        $programmeId = $application->programme_id;

        $globalFields = FormField::whereNull('programme_id')
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        $programmeFields = FormField::where('programme_id', $programmeId)
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        $globalDocs = ApplicationDocumentType::whereNull('programme_id')
            ->where('active', true)
            ->get();

        $programmeDocs = ApplicationDocumentType::where('programme_id', $programmeId)
            ->where('active', true)
            ->get();

        return view('applications.correct', compact(
            'application',
            'programmeId',
            'globalFields',
            'programmeFields',
            'globalDocs',
            'programmeDocs'
        ));
    }

    /**
     * Update and resubmit corrected application.
     */
    public function update(Request $request, Application $application)
    {
        Gate::authorize('correct', $application);

        $request->validate([
            'programme_id' => 'required|exists:programmes,id',
        ]);

        $fields = FormField::where(function ($query) use ($application) {
                $query->whereNull('programme_id')
                      ->orWhere('programme_id', $application->programme_id);
            })
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($fields as $field) {
            if ($request->has($field->key)) {
                $value = is_array($request->input($field->key))
                    ? json_encode($request->input($field->key))
                    : $request->input($field->key);

                ApplicationFieldValue::updateOrCreate(
                    [
                        'application_id' => $application->id,
                        'form_field_id'  => $field->id,
                    ],
                    ['value' => $value]
                );
                Log::info("Corrected field value: {$field->key} for application {$application->id}");
            }
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $key => $file) {
                $docType = ApplicationDocumentType::where('key', $key)
                    ->where('active', true)
                    ->where(function ($q) use ($application) {
                        $q->whereNull('programme_id')
                          ->orWhere('programme_id', $application->programme_id);
                    })
                    ->first();

                if (! $docType) {
                    Log::warning("No ApplicationDocumentType found for key: {$key}");
                    continue;
                }

                if ($file && $file->isValid()) {
                    $path = $file->store('applications/' . $application->id, 'public');

                    ApplicationDocument::updateOrCreate(
                        [
                            'application_id'   => $application->id,
                            'document_type_id' => $docType->id,
                        ],
                        [
                            'disk'          => 'public',
                            'path'          => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type'     => $file->getClientMimeType(),
                            'size'          => $file->getSize(),
                            'status'        => 'PENDING',
                            'uploaded_at'   => now(),
                        ]
                    );

                    Log::info("Corrected document {$docType->key} for application {$application->id}");
                }
            }
        }

        // Reset status back to SUBMITTED after correction
        $application->setApplicationStatus(Application::STATUS_SUBMITTED, auth()->id());

        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'old_status'     => Application::STATUS_CORRECTION_REQUIRED,
            'new_status'     => Application::STATUS_SUBMITTED,
            'changed_by'     => auth()->id(),
            'changed_at'     => now(),
        ]);

        Log::info("Application {$application->id} corrected and resubmitted by user ".auth()->id());

        return redirect()
            ->route('dashboard')
            ->with('status', 'Application corrected and resubmitted successfully.');
    }
}

