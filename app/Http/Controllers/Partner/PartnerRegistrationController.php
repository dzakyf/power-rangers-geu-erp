<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ApprovalController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Unified partner-registration submit pipeline.
 *
 * Serves BOTH entry points with the same shared form data contract:
 *   - internal (sales/admin, authenticated): POST /sales-opportunity-registration-submit
 *   - public (lead with one-time token):       POST /partner-registration-submit
 *
 * Response contract is plain text and unchanged from the legacy handlers:
 *   "success", "failed <message>", "error: <message>".
 */
class PartnerRegistrationController extends Controller
{
    public function submit(Request $request)
    {
        $token = $request->input('registration_token');

        try {
            return DB::transaction(function () use ($request, $token) {
                // ---- One-time token guard (public path only) -------------
                if ($token) {
                    $opportunity = DB::table('opportunity')
                        ->where('registration_token', $token)
                        ->where(function ($q) {
                            $q->whereNull('token_expires_at')
                              ->orWhere('token_expires_at', '>=', now());
                        })
                        ->first();

                    if (!$opportunity) {
                        return 'error: Link tidak valid atau sudah kadaluarsa';
                    }

                    DB::table('opportunity')->where('ID', $opportunity->ID)->update([
                        'registration_token' => null,
                        'token_expires_at'   => null,
                    ]);
                }

                // ---- Data mapping ---------------------------------------
                $data = [];
                $data['lead_id'] = $request->input('lead_id', null);
                $data['register_as'] = $request->input('Register_As', $request->input('register_as'));

                $data['full_name'] = $request->input('Full_Name');

                $username = auth()->check() ? auth()->user()->username : str_replace(' ', '_', strtolower((string) $data['full_name']));
                if (!auth()->check()) {
                    while (DB::table('users')->where('username', $username)->exists()) {
                        $username = $username . rand(100, 999);
                    }
                }

                $data['contact'] = $request->input('Contact', '');
                $data['email'] = $request->input('Email');

                $prefix = "I_";
                if ($data['register_as'] === 'company') {
                    $prefix = "C_";
                }

                $data['address'] = $request->input($prefix . 'Address_Detail');
                $data['pi_rt'] = $request->input($prefix . 'Address_RT');
                $data['pi_rw'] = $request->input($prefix . 'Address_RW');
                $data['pi_ward'] = $request->input($prefix . 'Address_Ward');
                $data['pi_subdistrict'] = $request->input($prefix . 'Address_Subdistrict');
                $data['pi_city'] = $request->input($prefix . 'Address_City');
                $data['pi_province'] = $request->input($prefix . 'Address_Province');
                $data['pi_postal_code'] = $request->input($prefix . 'Address_Postal');

                $data['gender'] = $request->input('Gender');
                $data['highest_education'] = $request->input('Last_Education');
                $data['source_channels'] = $request->input('Source') ?? null;
                $data['pic'] = $request->input('PIC');

                $data['employment_status'] = $request->input('Employment_Status');
                $data['company_name'] = $request->input('Company_Name');
                $data['business_field'] = $request->input('Business_Field');
                $data['length_of_work'] = $request->input('Length_of_Work');
                $data['length_of_work_unit'] = $request->input('Length_of_Work_Unit');
                $data['position'] = $request->input('Position');
                $data['company_address'] = $request->input('Company_Address');

                $data['gross_monthly'] = $request->input('Gross_Monthly');
                $data['gross_annual'] = $request->input('Gross_Annual');

                $rawFunds = array_filter([
                    $request->input('Source_Personal_Savings'),
                    $request->input('Source_Inheritance'),
                    $request->input('Source_Business_Income'),
                    $request->input('Source_Loan'),
                    $request->input('Source_Pension'),
                ]);
                $data['sources_of_funds'] = json_encode($rawFunds);

                $data['is_recommended_to_be_approved'] = '3'; // In Consideration
                if (in_array('loan', $rawFunds) || in_array('pension', $rawFunds)) {
                    $data['is_recommended_to_be_approved'] = '2';
                } elseif (in_array('savings', $rawFunds) || in_array('business', $rawFunds)) {
                    $data['is_recommended_to_be_approved'] = '1';
                }

                $data['warehouse_count'] = $request->input('Warehouse_Count');
                $data['motivation'] = $request->input('Motivation');

                $data['province'] = $request->input('Province');
                $data['city'] = $request->input('City');
                $data['location_status'] = $request->input('Location_Status');

                $data['latitude'] = $request->input('Latitude') ?: $request->input('Company_Latitude');
                $data['longitude'] = $request->input('Longitude') ?: $request->input('Company_Longitude');

                for ($i = 1; $i <= 6; $i++) {
                    $data['q' . $i] = $request->input('Q' . $i);
                }

                // Form radios send the literal package code ("mobil"/"booth").
                $data['package_option'] = $request->input('Package');
                $data['supporting_documents'] = json_encode(array_filter([
                    $request->input('Doc_KTP'),
                    $request->input('Doc_NPWP'),
                    $request->input('Doc_Others'),
                ]));

                $data['company_official_name'] = $request->input('Company_Official_Name');
                $data['legal_forms'] = json_encode(array_filter([
                    $request->input('Legal_PT'),
                    $request->input('Legal_CV'),
                    $request->input('Legal_Coop'),
                    $request->input('Legal_Foundation'),
                    $request->input('Legal_Other'),
                ]));
                $data['primary_business'] = $request->input('Primary_Business');
                $data['year_established'] = $request->input('Year_Established');
                $data['head_office_address'] = $request->input('Head_Office_Address');
                $data['company_website'] = $request->input('Company_Website');
                $data['company_tax_id'] = $request->input('Company_Tax_ID');
                $data['company_nib'] = $request->input('Company_NIB');

                $data['pic_name'] = $request->input('PIC_Name');
                $data['pic_position'] = $request->input('PIC_Position');
                $data['pic_phone'] = $request->input('PIC_Phone', '');
                $data['pic_email'] = $request->input('PIC_Email');

                $data['company_scale'] = $request->input('Company_Scale');
                $data['estimated_revenue'] = $request->input('Estimated_Revenue');

                $rawCompanyFunds = array_filter([
                    $request->input('Company_Fund_Business'),
                    $request->input('Company_Fund_Investor'),
                    $request->input('Company_Fund_Loan'),
                    $request->input('Company_Fund_CSR'),
                    $request->input('Company_Fund_Private'),
                ]);
                $data['company_funds'] = json_encode($rawCompanyFunds);

                if (in_array('loan', $rawCompanyFunds)) {
                    $data['is_recommended_to_be_approved'] = '2';
                }
                if (empty($data['email']) && !empty($data['pic_email'])) {
                    $data['email'] = $request->input('PIC_Email');
                }

                $data['company_warehouse_count'] = $request->input('Company_Warehouse_Count');
                $data['company_experience'] = $request->input('Company_Experience');
                $data['company_has_location'] = $request->input('Company_Has_Location');
                $data['location_types'] = json_encode(array_filter([
                    $request->input('Loc_Warehouse'),
                    $request->input('Loc_Store'),
                    $request->input('Loc_Own'),
                    $request->input('Loc_Rent'),
                ]));

                for ($i = 1; $i < 5; $i++) {
                    $data['c_p6q' . $i] = $request->input($prefix . 'P6Q' . $i);
                    if ($i == 1 || $i == 3) {
                        $data['c_p6q' . $i . 'a1'] = $request->input($prefix . 'P6Q' . $i . 'A1');
                    }
                }

                for ($i = 1; $i <= 6; $i++) {
                    $data['c_q' . $i] = $request->input('C_Q' . $i);
                }

                $data['company_package'] = $request->input('Company_Package');
                $data['company_documents'] = json_encode(array_filter([
                    $request->input('C_Doc_Akta'),
                    $request->input('C_Doc_NIB'),
                    $request->input('C_Doc_NPWP'),
                    $request->input('C_Doc_Others'),
                ]));

                $data['created_at'] = Carbon::now();

                // user_row_id is intentionally not set here: it is backfilled
                // by OnboardingController when the onboarding approval creates
                // the partner's user account.
                $id = DB::table('opportunity_partner')->insertGetId($data);

                $this->storeDocuments($data, $id, $username);

                // Requester_Id: the authenticated submitter for the internal
                // flow; null (coerced to 0 by MySQL) for the public flow where
                // no user account exists yet.
                // Requester_Id: authenticated submitter for the internal flow;
                // '0' (system placeholder) for the public flow where no user
                // account exists yet - the column is NOT NULL.
                $requesterId = auth()->check() ? (string) auth()->id() : '0';

                app(ApprovalController::class)->createApprovalRequest(
                    'ONBOARDING',
                    $requesterId,
                    'NEED_APPROVAL',
                    ['Opportunity_Partner_Id' => $id, 'Map_To' => $id]
                );

                return 'success';
            });
        } catch (\Exception $e) {
            Log::error($e);
            return 'failed ' . $e->getMessage();
        }
    }

