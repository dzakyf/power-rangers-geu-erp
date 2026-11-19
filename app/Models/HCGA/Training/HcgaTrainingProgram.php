<?php

namespace App\Models\HCGA\Training;

use Illuminate\Database\Eloquent\Model;

class HcgaTrainingProgram extends Model
{
    protected $table = 'hcga_training_programs';

    protected $fillable = [
        'program_code',
        'program_name',
        'category_id',
        'training_type',
        'training_method',
        'description',
        'objective',
        'competency',
        'default_passing_grade',
        'module_file_path',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'default_passing_grade' => 'float',
    ];

    public function trainings()
    {
        return $this->hasMany(HcgaTraining::class, 'program_id');
    }
}
