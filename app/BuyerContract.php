<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BuyerContract extends Model
{
    protected $table = 'cns_buyer_contracts';
    protected $primaryKey = 'ID';
    protected $guarded = ['ID'];
    public $timestamps = false;

    public const STATUSES = [
        'UNDER_REVIEW'      => 'Under Review',
        'PENDING_SIGNATURE' => 'Pending Signature',
        'ACTIVE_IN_PROGRESS'=> 'Active - Quantity in Progress',
        'COMPLETED'         => 'Completed – Quantity Fulfilled',
        'EXPIRED'           => 'Expired',
        'TERMINATED'        => 'Terminated',
    ];

    public function bookings()
    {
        return $this->hasMany(BuyerContractBooking::class, 'Contract_ID', 'ID');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->Status] ?? $this->Status;
    }

    public function quantityDisplay(): string
    {
        return $this->formatDecimal($this->Quantity_MT) . ' MT (' . (int) $this->Container_Count . ' x ' . $this->Container_Size . ')';
    }

    public function priceDisplay(): string
    {
        $amount = (float) $this->Price_Per_MT;
        $decimals = ($amount == floor($amount)) ? 0 : 2;

        return strtoupper($this->Price_Currency) . ' ' . number_format($amount, $decimals, ',', '.') . '/MT';
    }

    private function formatDecimal($value): string
    {
        $formatted = number_format((float) $value, 3, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }
}
