<?php

namespace App\Models\HCGA;

use App\HcgaNewsAnnouncement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class StorageDocumentVersion extends Model
{
    protected $table = 'hcga_storage_document_versions';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'document_id'             => 'integer',
        'version_number'          => 'integer',
        'attachment_size'         => 'integer',
        'is_perpetual'            => 'integer',
        'target_departments'      => 'array',
        'is_pinned'               => 'integer',
        'require_read_receipt'    => 'integer',
        'publish_to_mobile'       => 'integer',
        'archive_old_announcement'=> 'integer',
        'approval_request_id'     => 'integer',
    ];

    const STATUS_DRAFT = 'DRAFT';
    const STATUS_WAITING_APPROVAL = 'WAITING_APPROVAL';
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_SUPERSEDED = 'SUPERSEDED';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_REVOKED = 'REVOKED';

    const VALIDITY_FIXED_RANGE = 'FIXED_RANGE';
    const VALIDITY_PERPETUAL = 'PERPETUAL';

    const VISIBILITY_ADMIN_ONLY = 'ADMIN_ONLY';
    const VISIBILITY_EMPLOYEE_VISIBLE = 'EMPLOYEE_VISIBLE';

    const SYNC_UPDATE_EXISTING = 'UPDATE_EXISTING';
    const SYNC_CREATE_NEW = 'CREATE_NEW';

    public function document()
    {
        return $this->belongsTo(StorageDocument::class, 'document_id', 'id');
    }

    public function readReceipts()
    {
        return $this->hasMany(StorageReadReceipt::class, 'version_id', 'id')
            ->orderBy('read_at', 'DESC');
    }

    public function announcements()
    {
        return $this->hasMany(HcgaNewsAnnouncement::class, 'storage_version_id', 'id');
    }

    public function computeEffectiveStatus($date = null)
    {
        if ($this->version_status !== self::STATUS_ACTIVE) {
            return $this->version_status;
        }

        $today = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();

        if (!empty($this->effective_from) && $this->effective_from > $today) {
            return 'SCHEDULED';
        }

        if ((int) $this->is_perpetual !== 1 && !empty($this->effective_until) && $this->effective_until < $today) {
            return 'EXPIRED';
        }

        return 'ACTIVE';
    }

    public function isCurrentlyEffective($date = null)
    {
        return $this->computeEffectiveStatus($date) === 'ACTIVE';
    }

    public function getValidityLabelAttribute()
    {
        $from = $this->effective_from ? Carbon::parse($this->effective_from)->format('d M Y') : '-';
        if ((int) $this->is_perpetual === 1 || $this->validity_mode === self::VALIDITY_PERPETUAL) {
            return "Mulai {$from} s/d Selalu Aktif";
        }

        $until = $this->effective_until ? Carbon::parse($this->effective_until)->format('d M Y') : '-';
        return "{$from} s/d {$until}";
    }

    public function getTargetLabelAttribute()
    {
        if ($this->target_audience === 'DEPARTMENT' && is_array($this->target_departments) && count($this->target_departments) > 0) {
            return 'Dept: ' . implode(', ', $this->target_departments);
        }
        return 'Semua Karyawan (ALL)';
    }
}
