<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Offering Letter - {{ $candidateName }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing:antialiased; color:#1e293b;">

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#f1f5f9; padding:30px 15px;">
  <tr>
    <td align="center">
      <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width:640px; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 18px rgba(0,0,0,0.06); border:1px solid #e2e8f0;">
        
        <!-- Header Banner -->
        <tr>
          <td style="background:linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #0284c7 100%); padding:28px 32px; text-align:left;">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td>
                  <span style="display:inline-block; font-size:11px; font-weight:700; color:#93c5fd; letter-spacing:1.5px; text-transform:uppercase; margin-bottom:4px;">
                    PT GREEN ENERGI UTAMA &bull; HUMAN CAPITAL
                  </span>
                  <h1 style="margin:0 0 6px 0; color:#ffffff; font-size:22px; font-weight:700; letter-spacing:0.3px;">
                    Surat Penawaran Kerja (Offering Letter)
                  </h1>
                  <p style="margin:0; color:#e0f2fe; font-size:13px; line-height:1.4;">
                    No. Dokumen: <strong>{{ $offeringNumber ?? '-' }}</strong>
                  </p>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Main Body -->
        <tr>
          <td style="padding:28px 32px;">
            <p style="margin:0 0 14px 0; color:#334155; font-size:14px; line-height:1.6;">
              Kepada Yth.<br>
              <strong style="font-size:16px; color:#0f172a;">{{ $candidateName }}</strong><br>
              Di Tempat
            </p>

            <p style="margin:0 0 16px 0; color:#334155; font-size:14px; line-height:1.6; text-align:justify;">
              Selamat! Kami ucapkan terima kasih atas partisipasi dan antusiasme Anda dalam mengikuti seluruh tahapan seleksi rekrutmen di <strong>PT Green Energi Utama</strong>. Berdasarkan hasil evaluasi kualifikasi dan kompetensi Anda yang memuaskan, dengan ini kami bermaksud mengajukan penawaran resmi pekerjaan (<em>Offering Letter</em>) kepada Anda dengan rincian ringkas sebagai berikut:
            </p>

            <!-- Card Highlight Detail Penawaran -->
            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; overflow:hidden; margin-bottom:20px;">
              <tr>
                <td style="padding:10px 16px; border-bottom:1px solid #e2e8f0; color:#64748b; font-size:13px; font-weight:600; width:38%;">Posisi / Jabatan</td>
                <td style="padding:10px 16px; border-bottom:1px solid #e2e8f0; color:#0f172a; font-size:13px; font-weight:700;">{{ $position ?? '-' }}</td>
              </tr>
              @if(!empty($grading))
              <tr>
                <td style="padding:10px 16px; border-bottom:1px solid #e2e8f0; color:#64748b; font-size:13px; font-weight:600;">Grading</td>
                <td style="padding:10px 16px; border-bottom:1px solid #e2e8f0; color:#0f172a; font-size:13px;">{{ $grading }}</td>
              </tr>
              @endif
              <tr>
                <td style="padding:10px 16px; border-bottom:1px solid #e2e8f0; color:#64748b; font-size:13px; font-weight:600;">Lokasi Penempatan</td>
                <td style="padding:10px 16px; border-bottom:1px solid #e2e8f0; color:#0f172a; font-size:13px;">{{ $workLocation ?? '-' }}</td>
              </tr>
              <tr>
                <td style="padding:10px 16px; border-bottom:1px solid #e2e8f0; color:#64748b; font-size:13px; font-weight:600;">Status Kekaryawanan</td>
                <td style="padding:10px 16px; border-bottom:1px solid #e2e8f0; color:#0f172a; font-size:13px;">{{ $contractType ?? '-' }}</td>
              </tr>
              <tr>
                <td style="padding:10px 16px; border-bottom:1px solid #e2e8f0; color:#64748b; font-size:13px; font-weight:600;">Rencana Tanggal Masuk (Join)</td>
                <td style="padding:10px 16px; border-bottom:1px solid #e2e8f0; color:#0f172a; font-size:13px; font-weight:700; color:#0284c7;">
                  {{ $startDateFormatted ?? '-' }}
                </td>
              </tr>
              <tr>
                <td style="padding:10px 16px; border-bottom:1px solid #e2e8f0; color:#64748b; font-size:13px; font-weight:600;">Total Remunerasi (Gross)</td>
                <td style="padding:10px 16px; border-bottom:1px solid #e2e8f0; color:#16a34a; font-size:14px; font-weight:700;">
                  Rp {{ number_format((float)($salaryTotal ?? 0), 0, ',', '.') }},- gross
                </td>
              </tr>
            </table>

            <!-- Petunjuk Langkah Selanjutnya -->
            <div style="background:#eff6ff; border-left:4px solid #2563eb; padding:14px 16px; border-radius:4px; margin-bottom:22px;">
              <h4 style="margin:0 0 6px 0; color:#1e40af; font-size:14px; font-weight:700;">
                Petunjuk Konfirmasi &amp; Pengembalian Dokumen:
              </h4>
              <ol style="margin:0; padding-left:18px; color:#334155; font-size:13px; line-height:1.6;">
                <li>Dokumen surat penawaran resmi lengkap terlampir pada email ini (<strong>{{ $filename ?? 'Offering-Letter.pdf' }}</strong>).</li>
                <li>Silakan unduh dokumen tersebut, baca seluruh rincian hak, kewajiban, dan klausul yang tercantum.</li>
                <li>Cetak (print) dokumen, bubuhkan <strong>tanda tangan basah di atas materai Rp 10.000</strong> pada kolom persetujuan kandidat.</li>
                <li>Pindai (scan) kembali dokumen yang telah ditandatangani dan kirimkan kembali kepada kami melalui email ini dalam jangka waktu <strong>1 x 24 jam</strong> sejak email ini diterima.</li>
              </ol>
            </div>

            <p style="margin:0 0 18px 0; color:#334155; font-size:13.5px; line-height:1.6; text-align:justify;">
              Apabila terdapat pertanyaan atau hal yang memerlukan klarifikasi lebih lanjut mengenai penawaran ini, jangan ragu untuk membalas email ini atau menghubungi tim Human Capital kami.
            </p>

            <p style="margin:0 0 4px 0; color:#334155; font-size:13.5px;">Atas perhatian dan kerja samanya, kami ucapkan terima kasih.</p>
            <p style="margin:0 0 24px 0; color:#334155; font-size:13.5px;">Salam hangat,</p>

            <!-- Signature Box -->
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td style="border-top:1px dashed #cbd5e1; padding-top:14px;">
                  <strong style="color:#0f172a; font-size:14px;">Human Capital &amp; General Affairs Department</strong><br>
                  <span style="color:#64748b; font-size:12.5px;">PT Green Energi Utama</span><br>
                  <small style="color:#94a3b8; font-size:11px;">Diproses oleh: {{ $senderUser ?? 'HC Administrator' }}</small>
                </td>
              </tr>
            </table>

          </td>
        </tr>

        <!-- Footer Notice -->
        <tr>
          <td style="background:#f8fafc; border-top:1px solid #e2e8f0; padding:16px 32px; text-align:center;">
            <p style="margin:0; font-size:11px; color:#94a3b8; line-height:1.5;">
              Email ini dikirim secara otomatis oleh ERP System PT Green Energi Utama.<br>
              Informasi di dalam email ini bersifat rahasia dan hanya ditujukan untuk penerima yang bersangkutan.
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>

