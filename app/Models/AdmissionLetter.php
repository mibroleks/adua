<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdmissionLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'admission_decision_id',
        'letter_number',
        'type',
        'issued_by',
        'issued_at',
        'published_at',
        'file_path',
    ];

    /**
     * Link to the application.
     */
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Link to the admission decision.
     */
    public function decision()
    {
        return $this->belongsTo(AdmissionDecision::class, 'admission_decision_id');
    }

    /**
     * Officer who issued the letter.
     */
    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
