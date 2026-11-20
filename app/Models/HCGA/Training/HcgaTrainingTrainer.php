<?php

namespace App\Models\HCGA\Training;

use Illuminate\Database\Eloquent\Model;

class HcgaTrainingTrainer extends Model
{
    protected $table = 'master_training_hcga_trainer';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'Trainer_Name',
        'Trainer_Type',
        'Trainer_Company',
        'Email',
        'Phone',
        'Certificate',
        'Address',
        'Status',
        'Created_By',
        'Created_Date',
        'Updated_By',
        'Updated_Date',
    ];

    public function trainings()
    {
        return $this->hasMany(HcgaTraining::class, 'trainer_id', 'ID');
    }
}
