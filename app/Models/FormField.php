<?php

/*
Component: FormField Model
File Path: app/Models/FormField.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Represents dynamic application form fields defined by officers.
Supports label, type, validation, ordering, and programme linkage.

Status: ✅ Production Ready
Version: 1.1
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'programme_id',
        'label',
        'key',
        'type',
        'options',
        'section',
        'required',
        'validation_rules',
        'placeholder',
        'help_text',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * Relationship: FormField belongs to a Programme (nullable = global field).
     */
    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }

    /**
     * Relationship: FormField has many ApplicationFieldValues.
     */
    public function values()
    {
        return $this->hasMany(ApplicationFieldValue::class, 'form_field_id');
    }
}
