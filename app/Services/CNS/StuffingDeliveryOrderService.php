<?php

namespace App\Services\CNS;

use App\Services\UploadService;
use App\VesselSchedule;
use Illuminate\Support\Facades\DB;

class StuffingDeliveryOrderService
{
    public const TOPIC = 'cns-delivery-order';

    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function forSchedule(VesselSchedule $schedule)
    {
        if (!$schedule->DO_Meta_File_ID) {
            return null;
        }

        return DB::table('meta_files')->where('ID', $schedule->DO_Meta_File_ID)->first();
    }

    public function save(
        VesselSchedule $schedule,
        $file,
        ?string $doNo,
        ?string $status,
        ?string $note,
        string $actor
    ): void {
        if ($file) {
            $this->deleteExisting($schedule);

            $fileId = $this->uploadService->storeFile(
                $file,
                'file',
                self::TOPIC,
                (string) $schedule->id,
                'Delivery Order'
            );

            $schedule->DO_Meta_File_ID = $fileId;
        }

        $schedule->DO_No = $doNo;
        $schedule->DO_Status = $status;
        $schedule->DO_Note = $note;
        $schedule->DO_Updated_By = $actor;
        $schedule->DO_Updated_Date = now();
        $schedule->save();
    }

    public function url($meta): ?string
    {
        return $meta ? asset('filevendor/' . $meta->Path) : null;
    }

    private function deleteExisting(VesselSchedule $schedule): void
    {
        $meta = $this->forSchedule($schedule);

        if (!$meta) {
            return;
        }

        $path = public_path('filevendor/' . $meta->Path);

        if (is_file($path)) {
            @unlink($path);
        }

        DB::table('meta_files')->where('ID', $meta->ID)->delete();
        $schedule->DO_Meta_File_ID = null;
    }
}
