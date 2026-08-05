<?php header('Content-type: text/xml'); ?>
<?php echo "<?xml version='1.0' encoding='iso-8859-1'?>\n"; ?>
<rows>
@foreach ($pivotRows as $row)
    <row id="{{ $row['PTK_Number'] }}">
        <cell>{{ $row['PTK_Number'] }}</cell>
        <cell>{{ $row['Current_Approver_By'] }}</cell>
        <cell>{{ $row['Seleksi Administrasi'] }}</cell>
        <cell>{{ $row['Psikotes'] }}</cell>
        <cell>{{ $row['Interview HR'] }}</cell>
        <cell>{{ $row['Interview User'] }}</cell>
        <cell>{{ $row['Tes Teknik'] }}</cell>
        <cell>{{ $row['MCU'] }}</cell>
        <cell>{{ $row['Offering Letter'] }}</cell>
        <cell>{{ $row['Keterangan'] }}</cell>
    </row>
@endforeach
</rows>
