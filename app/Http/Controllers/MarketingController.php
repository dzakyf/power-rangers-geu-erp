<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GeneratorService;
use App\Services\UploadService;
use App\Services\UserService;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\TemplateProcessor;
use SimpleXMLElement;
use Imagick;



class MarketingController extends Controller
{
    protected $uploadService;
    protected $userService;
    protected $generatorService;
    protected $year;
    protected $month;

    public function __construct(UploadService $up, UserService $u, GeneratorService $g)
    {
        $this->uploadService = $up;
        $this->userService = $u;
        $this->generatorService = $g;
        $this->year = Carbon::now()->year;
        $this->month = Carbon::now()->format('m');
    }

    public function indexMarketing()
    {
        return view('partner.branding.marketing-index');
    }

    public function indexMarketingRecap()
    {
        return view('partner.branding.marketing-recap');
    }

    public function indexPromotion()
    {
        return view('partner.branding.promotion-index');
    }

    public function indexMonitoring()
    {
        return view('partner.branding.monitoring-index');
    }

    public function indexEvaluation()
    {
        return view('partner.branding.evaluation-index');
    }

    public function getEvaluationData(Request $r)
    {
        $q = $r->query();

        $d = DB::table('branding_monitoring as bm')
            ->select([
                'bm.*',
                'bm.ID as Monitoring_Id',
                'bp.*',
                'bp.ID as Promotion_Id',
                'bp.Code as Promotion_Code',
                'us.Name as PIC_Name',
                DB::raw('(
            SELECT COUNT(*)
            FROM opportunity o
            WHERE o.Created_Date BETWEEN bm.Campaign_Start_At AND bm.Campaign_End_At
        ) as Total_Leads'),
                DB::raw('(
            SELECT COUNT(*)
            FROM opportunity o
            WHERE o.Status = \'Deal\'
              AND o.Created_Date BETWEEN bm.Campaign_Start_At AND bm.Campaign_End_At
        ) as Total_Deals'),
            ])
            ->leftJoin('branding_promotion as bp', 'bp.ID', '=', 'bm.Promotion_Id')
            ->leftJoin('users as us', 'us.id', '=', 'bm.PIC_Id')
            ->where('bp.ID', '=', $q['promotion_id'])
            ->get();

        $config = DB::table('config_system as cs')->select('*')->where('Constraint', '=', 'TOTAL_HUB_INVESTATION')->first();


        $xml = new SimpleXMLElement('<rows></rows>');

        $count = 1;
        $totalLeads = $d[0]->Total_Leads;
        $costPerLead = $totalLeads != 0 ? $d[0]->Budget_Realization / $d[0]->Total_Leads : 0;
        $conversionRate = $totalLeads != 0 ? (($d[0]->Total_Deals / $totalLeads) * 100) : 0 . "%";
        $costPerAcquisition = $totalLeads != 0 ? $d[0]->Budget_Realization / $d[0]->Total_Deals : 0;
        $priceDealLead = $config->Value * $totalLeads;
        $revenue = $priceDealLead * $d[0]->Total_Deals;
        $ROI = (($revenue - $d[0]->Budget_Realization) / $d[0]->Budget_Realization * 100) . "%";
        foreach ($d as $d) {

            $row = $xml->addChild('row');
            $row->addAttribute('ID', $d->ID);
            $row->addChild('cell', $count);
            $row->addChild('cell', $d->Platform);
            $row->addChild('cell', $d->Budget_Realization);
            $row->addChild('cell', $d->Total_Leads);
            $row->addChild('cell', $costPerLead);
            $row->addChild('cell', $d->Total_Deals);
            $row->addChild('cell', $conversionRate);
            $row->addChild('cell', $costPerAcquisition);
            $row->addChild('cell', $priceDealLead);
            $row->addChild('cell', $revenue);
            $row->addChild('cell', $ROI);

            $count++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function submitPromotionEvaluation(Request $r)
    {
        $b = $r->all();

        $id = DB::table('branding_followup')->insertGetId([
            "Promotion_Id" => $b['Promotion_Id'],
            "Platform" => $b['Platform'],
            "Total_Budget" => $b['Budget_Realization'],
            "Total_Leads" => $b['Total_Leads'],
            "Cost_Per_Lead" => $b['Cost_Per_Lead'],
            "Total_Deal" => $b['Total_Deal'],
            "Conversion_Rate" => $b['Conversion_Rate'],
            "Cost_Per_Acquisition" => $b['Cost_Per_Acquisition'],
            "Price_Deal_Lead" => $b['Price_Deal_Lead'],
            "Revenue" => $b['Revenue'],
            "ROI" => $b['ROI'],
            "Recommendation" => $b['Recommendation'],
        ]);

        return response()->json(['message' => 'success']);
    }

    public function createMarketingData(Request $r)
    {
        return view('partner.branding.marketing-create');
    }

    public function createPromotionData(Request $r)
    {
        return view('partner.branding.promotion-create');
    }

    public function createMonitoringData(Request $r)
    {
        return view('partner.branding.monitoring-create');
    }

    public function selectMarketingData(Request $r)
    {
        $q = $r->query();

        if (!empty($q['marketing_id'])) {
            return $this->getMarketingDataById($r);
        }

        return $this->getMarketingData($r);
    }

    public function getMarketingData(Request $r)
    {

        $q = $r->query();

        $d = DB::table('branding_marketing as m')
            ->select('m.*', 'm.ID as Marketing_Id', 'mf.ID as File_Id', 'm.Description as Marketing_Desc', 'm.Created_At as Marketing_Created_At', 'mf.*')
            ->leftJoin('meta_files as mf', function ($join) {
                $join->on('mf.Map_To', '=', 'm.ID')
                    ->where('mf.Topic', '=', 'branding-marketing');
            })
            ->get();


        $xml = new SimpleXMLElement('<rows></rows>');

        // dd($d);

        $count = 1;
        foreach ($d as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $d->Marketing_Id);
            $row->addChild('cell', $count);
            $row->addChild('cell', $d->Marketing_Id);
            $row->addChild('cell', $d->Code);
            $row->addChild('cell', $d->Name);
            $row->addChild('cell', $d->Type);
            $row->addChild('cell', $d->Platform);
            $row->addChild('cell', $d->Channel);
            $row->addChild('cell', $d->Marketing_Desc);
            $row->addChild('cell', $d->Status);
            $row->addChild('cell', $d->Marketing_Created_At);
            $row->addChild('cell', "Lihat Url^ " . $d->Media_Url);
            $count++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getMarketingDataById(Request $r)
    {
        $q = $r->query();

        // dd($q);

        $d = DB::table('branding_marketing as m')
            ->select('m.*', 'm.ID as Marketing_Id', 'mf.ID as File_Id', 'm.Description as Marketing_Desc', 'm.Created_At as Marketing_Created_At', 'mf.*')
            ->leftJoin('meta_files as mf', function ($join) {
                $join->on('mf.Map_To', '=', 'm.ID')
                    ->where('mf.Topic', '=', 'branding-marketing');
            })
            ->where('m.ID', $q['marketing_id'])
            ->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        $count = 1;
        foreach ($d as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $d->ID);
            $row->addChild('cell', $count);
            $row->addChild('cell', $d->Code);
            $row->addChild('cell', $d->Name);
            $row->addChild('cell', $d->Type);
            $row->addChild('cell', $d->Platform);
            $row->addChild('cell', $d->Channel);
            $row->addChild('cell', $d->Marketing_Desc);
            $row->addChild('cell', $d->Status);
            $row->addChild('cell', $d->Marketing_Created_At);
            $row->addChild('cell', $d->Media_Url);
            $count++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getPromotionData()
    {
        $d = DB::table('branding_promotion as p')->select('p.ID as Promotion_Id', 'p.*', 'p.Description as Promotion_Desc', 'p.Created_At as Applied_At', 'bm.Code as Marketing_Code', 'mf.ID as File_Id', 'mf.*')
            ->join('branding_marketing as bm', 'bm.ID', '=', 'p.Marketing_Id')
            ->leftJoin('meta_files as mf', function ($join) {
                $join->on('mf.Map_To', '=', 'p.ID')
                    ->where('mf.topic', '=', 'branding-promotion');
            })
            ->get();

        $xml = new SimpleXMLElement('<rows></rows>');


        $count = 1;
        foreach ($d as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $d->Promotion_Id);
            $row->addChild('cell', $count);
            $row->addChild('cell', $d->Code);
            $row->addChild('cell', $d->Applied_At);
            $row->addChild('cell', htmlspecialchars($d->Department));
            $row->addChild('cell', $d->Type);
            $row->addChild('cell', $d->Platform);
            $row->addChild('cell', $d->Marketing_Code);
            $row->addChild('cell', htmlspecialchars($d->Promotion_Desc));
            $row->addChild('cell', $d->Audience);
            $row->addChild('cell', $d->Estimation);
            $row->addChild('cell', $d->Status);
            $count++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getMonitoringPromotionData()
    {
        $query = DB::table('branding_monitoring as bm')
            ->select([
                'bm.*',
                'bm.ID as Monitoring_Id',
                'bp.*',
                'bp.ID as Promotion_Id',
                'bp.Code as Promotion_Code',
                'us.Name as PIC_Name',
                DB::raw('(
            SELECT COUNT(*)
            FROM opportunity o
            WHERE o.Created_Date BETWEEN bm.Campaign_Start_At AND bm.Campaign_End_At
        ) as Total_Leads'),
                DB::raw('(
            SELECT COUNT(*)
            FROM opportunity o
            WHERE o.Status = \'Deal\'
              AND o.Created_Date BETWEEN bm.Campaign_Start_At AND bm.Campaign_End_At
        ) as Total_Deals'),
            ])
            ->leftJoin('branding_promotion as bp', 'bp.ID', '=', 'bm.Promotion_Id')
            ->leftJoin('users as us', 'us.id', '=', 'bm.PIC_Id');

        if (!empty($q['promotion_id'])) {
            $query->where('bp.ID', '=', $q['promotion_id']);
        }

        $d = $query->get();


        $config = DB::table('config_system as cs')->select('*')->where('Constraint', '=', 'TOTAL_HUB_INVESTATION_BOOTH')->first();

        $xml = new SimpleXMLElement('<rows></rows>');

        $count = 1;
        $totalLeads = $d[0]->Total_Leads;
        $costPerLead = $totalLeads != 0 ? $d[0]->Budget_Realization / $d[0]->Total_Leads : 0;
        $conversionRate = $totalLeads != 0 ? (($d[0]->Total_Deals / $totalLeads) * 100) : 0 . "%";
        $costPerAcquisition = $totalLeads != 0 ? $d[0]->Budget_Realization / $d[0]->Total_Deals : 0;
        $priceDealLead = $config->Value * $totalLeads;
        $revenue = $priceDealLead * $d[0]->Total_Deals;
        $ROI = (($revenue - $d[0]->Budget_Realization) / $d[0]->Budget_Realization * 100) . "%";
        foreach ($d as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('Monitoring_Id', $d->Monitoring_Id);
            $row->addAttribute('Promotion_Id', $d->Promotion_Id);
            $row->addChild('cell', $count);
            $row->addChild('cell', $d->Promotion_Code);
            $row->addChild('cell', $d->Campaign_Start_At);
            $row->addChild('cell', $d->Campaign_End_At);
            $row->addChild('cell', $d->Platform);
            $row->addChild('cell', $d->Budget_Realization);
            $row->addChild('cell', $d->Campaign_Status);
            $row->addChild('cell', $d->PIC_Name);
            $row->addChild('cell', $d->Total_Leads);
            $row->addChild('cell', $costPerLead);
            $row->addChild('cell', $d->Total_Deals);
            $row->addChild('cell', $conversionRate);
            $row->addChild('cell', $costPerAcquisition);
            $row->addChild('cell', $priceDealLead);
            $row->addChild('cell', $revenue);
            $row->addChild('cell', $ROI);
            $count++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }


    public function updateMarketingData(Request $r)
    {
        $b = $r->all();

        try {

            DB::beginTransaction();

            $updated = DB::table('branding_marketing as bp')->where('bp.ID', '=', $b['Marketing_Id'])->update([
                "Code" => $b['Code'],
                "Name" => $b['Name'],
                "Type" => $b['Type'],
                "Platform" => $b['Platform'],
                "Channel" => $b['Channel'],
                "Description" => $b['Description'],
                "Status" => $b['Status'],
                "Media_Url" => $b['Media_Url']
            ]);

            DB::commit();
            return response()->json(['message' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()]);
        }
    }



    public function submitMarketing(Request $r)
    {
        $b = $r->all();

        $highest = DB::table('branding_marketing')->max('ID');
        $applicationCode = $this->generatorService->generateApplicationCode($highest, "MKT"); // Marketing

        $marketingId = DB::table('branding_marketing')->insertGetId([
            "Code" => $applicationCode,
            "Name" => $b['name'],
            "Type" => $b['type'],
            "Platform" => $b['platform'],
            "Channel" => $b['channel'],
            "Description" => $b['description'],
            "Status" => $b['status'],
            "Media_Url" => $b['media_url']
        ]);


        $file = $r->file('design');

        if ($file && $file->isValid()) {
            $path = $file->store("$this->year/$this->month", 'public');
            $ext = $file->getClientOriginalExtension();

            $filename = basename($path);
            $desc = "Design marketing";
            $topic = "branding-marketing";
            $mapTo = $marketingId;

            $this->uploadService->uploadMetaFile($filename, $path, $desc, $ext, $topic, $mapTo);

            $marketingId = DB::table('branding_marketing as bm')->where('bm.ID', '=', $marketingId)->update([
                "Media_Url" => "{$r->root()}/$path",
            ]);
        }


        return response()->json(['message' => 'success']);
    }

    public function makePromotionVoucherDocs($promotion, $filesAttachment)
    {

        $path = public_path('template-voucher-marketing.docx');

        if (!file_exists($path)) {
            throw new \Exception('Template file not found: ' . $path);
        }

        // ── Fill DOCX template ────────────────────────────────────────────
        $session   = $this->userService->getCurrentSessionUserInfo();
        // $promotion = DB::table('branding_promotion')->where('Marketing_Id', $id)->first();

        $doc = new TemplateProcessor($path);
        $doc->setValues([
            'vendor_code'        => $session->Emp_ID,
            'site'               => $session->City ?? '',
            'marketing_platform' => $promotion->Platform,
            'budget_realization' => $this->generatorService->rupiahFormatter($promotion->Estimation),
            'nominal_spelled'    => $this->generatorService->spellRupiah($promotion->Estimation),
            'vp_finance_name'    => 'Fariza N Kartosudarmo',
            'uploaded_at'        => Carbon::now()->format('d F Y'),
            'checker_name'       => '',
            'sales_name'         => $session->Name,
        ]);

        // ── Save filled DOCX to temp, convert to PDF bytes, cleanup ──────
        $tempDocx = tempnam(sys_get_temp_dir(), 'voucher_') . '.docx';
        $doc->saveAs($tempDocx);

        $voucherPdfBytes = $this->convertDocxToPdfViaDompdf($tempDocx);
        @unlink($tempDocx);

        // ── Collect PDF attachment paths ──────────────────────────────────
        $pdfAttachments = collect($filesAttachment)
            ->filter(fn($file) => strtolower($file->Extension) === 'pdf')
            ->map(fn($file) => public_path($file->Path))
            ->filter(fn($p) => file_exists($p))
            ->values()
            ->toArray();

        // ── Merge voucher + attachments, return as download response ──────
        $filename = "DOC-{$promotion->Code}.pdf";

        if (empty($pdfAttachments)) {
            return response($voucherPdfBytes, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $mergedPath = $this->mergePdfsFromBytes($voucherPdfBytes, $pdfAttachments);
        $pdfBytes   = file_get_contents($mergedPath);
        @unlink($mergedPath);

        return response($pdfBytes, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Convert DOCX → PDF using PhpWord HTML export + Dompdf
    // ─────────────────────────────────────────────────────────────────────────────
    private function convertDocxToPdfViaDompdf(string $docxPath): string
    {
        // ── Load DOCX into PhpWord ────────────────────────────────────────
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($docxPath);

        // ── Export to HTML string ─────────────────────────────────────────
        $tempHtml = tempnam(sys_get_temp_dir(), 'voucher_html_') . '.html';
        $writer   = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
        $writer->save($tempHtml);

        $body = file_get_contents($tempHtml);
        @unlink($tempHtml);

        // ── Wrap in full HTML doc with A4 page layout ─────────────────────
        $html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8"/>
            <style>
                @page {
                    size: legal portrait;
                    margin: 1.5cm 1.5cm 1.5cm 1.5cm;
                }
                * {
                    /* margin: 0; */
                    /* padding: 0; */
                    box-sizing: border-box;
                }
                body {
                    font-family: 'DejaVu Sans', sans-serif;
                    font-size: 8pt;
                    line-height: 1.2;
                    color: #000;
                }
                p, div, span {
                    margin: 0 !important;
                    padding: 0 !important;
                    line-height: 1.2 !important;
                }
                br { display: block; margin: 0; line-height: 0.5; }
                table { width: 100%; border-collapse: collapse; margin: 0; }
                td, th { padding: 1px 3px !important; vertical-align: top; line-height: 1.2; }
                u { display: inline-block; width: 100%; border-bottom: 1px solid #000; text-decoration: none; }
                td u, th u { width: 100%; display: block; border-bottom: 1px solid #000; text-decoration: none; }
            </style>
        </head>
        <body>
            {$body}
        </body>
        </html>
        HTML;


        // ── Render with Dompdf ────────────────────────────────────────────
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('legal', 'portrait');
        $dompdf->render();

        $bytes = $dompdf->output();

        if (empty($bytes)) {
            throw new \Exception('Dompdf failed to generate PDF');
        }

        return $bytes;
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Merge multiple PDFs into one using FPDI
    // ─────────────────────────────────────────────────────────────────────────────

    private function mergePdfsFromBytes(string $voucherBytes, array $attachmentPaths): string
    {
        $tempVoucher = tempnam(sys_get_temp_dir(), 'voucher_merge_') . '.pdf';
        file_put_contents($tempVoucher, $voucherBytes);

        $result = $this->mergePdfs(array_merge([$tempVoucher], $attachmentPaths));
        @unlink($tempVoucher);

        return $result;
    }

    private function mergePdfs(array $pdfPaths): string
    {
        // Use TcpdfFpdi instead of Fpdi — no FPDF dependency needed
        $fpdi = new \setasign\Fpdi\TcpdfFpdi();

        foreach ($pdfPaths as $pdfPath) {
            if (!file_exists($pdfPath)) {
                // \Log::warning("mergePdfs: skipping missing file {$pdfPath}");
                continue;
            }

            try {
                $pageCount = $fpdi->setSourceFile($pdfPath);

                for ($i = 1; $i <= $pageCount; $i++) {
                    $tpl         = $fpdi->importPage($i);
                    $size        = $fpdi->getTemplateSize($tpl);
                    $orientation = $size['width'] > $size['height'] ? 'L' : 'P';

                    $fpdi->AddPage($orientation, [$size['width'], $size['height']]);
                    $fpdi->useTemplate($tpl);
                }
            } catch (\Exception $e) {
                // \Log::warning("mergePdfs: skipped {$pdfPath} — " . $e->getMessage());
                continue;
            }
        }

        $outputPath = tempnam(sys_get_temp_dir(), 'merged_pdf_') . '.pdf';
        $fpdi->Output($outputPath, 'F');

        return $outputPath;
    }



    // public function makePromotionVoucherDocs($r, $id, $applicationCode, $filesAttachment)
    // {

    //     $path = public_path('template-voucher-marketing.docx');

    //     if (!file_exists($path)) {
    //         return response()->json(['error' => 'File not found'], 404);
    //     }

    //     $filesAttachment = "{$r->root()}/filevendor/$filesAttachment->Path}";

    //     $doc = new TemplateProcessor($path);

    //     $session = $this->userService->getCurrentSessionUserInfo();

    //     $promotion = DB::table('branding_promotion as bp')
    //         ->select('bp.*')
    //         ->where('bp.ID', '=', $id)
    //         ->get();


    //     $ref_no = "DOC-{$applicationCode}";



    //     $doc->setValues([
    //         'vendor_code' => $session->Emp_ID,
    //         'site' => $session->City ?? '',
    //         'marketing_platform' => $promotion[0]->Platform,
    //         'budget_realization' => $this->generatorService->rupiahFormatter($promotion[0]->Estimation),
    //         'nominal_spelled' => $this->generatorService->spellRupiah($promotion[0]->Estimation),
    //         'vp_finance_name' => 'Fariza N Kartosudarmo',
    //         'uploaded_at' => Carbon::now(),
    //         'checker_name' => '',
    //         'sales_name' => $session->Name,
    //         'media' => $promotion[0]->Media_Url
    //     ]);

    //     return [
    //         'doc' => $doc,
    //         'reference_no' => $ref_no,
    //     ];
    // }


    public function submitPromotion(Request $r)
    {
        $b    = $r->all();
        $user = $this->userService->getCurrentSessionUserInfo();


        // Store paths outside transaction for cleanup on failure
        $savedFilePaths = [];

        DB::beginTransaction();

        try {
            $highest         = DB::table('branding_promotion')->max('ID');
            $applicationCode = $this->generatorService->generateApplicationCode($highest, "PROM-VCH");


            $promotionId = DB::table('branding_promotion')->insertGetId([
                "Code"         => $applicationCode,
                "Department"   => $b["department"],
                "Type"         => $b["type"],
                "Platform"     => $b["platform"],
                "Marketing_Id" => $b["marketing_id"],
                "Description"  => $b["description"],
                "Audience"     => $b["audience"],
                "Estimation"   => $b["estimation"],
                "Status"       => $b["status"],
            ]);

            $topic  = "branding-promotion";
            $folder = "filevendor/$this->year/$this->month";


            //file
            $filesAttachment = DB::table('meta_files as mf')->select('mf.*')->where('mf.Map_To', '=', $b['marketing_id'])->where('mf.Topic', '=', 'branding-marketing')->get();


            // ─── Generated .docx ──────────────────────────────────────────────
            // $doc      = $this->generateVoucherRAB($promotionId, $applicationCode, $filesAttachment);



            // $path         = "{$this->generatorService->generateUUID()}.docx";
            // $filename     = "{$folder}/{$path}";
            // $savedDocPath = public_path($filename);

            // Ensure directory exists
            // if (!file_exists(dirname($savedDocPath))) {
            //     mkdir(dirname($savedDocPath), 0755, true);
            // }

            // Move the temp merged PDF to your desired location
            // copy($doc['doc'], $savedDocPath);

            // Optional: clean up temp file
            // @unlink($doc['doc']);

            // $this->store
            // $doc['doc']->saveAs($savedDocPath);

            // $savedFilePaths[] = $savedDocPath;


            // $this->uploadService->uploadMetaFile(
            //     $path,
            //     $filename,
            //     "Voucher Promosi - Dokumen",
            //     'docx',
            //     $topic,
            //     $promotionId
            // );


            // // ─── Approval ─────────────────────────────────────────────────────
            // $level = DB::table('approval_level as al')
            //     ->select('*')
            //     ->where("al.Request_Type", '=', 'BRANDING_PROMOTION')
            //     ->where("al.Level_Number", '=', 1)
            //     ->first();

            // if (!$level) {
            //     throw new \Exception("Approval level not found for BRANDING_PROMOTION level 1.");
            // }

            // DB::table('approval_request')->insertGetId([
            //     "Status"        => "NEED_APPROVAL",
            //     "Requester_Id"  => $user->id,
            //     "Description"   => $level->Description,
            //     "Current_Level" => $level->Level_Number,
            //     "Request_Type"  => $level->Request_Type,
            //     "Level_Id"      => $level->ID,
            //     "Map_To"        => $promotionId,
            // ]);

            // DB::commit();

            // return $doc;
            return response()->json(['status' => 'success', 'message' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();

            // ─── Clean up all saved files on failure ──────────────────────────
            foreach ($savedFilePaths as $filePath) {
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            // print_r($e);

            return response()->json([
                'message' => 'Failed to submit promotion.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function submitMonitoring(Request $r)
    {
        $b = $r->all();

        $monitoringId = DB::table('branding_monitoring')->insertGetId([
            "Promotion_Id" => $b["promotion_id"],
            "Campaign_Start_At" => $b["campaign_start_at"],
            "Campaign_End_At" => $b["campaign_end_at"],
            "Platform" => $b["platform"],
            "Budget_Realization" => $b["budget_realization"],
            "Campaign_Status" => $b["status"],
            "PIC_Id" => $b["sales"],
        ]);

        return response()->json(['message' => 'success']);
    }

    public function followUpPromotion(Request $r)
    {
        $b = $r->all();

        $followUpId = DB::table('branding_followup')->insertGetId([
            "Promotion_Id" => $b["promotion_id"],
            "Platform" => $b["platform"],
            "Total_Budget" => $b["total_budget"],
            "Total_Leads" => $b["total_leads"],
            "Cost_Per_Lead" => $b["cost_per_lead"],
            "Total_Deal" => $b["total_deal"],
            "Conversion_Rate" => $b["conversion_rate"],
            "Cost_Per_Acquisition" => $b["cost_per_acquisition"],
            "Price_Deal_Lead" => $b["price_deal_lead"],
            "Revenue" => $b["revenue"],
            "ROI" => $b["ROI"],
            "Recommendation" => $b["recommendation"],
        ]);


        return response()->json(['message' => 'success']);
    }

    public function getRecapMarketingData(Request $r)
    {

        $d = DB::table('opportunity')->select('ID',  'Source_Channel', 'Date')->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        $allData = [];
        $monthlyData = [];

        foreach ($d as $item) {
            $date = new DateTime($item->Date);
            $month = $date->format('F');
            $year = $date->format('Y');
            $source = $item->Source_Channel ?? 'Unknown';
            $key = $month . '_' . $year . '_' . $source;

            if ($year == '-0001' || $source == 'Unknown' || $source == '') {
                continue;
            }

            if (!isset($monthlyData[$key])) {
                $monthlyData[$key] = [
                    'Month' => $month,
                    'Year' =>  $year,
                    'Platform' => $source,
                    'Ads_Fee' => 0,
                    'Leads' => 0,
                    'Content_Url' => '#'
                ];
            }

            $monthlyData[$key]['Leads']++;

            if (isset($item->Ads_Fee)) {
                $monthlyData[$key]['Ads_Fee'] += $item->Ads_Fee;
            }

            if (isset($item->Leads)) {
                $monthlyData[$key]['Leads'] += $item->Leads;
            }

            if (isset($item->Content_Url) && $item->Content_Url) {
                if (empty($monthlyData[$key]['Content_Url'])) {
                    $monthlyData[$key]['Content_Url'] = $item->Content_Url;
                } else {
                    $monthlyData[$key]['Content_Url'] .= ', ' . $item->Content_Url;
                }
            }
        }

        $allData = array_values($monthlyData);

        usort($allData, function ($a, $b) {
            $monthOrder = array_flip([
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December'
            ]);

            if ($a['Year'] != $b['Year']) {
                return $a['Year'] - $b['Year'];
            }

            return $monthOrder[$a['Month']] - $monthOrder[$b['Month']];
        });

        // dd($allData);

        $counter = 1;
        foreach ($allData as $d) {
            $row = $xml->addChild('row');
            // $row->addAttribute('Opportunity_Id', $d->id);
            $row->addChild('cell', $counter);
            $row->addChild('cell', $d['Month']);
            $row->addChild('cell', $d['Year']);
            $row->addChild('cell', $d['Platform']);
            $row->addChild('cell', $d['Ads_Fee']);
            $row->addChild('cell', $d['Leads']);
            $row->addChild('cell', $d['Content_Url']);
            $counter++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }


    public function exportMarketingData(Request $r)
    {
        return $this->getEvaluationData($r);
    }

    public function generateVoucherRAB(Request $r)
    {
        $promotion = DB::table('branding_promotion as bp')
            ->join('branding_marketing as bm', 'bm.ID', '=', 'bp.Marketing_Id')
            ->where('bp.ID', $r->marketing_id)
            ->select('bp.*', 'bm.*')
            ->first();

        $filesAttachment = DB::table('meta_files as mf')
            ->where('mf.Map_To', $promotion->Marketing_Id)
            ->where('mf.Topic', 'branding-marketing')
            ->get();

        return $this->makePromotionVoucherDocs($promotion, $filesAttachment);
    }
}
