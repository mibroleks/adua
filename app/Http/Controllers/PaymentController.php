<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Payment;
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
     * Show the applicant's payment details.
     *
     * Route name: applications.payment
     * URL: /applications/payment
     */
    public function show(Request $request)
    {
        $application = Application::with('payment')
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->first();

        return view('applications.payment', compact('application'));
    }

    /**
     * Initialize or resume payment for an application.
     *
     * Route name: payment.initialize
     *
     * A successful initialization must provide a valid checkout URL before
     * redirecting the applicant away from the application.
     */
    public function initialize(Application $application)
    {
        Gate::authorize('view', $application);

        if (! $this->portalConfig->paymentsEnabled()) {
            abort(403, 'Payments are currently disabled.');
        }

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

        if ($application->payment_status === Application::PAYMENT_SUCCESS) {
            return redirect()
                ->route('application.status', $application)
                ->with('status', 'This application has already been paid for.');
        }

        /*
         * Keep the existing requirement that draft applications must have
         * their required documents before payment can be initialized.
         */
        if ($application->application_status === Application::STATUS_DRAFT) {
            $missingDocs = $this->payments->missingRequiredDocuments($application);

            if (! empty($missingDocs)) {
                Log::warning(
                    "Application {$application->id} is missing required documents before payment.",
                    [
                        'application_id' => $application->id,
                        'missing_documents' => $missingDocs,
                    ]
                );

                return redirect()
                    ->route('application.status', $application)
                    ->withErrors([
                        'documents' => 'Upload all required documents before payment: '
                            . implode(', ', $missingDocs),
                    ]);
            }
        }

        try {
            $result = $this->payments->initialize($application);
        } catch (\Throwable $exception) {
            Log::error(
                "Unexpected payment initialization error for application {$application->id}.",
                [
                    'application_id' => $application->id,
                    'exception' => $exception->getMessage(),
                ]
            );

            return redirect()
                ->route('application.status', $application)
                ->withErrors([
                    'payment' => 'We could not start checkout at this time. '
                        . 'Your application has not been restarted. Please try again.',
                ]);
        }

        if (! ($result['success'] ?? false)) {
            Log::warning(
                "Payment initialization was unsuccessful for application {$application->id}.",
                [
                    'application_id' => $application->id,
                    'reference' => $result['reference'] ?? null,
                    'message' => $result['message'] ?? null,
                ]
            );

            return redirect()
                ->route('application.status', $application)
                ->withErrors([
                    'payment' => $result['message']
                        ?? 'Unable to initialize payment. Please try again.',
                ]);
        }

        if (($result['already_paid'] ?? false) === true) {
            return redirect()
                ->route('application.status', $application)
                ->with('status', 'This application has already been paid for.');
        }

        /*
         * Never redirect to a missing, malformed, or non-HTTPS checkout URL.
         *
         * A successful service result alone does not guarantee that a usable
         * checkout URL exists, particularly when recovering an old pending
         * payment.
         */
        $authorizationUrl = $result['authorization_url'] ?? null;

        $validCheckoutUrl = is_string($authorizationUrl)
            && filter_var($authorizationUrl, FILTER_VALIDATE_URL) !== false
            && strtolower((string) parse_url($authorizationUrl, PHP_URL_SCHEME)) === 'https'
            && ! empty(parse_url($authorizationUrl, PHP_URL_HOST));

        if (! $validCheckoutUrl) {
            Log::warning(
                "Payment initialization returned no usable checkout URL for application {$application->id}.",
                [
                    'application_id' => $application->id,
                    'reference' => $result['reference'] ?? null,
                    'already_paid' => $result['already_paid'] ?? false,
                ]
            );

            /*
             * Do not create another payment here. The service owns payment
             * creation, reference reuse, and gateway initialization.
             */
            return redirect()
                ->route('application.status', $application)
                ->withErrors([
                    'payment' => $result['message']
                        ?? 'Your payment may still be awaiting confirmation, '
                            . 'or checkout could not be resumed. '
                            . 'Please check the payment status before retrying.',
                ]);
        }

        return redirect()->away($authorizationUrl);
    }

    /**
     * Handle the Paystack callback after a payment attempt.
     *
     * Route name: payment.callback
     */
    public function callback(Request $request)
    {
        $reference = $request->query('reference');

        if (! is_string($reference) || trim($reference) === '') {
            return redirect()
                ->route('dashboard')
                ->withErrors([
                    'payment' => 'Payment reference was not provided.',
                ]);
        }

        try {
            $payment = $this->payments->verify($reference);
        } catch (\Throwable $exception) {
            Log::error('Payment callback verification raised an exception.', [
                'reference' => $reference,
                'exception' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('dashboard')
                ->withErrors([
                    'payment' => 'We could not confirm your payment at this time. '
                        . 'Please check your application status before trying again.',
                ]);
        }

        if (! $payment) {
            Log::warning('Payment callback could not resolve or verify the payment.', [
                'reference' => $reference,
            ]);

            return redirect()
                ->route('dashboard')
                ->withErrors([
                    'payment' => 'We could not confirm this payment. '
                        . 'Please check your application status before trying again.',
                ]);
        }

        /*
         * PaymentService::verify() is responsible for verifying the gateway
         * response and updating the payment. Do not mark a payment successful
         * merely because the browser returned from the gateway.
         */
        if (
            $payment->status === Payment::STATUS_SUCCESS
            && ! $payment->verified_at
        ) {
            $payment->update([
                'verified_at' => now(),
            ]);
        }

        $application = $payment->application;

        if (! $application) {
            Log::error('Verified payment has no associated application.', [
                'payment_id' => $payment->id,
                'reference' => $reference,
            ]);

            return redirect()
                ->route('dashboard')
                ->withErrors([
                    'payment' => 'The payment was received, but its application '
                        . 'could not be loaded. Please contact support.',
                ]);
        }

        if ($payment->status === Payment::STATUS_SUCCESS) {
            return redirect()
                ->route('application.status', $application)
                ->with(
                    'status',
                    'Payment successful! Your application is eligible for the next step.'
                );
        }

        if ($payment->status === Payment::STATUS_PENDING) {
            return redirect()
                ->route('application.status', $application)
                ->with(
                    'status',
                    'Your payment is awaiting confirmation. Please check the payment status '
                        . 'before attempting another payment.'
                );
        }

        if ($payment->status === Payment::STATUS_FAILED) {
            return redirect()
                ->route('application.status', $application)
                ->withErrors([
                    'payment' => 'The payment was not completed successfully. '
                        . 'You can retry from your existing application.',
                ]);
        }

        Log::warning('Payment callback returned an unrecognized local payment status.', [
            'payment_id' => $payment->id,
            'reference' => $reference,
            'status' => $payment->status,
        ]);

        return redirect()
            ->route('application.status', $application)
            ->with(
                'status',
                'Your payment status is being checked. Please check again shortly.'
            );
    }

    /**
     * Handle Paystack's server-to-server webhook.
     *
     * Route name: payment.webhook
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('x-paystack-signature');

        if (! $this->payments->validateWebhook($payload, $signature)) {
            Log::warning('Invalid Paystack webhook signature.');

            return response()->json([
                'status' => 'invalid signature',
            ], 401);
        }

        $reference = $request->input('data.reference');

        if (! is_string($reference) || trim($reference) === '') {
            Log::warning('Paystack webhook did not contain a valid payment reference.');

            return response()->json([
                'status' => 'missing reference',
            ], 400);
        }

        try {
            $payment = $this->payments->verify($reference);
        } catch (\Throwable $exception) {
            Log::error('Paystack webhook verification raised an exception.', [
                'reference' => $reference,
                'exception' => $exception->getMessage(),
            ]);

            return response()->json([
                'status' => 'verification error',
            ], 500);
        }

        if (! $payment) {
            Log::warning('Paystack webhook payment could not be verified.', [
                'reference' => $reference,
            ]);

            return response()->json([
                'status' => 'verification pending',
            ], 200);
        }

        if ($payment->status === Payment::STATUS_SUCCESS) {
            if (! $payment->verified_at) {
                $payment->update([
                    'verified_at' => now(),
                ]);
            }

            $metadata = $payment->metadata ?? [];

            Log::info(
                "Paystack webhook confirmed payment for application {$payment->application_id}.",
                [
                    'payment_id' => $payment->id,
                    'reference' => $reference,
                    'expected_amount' => $payment->amount,
                    'requested_amount' => $metadata['requested_amount'] ?? null,
                    'customer_paid' => $metadata['amount'] ?? null,
                    'gateway_fee' => $metadata['fees'] ?? null,
                ]
            );

            return response()->json([
                'status' => 'ok',
            ]);
        }

        /*
         * A pending or unresolved transaction is not automatically a failed
         * payment. A later webhook or verification attempt can resolve it.
         */
        if ($payment->status === Payment::STATUS_PENDING) {
            Log::info('Paystack webhook payment remains pending verification.', [
                'payment_id' => $payment->id,
                'reference' => $reference,
            ]);

            return response()->json([
                'status' => 'pending',
            ], 200);
        }

        if ($payment->status === Payment::STATUS_FAILED) {
            Log::warning('Paystack webhook payment is recorded as failed.', [
                'payment_id' => $payment->id,
                'reference' => $reference,
            ]);

            /*
             * A correctly signed webhook was received and processed. Return
             * success to avoid unnecessary delivery retries for a known
             * terminal payment state.
             */
            return response()->json([
                'status' => 'processed',
            ], 200);
        }

        Log::warning('Paystack webhook returned an unrecognized payment status.', [
            'payment_id' => $payment->id,
            'reference' => $reference,
            'status' => $payment->status,
        ]);

        return response()->json([
            'status' => 'unresolved',
        ], 200);
    }
}
