{{-- Cetak PO. Render mPDF via ProcurementController@printPO. Kolom: purchase_order + purchase_order_dtl + supplier. --}}
<style>
  * { margin:0; padding:0; }
  body { font-family: Arial, sans-serif; font-size:9pt; color:#000; }
  table { border-collapse:collapse; }
  .title { text-align:center; font-weight:bold; font-size:13pt; }
  .sub { text-align:center; font-size:9pt; margin:2px 0 8px 0; }
  .head td { border:1px solid #000; font-size:8.5pt; padding:4px 6px; vertical-align:top; }
  .items th, .items td { border:1px solid #000; font-size:8pt; padding:4px; }
  .items th { background:#eee; text-align:center; }
  .num { text-align:center; } .r { text-align:right; }
  .terms { font-size:8.5pt; line-height:1.6; margin-top:8px; }
  .sig td { font-size:9pt; text-align:center; padding:4px; vertical-align:top; }
</style>

<div style="margin:0 35px;">
  <div class="title">{{ $doc['po_title'] }}</div>
  <div class="sub">No. PO : {{ $po->PO_No }} &nbsp;&nbsp; Tanggal : {{ $po->PO_Date }}</div>

  <table width="100%" class="head" cellspacing="0" cellpadding="0">
    <tr>
      <td width="16%"><b>Pemesan</b></td><td width="34%"></td>
      <td width="16%"><b>Kepada</b></td><td width="34%"></td>
    </tr>
    <tr>
      <td>Nama PT</td><td>: PT Green Energi Utama</td>
      <td>Nama Vendor</td><td>: {{ $vendor->Company_Name ?? $po->Supplier }}</td>
    </tr>
    <tr>
      <td>Alamat Pengiriman</td><td>: {{ $po->PIC_Address ?? ($pr->PIC_Address ?? 'Sesuai lampiran') }}</td>
      <td>Alamat</td><td>: {{ trim(($vendor->Address ?? '') . ' ' . ($vendor->City ?? '')) ?: '-' }}</td>
    </tr>
    <tr>
      <td>Telepon</td><td>: {{ $po->PIC_Phone ?? ($pr->PIC_Phone ?? '-') }}</td>
      <td>Telepon</td><td>: {{ $vendor->Phone ?? '-' }} {{ $vendor->Contact_Person ?? '' }}</td>
    </tr>
  </table>

  <p style="font-size:9pt; margin:8px 0 4px 0;">Dengan ini akan melakukan pemesanan barang sebagai berikut:</p>

  <table width="100%" class="items" cellspacing="0" cellpadding="0">
    <thead>
      <tr>
        <th width="4%">No</th><th width="20%">DESKRIPSI</th><th width="28%">SPESIFIKASI</th>
        <th width="8%">QTY</th>
        @if($isRent)<th width="8%">PERIODE SEWA</th>@endif
        <th width="11%">HARGA/UNIT</th>
        @if($isRent)<th width="11%">TOTAL PERBULAN</th><th width="11%">TOTAL 12 BULAN</th>
        @else<th width="12%">TOTAL</th>@endif
      </tr>
    </thead>
    <tbody>
      @foreach($details as $i => $d)
      <tr>
        <td class="num">{{ $i + 1 }}.</td>
        <td>{{ $d->Product_Desc }} {{ $d->Product_Code }}</td>
        <td>{{ $d->Specification }}</td>
        <td class="num">{{ rtrim(rtrim($d->Qty, '0'), '.') }} {{ $d->Unit }}</td>
        @if($isRent)<td class="num">{{ $d->Remark }}</td>@endif
        <td class="r">Rp {{ number_format($d->Price, 0, ',', '.') }}</td>
        @if($isRent)
        <td class="r">Rp {{ number_format($d->Subtotal, 0, ',', '.') }}</td>
        <td class="r">Rp {{ number_format($d->Total, 0, ',', '.') }}</td>
        @else
        <td class="r">Rp {{ number_format($d->Total, 0, ',', '.') }}</td>
        @endif
      </tr>
      @endforeach
      <tr>
        <td class="num"><b>Total</b></td>
        <td><b>{{ $details->sum('Qty') }}</b></td>
        <td colspan="{{ $isRent ? 6 : 4 }}"></td>
      </tr>
    </tbody>
  </table>

  <table width="100%" cellspacing="0" cellpadding="0" style="margin-top:6px; font-size:8.5pt;">
    <tr><td width="20%">Subtotal</td><td>: Rp {{ number_format($po->Subtotal, 0, ',', '.') }}</td></tr>
    <tr><td>PPN</td><td>: Rp {{ number_format($po->PPN, 0, ',', '.') }}</td></tr>
    <tr><td><b>Grand Total</b></td><td><b>: Rp {{ number_format($po->Total, 0, ',', '.') }}</b></td></tr>
    <tr><td>Ref PR</td><td>: {{ $po->PR_No }}</td></tr>
  </table>

  <div class="terms">
    <b>KETENTUAN</b>
    <ol>
      @foreach($doc['po_terms'] as $t)<li>{{ $t }}</li>@endforeach
    </ol>
    <p style="margin-top:6px;">Demikian PO ini dibuat, sebagai acuan untuk pemenuhan kebutuhan dari Perusahaan kami dan penerbitan invoice. Terima kasih</p>
  </div>

  <p style="margin-top:10px;">Malang, {{ date('d F Y', strtotime($po->PO_Date)) }}</p>
  <table width="100%" class="sig" cellspacing="0" cellpadding="0" style="margin-top:6px;">
    <tr>
      <td width="33%">Dibuat oleh,<br><br><br><br><b>{{ $po->Process_By }}</b><br>Warehouse & Logistic Mgr.</td>
      <td width="33%">Mengetahui,<br><br><br><br><b>{{ $po->Confirmed_By ?? '' }}</b><br>HCGA Mgr.</td>
      <td width="34%">Menyetujui<br><br><br><br><b>(_______________)</b><br>Penyedia Barang/Vendor</td>
    </tr>
  </table>
</div>