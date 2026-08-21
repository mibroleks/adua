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

    public function __construct(PaymentService $payments, PortalConfigService $portalConfig)
    {
        $this->payments = $payments;
        $this->portalConfig = $portalConfig;
    }

    /**
     * Show the applicant’s payment details.
     *
     * Route name: applications.payment
     * URL: /applications/payment
     */
    public function show(Request $request)
    {
        $application = Application::with('payment')
            ->where('user_id', $request->user()->id)
            ->first();

        return view('applications.payment', compact('application'));
    }

    /**
     * Initialize payment for an application.
     *
     * Route name: payment.initialize
     */
    public function initialize(Application $application)
    {
        Gate::authorize('view', $application);

        if (! $this->portalConfig->paymentsEnabled()) {
            abort(403, 'Payments are currently disabled.');
        }

        abort_unless(
            in_array($application->application_status, [
                Application::STATUS_DRAFT,
                Application::STATUS_SUBMITTED,
            ], true),
            422,
            'This application is not ready for payment.'
        );

        if ($application->payment_status === Application::PAYMENT_SUCCESS) {
            return redirect()
                ->route('application.status', $application)
                ->with('status', 'This application has already been paid for.');
        }

        if ($application->application_status === Application::STATUS_DRAFT) {
            $missingDocs = $this->payments->missingRequiredDocuments($application);
            if (! empty($missingDocs)) {
                Log::warning("Application {$application->id} missing required documents before payment: ".implode(', ', $missingDocs));
                return redirect()
                    ->route('application.status', $application)
                    ->withErrors([
                        'documents' => 'You must upload all required documents before payment: '
                                       . implode(', ', $missingDocs),
                    ]);
            }
        }

        $result = $this->payments->initialize($application);

        if (! ($result['success'] ?? false)) {
            Log::error("Payment initialization failed for application {$application->id}", [
                'message' => $result['message'] ?? null,
                'reference' => $result['reference'] ?? null,
            ]);
            return redirect()
                ->route('application.status', $application)
                ->withErrors([
                    'payment' => $result['message'] ?? 'Unable to initialize payment',
                ]);
        }

        if (($result['already_paid'] ?? false) === true) {
            return redirect()
                ->route('application.status', $application)
                ->with('status', 'This application has already been paid for.');
        }

        return redirect()->away($result['authorization_url']);
    }

    /**
     * Handle Paystack callback after payment attempt.
     *
     * Route name: payment.callback
     */
    public function callback(Request $request)
    {
        $reference = $request->query('reference');

        if (! $reference) {
            return redirect()->route('dashboard')
                ->withErrors(['payment' => 'Payment reference was not provided.']);
        }

        $payment = $this->payments->verify($reference);

        if (! $payment) {
            return redirect()->route('dashboard')
                ->withErrors(['payment' => 'Payment verification failed']);
        }

        // ✅ Use requested_amount for validation; status SUCCESS means payment succeeded
        if ($payment->status === Payment::STATUS_SUCCESS && ! $payment->verified_at) {
            $payment->update(['verified_at' => now()]);
        }

        return redirect()->route('application.status', $payment->application)
            ->with('status', $payment->status === Payment::STATUS_SUCCESS
                ? 'Payment successful! Your application is now eligible for submission.'
                : 'Payment failed. Please try again.');
    }

    /**
     * Handle Paystack webhook (server-to-server).
     *
     * Route name: payment.webhook
     */
    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $signature = $request->header('x-paystack-signature');

        if (! $this->payments->validateWebhook($payload, $signature)) {
            Log::warning('Invalid Paystack webhook signature.');
            return response()->json(['status' => 'invalid signature'], 401);
        }

        $reference = $request->input('data.reference');
        $payment   = $this->payments->verify($reference);

        // ✅ Verification now tolerant of gateway fees
        if ($payment && $payment->status === Payment::STATUS_SUCCESS) {
            if (! $payment->verified_at) {
                $payment->update(['verified_at' => now()]);
            }
            Log::info("Webhook verified payment for application {$payment->application_id}", [
                'expected_amount' => $payment->amount,
                'requested_amount' => $payment->metadata['requested_amount'] ?? null,
                'customer_paid'   => $payment->metadata['amount'] ?? null,
                'gateway_fee'     => $payment->metadata['fees'] ?? null,
            ]);
            return response()->json(['status' => 'ok']);
        }

        Log::warning("Webhook payment verification failed for reference {$reference}");
        return response()->json(['status' => 'failed'], 400);
    }
}
