<?php

/*
Component: Generic Notification
File Path: app/Notifications/GenericNotification.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides a reusable notification wrapper for all lifecycle events.
Accepts a standardized payload (title, message, link, type).
Supports database + broadcast channels for portal + real-time UI.

Status: ✅ Production Ready
Version: 1.0
*/

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class GenericNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Channels: database + broadcast.
     */
    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Store in database.
     */
    public function toDatabase($notifiable): DatabaseMessage
    {
        return new DatabaseMessage($this->payload);
    }

    /**
     * Broadcast for real-time UI.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload);
    }
}
