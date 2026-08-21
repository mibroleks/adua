<?php

/**
 * Component: Notification Controller
 * File Path: app/Http/Controllers/NotificationController.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Shows the logged-in applicant their notifications.
 * Provides endpoints to mark notifications as read individually or in bulk.
 * Ensures the authenticated student only sees and manages their own notifications.
 *
 * Status: 🚦 Integration / Hardening
 * Version: 1.1 (added markAsRead + markAllAsRead)
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display the applicant’s notifications.
     *
     * Route name: notifications.index
     * URL: /notifications
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->get();

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a single notification as read.
     *
     * Route name: notifications.read
     * URL: /notifications/{id}/read
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);

        $notification->markAsRead();

        return redirect()
            ->route('notifications.index')
            ->with('status', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     *
     * Route name: notifications.readAll
     * URL: /notifications/read-all
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()
            ->route('notifications.index')
            ->with('status', 'All notifications marked as read.');
    }
}
