<?php

/*
Component: ApplicationDocument Model
File Path: app/Models/ApplicationDocument.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Represents a document uploaded by a student for their application.
Includes metadata for officer review (verified/rejected).
Links each file to its parent application and its document type.
Automatically captures file metadata and enforces type/size validation.

Status: ✅ Production Ready
Version: 1.6 (fixed filename + size validation)
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ApplicationDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'document_type_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'status',
        'uploaded_at',
        'verified_at',
        'verified_by',
        'rejection_reason',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Relationship: Document belongs to an Application.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Relationship: Document belongs to a Document Type.
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(ApplicationDocumentType::class, 'document_type_id');
    }

    /**
     * Relationship: Officer who verified/rejected the document.
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Auto-populate metadata and enforce validation when a file is uploaded.
     */
    protected static function booted()
    {
        static::saving(function ($document) {
            if ($document->isDirty('path') && $document->path) {
                $disk = $document->disk ?? 'public';
                $fullPath = $document->path;

                if (Storage::disk($disk)->exists($fullPath)) {
                    // Preserve original_name if already set by controller
                    if (empty($document->original_name)) {
                        $document->original_name = basename($fullPath);
                    }

                    $document->mime_type = Storage::disk($disk)->mimeType($fullPath);
                    $document->size = Storage::disk($disk)->size($fullPath);
                    $document->uploaded_at = now();

                    // ✅ Validation against ApplicationDocumentType rules
                    $type = $document->documentType;
                    if ($type) {
                        // Check allowed file types
                        if (is_array($type->allowed_file_types) && !empty($type->allowed_file_types)) {
                            $extension = strtolower(pathinfo($document->original_name, PATHINFO_EXTENSION));
                            if (!in_array($extension, $type->allowed_file_types)) {
                                throw ValidationException::withMessages([
                                    'path' => "File type '{$extension}' is not allowed for {$type->name}.",
                                ]);
                            }
                        }

                        // Check max size (convert KB to bytes)
                        if ($type->max_size) {
                            $maxBytes = $type->max_size * 1024;
                            if ($document->size > $maxBytes) {
                                throw ValidationException::withMessages([
                                    'path' => "File exceeds maximum size of {$type->max_size} KB for {$type->name}.",
                                ]);
                            }
                        }
                    }
                }
            }
        });
    }
}
