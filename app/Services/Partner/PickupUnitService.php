<?php

namespace App\Services\Partner;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Resolves package-based pickup orders (jerrycans, drums, ...) into the
 * canonical liter quantities used by warehouse stock.
 */
class PickupUnitService
{
    public const LITER = 'L';
    public const PACKAGE = 'PCS';

    /**
     * Capacity in liters for a master packaging name. Unknown items are
     * treated as one liter so legacy data keeps its old behaviour.
     */
    public static function packageCapacity(?string $packageName): float
    {
        $name = trim((string) $packageName);
        if ($name === '') {
            return 1.0;
        }

        $capacity = null;
        if (Schema::hasTable('master_packaging')) {
            $capacity = DB::table('master_packaging')->where('Name', $name)->value('Capacity');
        }

        if ($capacity !== null && (float) $capacity > 0) {
            return (float) $capacity;
        }

        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(?:L|LT|LTR|LITER)\b/i', $name, $matches)) {
            $parsed = (float) str_replace(',', '.', $matches[1]);
            if ($parsed > 0) {
                return $parsed;
            }
        }

        return 1.0;
    }

    /**
     * Units to persist when a pickup order is created from a package master.
     *
     * @return array{0:string,1:string,2:float}
     */
    public static function unitsForPackage(?string $packageName): array
    {
        $capacity = self::packageCapacity($packageName);
        $unit = $capacity > 1 ? self::PACKAGE : self::LITER;

        return [$unit, $unit, $capacity];
    }

    /**
     * Normalize a pickup order row into package + liter values.
     *
     * @param object|array $po
     * @return array<string, mixed>
     */
    public static function resolve($po, ?float $pooPrice = null): array
    {
        $package = self::value($po, 'Package');
        $capacityValue = self::value($po, 'Package_Capacity');
        $capacity = ($capacityValue !== null && (float) $capacityValue > 0)
            ? (float) $capacityValue
            : self::packageCapacity($package);

        $qty = (float) (self::value($po, 'Qty') ?? 0);
        $actualLiters = (float) (self::value($po, 'Actual_Qty') ?? 0);
        $price = (float) (self::value($po, 'Price') ?? 0);
        $qtyUnit = self::value($po, 'Qty_Unit');
        $priceUnit = self::value($po, 'Price_Unit');

        if (!$qtyUnit || !$priceUnit) {
            // Legacy rows have no unit metadata. A row was entered in
            // packages when Actual_Qty matches Qty x capacity, or when the
            // PO price is clearly a package price rather than the POO
            // per-liter price.
            $matchesPackageQty = $capacity > 1 && $actualLiters > 0
                && abs($actualLiters - ($qty * $capacity)) <= max(0.5, $actualLiters * 0.02);
            $looksPackagePrice = $capacity > 1 && $pooPrice !== null && $pooPrice > 0
                && $price > ($pooPrice * 1.5);
            $isPackage = $matchesPackageQty || $looksPackagePrice;
            $qtyUnit = $qtyUnit ?: ($isPackage ? self::PACKAGE : self::LITER);
            $priceUnit = $priceUnit ?: ($isPackage ? self::PACKAGE : self::LITER);
        }

        if ($price <= 0 && $pooPrice !== null) {
            $price = (float) $pooPrice;
            $priceUnit = self::LITER;
        }

        $pricePerLiter = $priceUnit === self::PACKAGE
            ? ($capacity > 0 ? $price / $capacity : $price)
            : $price;
        $pricePerPackage = $priceUnit === self::PACKAGE
            ? $price
            : $price * $capacity;

        return [
            'package' => $package,
            'capacity' => $capacity,
            'qty_unit' => $qtyUnit,
            'price_unit' => $priceUnit,
            'qty' => $qty,
            'qty_plan_liters' => $qtyUnit === self::PACKAGE ? $qty * $capacity : $qty,
            'qty_plan_packages' => $qtyUnit === self::PACKAGE ? $qty : ($capacity > 0 ? $qty / $capacity : $qty),
            'actual_liters' => $actualLiters,
            'actual_packages' => $capacity > 0 ? $actualLiters / $capacity : $actualLiters,
            'po_price' => $price,
            'price_per_liter' => $pricePerLiter,
            'price_per_package' => $pricePerPackage,
        ];
    }

    /**
     * Validate and normalize explicit PO quantity/price units.
     *
     * @return array{0:string,1:string}
     * @throws \InvalidArgumentException
     */
    public static function normalizeUnits(?string $qtyUnit, ?string $priceUnit, float $capacity): array
    {
        $qtyUnit = strtoupper(trim((string) $qtyUnit)) ?: ($capacity > 1 ? self::PACKAGE : self::LITER);
        $priceUnit = strtoupper(trim((string) $priceUnit)) ?: ($capacity > 1 ? self::PACKAGE : self::LITER);

        $allowed = [self::LITER, self::PACKAGE];
        if (!in_array($qtyUnit, $allowed, true) || !in_array($priceUnit, $allowed, true)) {
            throw new \InvalidArgumentException('Satuan PO harus L atau PCS.');
        }
        if (($qtyUnit === self::PACKAGE || $priceUnit === self::PACKAGE) && $capacity <= 1) {
            throw new \InvalidArgumentException('Kemasan PCS membutuhkan kapasitas kemasan yang valid.');
        }

        return [$qtyUnit, $priceUnit];
    }

    public static function litersFromInput(float $value, ?string $unit, float $capacity): float
    {
        return strtoupper((string) $unit) === self::PACKAGE
            ? $value * $capacity
            : $value;
    }

    private static function value($po, string $key)
    {
        if (is_array($po)) {
            return $po[$key] ?? null;
        }

        return $po->{$key} ?? null;
    }
}
