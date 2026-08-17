<?php

/*
Component: ApplicationFieldValue Model
File Path: app/Models/ApplicationFieldValue.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Stores student responses to dynamic form fields.
Links each application to its field values.
Provides relationship to the FormField definition for labels.

Status: ✅ Production Ready
Version: 1.1 (renamed relationship to formField + type hints)
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'form_field_id',
        'value',
    ];

    /**
     * Relationship: belongs to Application.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Relationship: belongs to FormField.
     * Renamed to formField for clarity in dossier views/exports.
     */
    public function formField(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'form_field_id');
    }
}
