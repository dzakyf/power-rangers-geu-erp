<?php
use Illuminate\Support\Facades\Auth;
$Saya = Auth::user()->username;
?>
<div class="mt-3">
    <div class="JudulContent" style="color:#699;">NEW SUSTAINABILITY DECLARATION</div>
    <div class="card">
        <div class="card-body">
            <div id="ContentPagesSD">

                {{-- Header --}}
                <div class="row mb-2">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Unique Number of Sustainability Declaration</label>
                            <input type="text" name="SD_No" id="SD_No" value="{{ $SdNo }}" class="form-control shadow-sm" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date of Issuance</label>
                            <input type="text" name="Issue_Date" id="Issue_Date" value="{{ date('Y-m-d') }}" class="form-control shadow-sm" required />
                        </div>
                    </div>
                </div>

                {{-- Recipient --}}
                <h6 class="font-weight-bold border-bottom pb-1 mt-3" style="color:#1a56a0;">Recipient</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Consignee (Recipient Name)</label>
                            <select name="Consignee" id="Consignee" class="form-control shadow-sm" required>
                                <option value="">-- Pilih Consignee --</option>
                                @foreach($companies as $c)
                                    <option value="{{ $c->Supplier_ID }}">{{ $c->Company_Name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Certificate Number (Supplier)</label>
                            <input type="text" name="Certificate_No" id="Certificate_No" value="" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Contract Number</label>
                            <input type="text" name="Contract_No" id="Contract_No" value="" class="form-control shadow-sm" />
                        </div>
                    </div>
                </div>

                {{-- Dispatch / Receipt Address --}}
                <h6 class="font-weight-bold border-bottom pb-1 mt-3" style="color:#1a56a0;">Dispatch & Receipt</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Address of Dispatch / Shipping Point</label>
                            <textarea name="Dispatch_Address" id="Dispatch_Address" rows="2" class="form-control shadow-sm">PT PELINDO ENERGI LOGISTIK
Jl. Perak Timur No.610, Perak Utara, Kec. Pabean Cantikan, Surabaya, Jawa Timur 60165</textarea>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="Dispatch_Same_As_Supplier" id="Dispatch_Same_As_Supplier" value="1">
                            <label class="form-check-label" for="Dispatch_Same_As_Supplier">Same as address of supplier</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Address of Receipt / Receiving Point</label>
                            <textarea name="Receipt_Address" id="Receipt_Address" rows="2" class="form-control shadow-sm">Pasir Gudang, Johor, Malaysia</textarea>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="Receipt_Same_As_Recipient" id="Receipt_Same_As_Recipient" value="1">
                            <label class="form-check-label" for="Receipt_Same_As_Recipient">Same as address of recipient</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date of Dispatch</label>
                            <input type="text" name="Dispatch_Date" id="Dispatch_Date" value="{{ date('Y-m-d') }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                </div>

                {{-- Section 1 --}}
                <h6 class="font-weight-bold border-bottom pb-1 mt-3" style="color:#1a56a0;">1. General Information</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Type of Product</label>
                            <input type="text" name="Product_Type" id="Product_Type" value="Raw Material" class="form-control shadow-sm" required />
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Type of Raw Material</label>
                            <input type="text" name="Raw_Material_Type" id="Raw_Material_Type" value="Used cooking oil (UCO) entirely of veg. origin" class="form-control shadow-sm" required />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Additional Information (voluntary)</label>
                            <textarea name="Additional_Info" id="Additional_Info" rows="2" class="form-control shadow-sm"></textarea>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Country of Origin</label>
                            <input type="text" name="Country_Of_Origin" id="Country_Of_Origin" value="Indonesia" class="form-control shadow-sm" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="text" inputmode="decimal" name="Quantity" id="Quantity" value="0.00" class="form-control shadow-sm cns-decimal" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Quantity Unit</label>
                            <select name="Quantity_Unit" id="Quantity_Unit" class="form-control shadow-sm">
                                <option value="metric tons" selected>metric tons</option>
                                <option value="m3">m³</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Chain of Custody Option</label>
                            <input type="text" name="Chain_Of_Custody" id="Chain_Of_Custody" value="Mass balance" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="d-block">ISCC Compliant Material</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="ISCC_Compliant" id="ISCC_Compliant" value="1" checked>
                            <label class="form-check-label" for="ISCC_Compliant">Yes</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="d-block">EU RED Compliant Material</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="EU_RED_Compliant" id="EU_RED_Compliant" value="1" checked>
                            <label class="form-check-label" for="EU_RED_Compliant">Yes</label>
                        </div>
                    </div>
                </div>

                {{-- Section 2 --}}
                <h6 class="font-weight-bold border-bottom pb-1 mt-3" style="color:#1a56a0;">2. Scope of Certification of Raw Material</h6>
                <div class="row">
                    @php
                        $scopeFields = [
                            'Scope_Art29'             => 'Complies with sustainability criteria Art. 29 (2)-(7) RED III',
                            'Scope_Intermediate_Crop' => 'Agricultural biomass cultivated as intermediate crop (if applicable)',
                            'Scope_Low_ILUC'          => 'Agricultural biomass fulfills measures for low ILUC risk feedstocks (if applicable)',
                            'Scope_Waste_Residue'     => 'Raw material meets definition of waste or residue according to RED III',
                            'Scope_Support_Received'  => 'Support for production of fuel or fuel precursor received?',
                        ];
                        $scopeDefaults = ['Scope_Art29'=>'Yes','Scope_Intermediate_Crop'=>'Yes','Scope_Low_ILUC'=>'Yes','Scope_Waste_Residue'=>'Yes','Scope_Support_Received'=>'No'];
                    @endphp
                    @foreach($scopeFields as $field => $label)
                    <div class="col-md-12">
                        <div class="form-group row align-items-center">
                            <label class="col-md-8 col-form-label">{{ $label }}</label>
                            <div class="col-md-4">
                                <select name="{{ $field }}" id="{{ $field }}" class="form-control shadow-sm form-control-sm">
                                    <option value="Yes" {{ $scopeDefaults[$field]=='Yes'?'selected':'' }}>Yes</option>
                                    <option value="No" {{ $scopeDefaults[$field]=='No'?'selected':'' }}>No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Waste or Animal By-product Permit Number (if applicable)</label>
                            <input type="text" name="Waste_Permit_No" id="Waste_Permit_No" value="" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Support Nature and Scheme (if applicable)</label>
                            <textarea name="Support_Nature" id="Support_Nature" rows="2" class="form-control shadow-sm"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Section 3 --}}
                <h6 class="font-weight-bold border-bottom pb-1 mt-3" style="color:#1a56a0;">3. Greenhouse Gas (GHG) Emission Information</h6>
                <div class="row">
                    <div class="col-md-4">
                        <label class="d-block">Total Default Value (RED III) Applied</label>
                        <select name="GHG_Total_Default" id="GHG_Total_Default" class="form-control shadow-sm form-control-sm" style="width:100px;">
                            <option value="No" selected>No</option>
                            <option value="Yes">Yes</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-2">
                    @foreach(['Eec','El','Ep','Etd','Eu','Esca','Eccs','Eccr'] as $ghg)
                    <div class="col-md-1" style="min-width:80px;">
                        <div class="form-group">
                            <label style="font-size:11px;">{{ $ghg }}</label>
                            <input type="text" name="GHG_{{ $ghg }}" id="GHG_{{ $ghg }}" value="{{ $ghg=='Etd'?'DDV':'' }}" class="form-control shadow-sm form-control-sm" placeholder="{{ $ghg=='Etd'?'DDV':'' }}" />
                        </div>
                    </div>
                    @endforeach
                    <div class="col-md-2 align-self-end">
                        <p class="text-muted" style="font-size:11px;">kgCO₂eq/dry-ton</p>
                    </div>
                </div>
                <div class="row mt-1">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Ship (km)</label>
                            <input type="text" inputmode="decimal" name="Transport_Ship_KM" id="Transport_Ship_KM" value="" class="form-control shadow-sm form-control-sm cns-decimal" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Train (km)</label>
                            <input type="text" inputmode="decimal" name="Transport_Train_KM" id="Transport_Train_KM" value="" class="form-control shadow-sm form-control-sm cns-decimal" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Road (km)</label>
                            <input type="text" inputmode="decimal" name="Transport_Road_KM" id="Transport_Road_KM" value="3.30" class="form-control shadow-sm form-control-sm cns-decimal" />
                        </div>
                    </div>
                </div>
                <div class="row mt-1">
                    <div class="col-md-6">
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="GHG_DDV_Oil_Extraction" id="GHG_DDV_Oil_Extraction" value="1" checked>
                            <label class="form-check-label" for="GHG_DDV_Oil_Extraction">Disaggregated default value for oil extraction only is applied</label>
                        </div>
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="GHG_DDV_Soil_N2O" id="GHG_DDV_Soil_N2O" value="1" checked>
                            <label class="form-check-label" for="GHG_DDV_Soil_N2O">Disaggregated default value for soil N₂O emissions is applied</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Esca: Bonus or Cap</label>
                            <input type="text" name="GHG_Esca_Bonus_Cap" id="GHG_Esca_Bonus_Cap" value="" class="form-control shadow-sm form-control-sm" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>NUTS2 Area (if applicable)</label>
                            <input type="text" name="GHG_NUTS2_Area" id="GHG_NUTS2_Area" value="" class="form-control shadow-sm form-control-sm" />
                        </div>
                    </div>
                    <div class="col-md-8">
                        <label>El: Bonus of 29 g CO₂eq/MJ if biomass from restored degraded land</label>
                        <select name="GHG_El_Bonus" id="GHG_El_Bonus" class="form-control shadow-sm form-control-sm" style="width:100px;">
                            <option value="Yes" selected>Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>

                {{-- Section 4 --}}
                <h6 class="font-weight-bold border-bottom pb-1 mt-3" style="color:#1a56a0;">4. Voluntary Add-Ons</h6>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="Addon_FSS" id="Addon_FSS" value="1" checked>
                            <label class="form-check-label" for="Addon_FSS">Food Security Standard (FSS) - 202-04</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="Addon_EUDR" id="Addon_EUDR" value="1" checked>
                            <label class="form-check-label" for="Addon_EUDR">EU Deforestation Regulation (EUDR) - 202-09</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="Addon_NonGMO_Feed" id="Addon_NonGMO_Feed" value="1" checked>
                            <label class="form-check-label" for="Addon_NonGMO_Feed">Non-GMO for Food and Feed - 205-03</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="Addon_NonGMO_Technical" id="Addon_NonGMO_Technical" value="1" checked>
                            <label class="form-check-label" for="Addon_NonGMO_Technical">Non-GMO for Technical Markets - 205-04</label>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Note</label>
                            <textarea name="Note" id="Note" rows="2" class="form-control shadow-sm"></textarea>
                        </div>
                    </div>
                </div>

            </div>{{-- /ContentPagesSD --}}

            <div id="divtom">
                <input type="button" id="SubmitSD" value="Submit" class="btn btn-success mt-2 mr-2" />
            </div>
        </div>
    </div>
    <div id="DivProcess"></div>
</div>

<script>
    var myCalSD;
    myCalSD = new dhtmlXCalendarObject(["Issue_Date", "Dispatch_Date"]);
    myCalSD.setDateFormat("%Y-%m-%d");

    $('#SubmitSD').click(function () {
        $.blockUI();
        $('#ContentPagesSD .cns-decimal').each(function () {
            $(this).val(cnsParseNumber($(this).val()));
        });
        var formData = $('#ContentPagesSD').find('input, select, textarea').not(':button').serialize();
        cnsInitDecimalInputs('#ContentPagesSD');

        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "cns-export-sustainability-declaration-new-submit",
            type: "POST",
            data: formData,
            success: function (data) {
                $.unblockUI();
                if (data.indexOf("success") !== -1) {
                    alert('Sustainability Declaration berhasil disimpan!');
                    refreshdata();
                    $('#divlistOpportunity').html('');
                } else {
                    alert('Gagal menyimpan!');
                }
            },
            error: function () {
                $.unblockUI();
                alert("Error Internal! Silakan coba lagi.");
            }
        });
    });
</script>
