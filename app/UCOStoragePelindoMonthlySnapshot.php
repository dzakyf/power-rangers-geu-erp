<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UCOStoragePelindoMonthlySnapshot extends Model
{
    protected $table = 'monthly_snapshots';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'Created_At';
    const UPDATED_AT = 'Updated_At';

    protected $fillable = [
        'Year',
        'Month',
        'Opening_Balance',
        'Total_Inbound',
        'Total_Outbound',
        'Closing_Balance',
        'Snapshot_Date',
    ];

    protected $casts = [
        'Opening_Balance' => 'decimal:2',
        'Total_Inbound'   => 'decimal:2',
        'Total_Outbound'  => 'decimal:2',
        'Closing_Balance' => 'decimal:2',
        'Snapshot_Date'   => 'date',
    ];
}
