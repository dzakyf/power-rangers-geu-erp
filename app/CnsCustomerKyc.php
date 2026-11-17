<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CnsCustomerKyc extends Model
{
    use SoftDeletes;

    protected $table = 'cns_customer_kyc';

    protected $fillable = [
        'kyc_no',
        'filler_name',
        'form_date',
        'supplier_id',
        'company_name',
        'company_reg_number',
        'incorporation_date',
        'registered_address',
        'admin_accounting_address',
        'legal_entity_type',
        'vat_number',
        'website',
        'trading_contact_person',
        'trading_contact_details',
        'finance_contact_person',
        'finance_contact_details',
        'business_activities',
        'countries_of_activities',
        'doc_register_extract',
        'doc_register_extract_name',
        'doc_certificate',
        'doc_certificate_name',
        'location',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'form_date'          => 'date',
        'incorporation_date' => 'date',
    ];

    public function getSupplierAttribute()
    {
        if (empty($this->supplier_id)) {
            return null;
        }

        return DB::table('supplier')->where('ID', $this->supplier_id)->first();
    }

    public static function romanMonth($month): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
        return $map[(int) $month] ?? 'I';
    }

    public static function formatKycNo(int $seq, Carbon $date): string
    {
        return sprintf('%03d/KYC-GEU/%s/%s', $seq, self::romanMonth($date->month), $date->year);
    }

    public static function nextSequenceFromNumbers(array $numbers): int
    {
        $max = 0;
        foreach ($numbers as $num) {
            if (preg_match('/^(\d+)\/KYC-GEU\//', trim($num), $m)) {
                $val = (int) $m[1];
                if ($val > $max) {
                    $max = $val;
                }
            }
        }
        return $max + 1;
    }

    public static function generateKycNo(Carbon $date): string
    {
        $year = $date->year;
        $pattern = '%/KYC-GEU/%/' . $year;

        $existing = DB::table('cns_customer_kyc')
            ->where('kyc_no', 'like', $pattern)
            ->pluck('kyc_no')
            ->all();

        $seq = self::nextSequenceFromNumbers($existing);
        return self::formatKycNo($seq, $date);
    }
}
