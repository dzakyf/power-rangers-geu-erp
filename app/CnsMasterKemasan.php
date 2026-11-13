<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CnsMasterKemasan extends Model
{
    protected $table = 'cns_master_kemasan';
    protected $primaryKey = 'Kemasan_ID';

    protected $fillable = [
        'Name',
        'product_type',
        'Volume',
        'Is_Ampas',
        'Max_Stock',
        'Unit',
        'Price',
    ];

    protected $casts = [
        'Volume'    => 'float',
        'Is_Ampas'  => 'boolean',
        'Max_Stock' => 'float',
        'Price'     => 'float',
    ];

    public function isAmpas(): bool
    {
        return (bool) $this->Is_Ampas || stripos((string) $this->Name, 'ampas') !== false;
    }
}
