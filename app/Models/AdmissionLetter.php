<?php

/*
Component: AdmissionLetter Model
File Path: app/Models/AdmissionLetter.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Represents admission-related letters issued to applicants,
including official institutional admission offer letters.

Responsibilities:
- Link letters to applications and admission decisions.
- Maintain unique official letter numbers.
- Preserve issued document content snapshots.
- Track letter versions and lifecycle status.
- Identify issuing and withdrawing officers.
- Preserve historical letter records.

Architecture:
- Application
- AdmissionDecision
- User
- AdmissionLetterService

Status: Integration / Hardening
Version: 2.0
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionLetter extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Letter Types
    |--------------------------------------------------------------------------
    */

    public const TYPE_OFFER = 'OFFER';

    public const TYPE_CONDITIONAL = 'CONDITIONAL';

    public const TYPE_REJECTION = 'REJECTION';

    /*
    |--------------------------------------------------------------------------
    | Lifecycle Statuses
    |--------------------------------------------------------------------------
    */

    public const STATUS_DRAFT = 'DRAFT';

    public const STATUS_ISSUED = 'ISSUED';

    public const STATUS_WITHDRAWN = 'WITHDRAWN';

    public const STATUS_SUPERSEDED = 'SUPERSEDED';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ISSUED,
        self::STATUS_WITHDRAWN,
        self::STATUS_SUPERSEDED,
    ];

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        // Existing fields
        'application_id',
        'admission_decision_id',
        'letter_number',
        'type',
        'issued_by',
        'issued_at',
        'published_at',
        'file_path',

        // Offer lifecycle fields
        'version',
        'status',
        'content_snapshot',
        'withdrawn_at',
        'withdrawn_by',
        'withdrawal_reason',
        'superseded_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attribute Casting
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'version' => 'integer',
        'issued_at' => 'datetime',
        'published_at' => 'datetime',
        'content_snapshot' => 'array',
        'withdrawn_at' => 'datetime',
        'superseded_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function decision(): BelongsTo
    {
        return $this->belongsTo(
            AdmissionDecision::class,
            'admission_decision_id'
        );
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'issued_by'
        );
    }

    public function withdrawnBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'withdrawn_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    public function isWithdrawn(): bool
    {
        return $this->status === self::STATUS_WITHDRAWN;
    }

    public function isSuperseded(): bool
    {
        return $this->status === self::STATUS_SUPERSEDED;
    }

    public function isAdmissionOffer(): bool
    {
        return $this->type === self::TYPE_OFFER;
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeIssued($query)
    {
        return $query->where('status', self::STATUS_ISSUED);
    }

    public function scopeWithdrawn($query)
    {
        return $query->where('status', self::STATUS_WITHDRAWN);
    }

    public function scopeSuperseded($query)
    {
        return $query->where('status', self::STATUS_SUPERSEDED);
    }

    public function scopeAdmissionOffers($query)
    {
        return $query->where('type', self::TYPE_OFFER);
    }
}
