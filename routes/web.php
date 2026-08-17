<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdmissionLetterController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\ProgrammeController;
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
 *
 * Status: ✅ Production Ready
 * Version: 2.1 (added webhook route + dossier routes)
 */

// Root route (public landing page)
Route::get('/', function () {
    $programmes = Programme::with('faculty')->orderBy('name')->get();
    return view('landing', compact('programmes'));
})->name('landing');

// Public programmes catalogue
Route::get('/programmes', [ProgrammeController::class, 'index'])
    ->name('programmes.index');

Route::get('/programmes/{programme}', [ProgrammeController::class, 'show'])
    ->name('programmes.show');

// Dynamic theme CSS (public)
Route::get('/theme.css', [ThemeController::class, 'css'])
    ->name('theme.css');

/*
|--------------------------------------------------------------------------
| Paystack Webhook (server-to-server)
|--------------------------------------------------------------------------
| This route must be public (outside auth middleware).
| Paystack calls this endpoint directly to notify payment status.
*/
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])
    ->name('payment.webhook');

// Grouped routes requiring authentication
Route::middleware(['auth'])->group(function () {
    // Show application form
    Route::get('/apply', [ApplicationController::class, 'create'])
        ->name('application.create');

    // Store draft application
    Route::post('/apply', [ApplicationController::class, 'store'])
        ->name('application.store');

    // Submit application
    Route::post('/application/{application}/submit', [ApplicationController::class, 'submit'])
        ->name('application.submit');

    // View application status
    Route::get('/application/{application}/status', [ApplicationController::class, 'status'])
        ->name('application.status');

    // 🔎 View full application dossier
    Route::get('/application/{application}/show', [ApplicationController::class, 'show'])
        ->name('application.show');

    // 🔎 Print dossier (HTML view styled for printing)
    Route::get('/application/{application}/print', [ApplicationController::class, 'print'])
        ->name('application.print');

    // 🔎 Download dossier as PDF
    Route::get('/application/{application}/pdf', [ApplicationController::class, 'pdf'])
        ->name('application.pdf');

    // 🔎 Export dossier to Excel
    Route::get('/application/{application}/export/excel', [ApplicationController::class, 'exportExcel'])
        ->name('application.export.excel');

    // 🔎 Export dossier to CSV
    Route::get('/application/{application}/export/csv', [ApplicationController::class, 'exportCsv'])
        ->name('application.export.csv');

    // Initialize payment for an application
    Route::post('/application/{application}/pay', [PaymentController::class, 'initialize'])
        ->name('payment.initialize');

    // Handle Paystack callback (browser redirect)
    Route::get('/payment/callback', [PaymentController::class, 'callback'])
        ->name('payment.callback');

    // Student dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Admission letter download
    Route::get('/admission-letter/{application}', [AdmissionLetterController::class, 'show'])
        ->name('admission.letter');
});
