<?php

/**
 * Component: Payment Controller
 * File Path: app/Http/Controllers/PaymentController.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Handles payment lifecycle for applications:
 * - initialize: start Paystack payment
 * - callback: handle user redirect after payment
 * - webhook: handle server-to-server verification
 *
 * Status: Production Ready
 * Version: 2.0
 */

namespace App\Http\Controllers;

use App\Models\Application;
use App\Services\PaymentService;
use App\Services\PortalConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected PaymentService $payments;

    protected PortalConfigService $portalConfig;

    public function __construct(
        PaymentService $payments,
        PortalConfigService $portalConfig
    ) {
        $this->payments = $payments;
        $this->portalConfig = $portalConfig;
    }

    /**
     * Initialize payment for an application.
     *
     * Payment is allowed while the application is DRAFT.
     *
     * A successful payment will cause the DRAFT application
     * to be submitted after all submission requirements pass.
     */
    public function initialize(Application $application)
    {
        /*
         * Applicant may only pay/view their own application.
         * Officers are also authorized through ApplicationPolicy.
         */
        Gate::authorize('view', $application);

        /*
         * Ensure payments are enabled globally.
         */
        if (! $this->portalConfig->paymentsEnabled()) {
            abort(403, 'Payments are currently disabled.');
        }

        /*
         * Payment may be initialized for:
         *
         * DRAFT:
         * Applicant has completed the application and is ready to pay.
         *
         * SUBMITTED:
         * Allows payment retry/recovery where submission already exists.
         */
        abort_unless(
            in_array(
                $application->application_status,
                [
                    Application::STATUS_DRAFT,
                    Application::STATUS_SUBMITTED,
                ],
                true
            ),
            422,
            'This application is not ready for payment.'
        );

        /*
         * Never initialize another payment for an application
         * that has already been successfully paid.
         */
        if ($application->payment_status === 'SUCCESS') {
            return redirect()
                ->route('application.status', $application)
                ->with(
                    'status',
                    'This application has already been paid for.'
                );
        }

        /*
         * A DRAFT application must have all required documents
         * before the applicant can proceed to payment.
         *
         * This is important because payment success will submit
         * the application.
         */
        if ($application->application_status === Application::STATUS_DRAFT) {

            $missingDocuments =
                $this->payments->missingRequiredDocuments($application);

            if (! empty($missingDocuments)) {

                return redirect()
                    ->route('application.status', $application)
                    ->withErrors([
                        'documents' =>
                            'You must upload all required documents before payment: '
                            . implode(', ', $missingDocuments),
                    ]);
            }
        }

        /*
         * Initialize the Paystack transaction.
         */
        $result = $this->payments->initialize($application);

        /*
         * If Paystack initialization failed, record it in logs
         * and return the applicant to the status page.
         */
        if (empty($result['authorization_url'])) {

            Log::error(
                'Payment initialization failed.',
                [
                    'application_id' =>
                        $application->id,

                    'application_number' =>
                        $application->application_number,

                    'message' =>
                        $result['message'] ?? null,

                    'reference' =>
                        $result['reference'] ?? null,
                ]
            );

            return redirect()
                ->route('application.status', $application)
                ->withErrors([
                    'payment' =>
                        $result['message']
                        ?? 'Unable to initialize payment.',
                ]);
        }

        /*
         * Redirect applicant to Paystack checkout.
         */
        return redirect()->away(
            $result['authorization_url']
        );
    }

    /**
     * Handle Paystack callback after payment attempt.
     */
    public function callback(Request $request)
    {
        $reference = $request->query('reference');

        if (! $reference) {
            return redirect()
                ->route('dashboard')
                ->withErrors([
                    'payment' =>
                        'Payment reference was not provided.',
                ]);
        }

        /*
         * PaymentService performs the authoritative Paystack
         * verification and local payment/application updates.
         */
        $payment = $this->payments->verify($reference);

        if (! $payment) {
            return redirect()
                ->route('dashboard')
                ->withErrors([
                    'payment' =>
                        'Payment verification failed.',
                ]);
        }

        /*
         * Return applicant to the application status page.
         */
        return redirect()
            ->route(
                'application.status',
                $payment->application
            )
            ->with(
                'status',
                $payment->status === 'SUCCESS'
                    ? 'Payment successful! Your application has been submitted.'
                    : 'Payment failed. Please try again.'
            );
    }

    /**
     * Handle Paystack webhook.
     */
    public function webhook(Request $request)
    {
        /*
         * Validate Paystack signature before processing
         * the webhook.
         */
        $signature = $request->header(
            'x-paystack-signature'
        );

        if (! $signature) {

            Log::warning(
                'Paystack webhook rejected: missing signature.'
            );

            return response()->json([
                'status' => 'failed',
                'message' => 'Missing signature.',
            ], 401);
        }

        if (
            ! $this->payments->validateWebhook(
                $request->getContent(),
                $signature
            )
        ) {

            Log::warning(
                'Paystack webhook rejected: invalid signature.'
            );

            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid signature.',
            ], 401);
        }

        $reference = $request->input(
            'data.reference'
        );

        if (! $reference) {

            Log::warning(
                'Paystack webhook received without reference.'
            );

            return response()->json([
                'status' => 'failed',
                'message' => 'Missing payment reference.',
            ], 422);
        }

        Log::info(
            'Paystack webhook received.',
            [
                'reference' => $reference,
            ]
        );

        $payment = $this->payments->verify(
            $reference
        );

        if (
            $payment
            && $payment->status === 'SUCCESS'
        ) {

            Log::info(
                'Paystack webhook payment verified.',
                [
                    'reference' =>
                        $reference,

                    'application_id' =>
                        $payment->application_id,
                ]
            );

            return response()->json([
                'status' => 'ok',
            ]);
        }

        Log::warning(
            'Paystack webhook payment verification failed.',
            [
                'reference' => $reference,
            ]
        );

        return response()->json([
            'status' => 'failed',
        ], 400);
    }
}