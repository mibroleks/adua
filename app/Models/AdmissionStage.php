<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdmissionStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'order',
        'active',
    ];

    /**
     * Each stage can be linked to multiple application stages.
     */
    public function applicationStages()
    {
        return $this->hasMany(ApplicationStage::class);
    }
}
