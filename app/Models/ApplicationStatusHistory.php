<?php

/**
 * Component: Application Status History Model
 * File Path: app/Models/ApplicationStatusHistory.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Tracks every status change for an application.
 * Stores old/new status, officer who made the change, remarks, and timestamp.
 * Provides relationships to the application and officer for audit trail rendering.
 *
 * Status: ✅ Production Ready
 * Version: 1.2 (added remarks field)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStatusHistory extends Model
{
    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'application_id',
        'old_status',
        'new_status',
        'changed_by',
        'changed_at',
        'remarks',   // ✅ Added remarks for audit explanations
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'changed_at' => 'datetime',
    ];

    /**
     * Relationship: Status history belongs to an Application.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Relationship: Status history belongs to a User (officer).
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
