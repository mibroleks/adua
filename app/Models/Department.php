<?php

/*
Component: Department Model
File Path: app/Models/Department.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Represents academic departments in the university.
Each department belongs to a faculty and contains programmes.
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'faculty_id',
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
     * Relationship: Department belongs to a Faculty.
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Relationship: Department has many Programmes.
     */
    public function programmes(): HasMany
    {
        return $this->hasMany(Programme::class);
    }
}
