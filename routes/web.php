<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationStatusController;
use App\Http\Controllers\ApplicationDossierController;
use App\Http\Controllers\ApplicationDocumentController;
use App\Http\Controllers\ApplicationDecisionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdmissionLetterController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ApplicationListController;
use App\Models\Programme;

/**
 * Routes File
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Defines web routes for the application.
 * Includes:
 * - Root landing page
 * - Public programmes catalogue
 * - Authenticated student application routes
 * - Payment routes
 * - Student dashboard
 * - Admission letter download
 * - Dynamic theme CSS endpoint
 * - Application dossier (view, print, PDF, export)
 * - Secure logout
 * - User profile page
 * - Applications list + single dossier + legacy alias
 * - Dedicated views: documents, decision, payment, progress
 * - Support & Notifications
 *
 * Status: 🚦 Integration / Hardening
 * Version: 3.8 (added notification read routes)
 */

// Root route (public landing page)
Route::get('/', function () {
    $programmes = Programme::with('faculty')->orderBy('name')->get();
    return view('landing', compact('programmes'));
})->name('landing');

// Public programmes catalogue
Route::get('/programmes', [ProgrammeController::class, 'index'])->name('programmes.index');
Route::get('/programmes/{programme}', [ProgrammeController::class, 'show'])->name('programmes.show');

// Dynamic theme CSS (public)
Route::get('/theme.css', [ThemeController::class, 'css'])->name('theme.css');

/*
|--------------------------------------------------------------------------
| Paystack Webhook (server-to-server)
|--------------------------------------------------------------------------
*/
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

// Grouped routes requiring authentication
Route::middleware(['auth'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Application lifecycle
    |--------------------------------------------------------------------------
    */
    Route::get('/apply', [ApplicationController::class, 'create'])->name('application.create');
    Route::post('/apply', [ApplicationController::class, 'store'])->name('application.store');
    Route::post('/application/{application}/submit', [ApplicationController::class, 'submit'])->name('application.submit');

    /*
    |--------------------------------------------------------------------------
    | Student application correction
    |--------------------------------------------------------------------------
    */
    Route::get('/application/{application}/correct', [ApplicationController::class, 'edit'])
        ->name('application.correct');
    Route::post('/application/{application}/correct', [ApplicationController::class, 'update'])
        ->name('application.correct.update');

    /*
    |--------------------------------------------------------------------------
    | Application status + dossier
    |--------------------------------------------------------------------------
    */
    Route::get('/application/{application}/status', [ApplicationStatusController::class, 'show'])->name('application.status');
    Route::get('/application/{application}/show', [ApplicationDossierController::class, 'show'])->name('application.show');
    Route::get('/application/{application}/print', [ApplicationDossierController::class, 'print'])->name('application.print');
    Route::get('/application/{application}/pdf', [ApplicationDossierController::class, 'pdf'])->name('application.pdf');
    Route::get('/application/{application}/export/excel', [ApplicationDossierController::class, 'exportExcel'])->name('application.export.excel');
    Route::get('/application/{application}/export/csv', [ApplicationDossierController::class, 'exportCsv'])->name('application.export.csv');

    /*
    |--------------------------------------------------------------------------
    | Payment
    |--------------------------------------------------------------------------
    */
    Route::post('/application/{application}/pay', [PaymentController::class, 'initialize'])->name('payment.initialize');
    Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
    Route::get('/applications/payment', [PaymentController::class, 'show'])->name('applications.payment');

    /*
    |--------------------------------------------------------------------------
    | Portal
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admission-letter/{application}', [AdmissionLetterController::class, 'show'])->name('admission.letter');

    // User profile
    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');

    /*
    |--------------------------------------------------------------------------
    | Applications list + single dossier + legacy alias
    |--------------------------------------------------------------------------
    */
    Route::get('/applications', [ApplicationListController::class, 'index'])->name('applications.index');
    Route::get('/applications/my', function () {
        $application = auth()->user()->applications()->latest()->first();
        return view('applications.my-application', compact('application'));
    })->name('applications.my');

    /*
    |--------------------------------------------------------------------------
    | Application-specific views
    |--------------------------------------------------------------------------
    */
    Route::get('/applications/documents', [ApplicationDocumentController::class, 'index'])->name('applications.documents');

    // ✅ Upload documents
    Route::post('/applications/{application}/documents/upload', [ApplicationDocumentController::class, 'upload'])->name('application.documents.upload');

    // ✅ Replace documents
    Route::post('/applications/{application}/documents/{document}/replace', [ApplicationDocumentController::class, 'replace'])->name('application.documents.replace');

    // ✅ Secure view documents
    Route::get('/application/{application}/documents/{document}/view', [ApplicationDocumentController::class, 'view'])->name('application.documents.view');

    Route::get('/applications/decision', [ApplicationDecisionController::class, 'show'])->name('applications.decision');
    Route::get('/applications/progress', [ApplicationStatusController::class, 'progress'])->name('applications.progress');

    // Catch-all single dossier (must come last!)
    Route::get('/applications/{application}', [ApplicationListController::class, 'show'])->name('applications.show');

    /*
    |--------------------------------------------------------------------------
    | Support & Notifications
    |--------------------------------------------------------------------------
    */
    Route::get('/support', function () {
        return view('support.index');
    })->name('support.index');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
});
