<?php

/*
Component: ApplicationDocumentType Model
File Path: app/Models/ApplicationDocumentType.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Represents the types of documents required for an application.
Supports name, key, required flag, allowed file types, maximum size, active status,
and programme linkage (nullable for global requirements).

Status: ✅ Production Ready
Version: 1.2 (clarified casts + size units)
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationDocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'programme_id',
        'name',
        'key',
        'required',
        'allowed_file_types',
        'max_size',   // stored in KB
        'active',
    ];

    protected $casts = [
        'required' => 'boolean',
        'active' => 'boolean',
        'allowed_file_types' => 'array', // requires JSON column in migration
    ];

    /**
     * Relationship: DocumentType belongs to a Programme (nullable = global requirement).
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    /**
     * Relationship: DocumentType has many ApplicationDocuments.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class, 'document_type_id');
    }

    /**
     * Helper: Get maximum size in bytes.
     */
    public function getMaxSizeInBytes(): ?int
    {
        return $this->max_size ? $this->max_size * 1024 : null;
    }
}
