<?php

/**
 * Component: Payment Policy
 * File Path: app/Policies/PaymentPolicy.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Defines authorization rules for payment records.
 * Students can view their own payments, officers can view all,
 * and sensitive actions are restricted to finance/admin roles.
 *
 * Status: ✅ Production Ready
 * Version: 1.3 (Hardened)
 */

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Determine whether the user can view any payments.
     */
    public function viewAny(User $user): bool
    {
        // Officers can view all payments; students can view their own via Filament.
        return $user->isOfficer() || $user->isStudent();
    }

    /**
     * Determine whether the user can view a specific payment.
     */
    public function view(User $user, Payment $payment): bool
    {
        return $user->isOfficer() || $payment->application->user_id === $user->id;
    }

    /**
     * Students should not manually create payments — handled by PaymentService.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Prevent arbitrary updates — payments are immutable audit records.
     */
    public function update(User $user, Payment $payment): bool
    {
        return false;
    }

    /**
     * Prevent deletion of payment records — audit integrity.
     */
    public function delete(User $user, Payment $payment): bool
    {
        return false;
    }

    /**
     * Prevent restore of payment records.
     */
    public function restore(User $user, Payment $payment): bool
    {
        return false;
    }

    /**
     * Prevent force delete of payment records.
     */
    public function forceDelete(User $user, Payment $payment): bool
    {
        return false;
    }

    /**
     * Reserved future action: controlled reconciliation by finance/admin.
     */
    public function reconcile(User $user, Payment $payment): bool
    {
        return $user->isFinanceAdmin();
    }
}
