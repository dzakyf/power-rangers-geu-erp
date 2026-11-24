<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CnsShipmentCostAllocation extends Model
{
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_FINAL = 'FINAL';

    public const CATEGORIES = [
        'freight'  => 'Freight',
        'trucking' => 'Trucking',
        'surveyor' => 'Surveyor',
        'port'     => 'Port',
    ];

    protected $table = 'cns_shipment_cost_allocations';

    protected $guarded = ['id'];

    protected $casts = [
        'default_exchange_rate'  => 'float',
        'total_freight_usd'      => 'float',
        'total_trucking_usd'     => 'float',
        'total_surveyor_usd'     => 'float',
        'total_port_usd'         => 'float',
        'total_cost_usd'         => 'float',
        'total_cost_idr'         => 'float',
        'volume_mt'              => 'float',
        'container_count'        => 'integer',
        'cost_per_mt_usd'        => 'float',
        'cost_per_container_usd' => 'float',
        'finalized_at'           => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(CnsShipmentCostAllocationItem::class, 'allocation_id', 'id');
    }

    public function shippingInstruction()
    {
        return $this->belongsTo(ShippingInstruction::class, 'si_id', 'id');
    }

    public function isFinal(): bool
    {
        return strtoupper((string) $this->status) === self::STATUS_FINAL;
    }

    /**
     * Recalculate category totals, grand totals (USD & IDR), and unit costs from persisted items.
     */
    public function recalculateTotals(): void
    {
        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();

        $byCategory = [
            'freight'  => 0.0,
            'trucking' => 0.0,
            'surveyor' => 0.0,
            'port'     => 0.0,
        ];
        $totalUsd = 0.0;
        $totalIdr = 0.0;

        foreach ($items as $item) {
            $cat = strtolower((string) $item->category);
            if (array_key_exists($cat, $byCategory)) {
                $byCategory[$cat] += (float) $item->amount_usd;
            }
            $totalUsd += (float) $item->amount_usd;
            $totalIdr += (float) $item->amount_idr;
        }

        $volumeMt = (float) $this->volume_mt;
        $containerCount = (int) $this->container_count;

        $this->total_freight_usd = round($byCategory['freight'], 4);
        $this->total_trucking_usd = round($byCategory['trucking'], 4);
        $this->total_surveyor_usd = round($byCategory['surveyor'], 4);
        $this->total_port_usd = round($byCategory['port'], 4);
        $this->total_cost_usd = round($totalUsd, 4);
        $this->total_cost_idr = round($totalIdr, 2);
        $this->cost_per_mt_usd = $volumeMt > 0 ? round($totalUsd / $volumeMt, 4) : 0.0;
        $this->cost_per_container_usd = $containerCount > 0 ? round($totalUsd / $containerCount, 4) : 0.0;
    }

    /**
     * Generate the next unique allocation_no in format CA/EXP/001/XI/2026.
     */
    public static function generateNextAllocationNo(): string
    {
        $romans = [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $roman = $romans[(int) date('n')];
        $year = date('Y');
        $prefix = 'CA/EXP/';
        $suffix = "/{$roman}/{$year}";

        $existing = self::query()
            ->where('allocation_no', 'like', $prefix . '%' . $suffix)
            ->pluck('allocation_no')
            ->all();

        $maxSeq = 0;
        $pattern = '/^' . preg_quote($prefix, '/') . '(\d+)' . preg_quote($suffix, '/') . '$/';
        foreach ($existing as $no) {
            if (preg_match($pattern, trim((string) $no), $m)) {
                $seq = (int) $m[1];
                if ($seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        }

        return $prefix . str_pad((string) ($maxSeq + 1), 3, '0', STR_PAD_LEFT) . $suffix;
    }
}
