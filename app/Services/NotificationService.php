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
- Official admission offer issued

Important:
Plural route names are canonical (applications.*).
Methods accept User + application_number for compatibility with AdmissionService.

Email Policy:
- Applicant-facing lifecycle notifications support email.
- Internal and non-email notifications retain database and broadcast delivery.
- Official offer emails require a successfully issued offer letter.

Dispatch Safety:
- Notification dispatch is deferred until after database commit.
- Dispatch exceptions are logged.
- Notification dispatch failures must not make committed business
  operations appear unsuccessful.

Status: Integration / Hardening
Version: 2.1
*/

namespace App\Services;

use App\Models\AdmissionLetter;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class NotificationService
{
    /**
     * Send a standardized notification.
     *
     * Email is opt-in at this level so that existing
     * non-email notifications retain their current behaviour.
     *
     * Dispatch is deferred until the surrounding database
     * transaction commits. Dispatch failures are logged and
     * must not make a committed business operation appear failed.
     */
    public function notify(
        User $user,
        string $title,
        string $message,
        ?string $link = null,
        string $type = 'general',
        bool $sendEmail = false,
        array $emailLines = []
    ): void {
        $payload = [
            'title'   => $title,
            'message' => $message,
            'link'    => $link,
            'type'    => $type,
        ];

        $notification = new GenericNotification(
            $payload,
            $sendEmail,
            $emailLines
        );

        $context = [
            'recipient_user_id' => $user->getKey(),
            'notification_title' => $title,
            'notification_type' => $type,
            'email_enabled' => $sendEmail,
        ];

        DB::afterCommit(function () use (
            $user,
            $notification,
            $context
        ): void {
            try {
                Notification::send($user, $notification);
            } catch (Throwable $exception) {
                Log::error(
                    'Notification dispatch failed after database commit.',
                    $context + [
                        'exception' => $exception,
                    ]
                );
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Applicant Lifecycle Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Notify an applicant of a successful payment.
     */
    public function paymentSuccess(
        User $user,
        string $reference,
        float $amountNaira
    ): void {
        $this->notify(
            $user,
            'Payment Successful',
            'Your payment of ₦'
                . number_format($amountNaira, 2)
                . " (Ref: {$reference}) was successful.",
            route('applications.payment'),
            'payment',
            true
        );
    }

    /**
     * Notify an applicant that their application was submitted.
     */
    public function applicationSubmitted(
        User $user,
        string $applicationNumber
    ): void {
        $this->notify(
            $user,
            'Application Submitted',
            "Your application {$applicationNumber} has been submitted successfully.",
            route('application.status', $applicationNumber),
            'application',
            true
        );
    }

    /**
     * Notify an applicant that corrections are required.
     */
    public function correctionRequired(
        User $user,
        string $applicationNumber,
        string $remarks
    ): void {
        $this->notify(
            $user,
            'Correction Required',
            "Your application {$applicationNumber} requires correction: {$remarks}",
            route('application.status', $applicationNumber),
            'application',
            true
        );
    }

    /**
     * Notify an applicant that application processing was approved.
     *
     * This is not an official admission offer.
     */
    public function applicationApproved(
        User $user,
        string $applicationNumber
    ): void {
        $this->notify(
            $user,
            'Application Processing Approved',
            "Your application {$applicationNumber} has been approved at the application-processing stage. This is not yet an official admission offer. Your institutional admission outcome will be communicated separately.",
            route('application.status', $applicationNumber),
            'application',
            true
        );
    }

    /**
     * Notify an applicant that their application was rejected.
     */
    public function applicationRejected(
        User $user,
        string $applicationNumber,
        ?string $remarks = null
    ): void {
        $message = "Your application {$applicationNumber} has been rejected.";

        if ($remarks) {
            $message .= " Reason: {$remarks}";
        }

        $this->notify(
            $user,
            'Application Rejected',
            $message,
            route('application.status', $applicationNumber),
            'application',
            true
        );
    }

    /**
     * Notify an applicant that a submitted document was rejected.
     */
    public function documentRejected(
        User $user,
        string $docName,
        ?string $reason = null
    ): void {
        $message = "Your document '{$docName}' was rejected.";

        if ($reason) {
            $message .= " Reason: {$reason}";
        }

        $this->notify(
            $user,
            'Document Rejected',
            $message,
            route('applications.documents'),
            'document',
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Official Admission Offer
    |--------------------------------------------------------------------------
    */

    /**
     * Notify an applicant after a new official admission offer
     * has been successfully issued.
     *
     * The actual AdmissionLetter record is used as the source
     * of truth for its number, version and content snapshot.
     */
    public function officialAdmissionOfferIssued(
        AdmissionLetter $letter
    ): void {
        /*
         * Never email draft, withdrawn, superseded or
         * non-offer letter records.
         */
        if (
            ! $letter->isAdmissionOffer()
            || ! $letter->isIssued()
        ) {
            return;
        }

        $letter->loadMissing([
            'application.user',
        ]);

        $application = $letter->application;
        $user = $application?->user;

        if (! $user) {
            return;
        }

        $snapshot = $letter->content_snapshot ?? [];

        $document = $snapshot['document'] ?? [];
        $programme = $snapshot['programme'] ?? [];

        $letterNumber = $letter->letter_number;
        $version = $letter->version;

        $issuedDate = $letter->issued_at?->format('F j, Y')
            ?? ($document['issued_date'] ?? null);

        $programmeName = $programme['name'] ?? null;

        $message = sprintf(
            'Congratulations! An official admission offer has been issued for your application %s.',
            $application->application_number
        );

        $emailLines = [
            'Official letter number: ' . $letterNumber,
            'Offer version: ' . $version,
        ];

        if ($issuedDate) {
            $emailLines[] = 'Issue date: ' . $issuedDate;
        }

        if ($programmeName) {
            $emailLines[] = 'Programme: ' . $programmeName;
        }

        $this->notify(
            $user,
            'Official Admission Offer Issued',
            $message,
            route('application.status', $application->application_number),
            'admission_offer',
            filled($user->email),
            $emailLines
        );
    }
}
