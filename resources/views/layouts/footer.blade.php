{{--
|--------------------------------------------------------------------------
| Component: Application Footer
|--------------------------------------------------------------------------
| File Path: resources/views/layouts/footer.blade.php
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Version: 3.0
| Design: Premium Institutional Shell
| Theme: Driven entirely by ThemeService tokens
|--------------------------------------------------------------------------
--}}

@php
    $theme = app(\App\Services\ThemeService::class);

    $institutionName = $theme->institutionName() ?? 'University';
    $logoUrl = $theme->logoUrl();
@endphp

<footer class="portal-footer">

    {{-- ============================================================
         MAIN FOOTER
    ============================================================= --}}
    <div class="portal-footer__main">

        <div class="portal-footer__grid">

            {{-- ====================================================
                 BRAND
            ===================================================== --}}
            <div class="portal-footer__brand">

                <a href="{{ url('/') }}" class="portal-footer__brand-link">
                    @if($logoUrl)
                        <img
                            src="{{ $logoUrl }}"
                            alt="{{ $institutionName }}"
                            class="portal-footer__logo"
                        >
                    @endif

                    <span class="portal-footer__identity">
                        <span class="portal-footer__institution">
                            {{ $institutionName }}
                        </span>
                        <span class="portal-footer__department">
                            Admissions Office
                        </span>
                    </span>
                </a>

                <div class="portal-footer__rule"></div>

                <p class="portal-footer__description">
                    Begin your journey toward a rigorous, transformative
                    university education. Explore our programmes and submit
                    your application with confidence.
                </p>
            </div>

            {{-- ====================================================
                 EXPLORE
            ===================================================== --}}
            <div class="portal-footer__column">
                <h3 class="portal-footer__heading">Explore</h3>

                <nav class="portal-footer__links" aria-label="Footer navigation">
                    <a href="{{ url('/') }}" class="portal-footer__link">Home</a>
                    <a href="{{ route('programmes.index') }}" class="portal-footer__link">Programmes</a>
                    <a href="{{ route('application.create') }}" class="portal-footer__link">Apply</a>
                    <a href="{{ route('login') }}" class="portal-footer__link">Applicant Login</a>
                </nav>
            </div>

            {{-- ====================================================
                 ADMISSIONS / CONTACT
            ===================================================== --}}
            <div class="portal-footer__column">
                <h3 class="portal-footer__heading">Admissions</h3>

                <div class="portal-footer__contact">
                    <p class="portal-footer__contact-item">
                        {{ setting('institution.address') ?? 'University Campus' }}
                    </p>

                    @if(setting('institution.email'))
                        <p class="portal-footer__contact-item">
                            <a href="mailto:{{ setting('institution.email') }}" class="portal-footer__contact-link">
                                {{ setting('institution.email') }}
                            </a>
                        </p>
                    @endif

                    @if(setting('institution.phone'))
                        <p class="portal-footer__contact-item">
                            <a href="tel:{{ setting('institution.phone') }}" class="portal-footer__contact-link">
                                {{ setting('institution.phone') }}
                            </a>
                        </p>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- ============================================================
         LEGAL / COPYRIGHT BAR
    ============================================================= --}}
    <div class="portal-footer__bottom">
        <p class="portal-footer__copyright">
            © {{ date('Y') }} {{ $institutionName }}. All rights reserved.
        </p>
        <p class="portal-footer__credit">
            Powered by <strong>Ygrace Tech</strong>
        </p>
    </div>

</footer>
