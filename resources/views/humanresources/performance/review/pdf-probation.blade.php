<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Form Penilaian Kontrak atau Masa Percobaan - {{ $review->review_no }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9px;
            color: #111;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        th,
        td {
            border: 1px solid #000000;
            padding: 4px 6px;
            vertical-align: middle;
            color: #111827;
        }

        .no-border td,
        .no-border th {
            border: none;
            padding: 2px 4px;
        }

        .bg-dark,
        .bg-dark th,
        .bg-dark td {
            background-color: #1b5e20;
            color: #ffffff;
            font-weight: bold;
        }

        .bg-gray,
        .bg-gray th,
        .bg-gray td {
            background-color: #e9ecef;
            color: #111827;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        td.scale-cell {
            padding: 3px 0;
            text-align: center;
            font-size: 8.5px;
            color: #374151;
            border-left: 1px solid #9ca3af;
            border-right: 1px solid #9ca3af;
        }

        td.scale-cell.scale-first {
            border-left: 1px solid #000000;
        }

        td.scale-cell.scale-last {
            border-right: 1px solid #000000;
        }

        td.scale-cell.scale-selected {
            background-color: #2e7d32;
            color: #ffffff;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div style="margin: 0 10mm;">
        <table>
            <tr>
                <td rowspan="2" style="width: 18%; text-align: center; font-weight: bold; font-size: 14px;">
                    @if (!empty($logoDataUri))
                        <img src="{{ $logoDataUri }}" alt="GEU" style="max-height: 38px; max-width: 110px;">
                    @else
                        GEU
                    @endif
                </td>
                <td colspan="3" class="text-center fw-bold" style="font-size: 12px;">Form Penilaian Kontrak atau Masa
                    Percobaan</td>
            </tr>
            <tr>
                <td style="width: 28%;"><strong>Process Owner :</strong> HCGA</td>
                <td style="width: 27%;"><strong>Revision :</strong> #01 | <strong>Doc ID:</strong> FR/HCGA/14-00</td>
                <td style="width: 27%;"><strong>Versi Form:</strong> v{{ $scaleConfig['version_no'] ?? 1 }} (Skala
                    {{ $scaleConfig['min'] }}-{{ $scaleConfig['max'] }})</td>
            </tr>
        </table>
    
        <table class="no-border" style="margin-bottom: 8px;">
            <tr>
                <td style="width: 18%;">Nama Karyawan</td>
                <td style="width: 32%;">: {{ $review->user->Name ?? '-' }}</td>
                <td style="width: 18%;">Penilai</td>
                <td style="width: 32%;">: {{ $review->manager->Name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Posisi / Dept</td>
                <td>: {{ $review->user->Position ?? '-' }} / {{ $review->user->Department ?? '-' }}</td>
                <td>Tanggal Evaluasi</td>
                <td>: {{ $review->updated_at ? $review->updated_at->format('d M Y') : date('d M Y') }}</td>
            </tr>
            <tr>
                <td>No. Dokumen</td>
                <td>: {{ $review->review_no }}</td>
                <td>Akhir Probation</td>
                <td>:
                    {{ $review->probation_end_date ? \Carbon\Carbon::parse($review->probation_end_date)->format('d M Y') : '-' }}
                </td>
            </tr>
        </table>
    
        <!-- I. SOFT SKILL -->
        @php
            $grouped = $competencies->groupBy(function ($c) {
                return $c->category_group ?: 'Umum';
            });
            $totalSoft = 0;
            $maxScale = (int) ($scaleConfig['max'] ?? 5);
            $minScale = (int) ($scaleConfig['min'] ?? 1);
            $colCount = max(1, ($maxScale - $minScale + 1));
            $cellW = round(24 / $colCount, 2);
        @endphp
        <table>
            <thead>
                <tr class="bg-dark">
                    <th colspan="2" style="width: 52%;">I. SOFT SKILL</th>
                    <th colspan="{{ $colCount }}" class="text-center" style="width: 24%;">Penilaian
                        ({{ $scaleConfig['min'] }}-{{ $scaleConfig['max'] }})</th>
                    <th class="text-center" style="width: 24%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($grouped as $groupName => $items)
                    <tr class="bg-gray">
                        <td colspan="{{ 3 + $colCount }}">{{ $groupName }}</td>
                    </tr>
                    @foreach ($items as $idx => $comp)
                        @php
                            $sc = $scoreMap->get($comp->id);
                            $val = $sc ? (int) $sc->score : 0;
                            $totalSoft += $val;
                        @endphp
                        <tr>
                            <td style="width: 4%; text-align: center;">{{ $idx + 1 }}</td>
                            <td style="width: 48%;">{{ $comp->name }}</td>
                            @for ($s = $minScale; $s <= $maxScale; $s++)
                                <td style="width: {{ $cellW }}%;"
                                    class="scale-cell {{ $val === $s ? 'scale-selected' : '' }} {{ $s === $minScale ? 'scale-first' : '' }} {{ $s === $maxScale ? 'scale-last' : '' }}">
                                    {{ $s }}
                                </td>
                            @endfor
                            <td style="width: 24%;">{{ $sc->comment ?? '' }}</td>
                        </tr>
                    @endforeach
                @endforeach
                <tr>
                    <td colspan="2" class="text-right fw-bold">TOTAL</td>
                    <td colspan="{{ $colCount }}" class="text-center fw-bold">{{ $totalSoft }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2" class="text-right fw-bold">Nilai Aspek I</td>
                    <td colspan="{{ $colCount }}" class="text-center fw-bold">{{ number_format($review->competency_score, 2) }}%</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    
        <!-- II. TARGET KINERJA INDIVIDU -->
        <table>
            <thead>
                <tr class="bg-dark">
                    <th colspan="5">II. TARGET KINERJA INDIVIDU</th>
                </tr>
                <tr class="bg-gray">
                    <th>Indikator / Target Kerja</th>
                    <th class="text-center" style="width: 14%;">Target</th>
                    <th class="text-center" style="width: 14%;">Pencapaian</th>
                    <th class="text-center" style="width: 10%;">Bobot</th>
                    <th class="text-center" style="width: 12%;">Capaian (%)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($review->kpiItems as $kpi)
                    <tr>
                        <td>{{ $kpi->title }}</td>
                        <td class="text-center">{{ number_format($kpi->target_value, 2) }} {{ $kpi->kpi_unit }}</td>
                        <td class="text-center">{{ number_format($kpi->actual_value, 2) }} {{ $kpi->kpi_unit }}</td>
                        <td class="text-center">{{ number_format($kpi->weight_percent, 0) }}%</td>
                        <td class="text-center fw-bold">{{ number_format($kpi->progress_percent, 2) }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada item target kinerja individu yang ditautkan.</td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="4" class="text-right fw-bold">Nilai Aspek II</td>
                    <td class="text-center fw-bold">{{ number_format($review->kpi_score, 2) }}%</td>
                </tr>
            </tbody>
        </table>
    
        <!-- TOTAL PENILAIAN & KESIMPULAN -->
        @php
            $wComp = (float) ($review->cycle->weight_competency ?? 30);
            $wKpi = (float) ($review->cycle->weight_kpi ?? 70);
            $conv1 = round($review->competency_score * ($wComp / 100), 2);
            $conv2 = round($review->kpi_score * ($wKpi / 100), 2);
            $recLabel = '-';
            if ($review->recommendation === 'NOT_RECOMMENDED') {
                $recLabel = 'Tidak disarankan';
            } elseif ($review->recommendation === 'EXTEND_CONTRACT') {
                $recLabel = 'Disarankan untuk di kontrak / diperpanjang kontraknya';
            } elseif ($review->recommendation === 'PROMOTE_PERMANENT') {
                $recLabel = 'Disarankan untuk dipromosikan sebagai karyawan tetap';
            }
        @endphp
        <table>
            <tr class="bg-dark">
                <th colspan="5">TOTAL PENILAIAN</th>
            </tr>
            <tr class="bg-gray text-center">
                <td>Bobot Aspek I</td>
                <td>Nilai Aspek I</td>
                <td>Konversi Aspek I</td>
                <td colspan="2" rowspan="5" style="vertical-align: top; text-align: left;">
                    <strong>TTD Penilai:</strong><br><br><br><br>
                    {{ $review->manager->Name ?? '-' }}
                </td>
            </tr>
            <tr class="text-center">
                <td>{{ number_format($wComp, 0) }}%</td>
                <td>{{ number_format($review->competency_score, 2) }}</td>
                <td>{{ number_format($conv1, 2) }}</td>
            </tr>
            <tr class="bg-gray text-center">
                <td>Bobot Aspek II</td>
                <td>Nilai Aspek II</td>
                <td>Konversi Aspek II</td>
            </tr>
            <tr class="text-center">
                <td>{{ number_format($wKpi, 0) }}%</td>
                <td>{{ number_format($review->kpi_score, 2) }}</td>
                <td>{{ number_format($conv2, 2) }}</td>
            </tr>
            <tr>
                <td colspan="2" class="text-right fw-bold">TOTAL</td>
                <td class="text-center fw-bold">{{ number_format($review->final_score ?? $conv1 + $conv2, 2) }}</td>
            </tr>
            <tr class="bg-dark">
                <td colspan="2">KESIMPULAN</td>
                <td colspan="3">{{ $recLabel }}</td>
            </tr>
        </table>
    
        <table>
            <tr class="bg-gray">
                <th colspan="2" style="text-align: left;">KETERANGAN NILAI</th>
            </tr>
            <tr>
                <td style="width: 70%;">Tidak disarankan</td>
                <td class="text-center">Nilai : 0 - 50</td>
            </tr>
            <tr>
                <td>Disarankan untuk di kontrak / diperpanjang kontraknya</td>
                <td class="text-center">Nilai : &gt;50 - 75</td>
            </tr>
            <tr>
                <td>Disarankan untuk dipromosikan sebagai karyawan tetap</td>
                <td class="text-center">Nilai : &gt;75 - 100</td>
            </tr>
        </table>
    </div>
</body>

</html>
