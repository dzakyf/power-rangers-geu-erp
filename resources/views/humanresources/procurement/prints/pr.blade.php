{{-- Cetak PR. Render mPDF via ProcurementController@printPR. Kolom: purchase_request + purchase_request_dtl. --}}
<style>
  * { margin:0; padding:0; }
  body { font-family: Arial, sans-serif; font-size:9pt; color:#000; }
  table { border-collapse:collapse; }
  .kop td { border:1px solid #000; }
  .title { text-align:center; font-weight:bold; font-size:13pt; padding:6px; }
  .meta { font-size:7.5pt; padding:3px 5px; vertical-align:top; }
  .h { font-size:8pt; padding:3px 6px; }
  .items th, .items td { border:1px solid #000; font-size:8pt; padding:4px; }
  .items th { background:#eee; text-align:center; }
  .num { text-align:center; } .r { text-align:right; }
  .sig td { font-size:8pt; text-align:center; padding:4px; vertical-align:top; }
</style>

<div style="margin:0 35px;">
  <table width="100%" class="kop" cellspacing="0" cellpadding="0">
    <tr>
      <td width="60%" class="title">{{ $doc['pr_title'] }}</td>
      <td class="meta">Process Owner : {{ $doc['pr_owner'] }}<br>Revision : {{ $doc['pr_rev'] }}<br>Nomor : {{ $pr->PR_No }}</td>
      <td class="meta">Effective Date : {{ $doc['pr_effective'] }}<br>Doc ID : {{ $doc['pr_doc_id'] }}</td>
    </tr>
  </table>

  <table width="100%" cellspacing="0" cellpadding="0" style="margin-top:6px;">
    <tr>
      <td class="h" width="65%">Please supply the requested items :</td>
      <td class="h">Date : {{ $pr->PR_Date }}</td>
    </tr>
    <tr>
      <td class="h"></td>
      <td class="h">Site : {{ $pr->Location }}</td>
    </tr>
    <tr>
      <td class="h">Department : {{ $pr->Department }} &nbsp; Category : {{ $pr->Category }} / {{ $pr->Sub_Category }}</td>
      <td class="h">Page : 1</td>
    </tr>
  </table>

  <table width="100%" class="items" cellspacing="0" cellpadding="0" style="margin-top:6px;">
    <thead>
      <tr>
        <th width="5%">No.</th><th width="14%">Item Code</th><th width="36%">Description of Goods</th>
        <th width="8%">Unit</th><th width="7%">Qty</th><th width="14%">Required Date</th><th width="16%">Price</th>
      </tr>
    </thead>
    <tbody>
      @forelse($details as $i => $d)
      <tr>
        <td class="num">{{ $i + 1 }}</td>
        <td>{{ $d->Product_Code }}</td>
        <td>{{ $d->Product_Desc }}@if($d->Specification)<br><small>{{ $d->Specification }}</small>@endif</td>
        <td class="num">{{ $d->Unit }}</td>
        <td class="num">{{ $d->Qty }}</td>
        <td class="num">{{ $pr->PR_Date }}</td>
        <td class="r">Rp {{ number_format(($d->Prices ?: $d->Default_Price) * 1, 0, ',', '.') }}</td>
      </tr>
      @empty
      <tr><td class="num">1</td><td colspan="6">&nbsp;</td></tr>
      <tr><td class="num">2</td><td colspan="6">&nbsp;</td></tr>
      @endforelse
      @for($k = count($details); $k < 5; $k++)
      <tr><td class="num">{{ $k + 1 }}</td><td colspan="6">&nbsp;</td></tr>
      @endfor
    </tbody>
  </table>

  <table width="100%" cellspacing="0" cellpadding="0" style="margin-top:6px; font-size:8pt;">
    <tr><td>Preferred Vendor : {{ $pr->Supplier_Name }} ({{ $pr->Supplier_ID }})</td></tr>
    <tr><td>Remarks : {{ $pr->Note ?: $pr->Remark }} &nbsp; | &nbsp; PIC : {{ $pr->PIC }} {{ $pr->PIC_Phone }} {{ $pr->PIC_Address }}</td></tr>
  </table>

  <table width="100%" class="sig" cellspacing="0" cellpadding="0" style="margin-top:10px;">
    <tr>
      <td width="25%">Requested By<br><br><br><br><b>{{ $creator->Name ?? $pr->Created_By }}</b><br>{{ $pr->Created_Date }}</td>
      @for($s = 0; $s < 3; $s++)
      <td width="25%">Approved By<br><br><br><br><b>{{ $appr[$s]->Action_By ?? '' }}</b><br>{{ isset($appr[$s]) ? $appr[$s]->Action_Date : '' }}</td>
      @endfor
    </tr>
    <tr><td colspan="3"></td><td>HCGA Manager</td></tr>
  </table>
</div>
