<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CertificateOfOrigin extends Model
{
    protected $table = 'certificate_of_origin';
    protected $primaryKey = 'ID';
    protected $guarded = ['ID'];
    public $timestamps = false;

    public function details()
    {
        return $this->hasMany(CertificateOfOriginDtl::class, 'COO_No', 'COO_No');
    }
}
