{{-- 
Component: Admissions Portal Disclaimer
File Path: resources/views/components/portal-disclaimer.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Reusable official admissions notice shown across the public admissions portal.

Architecture:
- Content is intentionally local to this component for Phase 1.
- Content is isolated so it can later be supplied by SettingsService
  or an administrative configuration without changing the component.
- Uses the existing dynamic ThemeService branding.
- Uses the institution logo where available.
- Uses localStorage to remember applicant acknowledgement.
- Designed for reuse across all public admissions pages.

Future Configuration:
The disclaimer content, visibility, acknowledgement behavior, and repeat
period can later be supplied through portal settings/admin configuration.

Status: ✅ Phase 1 Production Ready
Version: 1.1 (Institutional Visual Refinement)
--}}

@php
    /*
    |--------------------------------------------------------------------------
    | Theme / Institutional Identity
    |--------------------------------------------------------------------------
    */

    $theme = app(\App\Services\ThemeService::class);

    $institutionName =
        $theme->institutionName()
        ?? setting('institution.name')
        ?? 'University';

    $logoUrl =
        $theme->logoUrl()
        ?? setting('institution.logo')
        ?? asset('images/default-logo.png');


    /*
    |--------------------------------------------------------------------------
    | Phase 1 Disclaimer Content
    |--------------------------------------------------------------------------
    |
    | Centralized here for now.
    | Later these values can come from SettingsService/database configuration.
    |
    */

    $disclaimerTitle = 'Important Admissions Notice';

    $disclaimerMessage = [
        'Applicants are advised to use only this official admissions portal for applications and to rely only on official communications from ' . $institutionName . '.',

        $institutionName . ' does not authorize third parties to collect application fees, offer admission on its behalf, or request payment into personal bank accounts.',

        'Applicants should carefully verify any admission-related communication before making payments or providing personal information.',
    ];

    $disclaimerAcknowledgement =
        'By continuing, you acknowledge that you have read and understood this notice.';

    $disclaimerButton = 'I Understand & Continue';


    /*
    |--------------------------------------------------------------------------
    | Future Configuration Hook
    |--------------------------------------------------------------------------
    |
    | Keep these values centralized so they can later be replaced by
    | portal settings without changing the component structure.
    |
    */

    $disclaimerEnabled = true;

    $disclaimerStorageKey =
        'admissions_portal_disclaimer_acknowledged';
@endphp

<div
    id="portal-disclaimer"
    class="portal-disclaimer"
    data-disclaimer
    data-storage-key="{{ $disclaimerStorageKey }}"
    data-enabled="{{ $disclaimerEnabled ? 'true' : 'false' }}"
    role="dialog"
    aria-modal="true"
    aria-labelledby="portal-disclaimer-title"
    aria-describedby="portal-disclaimer-description"
    hidden
>
    {{-- 
    |--------------------------------------------------------------------------
    | Backdrop
    |--------------------------------------------------------------------------
    --}}

    <div
        class="portal-disclaimer__backdrop"
        data-disclaimer-backdrop
        aria-hidden="true"
    ></div>


    {{-- 
    |--------------------------------------------------------------------------
    | Dialog
    |--------------------------------------------------------------------------
    --}}

    <div
        class="portal-disclaimer__dialog"
        data-disclaimer-dialog
        tabindex="-1"
    >

        {{-- Institutional Identity --}}
        <div class="portal-disclaimer__identity">

            <div class="portal-disclaimer__logo-wrap">
                <img
                    src="{{ $logoUrl }}"
                    alt="{{ $institutionName }}"
                    class="portal-disclaimer__logo"
                >
            </div>

            <div class="portal-disclaimer__identity-text">
                <span class="portal-disclaimer__institution">
                    {{ $institutionName }}
                </span>

                <span class="portal-disclaimer__portal-label">
                    Official Admissions Portal
                </span>
            </div>

        </div>


        {{-- Notice Header --}}
        <div class="portal-disclaimer__header">

            <div class="portal-disclaimer__notice-mark" aria-hidden="true">

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="M12 3L21 7.5V11.5C21 16.75 17.35 20.35 12 21C6.65 20.35 3 16.75 3 11.5V7.5L12 3Z"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linejoin="round"
                    />

                    <path
                        d="M12 8V12.5"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                    />

                    <circle
                        cx="12"
                        cy="16"
                        r="0.8"
                        fill="currentColor"
                    />
                </svg>

            </div>

            <div class="portal-disclaimer__heading">

                <span class="portal-disclaimer__eyebrow">
                    Please Read Carefully
                </span>

                <h2
                    id="portal-disclaimer-title"
                    class="portal-disclaimer__title"
                >
                    {{ $disclaimerTitle }}
                </h2>

            </div>

        </div>


        {{-- Notice Content --}}
        <div
            id="portal-disclaimer-description"
            class="portal-disclaimer__content"
        >

            @foreach($disclaimerMessage as $paragraph)
                <p>
                    {{ $paragraph }}
                </p>
            @endforeach


            <div class="portal-disclaimer__acknowledgement">

                <div
                    class="portal-disclaimer__acknowledgement-icon"
                    aria-hidden="true"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M20 6L9 17L4 12"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>

                <p>
                    {{ $disclaimerAcknowledgement }}
                </p>

            </div>

        </div>


        {{-- Notice Footer --}}
        <div class="portal-disclaimer__footer">

            <div class="portal-disclaimer__security-note">

                <span
                    class="portal-disclaimer__security-icon"
                    aria-hidden="true"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M7 10V8C7 5.24 9.24 3 12 3C14.76 3 17 5.24 17 8V10"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                        />

                        <rect
                            x="5"
                            y="10"
                            width="14"
                            height="11"
                            rx="2"
                            stroke="currentColor"
                            stroke-width="1.7"
                        />

                        <circle
                            cx="12"
                            cy="15.5"
                            r="1"
                            fill="currentColor"
                        />
                    </svg>
                </span>

                <span>
                    Official portal • Secure application process
                </span>

            </div>


            <button
                type="button"
                class="portal-disclaimer__button"
                data-disclaimer-continue
            >
                <span>
                    {{ $disclaimerButton }}
                </span>

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <path
                        d="M5 12H19"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />

                    <path
                        d="M13 6L19 12L13 18"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </button>

        </div>

    </div>
</div>