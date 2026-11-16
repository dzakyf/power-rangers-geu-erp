@extends('layout.mainlayout-candidate')

@section('content-wrapper')
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">Asesmen & Hasil Tes</h4>
            <span class="text-muted mt-1 tx-13 ms-2 mb-0">/ Riwayat Evaluasi</span>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body text-center py-4">
        <h5 class="text-primary fw-bold mb-2"><i class="fa fa-clipboard-check me-2"></i>Informasi Asesmen Kandidat</h5>
        <p class="text-muted mb-0">Hasil tes yang telah dipublikasikan oleh tim HCGA akan tampil pada tabel di bawah ini.</p>
    </div>
</div>

@if($rec && ($rec->Psikotest_Score || $rec->Test_Score || $rec->Technical_Score))
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 fw-bold text-primary">Hasil Tes dari Tim HR / HCGA</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Jenis Tes</th>
                        <th style="width: 140px;" class="text-center">Skor / Nilai</th>
                        <th>Catatan</th>
                        <th style="width: 140px;" class="text-center">Lampiran</th>
                    </tr>
                </thead>
                <tbody>
                    @if($rec->Psikotest_Score)
                    <tr>
                        <td class="fw-semibold">Psikotest</td>
                        <td class="text-center"><span class="badge bg-info text-white">{{ $rec->Psikotest_Score }}</span></td>
                        <td>{{ $rec->Psikotest_Note ?? '-' }}</td>
                        <td class="text-center">
                            @if(!empty($rec->Psikotest_File))
                                <a href="{{ asset($rec->Psikotest_File) }}" class="btn btn-sm btn-outline-primary" target="_blank">Download</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endif
                    @if($rec->Test_Score)
                    <tr>
                        <td class="fw-semibold">User Test</td>
                        <td class="text-center"><span class="badge bg-info text-white">{{ $rec->Test_Score }}</span></td>
                        <td>{{ $rec->Test_Note ?? '-' }}</td>
                        <td class="text-center">
                            @if(!empty($rec->Test_File))
                                <a href="{{ asset($rec->Test_File) }}" class="btn btn-sm btn-outline-primary" target="_blank">Download</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endif
                    @if($rec->Technical_Score)
                    <tr>
                        <td class="fw-semibold">Technical Test</td>
                        <td class="text-center"><span class="badge bg-info text-white">{{ $rec->Technical_Score }}</span></td>
                        <td>{{ $rec->Technical_Note ?? '-' }}</td>
                        <td class="text-center">
                            @if(!empty($rec->Technical_File))
                                <a href="{{ asset($rec->Technical_File) }}" class="btn btn-sm btn-outline-primary" target="_blank">Download</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="card shadow-sm border-0">
    <div class="card-body text-center py-5">
        <p class="text-muted mb-0">Belum ada hasil tes yang dipublikasikan oleh HR.</p>
    </div>
</div>
@endif
@endsection
