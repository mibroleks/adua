<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'admission_stage_id',
        'status',
        'started_at',
        'completed_at',
    ];

    /**
     * Link to the application.
     */
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Link to the admission stage definition.
     */
    public function admissionStage()
    {
        return $this->belongsTo(AdmissionStage::class);
    }
}
