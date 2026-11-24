<?php

namespace App\Models\HCGA;

use Illuminate\Database\Eloquent\Model;

class StorageReadReceipt extends Model
{
    protected $table = 'hcga_storage_read_receipts';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'document_id'     => 'integer',
        'version_id'      => 'integer',
        'announcement_id' => 'integer',
        'user_id'         => 'integer',
    ];

    public function document()
    {
        return $this->belongsTo(StorageDocument::class, 'document_id', 'id');
    }

    public function version()
    {
        return $this->belongsTo(StorageDocumentVersion::class, 'version_id', 'id');
    }
}
