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
 * - initialize and resume application-fee payment attempts
 * - verify Paystack transactions before recovering checkout
 * - validate actual and requested payment amounts, currency and reference
 * - account for explicitly reported Paystack transaction fees during verification
 * - preserve unresolved payment attempts for reconciliation
 * - prevent duplicate concurrent application-fee attempts
 * - create a fresh reference only after the previous attempt is terminal
 * - maintain application payment status
 * - trigger application submission after successful payment
 * - dispatch applicant notifications after successful confirmation
 *
 * Status: Integration / Hardening
 * Version: 2.9 (Fee-Aware Verification, Atomic Recovery and Safe Confirmation)
 */

namespace App\Services;

use App\Models\Application;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class PaymentService
{
    protected AdmissionService $admissions;

    protected NotificationService $notifications;

    public function __construct(
        AdmissionService $admissions,
        NotificationService $notifications
    ) {
        $this->admissions = $admissions;
        $this->notifications = $notifications;
    }

    /**
     * Initialize or recover an application-fee payment.
     *
     * An unresolved attempt must never be replaced by a new attempt.
     * A fresh reference is created only after the previous attempt is
     * confirmed terminal, or when no prior attempt is pending.
     */
    public function initialize(Application $application): array
    {
        $fee = $application->application_fee;

        if ($fee === null || (int) $fee <= 0) {
            return [
                'success' => false,
                'authorization_url' => null,
                'reference' => null,
                'already_paid' => false,
                'message' => $fee === null
                    ? 'Application fee not configured.'
                    : 'Application fee must be greater than zero.',
            ];
        }

        /*
         * Check for a successful payment before considering a new attempt.
         */
        $successfulPayment = $this->findSuccessfulApplicationFee(
            $application->id
        );

        if ($successfulPayment) {
            return $this->alreadyPaidResponse($successfulPayment);
        }

        /*
         * Recover an existing pending attempt first.
         */
        $existingPending = $this->findPendingApplicationFee(
            $application->id
        );

        if ($existingPending) {
            $recovery = $this->recoverPendingPayment($existingPending);

            if ($recovery !== null) {
                return $recovery;
            }

            /*
             * A null recovery result means verification established that
             * the previous attempt is terminally failed. A new reference
             * may now be considered.
             */
        }

        /*
         * Recheck under an application-row lock before creating an attempt.
         *
         * This protects concurrent checkout requests from independently
         * creating multiple pending application-fee payments.
         *
         * Gateway HTTP requests are deliberately performed outside this
         * database transaction.
         */
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $result = $this->createOrFindApplicationFeeAttempt(
                $application,
                (int) $fee
            );

            if ($result['kind'] === 'paid') {
                return $this->alreadyPaidResponse($result['payment']);
            }

            if ($result['kind'] === 'created') {
                return $this->initializeGatewayTransaction(
                    $application,
                    $result['payment']
                );
            }

            if ($result['kind'] === 'pending') {
                $recovery = $this->recoverPendingPayment(
                    $result['payment']
                );

                if ($recovery !== null) {
                    return $recovery;
                }

                /*
                 * The concurrent pending attempt became terminally failed
                 * during verification. Recheck before creating a new one.
                 */
                continue;
            }
        }

        Log::warning(
            'Payment initialization stopped after repeated concurrent recovery attempts.',
            [
                'application_id' => $application->id,
            ]
        );

        return [
            'success' => false,
            'authorization_url' => null,
            'reference' => null,
            'already_paid' => false,
            'message' => 'Your payment status is still being checked. No additional payment has been created. Please try again shortly.',
        ];
    }

    /**
     * Find a successful application-fee payment.
     */
    protected function findSuccessfulApplicationFee(
        int|string $applicationId
    ): ?Payment {
        return Payment::query()
            ->where('application_id', $applicationId)
            ->where('payment_type', Payment::TYPE_APPLICATION_FEE)
            ->where('status', Payment::STATUS_SUCCESS)
            ->latest('id')
            ->first();
    }

    /**
     * Find the most recent pending application-fee payment.
     */
    protected function findPendingApplicationFee(
        int|string $applicationId
    ): ?Payment {
        return Payment::query()
            ->where('application_id', $applicationId)
            ->where('payment_type', Payment::TYPE_APPLICATION_FEE)
            ->where('status', Payment::STATUS_PENDING)
            ->latest('id')
            ->first();
    }

    /**
     * Format the response for an already-paid application.
     */
    protected function alreadyPaidResponse(Payment $payment): array
    {
        return [
            'success' => true,
            'authorization_url' => null,
            'reference' => $payment->reference,
            'already_paid' => true,
            'message' => 'Payment already completed.',
        ];
    }

    /**
     * Recover a pending payment.
     *
     * Returns null only when verification has established that the attempt
     * is terminally failed and a new attempt may be considered.
     */
    protected function recoverPendingPayment(Payment $payment): ?array
    {
        $verified = $this->verify($payment->reference);

        $payment = $verified ?? $payment->fresh();

        if (! $payment) {
            return [
                'success' => false,
                'authorization_url' => null,
                'reference' => null,
                'already_paid' => false,
                'message' => 'We could not retrieve your previous payment attempt. Please try again shortly.',
            ];
        }

        if ($payment->status === Payment::STATUS_SUCCESS) {
            return $this->alreadyPaidResponse($payment);
        }

        if ($payment->status === Payment::STATUS_FAILED) {
            return null;
        }

        if ($payment->status !== Payment::STATUS_PENDING) {
            Log::warning(
                'Payment recovery stopped because the attempt has an unexpected status.',
                [
                    'application_id' => $payment->application_id,
                    'payment_id' => $payment->id,
                    'reference' => $payment->reference,
                    'status' => $payment->status,
                ]
            );

            return [
                'success' => false,
                'authorization_url' => null,
                'reference' => $payment->reference,
                'already_paid' => false,
                'message' => 'We could not safely determine the state of your previous payment. Please try again shortly or contact support.',
            ];
        }

        $savedUrl = data_get(
            $payment->metadata ?? [],
            'authorization_url'
        );

        if ($this->isValidCheckoutUrl($savedUrl)) {
            return [
                'success' => true,
                'authorization_url' => $savedUrl,
                'reference' => $payment->reference,
                'already_paid' => false,
                'message' => 'Your existing payment checkout is being resumed.',
            ];
        }

        Log::warning(
            'Pending payment has no usable checkout URL; reinitialization was skipped.',
            [
                'application_id' => $payment->application_id,
                'payment_id' => $payment->id,
                'reference' => $payment->reference,
            ]
        );

        return [
            'success' => false,
            'authorization_url' => null,
            'reference' => $payment->reference,
            'already_paid' => false,
            'message' => 'Your previous payment attempt is still being checked. No new payment has been created. Please try again shortly or contact support if the problem continues.',
        ];
    }

    /**
     * Validate a checkout URL before returning it to the controller.
     */
    protected function isValidCheckoutUrl(mixed $url): bool
    {
        if (
            ! is_string($url)
            || filter_var($url, FILTER_VALIDATE_URL) === false
        ) {
            return false;
        }

        $parts = parse_url($url);

        return is_array($parts)
            && strtolower((string) ($parts['scheme'] ?? '')) === 'https'
            && ! empty($parts['host']);
    }

    /**
     * Atomically find or create an application-fee attempt.
     *
     * The application row is locked while successful and pending attempts
     * are rechecked. The actual Paystack initialization request happens
     * after this transaction commits.
     *
     * @return array{kind: string, payment: Payment}
     */
    protected function createOrFindApplicationFeeAttempt(
        Application $application,
        int $amountInKobo
    ): array {
        return DB::transaction(function () use (
            $application,
            $amountInKobo
        ): array {
            $lockedApplication = Application::query()
                ->whereKey($application->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $successfulPayment = $this->findSuccessfulApplicationFee(
                $lockedApplication->id
            );

            if ($successfulPayment) {
                return [
                    'kind' => 'paid',
                    'payment' => $successfulPayment,
                ];
            }

            $pendingPayment = $this->findPendingApplicationFee(
                $lockedApplication->id
            );

            if ($pendingPayment) {
                return [
                    'kind' => 'pending',
                    'payment' => $pendingPayment,
                ];
            }

            $payment = Payment::create([
                'application_id' => $lockedApplication->id,
                'reference' => 'ADM-' . Str::upper(Str::random(20)),
                'transaction_reference' => Str::uuid()->toString(),
                'payment_type' => Payment::TYPE_APPLICATION_FEE,
                'amount' => $amountInKobo,
                'currency' => 'NGN',
                'status' => Payment::STATUS_PENDING,
                'gateway' => 'paystack',
            ]);

            return [
                'kind' => 'created',
                'payment' => $payment,
            ];
        }, 3);
    }

    /**
     * Initialize a new Paystack checkout for a newly created payment.
     *
     * Never call this method to reinitialize an existing reference.
     */
    protected function initializeGatewayTransaction(
        Application $application,
        Payment $payment
    ): array {
        $secret = config('services.paystack.secret');
        $baseUrl = rtrim((string) config('services.paystack.url'), '/');

        if (
            ! is_string($secret)
            || $secret === ''
            || $baseUrl === ''
        ) {
            Log::error('Paystack configuration is incomplete.', [
                'application_id' => $application->id,
                'payment_id' => $payment->id,
                'reference' => $payment->reference,
            ]);

            return [
                'success' => false,
                'authorization_url' => null,
                'reference' => $payment->reference,
                'already_paid' => false,
                'message' => 'Payment service is temporarily unavailable. Your payment attempt has been preserved. Please try again shortly.',
            ];
        }

        $email = $application->user?->email;

        if (! is_string($email) || trim($email) === '') {
            Log::error('Payment initialization could not find an applicant email address.', [
                'application_id' => $application->id,
                'payment_id' => $payment->id,
                'reference' => $payment->reference,
            ]);

            return [
                'success' => false,
                'authorization_url' => null,
                'reference' => $payment->reference,
                'already_paid' => false,
                'message' => 'We could not prepare checkout because the applicant email address is missing. Please contact support.',
            ];
        }

        try {
            $response = Http::withToken($secret)
                ->acceptJson()
                ->connectTimeout(10)
                ->timeout(30)
                ->post($baseUrl . '/transaction/initialize', [
                    'reference' => $payment->reference,
                    'amount' => (int) $payment->amount,
                    'email' => $email,
                    'currency' => $payment->currency,
                    'callback_url' => route('payment.callback'),
                ]);
        } catch (Throwable $exception) {
            Log::error('Paystack initialization request threw an exception.', [
                'application_id' => $application->id,
                'payment_id' => $payment->id,
                'reference' => $payment->reference,
                'exception' => $exception->getMessage(),
            ]);

            /*
             * The request may have reached Paystack even if the response
             * was lost. Preserve the pending record for reconciliation.
             */
            return [
                'success' => false,
                'authorization_url' => null,
                'reference' => $payment->reference,
                'already_paid' => false,
                'message' => 'We could not reconnect to the payment service. Your payment attempt has been preserved while its status is checked. Please try again shortly.',
            ];
        }

        $payload = $response->json();
        $authorizationUrl = data_get($payload, 'data.authorization_url');
        $gatewayReference = data_get($payload, 'data.reference');

        $referenceMatches = is_string($gatewayReference)
            && hash_equals($payment->reference, $gatewayReference);

        if (
            $response->successful()
            && data_get($payload, 'status') === true
            && $this->isValidCheckoutUrl($authorizationUrl)
            && $referenceMatches
        ) {
            $metadata = $payment->metadata ?? [];
            $metadata['authorization_url'] = $authorizationUrl;
            $metadata['initialization_response'] = $payload;

            $payment->update([
                'metadata' => $metadata,
            ]);

            return [
                'success' => true,
                'authorization_url' => $authorizationUrl,
                'reference' => $payment->reference,
                'already_paid' => false,
                'message' => 'Payment checkout is ready.',
            ];
        }

        Log::warning('Paystack did not return a usable checkout URL.', [
            'application_id' => $application->id,
            'payment_id' => $payment->id,
            'reference' => $payment->reference,
            'http_status' => $response->status(),
            'gateway_reference' => $gatewayReference,
            'response' => $payload,
        ]);

        /*
         * Do not mark this attempt failed based solely on an initialization
         * error. Verification must establish its actual state.
         */
        return [
            'success' => false,
            'authorization_url' => null,
            'reference' => $payment->reference,
            'already_paid' => false,
            'message' => 'We could not open checkout yet. Your payment attempt has been preserved while its status is checked. Please try again shortly.',
        ];
    }

    /**
     * Verify a payment with Paystack.
     *
     * A successful payment must match:
     * - the local reference
     * - the requested amount
     * - the actual amount charged, including only a verified gateway fee
     * - the local currency
     *
     * Paystack may report the customer charge as the requested amount
     * plus its transaction fee when fee passing is enabled.
     *
     * The local Payment::amount remains the institution's intended amount.
     * Gateway fees are retained separately in the verification metadata.
     *
     * Only explicit Paystack failed/abandoned states mark an unresolved
     * attempt as FAILED. Other states remain unresolved.
     */
    public function verify(string $reference): ?Payment
    {
        $payment = Payment::query()
            ->where('reference', $reference)
            ->first();

        if (! $payment) {
            return null;
        }

        if ($payment->status === Payment::STATUS_SUCCESS) {
            return $payment;
        }

        /*
         * Do not return early for FAILED records. If a late callback or
         * webhook arrives, query Paystack so a successful gateway payment
         * can be flagged for reconciliation without resurrecting the record.
         */
        $secret = config('services.paystack.secret');
        $baseUrl = rtrim((string) config('services.paystack.url'), '/');

        if (
            ! is_string($secret)
            || $secret === ''
            || $baseUrl === ''
        ) {
            Log::error('Paystack verification skipped because configuration is incomplete.', [
                'payment_id' => $payment->id,
                'reference' => $reference,
            ]);

            return $payment;
        }

        try {
            $response = Http::withToken($secret)
                ->acceptJson()
                ->connectTimeout(10)
                ->timeout(30)
                ->get(
                    $baseUrl . '/transaction/verify/' . rawurlencode($reference)
                );
        } catch (Throwable $exception) {
            Log::error('Paystack verification request threw an exception.', [
                'payment_id' => $payment->id,
                'reference' => $reference,
                'exception' => $exception->getMessage(),
            ]);

            return $payment->fresh();
        }

        if ($response->failed()) {
            Log::warning('Paystack payment verification request failed.', [
                'payment_id' => $payment->id,
                'reference' => $reference,
                'http_status' => $response->status(),
            ]);

            return $payment->fresh();
        }

        $payload = $response->json();
        $data = data_get($payload, 'data');

        if (
            data_get($payload, 'status') !== true
            || ! is_array($data)
        ) {
            Log::warning('Paystack returned an inconclusive verification response.', [
                'payment_id' => $payment->id,
                'reference' => $reference,
                'http_status' => $response->status(),
                'response' => $payload,
            ]);

            return $payment->fresh();
        }

        $gatewayReference = $data['reference'] ?? null;
        $gatewayStatus = strtolower((string) ($data['status'] ?? ''));
        $currency = $data['currency'] ?? null;

        $requestedAmount = isset($data['requested_amount'])
            ? (int) $data['requested_amount']
            : null;

        $actualAmount = isset($data['amount'])
            ? (int) $data['amount']
            : null;

        /*
         * Paystack reports fees in the currency's smallest denomination.
         *
         * Do not assume a missing, null, or non-numeric fee is zero.
         * An unexplained difference must remain unverified.
         */
        $gatewayFees = isset($data['fees'])
            && is_numeric($data['fees'])
                ? (int) $data['fees']
                : null;

        $referenceMatches = is_string($gatewayReference)
            && hash_equals($payment->reference, $gatewayReference);

        /*
         * The amount requested from Paystack must always match the
         * amount stored locally for this payment.
         */
        $requestedAmountMatches = $requestedAmount !== null
            && $requestedAmount === (int) $payment->amount;

        /*
         * The actual amount must be explained by Paystack's verified
         * transaction response.
         *
         * Accepted:
         * - Actual amount equals requested amount.
         * - Actual amount equals requested amount plus reported fees.
         *
         * Rejected:
         * - Any other amount difference.
         * - Missing fees when the actual amount exceeds the requested amount.
         */
        $actualAmountMatches = $requestedAmount !== null
            && $actualAmount !== null
            && (
                $actualAmount === $requestedAmount
                || (
                    $gatewayFees !== null
                    && $gatewayFees >= 0
                    && $actualAmount === (
                        $requestedAmount + $gatewayFees
                    )
                )
            );

        $currencyMatches = is_string($currency)
            && strtoupper($currency) === strtoupper($payment->currency);

        /*
         * A reported success with mismatched transaction details is not
         * accepted. Preserve the local status for investigation.
         */
        if ($gatewayStatus === 'success') {
            if (
                ! $referenceMatches
                || ! $requestedAmountMatches
                || ! $actualAmountMatches
                || ! $currencyMatches
            ) {
                Log::critical(
                    'Paystack reported success but transaction details did not match the local payment.',
                    [
                        'payment_id' => $payment->id,
                        'application_id' => $payment->application_id,
                        'local_reference' => $payment->reference,
                        'gateway_reference' => $gatewayReference,
                        'local_amount' => (int) $payment->amount,
                        'gateway_requested_amount' => $requestedAmount,
                        'gateway_actual_amount' => $actualAmount,
                        'gateway_fees' => $gatewayFees,
                        'local_currency' => $payment->currency,
                        'gateway_currency' => $currency,
                    ]
                );

                return $payment->fresh();
            }

            /*
             * The transaction returns whether this request actually changed
             * the payment from a non-terminal state to SUCCESS.
             */
            $transition = DB::transaction(function () use (
                $payment,
                $data
            ): string {
                $lockedPayment = Payment::query()
                    ->whereKey($payment->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedPayment->status === Payment::STATUS_SUCCESS) {
                    return 'already_success';
                }

                /*
                 * Never resurrect a failed attempt. A late Paystack success
                 * must be reconciled separately.
                 */
                if ($lockedPayment->status === Payment::STATUS_FAILED) {
                    return 'failed_conflict';
                }

                if ($lockedPayment->status !== Payment::STATUS_PENDING) {
                    return 'unexpected_status';
                }

                $metadata = $lockedPayment->metadata ?? [];
                $metadata = array_merge($metadata, $data);

                $lockedPayment->update([
                    'status' => Payment::STATUS_SUCCESS,
                    'paid_at' => now(),
                    'verified_at' => now(),
                    'metadata' => $metadata,
                ]);

                $application = $lockedPayment->application()->firstOrFail();

                $application->setPaymentStatus(
                    Application::PAYMENT_SUCCESS
                );

                /*
                 * Keep submission in the same transaction as payment
                 * confirmation so a submission exception cannot leave a
                 * partially committed confirmation workflow.
                 */
                if (
                    $lockedPayment->payment_type === Payment::TYPE_APPLICATION_FEE
                    && $application->application_status === Application::STATUS_DRAFT
                ) {
                    $this->admissions->submit($application);
                }

                return 'confirmed';
            }, 3);

            $freshPayment = $payment->fresh();

            if ($transition === 'failed_conflict') {
                Log::critical(
                    'Paystack reports success for a locally failed payment attempt; reconciliation is required.',
                    [
                        'payment_id' => $payment->id,
                        'application_id' => $payment->application_id,
                        'reference' => $payment->reference,
                        'gateway_amount' => $actualAmount,
                    ]
                );

                return $freshPayment;
            }

            if ($transition === 'unexpected_status') {
                Log::critical(
                    'Paystack reports success for a payment with an unexpected local status.',
                    [
                        'payment_id' => $payment->id,
                        'reference' => $payment->reference,
                        'status' => $freshPayment?->status,
                    ]
                );

                return $freshPayment;
            }

            /*
             * Only the request that performed the state transition dispatches
             * the success notification. Duplicate callbacks do not repeat it.
             */
            if ($transition === 'confirmed') {
                try {
                    $confirmedPayment = $freshPayment;
                    $application = $confirmedPayment?->application;
                    $user = $application?->user;

                    if ($user && $confirmedPayment) {
                        $this->notifications->paymentSuccess(
                            $user,
                            $confirmedPayment->reference,
                            $confirmedPayment->amount_in_naira
                        );
                    } else {
                        Log::warning(
                            'Payment was confirmed, but its applicant notification could not be dispatched because the applicant relationship was missing.',
                            [
                                'payment_id' => $payment->id,
                                'application_id' => $payment->application_id,
                                'reference' => $payment->reference,
                            ]
                        );
                    }
                } catch (Throwable $exception) {
                    /*
                     * The payment is already committed. Do not roll back or
                     * misreport the successful payment because notification
                     * dispatch failed.
                     */
                    Log::error(
                        'Payment was confirmed, but the success notification failed.',
                        [
                            'payment_id' => $payment->id,
                            'application_id' => $payment->application_id,
                            'reference' => $payment->reference,
                            'exception' => $exception->getMessage(),
                        ]
                    );
                }

                Log::info('Payment successfully verified.', [
                    'payment_id' => $payment->id,
                    'application_id' => $payment->application_id,
                    'reference' => $payment->reference,
                    'requested_amount' => $requestedAmount,
                    'customer_paid' => $actualAmount,
                    'gateway_fee' => $gatewayFees,
                ]);
            }

            return $freshPayment;
        }

        /*
         * Only explicit terminal gateway states can mark a payment FAILED.
         */
        if (in_array($gatewayStatus, ['failed', 'abandoned'], true)) {
            $transition = DB::transaction(function () use (
                $payment,
                $data
            ): string {
                $lockedPayment = Payment::query()
                    ->whereKey($payment->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedPayment->status === Payment::STATUS_SUCCESS) {
                    return 'already_success';
                }

                if ($lockedPayment->status === Payment::STATUS_FAILED) {
                    return 'already_failed';
                }

                if ($lockedPayment->status !== Payment::STATUS_PENDING) {
                    return 'unexpected_status';
                }

                $metadata = $lockedPayment->metadata ?? [];
                $metadata = array_merge($metadata, $data);

                $lockedPayment->update([
                    'status' => Payment::STATUS_FAILED,
                    'metadata' => $metadata,
                ]);

                $application = $lockedPayment->application()->firstOrFail();

                /*
                 * Do not overwrite payment status if another attempt already
                 * succeeded or is still pending.
                 */
                $hasSuccessfulApplicationFee = Payment::query()
                    ->where('application_id', $application->id)
                    ->where('payment_type', Payment::TYPE_APPLICATION_FEE)
                    ->where('status', Payment::STATUS_SUCCESS)
                    ->exists();

                if (! $hasSuccessfulApplicationFee) {
                    $hasPendingApplicationFee = Payment::query()
                        ->where('application_id', $application->id)
                        ->where('payment_type', Payment::TYPE_APPLICATION_FEE)
                        ->where('status', Payment::STATUS_PENDING)
                        ->exists();

                    if (! $hasPendingApplicationFee) {
                        $application->setPaymentStatus(
                            Application::PAYMENT_FAILED
                        );
                    }
                }

                return 'failed';
            }, 3);

            if ($transition === 'failed') {
                Log::warning(
                    'Paystack confirmed a terminal unsuccessful payment state.',
                    [
                        'payment_id' => $payment->id,
                        'application_id' => $payment->application_id,
                        'reference' => $payment->reference,
                        'gateway_status' => $gatewayStatus,
                    ]
                );
            }

            return $payment->fresh();
        }

        /*
         * Preserve the current status for pending, processing, ongoing,
         * queued and unknown gateway states.
         *
         * A locally failed attempt remains failed even if the gateway now
         * reports an inconclusive state.
         */
        $freshPayment = $payment->fresh();

        if (
            ! $freshPayment
            || $freshPayment->status !== Payment::STATUS_PENDING
        ) {
            return $freshPayment;
        }

        $metadata = $freshPayment->metadata ?? [];
        $metadata['last_verification'] = $data;

        $freshPayment->update([
            'metadata' => $metadata,
        ]);

        Log::info('Paystack payment remains unresolved.', [
            'payment_id' => $freshPayment->id,
            'application_id' => $freshPayment->application_id,
            'reference' => $freshPayment->reference,
            'gateway_status' => $gatewayStatus ?: 'unknown',
        ]);

        return $freshPayment->fresh();
    }

    /**
     * Return required documents missing from an application.
     */
    public function missingRequiredDocuments(
        Application $application
    ): array {
        return $this->admissions->missingRequiredDocuments($application);
    }

    /**
     * Validate a Paystack webhook signature.
     */
    public function validateWebhook(
        string $payload,
        string $signature
    ): bool {
        $secret = config('services.paystack.secret');

        if (
            ! is_string($secret)
            || $secret === ''
            || $signature === ''
        ) {
            return false;
        }

        $expected = hash_hmac('sha512', $payload, $secret);

        return hash_equals($expected, $signature);
    }
}
