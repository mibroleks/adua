<?php

/**
 * Component: AdmissionDecision Model
 * File Path: app/Models/AdmissionDecision.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Represents an officer’s admission decision on a student application.
 * Stores decision outcome, remarks, officer reference, and timestamp.
 * Links to the application and officer user record.
 *
 * Status: ✅ Production Ready
 * Version: 1.6 (normalized field name to `decision`)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionDecision extends Model
{
    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'application_id',
        'officer_id',
        'decision',   // APPROVED or REJECTED
        'remarks',
        'decided_at',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'decided_at' => 'datetime',
    ];

    /**
     * Relationship: Decision belongs to an Application.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Relationship: Decision belongs to an Officer (User).
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    /**
     * Decision constants to avoid typos.
     */
    public const DECISION_APPROVED = 'APPROVED';
    public const DECISION_REJECTED = 'REJECTED';

    /**
     * Helper: Check if decision is approved.
     */
    public function isApproved(): bool
    {
        return $this->decision === self::DECISION_APPROVED;
    }

    /**
     * Helper: Check if decision is rejected.
     */
    public function isRejected(): bool
    {
        return $this->decision === self::DECISION_REJECTED;
    }
}
