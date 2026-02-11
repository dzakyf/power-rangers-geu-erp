<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$nama =  Auth::user()->Name;

$tanggal1 =  date("Y-m-d", strtotime("first day of january this year"));
$tanggal2 = date("Y-m-d");


$color = ['#3a86ff', '#fb5607', '#762761', '#9e7e78', '#8338ec', '#c9b135', '#ffbe0b', '#762761', '#094055', '#38A830', '#009DAB', '#ff006e',];

$Query = DB::select(DB::raw("SELECT Cust_ID,COUNT(ID) as OPENINV,SUM(AR_Val) as total,SUM(AR_Paid) as bayar,SUM(AR_Remain) as sisa FROM account_receive WHERE Location='$LSP' AND Delivered=1 AND Status IN('RECEIVED','PARTIAL','CLAIM_REQ') GROUP BY Cust_ID"));

$sum = array_sum(array_column($Query, 'sisa'));

$ar_monthly = DB::select(DB::raw("SELECT DATE_FORMAT(PTP, '%b-%Y') as tanggal,SUM(AR_Paid) as bayar FROM account_receive WHERE Location='$LSP' AND Delivered=1 AND Status = 'CLOSED' AND PTP BETWEEN '$tanggal1' AND '$tanggal2' GROUP BY tanggal ORDER BY tanggal ASC"));

$ar_monthly_tanggal = array_column($ar_monthly, 'tanggal');
$ar_monthly_nilai = array_column($ar_monthly, 'bayar');

$ba = DB::select(DB::raw("SELECT  DATE_FORMAT(Posting_Date,'%b-%Y') as tanggal, SUM(Total) as total FROM proforma WHERE  Posting_date BETWEEN '" . date("Y-m-d", strtotime($tanggal1)) . "' AND  '" . date("Y-m-d", strtotime($tanggal2)) . "' AND Status NOT IN ('EX','CANCELED','RETURN') GROUP BY tanggal ORDER BY tanggal ASC "));

