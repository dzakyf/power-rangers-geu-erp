<?php

namespace App;

use App\Models\HCGA\StorageDocument;
use App\Models\HCGA\StorageDocumentVersion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class HcgaNewsAnnouncement extends Model
{
    protected $table = 'hcga_news_announcements';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'storage_document_id'  => 'integer',
        'storage_version_id'   => 'integer',
        'is_final'             => 'integer',
        'is_pinned'            => 'integer',
        'is_perpetual'         => 'integer',
        'require_read_receipt' => 'integer',
        'view_count'           => 'integer',
        'target_departments'   => 'array',
    ];

    public function storageDocument()
    {
        return $this->belongsTo(StorageDocument::class, 'storage_document_id', 'id');
    }

    public function storageVersion()
    {
        return $this->belongsTo(StorageDocumentVersion::class, 'storage_version_id', 'id');
    }

    public function isFromStorage()
    {
        return !empty($this->storage_document_id);
    }

    /**
     * Scope for published / final announcements
     */
    public function scopePublished($query)
    {
        return $query->where('is_final', 1);
    }

    /**
     * Scope for currently effective items (hide SCHEDULED and EXPIRED)
     */
    public function scopeCurrentlyEffective($query, $today = null)
    {
        $today = $today ?: Carbon::today()->toDateString();

        return $query->where(function ($q) use ($today) {
            $q->whereNull('effective_from')
              ->orWhere('effective_from', '<=', $today);
        })->where(function ($q) use ($today) {
            $q->where('is_perpetual', 1)
              ->orWhereNull('is_perpetual')
              ->orWhereNull('effective_until')
              ->orWhere('effective_until', '>=', $today);
        });
    }

    /**
     * Scope for audience filtering (supports ALL, INTERNAL, HCGA, and DEPARTMENT)
     */
    public function scopeForAudience($query, $isHcga = false, $userDepartment = null)
    {
        if ($isHcga) {
            return $query;
        }

        $dept = strtoupper(trim((string) $userDepartment));

        return $query->where(function ($q) use ($dept) {
            $q->where('target_audience', 'ALL')
              ->orWhere('target_audience', 'INTERNAL')
              ->orWhereNull('target_audience');

            if ($dept !== '') {
                $escaped = addslashes(json_encode($dept));
                $q->orWhere(function ($sub) use ($escaped) {
                    $sub->where('target_audience', 'DEPARTMENT')
                        ->where('target_departments', 'LIKE', "%{$escaped}%");
                });
            }
        });
    }

    /**
     * Check whether a given user (by department and HCGA flag) can access this announcement
     */
    public function isAccessibleBy($isHcga = false, $userDepartment = null, $today = null)
    {
        if ($isHcga) {
            return true;
        }

        if ((int) $this->is_final !== 1) {
            return false;
        }

        $today = $today ?: Carbon::today()->toDateString();
        if (!empty($this->effective_from) && $this->effective_from > $today) {
            return false;
        }
        if ((int) $this->is_perpetual !== 1 && !empty($this->effective_until) && $this->effective_until < $today) {
            return false;
        }

        $audience = $this->target_audience ?: 'ALL';
        if (in_array($audience, ['ALL', 'INTERNAL'], true)) {
            return true;
        }

        if ($audience === 'DEPARTMENT') {
            $dept = strtoupper(trim((string) $userDepartment));
            $allowed = is_array($this->target_departments)
                ? array_map(function ($d) {
                    return strtoupper(trim((string) $d));
                }, $this->target_departments)
                : [];
            return $dept !== '' && in_array($dept, $allowed, true);
        }

        return false;
    }

    public function getValidityDisplayAttribute()
    {
        if (empty($this->effective_from) && (int) $this->is_perpetual === 1) {
            return null;
        }
        $from = $this->effective_from ? Carbon::parse($this->effective_from)->format('d M Y') : '-';
        if ((int) $this->is_perpetual === 1 || empty($this->effective_until)) {
            return "Berlaku sejak {$from} (Selalu Aktif)";
        }
        $until = Carbon::parse($this->effective_until)->format('d M Y');
        return "Berlaku {$from} s/d {$until}";
    }
}
