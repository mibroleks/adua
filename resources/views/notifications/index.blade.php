{{--
|--------------------------------------------------------------------------
| Component: Applicant Notifications
|--------------------------------------------------------------------------
| File:
| resources/views/notifications/index.blade.php
|
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Displays applicant notifications with unread state and relevant links.
|
| Status: 🚦 Hardened
| Version: 2.1 (fixed payload access from data JSON)
|--------------------------------------------------------------------------
--}}

@extends('layouts.portal')

@section('title', 'Notifications')

@section('content')

<div class="admission-page admission-page--notifications">

    <main class="admission-shell admission-shell--wide">

        {{-- ==========================================================
             PAGE HEADER
        =========================================================== --}}

        <header class="admission-page-header">

            <div class="admission-eyebrow">
                <span class="admission-eyebrow__dot" aria-hidden="true"></span>
                Notifications
            </div>

            <h1 class="admission-page-title">Notifications</h1>

            <p class="admission-page-description">
                Stay updated on your application progress and admission journey.
            </p>
        </header>

        @if($notifications->count())

            {{-- ======================================================
                 SUMMARY
            ======================================================= --}}
            <div class="notification-page-summary">
                <p class="notification-page-summary__text">
                    You have {{ $notifications->count() }}
                    notification{{ $notifications->count() === 1 ? '' : 's' }}
                    in your applicant portal.
                </p>
                <span class="notification-page-summary__count">
                    {{ $notifications->count() }}
                </span>
            </div>

            {{-- ======================================================
                 NOTIFICATION LIST
            ======================================================= --}}
            <section class="dashboard-notification-list" aria-label="Applicant notifications">
                @foreach($notifications as $notification)
                    @php
                        $data = $notification->data ?? [];
                    @endphp

                    <article class="dashboard-notification {{ $notification->read_at ? '' : 'dashboard-notification--unread' }}">
                        <div class="dashboard-notification__icon" aria-hidden="true">!</div>

                        <div class="dashboard-notification__content">
                            <strong>{{ $data['title'] ?? 'Notification' }}</strong>
                            <p>{{ $data['message'] ?? '' }}</p>
                            <small>{{ $notification->created_at->format('d M Y, H:i') }}</small>
                        </div>

                        @if(!empty($data['link']))
                            <div class="dashboard-notification__action">
                                <a href="{{ $data['link'] }}" class="admission-link">
                                    View <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        @endif
                    </article>
                @endforeach
            </section>

        @else
            {{-- ======================================================
                 EMPTY STATE
            ======================================================= --}}
            <section class="admission-empty-state" aria-labelledby="notifications-empty-title">
                <div class="admission-empty-state__icon" aria-hidden="true">!</div>
                <h2 id="notifications-empty-title">No notifications</h2>
                <p>
                    You don't have any notifications at the moment.
                    We'll let you know when there is an important update
                    about your admission journey.
                </p>
            </section>
        @endif

    </main>
</div>

@endsection
