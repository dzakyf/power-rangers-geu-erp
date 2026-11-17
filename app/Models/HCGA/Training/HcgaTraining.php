<?php

namespace App\Models\HCGA\Training;

use Illuminate\Database\Eloquent\Model;

class HcgaTraining extends Model
{
    protected $table = 'training_hcga';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'Task_No',
        'tna_id',
        'program_id',
        'trainer_id',
        'trainer_name',
        'Training_Name',
        'Training_Category',
        'Training_Type',
        'Training_Method',
        'Training_Description',
        'Training_Objective',
        'Training_Competence',
        'Start_Date',
        'End_Date',
        'passing_grade',
        'batch_type',
        'parent_training_id',
        'Training_Location',
        'Location',
        'Total_Cost',
        'Currency',
        'Status',
        'approval_request_id',
        'Created_By',
        'Created_Date',
        'Updated_By',
        'Updated_Date',
        'Approved_By',
        'Approved_Date',
        'File_Document',
    ];

    protected $casts = [
        'passing_grade' => 'float',
    ];

    public function tna()
    {
        return $this->belongsTo(HcgaTnaRequest::class, 'tna_id');
    }

    public function program()
    {
        return $this->belongsTo(HcgaTrainingProgram::class, 'program_id');
    }

    public function parentTraining()
    {
        return $this->belongsTo(self::class, 'parent_training_id', 'ID');
    }

    public function curriculums()
    {
        return $this->hasMany(HcgaTrainingCurriculum::class, 'training_id', 'ID')->orderBy('session_order');
    }

    public function participants()
    {
        return $this->hasMany(HcgaTrainingParticipant::class, 'training_id', 'ID');
    }

    public function feedbacks()
    {
        return $this->hasMany(HcgaTrainingFeedback::class, 'training_id', 'ID');
    }

    public function certificates()
    {
        return $this->hasMany(HcgaTrainingCertificate::class, 'training_id', 'ID');
    }

    public function coachings()
    {
        return $this->hasMany(HcgaTrainingCoaching::class, 'training_id', 'ID');
    }
}
