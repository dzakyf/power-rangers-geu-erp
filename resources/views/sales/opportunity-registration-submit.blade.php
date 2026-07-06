<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

try {
    $req = request();

    $data = [];
    $data['lead_id'] = request()->input('lead_id', null);
    $data['register_as'] = request()->input('Register_As', request()->input('register_as'));

    // Individual fields
    $data['full_name'] = request()->input('Full_Name');

    $username = auth()->check() ? auth()->user()->username : str_replace(' ', '_', strtolower($data['full_name']));
    if (!auth()->check()) {
        $cekUser = DB::table('users')->get();
        foreach ($cekUser as $key => $value) {
            if ($value->username == $username) {
                $username = $username . rand(100, 999);
            }
        }
    }
    $data['contact'] = request()->input('Contact', '');
    $data['email'] = request()->input('Email');

    $prefix = "I_";
    if ($data['register_as'] === 'company') {
        $prefix = "C_";
    }
    $addr_detail = $req->input($prefix . 'Address_Detail');
    $addr_rt = $req->input($prefix . 'Address_RT');
    $addr_rw = $req->input($prefix . 'Address_RW');
    $addr_ward = $req->input($prefix . 'Address_Ward');
    $addr_subdistrict = $req->input($prefix . 'Address_Subdistrict');
    $addr_city = $req->input($prefix . 'Address_City');
    $addr_province = $req->input($prefix . 'Address_Province');
    $addr_postal = $req->input($prefix . 'Address_Postal');

    $data['address'] = $addr_detail;
    $data['pi_rt'] = $addr_rt;
    $data['pi_rw'] = $addr_rw;
    $data['pi_ward'] = $addr_ward;
    $data['pi_subdistrict'] = $addr_subdistrict;
    $data['pi_city'] = $addr_city;
    $data['pi_province'] = $addr_province;
    $data['pi_postal_code'] = $addr_postal;


    $data['gender'] = request()->input('Gender');
    $data['highest_education'] = request()->input('Last_Education');
    // $data['source_channels'] = array_filter([request()->input('Source_Instagram'), request()->input('Source_Facebook'), request()->input('Source_TikTok'), request()->input('Source_Website'), request()->input('Source_Event')])[0] ?? null;
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

    // Recommendation to be approved
    $data['is_recommended_to_be_approved'] = '3'; // In Consideration
    if (in_array($data['sources_of_funds'], ["savings", "business"])) {
        $data['is_recommended_to_be_approved'] = '1'; // Yes
    } else if (in_array($data['sources_of_funds'], ["loan", "pension"])) {
        $data['is_recommended_to_be_approved'] = '2'; // No
    }

    // Partnership
    $data['warehouse_count'] = request()->input('Warehouse_Count');
    $data['motivation'] = request()->input('Motivation');

    // Location
    $data['province'] = request()->input('Province');
    $data['city'] = request()->input('City');
    $data['location_status'] = request()->input('Location_Status');

    // GPS coordinates
    $data["latitude"] = request()->input("Latitude") ? request()->input("Latitude") : request()->input("Company_Latitude");
    $data["longitude"] = request()->input("Longitude") ?  request()->input("Longitude") : request()->input("Company_Longitude");

    // Questions
    for ($i = 1; $i <= 6; $i++) {
        $data['q' . $i] = request()->input('Q' . $i);
    }

    $packageMapper = [
        "option1" => 'mobil',
        "option2" => 'booth'
    ];

    $package = (!empty($packageInput) && isset($packageMapper[$packageInput]))
        ? $packageMapper[$packageInput]
        : null;

    $data['package_option'] = $package;
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
    $data['pic_phone'] = request()->input('PIC_Phone', '');
    $data['pic_email'] = request()->input('PIC_Email');
    if (empty($data['email']) && !empty($data['pic_email'])) {
        $data['email'] = request()->input('PIC_Email');
    }

    // Company financials
    $data['company_scale'] = request()->input('Company_Scale');
    $data['estimated_revenue'] = request()->input('Estimated_Revenue');
    $data['company_funds'] = json_encode(array_filter([request()->input('Company_Fund_Business'), request()->input('Company_Fund_Investor'), request()->input('Company_Fund_Loan'), request()->input('Company_Fund_CSR'), request()->input('Company_Fund_Private')]));

    $data['company_warehouse_count'] = request()->input('Company_Warehouse_Count');
    $data['company_experience'] = request()->input('Company_Experience');
    $data['company_has_location'] = request()->input('Company_Has_Location');
    $data['location_types'] = json_encode(array_filter([request()->input('Loc_Warehouse'), request()->input('Loc_Store'), request()->input('Loc_Own'), request()->input('Loc_Rent')]));

    // Questioner Part 6 
    for ($i = 1; $i < 5; $i++) {
        $data['c_p6q' . $i] = $req->input('P6Q' . $i);

        if ($i == 1 || $i == 3) {
            $data['c_p6q' . $i . 'a1'] = $req->input('P6Q' . $i . 'A1');
        }
    }

    for ($i = 1; $i <= 6; $i++) {
        $data['c_q' . $i] = request()->input('C_Q' . $i);
    }

    $data['company_package'] = request()->input('Company_Package');
    $data['company_documents'] = json_encode(array_filter([request()->input('C_Doc_Akta'), request()->input('C_Doc_NIB'), request()->input('C_Doc_NPWP'), request()->input('C_Doc_Others')]));

    $data['created_at'] = Carbon::now();

    $id = DB::table('opportunity_partner')->insertGetId($data);


    foreach (json_decode($data['supporting_documents']) as $key => $value) {
        $filePath = request()->input('Doc_' . $value . '_File') ?? "";
        $ext = explode('.', $filePath);

        DB::table('opportunity_partner_file')->insert([
            'lead_id'        => $data['lead_id'],
            'opportunity_id' => $id,
            'type'           => $value,
            'file_type'      => $ext[count($ext) - 1],
            'file_path'      => $filePath,
            'created_at'     => now(),
            'created_by'     => $username,
        ]);

        DB::table('meta_files')->insert([
            'Filename'    => basename($filePath),
            'Path'        => $filePath,
            'Extension'   => $ext[count($ext) - 1],
            'Description' => $value,
            'Created_At'  => now(),
            'Topic'       => 'ONBOARDING',
            'Map_To'      => $id,
        ]);
    }

    foreach (json_decode($data['company_documents']) as $key => $value) {
        $filePath = request()->input('C_Doc_' . $value . '_File') ?? "";
        $ext = explode('.', $filePath);

        DB::table('opportunity_partner_file')->insert([
            'lead_id'        => $data['lead_id'],
            'opportunity_id' => $id,
            'type'           => $value,
            'file_type'      => $ext[count($ext) - 1],
            'file_path'      => $filePath,
            'created_at'     => now(),
            'created_by'     => $username,
        ]);

        DB::table('meta_files')->insert([
            'Filename'    => basename($filePath),
            'Path'        => $filePath,
            'Extension'   => $ext[count($ext) - 1],
            'Description' => $value,
            'Created_At'  => now(),
            'Topic'       => 'onboarding',
            'Map_To'      => $id,
        ]);
    }

    $approvalController = app(\App\Http\Controllers\ApprovalController::class);
    $approvalController->createApprovalRequest(
        'ONBOARDING',
        null, // no requester id, because no user account created yet.
        'NEED_APPROVAL',
        ['Opportunity_Partner_Id' => $id, 'Map_To' => $id]
    );


    echo "success";
} catch (\Exception $e) {
    echo 'failed ' . $e->getMessage();
    Log::error($e);
}
