<?php

/*
Component: Faculty Model
File Path: app/Models/Faculty.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Represents academic faculties in the university.
Each faculty groups multiple departments and programmes.
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Faculty extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'active',
        'sort_order',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Relationship: Faculty has many Departments.
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Relationship: Faculty has many Programmes through Departments.
     */
    public function programmes(): HasManyThrough
    {
        return $this->hasManyThrough(
            Programme::class,   // Final model
            Department::class,  // Intermediate model
            'faculty_id',       // Foreign key on departments table
            'department_id',    // Foreign key on programmes table
            'id',               // Local key on faculties table
            'id'                // Local key on departments table
        );
    }
}
