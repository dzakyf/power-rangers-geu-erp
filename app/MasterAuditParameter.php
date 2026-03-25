<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterAuditParameter extends Model
{
    protected $table = "master_audit_parameter";
    protected $guarded = ['id'];
    public $timestamps = false;
}
