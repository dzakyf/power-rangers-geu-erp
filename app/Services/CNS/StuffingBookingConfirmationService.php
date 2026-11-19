<?php

namespace App\Services\CNS;

use App\Services\UploadService;
use App\VesselSchedule;
use Illuminate\Support\Facades\DB;

class StuffingBookingConfirmationService
{
    public const TOPIC = 'cns-booking-confirmation';

    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function forSchedule(VesselSchedule $schedule)
    {
        if (!$schedule->BC_Meta_File_ID) {
            return null;
        }

        return DB::table('meta_files')->where('ID', $schedule->BC_Meta_File_ID)->first();
    }

    public function save(
        VesselSchedule $schedule,
        $file,
        ?string $bcNo,
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
                'Booking Confirmation'
            );

            $schedule->BC_Meta_File_ID = $fileId;
        }

        $schedule->BC_No = $bcNo;
        $schedule->BC_Status = $status;
        $schedule->BC_Note = $note;
        $schedule->BC_Updated_By = $actor;
        $schedule->BC_Updated_Date = now();
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
        $schedule->BC_Meta_File_ID = null;
    }
}
