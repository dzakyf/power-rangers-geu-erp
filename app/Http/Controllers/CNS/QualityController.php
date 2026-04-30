<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleXMLElement;

class QualityController extends Controller
{
    private $now;

    public function __construct()
    {
        $this->now = Carbon::now();
    }

    public function qualityControlIndexPage()
    {
        return view('cns.quality.index');
    }

    public function insertQualityControlPage()
    {
        return view('cns.quality.create');
    }

    public function insertQualityControl(Request $r)
    {
        $attachmentPath = null;
        $filename = null;
        $extension = null;

        if ($r->hasFile('Attachment')) {
            $file = $r->file('Attachment');
            $year = $this->now->format('Y');
            $month = $this->now->format('m');
            $extension = $file->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;
            $destination = public_path("filevendor/{$year}/{$month}");

            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            $file->move($destination, $filename);
            $attachmentPath = "filevendor/{$year}/{$month}/{$filename}";
        }

        DB::transaction(function () use ($r, $attachmentPath, $filename, $extension) {
            $date = Carbon::parse($r->Created_At ?? $this->now)->toDateString();
            $type = $r->Type;

            $existing = DB::table('uco_quality_control')
                ->whereDate('Created_At', $date)
                ->where('Type', $type)
                ->first();

            $payload = [
                'FFA_As_Palmitic' => $r->FFA_As_Palmitic,
                'FFA_As_Oleic'    => $r->FFA_As_Oleic,
                'MC'              => $r->MC,
                'IMP'             => $r->IMP,
                'Is_Deleted'      => 0,
            ];

            if ($existing) {
                DB::table('uco_quality_control')
                    ->where('ID', $existing->ID)
                    ->update($payload);
                $id = $existing->ID;
            } else {
                $id = DB::table('uco_quality_control')->insertGetId(array_merge($payload, [
                    'Created_At' => $date,
                    'Type'       => $type,
                ]));
            }

            if ($attachmentPath) {
                DB::table('meta_files')
                    ->where('Map_To', $id)
                    ->where('Topic', 'uco-quality-control')
                    ->delete();

                DB::table('meta_files')->insert([
                    'Filename'    => $filename,
                    'Path'        => $attachmentPath,
                    'Extension'   => $extension,
                    'Description' => $r->Description,
                    'Created_At'  => $this->now,
                    'Topic'       => 'uco-quality-control',
                    'Map_To'      => $id,
                ]);
            }
        });

        return response()->json(['status' => 'success', 'message' => 'Berhasil melakukan input']);
    }

    public function getQualityControlCollection($r)
    {
        $query = DB::table('uco_quality_control as uqc')
            ->leftJoin('meta_files as mf', function ($join) {
                $join->on('mf.Map_To', '=', 'uqc.ID')
                    ->where('mf.Topic', '=', 'uco-quality-control');
            })
            ->select(
                'uqc.ID',
                'uqc.Created_At',
                'uqc.FFA_As_Palmitic',
                'uqc.FFA_As_Oleic',
                'uqc.MC',
                'uqc.IMP',
                'uqc.Type',
                'mf.Filename',
                'mf.Path',
                'mf.Extension'
            );

        $query->where('uqc.Is_Deleted', '!=', 1);

        if ($r->Date) {
            $query->whereDate('uqc.Created_At', Carbon::parse($r->Date)->toDateString());
        }

        return $query->get();
    }

    public function getQualityControlData(Request $r)
    {
        $rows = $this->getQualityControlCollection($r);

        $grouped = [];
        foreach ($rows as $row) {
            $date = Carbon::parse($row->Created_At)->toDateString();
            $type = strtolower($row->Type);
            $grouped[$date][$type] = $row;
        }

        $xml = new SimpleXMLElement('<rows/>');

        $counter = 1;
        foreach ($grouped as $date => $types) {
            $top    = $types['top']    ?? null;
            $middle = $types['middle'] ?? null;
            $bottom = $types['bottom'] ?? null;

            $fields = ['FFA_As_Palmitic', 'FFA_As_Oleic', 'MC', 'IMP'];
            $avgs = [];
            foreach ($fields as $field) {
                $values = array_filter([
                    $top    ? $top->$field    : null,
                    $middle ? $middle->$field : null,
                    $bottom ? $bottom->$field : null,
                ], fn($v) => $v !== null);

                $avgs[$field] = count($values) > 0
                    ? round(array_sum($values) / count($values), 2)
                    : null;
            }

            $representative = $top ?? $middle ?? $bottom;
            $attachmentLink = ($representative && $representative->Path)
                ? $r->root() . '/' . $representative->Path
                : null;

            $item = $xml->addChild('row');

            $item->addChild('cell', $counter);
            $item->addChild('cell', Carbon::parse($date)->format('d-M-Y'));

            $item->addChild('cell', $top ? $top->FFA_As_Palmitic : null);
            $item->addChild('cell', $top ? $top->FFA_As_Oleic    : null);
            $item->addChild('cell', $top ? $top->MC . "%"              : null);
            $item->addChild('cell', $top ? $top->IMP . "%"             : null);

            $item->addChild('cell', $middle ? $middle->FFA_As_Palmitic : null);
            $item->addChild('cell', $middle ? $middle->FFA_As_Oleic    : null);
            $item->addChild('cell', $middle && $middle->MC ? $middle->MC . "%"              : null);
            $item->addChild('cell', $middle && $middle->IMP ? $middle->IMP . "%"            : null);

            $item->addChild('cell', $bottom ? $bottom->FFA_As_Palmitic : null);
            $item->addChild('cell', $bottom ? $bottom->FFA_As_Oleic    : null);
            $item->addChild('cell', $bottom ? $bottom->MC . "%"       : null);
            $item->addChild('cell', $bottom ? $bottom->IMP . "%"  : null);

            $item->addChild('cell', $avgs['FFA_As_Palmitic']);
            $item->addChild('cell', $avgs['FFA_As_Oleic']);
            $item->addChild('cell', $avgs['MC'] . "%");
            $item->addChild('cell', $avgs['IMP'] . "%");

            $item->addChild('cell', $attachmentLink);
            $counter++;
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }


    public function editQualityControl(Request $r)
    {
        $date = Carbon::parse($r->Created_At ?? $this->now)->toDateString();

        DB::table('uco_quality_control')
            ->whereDate('Created_At', $date)
            ->where('Type', $r->Type)
            ->update([
                'FFA_As_Palmitic' => $r->FFA_As_Palmitic,
                'FFA_As_Oleic'    => $r->FFA_As_Oleic,
                'MC'              => $r->MC,
                'IMP'             => $r->IMP,
            ]);

        return response()->json(['status' => 'success']);
    }

    public function deleteQualityControl(Request $r)
    {
        DB::table('uco_quality_control')
            ->whereDate('Created_At', Carbon::parse($r->Date)->toDateString())
            ->update(['Is_Deleted' => 1]);

        return response()->json(['status' => 'success']);
    }
}
