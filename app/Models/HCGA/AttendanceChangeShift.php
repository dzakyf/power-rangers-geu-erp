<?php

namespace App\Models\HCGA;

use Illuminate\Database\Eloquent\Model;

class AttendanceChangeShift extends Model
{
    protected $table = 'attendance_change_shifts';

    protected $guarded = ['id'];

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function currentShift()
    {
        return $this->belongsTo(MasterShift::class, 'current_shift_id', 'id');
    }

    public function targetShift()
    {
        return $this->belongsTo(MasterShift::class, 'target_shift_id', 'id');
    }

    public function peerCurrentShift()
    {
        return $this->belongsTo(MasterShift::class, 'peer_current_shift_id', 'id');
    }

    public function peerTargetShift()
    {
        return $this->belongsTo(MasterShift::class, 'peer_target_shift_id', 'id');
    }
}

