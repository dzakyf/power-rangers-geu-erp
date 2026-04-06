<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterAuditScore extends Model
{
    protected $table = "master_audit_score";
    protected $fillable = [
        "id",
        "score_range",
        "status",
    ];
    public $timestamps = false;
}
