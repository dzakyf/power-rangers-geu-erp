<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Services\GeneratorService;
use App\Services\UserService;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UploaderController extends Controller
{
    protected $generatorService;
    protected $userService;

    public function __construct(GeneratorService $generatorService, UserService $userService)
    {
        $this->userService = $userService;
        $this->generatorService = $generatorService;
    }

    public function downloadLeadsTemplate()
    {
        $path = public_path('import-leads-template.xlsx');

        if (!file_exists($path)) {
            abort(404, "Template tidak tersedia");
        };

        return response()->download($path);
    }

    public function uploadAndInsertFromCsv(Request $r)
    {
        $file = $r->file('template');

        $estimationModalMapper = [
            '>300 Juta' => 4,
            '200-300 Juta' => 3,
            '100-200 Juta' => 2,
            '<50 Juta' => 1
        ];

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

            array_shift($rows); // skip header row

            foreach ($rows as $row) {
                // columns: A=0, B=1, C=2, D=3, E=4
                $namaLengkap    = $row[0];
                $phone          = $row[1];
                $source         = $row[2];
                $tipeHub        = $row[3];
                $estimasiModal  = $row[4];

                if (empty(array_filter($row))) {
                    continue;
                }

                DB::table('opportunity')->insert([
                    'Full_Name'            => $namaLengkap,
                    'Source_Channel'       => $source,
                    'Phone_Number'         => $phone,
                    'Hub_Type'             => $tipeHub,
                    'Task_ID'              => $this->generatorService->generateTaskID(),
                    'Location'             => $this->userService->getUserOfficeLocation(),
                    'Date'                 => Carbon::now(),
                    'Partnership_Interest' => "1",
                    'Capital_Estimation'   => $estimationModalMapper[$estimasiModal] ?? null,
                    'Created_By'           => $this->userService->getCurrentSessionUser(),
                    'Status'               => "",
                    'Classification'        => "Fresh",
                    'Updated_By'           => "",
                ]);
            }

            return "Success";
        } catch (\Exception $e) {
            return "Uh Oh! Something terrible has happened!";
        }
    }
}
