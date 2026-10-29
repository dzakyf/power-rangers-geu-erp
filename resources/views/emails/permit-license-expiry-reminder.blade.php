<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f6fb;font-family:Arial,Helvetica,sans-serif;color:#243447;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f3f6fb;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:640px;background-color:#ffffff;border:1px solid #dfe6f0;border-radius:10px;overflow:hidden;">
                    <tr>
                        <td style="background-color:#0b5ed7;padding:24px 28px;color:#ffffff;">
                            <div style="font-size:11px;letter-spacing:1.5px;text-transform:uppercase;opacity:0.85;">ERP GEU &middot; Permit &amp; License</div>
                            <h1 style="margin:8px 0 0;font-size:22px;line-height:1.3;font-weight:700;">Pengingat Masa Berlaku Perizinan</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 14px;font-size:14px;line-height:1.6;">Yth. Bapak/Ibu,</p>
                            <p style="margin:0 0 20px;font-size:14px;line-height:1.6;">{!! nl2br(e($intro)) !!}</p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;font-size:13px;line-height:1.5;margin:0 0 20px;">
                                <tr>
                                    <td style="width:34%;padding:9px 10px;background-color:#f8fafc;border:1px solid #e3e8ef;color:#5b6b82;">Kode / Kategori</td>
                                    <td style="padding:9px 10px;border:1px solid #e3e8ef;">{{ $permit->Category_Code ?? '-' }}{{ !empty($permit->Category_Name) ? ' - ' . $permit->Category_Name : '' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:9px 10px;background-color:#f8fafc;border:1px solid #e3e8ef;color:#5b6b82;">Nama Perizinan</td>
                                    <td style="padding:9px 10px;border:1px solid #e3e8ef;font-weight:700;">{{ $permit->Name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:9px 10px;background-color:#f8fafc;border:1px solid #e3e8ef;color:#5b6b82;">Instansi</td>
                                    <td style="padding:9px 10px;border:1px solid #e3e8ef;">{{ $permit->Issuer ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:9px 10px;background-color:#f8fafc;border:1px solid #e3e8ef;color:#5b6b82;">Nomor Perizinan</td>
                                    <td style="padding:9px 10px;border:1px solid #e3e8ef;">{{ $permit->Permit_Number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:9px 10px;background-color:#f8fafc;border:1px solid #e3e8ef;color:#5b6b82;">Tanggal Terbit</td>
                                    <td style="padding:9px 10px;border:1px solid #e3e8ef;">{{ $issueDateLabel }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:9px 10px;background-color:#f8fafc;border:1px solid #e3e8ef;color:#5b6b82;">Masa Berlaku</td>
                                    <td style="padding:9px 10px;border:1px solid #e3e8ef;">{{ $validityLabel }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:9px 10px;background-color:#f8fafc;border:1px solid #e3e8ef;color:#5b6b82;">Kontak PIC</td>
                                    <td style="padding:9px 10px;border:1px solid #e3e8ef;">{{ $picLabel }}</td>
                                </tr>
                            </table>

                            <div style="border-left:4px solid #f0ad4e;background-color:#fff8e6;padding:12px 14px;margin:0 0 22px;font-size:13px;line-height:1.6;color:#7a5b00;">
                                <strong>Status:</strong> {{ $statusLabel }}<br>
                                <strong>Sisa hari:</strong> @if($days === null) Sesuai ketentuan @elseif($days === 0) Hari ini @elseif($days < 0) Terlambat {{ abs($days) }} hari @else {{ $days }} hari @endif
                            </div>

                            <p style="margin:0 0 22px;font-size:14px;line-height:1.6;">Mohon segera lakukan perpanjangan atau pembaruan dokumen sebelum masa berlaku berakhir.</p>

                            <p style="margin:0;text-align:center;">
                                <a href="{{ $actionUrl }}" style="display:inline-block;background-color:#0b5ed7;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:6px;font-size:14px;font-weight:700;">Buka Menu Permit &amp; License</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px;background-color:#f8fafc;border-top:1px solid #e3e8ef;color:#6b7280;font-size:12px;line-height:1.6;">
                            {!! nl2br(e($footer)) !!}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>