<?php

require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Converter;

Settings::setOutputEscapingEnabled(true);

$sourceFile = __DIR__ . '/Panduan-Fitur-Business-Partner.md';
$outputFile = __DIR__ . '/Panduan-Fitur-Business-Partner.docx';

$lines = file($sourceFile, FILE_IGNORE_NEW_LINES);
if ($lines === false) {
    fwrite(STDERR, "Gagal membaca sumber: $sourceFile\n");
    exit(1);
}

$COLOR_PRIMARY = '1F4E79';
$COLOR_ACCENT  = '2E74B5';

function parseRich(string $text): array
{
    $segments = [];
    $parts = preg_split('/(\*\*[^*]+\*\*|\*[^*+\s][^*]*\*)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as $part) {
        if ($part === '' || $part === null) {
            continue;
        }
        if (preg_match('/^\*\*(.+)\*\*$/us', $part, $m)) {
            $segments[] = ['text' => $m[1], 'bold' => true, 'italic' => false];
        } elseif (preg_match('/^\*(.+)\*$/us', $part, $m)) {
            $segments[] = ['text' => $m[1], 'bold' => false, 'italic' => true];
        } else {
            $segments[] = ['text' => $part, 'bold' => false, 'italic' => false];
        }
    }
    return $segments;
}

function applySegments($run, string $text, array $baseFont, ?array $override = []): void
{
    $segments = parseRich($text);
    if (empty($segments)) {
        return;
    }
    foreach ($segments as $seg) {
        $font = array_merge($baseFont, [
            'bold'   => $seg['bold'],
            'italic' => $seg['italic'] || !empty($baseFont['italic']),
        ], $override);
        $run->addText($seg['text'], $font);
    }
}

$phpWord = new PhpWord();
$phpWord->setDefaultFontName('Calibri');
$phpWord->setDefaultFontSize(11);

$bodyFont = ['size' => 11, 'color' => '262626'];

$titleStyles = [
    1 => ['size' => 17, 'bold' => true, 'color' => $COLOR_PRIMARY],
    2 => ['size' => 14, 'bold' => true, 'color' => $COLOR_ACCENT],
    3 => ['size' => 12, 'bold' => true, 'color' => '404040'],
    4 => ['size' => 11, 'bold' => true, 'italic' => true, 'color' => '404040'],
];
foreach ($titleStyles as $depth => $fontStyle) {
    $phpWord->addTitleStyle($depth, $fontStyle, [
        'spacing' => ['before' => $depth === 1 ? 320 : 220, 'after' => 120],
        'keepNext' => true,
    ]);
}

$section = $phpWord->addSection([
    'pageSizeW' => 11906,
    'pageSizeH' => 16838,
    'marginTop' => 1134,
    'marginBottom' => 1134,
    'marginLeft' => 1134,
    'marginRight' => 1134,
]);

$footer = $section->addFooter();
$footer->addPreserveText('Halaman {PAGE} dari {NUMPAGES}', ['size' => 9, 'color' => '808080'], ['alignment' => 'right']);

$listPara = function (array $extra = []) {
    return array_merge(['spacing' => ['after' => 80], 'indentation' => ['left' => 709, 'hanging' => 340]], $extra);
};

$state = [
    'titleEmitted' => false,
    'inCoverMeta' => false,
    'tocEmitted' => false,
    'skippingTocSection' => false,
    'listType' => null,
    'listCounter' => 0,
];

$tableBuffer = [];

function flushTable($section, array &$buffer, array $bodyFont): void
{
    if (empty($buffer)) {
        return;
    }
    $rowsData = [];
    foreach ($buffer as $rowLine) {
        $cells = explode('|', trim($rowLine));
        array_shift($cells);
        array_pop($cells);
        $rowsData[] = array_map('trim', $cells);
    }
    $dataRows = [];
    foreach ($rowsData as $idx => $cells) {
        if ($idx === 1 && preg_match('/^[\s\-:|]+$/', implode('', $cells))) {
            continue;
        }
        $dataRows[] = $cells;
    }
    if (empty($dataRows)) {
        $buffer = [];
        return;
    }

    $usableWidth = 9638;
    $colCount = count($dataRows[0]);
    $colWidth = (int) floor($usableWidth / max(1, $colCount));

    $table = $section->addTable([
        'borderSize' => 4,
        'borderColor' => 'BFBFBF',
        'cellMarginTop' => 50,
        'cellMarginBottom' => 50,
        'cellMarginLeft' => 90,
        'cellMarginRight' => 90,
    ]);
    foreach ($dataRows as $rIdx => $cells) {
        $isHeader = $rIdx === 0;
        $table->addRow(null, $isHeader ? ['tblHeader' => true] : []);
        for ($c = 0; $c < $colCount; $c++) {
            $cellText = $cells[$c] ?? '';
            $cell = $table->addCell($colWidth, $isHeader ? ['shading' => ['fill' => 'D9E2F3']] : []);
            applySegments(
                $cell->addTextRun(['spacing' => ['after' => 0]]),
                $cellText,
                $isHeader ? array_merge($bodyFont, ['bold' => true]) : $bodyFont
            );
        }
    }
    $section->addTextBreak(1, null, ['spacing' => ['after' => 40]]);
    $buffer = [];
}

