<?php

/*
Component: Portal Config Service
File Path: app/Services/PortalConfigService.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides centralized access to portal behaviour and admissions cycle configuration.
Reads values from the settings table via the Setting model.
Ensures officers can toggle applications/payments and control admissions cycle
without touching code.

Architecture:
- Settings table stores portal.* and admissions.* values.
- PortalConfigService exposes typed getters for behaviour flags and cycle dates.
- Prevents hardcoding or scattered Setting::get calls in controllers.
- Enforces rules server-side for security.

Status: ✅ Production Ready
Version: 1.0 (Filament 5.7.6 compatible)
*/

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;

class PortalConfigService
{
    /*
    | Portal Behaviour
    */
    public function applicationsEnabled(): bool
    {
        return (bool) Setting::get('portal.enable_applications', true);
    }

    public function paymentsEnabled(): bool
    {
        return (bool) Setting::get('portal.enable_payments', true);
    }

    public function applicationTitle(): string
    {
        return Setting::get('portal.application_title', 'Admissions Portal');
    }

    public function welcomeMessage(): string
    {
        return Setting::get('portal.welcome_message', 'Welcome to the admissions portal.');
    }

    public function footerText(): string
    {
        return Setting::get('portal.footer_text', 'All rights reserved.');
    }

    /*
    | Admissions Cycle
    */
    public function applicationStart(): ?Carbon
    {
        $date = Setting::get('admissions.application_start');
        return $date ? Carbon::parse($date) : null;
    }

    public function applicationDeadline(): ?Carbon
    {
        $date = Setting::get('admissions.application_deadline');
        return $date ? Carbon::parse($date) : null;
    }

    public function applicationFee(): int
    {
        return (int) Setting::get('admissions.application_fee', 0);
    }

    public function applicationStatus(): string
    {
        $now = Carbon::now();
        $start = $this->applicationStart();
        $deadline = $this->applicationDeadline();

        if ($start && $now->lt($start)) {
            return 'Not started';
        }

        if ($deadline && $now->gt($deadline)) {
            return 'Closed';
        }

        return 'Open';
    }
}
