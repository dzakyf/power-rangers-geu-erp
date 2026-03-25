<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterAuditCategory extends Model
{
    protected $table = "master_audit_category";
    protected $guarded = ['id'];
    public $timestamps = false;
}
