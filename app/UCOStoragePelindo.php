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
        'fleet_name',
        'fleet_plat',
        'driver_name',
        'Notim_Path',
        'FFA',
        'MC',
        'IMP',
        'Movement_FFA',
        'Movement_MC',
        'Movement_IMP',
        'Notes',
        'QC_Path',
        'BA_Path',
        'Approval_Status',
        'Approved_By',
        'Approved_At',
        'Rejection_Reason'
    ];

    protected $casts = [
        'Transaction_Date'  => 'date',
        'Approved_At'       => 'datetime',
        'Quantity'          => 'decimal:2',
        'Quantity_PTU'      => 'decimal:2',
        'Quantity_Sounding' => 'decimal:2',
        'FFA'               => 'decimal:2',
        'MC'                => 'decimal:2',
        'IMP'               => 'decimal:2',
        'Movement_FFA'      => 'decimal:2',
        'Movement_MC'       => 'decimal:2',
        'Movement_IMP'      => 'decimal:2',
    ];

    public function scopeInbound($query)
    {
        return $query->where('Type', 'inbound');
    }

    public function scopeOutbound($query)
    {
        return $query->where('Type', 'outbound');
    }

    public function scopeApproved($query)
    {
        return $query->where(function ($q) {
            $q->where('Approval_Status', 'APPROVED')
              ->orWhereNull('Approval_Status');
        });
    }

    public function isApproved(): bool
    {
        return $this->Approval_Status === null || strtoupper((string) $this->Approval_Status) === 'APPROVED';
    }

    public static function balanceAsOf(string $date, ?string $warehouse = null, ?string $tank = null): float
    {
        $query = self::approved()->where('Transaction_Date', '<', $date);

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
