<?php

namespace App\Models\HCGA\Training;

use Illuminate\Database\Eloquent\Model;

class HcgaTrainingFeedback extends Model
{
    protected $table = 'hcga_training_feedbacks';

    protected $fillable = [
        'training_id',
        'participant_id',
        'feedback_type',
        'aspect_name',
        'rating',
        'comments',
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
