<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$nama =  Auth::user()->Name;

$tanggal1 = date("Y-m-d",strtotime('first day of january this year'));
$tanggal2 = date("Y-m-d");

$color = ['#3a86ff', '#fb5607', '#ff006e', '#762761', '#9e7e78', '#8338ec', '#c9b135', '#ffbe0b', '#762761', '#094055', '#38A830', '#009DAB'];

$ba = DB::select(DB::raw("SELECT  DATE_FORMAT(CONCAT(LEFT(Periode_Tagihan, 4),'-',RIGHT(Periode_Tagihan, 2),'-01'),'%b-%Y') as tanggal, SUM(Total) as total FROM proforma WHERE  Periode_Tagihan BETWEEN " . date("Ym", strtotime($tanggal1)) . " AND  " . date("Ym", strtotime($tanggal2)) . " AND Status NOT IN ('EX','CANCELED','RETURN') GROUP BY tanggal ORDER BY Periode_Tagihan ASC "));


// $ba = DB::select(DB::raw("SELECT  DATE_FORMAT(Dateinsert,'%b-%Y') as tanggal, SUM(Total) as total FROM proforma WHERE  Dateinsert BETWEEN '" . date("Y-m-d", strtotime($tanggal1)) . "' AND  '" . date("Y-m-d", strtotime($tanggal2)) . "' AND Status NOT IN ('EX','CANCELED','RETURN') GROUP BY tanggal ORDER BY Dateinsert ASC "));

$ba_tanggal = array_column($ba, 'tanggal');
$ba_total = array_column($ba, 'total');
//$punya_ba = array_combine($ba_tanggal, $ba_total);

$ba_sum = DB::select(DB::raw("SELECT SUM(IF(Status = 'CREATED' OR Status = 'ATTACHED' OR Status = 'PROPOSED' OR Status = 'PROPOSED1',Total,NULL)) AS 'progress',

SUM(IF(Status = 'POSTED',Total,NULL)) AS 'posted',
SUM(IF(Status = 'RECEIVED',Total,NULL)) AS 'received'
FROM proforma WHERE Periode_Tagihan BETWEEN '" . date("Ym", strtotime($tanggal1)) . "' AND  '" . date("Ym", strtotime($tanggal2)) . "' AND Status NOT IN ('EX','CANCELED','RETURN') "));

$sum_ba = array_sum($ba_total);

?>
@extends('layout.mainlayout-sales')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">

</div>
<!-- End Main Content Header -->
<div class="row">
    <div class="col-md-3  col-sm-6">

        <div class="stats-card-two mb-30">
            <div class="media align-items-center justify-content-between">
                <div class="ml-0">
                    <p class="mb-10 line-height-1">Total BA</p>
                    <h3 class="mb-0 fs-18">Rp. <?= number_format($sum_ba, 0, ',', '.');  ?></h3>
                </div>
                <div class="avatar avatar-blue">
                    <i data-feather="shopping-cart" class="icon"></i>
                </div>
            </div>
        </div>

    </div>
    <div class="col-md-3 col-sm-6">

        <div class="stats-card-two mb-30">
            <div class="media align-items-center justify-content-between">
                <div class="ml-0">
                    <p class="mb-10 line-height-1">BA On Progress</p>
                    <h3 class="mb-0 fs-18">Rp. <?= number_format($ba_sum[0]->progress, 0, ',', '.');  ?></h3>
                </div>
                <div class="avatar avatar-blue">
                    <i data-feather="shopping-cart" class="icon"></i>
                </div>
            </div>
        </div>

    </div>
    <div class="col-md-3  col-sm-6">

        <div class="stats-card-two mb-30">
            <div class="media align-items-center justify-content-between">
                <div class="ml-0">
                    <p class="mb-10 line-height-1">FPP</p>
                    <h3 class="mb-0 fs-18">Rp. <?= number_format($ba_sum[0]->posted, 0, ',', '.');  ?></h3>
                </div>
                <div class="avatar avatar-blue">
                    <i data-feather="shopping-cart" class="icon"></i>
                </div>
            </div>
        </div>

    </div>
    <div class="col-md-3 col-sm-6">

        <div class="stats-card-two mb-30">
            <div class="media align-items-center justify-content-between">
                <div class="ml-0">
                    <p class="mb-10 line-height-1">RECEIVED BY FINANCE</p>
                    <h3 class="mb-0 fs-18">Rp. <?= number_format($ba_sum[0]->received, 0, ',', '.');  ?></h3>
                </div>
                <div class="avatar avatar-blue">
                    <i data-feather="shopping-cart" class="icon"></i>
                </div>
            </div>
        </div>

    </div>
</div>
<div class="row">

    <div class="col-sm-6">
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="header card-header">
                        <span>Berita Acara Perbulan</span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div id="invoice-ba-chart" class="col-12"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>






<script>
    (function($) {
        "use strict";
        jQuery(document).on('ready', function() {
            var optionsColumn = {
                series: [{
                    name: 'BA',
                    data: <?= json_encode($ba_total) ?>
                }],
                chart: {
                    type: 'bar',
                    height: 350
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: <?= json_encode($ba_tanggal) ?>,
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
                            }).format(val / 1000000);
                            return formatRupiah;
                        },
                    }
                },
                fill: {
                    opacity: 1
                },
                colors: <?= json_encode($color) ?>,
                grid: {
                    row: {
                        colors: ['#e5e5e5', 'transparent'],
                        opacity: 0.5
                    },
                    column: {
                        colors: ['#f8f8f8', 'transparent'],
                    },
                    xaxis: {
                        lines: {
                            show: true
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            var formatRupiah = new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR'
                            }).format(val);
                            return formatRupiah;
                        }
                    }
                }
            };
            var chart1 = new ApexCharts(document.querySelector("#invoice-ba-chart"), optionsColumn);
            chart1.render();
        });
    }(jQuery))
</script>
@endsection