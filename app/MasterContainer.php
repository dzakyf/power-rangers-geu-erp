<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterContainer extends Model
{
    protected $table = 'master_container';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = ['Name', 'Volume', 'Warehouse_ID', 'Note', 'Status', 'Created_By', 'Created_At'];
}
