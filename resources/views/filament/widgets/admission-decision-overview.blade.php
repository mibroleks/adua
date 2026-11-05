{{--
Component: Admission Decision Overview
File Path: resources/views/filament/widgets/admission-decision-overview.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Presents admission decision intelligence and referral-source
performance in the Admission Decisions register.

Data supplied by:
AdmissionDecisionOverview widget.

Responsibilities:
- Display admission decision KPI cards.
- Display actual admitted-applicant conversion.
- Present referral partner performance.
- Distinguish application decisions from admission outcomes.
- Identify inactive referral partners.
- Display Direct / Organic attribution separately.

Status: Premium presentation
Version: 1.0
--}}

<div class="admission-decision-overview">

    {{-- Overview heading --}}
    <div class="admission-decision-overview__header">
        <div class="admission-decision-overview__heading">
            <div class="admission-decision-overview__eyebrow">
                <x-filament::icon
                    icon="heroicon-o-chart-bar-square"
                    class="admission-decision-overview__eyebrow-icon"
                />

                Admission Intelligence
            </div>

            <h2 class="admission-decision-overview__title">
                Admission Decision Overview
            </h2>

            <p class="admission-decision-overview__subtitle">
                Institutional admission outcomes and referral performance
                across the current register selection.
            </p>
        </div>

        <div class="admission-decision-overview__header-indicator">
            <span class="admission-decision-overview__indicator-dot"></span>
            <span>Live register data</span>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="admission-decision-overview__grid">

        @foreach ($stats as $stat)
            <div @class([
                'admission-decision-overview__card',
                'admission-decision-overview__card--primary' => $stat['color'] === 'primary',
                'admission-decision-overview__card--info' => $stat['color'] === 'info',
                'admission-decision-overview__card--success' => $stat['color'] === 'success',
                'admission-decision-overview__card--warning' => $stat['color'] === 'warning',
                'admission-decision-overview__card--danger' => $stat['color'] === 'danger',
                'admission-decision-overview__card--featured' => $stat['type'] === 'admitted',
            ])>

                <div class="admission-decision-overview__card-top">
                    <span class="admission-decision-overview__card-label">
                        {{ $stat['label'] }}
                    </span>

                    <span class="admission-decision-overview__card-icon">
                        <x-filament::icon
                            :icon="$stat['icon']"
                            class="admission-decision-overview__icon"
                        />
                    </span>
                </div>

                <div class="admission-decision-overview__card-value">
                    {{ $stat['value'] }}
                </div>

                <p class="admission-decision-overview__card-description">
                    {{ $stat['description'] }}
                </p>

                @if ($stat['type'] === 'admitted')
                    <span class="admission-decision-overview__card-tag">
                        Actual admission
                    </span>
                @elseif ($stat['type'] === 'conversion')
                    <span class="admission-decision-overview__card-tag">
                        Applicant-based rate
                    </span>
                @endif

            </div>
        @endforeach

    </div>

    {{-- Referral performance --}}
    <section class="admission-decision-overview__referrals">

        <div class="admission-decision-overview__section-header">
            <div>
                <div class="admission-decision-overview__section-eyebrow">
                    Recruitment Intelligence
                </div>

                <h3 class="admission-decision-overview__section-title">
                    Referral Source Performance
                </h3>

                <p class="admission-decision-overview__section-description">
                    Actual institutional admission outcomes attributed
                    to each original application referral source.
                </p>
            </div>

            <div class="admission-decision-overview__source-count">
                <span class="admission-decision-overview__source-count-value">
                    {{ count($sources) }}
                </span>

                <span class="admission-decision-overview__source-count-label">
                    Sources
                </span>
            </div>
        </div>

        {{-- Desktop referral table --}}
        <div class="admission-decision-overview__table-wrapper">

            <table class="admission-decision-overview__table">

                <thead>
                    <tr>
                        <th>Referral Source</th>
                        <th class="is-numeric">Decisions</th>
                        <th class="is-numeric">Applicants</th>
                        <th class="is-numeric">Approved Apps</th>
                        <th class="is-numeric">Rejected Apps</th>
                        <th class="is-numeric">Admitted</th>
                        <th class="is-numeric">Waitlisted</th>
                        <th class="is-numeric">Conversion</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($sources as $source)

                        <tr @class([
                            'admission-decision-overview__source-row',
                            'admission-decision-overview__source-row--direct' => $source['source_type'] === 'direct',
                        ])>

                            {{-- Source identity --}}
                            <td>
                                <div class="admission-decision-overview__source">

                                    <span @class([
                                        'admission-decision-overview__source-icon',
                                        'admission-decision-overview__source-icon--direct' => $source['source_type'] === 'direct',
                                        'admission-decision-overview__source-icon--partner' => $source['source_type'] === 'partner',
                                    ])>
                                        <x-filament::icon
                                            :icon="$source['source_type'] === 'direct'
                                                ? 'heroicon-o-globe-alt'
                                                : 'heroicon-o-user-group'"
                                            class="admission-decision-overview__source-icon-svg"
                                        />
                                    </span>

                                    <span class="admission-decision-overview__source-details">
                                        <span class="admission-decision-overview__source-name">
                                            {{ $source['name'] }}
                                        </span>

                                        <span class="admission-decision-overview__source-meta">

                                            @if ($source['source_type'] === 'direct')
                                                Direct application
                                            @else
                                                @if (filled($source['code']))
                                                    {{ $source['code'] }}
                                                @else
                                                    Referral partner
                                                @endif

                                                @if (! $source['is_active'])
                                                    <span class="admission-decision-overview__inactive">
                                                        Inactive
                                                    </span>
                                                @endif
                                            @endif

                                        </span>
                                    </span>

                                </div>
                            </td>

                            {{-- Total decisions --}}
                            <td class="is-numeric">
                                <span class="admission-decision-overview__number">
                                    {{ number_format($source['total_decisions']) }}
                                </span>
                            </td>

                            {{-- Unique applicants --}}
                            <td class="is-numeric">
                                <span class="admission-decision-overview__number">
                                    {{ number_format($source['unique_applicants']) }}
                                </span>
                            </td>

                            {{-- Application approvals --}}
                            <td class="is-numeric">
                                <span class="admission-decision-overview__metric admission-decision-overview__metric--success">
                                    {{ number_format($source['approved_applications']) }}
                                </span>
                            </td>

                            {{-- Application rejections --}}
                            <td class="is-numeric">
                                <span class="admission-decision-overview__metric admission-decision-overview__metric--danger">
                                    {{ number_format($source['rejected_applications']) }}
                                </span>
                            </td>

                            {{-- Actual admitted applicants --}}
                            <td class="is-numeric">
                                <span class="admission-decision-overview__metric admission-decision-overview__metric--admitted">
                                    {{ number_format($source['admitted_applicants']) }}
                                </span>
                            </td>

                            {{-- Waitlisted decisions --}}
                            <td class="is-numeric">
                                <span class="admission-decision-overview__number">
                                    {{ number_format($source['waitlisted_decisions']) }}
                                </span>
                            </td>

                            {{-- Actual admission conversion --}}
                            <td class="is-numeric">
                                <div class="admission-decision-overview__conversion">

                                    <span @class([
                                        'admission-decision-overview__conversion-value',
                                        'admission-decision-overview__conversion-value--positive' => $source['admission_conversion_rate'] > 0,
                                        'admission-decision-overview__conversion-value--neutral' => $source['admission_conversion_rate'] <= 0,
                                    ])>
                                        {{ number_format($source['admission_conversion_rate'], 2) }}%
                                    </span>

                                    <span class="admission-decision-overview__conversion-caption">
                                        Admitted applicants
                                    </span>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8">
                                <div class="admission-decision-overview__empty">
                                    <x-filament::icon
                                        icon="heroicon-o-chart-bar"
                                        class="admission-decision-overview__empty-icon"
                                    />

                                    <span>No referral performance data is available.</span>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- Performance explanation --}}
        <div class="admission-decision-overview__footnote">
            <x-filament::icon
                icon="heroicon-o-information-circle"
                class="admission-decision-overview__footnote-icon"
            />

            <p>
                Admission conversion is calculated using unique applicants
                with an actual <strong>ADMITTED</strong> institutional outcome,
                divided by unique applicants attributed to that source.
                Application approval alone is not counted as admission.
            </p>
        </div>

    </section>

</div>
