<?php

/*
Component: Referral Partner Seeder
File Path: database/seeders/ReferralPartnerSeeder.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Registers approved recruitment and referral partners.

Architecture:
- Supports multiple referral partners.
- Admilify is registered using the official code "admilify".
- Pathfinder is registered using the official code "pathfinder".
- Seeding is idempotent; repeated execution does not create duplicates.
- Referral attribution remains independent of admission and payment decisions.

Status: Implementation
Version: 1.1 (Admilify and Pathfinder registration)
*/

namespace Database\Seeders;

use App\Models\ReferralPartner;
use Illuminate\Database\Seeder;

class ReferralPartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ReferralPartner::updateOrCreate(
            [
                'code' => 'admilify',
            ],
            [
                'name' => 'Admilify',
                'is_active' => true,
                'notes' => 'Approved recruitment referral partner for ADUA admissions.',
            ]
        );

        ReferralPartner::updateOrCreate(
            [
                'code' => 'pathfinder',
            ],
            [
                'name' => 'Pathfinder',
                'is_active' => true,
                'notes' => 'Approved recruitment referral partner for ADUA admissions.',
            ]
        );
    }
}
