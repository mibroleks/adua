<?php

/*
Component: Faculty Model
File Path: app/Models/Faculty.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Represents academic faculties in the university.
Each faculty groups multiple departments.
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
