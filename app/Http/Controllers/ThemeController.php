<?php

/*
|--------------------------------------------------------------------------
| Component: Theme Controller
|--------------------------------------------------------------------------
| File Path: app/Http/Controllers/ThemeController.php
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Serves dynamic CSS variables for the student portal.
| Consumes ThemeService to output brand + component tokens as :root variables.
| Allows officers to change branding via SettingResource without code edits.
|
| Status: ✅ Production Ready
| Version: 2.0 (v5 compatible, caching headers, hardened response)
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Services\ThemeService;
use Illuminate\Http\Response;

class ThemeController extends Controller
{
    /**
     * Serve dynamic CSS variables for the portal.
     *
     * @param ThemeService $theme
     * @return Response
     */
    public function css(ThemeService $theme): Response
    {
        $css = $theme->toCssVariables();

        return response($css, 200)
            ->header('Content-Type', 'text/css')
            ->header('Cache-Control', 'public, max-age=300') // cache for 5 minutes
            ->header('X-Theme-Preset', $theme->preset())
            ->header('X-Theme-Mode', $theme->mode());
    }
}
