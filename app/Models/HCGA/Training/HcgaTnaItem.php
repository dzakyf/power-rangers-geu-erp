<?php

namespace App\Models\HCGA\Training;

use Illuminate\Database\Eloquent\Model;

class HcgaTnaItem extends Model
{
    protected $table = 'hcga_tna_items';

    protected $fillable = [
        'tna_id',
        'employee_username',
        'employee_name',
        'position_name',
        'competency_gap',
        'recommended_training',
        'program_id',
        'priority',
        'target_quarter',
        'notes',
    ];

    public function tna()
    {
        return $this->belongsTo(HcgaTnaRequest::class, 'tna_id');
    }

    public function program()
    {
        return $this->belongsTo(HcgaTrainingProgram::class, 'program_id');
    }
}
