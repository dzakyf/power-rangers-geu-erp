<?php

namespace App\Models\HCGA;

use Illuminate\Database\Eloquent\Model;

class EmployeeRosterAssignment extends Model
{
    protected $table = 'employee_roster_assignments';

    protected $guarded = ['id'];

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function pattern()
    {
        return $this->belongsTo(RosterPattern::class, 'pattern_id', 'id');
    }
}

