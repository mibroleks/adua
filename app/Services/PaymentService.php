<?php

/**
 * Component: Payment Service
 * File Path: app/Services/PaymentService.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Handles payment initialization and verification with Paystack.
 *
 * Responsibilities:
 * - initialize Paystack transactions
 * - verify Paystack transactions
 * - maintain payment lifecycle
 * - update application payment status
 * - trigger application submission after successful payment
 * - dispatch applicant notifications
 *
 * Status: 🚦 Integration / Hardening
 * Version: 2.5 (Verification fixed to use requested_amount)
 */

namespace App\Services;

use App\Models\Application;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    protected AdmissionService $admissions;
    protected NotificationService $notifications;

    public function __construct(AdmissionService $admissions, NotificationService $notifications)
    {
        $this->admissions    = $admissions;
        $this->notifications = $notifications;
    }

    /**
     * Initialize a payment with Paystack.
     *
     * Returns a canonical application-level response.
     */
    public function initialize(Application $application): array
    {
        $fee = $application->application_fee;

        if ($fee === null) {
            return [
                'success' => false,
                'authorization_url' => null,
                'reference' => null,
                'message' => 'Application fee not configured.',
            ];
        }

        $amountInKobo = (int) $fee;
        if ($amountInKobo <= 0) {
            return [
                'success' => false,
                'authorization_url' => null,
                'reference' => null,
                'message' => 'Application fee must be greater than zero.',
            ];
        }

        // Check for existing pending payment
        $existing = Payment::where('application_id', $application->id)
            ->where('status', Payment::STATUS_PENDING)
            ->first();

        if ($existing) {
            $verified = $this->verify($existing->reference);

            if ($verified && $verified->status === Payment::STATUS_SUCCESS) {
                return [
                    'success' => true,
                    'authorization_url' => null,
                    'reference' => $verified->reference,
                    'message' => 'Payment already completed.',
                    'already_paid' => true,
                ];
            }

            return [
                'success' => true,
                'authorization_url' => null,
                'reference' => $existing->reference,
                'message' => 'Payment already initialized, awaiting completion.',
                'already_paid' => false,
            ];
        }

        // Create local payment record
        $payment = Payment::create([
            'application_id'        => $application->id,
            'reference'             => 'ADM-' . Str::upper(Str::random(20)),
            'transaction_reference' => Str::uuid()->toString(),
            'payment_type'          => Payment::TYPE_APPLICATION_FEE,
            'amount'                => $amountInKobo,
            'currency'              => 'NGN',
            'status'                => Payment::STATUS_PENDING,
            'gateway'               => 'paystack',
        ]);

        // Initialize transaction with Paystack
        $response = Http::withToken(config('services.paystack.secret'))
            ->post(rtrim(config('services.paystack.url'), '/') . '/transaction/initialize', [
                'reference'     => $payment->reference,
                'amount'        => $payment->amount,
                'email'         => $application->user->email,
                'currency'      => $payment->currency,
                'callback_url'  => route('payment.callback'),
            ]);

        if ($response->failed()) {
            $payment->update(['status' => Payment::STATUS_FAILED]);

            Log::error('Paystack transaction initialization failed.', [
                'application_id' => $application->id,
                'payment_id'     => $payment->id,
                'reference'      => $payment->reference,
                'http_status'    => $response->status(),
                'response'       => $response->json(),
            ]);

            return [
                'success' => false,
                'authorization_url' => null,
                'reference' => $payment->reference,
                'message' => 'Failed to initialize Paystack transaction.',
            ];
        }

        $data             = $response->json('data');
        $authorizationUrl = $data['authorization_url'] ?? null;
        $reference        = $data['reference'] ?? $payment->reference;

        if (! $authorizationUrl) {
            $payment->update([
                'status'   => Payment::STATUS_FAILED,
                'metadata' => $response->json(),
            ]);

            Log::error('Paystack initialization returned no authorization URL.', [
                'application_id' => $application->id,
                'payment_id'     => $payment->id,
                'reference'      => $payment->reference,
                'response'       => $response->json(),
            ]);

            return [
                'success' => false,
                'authorization_url' => null,
                'reference' => $reference,
                'message' => 'Paystack did not return a payment authorization URL.',
            ];
        }

        return [
            'success'          => true,
            'authorization_url'=> $authorizationUrl,
            'reference'        => $reference,
            'message'          => $response->json('message') ?? 'Payment initialized successfully.',
            'already_paid'     => false,
        ];
    }

    /**
     * Verify a payment with Paystack.
     */
    public function verify(string $reference): ?Payment
    {
        $payment = Payment::where('reference', $reference)->first();
        if (! $payment) {
            return null;
        }

        if ($payment->status === Payment::STATUS_SUCCESS) {
            return $payment;
        }

        $response = Http::withToken(config('services.paystack.secret'))
            ->get(rtrim(config('services.paystack.url'), '/') . "/transaction/verify/{$reference}");

        if ($response->failed()) {
            Log::error('Paystack payment verification request failed.', [
                'reference'  => $reference,
                'payment_id' => $payment->id,
            ]);
            return null;
        }

        $data = $response->json('data');

        // ✅ Compare requested_amount to our payment.amount, not amount (which includes gateway fees)
        $requestedAmount = (int) ($data['requested_amount'] ?? 0);
        $currency        = $data['currency'] ?? null;
        $status          = $data['status'] ?? null;

        $isSuccessful = $data
            && $status === 'success'
            && $requestedAmount === (int) $payment->amount
            && $currency === $payment->currency;

        if ($isSuccessful) {
            DB::transaction(function () use ($payment, $data) {
                $payment->update([
                    'status'     => Payment::STATUS_SUCCESS,
                    'paid_at'    => now(),
                    'verified_at'=> now(),
                    'metadata'   => $data,
                ]);

                $application = $payment->application->fresh();
                $application->setPaymentStatus(Application::PAYMENT_SUCCESS);

                // 🔔 Notify applicant of payment success
                $this->notifications->paymentSuccess(
                    $application->user,
                    $payment->reference,
                    $payment->amount_in_naira
                );

                if (
                    $payment->payment_type === Payment::TYPE_APPLICATION_FEE &&
                    $application->application_status === Application::STATUS_DRAFT
                ) {
                    $this->admissions->submit($application);
                }
            });

            Log::info('Payment successfully verified.', [
                'payment_id'     => $payment->id,
                'application_id' => $payment->application_id,
                'reference'      => $payment->reference,
                'requested_amount'=> $requestedAmount,
                'customer_paid'   => $data['amount'] ?? null,
                'gateway_fee'     => $data['fees'] ?? null,
            ]);

            return $payment->fresh();
        }

        $payment->update([
            'status'   => Payment::STATUS_FAILED,
            'metadata' => $data,
        ]);

        $payment->application->setPaymentStatus(Application::PAYMENT_FAILED);

        Log::warning('Payment verification did not succeed.', [
            'payment_id'     => $payment->id,
            'application_id' => $payment->application_id,
            'reference'      => $payment->reference,
            'requested_amount'=> $requestedAmount,
            'customer_paid'   => $data['amount'] ?? null,
        ]);

        return $payment->fresh();
    }

    public function missingRequiredDocuments(Application $application): array
    {
        return $this->admissions->missingRequiredDocuments($application);
    }

    public function validateWebhook(string $payload, string $signature): bool
    {
        $expected = hash_hmac('sha512', $payload, config('services.paystack.secret'));
        return hash_equals($expected, $signature);
    }
}
