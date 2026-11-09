<?php

namespace App\Models\HCGA;

use Illuminate\Database\Eloquent\Model;

class CompanyGuideline extends Model
{
    protected $table = 'hcga_company_guidelines';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'type',
        'title',
        'code',
        'summary',
        'content',
        'icon',
        'badge_color',
        'attachment_file',
        'attachment_name',
        'sort_order',
        'is_active',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'integer',
    ];

    const TYPE_VISI = 'VISI';
    const TYPE_MISI = 'MISI';
    const TYPE_CORE_VALUE = 'CORE_VALUE';
    const TYPE_PERATURAN = 'PERATURAN';

    public static function getTypes()
    {
        return [
            self::TYPE_VISI => 'Visi Perusahaan',
            self::TYPE_MISI => 'Misi Perusahaan',
            self::TYPE_CORE_VALUE => 'Core Value',
            self::TYPE_PERATURAN => 'Peraturan Perusahaan',
        ];
    }
}

