<?php
$LSP = 'GEU-HO';
$pic = DB::table('users')->where('Location', $LSP)->where('Priv_ID', 'SALES')->get();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Form - GEU</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/LineIcons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/viewer.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/icofont.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/calendar.css') }}" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/responsive.css') }}" rel="stylesheet">
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon2.png') }}">
    <link href="{{ asset('ajaxloader/ajaxloader.css') }}" type="text/css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{ asset('blockui.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('jquery.coolautosuggest.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('jquery.coolfieldset.css') }}" />

    <script  src="{{ asset('jquery2.min.js')}}"></script>
    <script  src="{{ asset('timeline.min.js')}}"></script>

    <script>
    function LoadM(DivID,PathID){

        $("#"+DivID).ajaxloader(PathID);	
        
    }

    </script>


    <link rel="STYLESHEET" type="text/css" href="{{ asset('dhtmlx/dhtmlx.css') }}">
    <script  src="{{ asset('dhtmlx/dhtmlx.js')}}"></script>



    <script type="text/javascript"  src="{{ asset('./jquery.confirm.js') }}"></script>
    <script type="text/javascript" src="http://code.jquery.com/jquery-migrate-1.0.0.js"></script>

    <link rel="stylesheet" type="text/css"  href="{{ asset('form_input.css') }}" />
    <link rel="stylesheet" type="text/css"  href="{{ asset('./jquery-confirm.css') }}" />
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500" rel="stylesheet">

    <script  src="{{ asset('vault.js?v=3.0.0')}}"></script>
    <link rel="stylesheet" type="text/css"  href="{{ asset('vault.css') }}" />
    <link rel="stylesheet" type="text/css"  href="{{ asset('css/timeline.min.css') }}" />

    <script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
    <script src="{{ asset('jquery.coolfieldset.js') }}"></script>
    <link rel="STYLESHEET" type="text/css" href="{{ asset('dhtmlx/diagram.css') }}">
    <link rel="STYLESHEET" type="text/css" href="{{ asset('dhtmlx/diagram-editor.css') }}">
    <link rel="STYLESHEET" type="text/css" href="{{ asset('dhtmlx/diagram.css') }}">

    <script  src="{{ asset('dhtmlx/diagram.js')}}"></script>
    <script  src="{{ asset('dhtmlx/diagram-editor.js')}}"></script>
    <script  src="{{ asset('highcharts/highcharts.js')}}"></script>
    <script  src="{{ asset('highcharts/modules/exporting.js')}}"></script>

    <script src="{{ asset('/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('/assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('/assets/js/bootstrap.min.js') }}"></script>

    <!-- Feather Icon JS -->
    <script src="{{ asset('/assets/js/feather.min.js') }}"></script>
    <!-- Gllery viewer JS -->
    <script src="{{ asset('/assets/js/viewer.min.js') }}"></script>

    <!-- ApexCharts JS -->
    <script type="text/javascript" src="jquery.mask.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('/assets/js/custom.js') }}"></script>
    <script src="{{ asset('/assets/js/apex-charts/apexcharts.min.js') }}"></script>

    <script src="{{ asset('jquery.ajaxloader.1.5.1.js') }}"></script>
    <script src="{{ asset('blockui.js') }}"></script>
