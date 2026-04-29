<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SustainabilityDeclaration extends Model
{
    protected $table = 'sustainability_declarations';
    protected $primaryKey = 'ID';
    protected $guarded = ['ID'];
    public $timestamps = false;
}
