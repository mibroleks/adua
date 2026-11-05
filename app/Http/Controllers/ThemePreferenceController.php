<?php

/*
|--------------------------------------------------------------------------
| Component: Theme Preference Controller
|--------------------------------------------------------------------------
| File Path: app/Http/Controllers/ThemePreferenceController.php
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Updates an authenticated applicant's personal portal theme preference.
|
| Rules:
| - Only authenticated students can update this preference.
| - Only light and dark are accepted.
| - Does not modify institutional appearance settings.
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ThemePreferenceController extends Controller
{
    /**
     * Update the authenticated student's personal theme mode.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user && $user->isStudent(), 403);

        $validated = $request->validate([
            'mode' => [
                'required',
                'string',
                Rule::in(['light', 'dark']),
            ],
        ]);

        $user->forceFill([
            'theme_mode' => $validated['mode'],
        ])->save();

        return response()->json([
            'success' => true,
            'mode' => $user->fresh()->preferredThemeMode(),
            'message' => 'Your appearance preference has been saved.',
        ]);
    }
}