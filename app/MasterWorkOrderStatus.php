<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterWorkOrderStatus extends Model
{
    protected $table = 'master_work_order_status';
    protected $primaryKey = 'ID';
    const CREATED_AT = 'Created_At';
    const UPDATED_AT = 'Updated_At';

    protected $fillable = [
        'Name', 'Code', 'Description', 'Is_Active',
    ];
}
