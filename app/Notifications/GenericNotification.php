<?php

/*
Component: Generic Notification
File Path: app/Notifications/GenericNotification.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides a reusable notification wrapper for lifecycle events.
Accepts a standardized payload (title, message, link, type).
Supports database, broadcast and optional email channels.

Architecture:
- Database and broadcast notifications remain available.
- Email delivery is explicitly enabled for applicant-facing events.
- Queued notifications are dispatched after database commit.
- Applicant emails use the institution's configured branding.
- Email presentation is isolated in a dedicated Blade view.

Status: Integration / Hardening
Version: 2.2
*/

namespace App\Notifications;

use App\Models\Setting;
use App\Services\ThemeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GenericNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $payload;

    protected bool $sendEmail;

    protected array $emailLines;

    public function __construct(
        array $payload,
        bool $sendEmail = false,
        array $emailLines = []
    ) {
        $this->payload = $payload;
        $this->sendEmail = $sendEmail;
        $this->emailLines = $emailLines;

        /*
         * Do not allow queued notification jobs to run
         * before the surrounding database transaction commits.
         *
         * This also protects notifications dispatched from
         * nested service transactions.
         */
        $this->afterCommit();
    }

    /**
     * Notification delivery channels.
     *
     * Database and broadcast remain enabled for every
     * notification. Email is explicitly enabled per event.
     */
    public function via($notifiable): array
    {
        $channels = [
            'database',
            'broadcast',
        ];

        if ($this->sendEmail) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Store the notification in the database.
     */
    public function toDatabase($notifiable): DatabaseMessage
    {
        return new DatabaseMessage($this->payload);
    }

    /**
     * Broadcast the notification for real-time portal updates.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload);
    }

    /**
     * Send the branded applicant-facing email.
     *
     * Uses the institution's existing ThemeService configuration.
     * The dedicated Blade view keeps this presentation separate
     * from password-reset and unrelated system emails.
     */
    public function toMail($notifiable): MailMessage
    {
        /*
         * Resolve the existing institutional branding service.
         */
        $theme = app(ThemeService::class);

        /*
         * Always use the light palette for email readability.
         * Email clients do not reliably support application
         * theme switching or external CSS variables.
         */
        $tokens = $theme->tokens('light');

        /*
         * Institutional identity.
         */
        $institutionName = $theme->institutionName();

        $shortName = $theme->shortName();

        $logoUrl = $theme->logoUrl();

        $fontFamily = $theme->fontFamily();

        /*
         * Notification content.
         *
         * Do not use the variable name $message here.
         * Laravel injects its own $message object when rendering
         * notification mail views.
         */
        $subject = $this->payload['title']
            ?? $institutionName;

        $greeting = 'Hello ' . (
            $notifiable->name ?? 'Applicant'
        ) . ',';

        $notificationMessage = $this->payload['message']
            ?? 'You have received a new notification from the institution.';

        $emailLines = $this->emailLines;

        /*
         * Preserve the existing portal action link.
         */
        $actionUrl = $this->payload['link'] ?? null;

        $actionText = 'View in Portal';

        $salutation = 'Regards, ' . $institutionName;

        /*
         * Institutional contact information.
         *
         * These values come from the existing settings records.
         * Missing values are handled by the Blade view.
         */
        $address = Setting::get('institution.address');

        $institutionEmail = Setting::get('institution.email');

        $institutionPhone = Setting::get('institution.phone');

        $website = Setting::get('institution.website');

        /*
         * Render the dedicated branded applicant email.
         *
         * No global mail layout or password-reset notification
         * presentation is modified.
         */
        return (new MailMessage())
            ->subject($subject)
            ->view('emails.notifications.applicant', [
                // Notification content
                'subject' => $subject,
                'title' => $subject,
                'greeting' => $greeting,
                'notificationMessage' => $notificationMessage,
                'emailLines' => $emailLines,
                'actionUrl' => $actionUrl,
                'actionText' => $actionText,
                'salutation' => $salutation,

                // Institutional identity
                'institutionName' => $institutionName,
                'shortName' => $shortName,
                'logoUrl' => $logoUrl,

                // Institutional contact information
                'address' => $address,
                'institutionEmail' => $institutionEmail,
                'institutionPhone' => $institutionPhone,
                'website' => $website,

                // Selected institutional appearance
                'tokens' => $tokens,
                'fontFamily' => $fontFamily,
            ]);
    }
}
