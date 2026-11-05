<?php

/*
Component: Referral Attribution Service
File Path: app/Services/ReferralAttributionService.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Captures and validates recruitment referral attribution.

Architecture:
- Referral codes must belong to registered active partners.
- The first valid referral attribution is preserved.
- Invalid and inactive referral codes are ignored.
- Referral attribution is stored in the applicant's session
  until registration persists it against the user.
- Referral attribution does not affect admission or payment decisions.

Status: Implementation
Version: 1.0 (initial referral attribution service)
*/

namespace App\Services;

use App\Models\ReferralPartner;
use Illuminate\Http\Request;

class ReferralAttributionService
{
    /**
     * Session key used to retain referral attribution.
     */
    private const SESSION_KEY = 'referral_partner_id';

    /**
     * Capture a valid referral code from the incoming request.
     *
     * The first valid referral is preserved. Later referral URLs
     * cannot silently replace an existing valid attribution.
     */
    public function capture(Request $request): ?ReferralPartner
    {
        $existingPartner = $this->getCapturedPartner($request);

        if ($existingPartner) {
            return $existingPartner;
        }

        $code = $request->query('ref');

        if (! is_string($code) || trim($code) === '') {
            return null;
        }

        $partner = ReferralPartner::findActiveByCode($code);

        if (! $partner) {
            return null;
        }

        $request->session()->put(self::SESSION_KEY, $partner->id);

        return $partner;
    }

    /**
     * Retrieve the referral partner currently captured in the session.
     *
     * The partner must still exist, but it does not need to remain active
     * after the original referral has been captured.
     */
    public function getCapturedPartner(Request $request): ?ReferralPartner
    {
        $partnerId = $request->session()->get(self::SESSION_KEY);

        if (! $partnerId) {
            return null;
        }

        return ReferralPartner::query()->find($partnerId);
    }

    /**
     * Retrieve the captured referral partner ID.
     */
    public function getCapturedPartnerId(Request $request): ?int
    {
        return $this->getCapturedPartner($request)?->id;
    }

    /**
     * Clear captured referral attribution from the session.
     */
    public function clear(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }
}
