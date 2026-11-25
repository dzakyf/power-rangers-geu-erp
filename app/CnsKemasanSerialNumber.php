<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CnsKemasanSerialNumber extends Model
{
    public const STATUS_AVAILABLE = 'AVAILABLE';
    public const STATUS_RESERVED = 'RESERVED';
    public const STATUS_USED = 'USED';

    protected $table = 'cns_kemasan_serial_numbers';

    protected $fillable = [
        'kemasan_id',
        'serial_number',
        'status',
        'goods_receipt_id',
        'vessel_schedule_id',
        'vessel_schedule_dtl_id',
        'used_at',
    ];

    protected $casts = [
        'kemasan_id' => 'integer',
        'goods_receipt_id' => 'integer',
        'vessel_schedule_id' => 'integer',
        'vessel_schedule_dtl_id' => 'integer',
        'used_at' => 'datetime',
    ];

    public function kemasan()
    {
        return $this->belongsTo(CnsMasterKemasan::class, 'kemasan_id', 'Kemasan_ID');
    }
}
