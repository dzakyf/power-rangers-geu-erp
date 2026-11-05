<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnnexVII extends Model
{
    protected $table = 'annex_vii';

    // Allow mass assignment for these fields (keep consistent with SQL below)
    protected $fillable = [
        'Annex_No',
        'SI_No', 'Order_No', 'Qty1', 'Qty2', 'SI_Date',
        'Shipper', 'arranger_address', 'arranger_contact', 'arranger_tel', 'arranger_email',
        'Consignee', 'consignee_email', 'consignee_telp', 'actual_quantity',
        'ETD', 'ETA', 'Stuffing', 'POL', 'POD', 'PEB_Date', 'PEB_No', 'BL_No', 'HS_Code',
        'Contract_No', 'Delivery_Terms', 'Voyage', 'Destination_Port', 'Notify',
        'carrier1_name','carrier1_address','carrier1_phone','carrier1_email','carrier1_date','carrier1_licence',
        'carrier2_name','carrier2_address','carrier2_contact','carrier2_tel','carrier2_fax','carrier2_email','carrier2_date','carrier2_licence',
        'carrier3_name','carrier3_address','carrier3_contact','carrier3_tel','carrier3_fax','carrier3_email','carrier3_transport','carrier3_date','carrier3_licence',
        'Container1','Container2','Container3','Container4','Container5',
        'waste_generator_name','waste_generator_address','recovery_address','recovery_contact','recovery_tel','recovery_email',
        'recovery_operation','Product_Name','basel_annex','oecd_code','ec_list','national_code',
        'export_country','transit_country','import_destination',
        'declarant_name','declarant_date',
        'created_by','updated_by'
    ];

    public $timestamps = true;
}