$budget = DB::select(DB::raw("SELECT budget.`Budget_Year`,budget.`Department_Code`, 
sum(budget_detail.January_Value) as jan, 
sum(budget_detail.February_Value) as feb, 
sum(budget_detail.`March_Value`) as mar, 
sum(budget_detail.April_Value) as apr, 
sum(budget_detail.May_Value) as may, 
sum(budget_detail.June_Value) as jun, 
sum(budget_detail.July_Value) as jul,
sum( budget_detail.August_Value) as aug, 
sum(budget_detail.September_Value) as sep, 
sum(budget_detail.October_Value) as oct, 
sum(budget_detail.November_Value) as nov, 
sum(budget_detail.December_Value) as des 
FROM `budget` 
Join budget_detail on budget_detail.Task_ID = budget.ID

WHERE   budget.`Budget_Year` = " . date("Y") . " AND budget.`Department_Code` ='BUS' AND budget_detail.COA LIKE '%4%' group by budget.`Budget_Year`,budget.`Department_Code`"));

if ($budget) {
    $budgetValue = [$budget[0]->jan, $budget[0]->feb, $budget[0]->mar, $budget[0]->apr, $budget[0]->may, $budget[0]->jun, $budget[0]->jul, $budget[0]->aug, $budget[0]->sep, $budget[0]->oct, $budget[0]->nov, $budget[0]->des];
} else {
    $budgetValue = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
}

$ba_tanggal = array_column($ba, 'tanggal');
$ba_total = array_column($ba, 'total');
//$punya_ba = array_combine($ba_tanggal, $ba_total);

$invoice = DB::select(DB::raw("SELECT DATE_FORMAT(Inv_Date, '%b-%Y') as tanggal, SUM(Subtotal+mgm_fee) as total FROM invoice WHERE Status NOT IN ('RETURN','CANCELED') AND Inv_Date BETWEEN '$tanggal1' AND '$tanggal2' GROUP BY tanggal ORDER BY tanggal ASC "));
$invoice_tanggal = array_column($invoice, 'tanggal');
$invoice_total = array_column($invoice, 'total');
$punya_invoice = array_combine($invoice_tanggal, $invoice_total);
$tanggal = array_unique(array_merge($ba_tanggal, $invoice_tanggal));
$tanggal_tahun = ['jan-' . date("Y"), 'feb-' . date("Y"), 'mar-' . date("Y"), 'apr-' . date("Y"), 'mei-' . date("Y"), 'jun-' . date("Y"), 'jul-' . date("Y"), 'agt-' . date("Y"), 'sep-' . date("Y"), 'oct-' . date("Y"), 'nov-' . date("Y"), 'dec-' . date("Y"),];

$invoice_connect = [];
foreach ($tanggal as $key => $value) {
    if (isset($punya_invoice[$value])) {
        $invoice_connect[] = $punya_invoice[$value];
    } else {
        $invoice_connect[] = 0;
    }
}

$sum_ba = array_sum($ba_total);
$sum_invoice = array_sum($invoice_total);
?>
@extends('layout.mainlayout-finance')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">

</div>
<!-- End Main Content Header -->
<div class="row">
    <div class="col-sm-6">
        <div class="row">
            <div class="col-12">
                <div class="stats-card-two mb-30">
                    <div class="media align-items-center justify-content-between">
                        <div class="ml-0">
                            <p class="mb-10 line-height-1">AR Remain</p>
                            <h3 class="mb-0 fs-25">Rp. <?= number_format($sum, 0, ',', '.');  ?> </h3>
                        </div>
                        <div class="avatar avatar-blue">
                            <i data-feather="activity" class="icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="header card-header">
                        <span> AR Remain </span><button type="button" class="btn btn-primary mr-1 float-right" data-toggle="modal" data-target="#modalArTabel">Detail</button>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <div id="ar-remain-chart" class="col-12"></div>

                        </div>

                    </div>
                </div>
            </div>
            <div class="col-12">

                <div class="card">
                    <div class="header card-header">
                        <span> Invoice PAID </span>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <div id="ar-monthly" class="col-12"></div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6">
        <div class="row">
            <div class="col-6">

                <div class="stats-card-two mb-30">
                    <div class="media align-items-center justify-content-between">
                        <div class="ml-0">
                            <p class="mb-10 line-height-1">Penagihan (Berita Acara)</p>
                            <h3 class="mb-0 fs-18">Rp. <?= number_format($sum_ba, 0, ',', '.');  ?></h3>
                        </div>
                        <div class="avatar avatar-blue">
                            <i data-feather="shopping-cart" class="icon"></i>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-6">
                <div class="stats-card-two mb-30">
                    <div class="media align-items-center justify-content-between">
                        <div class="ml-0">
                            <p class="mb-10 line-height-1">Invoice</p>
                            <h3 class="mb-0 fs-18">Rp. <?= number_format($sum_invoice, 0, ',', '.');  ?></h3>
                        </div>
                        <div class="avatar avatar-blue">
                            <i data-feather="shopping-bag" class="icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="header card-header">
                        <span> Invoice Terhadap Berita Acara </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div id="invoice-ba-chart" class="col-12"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="stats-card-two mb-30">
                    <div class="media align-items-center justify-content-between">
                        <div class="ml-0">
                            <p class="mb-10 line-height-1">Selisih BA dan Invoice</p>
                            <h3 class="mb-0 fs-16">Rp. <?= number_format($sum_ba - $sum_invoice, 0, ',', '.');  ?> </h3>
                            <ul class="list-group">
                                <?php
                                $ba_sum = DB::select(DB::raw("SELECT SUM(IF(Status = 'CREATED' OR Status = 'ATTACHED' OR Status = 'PROPOSED' OR Status = 'PROPOSED1',Total,NULL)) AS 'progress',

                               SUM(IF(Status = 'POSTED',Total,NULL)) AS 'posted',
                               SUM(IF(Status = 'RECEIVED',Total,NULL)) AS 'received'
                               FROM proforma WHERE Dateinsert BETWEEN '" . date("Y-m-d", strtotime($tanggal1)) . "' AND  '" . date("Y-m-d", strtotime($tanggal2)) . "' AND Status NOT IN ('EX','CANCELED','RETURN') "));


                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Progress
                                    <span class="badge badge-primary badge-pill"> Rp. <?= number_format($ba_sum[0]->progress, 0, ',', '.');  ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Posted
                                    <span class="badge badge-primary badge-pill"> Rp. <?= number_format($ba_sum[0]->posted, 0, ',', '.'); ?></span>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    RECEIVED BY FINANCE
                                    <span class="badge badge-primary badge-pill"> Rp. <?= number_format($ba_sum[0]->received, 0, ',', '.');  ?></span>
                                </li>



                            </ul>
                        </div>
                        <div class="avatar avatar-blue">
                            <i data-feather="activity" class="icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>



<div id="modalArTabel" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Ar Remain</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <table class="table m-0 text-vertical-middle table-bordered table-striped">
                        <thead class="bort-none borpt-0">
                            <tr>
                                <th class="text-center">Customer</th>
                                <th class="text-center">Remain</th>
                                <th class="text-center">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>



                            <?php

                            $chartName = [];
                            $chartValue = [];
                            $nomor = 0;
                            $jumlah = 0;
                            foreach ($Query as $Fetch) {
                                $customer = DB::table("customerz")->where("Customer_No", $Fetch->Cust_ID)->first();
                                $chartName[] = $customer->Company_Name;
                                $chartValue[] = $Fetch->sisa;
                            ?>
                                <tr>
                                    <td>
                                        <?= $customer->Company_Name ?>
                                    </td>

                                    <td class="text-right">
                                        <span class="badge badge-primary badge-pill text-right" style="background-color:<?= $color[$nomor] ?>">Rp.
                                            <?= number_format($Fetch->sisa, 2, ',', '.'); ?></span>
                                    </td>

                                    <td class="text-right">
                                        <?= $sum == 0 ? 0 : number_format(($Fetch->sisa / $sum) * 100, 2, ',', '.'); ?> %
                                    </td>
                                </tr>

                            <?php
                                $jumlah += $Fetch->sisa;
                                $nomor++;
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr class="text-white" style="background-color:#005792;">
                                <td class="font-weight-bold text-white">Total</td>
                                <td class="font-weight-bold text-white text-right">
                                    <span class="text-right"></span>Rp. <?= number_format($jumlah, 2, ',', '.');  ?>
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
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
                    type: 'pie',
                },
                colors: <?= json_encode($color) ?>,
                tooltip: {
                    enabled: true,
                    y: {
                        formatter: function(val) {
                            var formatRupiah = new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR'
                            }).format(val);
                            return formatRupiah;
                        }
                    }
                },
                labels: <?= json_encode($chartName) ?>,
                series: <?= json_encode($chartValue) ?>,
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }],
                legend: {
                    horizontalAlign: 'bottom',
                    position: 'bottom',
                }
            }
            var chart = new ApexCharts(document.querySelector("#ar-remain-chart"), options);
            chart.render();

            var optionsColumn = {
                series: [{
                        name: 'Budget',
                        data: <?= json_encode($budgetValue) ?>
                    },
                    {
                        name: 'Invoice',
                        data: <?= json_encode($invoice_connect) ?>
                    }, {
                        name: 'Ba',
                        data: <?= json_encode($ba_total) ?>
                    },

                ],
                chart: {
                    type: 'bar',
                    height: 300,
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '70%',
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
                    categories: <?= json_encode($tanggal_tahun) ?>,
                    tickPlacement: 'on'
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

            var optionsColumn2 = {
                series: [{
                        name: 'Invoice',
                        data: <?= json_encode($invoice_connect) ?>
                    },
                    {
                        name: 'Pembayaran',
                        data: <?= json_encode($ar_monthly_nilai) ?>
                    }
                ],
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
                    categories: <?= json_encode($tanggal) ?>,
                    tickPlacement: 'on'
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

            var chart2 = new ApexCharts(document.querySelector("#ar-monthly"), optionsColumn2);
            chart2.render();


        });
    }(jQuery))


    var myCalendar = new dhtmlXCalendarObject(["tanggal2", "tanggal1"]);
    myCalendar.setSkin('dhx_skyblue');
    myCalendar.setDateFormat("%Y-%m-%d");
</script>
@endsection