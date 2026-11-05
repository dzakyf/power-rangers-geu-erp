{{--
    Header form PTK (FR/HCGA/19-15) -> dipakai sebagai HTML header mPDF (tampil di setiap halaman).
    Semua style INLINE (header mPDF dirender terpisah dari <style> pada body).
--}}
<table width="100%" cellspacing="0" cellpadding="0"
       style="border-collapse:collapse; font-family:Arial,Helvetica,sans-serif; font-size:8.5pt; color:#000;
              border-top:1.2pt solid #000; border-left:1.2pt solid #000; border-right:1.2pt solid #000; border-bottom:0.75pt solid #000;">
  <tr>
    <td rowspan="2" width="28%"
        style="text-align:center; vertical-align:middle; padding:2mm; border-right:0.75pt solid #000;">
      @if(!empty($logoSrc))
        <img src="{{ $logoSrc }}" alt="Logo" width="130">
      @elseif(file_exists(public_path('assets/images/large-logo.png')))
        <img src="{{ public_path('assets/images/large-logo.png') }}" alt="Logo" width="130">
      @else
        <strong>HCGA</strong>
      @endif
    </td>
    <td colspan="4"
        style="text-align:center; font-size:9.5pt; padding:1.2mm 2mm; border-bottom:0.75pt solid #000;">
      Permintaan Tenaga Kerja
    </td>
  </tr>
  <tr>
    <td width="16%" style="font-size:7.5pt; line-height:1.3; vertical-align:top; padding:1.2mm 2mm; border-right:0.75pt solid #000;">
      Process Owner :<br>HCGA
    </td>
    <td width="16%" style="font-size:7.5pt; line-height:1.3; vertical-align:top; padding:1.2mm 2mm; border-right:0.75pt solid #000;">
      Revision :<br>{{ $revision ?? '#00' }}
    </td>
    <td width="16%" style="font-size:7.5pt; line-height:1.3; vertical-align:top; padding:1.2mm 2mm; border-right:0.75pt solid #000;">
      Effective Date :<br>{{ $effectiveDate ?? '04-02-2025' }}
    </td>
    <td width="24%" style="font-size:8.5pt; text-align:center; vertical-align:middle; padding:1.2mm 2mm;">
      Doc ID :<br>{{ $docId ?? 'FR/HCGA/19-15' }}
    </td>
  </tr>
</table>