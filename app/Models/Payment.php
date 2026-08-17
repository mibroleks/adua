<?php

/**
 * Component: Payment Model
 * File Path: app/Models/Payment.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Represents a payment record linked to a student application.
 * Stores transaction reference, amount, currency, gateway, metadata, and status.
 * Provides helpers for success checks and amount formatting.
 *
 * Status: ✅ Production Ready
 * Version: 1.6 (Added helper methods and type enforcement)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'application_id',
        'reference',
        'transaction_reference', // internal transaction reference
        'payment_type',          // APPLICATION_FEE, ACCEPTANCE_FEE, etc.
        'amount',
        'currency',
        'status',
        'gateway',
        'metadata',
        'paid_at',
        'verified_at',           // server-side verification timestamp
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'metadata'    => 'array',
        'paid_at'     => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Relationship: Payment belongs to an Application.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /*
    |--------------------------------------------------------------------------
    | Display Helpers
    |--------------------------------------------------------------------------
    */
    public function amountInNaira(): float
    {
        return $this->amount / 100;
    }

    /*
    |--------------------------------------------------------------------------
    | Status Constants
    |--------------------------------------------------------------------------
    */
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_SUCCESS = 'SUCCESS';
    public const STATUS_FAILED  = 'FAILED';

    /*
    |--------------------------------------------------------------------------
    | Payment Type Constants
    |--------------------------------------------------------------------------
    */
    public const TYPE_APPLICATION_FEE = 'APPLICATION_FEE';
    public const TYPE_ACCEPTANCE_FEE  = 'ACCEPTANCE_FEE';
    public const TYPE_SCHOOL_FEE      = 'SCHOOL_FEE';
    public const TYPE_SEMESTER_FEE    = 'SEMESTER_FEE';
    public const TYPE_ACCOMMODATION   = 'ACCOMMODATION';
    public const TYPE_OTHER           = 'OTHER';
}
