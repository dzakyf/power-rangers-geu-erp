<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Services\GeneratorService;
use App\Services\UserService;
use Carbon\Carbon;

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
        $path = public_path('import-leads-template.csv');

        if (!file_exists($path)) {
            abort(404, "Template tidak tersedia");
        };

        return response()->download($path, 'import-leads-template.csv');
    }

    public function uploadAndInsertFromCsv(Request $r)
    {
        $file = $r->file('csv_file');
        $csvData = array_map('str_getcsv', file($file));
        $headers = array_shift($csvData);

        $successCount = 0;

        $estimationModalMapper = [
            '>300 Juta' => 4,
            '200-300 Juta' => 3,
            '100-200 Juta' => 2,
            '<50 Juta' => 1
        ];
        try {
            foreach ($csvData as $idx => $row) {
                if (empty(array_filter($row))) {
                    continue;
                }

                $data = array_combine($headers, $row);

                DB::table('opportunity')->insert([
                    'Full_Name' => $data['Nama Lengkap'],
                    'Source_Channel' => $data['Source'],
                    'Phone_Number' => $data['Phone'],
                    'Hub_Type' => $data['Tipe Hub'],
                    'Task_ID' => $this->generatorService->generateTaskID(),
                    'Location' => $this->userService->getUserOfficeLocation(),
                    'Date' => Carbon::now(),
                    'Partnership_Interest' => "1",
                    'Capital_Estimation' => $estimationModalMapper[$data['Estimasi Modal']],
                    'Created_By' => $this->userService->getCurrentSessionUser(),
                    'Status' => "",
                    'Updated_By' => "",
                ]);

                $successCount++;
            }

            return "Success";
        } catch (\Exception $e) {
            return "Uh Oh! Something terrible has happened!";
        }
    }
}
