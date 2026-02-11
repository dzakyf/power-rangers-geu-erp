<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$nama =  Auth::user()->Name;
$color = ['#3a86ff', '#fb5607', '#762761', '#9e7e78', '#8338ec', '#c9b135', '#ffbe0b', '#762761', '#094055', '#38A830', '#009DAB', '#ff006e',];
$tahun = date('Y');
$uang = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%c') as tanggal, Account_No, Reference_No, sum(Debet) as penerimaan, sum(Credit) as pengeluaran from `ho` where `Posting_Date` LIKE '$tahun%' and `Account_No` LIKE '111%' group by `tanggal` ASC, `Account_No`, `Reference_No` 
"));
$pettyCashDebet =[1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0];
$pettyCashCredit= [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0];
$bankDebet= [];
$bankCredit= [];
$totalBank= [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0];
$totalCash= [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0];
foreach ($uang as $data) {
    if($data->Account_No == "111.1") {
        $pettyCashDebet[$data->tanggal]  +=  $data->penerimaan + 0;
        $pettyCashCredit[$data->tanggal]  +=  $data->pengeluaran + 0;
        $totalCash[$data->tanggal] +=  $data->penerimaan - $data->pengeluaran;
    } else {
        $bankDebet[$data->Reference_No][$data->tanggal]  =  $data->penerimaan + 0;
        $bankCredit[$data->Reference_No][$data->tanggal]  =  $data->pengeluaran + 0;
        $totalBank[$data->tanggal] +=  $data->penerimaan - $data->pengeluaran;
    }

}

$saldoawal = DB::table("ho_sa_year")->where("Journal_Year","=",($tahun - 1))->where("Acc_No","LIKE","111%")->get();
$saldoCash = 0;
$saldoawalBank = 0;
$saldoBank = [];
foreach ($saldoawal as $data2) {
    if(($data2->Acc_No == "111.1")) { 
        $saldoCash = $data2->Journal_Value;
    }
    
    if($data2->Group_Report == "BANK") {
        $saldoBank[$data2->Remark] = $data2->Journal_Value;
        $saldoawalBank +=$data2->Journal_Value;
    }
}
$saldoAkhir = 0;
for($i = 1 ; $i < 13 ; $i++) {
    if($i == 1) {
        $jum = $saldoCash + $saldoawalBank + $totalCash[$i] + $totalBank[$i];
    } else {
        $jum = $saldoAkhir + $totalCash[$i] + $totalBank[$i];
    }
   
    $grafikCashFlow [] = $jum;
    $saldoAkhir = $jum;
}


?>
@extends('layout.mainlayout-accounting')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">

</div>
<!-- End Main Content Header -->

<div class="row">
<div class="col-md-4">
        <div class="stats-card-two mb-30">
            <div class="media align-items-center justify-content-between">
                <div class="ml-0">
                    <p class="mb-10 line-height-1">Cash/Bank</p>
                    <h3 class="mb-0 fs-25">Rp.
                        <?= number_format($saldoCash + array_sum($totalCash) + $saldoawalBank + array_sum($totalBank), 2, ',', '.');  ?>
                    </h3>
                </div>
                <div class="avatar avatar-blue">
                    <i data-feather="activity" class="icon"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card-two mb-30">
            <div class="media align-items-center justify-content-between">
                <div class="ml-0">
                    <p class="mb-10 line-height-1">Petty Cash</p>
                    <h3 class="mb-0 fs-25">Rp.
                        <?= number_format($saldoCash + array_sum($totalCash), 2, ',', '.');  ?>
                    </h3>
                </div>
                <div class="avatar avatar-blue">
                    <i data-feather="activity" class="icon"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card-two mb-30">
            <div class="media align-items-center justify-content-between">
                <div class="ml-0">
                    <p class="mb-10 line-height-1">Bank</p>
                    <h3 class="mb-0 fs-25">Rp.
                        <?= number_format($saldoawalBank + array_sum($totalBank) , 2, ',', '.');  ?>
                    </h3>
                </div>
                <div class="avatar avatar-blue">
                    <i data-feather="activity" class="icon"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-12">

<div class="card">
    <div class="header card-header">
        <span> Cash Flow </span>
    </div>
    <div class="card-body">
        <div class="row">
            <div id="cash-flow" class="col-12"></div>
        </div>
    </div>
</div>
</div>

<script>
    (function($) {
        "use strict";
        jQuery(document).on('ready', function() {
            var options = {
                chart: {
                    width: '100%',
                    height: 280,
                    type: 'line',
                },
                yaxis: {
                    title: {
                        text: 'Rupiah (Jutaan)',
                    },
                    labels: {
                        style: {
                            colors: [],
                            fontSize: '12px',
                            fontFamily: 'Helvetica, Arial, sans-serif',
                            fontWeight: 600,
                            cssClass: 'apexcharts-yaxis-label',
                        },
                        formatter: function(val) {
                            var formatRupiah = new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR'
                            }).format(val);
                            return formatRupiah;
                        },
                    }
                },
                series: [{
                    name: "Cash Flow",
                    data: <?= json_encode($grafikCashFlow) ?>,
                }],

                title : {
                    text: 'Cash Flow Per Bulan',
                    align: 'left'
                },
                // grid: {
                //     row: {
                //         colors: <?= json_encode($color) ?>,
                //         opacity: 0.5
                //     }
                // },
                xaxis: {
                    categories:['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','Nopember','Desember'],
                },
        
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                    }
                }],
    
            }
            var chart = new ApexCharts(document.querySelector("#cash-flow"), options);
            chart.render();
        });
    }(jQuery))


</script>
@endsection