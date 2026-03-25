<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterAuditTemuan extends Model
{
    protected $table = "master_audit_temuan";
    protected $guarded = ['id'];
    public $timestamps = false;
}
