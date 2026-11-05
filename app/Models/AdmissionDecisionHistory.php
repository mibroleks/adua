<?php

/*
Component: AdmissionDecisionHistory Model
File Path: app/Models/AdmissionDecisionHistory.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Records the history of institutional admission outcome changes.

Responsibilities:
- Preserve previous admission status.
- Record the new admission status.
- Identify the officer responsible for the change.
- Preserve remarks explaining the decision.
- Record when the change occurred.

Architecture:
- History records belong to an AdmissionDecision.
- History records are not directly edited through ordinary CRUD.
- Every admission outcome transition should be recorded
  through AdmissionService.

Version: 1.0
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionDecisionHistory extends Model
{
    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'admission_decision_id',
        'old_status',
        'new_status',
        'officer_id',
        'remarks',
        'changed_at',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'changed_at' => 'datetime',
    ];

    /**
     * History belongs to an admission decision.
     */
    public function admissionDecision(): BelongsTo
    {
        return $this->belongsTo(
            AdmissionDecision::class,
            'admission_decision_id'
        );
    }

    /**
     * Officer who made the admission outcome change.
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }
}
