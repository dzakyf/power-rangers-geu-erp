<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CnsShipmentCostAllocationItem extends Model
{
    protected $table = 'cns_shipment_cost_allocation_items';

    protected $guarded = ['id'];

    protected $casts = [
        'allocation_id' => 'integer',
        'amount'        => 'float',
        'exchange_rate' => 'float',
        'amount_usd'    => 'float',
        'amount_idr'    => 'float',
    ];

    public function allocation()
    {
        return $this->belongsTo(CnsShipmentCostAllocation::class, 'allocation_id', 'id');
    }

    /**
     * Compute USD and IDR equivalents from currency, amount, and exchange rate (IDR per 1 USD).
     */
    public static function convertAmounts(string $currency, float $amount, float $exchangeRate): array
    {
        $currency = strtoupper(trim($currency)) === 'IDR' ? 'IDR' : 'USD';
        $rate = $exchangeRate > 0 ? $exchangeRate : 16000.0;

        if ($currency === 'IDR') {
            $amountIdr = round($amount, 2);
            $amountUsd = round($amount / $rate, 4);
        } else {
            $amountUsd = round($amount, 4);
            $amountIdr = round($amount * $rate, 2);
        }

        return [
            'currency'      => $currency,
            'exchange_rate' => $rate,
            'amount_usd'    => $amountUsd,
            'amount_idr'    => $amountIdr,
        ];
    }
}
