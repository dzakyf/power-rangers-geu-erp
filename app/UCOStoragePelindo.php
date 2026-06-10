<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UCOStoragePelindo extends Model
{
    protected $table = 'uco_storage_pelindo';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'Created_At';
    const UPDATED_AT = 'Updated_At';

    protected $fillable = [
        'Type',
        'Quantity',
        'Quantity_PTU',
        'Quantity_Sounding',
        'Created_By',
        'Transaction_Date',
        'Reference_Number',
        'Notes',
    ];

    protected $casts = [
        'Transaction_Date'  => 'date',
        'Quantity'          => 'decimal:2',
        'Quantity_PTU'      => 'decimal:2',
        'Quantity_Sounding' => 'decimal:2',
    ];

    public function scopeInbound($query)
    {
        return $query->where('Type', 'inbound');
    }

    public function scopeOutbound($query)
    {
        return $query->where('Type', 'outbound');
    }

    public static function balanceAsOf(string $date): float
    {
        return (float) self::where('Transaction_Date', '<', $date)
            ->selectRaw("SUM(CASE WHEN Type = 'inbound' THEN Quantity ELSE -Quantity END) as balance")
            ->value('balance') ?: 0;
    }
}
