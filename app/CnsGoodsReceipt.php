<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CnsGoodsReceipt extends Model
{
    protected $table = 'cns_goods_receipts';

    protected $fillable = [
        'gr_no',
        'po_no',
        'receipt_date',
        'delivery_note_no',
        'driver_name',
        'vehicle_no',
        'received_by',
        'status',
        'approved_by',
        'approved_at',
        'attachment_path',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'approved_at'  => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(CnsGoodsReceiptDetail::class, 'goods_receipt_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrderCNS::class, 'po_no', 'PO_No');
    }

    public function inventoryMutations()
    {
        return $this->hasMany(CnsInventoryNonUco::class, 'goods_receipt_id');
    }
}
