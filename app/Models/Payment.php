<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'application_id',
        'reference',
        'transaction_reference',
        'payment_type',
        'amount',              // University/application fee in kobo
        'currency',
        'status',
        'gateway',
        'metadata',            // Gateway metadata (requested_amount, amount, fees, etc.)
        'paid_at',
        'verified_at',
    ];

    protected $casts = [
        'metadata'    => 'array',
        'paid_at'     => 'datetime',
        'verified_at' => 'datetime',
    ];

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
    | Display Helpers (Accessors + Proxy Method)
    |--------------------------------------------------------------------------
    */
    // Proper accessor for Eloquent/Filament
    public function getAmountInNairaAttribute(): float
    {
        return $this->amount ? $this->amount / 100 : 0.0;
    }

    public function getFormattedAmountAttribute(): string
    {
        return $this->amount === null
            ? '—'
            : '₦' . number_format($this->amount / 100, 2);
    }

    // Proxy method for backward compatibility
    public function amountInNaira(): float
    {
        return $this->getAmountInNairaAttribute();
    }

    /*
    |--------------------------------------------------------------------------
    | Gateway Metadata Helpers
    |--------------------------------------------------------------------------
    */
    // Application/merchant amount (what the university charges)
    public function getApplicationAmountAttribute(): ?int
    {
        return $this->amount;
    }

    // Gateway fee (if passed to customer)
    public function getGatewayFeeAttribute(): float
    {
        return isset($this->metadata['fees'])
            ? $this->metadata['fees'] / 100
            : 0.0;
    }

    // Customer total paid (application fee + gateway fee)
    public function getCustomerPaidAmountAttribute(): float
    {
        return isset($this->metadata['amount'])
            ? $this->metadata['amount'] / 100
            : $this->getAmountInNairaAttribute();
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
