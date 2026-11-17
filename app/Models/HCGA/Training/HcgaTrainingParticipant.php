<?php

namespace App\Models\HCGA\Training;

use Illuminate\Database\Eloquent\Model;

class HcgaTrainingParticipant extends Model
{
    protected $table = 'hcga_training_participants';

    protected $fillable = [
        'training_id',
        'tna_item_id',
        'employee_username',
        'employee_name',
        'department',
        'attendance_status',
        'pre_test_score',
        'post_test_score',
        'practice_score',
        'final_score',
        'is_passed',
        'evaluation_notes',
        'evaluated_by',
        'evaluated_at',
        'feedback_submitted_at',
    ];

    protected $casts = [
        'pre_test_score' => 'float',
        'post_test_score' => 'float',
        'practice_score' => 'float',
        'final_score' => 'float',
        'is_passed' => 'boolean',
        'evaluated_at' => 'datetime',
        'feedback_submitted_at' => 'datetime',
    ];

    public function training()
    {
        return $this->belongsTo(HcgaTraining::class, 'training_id', 'ID');
    }

    public function tnaItem()
    {
        return $this->belongsTo(HcgaTnaItem::class, 'tna_item_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(HcgaTrainingFeedback::class, 'participant_id');
    }

    public function certificate()
    {
        return $this->hasOne(HcgaTrainingCertificate::class, 'participant_id');
    }

    public function coachings()
    {
        return $this->hasMany(HcgaTrainingCoaching::class, 'participant_id');
    }
}
