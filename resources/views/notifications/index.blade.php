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
| Supports individual and bulk notification read actions.
|
| Status: 🚦 Hardened
| Version: 2.2 (individual and bulk read management)
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

        {{-- ==========================================================
             FLASH STATUS
        =========================================================== --}}

        @if(session('status'))
            <div
                class="notification-page-status"
                role="status"
                aria-live="polite"
            >
                {{ session('status') }}
            </div>
        @endif

        @if($notifications->count())

            {{-- ======================================================
                 SUMMARY
            ======================================================= --}}

            <div class="notification-page-summary">

                <p class="notification-page-summary__text">
                    You have {{ $notifications->count() }}
                    notification{{ $notifications->count() === 1 ? '' : 's' }}
                    in your applicant portal.

                    @if(($unreadNotificationCount ?? 0) > 0)
                        <span>
                            {{ $unreadNotificationCount }}
                            unread.
                        </span>
                    @else
                        <span>
                            All notifications have been read.
                        </span>
                    @endif
                </p>

                <span class="notification-page-summary__count">
                    {{ $notifications->count() }}
                </span>

            </div>

            {{-- ======================================================
                 BULK READ ACTION
            ======================================================= --}}

            @if(($unreadNotificationCount ?? 0) > 0)

                <div class="notification-page-toolbar">

                    <form
                        method="POST"
                        action="{{ route('notifications.readAll') }}"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="admission-button admission-button--secondary"
                        >
                            Mark all as read
                        </button>
                    </form>

                </div>

            @endif

            {{-- ======================================================
                 NOTIFICATION LIST
            ======================================================= --}}

            <section
                class="dashboard-notification-list"
                aria-label="Applicant notifications"
            >

                @foreach($notifications as $notification)

                    @php
                        $data = is_array($notification->data)
                            ? $notification->data
                            : [];

                        $isUnread = is_null($notification->read_at);
                    @endphp

                    <article
                        class="dashboard-notification {{ $isUnread ? 'dashboard-notification--unread' : '' }}"
                    >

                        <div
                            class="dashboard-notification__icon"
                            aria-hidden="true"
                        >
                            !
                        </div>

                        <div class="dashboard-notification__content">

                            <strong>
                                {{ $data['title'] ?? 'Notification' }}
                            </strong>

                            <p>
                                {{ $data['message'] ?? '' }}
                            </p>

                            <small>
                                {{ $notification->created_at->format('d M Y, H:i') }}
                            </small>

                            @if($isUnread)
                                <small class="notification-page-read-state">
                                    Unread
                                </small>
                            @else
                                <small class="notification-page-read-state">
                                    Read
                                    @if($notification->read_at)
                                        · {{ $notification->read_at->format('d M Y, H:i') }}
                                    @endif
                                </small>
                            @endif

                        </div>

                        <div class="dashboard-notification__action">

                            @if(!empty($data['link']))
                                <a
                                    href="{{ $data['link'] }}"
                                    class="admission-link"
                                >
                                    View
                                    <span aria-hidden="true">→</span>
                                </a>
                            @endif

                            @if($isUnread)

                                <form
                                    method="POST"
                                    action="{{ route('notifications.read', $notification->id) }}"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="admission-link notification-page-mark-read"
                                    >
                                        Mark as read
                                    </button>
                                </form>

                            @endif

                        </div>

                    </article>

                @endforeach

            </section>

        @else

            {{-- ======================================================
                 EMPTY STATE
            ======================================================= --}}

            <section
                class="admission-empty-state"
                aria-labelledby="notifications-empty-title"
            >

                <div
                    class="admission-empty-state__icon"
                    aria-hidden="true"
                >
                    !
                </div>

                <h2 id="notifications-empty-title">
                    No notifications
                </h2>

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
