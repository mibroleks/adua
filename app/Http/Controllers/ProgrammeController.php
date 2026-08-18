<?php

namespace App\Http\Controllers;

use App\Models\Programme;
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
        // Paginate programmes with faculty relationship
        $programmes = Programme::with('faculty')
            ->orderBy('name')
            ->paginate(9);

        return view('programmes.index', compact('programmes'));
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
            ->where('faculty_id', $programme->faculty_id)
            ->where('id', '!=', $programme->id)
            ->limit(3)
            ->get();

        // Return the programme detail view
        return view('programme_detail', compact('programme', 'relatedProgrammes'));
    }
}
