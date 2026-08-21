{{--
|--------------------------------------------------------------------------
| Component: Portal Student Footer
|--------------------------------------------------------------------------
| File Path: resources/views/layouts/portal-student-footer.blade.php
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Lightweight footer for authenticated student portal pages.
| Distinct from the public institutional footer.
|--------------------------------------------------------------------------
--}}

@php
    $theme = app(\App\Services\ThemeService::class);
    $institutionName = $theme->institutionName() ?? 'University';
@endphp

<footer class="portal-student-footer">
    <div class="portal-student-footer__container">

        {{-- Copyright --}}
        <div class="portal-student-footer__copy">
            © {{ date('Y') }} {{ $institutionName }} — Student Portal
        </div>

        {{-- Quick Links --}}
        <nav class="portal-student-footer__nav" aria-label="Portal footer navigation">
            <a href="{{ route('support.index') }}">Support</a>
            <a href="{{ route('notifications.index') }}">Notifications</a>
            <a href="{{ route('profile') }}">Profile</a>
        </nav>

    </div>
</footer>
