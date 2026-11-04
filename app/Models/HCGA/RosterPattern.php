<?php

namespace App\Models\HCGA;

use Illuminate\Database\Eloquent\Model;

class RosterPattern extends Model
{
    protected $table = 'roster_patterns';

    protected $guarded = ['id'];

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function details()
    {
        return $this->hasMany(RosterPatternDetail::class, 'pattern_id', 'id')->orderBy('day_sequence', 'asc');
    }
}

