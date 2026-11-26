<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CnsCustomsClearance extends Model
{
    protected $table = 'cns_customs_clearances';

    public const DOC_TYPES = [
        'doc_3d' => 'Dokumen 3D',
        'peb'    => 'PEB',
        'npe'    => 'NPE',
        'sphib'  => 'SPHIB',
    ];

    protected $fillable = [
        'booking_id',
        'doc_3d_path',
        'doc_3d_name',
        'peb_path',
        'peb_name',
        'npe_path',
        'npe_name',
        'sphib_path',
        'sphib_name',
        'notes',
        'updated_by',
    ];

    public function booking()
    {
        return $this->belongsTo(BuyerContractBooking::class, 'booking_id', 'ID');
    }

    public function uploadedCount(): int
    {
        $count = 0;
        foreach (array_keys(self::DOC_TYPES) as $type) {
            if (!empty($this->{$type . '_path'})) {
                $count++;
            }
        }

        return $count;
    }
}
