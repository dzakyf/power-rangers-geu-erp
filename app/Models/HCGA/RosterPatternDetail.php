<?php

namespace App\Models\HCGA;

use Illuminate\Database\Eloquent\Model;

class RosterPatternDetail extends Model
{
    protected $table = 'roster_pattern_details';

    protected $guarded = ['id'];

    public $timestamps = false;

    public function shift()
    {
        return $this->belongsTo(MasterShift::class, 'shift_id', 'id');
    }

    public function pattern()
    {
        return $this->belongsTo(RosterPattern::class, 'pattern_id', 'id');
    }
}

