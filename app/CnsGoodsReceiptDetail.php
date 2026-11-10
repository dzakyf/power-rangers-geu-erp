<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CnsGoodsReceiptDetail extends Model
{
    protected $table = 'cns_goods_receipt_details';

    protected $fillable = [
        'goods_receipt_id',
        'po_dtl_id',
        'item_code',
        'item_name',
        'category',
        'unit',
        'qty_ordered',
        'qty_received',
        'notes',
    ];

    protected $casts = [
        'qty_ordered'  => 'float',
        'qty_received' => 'float',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(CnsGoodsReceipt::class, 'goods_receipt_id');
    }
}
