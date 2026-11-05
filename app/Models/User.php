<?php

/*
Component: User Model
File Path: app/Models/User.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Handles authentication, roles, applications, admission decisions,
recruitment referral attribution, and personal portal preferences.

Architecture:
- Single User model for both students and officers.
- Role column defines type (student default, officer via Filament).
- Relationships link to applications, admission decisions,
  and referral partners.
- Referral attribution is optional and does not affect admission
  or payment decisions.
- Personal theme preference belongs to the individual user.
- Officers cannot self-register; only admins create them via Filament.

Status: Implementation
Version: 2.2 (personal portal theme preference)
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Filament imports
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * Supported personal theme modes.
     */
    public const THEME_MODES = [
        'light',
        'dark',
    ];

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'referral_partner_id',
        'theme_mode',
    ];

    /**
     * Hidden attributes for arrays.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Role helpers

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isOfficer(): bool
    {
        return $this->role === 'officer';
    }

    /**
     * Return the user's valid personal theme preference.
     *
     * Null means the institution's configured theme mode
     * should be used.
     */
    public function preferredThemeMode(): ?string
    {
        $mode = $this->theme_mode;

        return in_array($mode, self::THEME_MODES, true)
            ? $mode
            : null;
    }

    // Relationships

    /**
     * User's applications.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Admission decisions handled by this officer.
     */
    public function decisions(): HasMany
    {
        return $this->hasMany(AdmissionDecision::class, 'officer_id');
    }

    /**
     * Referral partner associated with this user's registration.
     */
    public function referralPartner(): BelongsTo
    {
        return $this->belongsTo(ReferralPartner::class, 'referral_partner_id');
    }

    /**
     * Filament panel access control.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'officer' => $this->isOfficer(),
            default => false,
        };
    }
}