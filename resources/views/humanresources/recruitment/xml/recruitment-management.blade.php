<?php header('Content-type: text/xml'); ?>
<?php echo "<?xml version='1.0' encoding='iso-8859-1'?>\n"; ?>
<rows>
@php $nomor = 1; @endphp
@foreach ($candidates as $Fetch)
    @php
        $pecahin = explode(',', (string) $Fetch->Requisition_ID);
    @endphp
    @foreach ($pecahin as $pecahreq)
        @php
            $Requisition = $requisitions->get($pecahreq);
            $warna1 = $warna2 = $warna3 = $warna4 = $warna5 = $warna6 = $warna7 = $warna8 = $warna9 = '';
            $warnapending = '';
            $statusPending = 0;
            $status1 = 0;
            $status2 = 0;
            $status3 = 0;
            $status4 = 0;
            $status5 = 0;
            $status6 = 0;
            $status7 = 0;
            $status8 = 0;
            $status9 = 0;
            
            if ($Fetch->Status == 'CREATED' || $Fetch->Status == 'UPLOADED') {
                $status1 = 1;
                $warna2 = "style='backgorund-color:red'";
            } elseif ($Fetch->Status == 'PSIKOTEST') {
                $status2 = 1;
                $warna2 = "style='backgorund-color:red'";
            } elseif ($Fetch->Status == 'PENDING') {
                $statusPending = 1;
                $warnapending = "style='backgorund-color:red'";
            } elseif ($Fetch->Status == 'INTERVIEWED') {
                $status3 = 1;
                $warna3 = "style='backgorund-color:red'";
            } elseif ($Fetch->Status == 'BUSINESS_REVIEW') {
                $status4 = 1;
                $warna4 = "style='backgorund-color:red'";
            } elseif ($Fetch->Status == 'INTERVIEWED') {
                $status5 = 1;
                $warna5 = "style='backgorund-color:red'";
            } elseif ($Fetch->Status == 'CLOSED') {
                $status6 = 1;
                $warna6 = "style='backgorund-color:red'";
            } elseif ($Fetch->Status == 'REJECT') {
                $status7 = 1;
                $warna5 = "style='backgorund-color:red'";
                $warna7 = "style='backgorund-color:red'";
            } elseif ($Fetch->Status == 'OFFER DECLINED') {
                $status8 = 1;
                $warna8 = "style='backgorund-color:red'";
            } elseif ($Fetch->Status == 'INVITATION DECLINED') {
                $status9 = 1;
                $warna9 = "style='backgorund-color:red'";
            }
        @endphp
        <row id="{{ $Fetch->ID }}|{{ $nomor }}">
            <cell>{{ $nomor }}</cell>
            <cell>{{ $Fetch->Recruitment_Type }}</cell>
            <cell>{{ $Fetch->Name }}</cell>
            <cell>{{ $Fetch->Gender }}</cell>
            <cell>{{ $Requisition ? $Requisition->PO_No : '' }}</cell>
            <cell>{{ $Requisition ? $Requisition->Job_Position : '' }}</cell>
            <cell>{{ $Fetch->Last_Education }}</cell>
            <cell>{{ $Fetch->Education_Major }}</cell>
            <cell>{{ $Fetch->Interview_By }}</cell>
            <cell {{ $warna1 }}>{{ $status1 }}</cell>
            <cell {{ $warna2 }}>{{ $status2 }}</cell>
            <cell {{ $warna9 }}>{{ $status9 }}</cell>
            <cell {{ $warna7 }}>{{ $status7 }}</cell>
            <cell {{ $warna3 }}>{{ $status3 }}</cell>
            <cell {{ $warnapending }}>{{ $statusPending }}</cell>
            <cell {{ $warna4 }}>{{ $status4 }}</cell>
            <cell {{ $warna5 }}>{{ $status5 }}</cell>
            <cell {{ $warna8 }}>{{ $status8 }}</cell>
            <cell {{ $warna6 }}>{{ $status6 }}</cell>
        </row>
        @php $nomor++; @endphp
    @endforeach
@endforeach
</rows>
