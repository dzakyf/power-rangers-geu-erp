<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformanceDeptPic extends Model
{
    protected $table = 'performance_dept_pics';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
