{{--
    Component: Application Overview Widget
    File Path: resources/views/filament/widgets/application-overview.blade.php
    Company: Ygrace Tech
    Author: Ibrahim Olalekan

    Purpose:
    Premium custom presentation for the Applications KPI widget.

    Architecture:
    - Receives structured statistics from ApplicationOverview.
    - Does not perform database queries.
    - Does not modify application records.
    - Uses the dedicated Officer Filament theme stylesheet.
    - Supports enhanced referral partner performance details.
    - Preserves the existing KPI presentation structure.

    Version: 2.1
--}}

<div class="application-overview">

    {{-- Overview heading --}}
    <div class="application-overview__header">

        <div class="application-overview__heading-content">

            <div class="application-overview__eyebrow">
                <span class="application-overview__eyebrow-line"></span>
                APPLICATION INTELLIGENCE
            </div>

            <div class="application-overview__title-row">
                <div>
                    <h2 class="application-overview__title">
                        Application Overview
                    </h2>

                    <p class="application-overview__subtitle">
                        A consolidated view of application activity,
                        recruitment sources and admission progress.
                    </p>
                </div>
            </div>

        </div>

        <div class="application-overview__header-mark" aria-hidden="true">
            <x-filament::icon
                icon="heroicon-o-chart-bar-square"
                class="application-overview__header-icon"
            />
        </div>

    </div>

    {{-- KPI collection --}}
    @if (count($stats))

        <div class="application-overview__grid">

            @foreach ($stats as $stat)

                @php
                    $statKey = $stat['key'] ?? '';

                    $isTotal = $statKey === 'total';

                    /*
                     * Only individual referral partner cards
                     * receive the referral partner presentation.
                     *
                     * The aggregate Partner Referrals card must
                     * remain a general KPI card.
                     */
                    $isPartner = str_starts_with($statKey, 'partner_')
                        && $statKey !== 'partner_referrals';
                @endphp

                <article
                    @class([
                        'application-kpi',
                        'application-kpi--' . ($stat['color'] ?? 'gray'),
                        'application-kpi--featured' => $isTotal,
                        'application-kpi--partner' => $isPartner,
                        'application-kpi--unique' => $statKey === 'unique_applicants',
                    ])
                    aria-label="{{ $stat['label'] }}: {{ $stat['value'] }}"
                >

                    {{-- Card upper section --}}
                    <div class="application-kpi__top">

                        <div class="application-kpi__identity">

                            <span class="application-kpi__label">
                                {{ $stat['label'] }}
                            </span>

                            @if ($isTotal)

                                <span class="application-kpi__tag">
                                    OVERALL
                                </span>

                            @elseif ($isPartner)

                                <span class="application-kpi__tag application-kpi__tag--partner">
                                    REFERRAL PARTNER
                                </span>

                            @endif

                        </div>

                        <div class="application-kpi__icon-wrap">
                            <x-filament::icon
                                :icon="$stat['icon'] ?? 'heroicon-o-chart-bar'"
                                class="application-kpi__icon"
                            />
                        </div>

                    </div>

                    {{-- Main KPI value --}}
                    <div class="application-kpi__value">
                        {{ $stat['value'] }}
                    </div>

                    {{-- Card footer --}}
                    <div class="application-kpi__footer">

                        <span class="application-kpi__description">
                            {{ $stat['description'] ?? '' }}
                        </span>

                        @unless ($isPartner)
                            <span class="application-kpi__indicator" aria-hidden="true">
                                <x-filament::icon
                                    icon="heroicon-m-arrow-up-right"
                                    class="application-kpi__indicator-icon"
                                />
                            </span>
                        @endunless

                    </div>

                    {{-- Decorative accent --}}
                    <span
                        class="application-kpi__accent"
                        aria-hidden="true"
                    ></span>

                </article>

            @endforeach

        </div>

    @else

        <div class="application-overview__empty">

            <div class="application-overview__empty-icon">
                <x-filament::icon
                    icon="heroicon-o-chart-bar"
                    class="application-overview__empty-icon-svg"
                />
            </div>

            <h3 class="application-overview__empty-title">
                No application statistics available
            </h3>

            <p class="application-overview__empty-description">
                Application performance indicators will appear here
                when data becomes available.
            </p>

        </div>

    @endif

</div>
