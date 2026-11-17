{!! '<' . '?xml version="1.0" encoding="iso-8859-1"?>' !!}
<rows>
@php
    $kueri = DB::table('purchase_request')->orderBy('ID', 'DESC')->get();
    $nomor = 1;
@endphp

@foreach($kueri as $Fetch)
    @php
        $warna = ($Fetch->Status == 'APPROVED') ? "style='background-color:#d4edda'" : "style='background-color:#fff3cd'";
    @endphp
    <row id="{{ $Fetch->PR_No }}" {!! $warna !!}>
        <cell>{{ $nomor }}</cell>
        <cell>{{ htmlspecialchars($Fetch->PR_No) }}</cell>
        <cell>{{ htmlspecialchars($Fetch->PR_Date ?? '-') }}</cell>
        <cell>{{ htmlspecialchars($Fetch->Department ?? '-') }}</cell>
        <cell>{{ htmlspecialchars($Fetch->Material_Code ?? '-') }}</cell>
        <cell>{{ htmlspecialchars($Fetch->Product_Desc ?? '-') }}</cell>
        <cell>{{ htmlspecialchars($Fetch->Qty ?? 0) }}</cell>
        <cell>{{ htmlspecialchars($Fetch->Unit ?? 'PCS') }}</cell>
        <cell>{{ htmlspecialchars($Fetch->Status ?? 'PENDING') }}</cell>
    </row>
    @php $nomor++; @endphp
@endforeach
</rows>