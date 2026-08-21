<?php

namespace App\Http\Controllers;

use App\Models\Programme;
use App\Models\Faculty;
use Illuminate\Http\Request;

class ProgrammeController extends Controller
{
    /**
     * Display a listing of programmes.
     *
     * Route: programmes.index
     */
    public function index()
    {
        // Grouped by faculty for catalogue
        $faculties = Faculty::with(['programmes' => function ($query) {
            // Qualify columns to avoid ambiguity
            $query->where('programmes.active', true)
                  ->orderBy('programmes.name');
        }])->orderBy('name')->get();

        // Flat list of programmes (for legacy use or other components)
        $programmes = Programme::with('faculty')
            ->where('programmes.active', true)
            ->orderBy('programmes.name')
            ->get();

        return view('programmes.index', compact('faculties', 'programmes'));
    }

    /**
     * Display the specified programme.
     *
     * Route: programmes.show
     */
    public function show(Programme $programme)
    {
        // Load faculty relation for display
        $programme->load('faculty');

        // Fetch related programmes from the same faculty
        $relatedProgrammes = Programme::with('faculty')
            ->whereHas('department', function ($query) use ($programme) {
                $query->where('faculty_id', $programme->department->faculty_id);
            })
            ->where('id', '!=', $programme->id)
            ->limit(3)
            ->get();

        // Return the programme detail view
        return view('programme_detail', compact('programme', 'relatedProgrammes'));
    }
}
