<x-filament-panels::page>
    @php
        $data = $this->dashboardData ?? [];

        /*
         * Application Management dataset.
         */
        $summary = $data['summary'] ?? [];
        $trends = $data['trends'] ?? [];
        $statusDistribution = $data['status_distribution'] ?? [];
        $programmeDemand = $data['programme_demand'] ?? [];
        $referralPerformance = $data['referral_performance'] ?? [];
        $recentActivity = $data['recent_activity'] ?? [];
        $actionCentre = $data['action_centre'] ?? [];

        $filterOptions = $this->filterOptions ?? [];

        /*
         * Institutional Admission Outcomes dataset.
         */
        $admissionDecisionData = $data['admission_decisions'] ?? [];

        $admissionSummary = $admissionDecisionData['summary'] ?? [];
        $admissionStatusDistribution = $admissionDecisionData['status_distribution'] ?? [];
        $admissionReferralPerformance = $admissionDecisionData['referral_performance'] ?? [];
        $admissionRecentActivity = $admissionDecisionData['recent_activity'] ?? [];

        $admissionFilterOptions =
            $admissionDecisionData['filter_options']['admission_statuses'] ?? [];

        /*
         * Trend calculations.
         */
        $trendLabels = $trends['labels'] ?? [];
        $trendValues = $trends['values'] ?? [];

        $maximumTrend = max(
            array_merge([1], array_map('intval', $trendValues))
        );

        $maximumStatus = max(
            1,
            (int) collect($statusDistribution)->max('total')
        );

        $maximumProgramme = max(
            1,
            (int) collect($programmeDemand)->max('total')
        );

        $maximumReferral = max(
            1,
            (int) collect($referralPerformance)->max('total')
        );

        $maximumAdmissionStatus = max(
            1,
            (int) collect($admissionStatusDistribution)->max('total')
        );

        $maximumAdmissionReferral = max(
            1,
            (int) collect($admissionReferralPerformance)->max('total_decisions')
        );

        /*
         * Formatting helpers.
         */
        $formatNumber = fn ($value) => number_format((int) ($value ?? 0));

        $formatRate = fn ($value) =>
            number_format((float) ($value ?? 0), 1) . '%';

        /*
         * Application processing status classes.
         */
        $statusClasses = [
            'DRAFT' => 'draft',
            'SUBMITTED' => 'submitted',
            'UNDER_REVIEW' => 'review',
            'CORRECTION_REQUIRED' => 'correction',
            'APPROVED' => 'approved',
            'REJECTED' => 'rejected',
        ];

        /*
         * Institutional admission outcome classes.
         */
        $admissionStatusClasses = [
            'PENDING' => 'pending',
            'ADMITTED' => 'admitted',
            'NOT_ADMITTED' => 'not-admitted',
            'WAITLISTED' => 'waitlisted',
            'DEFERRED' => 'deferred',
        ];

        $activeTab = in_array(
            $this->activeTab ?? 'applications',
            ['applications', 'outcomes'],
            true
        ) ? $this->activeTab : 'applications';
    @endphp

    <div class="admission-dashboard">

        {{-- =========================================================
             SHARED DASHBOARD HERO
        ========================================================== --}}
        <section class="admission-dashboard__hero">
            <div class="admission-dashboard__hero-content">
                <div class="admission-dashboard__eyebrow">
                    <span class="admission-dashboard__eyebrow-dot"></span>
                    ADMISSIONS MANAGEMENT
                </div>

                <h1>Admissions Overview</h1>

                <p>
                    A central workspace for managing applications and
                    monitoring institutional admission outcomes.
                </p>
            </div>

            <div class="admission-dashboard__hero-meta">
                <div class="admission-dashboard__hero-meta-label">
                    REPORTING PERIOD
                </div>

                <div class="admission-dashboard__hero-meta-value">
                    {{ $filterOptions['periods'][$this->period] ?? 'All time' }}
                </div>

                <div class="admission-dashboard__hero-meta-caption">
                    Institutional admissions performance
                </div>
            </div>
        </section>

        {{-- =========================================================
             WORKSPACE NAVIGATION
        ========================================================== --}}
        <nav
            class="admission-dashboard__workspace-nav"
            aria-label="Admissions dashboard workspaces"
        >
            <button
                type="button"
                wire:click="switchTab('applications')"
                @class([
                    'admission-dashboard__workspace-tab',
                    'is-active' => $activeTab === 'applications',
                ])
                @if($activeTab === 'applications') aria-current="page" @endif
            >
                <span class="admission-dashboard__workspace-tab-icon">
                    <x-filament::icon icon="heroicon-o-document-text" />
                </span>

                <span class="admission-dashboard__workspace-tab-content">
                    <strong>Application Management</strong>
                    <small>Applications, reviews and processing</small>
                </span>

                @if($activeTab === 'applications')
                    <span class="admission-dashboard__workspace-tab-indicator"></span>
                @endif
            </button>

            <button
                type="button"
                wire:click="switchTab('outcomes')"
                @class([
                    'admission-dashboard__workspace-tab',
                    'is-active' => $activeTab === 'outcomes',
                ])
                @if($activeTab === 'outcomes') aria-current="page" @endif
            >
                <span class="admission-dashboard__workspace-tab-icon">
                    <x-filament::icon icon="heroicon-o-academic-cap" />
                </span>

                <span class="admission-dashboard__workspace-tab-content">
                    <strong>Admission Outcomes</strong>
                    <small>Institutional decisions and admission conversion</small>
                </span>

                @if($activeTab === 'outcomes')
                    <span class="admission-dashboard__workspace-tab-indicator"></span>
                @endif
            </button>
        </nav>

        {{-- =========================================================
             APPLICATION MANAGEMENT WORKSPACE
        ========================================================== --}}
        @if($activeTab === 'applications')

            <section
                class="admission-dashboard__workspace"
                aria-label="Application Management"
            >
                <header class="admission-dashboard__workspace-header">
                    <div>
                        <span class="admission-dashboard__section-kicker">
                            APPLICATION WORKSPACE
                        </span>

                        <h2>Application Management</h2>

                        <p>
                            Monitor applicant activity, review application
                            performance and identify applications requiring
                            operational attention.
                        </p>
                    </div>

                    <div class="admission-dashboard__workspace-context">
                        <span class="admission-dashboard__workspace-context-dot"></span>
                        Application processing
                    </div>
                </header>

                {{-- Application filters --}}
                <section class="admission-dashboard__filters">
                    <div class="admission-dashboard__section-heading">
                        <div>
                            <span class="admission-dashboard__section-kicker">
                                REFINE APPLICATION REPORTING
                            </span>

                            <h2>Application filters</h2>

                            <p>
                                Narrow application analytics by period,
                                processing status, programme and payment.
                            </p>
                        </div>
                    </div>

                    <form
                        wire:submit.prevent="applyFilters"
                        class="admission-dashboard__filter-form"
                    >
                        <div class="admission-dashboard__filter-grid">

                            <div class="admission-dashboard__filter-field">
                                <label for="application-period">Reporting period</label>

                                <select id="application-period" wire:model="period">
                                    @foreach(($filterOptions['periods'] ?? []) as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="admission-dashboard__filter-field">
                                <label for="application-status">Application status</label>

                                <select id="application-status" wire:model="status">
                                    @foreach(($filterOptions['statuses'] ?? []) as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="admission-dashboard__filter-field">
                                <label for="application-programme">Programme</label>

                                <select id="application-programme" wire:model="programmeId">
                                    <option value="">All programmes</option>

                                    @foreach(($filterOptions['programmes'] ?? []) as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="admission-dashboard__filter-field">
                                <label for="application-referral">Referral source</label>

                                <select id="application-referral" wire:model="referralSource">
                                    @foreach(($filterOptions['referral_sources'] ?? []) as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="admission-dashboard__filter-field">
                                <label for="application-payment">Payment status</label>

                                <select id="application-payment" wire:model="paymentStatus">
                                    @foreach(($filterOptions['payment_statuses'] ?? []) as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if($this->period === 'custom')
                                <div class="admission-dashboard__filter-field">
                                    <label for="application-date-from">From date</label>

                                    <input
                                        id="application-date-from"
                                        type="date"
                                        wire:model="dateFrom"
                                    />
                                </div>

                                <div class="admission-dashboard__filter-field">
                                    <label for="application-date-to">To date</label>

                                    <input
                                        id="application-date-to"
                                        type="date"
                                        wire:model="dateTo"
                                    />
                                </div>
                            @endif

                        </div>

                        <div class="admission-dashboard__filter-actions">
                            <button
                                type="button"
                                class="admission-dashboard__reset-button"
                                wire:click="resetDashboardFilters"
                                wire:loading.attr="disabled"
                            >
                                Reset filters
                            </button>

                            <button
                                type="submit"
                                class="admission-dashboard__apply-button"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove wire:target="applyFilters">
                                    Apply filters
                                </span>

                                <span wire:loading wire:target="applyFilters">
                                    Applying...
                                </span>
                            </button>
                        </div>
                    </form>
                </section>

                {{-- Application KPI cards --}}
                <section class="admission-dashboard__section">
                    <div class="admission-dashboard__section-heading">
                        <div>
                            <span class="admission-dashboard__section-kicker">
                                APPLICATION PERFORMANCE
                            </span>

                            <h2>Application processing at a glance</h2>

                            <p>
                                Application, applicant and payment indicators.
                                Application approval is not an institutional
                                admission offer.
                            </p>
                        </div>
                    </div>

                    <div class="admission-dashboard__kpi-grid">
                        @foreach([
                            [
                                'label' => 'Total applications',
                                'value' => $summary['total'] ?? 0,
                                'tone' => 'navy',
                                'symbol' => 'A',
                            ],
                            [
                                'label' => 'Unique applicants',
                                'value' => $summary['unique_applicants'] ?? 0,
                                'tone' => 'blue',
                                'symbol' => 'U',
                            ],
                            [
                                'label' => 'Submitted',
                                'value' => $summary['submitted'] ?? 0,
                                'tone' => 'blue',
                                'symbol' => 'S',
                            ],
                            [
                                'label' => 'Under review',
                                'value' => $summary['under_review'] ?? 0,
                                'tone' => 'amber',
                                'symbol' => 'R',
                            ],
                            [
                                'label' => 'Correction required',
                                'value' => $summary['correction_required'] ?? 0,
                                'tone' => 'orange',
                                'symbol' => 'C',
                            ],
                            [
                                'label' => 'Approved applications',
                                'value' => $summary['approved'] ?? 0,
                                'tone' => 'green',
                                'symbol' => '✓',
                            ],
                            [
                                'label' => 'Approved applicants',
                                'value' => $summary['approved_applicants'] ?? 0,
                                'tone' => 'green',
                                'symbol' => '✓',
                            ],
                            [
                                'label' => 'Rejected',
                                'value' => $summary['rejected'] ?? 0,
                                'tone' => 'red',
                                'symbol' => '×',
                            ],
                            [
                                'label' => 'Draft applications',
                                'value' => $summary['draft'] ?? 0,
                                'tone' => 'slate',
                                'symbol' => 'D',
                            ],
                            [
                                'label' => 'Awaiting review',
                                'value' => $summary['awaiting_review'] ?? 0,
                                'tone' => 'purple',
                                'symbol' => '!',
                            ],
                        ] as $metric)
                            <article class="admission-dashboard__kpi-card">
                                <div class="admission-dashboard__kpi-top">
                                    <span class="admission-dashboard__kpi-label">
                                        {{ $metric['label'] }}
                                    </span>

                                    <span class="admission-dashboard__kpi-icon admission-dashboard__kpi-icon--{{ $metric['tone'] }}">
                                        {{ $metric['symbol'] }}
                                    </span>
                                </div>

                                <div class="admission-dashboard__kpi-value">
                                    {{ $formatNumber($metric['value']) }}
                                </div>

                                <div class="admission-dashboard__kpi-footer">
                                    Application processing
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="admission-dashboard__payment-strip">
                        <div class="admission-dashboard__payment-item">
                            <span class="admission-dashboard__payment-dot pending"></span>
                            <span>Payments pending</span>
                            <strong>{{ $formatNumber($summary['payment_pending'] ?? 0) }}</strong>
                        </div>

                        <div class="admission-dashboard__payment-item">
                            <span class="admission-dashboard__payment-dot successful"></span>
                            <span>Successful payments</span>
                            <strong>{{ $formatNumber($summary['payment_success'] ?? 0) }}</strong>
                        </div>

                        <div class="admission-dashboard__payment-item">
                            <span class="admission-dashboard__payment-dot failed"></span>
                            <span>Failed payments</span>
                            <strong>{{ $formatNumber($summary['payment_failed'] ?? 0) }}</strong>
                        </div>
                    </div>
                </section>

                {{-- Application trends and status distribution --}}
                <section class="admission-dashboard__analytics-grid">

                    <article class="admission-dashboard__panel admission-dashboard__trends-panel">
                        <div class="admission-dashboard__panel-heading">
                            <div>
                                <span class="admission-dashboard__section-kicker">
                                    APPLICATION ACTIVITY
                                </span>

                                <h2>Application trends</h2>

                                <p>
                                    {{ $trends['grouping'] ?? 'Application activity' }}
                                    overview
                                </p>
                            </div>

                            <div class="admission-dashboard__panel-total">
                                <strong>{{ $formatNumber($trends['total'] ?? 0) }}</strong>
                                <span>Applications</span>
                            </div>
                        </div>

                        @if(count($trendLabels))
                            <div class="admission-dashboard__trend-chart">
                                <div class="admission-dashboard__trend-bars">
                                    @foreach($trendLabels as $index => $label)
                                        @php
                                            $value = (int) ($trendValues[$index] ?? 0);

                                            $height = $maximumTrend > 0
                                                ? max(3, ($value / $maximumTrend) * 100)
                                                : 3;
                                        @endphp

                                        <div class="admission-dashboard__trend-column">
                                            <div class="admission-dashboard__trend-value">
                                                {{ $value > 0 ? $value : '' }}
                                            </div>

                                            <div class="admission-dashboard__trend-track">
                                                <div
                                                    class="admission-dashboard__trend-bar"
                                                    style="height: {{ $height }}%"
                                                    title="{{ $label }}: {{ $value }}"
                                                ></div>
                                            </div>

                                            <span class="admission-dashboard__trend-label">
                                                {{ $label }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="admission-dashboard__empty-state">
                                <span class="admission-dashboard__empty-symbol">↗</span>

                                <strong>No application trends available</strong>

                                <p>
                                    Application activity will appear here when records are available.
                                </p>
                            </div>
                        @endif
                    </article>

                    <article class="admission-dashboard__panel">
                        <div class="admission-dashboard__panel-heading">
                            <div>
                                <span class="admission-dashboard__section-kicker">
                                    APPLICATION BREAKDOWN
                                </span>

                                <h2>Status distribution</h2>

                                <p>Applications grouped by their current status.</p>
                            </div>
                        </div>

                        @forelse($statusDistribution as $item)
                            @php
                                $statusKey = strtoupper($item['status'] ?? '');

                                $statusClass = $statusClasses[$statusKey] ?? 'default';

                                $percentage = ($item['total'] ?? 0) / $maximumStatus * 100;
                            @endphp

                            <div class="admission-dashboard__distribution-item">
                                <div class="admission-dashboard__distribution-meta">
                                    <span>
                                        <span class="admission-dashboard__status-dot {{ $statusClass }}"></span>

                                        {{ $item['label'] ?? str_replace('_', ' ', $statusKey) }}
                                    </span>

                                    <strong>
                                        {{ $formatNumber($item['total'] ?? 0) }}
                                    </strong>
                                </div>

                                <div class="admission-dashboard__progress-track">
                                    <div
                                        class="admission-dashboard__progress-fill {{ $statusClass }}"
                                        style="width: {{ $percentage }}%"
                                    ></div>
                                </div>
                            </div>
                        @empty
                            <div class="admission-dashboard__empty-state">
                                <strong>No status data available</strong>

                                <p>
                                    Application status distribution will appear here.
                                </p>
                            </div>
                        @endforelse
                    </article>

                </section>

                {{-- Programme demand and application referral performance --}}
                <section class="admission-dashboard__analytics-grid">

                    <article class="admission-dashboard__panel">
                        <div class="admission-dashboard__panel-heading">
                            <div>
                                <span class="admission-dashboard__section-kicker">
                                    PROGRAMME INTEREST
                                </span>

                                <h2>Programme demand</h2>

                                <p>Programmes attracting the highest applications.</p>
                            </div>
                        </div>

                        @forelse($programmeDemand as $item)
                            @php
                                $percentage = ($item['total'] ?? 0) / $maximumProgramme * 100;
                            @endphp

                            <div class="admission-dashboard__ranking-item">
                                <div class="admission-dashboard__ranking-meta">
                                    <div class="admission-dashboard__ranking-name">
                                        {{ $item['programme'] ?? 'Unassigned programme' }}
                                    </div>

                                    <strong>
                                        {{ $formatNumber($item['total'] ?? 0) }}
                                    </strong>
                                </div>

                                <div class="admission-dashboard__progress-track">
                                    <div
                                        class="admission-dashboard__progress-fill programme"
                                        style="width: {{ $percentage }}%"
                                    ></div>
                                </div>
                            </div>
                        @empty
                            <div class="admission-dashboard__empty-state">
                                <strong>No programme demand data</strong>

                                <p>
                                    Programme application statistics will appear here.
                                </p>
                            </div>
                        @endforelse
                    </article>

                    <article class="admission-dashboard__panel">
                        <div class="admission-dashboard__panel-heading">
                            <div>
                                <span class="admission-dashboard__section-kicker">
                                    APPLICATION ATTRIBUTION
                                </span>

                                <h2>Application referral performance</h2>

                                <p>
                                    Compare application volume, applicant reach,
                                    application approvals and payment performance.
                                </p>
                            </div>
                        </div>

                        @forelse($referralPerformance as $item)
                            @php
                                $percentage = ($item['total'] ?? 0) / $maximumReferral * 100;

                                $sourceType = $item['source_type'] ?? $item['source'] ?? 'direct';

                                $isPartner = $sourceType === 'partner';

                                $approvalRate = $item['applicant_admission_rate'] ?? 0;
                            @endphp

                            <div class="admission-dashboard__ranking-item admission-dashboard__referral-item">
                                <div class="admission-dashboard__ranking-meta">
                                    <div>
                                        <div class="admission-dashboard__ranking-name">
                                            {{ $item['name'] ?? 'Unknown source' }}
                                        </div>

                                        <span class="admission-dashboard__ranking-caption">
                                            @if($isPartner)
                                                Referral partner

                                                @if(!empty($item['code']))
                                                    · {{ $item['code'] }}
                                                @endif
                                            @else
                                                Direct / Organic
                                            @endif

                                            @if(
                                                $isPartner &&
                                                array_key_exists('is_active', $item) &&
                                                !$item['is_active']
                                            )
                                                · Inactive
                                            @endif
                                        </span>
                                    </div>

                                    <strong>
                                        {{ $formatNumber($item['total_applications'] ?? $item['total'] ?? 0) }}
                                    </strong>
                                </div>

                                <div class="admission-dashboard__progress-track">
                                    <div
                                        class="admission-dashboard__progress-fill referral"
                                        style="width: {{ $percentage }}%"
                                    ></div>
                                </div>

                                <div class="admission-dashboard__referral-metrics">
                                    <div class="admission-dashboard__referral-metric">
                                        <span>Unique applicants</span>
                                        <strong>{{ $formatNumber($item['unique_applicants'] ?? 0) }}</strong>
                                    </div>

                                    <div class="admission-dashboard__referral-metric">
                                        <span>Approved applications</span>
                                        <strong>{{ $formatNumber($item['approved_applications'] ?? 0) }}</strong>
                                    </div>

                                    <div class="admission-dashboard__referral-metric">
                                        <span>Approved applicants</span>
                                        <strong>{{ $formatNumber($item['approved_applicants'] ?? 0) }}</strong>
                                    </div>

                                    <div class="admission-dashboard__referral-metric">
                                        <span>Application approval rate</span>
                                        <strong>{{ $formatRate($approvalRate) }}</strong>
                                    </div>

                                    <div class="admission-dashboard__referral-metric">
                                        <span>Paid applications</span>
                                        <strong>{{ $formatNumber($item['paid_applications'] ?? 0) }}</strong>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="admission-dashboard__empty-state">
                                <strong>No referral data available</strong>

                                <p>
                                    Application referral performance will appear here.
                                </p>
                            </div>
                        @endforelse
                    </article>

                </section>

                {{-- Application action centre --}}
                <section class="admission-dashboard__section">
                    <div class="admission-dashboard__section-heading">
                        <div>
                            <span class="admission-dashboard__section-kicker">
                                OPERATIONAL PRIORITIES
                            </span>

                            <h2>Admissions action centre</h2>

                            <p>
                                Applications requiring attention from admission officers.
                            </p>
                        </div>
                    </div>

                    <div class="admission-dashboard__action-grid">
                        @foreach([
                            [
                                'key' => 'awaiting_review',
                                'title' => 'Awaiting review',
                                'description' => 'Submitted applications requiring review.',
                                'tone' => 'blue',
                            ],
                            [
                                'key' => 'awaiting_decision',
                                'title' => 'Awaiting decision',
                                'description' => 'Applications currently awaiting a decision.',
                                'tone' => 'amber',
                            ],
                            [
                                'key' => 'correction_required',
                                'title' => 'Correction required',
                                'description' => 'Applications requiring applicant attention.',
                                'tone' => 'orange',
                            ],
                        ] as $action)
                            @php
                                $items = $actionCentre[$action['key']] ?? [];
                            @endphp

                            <article class="admission-dashboard__action-card admission-dashboard__action-card--{{ $action['tone'] }}">
                                <div class="admission-dashboard__action-card-heading">
                                    <div>
                                        <h3>{{ $action['title'] }}</h3>
                                        <p>{{ $action['description'] }}</p>
                                    </div>

                                    <span class="admission-dashboard__action-count">
                                        {{ $formatNumber(count($items)) }}
                                    </span>
                                </div>

                                <div class="admission-dashboard__action-list">
                                    @forelse($items as $item)
                                        <div class="admission-dashboard__action-item">
                                            <div>
                                                <strong>
                                                    {{ data_get($item, 'application_number', 'Application') }}
                                                </strong>

                                                <span>
                                                    {{ data_get($item, 'user.name', 'Applicant') }}
                                                </span>
                                            </div>

                                            <span class="admission-dashboard__action-programme">
                                                {{ data_get($item, 'programme.name', 'Programme not specified') }}
                                            </span>
                                        </div>
                                    @empty
                                        <div class="admission-dashboard__action-empty">
                                            No applications currently in this queue.
                                        </div>
                                    @endforelse
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                {{-- Recent application activity --}}
                <section class="admission-dashboard__panel admission-dashboard__activity-panel">
                    <div class="admission-dashboard__panel-heading">
                        <div>
                            <span class="admission-dashboard__section-kicker">
                                APPLICATION UPDATES
                            </span>

                            <h2>Recent application activity</h2>

                            <p>
                                Recent application creation and application
                                processing status changes.
                            </p>
                        </div>
                    </div>

                    <div class="admission-dashboard__activity-list">
                        @forelse($recentActivity as $activity)
                            @php
                                $activityStatus = strtoupper($activity['status'] ?? '');

                                $activityClass = $statusClasses[$activityStatus] ?? 'default';
                            @endphp

                            <article class="admission-dashboard__activity-item">
                                <span class="admission-dashboard__activity-marker {{ $activityClass }}"></span>

                                <div class="admission-dashboard__activity-content">
                                    <div class="admission-dashboard__activity-top">
                                        <strong>
                                            {{ $activity['title'] ?? 'Admissions activity' }}
                                        </strong>

                                        @if(!empty($activity['application_number']))
                                            <span class="admission-dashboard__activity-reference">
                                                {{ $activity['application_number'] }}
                                            </span>
                                        @endif
                                    </div>

                                    <p>
                                        {{ $activity['description'] ?? 'An application record was updated.' }}
                                    </p>

                                    <div class="admission-dashboard__activity-meta">
                                        @if(!empty($activity['programme']))
                                            <span>{{ $activity['programme'] }}</span>
                                        @endif

                                        @if(!empty($activity['created_at']))
                                            <span>
                                                {{ \Illuminate\Support\Carbon::parse($activity['created_at'])->format('d M Y, h:i A') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if(!empty($activity['status']))
                                    <span class="admission-dashboard__status-badge {{ $activityClass }}">
                                        {{ str_replace('_', ' ', $activityStatus) }}
                                    </span>
                                @endif
                            </article>
                        @empty
                            <div class="admission-dashboard__empty-state">
                                <strong>No recent application activity</strong>

                                <p>New application activity will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </section>

        @endif

        {{-- =========================================================
             INSTITUTIONAL ADMISSION OUTCOMES WORKSPACE
        ========================================================== --}}
        @if($activeTab === 'outcomes')

            <section
                class="admission-dashboard__workspace admission-dashboard__outcomes-workspace"
                aria-label="Institutional Admission Outcomes"
            >
                <header class="admission-dashboard__workspace-header">
                    <div>
                        <span class="admission-dashboard__section-kicker">
                            INSTITUTIONAL DECISION WORKSPACE
                        </span>

                        <h2>Admission Outcomes</h2>

                        <p>
                            Monitor the institution's actual admission decisions,
                            evaluate admission conversion and review recent
                            outcome changes.
                        </p>
                    </div>

                    <div class="admission-dashboard__workspace-context admission-dashboard__workspace-context--outcomes">
                        <span class="admission-dashboard__workspace-context-dot"></span>
                        Institutional decisions
                    </div>
                </header>

                {{-- Admission outcome filters --}}
                <section class="admission-dashboard__filters admission-dashboard__outcome-filters">
                    <div class="admission-dashboard__section-heading">
                        <div>
                            <span class="admission-dashboard__section-kicker">
                                REFINE OUTCOME REPORTING
                            </span>

                            <h2>Admission outcome filters</h2>

                            <p>
                                Focus on institutional decisions by reporting
                                period, outcome, programme and referral source.
                            </p>
                        </div>
                    </div>

                    <form
                        wire:submit.prevent="applyFilters"
                        class="admission-dashboard__filter-form"
                    >
                        <div class="admission-dashboard__filter-grid">

                            <div class="admission-dashboard__filter-field">
                                <label for="outcome-period">Reporting period</label>

                                <select id="outcome-period" wire:model="period">
                                    @foreach(($filterOptions['periods'] ?? []) as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="admission-dashboard__filter-field admission-dashboard__filter-field--outcome">
                                <label for="outcome-status">Admission outcome</label>

                                <select
                                    id="outcome-status"
                                    wire:model="admissionStatus"
                                >
                                    <option value="all">All admission outcomes</option>

                                    @foreach($admissionFilterOptions as $option)
                                        <option value="{{ $option['value'] }}">
                                            {{ $option['label'] }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="admission-dashboard__filter-hint">
                                    Filters institutional admission decisions.
                                </span>
                            </div>

                            <div class="admission-dashboard__filter-field">
                                <label for="outcome-programme">Programme</label>

                                <select id="outcome-programme" wire:model="programmeId">
                                    <option value="">All programmes</option>

                                    @foreach(($filterOptions['programmes'] ?? []) as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="admission-dashboard__filter-field">
                                <label for="outcome-referral">Referral source</label>

                                <select id="outcome-referral" wire:model="referralSource">
                                    @foreach(($filterOptions['referral_sources'] ?? []) as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if($this->period === 'custom')
                                <div class="admission-dashboard__filter-field">
                                    <label for="outcome-date-from">From date</label>

                                    <input
                                        id="outcome-date-from"
                                        type="date"
                                        wire:model="dateFrom"
                                    />
                                </div>

                                <div class="admission-dashboard__filter-field">
                                    <label for="outcome-date-to">To date</label>

                                    <input
                                        id="outcome-date-to"
                                        type="date"
                                        wire:model="dateTo"
                                    />
                                </div>
                            @endif

                        </div>

                        <div class="admission-dashboard__filter-actions">
                            <button
                                type="button"
                                class="admission-dashboard__reset-button"
                                wire:click="resetDashboardFilters"
                                wire:loading.attr="disabled"
                            >
                                Reset filters
                            </button>

                            <button
                                type="submit"
                                class="admission-dashboard__apply-button"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove wire:target="applyFilters">
                                    Apply filters
                                </span>

                                <span wire:loading wire:target="applyFilters">
                                    Applying...
                                </span>
                            </button>
                        </div>
                    </form>
                </section>

                {{-- Admission outcome KPIs --}}
                <section class="admission-dashboard__section admission-dashboard__outcomes">
                    <div class="admission-dashboard__section-heading admission-dashboard__outcomes-heading">
                        <div>
                            <span class="admission-dashboard__section-kicker">
                                INSTITUTIONAL ADMISSION PERFORMANCE
                            </span>

                            <h2>Admission outcomes at a glance</h2>

                            <p>
                                Actual institutional admission decisions.
                                These figures are separate from application
                                processing approvals.
                            </p>
                        </div>

                        <div class="admission-dashboard__outcomes-filter-indicator">
                            <span>Current outcome filter</span>

                            <strong>
                                @if($this->admissionStatus === 'all')
                                    All outcomes
                                @else
                                    {{ str($this->admissionStatus)->replace('_', ' ')->title() }}
                                @endif
                            </strong>
                        </div>
                    </div>

                    <div class="admission-dashboard__outcome-kpi-grid">
                        @foreach([
                            [
                                'label' => 'Total decisions',
                                'value' => $admissionSummary['total'] ?? 0,
                                'tone' => 'navy',
                                'symbol' => 'D',
                            ],
                            [
                                'label' => 'Unique applicants',
                                'value' => $admissionSummary['unique_applicants'] ?? 0,
                                'tone' => 'blue',
                                'symbol' => 'U',
                            ],
                            [
                                'label' => 'Admitted',
                                'value' => $admissionSummary['admitted'] ?? 0,
                                'tone' => 'green',
                                'symbol' => '✓',
                            ],
                            [
                                'label' => 'Not admitted',
                                'value' => $admissionSummary['not_admitted'] ?? 0,
                                'tone' => 'red',
                                'symbol' => '×',
                            ],
                            [
                                'label' => 'Waitlisted',
                                'value' => $admissionSummary['waitlisted'] ?? 0,
                                'tone' => 'amber',
                                'symbol' => 'W',
                            ],
                            [
                                'label' => 'Deferred',
                                'value' => $admissionSummary['deferred'] ?? 0,
                                'tone' => 'purple',
                                'symbol' => 'D',
                            ],
                            [
                                'label' => 'Pending outcome',
                                'value' => $admissionSummary['pending'] ?? 0,
                                'tone' => 'slate',
                                'symbol' => 'P',
                            ],
                            [
                                'label' => 'Admitted applicants',
                                'value' => $admissionSummary['admitted_applicants'] ?? 0,
                                'tone' => 'green',
                                'symbol' => '✓',
                            ],
                        ] as $metric)
                            <article class="admission-dashboard__outcome-kpi-card">
                                <div class="admission-dashboard__outcome-kpi-top">
                                    <span class="admission-dashboard__outcome-kpi-label">
                                        {{ $metric['label'] }}
                                    </span>

                                    <span class="admission-dashboard__outcome-kpi-icon admission-dashboard__outcome-kpi-icon--{{ $metric['tone'] }}">
                                        {{ $metric['symbol'] }}
                                    </span>
                                </div>

                                <div class="admission-dashboard__outcome-kpi-value">
                                    {{ $formatNumber($metric['value']) }}
                                </div>

                                <div class="admission-dashboard__outcome-kpi-footer">
                                    Institutional admission outcome
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                {{-- Outcome distribution and admission referral conversion --}}
                <section class="admission-dashboard__outcome-analytics-grid">

                    <article class="admission-dashboard__panel admission-dashboard__outcome-panel">
                        <div class="admission-dashboard__panel-heading">
                            <div>
                                <span class="admission-dashboard__section-kicker">
                                    OUTCOME BREAKDOWN
                                </span>

                                <h2>Admission status distribution</h2>

                                <p>
                                    Institutional decisions grouped by their
                                    current admission outcome.
                                </p>
                            </div>
                        </div>

                        @forelse($admissionStatusDistribution as $item)
                            @php
                                $outcomeKey = strtoupper($item['status'] ?? '');

                                $outcomeClass =
                                    $admissionStatusClasses[$outcomeKey] ?? 'default';

                                $percentage = (
                                    (int) ($item['total'] ?? 0)
                                    / $maximumAdmissionStatus
                                ) * 100;
                            @endphp

                            <div class="admission-dashboard__outcome-distribution-item">
                                <div class="admission-dashboard__distribution-meta">
                                    <span>
                                        <span class="admission-dashboard__outcome-status-dot {{ $outcomeClass }}"></span>

                                        {{ $item['label'] ?? str($outcomeKey)->replace('_', ' ')->title() }}
                                    </span>

                                    <strong>
                                        {{ $formatNumber($item['total'] ?? 0) }}
                                    </strong>
                                </div>

                                <div class="admission-dashboard__progress-track">
                                    <div
                                        class="admission-dashboard__progress-fill admission-outcome-{{ $outcomeClass }}"
                                        style="width: {{ $percentage }}%"
                                    ></div>
                                </div>
                            </div>
                        @empty
                            <div class="admission-dashboard__empty-state">
                                <strong>No admission outcome data available</strong>

                                <p>
                                    Institutional admission outcome distribution
                                    will appear here when decisions are available.
                                </p>
                            </div>
                        @endforelse
                    </article>

                    <article class="admission-dashboard__panel admission-dashboard__outcome-panel">
                        <div class="admission-dashboard__panel-heading">
                            <div>
                                <span class="admission-dashboard__section-kicker">
                                    INSTITUTIONAL CONVERSION
                                </span>

                                <h2>Admission referral performance</h2>

                                <p>
                                    Compare actual admission outcomes by referral
                                    partner and direct application source.
                                </p>
                            </div>
                        </div>

                        @forelse($admissionReferralPerformance as $item)
                            @php
                                $sourceType = $item['source_type'] ?? 'direct';

                                $isPartner = $sourceType === 'partner';

                                $decisionTotal = (int) (
                                    $item['total_decisions'] ?? 0
                                );

                                $percentage = $maximumAdmissionReferral > 0
                                    ? ($decisionTotal / $maximumAdmissionReferral) * 100
                                    : 0;
                            @endphp

                            <div class="admission-dashboard__outcome-referral-item">
                                <div class="admission-dashboard__ranking-meta">
                                    <div>
                                        <div class="admission-dashboard__ranking-name">
                                            {{ $item['name'] ?? 'Unknown source' }}
                                        </div>

                                        <span class="admission-dashboard__ranking-caption">
                                            @if($isPartner)
                                                Referral partner

                                                @if(!empty($item['code']))
                                                    · {{ $item['code'] }}
                                                @endif
                                            @else
                                                Direct / Organic
                                            @endif

                                            @if(
                                                $isPartner &&
                                                array_key_exists('is_active', $item) &&
                                                !$item['is_active']
                                            )
                                                · Inactive
                                            @endif
                                        </span>
                                    </div>

                                    <strong>
                                        {{ $formatNumber($decisionTotal) }}
                                    </strong>
                                </div>

                                <div class="admission-dashboard__progress-track">
                                    <div
                                        class="admission-dashboard__progress-fill admission-outcome-referral"
                                        style="width: {{ $percentage }}%"
                                    ></div>
                                </div>

                                <div class="admission-dashboard__outcome-referral-metrics">
                                    <div>
                                        <span>Unique applicants</span>
                                        <strong>{{ $formatNumber($item['unique_applicants'] ?? 0) }}</strong>
                                    </div>

                                    <div>
                                        <span>Admitted decisions</span>
                                        <strong>{{ $formatNumber($item['admitted'] ?? 0) }}</strong>
                                    </div>

                                    <div>
                                        <span>Admitted applicants</span>
                                        <strong>{{ $formatNumber($item['admitted_applicants'] ?? 0) }}</strong>
                                    </div>

                                    <div>
                                        <span>Admission conversion</span>
                                        <strong>{{ $formatRate($item['applicant_admission_rate'] ?? 0) }}</strong>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="admission-dashboard__empty-state">
                                <strong>No admission referral data available</strong>

                                <p>
                                    Institutional admission referral performance
                                    will appear here when outcome records are available.
                                </p>
                            </div>
                        @endforelse
                    </article>

                </section>

                {{-- Recent institutional admission outcome activity --}}
                <section class="admission-dashboard__panel admission-dashboard__activity-panel admission-dashboard__outcome-activity-panel">
                    <div class="admission-dashboard__panel-heading">
                        <div>
                            <span class="admission-dashboard__section-kicker">
                                INSTITUTIONAL DECISION HISTORY
                            </span>

                            <h2>Recent admission outcome activity</h2>

                            <p>
                                Recent changes recorded in the separate
                                institutional admission decision history.
                            </p>
                        </div>
                    </div>

                    <div class="admission-dashboard__activity-list">
                        @forelse($admissionRecentActivity as $activity)
                            @php
                                $activityStatus = strtoupper($activity['status'] ?? '');

                                $activityClass =
                                    $admissionStatusClasses[$activityStatus] ?? 'default';
                            @endphp

                            <article class="admission-dashboard__activity-item admission-dashboard__outcome-activity-item">
                                <span class="admission-dashboard__activity-marker admission-outcome-marker-{{ $activityClass }}"></span>

                                <div class="admission-dashboard__activity-content">
                                    <div class="admission-dashboard__activity-top">
                                        <strong>
                                            {{ $activity['title'] ?? 'Admission outcome updated' }}
                                        </strong>

                                        @if(!empty($activity['application_number']))
                                            <span class="admission-dashboard__activity-reference">
                                                {{ $activity['application_number'] }}
                                            </span>
                                        @endif
                                    </div>

                                    <p>
                                        {{ $activity['description'] ?? 'An institutional admission outcome was updated.' }}
                                    </p>

                                    <div class="admission-dashboard__activity-meta">
                                        @if(!empty($activity['applicant']))
                                            <span>{{ $activity['applicant'] }}</span>
                                        @endif

                                        @if(!empty($activity['programme']))
                                            <span>{{ $activity['programme'] }}</span>
                                        @endif

                                        @if(!empty($activity['officer']))
                                            <span>Officer: {{ $activity['officer'] }}</span>
                                        @endif

                                        @if(!empty($activity['created_at']))
                                            <span>
                                                {{ \Illuminate\Support\Carbon::parse($activity['created_at'])->format('d M Y, h:i A') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if(!empty($activity['status']))
                                    <span class="admission-dashboard__status-badge admission-outcome-badge-{{ $activityClass }}">
                                        {{ str($activityStatus)->replace('_', ' ')->title() }}
                                    </span>
                                @endif
                            </article>
                        @empty
                            <div class="admission-dashboard__empty-state">
                                <strong>No recent admission outcome activity</strong>

                                <p>
                                    Institutional admission decision history
                                    will appear here when outcomes are recorded.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </section>

        @endif

    </div>
</x-filament-panels::page>
