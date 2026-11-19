<?php

namespace App\Models\HCGA\Training;

use Illuminate\Database\Eloquent\Model;

class HcgaTnaRequest extends Model
{
    protected $table = 'hcga_tna_requests';

    protected $fillable = [
        'tna_no',
        'department',
        'fiscal_year',
        'title',
        'background',
        'requested_by',
        'requester_user_id',
        'status',
        'approval_request_id',
        'approved_at',
        'approved_by',
        'approval_notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(HcgaTnaItem::class, 'tna_id');
    }

    public function trainings()
    {
        return $this->hasMany(HcgaTraining::class, 'tna_id');
    }
}
