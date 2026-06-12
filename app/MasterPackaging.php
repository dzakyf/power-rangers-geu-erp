<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterPackaging extends Model
{
    protected $table = 'master_packaging';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'Created_At';
    const UPDATED_AT = 'Updated_At';

    protected $fillable = [
        'Name',
        'Capacity',
        'Unit',
        'Description',
        'Status',
    ];

    protected $casts = [
        'Capacity' => 'decimal:2',
    ];
}
