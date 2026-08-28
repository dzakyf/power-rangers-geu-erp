@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="hrd-reimbursement-index">Reimbursement</a></li>
            <li class="breadcrumb-item active"><span class="active">Detail Reimbursement</span></li>
        </ol>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title m-0">Detail Reimbursement : {{ $reimbursement->Reimbursement_No }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Status</label>
                                <div>
                                    <b style="color:{{ $reimbursement->Status == 'CLOSED' ? 'green' : (($reimbursement->Status == 'REJECT' || $reimbursement->Status == 'CANCELED') ? 'red' : 'orange') }};">{{ $reimbursement->Status }}</b>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>No Reimbursement</label>
                                <div>{{ $reimbursement->Reimbursement_No }}</div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Departemen</label>
                                <div>{{ $reimbursement->Department }}</div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Karyawan</label>
                                <div>{{ $reimbursement->User_Responsibility_Name }} ({{ $reimbursement->User_Responsibility_ID }})</div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Reference No</label>
                                <div>{{ $reimbursement->Reference_No ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Tanggal Reimbursement</label>
                                <div>{{ $reimbursement->Reimbursement_Date }}</div>
                            </div>
                        </div>

                        <div class="col-md-12 col-12">
                            <div class="form-group">
                                <label>Keterangan</label>
                                <div>{{ $reimbursement->Description }}</div>
                            </div>
                        </div>

                        @if ($reimbursement->Payment_Type || $reimbursement->Payment_Date)
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Pembayaran</label>
                                <div>
                                    {{ $reimbursement->Payment_Type ?: '-' }}
                                    @if ($reimbursement->Payment_Reference_No) / {{ $reimbursement->Payment_Reference_No }} @endif
                                    @if ($reimbursement->Payment_Date) / {{ $reimbursement->Payment_Date }} @endif
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>

                    <h5 class="mt-3">Rincian Biaya</h5>
                    <table class="table table-bordered" width="70%">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Reference</th>
                                <th>Keterangan</th>
                                <th>Jumlah (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $total = 0; @endphp
                            @foreach ($dtls as $i => $d)
                                @php $total += (float) $d->Amount; @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $d->Reference }}</td>
                                    <td>{{ $d->Remark }}</td>
                                    <td align="right">{{ number_format((float) $d->Amount, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr class="font-weight-bold">
                                <td colspan="3">Total</td>
                                <td align="right">{{ number_format($total, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <h5 class="mt-3">Progress History</h5>
                    <table class="table table-bordered" width="70%">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Transaction</th>
                                <th>Status</th>
                                <th>Description</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $i => $l)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $l->Transaction }}</td>
                                <td>{{ $l->Status }}</td>
                                <td>{{ $l->Description }}</td>
                                <td>{{ $l->Created_Date }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="col-12 mt-2">
                        <a href="hrd-reimbursement-index" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
