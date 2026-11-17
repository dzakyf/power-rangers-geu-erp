<?php

namespace App\Models\HCGA\Training;

use Illuminate\Database\Eloquent\Model;

class HcgaTrainingCoaching extends Model
{
    protected $table = 'hcga_training_coachings';

    protected $fillable = [
        'training_id',
        'participant_id',
        'mentor_username',
        'mentor_name',
        'coaching_date',
        'notes',
        'action_plan',
        'follow_up_type',
        'refreshment_training_id',
        'status',
        'created_by',
    ];

    protected $casts = [
        'coaching_date' => 'date',
    ];

    public function training()
    {
        return $this->belongsTo(HcgaTraining::class, 'training_id', 'ID');
    }

    public function participant()
    {
        return $this->belongsTo(HcgaTrainingParticipant::class, 'participant_id');
    }

    public function refreshmentTraining()
    {
        return $this->belongsTo(HcgaTraining::class, 'refreshment_training_id', 'ID');
    }
}
