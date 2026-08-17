{{--
|--------------------------------------------------------------------------
| Application Countdown
|--------------------------------------------------------------------------
| File:
| resources/views/components/application-countdown.blade.php
|
| Purpose:
| Premium, live admissions application countdown.
|
| Theme:
| Fully driven by --theme-* semantic tokens.
|--------------------------------------------------------------------------
--}}

@php
    use Carbon\Carbon;

    $openDate = Carbon::parse(setting('admissions.application_start'));
    $closeDate = Carbon::parse(setting('admissions.application_deadline'));
    $now = Carbon::now();

    $isOpen = $now->between($openDate, $closeDate);
@endphp

@if($isOpen)

    <section
        class="application-countdown"
        data-countdown
        data-end="{{ $closeDate->toIso8601String() }}"
        aria-label="Application deadline countdown"
    >

        <div class="application-countdown__inner">

            {{-- Header --}}
            <div class="application-countdown__header">

                <div>

                    <div class="application-countdown__eyebrow">
                        Admissions
                    </div>

                    <h2 class="application-countdown__title">
                        Applications are now open
                    </h2>

                    <p class="application-countdown__description">
                        Secure your place by completing your application
                        before the admissions window closes.
                    </p>

                </div>

                <div class="application-countdown__status">
                    <span
                        class="application-countdown__status-dot"
                        aria-hidden="true"
                    ></span>

                    Open
                </div>

            </div>


            {{-- Countdown --}}
            <div class="application-countdown__grid">

                {{-- Days --}}
                <div class="application-countdown__unit">

                    <span
                        class="application-countdown__number"
                        data-days
                        aria-label="Days remaining"
                    >
                        00
                    </span>

                    <span class="application-countdown__label">
                        Days
                    </span>

                    <span
                        class="application-countdown__separator"
                        aria-hidden="true"
                    ></span>

                </div>


                {{-- Hours --}}
                <div class="application-countdown__unit">

                    <span
                        class="application-countdown__number"
                        data-hours
                        aria-label="Hours remaining"
                    >
                        00
                    </span>

                    <span class="application-countdown__label">
                        Hours
                    </span>

                    <span
                        class="application-countdown__separator"
                        aria-hidden="true"
                    ></span>

                </div>


                {{-- Minutes --}}
                <div class="application-countdown__unit">

                    <span
                        class="application-countdown__number"
                        data-minutes
                        aria-label="Minutes remaining"
                    >
                        00
                    </span>

                    <span class="application-countdown__label">
                        Minutes
                    </span>

                    <span
                        class="application-countdown__separator"
                        aria-hidden="true"
                    ></span>

                </div>


                {{-- Seconds --}}
                <div class="application-countdown__unit">

                    <span
                        class="application-countdown__number"
                        data-seconds
                        aria-label="Seconds remaining"
                    >
                        00
                    </span>

                    <span class="application-countdown__label">
                        Seconds
                    </span>

                </div>

            </div>


            {{-- Deadline --}}
            <div class="application-countdown__deadline">

                <span class="application-countdown__deadline-label">
                    Application deadline
                </span>

                <span class="application-countdown__deadline-date">
                    {{ $closeDate->toDayDateTimeString() }}
                </span>

            </div>

        </div>

    </section>

@else

    {{-- Closed --}}
    <section
        class="application-countdown application-countdown--closed"
        aria-label="Applications closed"
    >

        <div class="application-countdown__inner">

            <div class="application-countdown__header">

                <div class="application-countdown__closed-message">

                    <div class="application-countdown__eyebrow">
                        Admissions
                    </div>

                    <h2 class="application-countdown__closed-title">
                        Applications are currently closed
                    </h2>

                    <p class="application-countdown__closed-description">
                        The current application window has ended.
                        The next admissions window opens on
                        {{ $openDate->toDayDateTimeString() }}.
                    </p>

                </div>

                <div class="application-countdown__status application-countdown__status--closed">
                    Closed
                </div>

            </div>

        </div>

    </section>

@endif