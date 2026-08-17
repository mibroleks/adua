<?php

/*
Component: Fortify Configuration
File Path: config/fortify.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Configures Laravel Fortify authentication features and guards.

Architecture:
- Public registration creates only students.
- Officers must be created via Filament admin panel.
- Supports registration, password reset, profile updates, passkeys, and two-factor authentication.
- Guard set to web, passwords broker set to users.

Status: ✅ Production Ready
Version: 1.0 (restricted officer registration, extended features)
*/

use Laravel\Fortify\Features;

return [

    // Guard used for authentication
    'guard' => 'web',

    // Password broker for reset operations
    'passwords' => 'users',

    // Username/email field
    'username' => 'email',
    'email' => 'email',

    // Lowercase usernames before saving
    'lowercase_usernames' => true,

    // Redirect path after login/reset
    'home' => '/dashboard',

    // Route prefix and domain
    'prefix' => '',
    'domain' => null,

    // Middleware for Fortify routes
    'middleware' => ['web'],

    // Rate limiting
    'limiters' => [
        'login' => 'login',
        'two-factor' => 'two-factor',
        'passkeys' => 'passkeys',
    ],

    // Enable Fortify view routes
    'views' => true,

    // Passkey (WebAuthn) configuration
    'passkeys' => [
        'relying_party_id' => parse_url(config('app.url'), PHP_URL_HOST),
        'allowed_origins' => [config('app.url')],
        'timeout' => 60000,
    ],

    // Enabled features
    'features' => [
        Features::registration(),              // Students only
        Features::resetPasswords(),
        // Features::emailVerification(),      // Optional
        Features::updateProfileInformation(),
        Features::updatePasswords(),
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]),
        Features::passkeys([
            'confirmPassword' => true,
        ]),
    ],

];
