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

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REJECTED = 'REJECTED';

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
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'form_date'          => 'date',
        'incorporation_date' => 'date',
        'approved_at'        => 'datetime',
    ];

    public function scopeApproved($query)
    {
        return $query->where('approval_status', self::STATUS_APPROVED);
    }

    public function isPending(): bool
    {
        return ($this->approval_status ?: self::STATUS_PENDING) === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->approval_status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->approval_status === self::STATUS_REJECTED;
    }

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

    /**
     * Build non-empty Master Company (supplier) fields from this KYC record.
     */
    public function masterCompanyPayload(): array
    {
        $payload = [
            'Company_Name' => mb_substr(trim((string) $this->company_name), 0, 100),
        ];

        $address = trim((string) ($this->registered_address ?: $this->admin_accounting_address));
        if ($address !== '') {
            $payload['Address'] = $address;
        }

        $npwp = trim((string) $this->vat_number);
        if ($npwp !== '') {
            $payload['NPWP'] = mb_substr($npwp, 0, 50);
        }

        $contactPerson = trim((string) ($this->trading_contact_person ?: $this->finance_contact_person));
        if ($contactPerson !== '') {
            $payload['Contact_Person'] = mb_substr($contactPerson, 0, 30);
        }

        $contactDetails = trim(implode("\n", array_filter([
            $this->trading_contact_details,
            $this->finance_contact_details,
        ])));

        if ($contactDetails !== '') {
            if (preg_match_all('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $contactDetails, $emailMatches)) {
                $emails = array_values(array_unique(array_map('strtolower', $emailMatches[0])));
                $emails = array_slice($emails, 0, 10);
                $payload['Email'] = mb_substr($emails[0], 0, 60);
                $payload['Emails'] = json_encode($emails);
                $contactDetails = str_replace($emailMatches[0], ' ', $contactDetails);
            }

            if (preg_match('/(\+?\d[\d\s\-().]{6,}\d)/', $contactDetails, $phoneMatch)) {
                $phone = trim(preg_replace('/\s+/', ' ', $phoneMatch[1]));
                if ($phone !== '') {
                    $payload['Phone'] = mb_substr($phone, 0, 60);
                }
            }
        }

        return $payload;
    }
}
