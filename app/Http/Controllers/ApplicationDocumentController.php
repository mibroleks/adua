<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\ApplicationDocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\AdmissionService;

class ApplicationDocumentController extends Controller
{
    /**
     * List all documents for the authenticated applicant.
     *
     * Route name: applications.documents
     * URL: /applications/documents
     */
    public function index(Request $request)
    {
        $application = Application::with('documents.documentType')
            ->where('user_id', $request->user()->id)
            ->first();

        return view('applications.documents', compact('application'));
    }

    /**
     * Upload new documents for an application.
     *
     * Route name: application.documents.upload
     */
    public function upload(Request $request, Application $application)
    {
        if ($application->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized to upload documents for this application.');
        }

        Gate::authorize('update', $application);

        if (! $request->hasFile('documents')) {
            return redirect()
                ->route('application.status', $application)
                ->withErrors(['documents' => 'No documents were uploaded.']);
        }

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
                $document = ApplicationDocument::create([
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

                // Record initial upload history
                \App\Models\ApplicationDocumentHistory::create([
                    'application_document_id' => $document->id,
                    'application_id'          => $application->id,
                    'action'                  => 'UPLOADED',
                    'old_status'              => null,
                    'new_status'              => 'PENDING',
                    'performed_by'            => $request->user()->id,
                    'performed_at'            => now(),
                ]);

                Log::info("Uploaded document {$docType->key} for application {$application->id}");
            }
        }

        return redirect()
            ->route('application.status', $application)
            ->with('status', 'Documents uploaded successfully.');
    }

    /**
     * Replace an existing document.
     *
     * Route name: application.documents.replace
     */
    public function replace(Request $request, Application $application, ApplicationDocument $document)
    {
        if ($document->application_id !== $application->id) {
            abort(404);
        }

        Gate::authorize('replaceDocument', [$application, $document]);

        $request->validate([
            'document' => [
                'required',
                'file',
                'max:10240', // 10 MB
                'mimes:pdf,jpg,jpeg,png',
            ],
        ]);

        app(AdmissionService::class)->replaceDocument(
            $document,
            $request->file('document'),
            $request->user()->id
        );

        Log::info("Replaced document {$document->id} for application {$application->id}");

        return redirect()
            ->route('application.status', $application)
            ->with('status', 'Replacement uploaded successfully. Your document is now awaiting verification.');
    }

    /**
     * View a document securely.
     *
     * Route name: application.documents.view
     */
    public function view(Request $request, Application $application, ApplicationDocument $document)
    {
        if ($document->application_id !== $application->id) {
            abort(404);
        }

        // ✅ Use the correct policy method
        Gate::authorize('viewDocument', [$application, $document]);

        $disk = $document->disk ?? 'public';

        if (! Storage::disk($disk)->exists($document->path)) {
            abort(404, 'File not found.');
        }

        $mime = $document->mime_type ?? Storage::disk($disk)->mimeType($document->path);

        // ✅ Stream file inline with proper headers
        return response()->file(Storage::disk($disk)->path($document->path), [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($document->original_name) . '"',
        ]);
    }
}
