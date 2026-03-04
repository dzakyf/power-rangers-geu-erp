<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OpportunityPartnerFacility extends Model
{
    protected $primaryKey = "ID";
    protected $table = "opportunity_partner_facility";
    protected $guarded = [];
    public $timestamps = false;
}
