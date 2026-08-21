<?php

/**
 * Component: Logout Controller
 * File Path: app/Http/Controllers/Auth/LogoutController.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Handles secure logout for authenticated applicants.
 * Invalidates session and regenerates CSRF token.
 *
 * Status: ✅ Production Ready
 * Version: 1.0
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')
            ->with('status', 'You have been logged out successfully.');
    }
}