foreach ($lines as $rawLine) {
    $line = rtrim($rawLine);

    if (count($tableBuffer) > 0 || preg_match('/^\s*\|.*\|\s*$/', $line)) {
        if (preg_match('/^\s*\|.*\|\s*$/', $line)) {
            $tableBuffer[] = $line;
            continue;
        }
        flushTable($section, $tableBuffer, $bodyFont);
    }

    if (trim($line) === '' ) {
        $state['listType'] = null;
        continue;
    }

    if (preg_match('/^---+\s*$/', $line)) {
        $state['listType'] = null;
        continue;
    }

    if (preg_match('/^(#{1,6})\s+(.*)$/u', $line, $m)) {
        $level = strlen($m[1]);
        $text = trim($m[2]);
        $state['listType'] = null;

        if (!$state['titleEmitted']) {
            $section->addText($text, ['size' => 30, 'bold' => true, 'color' => $COLOR_PRIMARY], [
                'alignment' => 'center',
                'spaceBefore' => 3600,
                'spacing' => ['after' => 240],
            ]);
            $section->addText('Panduan Penggunaan Fitur untuk Pengguna Internal dan Mitra', ['size' => 13, 'color' => $COLOR_ACCENT], [
                'alignment' => 'center',
                'spacing' => ['after' => 1200],
            ]);
            $state['titleEmitted'] = true;
            $state['inCoverMeta'] = true;
            continue;
        }

        if (!$state['tocEmitted']) {
            $section->addPageBreak();
            $section->addText('DAFTAR ISI', ['size' => 16, 'bold' => true, 'color' => $COLOR_PRIMARY], [
                'alignment' => 'center',
                'spacing' => ['after' => 240],
            ]);
            $section->addTOC(
                ['size' => 11, 'color' => '262626'],
                ['tabLeader' => 'dot', 'indentation' => ['left' => 200]]
            );
            $section->addTextBreak(1);
            $section->addText(
                'Catatan: Setelah dokumen dibuka pada Microsoft Word, klik kanan pada daftar isi lalu pilih "Update Field" agar nomor halaman tampil dengan benar.',
                ['italic' => true, 'size' => 9, 'color' => '808080']
            );
            $section->addPageBreak();
            $state['tocEmitted'] = true;
        }

        if ($level === 2 && strcasecmp($text, 'Daftar Isi') === 0) {
            $state['skippingTocSection'] = true;
            continue;
        }
        $state['skippingTocSection'] = false;

        $mapLevel = max(1, min(4, $level - 1));
        $section->addTitle($text, $mapLevel);
        continue;
    }

    if ($state['inCoverMeta']) {
        $metaText = trim(preg_replace('/^\*\*(.+?):\*\*/u', '$1:', $line));
        $metaText = str_replace(['**'], '', $metaText);
        $section->addText($metaText, ['size' => 11, 'color' => '404040'], [
            'alignment' => 'center',
            'spacing' => ['after' => 60],
        ]);
        continue;
    }

    if ($state['skippingTocSection']) {
        continue;
    }

    if (preg_match('/^>\s?(.*)$/u', $line, $m)) {
        $state['listType'] = null;
        $run = $section->addTextRun([
            'indentation' => ['left' => 454, 'right' => 227],
            'spacing' => ['before' => 100, 'after' => 140],
            'shading' => ['fill' => 'EAF1FB'],
        ]);
        applySegments($run, trim($m[1]), array_merge($bodyFont, ['italic' => true, 'color' => $COLOR_PRIMARY]));
        continue;
    }

    if (preg_match('/^\[Sisipkan tangkapan layar[^\]]*\]$/iu', trim($line))) {
        $state['listType'] = null;
        $section->addText(trim($line), ['italic' => true, 'size' => 10, 'color' => '8C8C8C'], [
            'indentation' => ['left' => 227],
            'spacing' => ['before' => 80, 'after' => 140],
            'shading' => ['fill' => 'F5F5F5'],
        ]);
        continue;
    }

    if (preg_match('/^-\s+(.*)$/u', $line, $m)) {
        if ($state['listType'] !== 'bullet') {
            $state['listType'] = 'bullet';
            $state['listCounter'] = 0;
        }
        $run = $section->addTextRun($listPara());
        $run->addText("•\t", array_merge($bodyFont, ['color' => $COLOR_ACCENT]));
        applySegments($run, trim($m[1]), $bodyFont);
        continue;
    }

    if (preg_match('/^(\d+)[.\)]\s+(.*)$/u', $line, $m)) {
        if ($state['listType'] !== 'number') {
            $state['listType'] = 'number';
            $state['listCounter'] = 0;
        }
        $state['listCounter']++;
        $run = $section->addTextRun($listPara());
        $run->addText($state['listCounter'] . ".\t", array_merge($bodyFont, ['bold' => true]));
        applySegments($run, trim($m[2]), $bodyFont);
        continue;
    }

    $state['listType'] = null;
    $run = $section->addTextRun(['spacing' => ['after' => 120]]);
    applySegments($run, trim($line), $bodyFont);
}

flushTable($section, $tableBuffer, $bodyFont);

$writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$writer->save($outputFile);

if (!file_exists($outputFile)) {
    fwrite(STDERR, "Dokumen gagal dibuat.\n");
    exit(1);
}
printf("OK: %s (%.1f KB)\n", basename($outputFile), filesize($outputFile) / 1024);
