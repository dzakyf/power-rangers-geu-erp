<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BuyerContractBooking extends Model
{
    protected $table = 'cns_buyer_contract_bookings';
    protected $primaryKey = 'ID';
    protected $guarded = ['ID'];
    public $timestamps = false;

    public function contract()
    {
        return $this->belongsTo(BuyerContract::class, 'Contract_ID', 'ID');
    }
}
