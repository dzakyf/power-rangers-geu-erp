<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VesselScheduleDtl extends Model
{
    protected $table = 'vessel_schedule_dtls';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    public function scopeActive($query)
    {
        return $query->where('Status', '!=', 'CANCELED');
    }

    public function scopeForSchedule($query, VesselSchedule $schedule)
    {
        if ($schedule->Order_No) {
            return $query->where('Order_No', $schedule->Order_No);
        }

        return $query->where('Task_ID', $schedule->Task_ID);
    }
}
