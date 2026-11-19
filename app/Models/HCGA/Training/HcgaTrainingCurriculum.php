<?php

namespace App\Models\HCGA\Training;

use Illuminate\Database\Eloquent\Model;

class HcgaTrainingCurriculum extends Model
{
    protected $table = 'hcga_training_curriculums';

    protected $fillable = [
        'training_id',
        'session_order',
        'topic_title',
        'speaker_name',
        'duration_minutes',
        'learning_method',
        'material_file_path',
        'notes',
    ];

    public function training()
    {
        return $this->belongsTo(HcgaTraining::class, 'training_id', 'ID');
    }
}
