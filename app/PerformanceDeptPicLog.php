<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformanceDeptPicLog extends Model
{
    protected $table = 'performance_dept_pic_logs';
    protected $guarded = ['id'];
    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function previousUser()
    {
        return $this->belongsTo(User::class, 'previous_user_id');
    }

    public function newUser()
    {
        return $this->belongsTo(User::class, 'new_user_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