    private function storeDocuments(array $data, int $opportunityId, string $username): void
    {
        $groups = [
            'supporting_documents' => 'Doc_',
            'company_documents'    => 'C_Doc_',
        ];

        foreach ($groups as $dataKey => $inputPrefix) {
            $docs = json_decode($data[$dataKey] ?? '[]', true) ?: [];

            foreach ($docs as $type) {
                $filePath = request()->input($inputPrefix . $type . '_File') ?? '';
                $ext = explode('.', $filePath);
                $extension = count($ext) > 1 ? end($ext) : '';

                DB::table('opportunity_partner_file')->insert([
                    'lead_id'        => $data['lead_id'],
                    'opportunity_id' => $opportunityId,
                    'type'           => $type,
                    'file_type'      => $extension,
                    'file_path'      => $filePath,
                    'created_at'     => now(),
                    'created_by'     => $username,
                ]);

                DB::table('meta_files')->insert([
                    'Filename'    => basename($filePath),
                    'Path'        => $filePath,
                    'Extension'   => $extension,
                    'Description' => $type,
                    'Created_At'  => now(),
                    'Topic'       => $dataKey === 'company_documents' ? 'onboarding' : 'ONBOARDING',
                    'Map_To'      => $opportunityId,
                ]);
            }
        }
    }
}
