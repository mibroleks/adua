{{--  
Component: Application Payment
File Path: resources/views/applications/payment.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays application fee and payment state.

Status: Production
Version: 2.0
--}}

@extends('layouts.portal')

@section('title', 'Application Payment')

@section('content')

<div class="admission-page admission-page--payment">

    <main class="admission-shell admission-shell--wide">

        <header class="admission-page-header">

            <div class="admission-eyebrow">
                <span class="admission-eyebrow__dot" aria-hidden="true"></span>
                Application Payment
            </div>

            <h1 class="admission-page-title">
                Application fee
            </h1>

            <p class="admission-page-description">
                Review your application fee and payment status. If a payment
                attempt failed, you can safely retry it.
            </p>

        </header>


        @if(!$application)

            <section class="admission-empty-state payment-empty-state">

                <div class="payment-empty-state__mark">
                    ₦
                </div>

                <div>
                    <span class="payment-empty-state__eyebrow">
                        APPLICATION REQUIRED
                    </span>

                    <h2>No application found</h2>

                    <p>
                        You have not started an application yet.
                    </p>

                    <x-portal-button
                        variant="primary"
                        href="{{ route('application.create') }}"
                    >
                        Start Application
                    </x-portal-button>
                </div>

            </section>

        @else

            <section class="payment-card">

                <div class="payment-card__header">

                    <div>

                        <span class="payment-card__eyebrow">
                            APPLICATION FEE
                        </span>

                        <h2>
                            Payment summary
                        </h2>

                    </div>

                    <span class="payment-card__application">
                        {{ $application->application_number }}
                    </span>

                </div>


                <div class="payment-card__amount">

                    <span>
                        Amount payable
                    </span>

                    @if($application->payment)

                        <strong>
                            ₦{{ number_format($application->payment->amountInNaira(), 2) }}
                        </strong>

                    @else

                        <strong>
                            {{ $application->formatted_application_fee ?? '—' }}
                        </strong>

                    @endif

                </div>


                @if($application->payment)

                    <div class="payment-card__status">

                        <span class="payment-card__status-label">
                            PAYMENT STATUS
                        </span>

                        <span class="admission-status admission-status--{{ strtolower($application->payment->status) }}">
                            {{ $application->payment->status }}
                        </span>

                        <p>
                            {{ $application->payment->paid_at?->format('d M Y, H:i') ?? 'Payment date unavailable' }}
                        </p>

                    </div>


                    @if($application->payment->status === 'FAILED')

                        <div class="payment-card__action">

                            <div>
                                <strong>
                                    Payment was unsuccessful
                                </strong>

                                <span>
                                    You can retry the payment without creating
                                    another application.
                                </span>
                            </div>

                            <form
                                method="POST"
                                action="{{ route('payment.initialize', $application) }}"
                            >
                                @csrf

                                <x-portal-button
                                    variant="primary"
                                    type="submit"
                                >
                                    Retry Payment
                                </x-portal-button>
                            </form>

                        </div>

                    @endif

                @else

                    <div class="payment-card__unpaid">

                        <div>

                            <span class="payment-card__unpaid-label">
                                PAYMENT REQUIRED
                            </span>

                            <h3>
                                Complete your application payment
                            </h3>

                            <p>
                                Payment is required to continue processing your
                                application.
                            </p>

                        </div>

                        <form
                            method="POST"
                            action="{{ route('payment.initialize', $application) }}"
                        >
                            @csrf

                            <x-portal-button
                                variant="primary"
                                type="submit"
                            >
                                Pay Application Fee
                            </x-portal-button>
                        </form>

                    </div>

                @endif

            </section>

        @endif

    </main>

</div>

@endsection