<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\TemplateProcessor;
use Ramsey\Uuid\Uuid;

class UploadService
{

    protected $userService;
    protected $generatorService;
    public function __construct(UserService $u, GeneratorService $g)
    {
        $this->userService = $u;
    }

    public function downloadTemplate(string $templateName, string $userId,  TemplateProcessor $doc)
    {
        $template = public_path($templateName);
        $doc = new TemplateProcessor($template);

        $partner = $this->userService->getCurrentSessionUserInfo($userId);

        $month = Carbon::now()->translatedFormat('F');
        $year = Carbon::now()->year;
        $position = "";
        $contractEnd = "";
        $mitraFullname = $partner->Name;
        $referenceNo = "000/RS/PFS/APL/{$month}/{$year}";

        $doc->setValues([
            'reference_no' => $referenceNo,
            'contract_end' => $contractEnd,
            'now' => Carbon::now(),
            'mitra_fullname' => $mitraFullname,
            'position' => $position
        ]);

        if (!file_exists($template)) {
            abort(404, 'File tidak ditemukan!');
        }

        $uuid = Uuid::uuid4();

        $outputPath = storage_path("app/public/mou_$uuid.docx");

        $doc->saveAs($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend(true);
    }

    public function uploadMetaFile($path, $filename, $desc, $ext, $topic, $mapTo)
    {
        $fileId = DB::table('meta_files')->insertGetId([
            'Filename' => $path,
            'Path' => $filename,
            'Extension' => $ext,
            'Description' => $desc,
            'Topic' => $topic,
            'Map_To' => $mapTo
        ]);

        return $fileId;
    }

    public function storeFile($file, string $type, string $topic, $mapTo, string $description = 'Attachment'): int
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $dir = public_path("filevendor/{$year}/{$month}");

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if ($type === 'base64') {
            $filename = "{$uuid}.png";
            $path = "{$year}/{$month}/{$filename}";
            file_put_contents(public_path("filevendor/{$path}"), base64_decode($file));
            $ext = 'png';
        } else {
            $ext = $file->getClientOriginalExtension();
            $filename = $uuid . '.' . $ext;
            $path = "{$year}/{$month}/{$filename}";
            $file->move($dir, $filename);
        }

        return DB::table('meta_files')->insertGetId([
            'Filename'    => $filename,
            'Path'        => $path,
            'Extension'   => $ext,
            'Description' => $description,
            'Topic'       => $topic,
            'Map_To'      => $mapTo,
            'Created_At'  => now(),
        ]);
    }
}
