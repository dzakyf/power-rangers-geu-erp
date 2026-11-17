<?php

namespace App\Models\HCGA\Training;

use Illuminate\Database\Eloquent\Model;

class HcgaTrainingCertificate extends Model
{
    protected $table = 'hcga_training_certificates';

    protected $fillable = [
        'training_id',
        'participant_id',
        'certificate_no',
        'verification_token',
        'issued_date',
        'file_path',
        'issued_by',
    ];

    protected $casts = [
        'issued_date' => 'date',
    ];

    public function training()
    {
        return $this->belongsTo(HcgaTraining::class, 'training_id', 'ID');
    }

    public function participant()
    {
        return $this->belongsTo(HcgaTrainingParticipant::class, 'participant_id');
    }
}
