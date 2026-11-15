{!! '<' . '?xml version="1.0" encoding="iso-8859-1"?>' !!}
<rows>
@php
    $kueri = DB::table('purchase_request')->orderBy('ID', 'DESC')->get();
    $nomor = 1;
@endphp

@foreach($kueri as $Fetch)
    @php
        $warna = ($Fetch->Status == 'APPROVED') ? "style='background-color:#d4edda'" : "style='background-color:#fff3cd'";
        
        // Ambil data rincian item pertama dan total Qty dari tabel purchase_request_dtl
        $detailFirst = DB::table('purchase_request_dtl')->where('PR_No', $Fetch->PR_No)->first();
        $totalQty = DB::table('purchase_request_dtl')->where('PR_No', $Fetch->PR_No)->sum('Qty');
        
        $materialCode = $detailFirst ? $detailFirst->Product_Code : '-';
        $productDesc  = $detailFirst ? $detailFirst->Product_Desc : '-';
        $unit         = $detailFirst ? $detailFirst->Unit : 'PCS';
    @endphp
    {{-- Tambahkan atribut status di sini agar JavaScript bisa membaca status baris dengan mudah --}}
    <row id="{{ $Fetch->PR_No }}" status="{{ $Fetch->Status ?? 'PENDING' }}" {!! $warna !!}>
        <cell>{{ $nomor }}</cell>
        <cell>{{ htmlspecialchars($Fetch->PR_No) }}</cell>
        <cell>{{ htmlspecialchars($Fetch->PR_Date ?? '-') }}</cell>
        <cell>{{ htmlspecialchars($Fetch->Department ?? '-') }}</cell>
        <cell>{{ htmlspecialchars($Fetch->Note ?? '-') }}</cell>
        <cell>{{ htmlspecialchars($Fetch->Created_By ?? '-') }}</cell>
        <cell>{{ htmlspecialchars($totalQty > 0 ? $totalQty : ($Fetch->Qty ?? 0)) }}</cell>
        <cell>{{ htmlspecialchars($unit) }}</cell>
        <cell>{{ htmlspecialchars($Fetch->Status ?? 'PENDING') }}</cell>
    </row>
    @php $nomor++; @endphp
@endforeach
</rows>