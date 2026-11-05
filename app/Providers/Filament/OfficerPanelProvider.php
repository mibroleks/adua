<?php

/*
Component: Officer Panel Provider
File Path: app/Providers/Filament/OfficerPanelProvider.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Configures the Filament Officer Panel.
Integrates ThemeService so the Officer Panel uses the same preset-driven theme
as the Student Portal (colors, logo, favicon, identity).

Institutional branding:
- Displays the institution logo and name together.
- Uses ThemeService for dynamic institution identity.
- Applies the same identity to the panel branding and login branding.
- Does not hardcode any institution name or logo.

Authentication:
- Registers the custom OfficerLogin page.
- Retains Filament's native authentication workflow.
- Preserves existing authentication middleware.

Dashboard:
- Registers the custom AdmissionDashboard as the Officer Panel dashboard.
- Preserves existing Officer resources and widgets.
- Does not modify the Student Admission Portal dashboard.
- Does not modify the Applications Register.

Registers the ThemePreviewWidget for live theme verification.

Status: Production Implementation
Version: 3.6 (custom officer admissions dashboard registration,
combined institutional logo and name branding,
preset + mode aware, runtime CSS token loading,
theme preview widget, hardened middleware)
Filament: 5.x
*/

namespace App\Providers\Filament;

use App\Filament\Pages\AdmissionDashboard;
use App\Filament\Pages\Auth\OfficerLogin;
use App\Filament\Widgets\ThemePreviewWidget;
use App\Services\ThemeService;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Support\HtmlString;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class OfficerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        /*
         * ---------------------------------------------------------
         * Institution Theme
         * ---------------------------------------------------------
         *
         * Resolve institutional appearance and identity through
         * the central ThemeService.
         */

        $theme = app(ThemeService::class);

        $tokens = $theme->tokens();

        return $panel
            ->default()
            ->id('officer')
            ->path('officer')

            // Compile the Officer Panel's static CSS through Vite.
            ->viteTheme('resources/css/filament/officer/theme.css')

            // Load institution-specific theme variables at runtime.
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(
                    '<link rel="stylesheet" href="' . e(route('theme.css')) . '">'
                )
            )

            /*
             * -----------------------------------------------------
             * Authentication
             * -----------------------------------------------------
             *
             * Register the custom Officer Login page.
             *
             * OfficerLogin extends Filament's native Login page,
             * preserving its authentication functionality while
             * allowing a completely custom visual presentation.
             */

            ->login(OfficerLogin::class)

            /*
             * -----------------------------------------------------
             * Institutional Branding
             * -----------------------------------------------------
             *
             * Resolve institutional identity through ThemeService.
             *
             * The logo and institution name are combined into one
             * branding element because Filament may prioritize
             * brandLogo over brandName.
             *
             * Branding is constructed inside the closure so the
             * identity values are resolved when rendered.
             */

            ->brandName(
                fn (): string => $theme->institutionName()
                    ?: config('app.name', 'Institutional Management Portal')
            )

            ->brandLogo(
                function () use ($theme): HtmlString {

                    $institutionName = $theme->institutionName()
                        ?: config('app.name', 'Institutional Management Portal');

                    $logoUrl = $theme->logoUrl();

                    $logo = filled($logoUrl)
                        ? '<img
                                src="' . e($logoUrl) . '"
                                alt="' . e($institutionName) . '"
                                style="
                                    display: block;
                                    flex: 0 0 auto;
                                    width: auto;
                                    height: 2.35rem;
                                    max-width: 3.5rem;
                                    object-fit: contain;
                                "
                            >'
                        : '';

                    return new HtmlString(
                        '<span
                            class="officer-panel-brand"
                            style="
                                display: inline-flex;
                                align-items: center;
                                gap: 0.75rem;
                                max-width: 100%;
                                min-width: 0;
                                white-space: normal;
                            "
                            aria-label="' . e($institutionName) . '"
                        >'
                            . $logo .
                            '<span
                                class="officer-panel-brand__name"
                                style="
                                    display: inline-block;
                                    min-width: 0;
                                    max-width: 24rem;
                                    color: inherit;
                                    font-size: 0.95rem;
                                    font-weight: 750;
                                    line-height: 1.3;
                                    letter-spacing: -0.025em;
                                    overflow-wrap: anywhere;
                                "
                            >'
                                . e($institutionName) .
                            '</span>
                        </span>'
                    );
                }
            )

            ->favicon(
                fn () => $theme->faviconUrl()
            )

            /*
             * -----------------------------------------------------
             * Institutional Theme Colours
             * -----------------------------------------------------
             */

            ->colors([
                'primary'    => Color::hex($tokens['primary'] ?? '#73152A'),
                'secondary'  => Color::hex($tokens['secondary'] ?? '#30252A'),
                'accent'     => Color::hex($tokens['accent'] ?? '#B89245'),
                'background' => Color::hex($tokens['page'] ?? $tokens['background'] ?? '#F7F4EE'),
                'surface'    => Color::hex($tokens['surface'] ?? '#FFFDFC'),
                'text'       => Color::hex($tokens['body'] ?? $tokens['text'] ?? '#3C3437'),
            ])

            /*
             * -----------------------------------------------------
             * Resource Discovery
             * -----------------------------------------------------
             */

            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )

            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages'
            )

            /*
             * -----------------------------------------------------
             * Officer Dashboard
             * -----------------------------------------------------
             *
             * Register the custom Admissions Dashboard as the
             * default landing page for the Officer Panel.
             *
             * Existing resources and the Student Portal dashboard
             * remain unchanged.
             */

            ->pages([
                AdmissionDashboard::class,
            ])

            /*
             * -----------------------------------------------------
             * Widget Discovery
             * -----------------------------------------------------
             */

            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets'
            )

            ->widgets([
                AccountWidget::class,
                ThemePreviewWidget::class,
            ])

            /*
             * -----------------------------------------------------
             * Panel Middleware
             * -----------------------------------------------------
             */

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])

            ->authMiddleware([
                Authenticate::class,

                // Add officer-only guard/role check here when implemented.
            ]);
    }
}
