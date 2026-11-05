<?php

/*
Component: Fortify Create New User Action
File Path: app/Actions/Fortify/CreateNewUser.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Validates registration details and creates new applicant accounts.

Architecture:
- Preserves existing registration validation.
- Uses the existing password validation rules.
- Associates a captured referral partner with the new user when available.
- Referral attribution is optional.
- Referral attribution does not affect admission or payment decisions.
- Clears the captured referral from the session after successful registration
  because the attribution has now been persisted against the user.

Status: Implementation
Version: 1.1 (added referral attribution during registration)
*/

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\ReferralAttributionService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        /*
         * Retrieve the referral partner captured from the public referral link.
         *
         * The service returns null when no valid referral was captured.
         * An already captured referral remains available even if the partner
         * has since been deactivated.
         */
        $referralAttribution = app(ReferralAttributionService::class);

        $referralPartner = $referralAttribution->getCapturedPartner(request());

        /*
         * Create the user and persist referral attribution when available.
         *
         * Existing registration behaviour is preserved.
         */
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'referral_partner_id' => $referralPartner?->id,
        ]);

        /*
         * The referral is now stored against the user account.
         * Clear only the referral attribution session entry.
         */
        $referralAttribution->clear(request());

        return $user;
    }
}
