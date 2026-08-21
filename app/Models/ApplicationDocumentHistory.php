<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDocumentHistory extends Model
{
    protected $fillable = [
        'application_document_id',
        'application_id',
        'action',
        'old_status',
        'new_status',
        'performed_by',
        'remarks',
        'performed_at',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];

    /**
     * Link back to the document this history belongs to.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(ApplicationDocument::class, 'application_document_id');
    }

    /**
     * Link back to the application.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Link to the user (officer or student) who performed the action.
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
