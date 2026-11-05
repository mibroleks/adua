<?php

/**
 * Component: Officer Login Page
 * File Path: app/Filament/Pages/Auth/OfficerLogin.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Provides the custom authentication page for the Officer Panel.
 *
 * Responsibilities:
 * - Extends Filament 5's native Login page.
 * - Retains Filament's authentication functionality.
 * - Uses a dedicated Blade view for the premium login interface.
 * - Preserves the existing authentication and password recovery workflow.
 *
 * Design:
 * The visual presentation is handled by the dedicated Blade view
 * and Officer Panel login stylesheet.
 *
 * Status: Production Implementation
 * Version: 1.2
 * Filament: 5.x
 */

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class OfficerLogin extends BaseLogin
{
    /**
     * Custom login page view.
     */
    protected string $view = 'filament.pages.auth.officer-login';
}
