{{--
|--------------------------------------------------------------------------
| Component: My Admission Offers
|--------------------------------------------------------------------------
| File:
| resources/views/admission_offers/index.blade.php
|
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Displays all currently issued official admission offers belonging
| to the authenticated applicant.
|
| Important:
| - This is an offers listing, not an admission letter.
| - Each offer links to its individual immutable official document.
| - Only currently issued official offers are displayed.
| - Withdrawn and superseded offers are excluded by the controller.
|--------------------------------------------------------------------------
| Version: 1.0
|--------------------------------------------------------------------------
--}}

@extends('layouts.portal')

@section('title', 'My Admission Offers')

@section('content')

<div class="admission-page admission-page--offers">

    <main class="admission-shell admission-shell--wide">

        {{-- Page heading --}}
        <header class="admission-page-header">

            <div class="admission-eyebrow">
                <span class="admission-eyebrow__dot" aria-hidden="true"></span>
                Admission Records
            </div>

            <h1 class="admission-page-title">
                My Admission Offers
            </h1>

            <p class="admission-page-description">
                View and access your official admission offers issued
                by the institution for your successful applications.
            </p>

        </header>

        {{-- Offers summary --}}
        <section
            class="admission-offers-summary"
            aria-label="Admission offers summary"
        >

            <div class="admission-offers-summary__content">

                <span class="admission-offers-summary__label">
                    Current Official Offers
                </span>

                <strong class="admission-offers-summary__value">
                    {{ $offers->count() }}
                </strong>

                <p>
                    Official admission documents currently available
                    to your applicant account.
                </p>

            </div>

            <div
                class="admission-offers-summary__icon"
                aria-hidden="true"
            >
                &#10003;
            </div>

        </section>

        @if($offers->isEmpty())

            {{-- Empty state --}}
            <section class="admission-empty-state">

                <div
                    class="admission-empty-state__icon"
                    aria-hidden="true"
                >
                    &#128196;
                </div>

                <h2>No official admission offers yet</h2>

                <p>
                    You do not currently have an issued official admission
                    offer. When an application receives an ADMITTED outcome
                    and its official offer is issued, it will appear here.
                </p>

                <x-portal-button
                    variant="primary"
                    href="{{ route('applications.index') }}"
                >
                    View My Applications
                </x-portal-button>

            </section>

        @else

            {{-- Offers list --}}
            <section class="admission-offers-list">

                <div class="admission-offers-list__heading">

                    <div>
                        <h2>Your Official Offers</h2>

                        <p>
                            Each offer belongs to a specific application
                            and programme.
                        </p>
                    </div>

                    <span class="admission-offers-list__count">
                        {{ $offers->count() }}
                        {{ \Illuminate\Support\Str::plural('offer', $offers->count()) }}
                    </span>

                </div>

                <div class="admission-table-wrapper">

                    <table class="admission-table">

                        <thead>
                            <tr>
                                <th>Offer Reference</th>
                                <th>Application No.</th>
                                <th>Programme</th>
                                <th>Issue Date</th>
                                <th>Version</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($offers as $offer)

                                <tr>

                                    {{-- Official offer reference --}}
                                    <td>
                                        <strong>
                                            {{ $offer->letter_number }}
                                        </strong>
                                    </td>

                                    {{-- Related application --}}
                                    <td>
                                        {{ $offer->application?->application_number ?? 'Unavailable' }}
                                    </td>

                                    {{-- Programme --}}
                                    <td>
                                        {{ $offer->application?->programme?->name ?? 'Programme unavailable' }}
                                    </td>

                                    {{-- Issue date --}}
                                    <td>
                                        {{ $offer->issued_at?->format('d M Y') ?? 'Not available' }}
                                    </td>

                                    {{-- Document version --}}
                                    <td>
                                        v{{ $offer->version ?? 1 }}
                                    </td>

                                    {{-- Offer status --}}
                                    <td>
                                        <span class="admission-offer-status">
                                            <span
                                                class="admission-offer-status__dot"
                                                aria-hidden="true"
                                            ></span>

                                            Issued
                                        </span>
                                    </td>

                                    {{-- Individual official offer --}}
                                    <td>
                                        <x-portal-button
                                            variant="primary"
                                            href="{{ route('admission.offer', $offer->application) }}"
                                        >
                                            View Official Offer
                                        </x-portal-button>
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </section>

        @endif

        {{-- Return navigation --}}
        <div class="admission-offers-footer">

            <a
                href="{{ route('applications.index') }}"
                class="admission-offers-footer__link"
            >
                &larr; Return to My Applications
            </a>

        </div>

    </main>

</div>

@endsection
