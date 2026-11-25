<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CnsOfferingLetter extends Model
{
    use SoftDeletes;

    protected $table = 'cns_offering_letters';

    protected $fillable = [
        'offer_no',
        'offer_date',
        'seller_id',
        'seller_code',
        'seller_name',
        'place_of_issue',
        'buyer_id',
        'buyer_name',
        'product',
        'hs_code',
        'certificate',
        'spec_ffa',
        'spec_mni',
        'spec_unsaponifiable',
        'spec_iodine_value',
        'spec_sulphur',
        'spec_total_metals',
        'spec_phosphorus',
        'spec_total_chloride',
        'quantity_mt',
        'quantity_note',
        'final_qty_quality',
        'price_currency',
        'price_per_mt',
        'loading_period',
        'delivery_term',
        'destination_port',
        'packaging',
        'payment_terms',
        'load_port_inspector',
        'signatory_key',
        'signatory_name',
        'signatory_title',
        'signatory_signature',
        'status',
        'approved_by',
        'approved_at',
        'valid_until',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'offer_date'  => 'date',
        'approved_at' => 'datetime',
        'valid_until' => 'date',
        'quantity_mt' => 'decimal:2',
        'price_per_mt' => 'decimal:2',
    ];

    const STATUS_DRAFT = 'DRAFT';
    const STATUS_APPROVED = 'APPROVED';
    const VALIDITY_DAYS = 14;

    public static function sellers(): array
    {
        return [
            'GNC' => [
                'name'  => 'Greennew Corporation PTE LTD',
                'place' => 'Singapore',
            ],
            'GEU' => [
                'name'  => 'Green Energi Utama',
                'place' => 'Jakarta',
            ],
        ];
    }

    public static function resolveSellerCode(?string $name, ?string $fallbackCode = null): string
    {
        $name = trim((string) $name);
        $fallbackCode = trim((string) $fallbackCode);

        if (stripos($name, 'Green Energi Utama') !== false || strtoupper($fallbackCode) === 'GEU') {
            return 'GEU';
        }

        if (
            stripos($name, 'Greennew') !== false
            || stripos($name, 'Grennew') !== false
            || strtoupper($fallbackCode) === 'GNC'
        ) {
            return 'GNC';
        }

        $cleanCode = preg_replace('/[^A-Za-z0-9]/', '', $fallbackCode);
        if (!empty($cleanCode)) {
            return strtoupper($cleanCode);
        }

        $words = preg_split('/\s+/', $name);
        $acronym = '';
        foreach ($words as $w) {
            if (!empty($w)) {
                $acronym .= $w[0];
            }
        }
        return strtoupper(substr($acronym ?: 'EXP', 0, 10));
    }

    public static function signatories(): array
    {
        return [
            'puput' => [
                'name'  => 'Puput Deni Iswara',
                'title' => 'CCO',
            ],
            'hani' => [
                'name'  => 'Hani Septyana',
                'title' => 'Commercial',
            ],
        ];
    }

    public static function hsCodes(): array
    {
        return ['15119020', '15180090'];
    }

    public static function formatLoadingPeriod(?string $value): string
    {
        $value = trim((string) $value);
        if (preg_match('/^(\d{4}-\d{2}-\d{2})\s*-\s*(\d{4}-\d{2}-\d{2})$/', $value, $m)) {
            $start = Carbon::createFromFormat('Y-m-d', $m[1])->format('d F Y');
            $end = Carbon::createFromFormat('Y-m-d', $m[2])->format('d F Y');
            return $start . ' - ' . $end;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return Carbon::createFromFormat('Y-m-d', $value)->format('d F Y');
        }
        if (preg_match('/^\d{4}-\d{2}$/', $value)) {
            return Carbon::createFromFormat('Y-m', $value)->format('F Y');
        }
        return $value;
    }

    public static function parseLoadingPeriodRange(?string $value): array
    {
        $value = trim((string) $value);
        if (preg_match('/^(\d{4}-\d{2}-\d{2})\s*-\s*(\d{4}-\d{2}-\d{2})$/', $value, $m)) {
            return ['start' => $m[1], 'end' => $m[2]];
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return ['start' => $value, 'end' => $value];
        }
        if (preg_match('/^\d{4}-\d{2}$/', $value)) {
            $start = Carbon::createFromFormat('Y-m', $value)->startOfMonth();
            return ['start' => $start->format('Y-m-d'), 'end' => $start->copy()->endOfMonth()->format('Y-m-d')];
        }
        return ['start' => '', 'end' => ''];
    }

    public static function loadingPeriodMonthValue(?string $value): string
    {
        $value = trim((string) $value);
        if (preg_match('/^\d{4}-\d{2}$/', $value)) {
            return $value;
        }
        if ($value !== '') {
            try {
                return Carbon::parse($value)->format('Y-m');
            } catch (\Exception $e) {
                return date('Y-m');
            }
        }
        return date('Y-m');
    }

    public static function defaults(): array
    {
        return [
            'product'             => 'Refined Oil (Used Cooking Oil Entirely of Vege Origin)',
            'hs_code'             => '15119020',
            'certificate'         => 'ISCC-EU',
            'spec_ffa'            => '5% max',
            'spec_mni'            => '2% max',
            'spec_unsaponifiable' => '1% max',
            'spec_iodine_value'   => '50 min',
            'spec_sulphur'        => '50 ppm max',
            'spec_total_metals'   => '85 max',
            'spec_phosphorus'     => '50 max',
            'spec_total_chloride' => '50 max',
            'quantity_note'       => '(+/- 5% operational tolerance) In bulk',
            'final_qty_quality'   => 'Quantity and quality shall be final and binding at the Port of Loading (POL) as determined by an independent third-party surveyor.',
            'price_currency'      => 'USD',
            'packaging'           => 'In Flexibag Container',
            'payment_terms'       => "100% L/C at Sight upon shipping documents\nSeller’s commercial Invoice (Scanned copies acceptable)\nCertificate(s) of Origin issued by Seller (Scanned copies acceptable)\nCertificate(s) of Quality issued by Seller/Independent Surveyor (Scanned copies acceptable)\nCertificate(s) of Quantity issued by Seller (Scanned copies acceptable)\nISCC-EU Sustainability Declaration / Proof of Sustainability (whichever applicable) (Scanned copies acceptable)\nPacking list (Scanned copies acceptable)",
            'load_port_inspector' => 'Third Party Independent Surveyor (Sharing Cost 50/50)',
        ];
    }

    public static function romanMonth(int $month): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $map[$month] ?? 'I';
    }

    /**
     * Format: XXX/OL/EXP-{GNC|GEU}/{ROMAN_MONTH}/{YEAR}
     */
    public static function formatOfferNo(int $sequence, string $sellerCode, Carbon $date): string
    {
        return sprintf(
            '%03d/OL/EXP-%s/%s/%s',
            $sequence,
            strtoupper($sellerCode),
            self::romanMonth((int) $date->format('n')),
            $date->format('Y')
        );
    }

    /**
     * Extract leading 3-digit sequence from existing offer_no strings and return max + 1.
     */
    public static function nextSequenceFromNumbers(iterable $existingNumbers): int
    {
        $max = 0;
        foreach ($existingNumbers as $no) {
            if (preg_match('/^(\d+)\/OL\//', (string) $no, $m)) {
                $seq = (int) $m[1];
                if ($seq > $max) {
                    $max = $seq;
                }
            }
        }

        return $max + 1;
    }

    public static function generateOfferNo(string $sellerCode, Carbon $date): string
    {
        $existing = self::withTrashed()
            ->where('seller_code', $sellerCode)
            ->whereYear('offer_date', $date->format('Y'))
            ->pluck('offer_no');

        return self::formatOfferNo(self::nextSequenceFromNumbers($existing), $sellerCode, $date);
    }
}
