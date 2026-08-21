<?php

/*
Component: Notification Service
File Path: app/Services/NotificationService.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Centralizes notification creation and dispatch.
Ensures consistent payloads across lifecycle events:
- Payment success
- Application submission
- Correction required
- Application approved/rejected
- Document rejected

Important:
Plural route names are canonical (applications.*).
Methods accept User + application_number for compatibility with AdmissionService.

Status: 🚦 Integration / Hardening
Version: 1.2 (fixed method signatures)
*/

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GenericNotification;

class NotificationService
{
    /**
     * Send a standardized notification.
     */
    public function notify(User $user, string $title, string $message, ?string $link = null, string $type = 'general'): void
    {
        $payload = [
            'title'   => $title,
            'message' => $message,
            'link'    => $link,
            'type'    => $type,
        ];

        Notification::send($user, new GenericNotification($payload));
    }

    /*
    |--------------------------------------------------------------------------
    | Lifecycle Helpers
    |--------------------------------------------------------------------------
    */
    public function paymentSuccess(User $user, string $reference, float $amountNaira): void
    {
        $this->notify(
            $user,
            'Payment Successful',
            "Your payment of ₦" . number_format($amountNaira, 2) . " (Ref: {$reference}) was successful.",
            route('applications.payment'),
            'payment'
        );
    }

    public function applicationSubmitted(User $user, string $applicationNumber): void
    {
        $this->notify(
            $user,
            'Application Submitted',
            "Your application {$applicationNumber} has been submitted successfully.",
            route('application.status', $applicationNumber),
            'application'
        );
    }

    public function correctionRequired(User $user, string $applicationNumber, string $remarks): void
    {
        $this->notify(
            $user,
            'Correction Required',
            "Your application {$applicationNumber} requires correction: {$remarks}",
            route('application.status', $applicationNumber),
            'application'
        );
    }

    public function applicationApproved(User $user, string $applicationNumber): void
    {
        $this->notify(
            $user,
            'Application Approved',
            "Congratulations! Your application {$applicationNumber} has been approved.",
            route('application.status', $applicationNumber),
            'application'
        );
    }

    public function applicationRejected(User $user, string $applicationNumber, ?string $remarks = null): void
    {
        $msg = "Your application {$applicationNumber} has been rejected.";
        if ($remarks) {
            $msg .= " Reason: {$remarks}";
        }

        $this->notify(
            $user,
            'Application Rejected',
            $msg,
            route('application.status', $applicationNumber),
            'application'
        );
    }

    public function documentRejected(User $user, string $docName, ?string $reason = null): void
    {
        $msg = "Your document '{$docName}' was rejected.";
        if ($reason) {
            $msg .= " Reason: {$reason}";
        }

        $this->notify(
            $user,
            'Document Rejected',
            $msg,
            route('applications.documents'),
            'document'
        );
    }
}
