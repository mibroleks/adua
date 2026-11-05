<?php

/*
Component: AdmissionDecision Model
File Path: app/Models/AdmissionDecision.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Maintains application-processing decisions and the separate
institutional admission outcome for an application.

Responsibilities:
- Preserve application approval and rejection records.
- Store the actual admission outcome separately.
- Identify the officers responsible for each decision.
- Maintain admission decision history.
- Provide reusable decision status constants and helpers.

Architecture:
- Application approval does not automatically mean admission.
- Admission decisions are recorded separately.
- Admission letters are issued through a separate process.
- Historical decision records must be preserved.

Version: 2.1
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdmissionDecision extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Application-processing decision constants
    |--------------------------------------------------------------------------
    */

    public const DECISION_APPROVED = 'APPROVED';

    public const DECISION_REJECTED = 'REJECTED';

    /*
    |--------------------------------------------------------------------------
    | Institutional admission outcome constants
    |--------------------------------------------------------------------------
    */

    public const ADMISSION_STATUS_PENDING = 'PENDING';

    public const ADMISSION_STATUS_ADMITTED = 'ADMITTED';

    public const ADMISSION_STATUS_NOT_ADMITTED = 'NOT_ADMITTED';

    public const ADMISSION_STATUS_WAITLISTED = 'WAITLISTED';

    public const ADMISSION_STATUS_DEFERRED = 'DEFERRED';

    /**
     * Available institutional admission outcomes.
     */
    public const ADMISSION_STATUSES = [
        self::ADMISSION_STATUS_PENDING,
        self::ADMISSION_STATUS_ADMITTED,
        self::ADMISSION_STATUS_NOT_ADMITTED,
        self::ADMISSION_STATUS_WAITLISTED,
        self::ADMISSION_STATUS_DEFERRED,
    ];

    /*
    |--------------------------------------------------------------------------
    | Mass assignable attributes
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        // Application-processing decision
        'application_id',
        'officer_id',
        'decision',
        'remarks',
        'decided_at',

        // Institutional admission decision
        'admission_status',
        'admission_officer_id',
        'admission_decided_at',
        'admission_remarks',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attribute casting
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'decided_at' => 'datetime',
        'admission_decided_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Decision belongs to an application.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Officer responsible for application processing.
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    /**
     * Officer responsible for the actual admission outcome.
     */
    public function admissionOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admission_officer_id');
    }

    /**
     * Historical changes to the institutional admission outcome.
     */
    public function admissionHistories(): HasMany
    {
        return $this->hasMany(AdmissionDecisionHistory::class)
            ->orderByDesc('changed_at')
            ->orderByDesc('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Application decision helpers
    |--------------------------------------------------------------------------
    */

    public function isApproved(): bool
    {
        return $this->decision === self::DECISION_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->decision === self::DECISION_REJECTED;
    }

    public function isApplicationApproved(): bool
    {
        return $this->decision === self::DECISION_APPROVED;
    }

    public function isApplicationRejected(): bool
    {
        return $this->decision === self::DECISION_REJECTED;
    }

    /*
    |--------------------------------------------------------------------------
    | Institutional admission decision helpers
    |--------------------------------------------------------------------------
    */

    public function isAdmissionPending(): bool
    {
        return $this->admission_status === self::ADMISSION_STATUS_PENDING;
    }

    public function isAdmitted(): bool
    {
        return $this->admission_status === self::ADMISSION_STATUS_ADMITTED;
    }

    public function isNotAdmitted(): bool
    {
        return $this->admission_status === self::ADMISSION_STATUS_NOT_ADMITTED;
    }

    public function isWaitlisted(): bool
    {
        return $this->admission_status === self::ADMISSION_STATUS_WAITLISTED;
    }

    public function isDeferred(): bool
    {
        return $this->admission_status === self::ADMISSION_STATUS_DEFERRED;
    }
}
