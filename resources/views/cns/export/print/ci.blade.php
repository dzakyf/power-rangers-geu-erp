<?php
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Database\Eloquent\Builder;

    
    $LSP = Auth::user()->Location;
    $Saya =  Auth::user()->username;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Job Sheet Report</title>
<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        background: #f5f5f5;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 800px;
        margin-top: 20px;
        margin-left: auto;
        margin-right: auto;
        background: #ffffff;
        padding: 40px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    @media print {
        .container {
            width: 93%;
            margin-top: 0;
            margin-left: 0;
            margin-right: 0;
            box-shadow: none;
        }
    }

    .header {
        text-align: center;
        position: relative;
        margin-bottom: 20px;
        padding-top: 80px;
    }

    .logo {
        position: absolute;
        right: 0px;
        top: 0;
        font-size: 36px;
        font-weight: bold;
        text-align: right;
    }

    .company-name {
        font-size: 14px;
        margin-bottom: 10px;
    }

    .title {
        font-size: 20px;
        font-weight: bold;
        margin-top: 10px;
    }

    hr {
        border: none;
        border-top: 1px solid #aaa;
        margin: 20px 0;
    }

    .section-title {
        font-weight: bold;
        margin-top: 20px;
        margin-bottom: 5px;
        padding-bottom: 10px;
        border-bottom: 1px solid #2b6cb0;
    }

    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }

    .grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 40px;
    }

    .grid-3-cust {
        display: grid;
        grid-template-columns: 1fr 0.5fr 1.5fr;
        gap: 40px;
    }

    .label {
        font-weight: bold;
    }

    .blue {
        color: #2b6cb0;
    }

    .job-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-top: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 14px;
    }

    th {
        background: #dcdcdc;
        text-align: left;
        padding: 8px;
        font-weight: bold;
    }

    td {
        padding: 8px;
    }

    .footer {
        position: absolute;
        right: 45%;
        left: auto;
        bottom: 3%;
        text-align: center;
        font-size: 12px;
        margin-top: 40px;
        color: #555;
    }
</style>
<link rel="stylesheet" href="paper.css" />
</head>
<body class="A4">

    <div class="sheet padding-10mm">

        <div class="header">
            <div class="logo">
                    <img src='#' height="100" />
                    <div class="company-name">{{ $companies[$invoice->Shipper]->Company_Name ?? '' }}</div>
            </div>
            <div class="title">Job Sheet Report</div>
        </div>
        
        <hr>

        <table>
            <tr style="border-top:none;">
                <td width="50%">&nbsp;</td>
                <td>
                    <table>
                        <tr style="padding:0;">
                            <td style="padding:0;vertical-align:top;font-weight:bold;">Address</td>
                            <td style="padding:0;vertical-align:top;font-weight:bold;">:</td>
                            <td style="padding:0;vertical-align:top">
                                <span class="blue">
                                    Plaza Summarecon Bekasi<br>
                                    8th Floor - Unit 801,<br>
                                    Jl Bolevard Ahmad Yani KA No.1<br>
                                    Bekasi 17142 - Indonesia.
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0;vertical-align:top;font-weight:bold;">Telephone</td>
                            <td style="padding:0;vertical-align:top;font-weight:bold;">:</td>
                            <td style="padding:0;vertical-align:top" class="blue">
                                +62 21 8946 1980
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0;vertical-align:top;font-weight:bold;">Fax</td>
                            <td style="padding:0;vertical-align:top;font-weight:bold;">:</td>
                            <td style="padding:0;vertical-align:top" class="blue">
                                +62 21 8946 1978
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0;vertical-align:top;font-weight:bold;">Email</td>
                            <td style="padding:0;vertical-align:top;font-weight:bold;">:</td>
                            <td style="padding:0;vertical-align:top" class="blue">
                                {{ $responsibility->email ?? '' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0;vertical-align:top;font-weight:bold;">NPWP</td>
                            <td style="padding:0;vertical-align:top;font-weight:bold;">:</td>
                            <td style="padding:0;vertical-align:top" class="blue">
                                070.406.319.7-016.000
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="grid-3-cust">

            <div>
                <div class="section-title">Customer Details</div>
                <div class="blue">
                    {{ $jobsheet->Customer_Name }}<br>
                    <span style="width:170px;height:auto">
                        {{ $jobsheet->Customer_Address }}
                    </span>
                </div>
            </div>

            <div>
                &nbsp;
            </div>

            <div>
                <div class="section-title">Site Details</div>
                <div class="blue">
                    {{ $jobsheet->Site_Name }}<br>
                    <span style="width:170px;height:auto">
                        {{ $jobsheet->Site_Address }}
                    </span>
                </div>
            </div>

        </div>

        <div class="section-title">Job Details</div>

        <div class="job-details">
            <div>
                <div><span class="label">Job Type:</span> <span class="blue">{{ $jobsheet->Job_Type }}</span></div>
                <div><span class="label">Contact Name:</span> <span class="blue">{{ $jobsheet->Contact_Name }}</span></div>
            </div>
            <div>
                <div><span class="label">Job Number:</span> <span class="blue">{{ $jobsheet->Job_Number }}</span></div>
                <div><span class="label">Job Category:</span> <span class="blue">{{ $jobsheet->Job_Category }}</span></div>
            </div>
        </div>

        <div class="section-title">Description</div>
        <div class="blue">
            {{ $jobsheet->Description }}
        </div>

        <div class="section-title">Job Tasks</div>

        <table>
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Status</th>
                    <th>Summary</th>
                    <th>Sub Task(s)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jobsheettask as $task)
                <tr>
                    <td class="blue">{{ $task->Task }}</td>
                    <td class="blue">{{ $task->Status }}</td>
                    <td class="blue">{{ $task->Summary }}</td>
                    <td class="blue">{{ $task->Job_Task }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            Internal use only
        </div>

    </div>

    <div class="sheet padding-10mm">

        <div class="header">
            <div class="logo">
                    <img src='<?php echo $logo?>' height="<?php echo $ukuran?>" />
                    <div class="company-name">PT Dürr Systems Indonesia</div>
            </div>
            <div class="title">Job Sheet Report</div>
        </div>
        
        <hr>

        <table>
            <thead>
                <tr>
                    <th>Attachment</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="blue">{{ $jobsheet->File_Document == '' ? 'Not Applicable' : $jobsheettask }}</td>
                </tr>
            </tbody>
        </table>

        <div class="grid-3">

            <div style="margin-top:60px;font-weight:bold;font-size:16px;">
                Print Name :
            </div>

            <div>
                <div style="background: #dcdcdc;padding:5px;margin-top:20px">Supplier Signature</div>
            </div>

            
            <div>
                <div style="background: #dcdcdc;padding:5px;margin-top:20px">Customer Signature</div>
            </div>

        </div>

        <div class="footer">
            Internal use only
        </div>

    </div>

</body>
</html>