<?php

/*
Component: Programme Model
File Path: app/Models/Programme.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Represents academic programmes available for admission.
Each programme belongs to a department (and indirectly a faculty).
Managed by officers via Filament.
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Programme extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'department_id',
        'name',
        'code',
        'description',
        'duration',
        'degree_type',
        'application_fee',
        'tuition',
        'credits',
        'delivery_mode',
        'language',
        'requirements',
        'career_paths',
        'outcomes',
        'scholarships',
        'accreditation_body',
        'active',
        'application_enabled',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'application_fee'    => 'decimal:2',   // stored in naira
        'tuition'            => 'decimal:2',   // stored in naira
        'active'             => 'boolean',
        'application_enabled'=> 'boolean',
        // If migrations use JSON columns, cast to array:
        // 'requirements' => 'array',
        // 'career_paths' => 'array',
        // 'outcomes'     => 'array',
        // 'scholarships' => 'array',
    ];

    /**
     * Relationship: Programme has many Applications.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Relationship: Programme belongs to a Department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relationship: Programme belongs to a Faculty (via Department).
     */
    public function faculty(): HasOneThrough
    {
        return $this->hasOneThrough(
            Faculty::class,
            Department::class,
            'id',            // Foreign key on departments table
            'id',            // Foreign key on faculties table
            'department_id', // Local key on programmes table
            'faculty_id'     // Local key on departments table
        );
    }
}
