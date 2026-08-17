<?php

/**
 * Component: Dashboard Controller
 * File Path: app/Http/Controllers/DashboardController.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Shows the student dashboard with their application details.
 * Loads programme, payment, documents, and admission decision relations.
 * Ensures the authenticated student only sees their own application.
 *
 * Status: ✅ Production Ready
 * Version: 1.4
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;

class DashboardController extends Controller
{
    /**
     * Display the student dashboard.
     */
    public function index(Request $request)
    {
        $application = Application::with([
                'programme.department.faculty', // full academic hierarchy
                'payment',
                'documents.type',
                'decision.officer',
                'statusHistories.officer',      // audit trail
            ])
            ->where('user_id', $request->user()->id)
            ->first();

        return view('dashboard', [
            'application' => $application, // may be null if not yet applied
        ]);
    }
}
