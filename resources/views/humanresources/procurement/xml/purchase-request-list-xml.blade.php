{!! '<' . '?xml version="1.0" encoding="iso-8859-1"?>' !!}
<rows>
@php
    $kueri = DB::table('purchase_request')->orderBy('ID', 'DESC')->get();
    $nomor = 1;
@endphp

@foreach($kueri as $Fetch)
    @php
        $statusUpper = strtoupper(trim((string) ($Fetch->Status ?? 'PENDING')));

        if ($statusUpper === 'APPROVED') {
            $warna = "style='background-color:#d4edda; color:#155724;'";
        } elseif (in_array($statusUpper, ['CANCELLED', 'CANCELED', 'REJECTED'], true)) {
            $warna = "style='background-color:#f8d7da; color:#721c24;'";
        } else {
            $warna = "style='background-color:#fff3cd; color:#856404;'";
        }

        // Ambil data rincian item pertama dan total Qty dari tabel purchase_request_dtl
        $detailFirst = DB::table('purchase_request_dtl')->where('PR_No', $Fetch->PR_No)->first();
        $totalQty = DB::table('purchase_request_dtl')->where('PR_No', $Fetch->PR_No)->sum('Qty');

        $materialCode = $detailFirst ? $detailFirst->Product_Code : '-';
        $productDesc  = $detailFirst ? $detailFirst->Product_Desc : '-';
        $unit         = $detailFirst ? $detailFirst->Unit : 'PCS';
    @endphp
    <row id="{{ $Fetch->PR_No }}" status="{{ $statusUpper }}" {!! $warna !!}>
        <cell>{{ $nomor }}</cell>
        <cell>{{ htmlspecialchars($Fetch->PR_No) }}</cell>
        <cell>{{ htmlspecialchars($Fetch->PR_Date ?? '-') }}</cell>
        <cell>{{ htmlspecialchars($Fetch->Department ?? '-') }}</cell>
        <cell>{{ htmlspecialchars($statusUpper) }}</cell>
        <cell>{{ htmlspecialchars($Fetch->Note ?? '-') }}</cell>
        <cell>{{ htmlspecialchars($Fetch->PIC ?? '-') }}</cell>
        <cell>{{ htmlspecialchars($Fetch->PIC_Phone ?? '-') }}</cell>
        <cell>{{ htmlspecialchars($Fetch->PIC_Email ?? '-') }}</cell>
        <cell>{{ htmlspecialchars($Fetch->PIC_Address ?? '-') }}</cell>
        <cell>{{ htmlspecialchars($totalQty > 0 ? $totalQty : ($Fetch->Qty ?? 0)) }}</cell>
        <cell>{{ htmlspecialchars($unit) }}</cell>
        <cell>{{ htmlspecialchars($Fetch->Sub_Category ?? '-') }}</cell>
        <cell>{{ htmlspecialchars($Fetch->Estimated_Budget ?? '0') }}</cell>
    </row>
    @php $nomor++; @endphp
@endforeach
</rows>