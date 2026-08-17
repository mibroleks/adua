<?php

/**
 * Component: Application Model
 * File Path: app/Models/Application.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Central aggregate/root record for an admission application.
 * Owns the relationship graph for:
 * - Applicant (User)
 * - Programme
 * - Dynamic field values
 * - Documents
 * - Payments
 * - Admission decision
 * - Status history
 *
 * Status: ✅ Production Ready
 * Version: 2.3 (applicationFeePayment latestOfMany fix)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use InvalidArgumentException;

class Application extends Model
{
    protected $fillable = [
        'application_number',
        'user_id',
        'programme_id',
        'application_fee',
        'application_status',
        'payment_status',
        'submitted_at',
    ];

    protected $casts = [
        'application_fee' => 'decimal:2',
        'submitted_at'    => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Status Constants
    |--------------------------------------------------------------------------
    */
    public const STATUS_DRAFT        = 'DRAFT';
    public const STATUS_SUBMITTED    = 'SUBMITTED';
    public const STATUS_UNDER_REVIEW = 'UNDER_REVIEW';
    public const STATUS_APPROVED     = 'APPROVED';
    public const STATUS_REJECTED     = 'REJECTED';

    public const PAYMENT_PENDING     = 'PENDING';
    public const PAYMENT_SUCCESS     = 'SUCCESS';
    public const PAYMENT_FAILED      = 'FAILED';

    public const APPLICATION_STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SUBMITTED,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
    ];

    public const PAYMENT_STATUSES = [
        self::PAYMENT_PENDING,
        self::PAYMENT_SUCCESS,
        self::PAYMENT_FAILED,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function fieldValues(): HasMany
    {
        return $this->hasMany(ApplicationFieldValue::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class);
    }

    // Allow multiple payments
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Latest payment helper (for Blade usage)
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    // Latest application-fee payment helper
    public function applicationFeePayment(): HasOne
    {
        return $this->hasOne(Payment::class)
            ->where('payment_type', Payment::TYPE_APPLICATION_FEE)
            ->latestOfMany();
    }

    public function decision(): HasOne
    {
        return $this->hasOne(AdmissionDecision::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */
    public function setApplicationStatus(string $newStatus, ?int $officerId = null): void
    {
        if (! in_array($newStatus, self::APPLICATION_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid application status: {$newStatus}");
        }

        $oldStatus = $this->application_status;

        if ($oldStatus === $newStatus) {
            return;
        }

        $this->application_status = $newStatus;
        $this->save();

        $this->statusHistories()->create([
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $officerId,
            'changed_at' => now(),
        ]);
    }

    public function setPaymentStatus(string $newStatus): void
    {
        if (! in_array($newStatus, self::PAYMENT_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid payment status: {$newStatus}");
        }

        $this->payment_status = $newStatus;
        $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Fee Snapshot
    |--------------------------------------------------------------------------
    */
    public function snapshotFee(): void
    {
        $this->loadMissing('programme');

        if ($this->programme) {
            $this->application_fee = $this->programme->application_fee;
            $this->save();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Display Helpers
    |--------------------------------------------------------------------------
    */
    public function getApplicantNameAttribute(): string
    {
        return $this->user?->name ?? 'Unknown Applicant';
    }

    public function getFormattedApplicationFeeAttribute(): string
    {
        return $this->application_fee === null
            ? '—'
            : '₦' . number_format((float) $this->application_fee, 2);
    }

    public function getFormattedSubmittedAtAttribute(): string
    {
        return $this->submitted_at?->format('d M Y, H:i') ?? '—';
    }

    /*
    |--------------------------------------------------------------------------
    | Complete Dossier Scope
    |--------------------------------------------------------------------------
    */
    public function scopeWithCompleteDossier($query)
    {
        return $query->with([
            'user',
            'programme.faculty',
            'programme.department',
            'fieldValues.formField',
            'documents.documentType',
            'payments',
            'decision.officer',
            'statusHistories.officer',
        ]);
    }
}
