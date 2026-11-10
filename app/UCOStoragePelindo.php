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
        'Asal_Warehouse',
        'Nama_Tangki',
        'Quantity_PTU',
        'Quantity_Sounding',
        'Created_By',
        'Transaction_Date',
        'Reference_Number',
        'Notim_Path',
        'FFA',
        'MC',
        'IMP',
        'Notes',
        'QC_Path'
    ];

    protected $casts = [
        'Transaction_Date'  => 'date',
        'Quantity'          => 'decimal:2',
        'Quantity_PTU'      => 'decimal:2',
        'Quantity_Sounding' => 'decimal:2',
        'FFA'               => 'decimal:2',
        'MC'                => 'decimal:2',
        'IMP'               => 'decimal:2',
    ];

    public function scopeInbound($query)
    {
        return $query->where('Type', 'inbound');
    }

    public function scopeOutbound($query)
    {
        return $query->where('Type', 'outbound');
    }

    public static function balanceAsOf(string $date, ?string $warehouse = null, ?string $tank = null): float
    {
        $query = self::where('Transaction_Date', '<', $date);

        if ($warehouse) {
            $query->where(function ($q) use ($warehouse) {
                if (strtoupper($warehouse) === 'PELINDO') {
                    $q->where('Asal_Warehouse', 'PELINDO')
                      ->orWhere(function ($sub) {
                          $sub->where('Type', 'outbound')
                              ->where(function ($s) {
                                  $s->whereNull('Asal_Warehouse')->orWhere('Asal_Warehouse', '');
                              });
                      });
                } else {
                    $q->where('Asal_Warehouse', $warehouse);
                }
            });
        }

        if ($tank) {
            $query->where('Nama_Tangki', $tank);
        }

        return (float) $query->selectRaw("SUM(CASE WHEN Type = 'inbound' THEN Quantity ELSE -Quantity END) as balance")
            ->value('balance') ?: 0;
    }
}
