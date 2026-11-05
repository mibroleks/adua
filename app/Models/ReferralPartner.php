<?php

/*
Component: Referral Partner Model
File Path: app/Models/ReferralPartner.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Represents approved recruitment and referral partners.

Architecture:
- Supports multiple recruitment and referral partners.
- Each partner has a unique referral code.
- Only active partners can receive new referral attribution.
- Existing historical attribution remains available when a partner
  is deactivated.
- Referral partners do not participate in admission or payment decisions.

Status: Implementation
Version: 1.0 (initial referral partner model)
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralPartner extends Model
{
    /**
     * Attributes that can be mass assigned.
     */
    protected $fillable = [
        'name',
        'code',
        'is_active',
        'notes',
    ];

    /**
     * Attribute casting.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Users originally attributed to this referral partner.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'referral_partner_id');
    }

    /**
     * Applications attributed to this referral partner.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'referral_partner_id');
    }

    /**
     * Retrieve an active referral partner using its referral code.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Find an active partner using a referral code.
     */
    public static function findActiveByCode(string $code): ?self
    {
        $code = trim($code);

        if ($code === '') {
            return null;
        }

        return static::query()
            ->active()
            ->whereRaw('LOWER(code) = ?', [strtolower($code)])
            ->first();
    }
}
