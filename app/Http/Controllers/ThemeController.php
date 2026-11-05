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
| Serves dynamic institutional and personal portal theme CSS variables.
|
| Status: Production Ready
| Version: 2.1 (user-aware theme resolution and private caching)
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Services\ThemeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ThemeController extends Controller
{
    /**
     * Serve dynamic CSS variables.
     */
    public function css(Request $request, ThemeService $theme): Response
    {
        $user = $request->user();

        $mode = $theme->portalMode($user);

        $css = $theme->toCssVariables($mode);

        $response = response($css, 200)
            ->header('Content-Type', 'text/css; charset=UTF-8')
            ->header('X-Theme-Preset', $theme->preset())
            ->header('X-Theme-Mode', $mode)
            ->header('Vary', 'Cookie');

        if ($user) {
            return $response->header(
                'Cache-Control',
                'private, no-store, no-cache, must-revalidate'
            );
        }

        return $response->header(
            'Cache-Control',
            'public, max-age=300'
        );
    }
}