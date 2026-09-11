<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpWord\TemplateProcessor;
use ZipArchive;

class PatchSppdTemplatesCommand extends Command
{
    protected $signature = 'sppd:patch-templates';

    protected $description = 'Normalize docx SPPD templates: merge split-run macros and add missing placeholders';

    public function handle()
    {
        $reqPath  = public_path('template-pengajuan-sppd.docx');
        $apprPath = public_path('template-approved-sppd.docx');
        $stlPath  = public_path('template-hr-settlement-sppd.docx');

        if (!file_exists($reqPath) || !file_exists($apprPath)) {
            $this->error('SPPD template files not found in public/');
            return 1;
        }

        $this->patchRequestTemplate($reqPath);
        $this->patchApprovedTemplate($apprPath);
        if (file_exists($stlPath)) {
            $this->patchSettlementTemplate($stlPath);
        }

        $pbPaths = [
            public_path('template-perbantuan-sementara-junior-middle.docx'),
            public_path('template-perbantuan-sementara-middle-senior.docx'),
            public_path('template-perbantuan-sementara-dept-head.docx'),
        ];
        foreach ($pbPaths as $pbPath) {
            if (file_exists($pbPath)) {
                $this->patchPerbantuanTemplate($pbPath);
            }
        }

        foreach (array_merge([$reqPath, $apprPath, $stlPath], $pbPaths) as $path) {
            if (!file_exists($path)) {
                continue;
            }
            $doc = new TemplateProcessor($path);
            $this->info(basename($path) . ' variables: ' . implode(', ', $doc->getVariables()));
        }

        $this->info('SPPD templates patched.');
    }

    protected function patchRequestTemplate($path)
    {
        $xml = $this->readDocumentXml($path);

        $xml = $this->mergeSplitMacros($xml);

        if (strpos($xml, '${department}') === false) {
            $xml = str_replace('<w:t>departemen</w:t>', '<w:t>Departemen : ${department}</w:t>', $xml);
        }

        if (strpos($xml, '${docNo}') !== false && strpos($xml, 'No Pengajuan : ') === false) {
            $xml = str_replace('<w:t>Pengajuan</w:t>', '<w:t>Pengajuan : ${docNo}</w:t>', $xml);
        }

        if (strpos($xml, '${description}') === false) {
            $xml = preg_replace(
                '/\$\{estimatedCost\}<\/w:t><\/w:r>/',
                '${estimatedCost}</w:t></w:r><w:r><w:rPr><w:rFonts w:ascii="Calibri" w:eastAsia="Calibri" w:hAnsi="Calibri" w:cs="Calibri"/></w:rPr><w:br/></w:r><w:r><w:rPr><w:rFonts w:ascii="Calibri" w:eastAsia="Calibri" w:hAnsi="Calibri" w:cs="Calibri"/></w:rPr><w:t xml:space="preserve">${description}</w:t></w:r>',
                $xml,
                1
            );
        }

        $this->writeDocumentXml($path, $xml);
    }

    protected function patchPerbantuanTemplate($path)
    {
        $xml = $this->readDocumentXml($path);

        $xml = $this->mergeSplitMacros($xml);

        // The generic merge corrupts the split `${ amount _ written }` macro into a
        // single `${_}` marker; repair it to the intended `${amount_written}` macro.
        $fixed = str_replace('${_}', '${amount_written}', $xml);
        if ($fixed !== $xml) {
            $xml = $fixed;
            $this->info('Repaired ${ amount _ written } to ${amount_written}');
        }

        $this->writeDocumentXml($path, $xml);
    }

    protected function patchSettlementTemplate($path)
    {
        $xml = $this->readDocumentXml($path);

        $xml = $this->mergeSplitMacros($xml);

        if (strpos($xml, '${totalCost}') === false) {
            $first  = strpos($xml, '${expenseCost}');
            if ($first !== false) {
                $second = strpos($xml, '${expenseCost}', $first + 1);
                if ($second !== false) {
                    $xml = substr($xml, 0, $second) . '${totalCost}' . substr($xml, $second + strlen('${expenseCost}'));
                    $this->info('Renamed Total-cell macro to ${totalCost}');
                }
            }
        }

        $this->writeDocumentXml($path, $xml);
    }

    protected function patchApprovedTemplate($path)
    {
        $xml = $this->readDocumentXml($path);

        if (strpos($xml, '${department}') === false) {
            $xml = str_replace(
                '<w:t xml:space="preserve">: HCGA</w:t>',
                '<w:t xml:space="preserve">: ${department}</w:t>',
                $xml
            );
        }

        $this->writeDocumentXml($path, $xml);
    }

    protected function mergeSplitMacros($xml)
    {
        $pattern = '/(?<open><w:r\b[^>]*>(?:<w:rPr>.*?<\/w:rPr>)?<w:t\b[^>]*>)(?<pre>[^<]*?)\$\{(?<tail><\/w:t><\/w:r>)(?:<w:proofErr[^>]*\/>)*<w:r\b[^>]*>(?:<w:rPr>.*?<\/w:rPr>)?<w:t\b[^>]*>(?<key>\w+)<\/w:t><\/w:r>(?:<w:proofErr[^>]*\/>)*<w:r\b[^>]*>(?:<w:rPr>.*?<\/w:rPr>)?<w:t\b[^>]*>\}(?<post>[^<]*)(?<end><\/w:t><\/w:r>)/';

        $merged = preg_replace_callback($pattern, function ($m) {
            return $m['open'] . $m['pre'] . '${' . $m['key'] . '}' . $m['post'] . $m['end'];
        }, $xml);

        return $merged;
    }

    protected function readDocumentXml($path)
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException("Cannot open $path");
        }
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        return $xml;
    }

    protected function writeDocumentXml($path, $xml)
    {
        $dir = storage_path('app/tmp_sppd_patch_' . uniqid());
        mkdir($dir, 0777, true);

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException("Cannot open $path");
        }
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            $dest = $dir . '/' . $name;
            if (substr($name, -1) === '/') {
                mkdir($dest, 0777, true);
                continue;
            }
            $info = pathinfo($dest);
            if (!is_dir($info['dirname'])) {
                mkdir($info['dirname'], 0777, true);
            }
            file_put_contents($dest, $zip->getFromIndex($i));
        }
        $zip->close();

        file_put_contents($dir . '/word/document.xml', $xml);

        $out = $path . '.patched';
        $new = new ZipArchive();
        if ($new->open($out, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Cannot create $out");
        }
        $this->addDirToZip($new, $dir, strlen($dir) + 1);
        $new->close();

        if (!rename($out, $path)) {
            throw new \RuntimeException("Cannot replace $path");
        }

        $this->rrmdir($dir);
    }

    protected function addDirToZip(ZipArchive $zip, $dir, $strip)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $local = substr($file->getPathname(), $strip);
            $zip->addFile($file->getPathname(), $local);
        }
    }

    protected function rrmdir($dir)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            $file->isDir() && !$file->isLink() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($dir);
    }
}
