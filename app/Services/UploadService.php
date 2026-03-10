<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UploadService
{
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
}
