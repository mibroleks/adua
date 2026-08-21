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
 * Status: 🚦 Integration / Hardening
 * Version: 2.6 (application_fee stored in kobo as integer + display helpers)
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
        'application_fee',     // stored in kobo
        'application_status',
        'payment_status',
        'submitted_at',
    ];

    protected $casts = [
        'application_fee' => 'integer',   // stored in kobo
        'submitted_at'    => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Status Constants
    |--------------------------------------------------------------------------
    */
    public const STATUS_DRAFT                = 'DRAFT';
    public const STATUS_SUBMITTED            = 'SUBMITTED';
    public const STATUS_UNDER_REVIEW         = 'UNDER_REVIEW';
    public const STATUS_APPROVED             = 'APPROVED';
    public const STATUS_REJECTED             = 'REJECTED';
    public const STATUS_CORRECTION_REQUIRED  = 'CORRECTION_REQUIRED';

    public const PAYMENT_PENDING             = 'PENDING';
    public const PAYMENT_SUCCESS             = 'SUCCESS';
    public const PAYMENT_FAILED              = 'FAILED';

    public const APPLICATION_STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SUBMITTED,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_CORRECTION_REQUIRED,
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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

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
            $this->application_fee = $this->programme->application_fee; // already in kobo
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

    public function getApplicationFeeInNairaAttribute(): float
    {
        return $this->application_fee / 100;
    }

    public function getFormattedApplicationFeeAttribute(): string
    {
        return $this->application_fee === null
            ? '—'
            : '₦' . number_format($this->application_fee / 100, 2);
    }

    public function getFormattedSubmittedAtAttribute(): string
    {
        return $this->submitted_at?->format('d M Y, H:i') ?? '—';
    }

    public function statusLabel(): string
    {
        return match ($this->application_status) {
            self::STATUS_DRAFT               => 'Draft',
            self::STATUS_SUBMITTED           => 'Submitted',
            self::STATUS_UNDER_REVIEW        => 'Under Review',
            self::STATUS_APPROVED            => 'Approved',
            self::STATUS_REJECTED            => 'Rejected',
            self::STATUS_CORRECTION_REQUIRED => 'Correction Required',
            default                          => ucfirst(strtolower($this->application_status ?? 'Unknown')),
        };
    }

    public function paymentLabel(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_PENDING => 'Payment Pending',
            self::PAYMENT_SUCCESS => 'Payment Complete',
            self::PAYMENT_FAILED  => 'Payment Failed',
            default               => ucfirst(strtolower($this->payment_status ?? 'Unpaid')),
        };
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
