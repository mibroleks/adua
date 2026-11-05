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
 * Status: 🚦 Hardened
 * Version: 1.2 (improved unread notification handling)
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display the applicant's notifications.
     *
     * Route name: notifications.index
     * URL: /notifications
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user
            ->notifications()
            ->latest()
            ->get();

        $unreadNotificationCount = $user
            ->unreadNotifications()
            ->count();

        return view('notifications.index', compact(
            'notifications',
            'unreadNotificationCount'
        ));
    }

    /**
     * Mark a single notification as read.
     *
     * Route name: notifications.read
     * URL: /notifications/{id}/read
     *
     * Security:
     * - Only the authenticated user's notifications are accessible.
     * - Already-read notifications are not updated unnecessarily.
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return redirect()
            ->route('notifications.index')
            ->with('status', 'Notification marked as read.');
    }

    /**
     * Mark all unread notifications as read.
     *
     * Route name: notifications.readAll
     * URL: /notifications/read-all
     *
     * Only unread notifications belonging to the authenticated user
     * are affected. Existing read notifications remain unchanged.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->unreadNotifications()
            ->update([
                'read_at' => now(),
            ]);

        return redirect()
            ->route('notifications.index')
            ->with('status', 'All notifications marked as read.');
    }
}
