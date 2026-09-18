<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterSppdJobLevel extends Model
{
    protected $table = 'master_sppd_job_levels';

    protected $fillable = [
        'level',
        'grade',
        'label',
    ];
}
