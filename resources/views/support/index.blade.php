{{--
|--------------------------------------------------------------------------
| Component: Applicant Support
|--------------------------------------------------------------------------
| File:
| resources/views/support/index.blade.php
|
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Displays FAQs and contact information for applicants needing help.
|
| Status: 🚦 Hardened
| Version: 2.0
|--------------------------------------------------------------------------
--}}

@extends('layouts.portal')

@section('title', 'Help & Support')

@section('content')

<div class="admission-page admission-page--support">

    <main class="admission-shell admission-shell--wide">

        {{-- ==========================================================
             PAGE HEADER
        =========================================================== --}}

        <header class="admission-page-header">

            <div class="admission-eyebrow">

                <span
                    class="admission-eyebrow__dot"
                    aria-hidden="true"
                ></span>

                Help & Support

            </div>

            <h1 class="admission-page-title">
                Support
            </h1>

            <p class="admission-page-description">
                Find answers to common questions or contact the admissions
                office for assistance with your application.
            </p>

        </header>


        <div class="support-content">

            {{-- ======================================================
                 FAQ
            ======================================================= --}}

            <section
                class="admission-panel support-panel"
                aria-labelledby="support-faq-title"
            >

                <div class="admission-panel__header">

                    <div class="admission-panel__icon" aria-hidden="true">
                        ?
                    </div>

                    <div>

                        <span class="admission-panel__kicker">
                            Common questions
                        </span>

                        <h2
                            id="support-faq-title"
                            class="admission-panel__title"
                        >
                            Frequently Asked Questions
                        </h2>

                        <p class="admission-panel__description">
                            Quick answers to some of the most common
                            applicant questions.
                        </p>

                    </div>

                </div>


                <div class="support-panel__body">

                    <ul class="support-faq-list">

                        <li class="support-faq">

                            <h3 class="support-faq__question">
                                How do I start an application?
                            </h3>

                            <p class="support-faq__answer">
                                Go to the
                                <a
                                    href="{{ route('application.create') }}"
                                    class="admission-link"
                                >
                                    Start Application
                                </a>
                                page and follow the application steps.
                            </p>

                        </li>


                        <li class="support-faq">

                            <h3 class="support-faq__question">
                                How do I pay my application fee?
                            </h3>

                            <p class="support-faq__answer">
                                Navigate to the
                                <a
                                    href="{{ route('applications.payment') }}"
                                    class="admission-link"
                                >
                                    Payment
                                </a>
                                page and complete the transaction securely.
                            </p>

                        </li>


                        <li class="support-faq">

                            <h3 class="support-faq__question">
                                What documents are required?
                            </h3>

                            <p class="support-faq__answer">
                                Check the
                                <a
                                    href="{{ route('applications.documents') }}"
                                    class="admission-link"
                                >
                                    Documents
                                </a>
                                page for the list of required uploads.
                            </p>

                        </li>


                        <li class="support-faq">

                            <h3 class="support-faq__question">
                                How will I know my admission decision?
                            </h3>

                            <p class="support-faq__answer">
                                Visit the
                                <a
                                    href="{{ route('applications.decision') }}"
                                    class="admission-link"
                                >
                                    Admission Decision
                                </a>
                                page once your application has been reviewed.
                            </p>

                        </li>

                    </ul>

                </div>

            </section>


            {{-- ======================================================
                 CONTACT ADMISSIONS
            ======================================================= --}}

            <section
                class="admission-panel support-panel"
                aria-labelledby="contact-admissions-title"
            >

                <div class="admission-panel__header">

                    <div class="admission-panel__icon" aria-hidden="true">
                        A
                    </div>

                    <div>

                        <span class="admission-panel__kicker">
                            Need assistance?
                        </span>

                        <h2
                            id="contact-admissions-title"
                            class="admission-panel__title"
                        >
                            Contact Admissions
                        </h2>

                        <p class="admission-panel__description">
                            Reach the admissions office if you need
                            assistance beyond the information provided here.
                        </p>

                    </div>

                </div>


                <div class="support-panel__body">

                    <div class="support-contact-list">

                        <div class="support-contact">

                            <span class="support-contact__label">
                                Email
                            </span>

                            <strong class="support-contact__value">
                                admissions@example.edu
                            </strong>

                        </div>


                        <div class="support-contact">

                            <span class="support-contact__label">
                                Phone
                            </span>

                            <strong class="support-contact__value">

                                <a
                                    href="tel:+2348001234567"
                                    class="admission-link"
                                >
                                    +234 800 123 4567
                                </a>

                            </strong>

                        </div>


                        <div class="support-contact">

                            <span class="support-contact__label">
                                Office Hours
                            </span>

                            <strong class="support-contact__value">

                                <span class="support-contact__hours">
                                    Mon–Fri, 9:00 AM – 4:00 PM
                                </span>

                            </strong>

                        </div>

                    </div>

                </div>

            </section>

        </div>

    </main>

</div>

@endsection