</head>
<body class="p-md-5 p-0" style="background-color: #f8f9fa;">
    <div class="container-fluid p-md-5 p-0">
        <div class="card">
            <div class="card-body">
            
                <div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">INFORMASI PARTNER</div>
                
                <div id="ContentPages">
                    <div class="FormInput_Form_Content">
                        <div class="form-group">
                            <label>Daftar Sebagai</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Register_As" id="Register_As_Individual" value="individual" checked />
                                <label class="form-check-label" for="Register_As_Individual">Individu</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Register_As" id="Register_As_Company" value="company" />
                                <label class="form-check-label" for="Register_As_Company">Perusahaan</label>
                            </div>
                        </div>
                        </div>
                
                        <!-- Individual Section -->
                        <div id="IndividualDiv">
                            <div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">1. Informasi Pribadi</div>
                            <div class="form-group">
                                <label for="Full_Name">Nama Lengkap</label>
                                <input type="text" name="Full_Name" id="Full_Name" class="form-control" />
                            </div>
                            <div class="form-group">
                                <label for="Contact">Nomor Telepon</label>
                                <input type="text" name="Contact" id="Contact" class="form-control" />
                            </div>
                            <div class="form-group">
                                <label for="Email">Email</label>
                                <input type="text" name="Email" id="Email" class="form-control" />
                            </div>
                            <div class="form-group">
                                <label for="Address">Alamat Rumah</label>
                                <input type="text" name="Address" id="Address" class="form-control" />
                            </div>
                            <div class="form-group">
                                <label>Jenis Kelamin</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Gender" id="Gender_1" value="M" />
                                    <label class="form-check-label" for="Gender_1">Laki-laki</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Gender" id="Gender_2" value="F" />
                                    <label class="form-check-label" for="Gender_2">Perempuan</label>
                                </div>
                            </div>
            
                        <div class="form-group">
                                <label>Pendidikan Terakhir</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Last_Education" id="Last_Education_1" value="1" />
                                    <label class="form-check-label" for="Last_Education_1">< SMA</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Last_Education" id="Last_Education_2" value="2" />
                                    <label class="form-check-label" for="Last_Education_2">SMA</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Last_Education" id="Last_Education_3" value="3" />
                                    <label class="form-check-label" for="Last_Education_3">Diploma</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Last_Education" id="Last_Education_4" value="4" />
                                    <label class="form-check-label" for="Last_Education_4">Sarjana</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Last_Education" id="Last_Education_5" value="5" />
                                    <label class="form-check-label" for="Last_Education_5">Pascasarjana</label>
                                </div>
                            </div>
            
                        <div class="form-group">
                                <label>Bagaimana Anda mengetahui program kemitraan ini?</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Source_Instagram" id="Source_Instagram" value="Instagram" />
                                    <label class="form-check-label" for="Source_Instagram">Instagram</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Source_Facebook" id="Source_Facebook" value="Facebook" />
                                    <label class="form-check-label" for="Source_Facebook">Facebook</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Source_Tiktok" id="Source_Tiktok" value="Tiktok" />
                                    <label class="form-check-label" for="Source_Tiktok">Tiktok</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Source_Website" id="Source_Website" value="Website" />
                                    <label class="form-check-label" for="Source_Website">Website</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Source_Event" id="Source_Event" value="Event" />
                                    <label class="form-check-label" for="Source_Event">Pameran/Acara</label>
                                </div>
                            </div>
            
                        <div class="form-group">
                                <label for="PIC">PIC Penjualan</label>
                                <select name="PIC" id="PIC" class="form-control">
                                    <option value="">-- Pilih PIC Penjualan --</option>
                                    @foreach($pic as $p)
                                        <option value="{{ $p->username }}">{{ $p->Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                    
                            <div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">2. Informasi Pekerjaan</div>
                            <div class="form-group">
                                <label>Status Pekerjaan</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Employment_Status" id="Employment_Status_1" value="1" />
                                    <label class="form-check-label" for="Employment_Status_1">Karyawan Swasta</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Employment_Status" id="Employment_Status_2" value="2" />
                                    <label class="form-check-label" for="Employment_Status_2">Pegawai Negeri</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Employment_Status" id="Employment_Status_3" value="3" />
                                    <label class="form-check-label" for="Employment_Status_3">Pengusaha</label>
                                </div>
                            </div>
            
                            <div class="FormInput_left">Company Name</div>
                            <div class="FormInput_right">
                                <input type="text" name="Company_Name" id="Company_Name" class="form-control" />
                            </div>
                            <div class="FormInput_left">Length of Work</div>
                            <div class="FormInput_right">
                                <input type="text" name="Length_of_Work" id="Length_of_Work" class="form-control" />
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="radio" name="Length_of_Work_Unit" id="Length_of_Work_Unit_1" value="1" />
                                    <label class="form-check-label" for="Length_of_Work_Unit_1">Years</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Length_of_Work_Unit" id="Length_of_Work_Unit_2" value="2" />
                                    <label class="form-check-label" for="Length_of_Work_Unit_2">Months</label>
                                </div>
                            </div>
                            <div class="FormInput_left">Position</div>
                            <div class="FormInput_right"><input type="text" name="Position" id="Position" class="form-control" /></div>
                            <div class="FormInput_left">Company Address</div>
                            <div class="FormInput_right"><input type="text" name="Company_Address" id="Company_Address" class="form-control" /></div>
                            <div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">3. Financial Information</div>
                            <div class="FormInput_left">Total gross income (monthly)</div>
                            <div class="FormInput_right"><input type="text" name="Gross_Monthly" id="Gross_Monthly" class="form-control" /></div>
                            <div class="FormInput_left">Total gross income (annual)</div>
                            <div class="FormInput_right"><input type="text" name="Gross_Annual" id="Gross_Annual" class="form-control" /></div>
                            <div class="FormInput_left">Source of funds for partnership</div>
                            <div class="FormInput_right">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Source_Personal_Savings" id="Source_Personal_Savings" value="savings" />
                                    <label class="form-check-label" for="Source_Personal_Savings">Personal Savings</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Source_Inheritance" id="Source_Inheritance" value="inheritance" />
                                    <label class="form-check-label" for="Source_Inheritance">Inheritance</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Source_Business_Income" id="Source_Business_Income" value="business" />
                                    <label class="form-check-label" for="Source_Business_Income">Business Income</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Source_Loan" id="Source_Loan" value="loan" />
                                    <label class="form-check-label" for="Source_Loan">Loan</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Source_Pension" id="Source_Pension" value="pension" />
                                    <label class="form-check-label" for="Source_Pension">Pension</label>
                                </div>
                            </div>
                            <div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">4. Partnership Information</div>
                            <div class="FormInput_left">Do you currently run a partnership / business with warehouses? How many?</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Warehouse_Count" id="Warehouse_Count_0" value="0" />
                                <label class="form-check-label" for="Warehouse_Count_0">0</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Warehouse_Count" id="Warehouse_Count_1" value="1" />
                                <label class="form-check-label" for="Warehouse_Count_1">1</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Warehouse_Count" id="Warehouse_Count_2" value="2" />
                                <label class="form-check-label" for="Warehouse_Count_2">2</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Warehouse_Count" id="Warehouse_Count_3" value=">2" />
                                <label class="form-check-label" for="Warehouse_Count_3">>2</label>
                            </div>
                            <div class="FormInput_left">Motivation to join the partnership</div>
                        <div class="FormInput_right"><textarea name="Motivation" id="Motivation" rows="3" class="form-control"></textarea></div>
                            <div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">5. Location Information</div>
                            <div class="FormInput_left">Province</div>
                        <div class="FormInput_right"><select name="Province" id="Province" class="form-control"><option value="">-- Select --</option></select></div>
                            <div class="FormInput_left">City / District</div>
                        <div class="FormInput_right"><select name="City" id="City" class="form-control"><option value="">-- Select --</option></select></div>
                            <div class="FormInput_left">Location Status</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Location_Status" id="Location_Own" value="own" />
                                <label class="form-check-label" for="Location_Own">Own</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Location_Status" id="Location_Rent" value="rent" />
                                <label class="form-check-label" for="Location_Rent">Rent</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Location_Status" id="Location_NoTarget" value="notarget" />
                                <label class="form-check-label" for="Location_NoTarget">No target location</label>
                            </div>
                            <div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">Partnership Questions</div>
                            <div class="FormInput_left">1. Willing to comply with business standardization?</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Q1" id="Q1_Yes" value="Yes" />
                                <label class="form-check-label" for="Q1_Yes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Q1" id="Q1_No" value="No" />
                                <label class="form-check-label" for="Q1_No">No</label>
                            </div>
                            <div class="FormInput_left">2. Willing to support growth targets for Hub GEU Express?</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Q2" id="Q2_Yes" value="Yes" />
                                <label class="form-check-label" for="Q2_Yes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Q2" id="Q2_No" value="No" />
                                <label class="form-check-label" for="Q2_No">No</label>
                            </div>
                            <div class="FormInput_left">3. Aware that partnership carries business risk?</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Q3" id="Q3_Yes" value="Yes" />
                                <label class="form-check-label" for="Q3_Yes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Q3" id="Q3_No" value="No" />
                                <label class="form-check-label" for="Q3_No">No</label>
                            </div>
                            <div class="FormInput_left">4. Willing to follow investment rules?</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Q4" id="Q4_Yes" value="Yes" />
                                <label class="form-check-label" for="Q4_Yes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Q4" id="Q4_No" value="No" />
                                <label class="form-check-label" for="Q4_No">No</label>
                            </div>
                            <div class="FormInput_left">5. Willing not to run similar competing business?</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Q5" id="Q5_Yes" value="Yes" />
                                <label class="form-check-label" for="Q5_Yes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Q5" id="Q5_No" value="No" />
                                <label class="form-check-label" for="Q5_No">No</label>
                            </div>
                            <div class="FormInput_left">6. Willing to sign partnership contract?</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Q6" id="Q6_Yes" value="Yes" />
                                <label class="form-check-label" for="Q6_Yes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Q6" id="Q6_No" value="No" />
                                <label class="form-check-label" for="Q6_No">No</label>
                            </div>
                            <div class="FormInput_left">Package Options</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Package" id="Package_1" value="option1" />
                                <label class="form-check-label" for="Package_1">Option 1</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Package" id="Package_2" value="option2" />
                                <label class="form-check-label" for="Package_2">Option 2</label>
                            </div>
                
                            <div class="FormInput_left">Supporting Documents (attach)</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Doc_KTP" id="Doc_KTP" value="KTP" />
                                <label class="form-check-label" for="Doc_KTP">KTP</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Doc_NPWP" id="Doc_NPWP" value="NPWP" />
                                <label class="form-check-label" for="Doc_NPWP">NPWP</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Doc_Others" id="Doc_Others" value="Others" />
                                <label class="form-check-label" for="Doc_Others">Others</label>
                            </div>
                            </div>
                        </div>
                
                        <!-- Company Section -->
                        <div id="CompanyDiv" style="display:none;">
                            <div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">1. Company Information</div>
                            <div class="FormInput_left">Company Name</div>
                        <div class="FormInput_right"><input type="text" name="Company_Official_Name" id="Company_Official_Name" class="form-control" /></div>
                            <div class="FormInput_left">Legal Form</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Legal_PT" id="Legal_PT" value="PT" />
                                <label class="form-check-label" for="Legal_PT">PT</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Legal_CV" id="Legal_CV" value="CV" />
                                <label class="form-check-label" for="Legal_CV">CV</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Legal_Coop" id="Legal_Coop" value="Cooperative" />
                                <label class="form-check-label" for="Legal_Coop">Cooperative</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Legal_Foundation" id="Legal_Foundation" value="Foundation" />
                                <label class="form-check-label" for="Legal_Foundation">Foundation</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Legal_Other" id="Legal_Other" value="Other" />
                                <label class="form-check-label" for="Legal_Other">Other</label>
                            </div>
                            <div class="FormInput_left">Primary Business</div>
                        <div class="FormInput_right"><input type="text" name="Primary_Business" id="Primary_Business" class="form-control" /></div>
                            <div class="FormInput_left">Year Established</div>
                        <div class="FormInput_right"><input type="text" name="Year_Established" id="Year_Established" class="form-control" /></div>
                            <div class="FormInput_left">Head Office Address</div>
                        <div class="FormInput_right"><input type="text" name="Head_Office_Address" id="Head_Office_Address" class="form-control" /></div>
                            <div class="FormInput_left">Company Website</div>
                        <div class="FormInput_right"><input type="text" name="Company_Website" id="Company_Website" class="form-control" /></div>
                            <div class="FormInput_left">Company NPWP</div>
                        <div class="FormInput_right"><input type="text" name="Company_Tax_ID" id="Company_Tax_ID" class="form-control" /></div>
                            <div class="FormInput_left">NIB</div>
                        <div class="FormInput_right"><input type="text" name="Company_NIB" id="Company_NIB" class="form-control" /></div>
                            <div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">2. PIC (Person In Charge)</div>
                            <div class="FormInput_left">PIC Name</div>
                        <div class="FormInput_right"><input type="text" name="PIC_Name" id="PIC_Name" class="form-control" /></div>
                            <div class="FormInput_left">PIC Position</div>
                        <div class="FormInput_right"><input type="text" name="PIC_Position" id="PIC_Position" class="form-control" /></div>
                            <div class="FormInput_left">PIC Phone</div>
                        <div class="FormInput_right"><input type="text" name="PIC_Phone" id="PIC_Phone" class="form-control" /></div>
                            <div class="FormInput_left">PIC Email</div>
                        <div class="FormInput_right"><input type="text" name="PIC_Email" id="PIC_Email" class="form-control" /></div>
                            <div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">3. Company Financials</div>
                            <div class="FormInput_left">Company Scale</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Company_Scale" id="Company_Scale_1" value="micro" />
                                <label class="form-check-label" for="Company_Scale_1">Micro</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Company_Scale" id="Company_Scale_2" value="small" />
                                <label class="form-check-label" for="Company_Scale_2">Small</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Company_Scale" id="Company_Scale_3" value="medium" />
                                <label class="form-check-label" for="Company_Scale_3">Medium</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Company_Scale" id="Company_Scale_4" value="large" />
                                <label class="form-check-label" for="Company_Scale_4">Large</label>
                            </div>
                            <div class="FormInput_left">Estimated annual revenue</div>
                        <div class="FormInput_right"><input type="text" name="Estimated_Revenue" id="Estimated_Revenue" class="form-control" /></div>
                            <div class="FormInput_left">Source of funds</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Company_Fund_Business" id="Company_Fund_Business" value="business" />
                                <label class="form-check-label" for="Company_Fund_Business">Business</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Company_Fund_Investor" id="Company_Fund_Investor" value="investor" />
                                <label class="form-check-label" for="Company_Fund_Investor">Investor</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Company_Fund_Loan" id="Company_Fund_Loan" value="loan" />
                                <label class="form-check-label" for="Company_Fund_Loan">Loan</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Company_Fund_CSR" id="Company_Fund_CSR" value="csr" />
                                <label class="form-check-label" for="Company_Fund_CSR">CSR</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Company_Fund_Private" id="Company_Fund_Private" value="private" />
                                <label class="form-check-label" for="Company_Fund_Private">Private</label>
                            </div>
                            <div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">4. Partnership Information</div>
                            <div class="FormInput_left">Number of warehouses / operational outlets planned</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Company_Warehouse_Count" id="Company_Warehouse_Count_1" value="1" />
                                <label class="form-check-label" for="Company_Warehouse_Count_1">1</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Company_Warehouse_Count" id="Company_Warehouse_Count_2" value="2" />
                                <label class="form-check-label" for="Company_Warehouse_Count_2">2</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Company_Warehouse_Count" id="Company_Warehouse_Count_3" value=">2" />
                                <label class="form-check-label" for="Company_Warehouse_Count_3">>2</label>
                            </div>
                            <div class="FormInput_left">Has experience in partnership business?</div>
                        <div class="FormInput_right"><textarea name="Company_Experience" id="Company_Experience" rows="3" class="form-control"></textarea></div>
                            <div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">5. Operational Location</div>
                            <div class="FormInput_left">Does the company have operational locations?</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Company_Has_Location" id="Company_Has_Location_Yes" value="yes" />
                                <label class="form-check-label" for="Company_Has_Location_Yes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Company_Has_Location" id="Company_Has_Location_No" value="no" />
                                <label class="form-check-label" for="Company_Has_Location_No">No</label>
                            </div>
                            <div class="FormInput_left">Location type</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Loc_Warehouse" id="Loc_Warehouse" value="warehouse" />
                                <label class="form-check-label" for="Loc_Warehouse">Warehouse</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Loc_Store" id="Loc_Store" value="store" />
                                <label class="form-check-label" for="Loc_Store">Kiosk / Store</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Loc_Own" id="Loc_Own" value="own" />
                                <label class="form-check-label" for="Loc_Own">Own</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Loc_Rent" id="Loc_Rent" value="rent" />
                                <label class="form-check-label" for="Loc_Rent">Rent</label>
                            </div>
                            <div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">Partnership Questions</div>
                            <div class="FormInput_left">1. Will the company follow operational standards and policies?</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="C_Q1" id="C_Q1_Yes" value="Yes" />
                                <label class="form-check-label" for="C_Q1_Yes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="C_Q1" id="C_Q1_No" value="No" />
                                <label class="form-check-label" for="C_Q1_No">No</label>
                            </div>
                            <div class="FormInput_left">2. Will the company commit to support growth targets?</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="C_Q2" id="C_Q2_Yes" value="Yes" />
                                <label class="form-check-label" for="C_Q2_Yes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="C_Q2" id="C_Q2_No" value="No" />
                                <label class="form-check-label" for="C_Q2_No">No</label>
                            </div>
                            <div class="FormInput_left">3. Does the company understand partnership risks?</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="C_Q3" id="C_Q3_Yes" value="Yes" />
                                <label class="form-check-label" for="C_Q3_Yes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="C_Q3" id="C_Q3_No" value="No" />
                                <label class="form-check-label" for="C_Q3_No">No</label>
                            </div>
                            <div class="FormInput_left">4. Will the company meet investment obligations?</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="C_Q4" id="C_Q4_Yes" value="Yes" />
                                <label class="form-check-label" for="C_Q4_Yes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="C_Q4" id="C_Q4_No" value="No" />
                                <label class="form-check-label" for="C_Q4_No">No</label>
                            </div>
                            <div class="FormInput_left">5. Will the company avoid running similar competing businesses?</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="C_Q5" id="C_Q5_Yes" value="Yes" />
                                <label class="form-check-label" for="C_Q5_Yes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="C_Q5" id="C_Q5_No" value="No" />
                                <label class="form-check-label" for="C_Q5_No">No</label>
                            </div>
                            <div class="FormInput_left">6. Will the company sign the cooperation agreement (MOU)?</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="C_Q6" id="C_Q6_Yes" value="Yes" />
                                <label class="form-check-label" for="C_Q6_Yes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="C_Q6" id="C_Q6_No" value="No" />
                                <label class="form-check-label" for="C_Q6_No">No</label>
                            </div>
                            <div class="FormInput_left">Package Options</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Company_Package" id="Company_Package_1" value="option1" />
                                <label class="form-check-label" for="Company_Package_1">Option 1</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Company_Package" id="Company_Package_2" value="option2" />
                                <label class="form-check-label" for="Company_Package_2">Option 2</label>
                            </div>
                
                            <div class="FormInput_left">Supporting Documents (attach)</div>
                            <div class="FormInput_right">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="C_Doc_Akta" id="C_Doc_Akta" value="akta" />
                                <label class="form-check-label" for="C_Doc_Akta">Deed of Establishment / Amendments</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="C_Doc_NIB" id="C_Doc_NIB" value="nib" />
                                <label class="form-check-label" for="C_Doc_NIB">NIB</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="C_Doc_NPWP" id="C_Doc_NPWP" value="npwp" />
                                <label class="form-check-label" for="C_Doc_NPWP">NPWP</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="C_Doc_Others" id="C_Doc_Others" value="others" />
                                <label class="form-check-label" for="C_Doc_Others">Others</label>
                            </div>
                            </div>
                        </div>
                
                        <div id="divtom">
                            <input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Submit" class="btn btn-success mt-2 mr-2" />
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
	// toggle sections based on Register As
	$(function() {
		$('input[name="Register_As"]').change(function() {
			var val = $(this).val();
			if (val === 'company') {
				$('#IndividualDiv').hide();
				$('#CompanyDiv').show();
			} else {
				$('#CompanyDiv').hide();
				$('#IndividualDiv').show();
			}
		});

		// initialize mask if field exists
		if ($('#Estimated_Revenue').length) {
			$('#Estimated_Revenue').mask('#,##0.00', { reverse: true });
		}
	});

	function isValidKey(e) {
		var charCode = e.keyCode || e.which;
		if (charCode == 46) return false;
		return true;
	}
        var myCalendar;
        myCalendar = new dhtmlXCalendarObject("Date");
        myCalendar.setDateFormat("%Y-%m-%d");
	$('#SubmitCustDetail').click(function() {

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "sales-opportunity-new-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					alert('Data Leads Submitted!');

					location.reload();
				} else {
					var cariAWB = data.split("||");
					alert(cariAWB[0]);
                    $('#' + cariAWB[1]).focus();
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("Error Internal! Please Try again!");

			}
		});
	})

	function getAllValues() {
		var inputValues = [];
		$('#ContentPages input,#ContentPages select,#ContentPages textarea').each(function() {
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues.push($(this).attr("name") + "=" + $(this).val());
			} else if (type != "button" && type != "submit" && type != "radio" && type != "checkbox") {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else if ($(this).is("textarea")) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
		})
		return inputValues.join('&');
	}

    </script>
</body>
</html>