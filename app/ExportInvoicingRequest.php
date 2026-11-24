<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExportInvoicingRequest extends Model
{
    const STATUS_READY = 'Ready to Submit';
    const STATUS_PENDING_FAT = 'pending FAT';
    const STATUS_APPROVED = 'Approved';
    const STATUS_REJECTED = 'Rejected';

    protected $table = 'export_invoicing_requests';

    protected $fillable = [
        'vessel_schedule_id',
        'order_no',
        'stuffing_no',
        'contract_no',
        'packing_list_no',
        'buyer_name',
        'status',
        'contract_file_path',
        'contract_file_name',
        'packing_list_file_path',
        'packing_list_file_name',
        'cns_notes',
        'submitted_by',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'finance_notes',
        'rejection_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function vesselSchedule()
    {
        return $this->belongsTo(VesselSchedule::class, 'vessel_schedule_id');
    }
}
