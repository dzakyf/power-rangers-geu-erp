<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentRequisition extends Model
{
    protected $table = 'hr_recruitment_requisitions';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $guarded = [];
    
    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_CLOSED = 'CLOSED';
    const STATUS_CANCELLED = 'CANCELLED';
    
    public static function generateRequisitionNo(): string
    {
        $year = now()->format('Y');
        $last = static::where('requisition_no', 'like', "REQ-{$year}-%")
                     ->orderBy('id', 'desc')
                     ->first();
        if ($last) {
            $lastNo = (int) str_replace("REQ-{$year}-", '', $last->requisition_no);
            $newNo = str_pad($lastNo + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNo = '0001';
        }
        return "REQ-{$year}-{$newNo}";
    }
}