<?php

namespace App\Models\HCGA;

use App\HcgaNewsAnnouncement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class StorageDocument extends Model
{
    protected $table = 'hcga_storage_documents';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'current_version_id'     => 'integer',
        'current_version_number' => 'integer',
    ];

    const MODULE_REGULATION = 'REGULATION';
    const MODULE_INFORMATION = 'INFORMATION';

    const STATUS_DRAFT = 'DRAFT';
    const STATUS_WAITING_APPROVAL = 'WAITING_APPROVAL';
    const STATUS_PUBLISHED = 'PUBLISHED';
    const STATUS_REVOKED = 'REVOKED';
    const STATUS_ARCHIVED = 'ARCHIVED';

    public static function getSubTypes($moduleType = null)
    {
        $regulation = [
            'SK_DIR'        => 'SK-Dir (Surat Keputusan Direksi)',
            'SOP'           => 'SOP (Standard Operating Procedure)',
            'INTERNAL_MEMO' => 'Internal Memo',
        ];

        $information = [
            'UMUM'       => 'Informasi Umum',
            'PANDUAN'    => 'Panduan & Tata Cara',
            'EVENT'      => 'Kegiatan / Event',
            'PENGUMUMAN' => 'Pengumuman Operasional',
        ];

        if ($moduleType === self::MODULE_REGULATION) {
            return $regulation;
        }
        if ($moduleType === self::MODULE_INFORMATION) {
            return $information;
        }

        return array_merge($regulation, $information);
    }

    public static function getSubTypeLabel($subType)
    {
        $shortLabels = [
            'SK_DIR'        => 'SK-Dir',
            'SOP'           => 'SOP',
            'INTERNAL_MEMO' => 'Internal Memo',
            'UMUM'          => 'Informasi Umum',
            'PANDUAN'       => 'Panduan',
            'EVENT'         => 'Event',
            'PENGUMUMAN'    => 'Pengumuman',
        ];

        return $shortLabels[$subType] ?? $subType;
    }

    public function versions()
    {
        return $this->hasMany(StorageDocumentVersion::class, 'document_id', 'id')
            ->orderBy('version_number', 'DESC');
    }

    public function currentVersion()
    {
        return $this->belongsTo(StorageDocumentVersion::class, 'current_version_id', 'id');
    }

    public function announcements()
    {
        return $this->hasMany(HcgaNewsAnnouncement::class, 'storage_document_id', 'id')
            ->orderBy('id', 'DESC');
    }

    /**
     * Compute real-time effective status considering document lifecycle and validity dates.
     */
    public function computeEffectiveStatus($date = null)
    {
        if ($this->status === self::STATUS_REVOKED) {
            return 'REVOKED';
        }
        if ($this->status === self::STATUS_ARCHIVED) {
            return 'ARCHIVED';
        }

        $ver = $this->currentVersion;
        if (!$ver) {
            return $this->status ?: 'DRAFT';
        }

        if ($ver->version_status === StorageDocumentVersion::STATUS_WAITING_APPROVAL || $this->status === self::STATUS_WAITING_APPROVAL) {
            return 'WAITING_APPROVAL';
        }
        if ($ver->version_status === StorageDocumentVersion::STATUS_REJECTED) {
            return 'REJECTED';
        }
        if ($ver->version_status === StorageDocumentVersion::STATUS_DRAFT || $this->status === self::STATUS_DRAFT) {
            return 'DRAFT';
        }

        return $ver->computeEffectiveStatus($date);
    }
}
