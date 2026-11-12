<?php
use Illuminate\Support\Facades\Auth;
$Saya = Auth::user()->username;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sustainability Declaration</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; background:#f5f5f5; margin:0; padding:0; }
    @media print {
        body { background:#fff; }
        .sheet { box-shadow: none; }
    }
    .sheet { background:#fff; width:780px; margin:10px auto; padding:0; box-shadow:0 0 8px rgba(0,0,0,0.15); }
    table { width:100%; border-collapse:collapse; font-size:11px; }
    td, th { padding:4px 6px; vertical-align:top; }
    .hdr-blue { background:#1a3c6e; color:#fff; font-weight:bold; font-size:12px; padding:6px 8px; }
    .hdr-orange { background:#c8692a; color:#fff; font-size:10px; padding:4px 8px; font-style:italic; }
    .hdr-supplier { background:#1a3c6e; color:#fff; font-weight:bold; padding:4px 8px; }
    .section-blue { background:#1a3c6e; color:#fff; font-weight:bold; padding:4px 8px; }
    .bd { border:1px solid #999; }
    .bd-t { border-top:1px solid #999; }
    .bd-b { border-bottom:1px solid #999; }
    .bd-l { border-left:1px solid #999; }
    .bd-r { border-right:1px solid #999; }
    .lbl { font-size:10px; color:#555; }
    .val { font-weight:bold; }
    .chk { display:inline-block; width:12px; height:12px; border:1px solid #333; text-align:center; line-height:11px; font-size:10px; vertical-align:middle; }
    .chk-yes { background:#1a3c6e; color:#fff; }
    p { margin:2px 0; }
</style>
<link rel="stylesheet" href="/assets/paper.css" />
</head>
<body class="A4">
<div class="sheet padding-10mm" style="padding:6mm 8mm;">

{{-- ============ HEADER ============ --}}
<table style="margin-bottom:0;">
    <tr>
        <td style="width:65%;" class="hdr-blue">
            Sustainability Declaration for Raw Materials and Intermediate Products
        </td>
        <td style="width:10%;text-align:right;" class="hdr-blue">V4.0</td>
        <td style="width:25%;text-align:right;vertical-align:middle;" rowspan="2" class="bd bd-l" style="padding:4px;">
            <img src="{{ asset('/assets/images/large-logo.png') }}" alt="ISCC" height="40"><br>
            <span style="font-size:9px;color:#666;">www.iscc-system.org</span>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="hdr-orange">
            Applies under the revised Renewable Energy Directive (EU) 2018/2001 (RED III)
        </td>
    </tr>
</table>

<table>
    <tr>
        <td style="width:35%;" class="bd lbl">Unique Number of Sustainability Declaration:</td>
        <td style="width:40%;" class="bd val">{{ $sd->SD_No }}</td>
        <td style="width:25%;" class="bd" rowspan="2" style="text-align:center;vertical-align:middle;">
            <img src="{{ asset('/assets/images/iscc-logo.png') }}" alt="ISCC" height="45" onerror="this.style.display='none'">
        </td>
    </tr>
    <tr>
        <td class="bd lbl">Date of Issuance of Sustainability Declaration</td>
        <td class="bd">{{ $sd->Issue_Date ? date('d/m/Y', strtotime($sd->Issue_Date)) : '' }}</td>
    </tr>
</table>

{{-- ============ SUPPLIER / RECIPIENT ============ --}}
<table>
    <tr>
        <td style="width:50%;" class="hdr-supplier">Supplier</td>
        <td style="width:50%;" class="hdr-supplier">Recipient</td>
    </tr>
    <tr>
        <td class="bd" style="min-height:80px;">
            <span class="lbl">Name:</span><br>
            <strong>PT GREEN ENERGI UTAMA</strong><br><br>
            <span class="lbl">Address:</span><br>
            Pergudangan Tanrise K Walk A2 Karanglo no 69<br>
            65153, Malang, Jawa Timur<br>
            Indonesia
        </td>
        <td class="bd" style="min-height:80px;">
            <span class="lbl">Name:</span><br>
            <strong>{{ isset($companies[$sd->Consignee]) ? strtoupper($companies[$sd->Consignee]->Company_Name) : '' }}</strong><br><br>
            <span class="lbl">Address:</span><br>
            {{ isset($companies[$sd->Consignee]) ? $companies[$sd->Consignee]->Address : '' }}
        </td>
    </tr>
    <tr>
        <td class="bd">
            <span class="lbl">Certification System:</span>
            <span class="bd" style="padding:1px 6px;">ISCC EU</span><br>
            <span class="lbl">Certificate Number:</span><br>
            {{ $sd->Certificate_No ?: '........................' }}
        </td>
        <td class="bd">
            <span class="lbl">Contract Number:</span><br>
            {{ $sd->Contract_No ?: '...................' }}
        </td>
    </tr>
    <tr>
        <td class="bd" colspan="2">
            <span class="lbl">Address of dispatch/shipping point of the sustainable material:</span>
            @if($sd->Dispatch_Same_As_Supplier)
                <span class="chk chk-yes">&#10003;</span> Same as address of supplier
            @else
                {{ $sd->Dispatch_Address }}<br>
                <span class="chk">&#9633;</span> Same as address of supplier
            @endif
        </td>
    </tr>
    <tr>
        <td class="bd" colspan="2">
            <span class="lbl">Address of receipt/receiving point of the sustainable material:</span>
            @if($sd->Receipt_Same_As_Recipient)
                <span class="chk chk-yes">&#10003;</span> Same as address of recipient
            @else
                {{ $sd->Receipt_Address }}<br>
                <span class="chk">&#9633;</span> Same as address of recipient
            @endif
        </td>
    </tr>
    <tr>
        <td class="bd" colspan="2">
            <span class="lbl">Date of dispatch of the sustainable material:</span>
            <span class="val">{{ $sd->Dispatch_Date ? date('d/m/Y', strtotime($sd->Dispatch_Date)) : '' }}</span>
        </td>
    </tr>
</table>

{{-- ============ SECTION 1 ============ --}}
<table>
    <tr><td colspan="2" class="section-blue">1. General information</td></tr>
    <tr>
        <td class="bd" style="width:40%;"><span class="lbl">Type of Product:<br><small>(e.g. raw material, crude oil)</small></span></td>
        <td class="bd">{{ $sd->Product_Type }}</td>
    </tr>
    <tr>
        <td class="bd"><span class="lbl">Type of Raw Material</span></td>
        <td class="bd">{{ $sd->Raw_Material_Type }}</td>
    </tr>
    <tr>
        <td class="bd"><span class="lbl">Additional Information (voluntary):</span></td>
        <td class="bd">{{ $sd->Additional_Info }}</td>
    </tr>
    <tr>
        <td class="bd"><span class="lbl">Country of Origin (of the raw material):</span></td>
        <td class="bd">{{ $sd->Country_Of_Origin }}</td>
    </tr>
    <tr>
        <td class="bd"><span class="lbl">Quantity:</span></td>
        <td class="bd">
            {{ number_format((float) $sd->Quantity, 2, '.', ',') }} mt
            <span class="chk chk-yes">&#10003;</span> metric tons
            <span class="chk">&#9633;</span> m³
        </td>
    </tr>
    <tr>
        <td class="bd"><span class="lbl">ISCC Compliant material<sup>1</sup></span></td>
        <td class="bd">
            <span class="chk {{ $sd->ISCC_Compliant ? 'chk-yes' : '' }}">{{ $sd->ISCC_Compliant ? '&#10003;' : '' }}</span> Yes
            <span class="chk {{ !$sd->ISCC_Compliant ? 'chk-yes' : '' }}">{{ !$sd->ISCC_Compliant ? '&#10003;' : '' }}</span> No
        </td>
    </tr>
    <tr>
        <td class="bd"><span class="lbl">EU RED Compliant material<sup>2</sup></span></td>
        <td class="bd">
            <span class="chk {{ $sd->EU_RED_Compliant ? 'chk-yes' : '' }}">{{ $sd->EU_RED_Compliant ? '&#10003;' : '' }}</span> Yes
            <span class="chk {{ !$sd->EU_RED_Compliant ? 'chk-yes' : '' }}">{{ !$sd->EU_RED_Compliant ? '&#10003;' : '' }}</span> No
        </td>
    </tr>
    <tr>
        <td class="bd"><span class="lbl">Chain of custody option (voluntary)</span></td>
        <td class="bd">{{ $sd->Chain_Of_Custody }}</td>
    </tr>
</table>

{{-- ============ SECTION 2 ============ --}}
<table>
    <tr><td colspan="3" class="section-blue">2. Scope of certification of raw material</td></tr>
    @php
        $scope = [
            ['label' => 'The raw material complies with the relevant sustainability criteria according to Art. 29 (2) - (7) RED III³', 'field' => 'Scope_Art29'],
            ['label' => 'The agricultural biomass was cultivated as intermediate crop (if applicable)', 'field' => 'Scope_Intermediate_Crop'],
            ['label' => 'The agricultural biomass additionally fulfills the measures for low ILUC risk feedstocks (if applicable)', 'field' => 'Scope_Low_ILUC'],
            ['label' => 'The raw material meets the definition of waste or residue according to the RED III⁴', 'field' => 'Scope_Waste_Residue'],
        ];
    @endphp
    @foreach($scope as $s)
    <tr>
        <td class="bd" style="width:70%;">{{ $s['label'] }}</td>
        <td class="bd" style="width:15%;text-align:center;">
            <span class="chk {{ $sd->{$s['field']}=='Yes'?'chk-yes':'' }}">{{ $sd->{$s['field']}=='Yes'?'&#10003;':'' }}</span> Yes
        </td>
        <td class="bd" style="width:15%;text-align:center;">
            <span class="chk {{ $sd->{$s['field']}=='No'?'chk-yes':'' }}">{{ $sd->{$s['field']}=='No'?'&#10003;':'' }}</span> No
        </td>
    </tr>
    @endforeach
    <tr>
        <td class="bd"><span class="lbl">If applicable, please specify waste or animal byproduct permit number</span></td>
        <td class="bd" colspan="2">{{ $sd->Waste_Permit_No }}</td>
    </tr>
    <tr>
        <td class="bd">Was support for the production of the fuel or fuel precursor received?<sup>5</sup></td>
        <td class="bd" style="text-align:center;">
            <span class="chk {{ $sd->Scope_Support_Received=='Yes'?'chk-yes':'' }}">{{ $sd->Scope_Support_Received=='Yes'?'&#10003;':'' }}</span> Yes
        </td>
        <td class="bd" style="text-align:center;">
            <span class="chk {{ $sd->Scope_Support_Received=='No'?'chk-yes':'' }}">{{ $sd->Scope_Support_Received=='No'?'&#10003;':'' }}</span> No
        </td>
    </tr>
    <tr>
        <td class="bd"><span class="lbl">If applicable, please specify support nature and scheme</span></td>
        <td class="bd" colspan="2">{{ $sd->Support_Nature }}</td>
    </tr>
</table>

{{-- ============ SECTION 3 ============ --}}
<table>
    <tr><td colspan="9" class="section-blue">3. Greenhouse Gas (GHG) emission information</td></tr>
    <tr>
        <td class="bd" colspan="9">
            <span class="lbl">Total default value according to RED III applied</span>
            <span class="chk {{ $sd->GHG_Total_Default=='Yes'?'chk-yes':'' }}">{{ $sd->GHG_Total_Default=='Yes'?'&#10003;':'' }}</span> Yes
            <span class="chk {{ $sd->GHG_Total_Default=='No'?'chk-yes':'' }}">{{ $sd->GHG_Total_Default=='No'?'&#10003;':'' }}</span> No
        </td>
    </tr>
    <tr>
        @foreach(['Eec','El','Ep','Etd','Eu','Esca','Eccs','Eccr'] as $g)
        <td class="bd" style="text-align:center;width:11%;"><span class="lbl">{{ $g }}</span><br>{{ $sd->{'GHG_'.$g} }}</td>
        @endforeach
        <td class="bd" style="font-size:9px;">kgCO₂eq/<br>dry-ton</td>
    </tr>
    <tr>
        <td class="bd" colspan="3">
            <span class="lbl">Means of transport</span><br>
            Ship &nbsp;&nbsp; Train &nbsp;&nbsp; Road
        </td>
        <td class="bd" colspan="2">
            <span class="lbl">Transport distance in km</span><br>
            {{ $sd->Transport_Ship_KM }} km &nbsp;
            {{ $sd->Transport_Train_KM }} km &nbsp;
            {{ $sd->Transport_Road_KM }} km
        </td>
        <td class="bd" colspan="4" style="font-size:9px;color:#555;">
            Only relevant if the recipient of the material is responsible to calculate actual emissions from transport and for biomass fuels
        </td>
    </tr>
    <tr>
        <td class="bd" colspan="7">Disaggregated default value for oil extraction only is applied</td>
        <td class="bd" colspan="2" style="text-align:center;">
            <span class="chk {{ $sd->GHG_DDV_Oil_Extraction?'chk-yes':'' }}">{{ $sd->GHG_DDV_Oil_Extraction?'&#10003;':'' }}</span> Yes
        </td>
    </tr>
    <tr>
        <td class="bd" colspan="7">Disaggregated default value for soil N₂O emissions is applied</td>
        <td class="bd" colspan="2" style="text-align:center;">
            <span class="chk {{ $sd->GHG_DDV_Soil_N2O?'chk-yes':'' }}">{{ $sd->GHG_DDV_Soil_N2O?'&#10003;':'' }}</span> Yes
        </td>
    </tr>
    <tr>
        <td class="bd" colspan="4"><span class="lbl">Esca: Specify which Bonus or Cap applies to any claimed Esca savings</span></td>
        <td class="bd" colspan="5">{{ $sd->GHG_Esca_Bonus_Cap }}</td>
    </tr>
    <tr>
        <td class="bd" colspan="4"><span class="lbl">If applicable, please specify the NUTS2 area:</span></td>
        <td class="bd" colspan="5">{{ $sd->GHG_NUTS2_Area }}</td>
    </tr>
    <tr>
        <td class="bd" colspan="6">
            El: Bonus of 29 g CO₂eq/MJ biofuel/bioliquid/biomass fuel if biomass is obtained from restored degraded land
        </td>
        <td class="bd" colspan="3" style="text-align:center;">
            <span class="chk {{ $sd->GHG_El_Bonus=='Yes'?'chk-yes':'' }}">{{ $sd->GHG_El_Bonus=='Yes'?'&#10003;':'' }}</span> Yes
            <span class="chk {{ $sd->GHG_El_Bonus=='No'?'chk-yes':'' }}">{{ $sd->GHG_El_Bonus=='No'?'&#10003;':'' }}</span> No
        </td>
    </tr>
</table>

{{-- ============ SECTION 4 ============ --}}
<table>
    <tr><td colspan="2" class="section-blue">4. Voluntary Add-Ons</td></tr>
    @php
        $addons = [
            ['label' => 'Food Security Standard (FSS) - 202-04',          'field' => 'Addon_FSS'],
            ['label' => 'EU Deforestation Regulation (EUDR) - 202-09',     'field' => 'Addon_EUDR'],
            ['label' => 'Non-GMO for Food and Feed - 205-03',              'field' => 'Addon_NonGMO_Feed'],
            ['label' => 'Non-GMO for Technical Markets - 205-04',          'field' => 'Addon_NonGMO_Technical'],
        ];
    @endphp
    @foreach($addons as $a)
    <tr>
        <td class="bd" style="width:85%;">{{ $a['label'] }}</td>
        <td class="bd" style="text-align:center;">
            <span class="chk {{ $sd->{$a['field']}?'chk-yes':'' }}">{{ $sd->{$a['field']}?'&#10003;':'' }}</span> Yes
        </td>
    </tr>
    @endforeach
    <tr>
        <td class="bd" colspan="2" style="font-size:9px;color:#444;font-style:italic;padding:6px;">
            This form is valid without signature. By issuing this Sustainability Declaration, the issuing party guarantees that
            all information made on this Sustainability Declaration for Raw Materials and Intermediate Products are correct
            and in compliance with the requirements of ISCC and the RED.
        </td>
    </tr>
</table>

</div>
</body>
</html>
