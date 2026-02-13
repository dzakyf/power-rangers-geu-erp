<?php 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
try {
    
    $data = [];
    $data['lead_id'] = request()->input('lead_id', null);
    $data['register_as'] = request()->input('Register_As', request()->input('register_as'));

    // Individual fields
    $data['full_name'] = request()->input('Full_Name');
    $data['contact'] = request()->input('Contact');
    $data['email'] = request()->input('Email');
    $data['address'] = request()->input('Address');
    $data['gender'] = request()->input('Gender');
    $data['highest_education'] = request()->input('Last_Education');
    // $data['source_channels'] = array_filter([request()->input('Source_Instagram'), request()->input('Source_Facebook'), request()->input('Source_Tiktok'), request()->input('Source_Website'), request()->input('Source_Event')])[0] ?? null;
    $data['source_channels'] = request()->input('Source') ?? null;
    $data['pic'] = request()->input('PIC');

    // Employment
    $data['employment_status'] = request()->input('Employment_Status');
    $data['company_name'] = request()->input('Company_Name');
    $data['business_field'] = request()->input('Business_Field');
    $data['length_of_work'] = request()->input('Length_of_Work');
    $data['length_of_work_unit'] = request()->input('Length_of_Work_Unit');
    $data['position'] = request()->input('Position');
    $data['company_address'] = request()->input('Company_Address');

    // Financial
    $data['gross_monthly'] = request()->input('Gross_Monthly');
    $data['gross_annual'] = request()->input('Gross_Annual');
    $data['sources_of_funds'] = json_encode(array_filter([request()->input('Source_Personal_Savings'), request()->input('Source_Inheritance'), request()->input('Source_Business_Income'), request()->input('Source_Loan'), request()->input('Source_Pension')]));

    // Partnership
    $data['warehouse_count'] = request()->input('Warehouse_Count');
    $data['motivation'] = request()->input('Motivation');

    // Location
    $data['province'] = request()->input('Province');
    $data['city'] = request()->input('City');
    $data['location_status'] = request()->input('Location_Status');

    // Questions
    for ($i=1;$i<=6;$i++) {
        $data['q'.$i] = request()->input('Q'.$i);
    }
    
    $data['package_option'] = request()->input('Package');
    $data['supporting_documents'] = json_encode(array_filter([request()->input('Doc_KTP'), request()->input('Doc_NPWP'), request()->input('Doc_Others')]));

    // Company-specific
    $data['company_official_name'] = request()->input('Company_Official_Name');
    $data['legal_forms'] = json_encode(array_filter([request()->input('Legal_PT'), request()->input('Legal_CV'), request()->input('Legal_Coop'), request()->input('Legal_Foundation'), request()->input('Legal_Other')]));
    $data['primary_business'] = request()->input('Primary_Business');
    $data['year_established'] = request()->input('Year_Established');
    $data['head_office_address'] = request()->input('Head_Office_Address');
    $data['company_website'] = request()->input('Company_Website');
    $data['company_tax_id'] = request()->input('Company_Tax_ID');
    $data['company_nib'] = request()->input('Company_NIB');
    
    // Company PIC
    $data['pic_name'] = request()->input('PIC_Name');
    $data['pic_position'] = request()->input('PIC_Position');
    $data['pic_phone'] = request()->input('PIC_Phone');
    $data['pic_email'] = request()->input('PIC_Email');
    
    // Company financials
    $data['company_scale'] = request()->input('Company_Scale');
    $data['estimated_revenue'] = request()->input('Estimated_Revenue');
    $data['company_funds'] = json_encode(array_filter([request()->input('Company_Fund_Business'), request()->input('Company_Fund_Investor'), request()->input('Company_Fund_Loan'), request()->input('Company_Fund_CSR'), request()->input('Company_Fund_Private')]));
    
    $data['company_warehouse_count'] = request()->input('Company_Warehouse_Count');
    $data['company_experience'] = request()->input('Company_Experience');
    $data['company_has_location'] = request()->input('Company_Has_Location');
    $data['location_types'] = json_encode(array_filter([request()->input('Loc_Warehouse'), request()->input('Loc_Store'), request()->input('Loc_Own'), request()->input('Loc_Rent')]));

    for ($i=1;$i<=6;$i++) {
        $data['c_q'.$i] = request()->input('C_Q'.$i);
    }
    
    $data['company_package'] = request()->input('Company_Package');
    $data['company_documents'] = json_encode(array_filter([request()->input('C_Doc_Akta'), request()->input('C_Doc_NIB'), request()->input('C_Doc_NPWP'), request()->input('C_Doc_Others')]));
    
    $id = DB::table('opportunity_partner')->insertGetId($data);

    echo "success";
} catch (\Exception $e) {
    echo 'failed '.$e->getMessage();
}
?>