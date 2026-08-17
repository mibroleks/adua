<?php

/*
Component: User Model
File Path: app/Models/User.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Handles authentication, roles, applications, and admission decisions.

Architecture:
- Single User model for both students and officers.
- Role column defines type (student default, officer via Filament).
- Relationships link to applications and admission decisions.
- Officers cannot self-register; only admins create them via Filament.

Status: ✅ Production Ready
Version: 1.0 (role-based authentication foundation)
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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

    // Relationships
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function decisions()
    {
        return $this->hasMany(AdmissionDecision::class, 'officer_id');
    }
}
