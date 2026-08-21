<?php

/*
Component: Programme Service
File Path: app/Services/ProgrammeService.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides reusable methods for fetching programmes.
Supports filtering by faculty, department, active status,
and admissions availability. Used by controllers and Filament.
*/

namespace App\Services;

use App\Models\Programme;
use App\Models\Faculty;
use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

class ProgrammeService
{
    protected EffectiveFeeResolver $feeResolver;

    public function __construct(EffectiveFeeResolver $feeResolver)
    {
        $this->feeResolver = $feeResolver;
    }

    /**
     * Get all active programmes.
     */
    public function getActiveProgrammes(): Collection
    {
        return Programme::where('active', true)->get();
    }

    /**
     * Get programmes available for application (active + application_enabled).
     */
    public function getAvailableProgrammes(): Collection
    {
        return Programme::where('active', true)
                        ->where('application_enabled', true)
                        ->get();
    }

    /**
     * Get programmes by faculty.
     */
    public function getProgrammesByFaculty(int $facultyId): Collection
    {
        return Programme::whereHas('department', function ($query) use ($facultyId) {
            $query->where('faculty_id', $facultyId);
        })->where('active', true)->get();
    }

    /**
     * Get programmes by department.
     */
    public function getProgrammesByDepartment(int $departmentId): Collection
    {
        return Programme::where('department_id', $departmentId)
                        ->where('active', true)
                        ->get();
    }

    /**
     * Get a single programme by code.
     */
    public function getProgrammeByCode(string $code): ?Programme
    {
        return Programme::where('code', $code)->first();
    }

    /**
     * Get programme fee snapshot by programme ID.
     * Uses EffectiveFeeResolver to ensure programme override > global default.
     */
    public function getProgrammeFee(int $programmeId): ?int
    {
        $programme = Programme::find($programmeId);
        return $programme ? $this->feeResolver->resolve($programme) : null;
    }

    /**
     * Get programme fee in naira.
     */
    public function getProgrammeFeeInNaira(int $programmeId): ?float
    {
        $programme = Programme::find($programmeId);
        return $programme ? $this->feeResolver->resolveInNaira($programme) : null;
    }

    /**
     * Get programme fee formatted for display.
     */
    public function getProgrammeFeeFormatted(int $programmeId): string
    {
        $programme = Programme::find($programmeId);
        return $programme ? $this->feeResolver->resolveFormatted($programme) : '—';
    }
}
