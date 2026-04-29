<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CertificateOfOriginDtl extends Model
{
    protected $table = 'certificate_of_origin_dtl';
    protected $guarded = ['ID'];
    public $timestamps = false;
}
